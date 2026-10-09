"""Motor de sincronización asíncrono para mallas vecinas (sync-peers).

Realiza consultas incrementales paginadas a las APIs remotas, aplica filtros de
canales, realiza el doble despacho (PotatoMesh local + Mosquitto) y gestiona
cursores atómicos y esperas progresivas.
"""

from __future__ import annotations

import asyncio
import json
import logging
from datetime import datetime, timezone
from typing import Any

import aiohttp
import asyncpg

from .config import Config
from .db import (
    get_or_create_cursor,
    record_peer_failure,
    record_peer_success,
    update_cursor_messages,
    update_cursor_nodes,
    update_cursor_traces,
)
from .mqtt import MqttPublisher
from .peers import PeerConfig, PeerManager

logger = logging.getLogger("sync-peers.syncer")

MAX_RESPONSE_BYTES = 8 * 1024 * 1024  # 8 MiB


class PeerSyncer:
    """Orquestador de tareas de sincronización por peer."""

    def __init__(
        self,
        config: Config,
        pool: asyncpg.Pool,
        peer_manager: PeerManager,
        mqtt_publisher: MqttPublisher,
    ) -> None:
        """Inicializa el sincronizador.

        Args:
            config: Configuración global del microservicio.
            pool: Pool de conexiones PostgreSQL.
            peer_manager: Gestor de configuración de peers.
            mqtt_publisher: Publicador MQTT para eventos snm/v1/peer/#.
        """
        self.config = config
        self.pool = pool
        self.peer_manager = peer_manager
        self.mqtt_publisher = mqtt_publisher

        self._running: bool = False
        self._peer_tasks: dict[str, asyncio.Task[None]] = {}
        self.potatomesh_status: str = "ok"
        self._potatomesh_last_fail: float = 0.0

    async def run(self) -> None:
        """Bucle supervisor que recarga peers.json y orquesta las tareas por peer."""
        self._running = True

        async with aiohttp.ClientSession(
            timeout=aiohttp.ClientTimeout(total=20.0)
        ) as session:
            while self._running:
                peers = self.peer_manager.reload()

                # Iniciar tareas para peers nuevos o activos
                for peer_id, peer in peers.items():
                    if peer.activo:
                        if peer_id not in self._peer_tasks or self._peer_tasks[peer_id].done():
                            self._peer_tasks[peer_id] = asyncio.create_task(
                                self._peer_worker(peer, session)
                            )
                    else:
                        # Cancelar si fue desactivado
                        if peer_id in self._peer_tasks and not self._peer_tasks[peer_id].done():
                            self._peer_tasks[peer_id].cancel()

                # Cancelar tareas de peers eliminados del archivo
                for peer_id, task in list(self._peer_tasks.items()):
                    if peer_id not in peers:
                        if not task.done():
                            task.cancel()
                        del self._peer_tasks[peer_id]

                await asyncio.sleep(15.0)

    async def _peer_worker(self, peer: PeerConfig, session: aiohttp.ClientSession) -> None:
        """Bucle de trabajo dedicado para un peer específico."""
        logger.info("Iniciando tarea de sincronización para peer '%s' (%s)", peer.id, peer.url)

        headers = {
            "User-Agent": self.config.user_agent,
            "Accept": "application/json",
        }

        while self._running:
            try:
                cursor = await get_or_create_cursor(self.pool, peer.id)
                consecutive_fails = cursor.get("fallos_consecutivos", 0)

                # 1. Mensajes (si están habilitados)
                if peer.mensajes:
                    await self._sync_messages(peer, session, headers, cursor)

                # 2. Trazas (si están habilitadas)
                if peer.trazas:
                    await self._sync_traces(peer, session, headers, cursor)

                # 3. Nodos (si están habilitados)
                if peer.nodos:
                    await self._sync_nodes(peer, session, headers, cursor)

                # Ciclo exitoso
                await record_peer_success(self.pool, peer.id)
                sleep_time = float(self.config.ciclo_mensajes_s)

            except asyncio.CancelledError:
                break
            except Exception as exc:
                err_msg = str(exc)
                logger.warning("Error en sincronización con peer '%s': %s", peer.id, err_msg)
                await record_peer_failure(self.pool, peer.id, err_msg)

                # Espera progresiva: 60 s -> 300 s (5m) -> 900 s (15m)
                cursor = await get_or_create_cursor(self.pool, peer.id)
                fails = cursor.get("fallos_consecutivos", 1)
                if fails <= 1:
                    sleep_time = 60.0
                elif fails == 2:
                    sleep_time = 300.0
                else:
                    sleep_time = 900.0

            await asyncio.sleep(sleep_time)

    async def _fetch_json(
        self, session: aiohttp.ClientSession, url: str, headers: dict[str, str]
    ) -> Any:
        """Descarga JSON controlando tamaño máximo (8 MiB) y timeouts."""
        async with session.get(url, headers=headers) as resp:
            if resp.status != 200:
                raise ValueError(f"HTTP {resp.status} al consultar {url}")

            chunks: list[bytes] = []
            total = 0
            async for chunk in resp.content.iter_chunked(65536):
                total += len(chunk)
                if total > MAX_RESPONSE_BYTES:
                    raise ValueError(f"Respuesta excede límite de 8 MiB ({total} bytes)")
                chunks.append(chunk)

            raw = b"".join(chunks).decode("utf-8")
            return json.loads(raw)

    async def _sync_messages(
        self,
        peer: PeerConfig,
        session: aiohttp.ClientSession,
        headers: dict[str, str],
        cursor: dict[str, Any],
    ) -> None:
        """Sincroniza mensajes de forma incremental y paginada."""
        since_time: datetime | None = cursor.get("last_message_time")
        current_cursor = int(since_time.timestamp()) if since_time else None

        pages = 0
        while pages < self.config.paginas_max:
            pages += 1
            query = "?limit=100"
            if current_cursor is not None:
                query += f"&since={current_cursor}"

            url = f"{peer.url}/api/messages{query}"
            data = await self._fetch_json(session, url, headers)

            if not isinstance(data, list) or not data:
                break

            # Filtrar mensajes
            valid_msgs: list[dict[str, Any]] = []
            max_rx = current_cursor

            for msg in data:
                if not isinstance(msg, dict):
                    continue

                # Descartar mensajes directos
                to_id = str(msg.get("to", msg.get("to_id", "")))
                if to_id and to_id not in ("^all", "0xffffffff") and not to_id.startswith("^"):
                    continue

                # Canal: verificar lista blanca
                ch_name = msg.get("channel_name", msg.get("channel"))
                if isinstance(ch_name, str):
                    res = self.config.get_channel_index_and_name(ch_name)
                    if res is None:
                        continue
                    ch_idx, _ = res
                    msg["channel"] = ch_idx

                rx = msg.get("rx_time")
                if isinstance(rx, int) and (max_rx is None or rx > max_rx):
                    max_rx = rx

                valid_msgs.append(msg)

            if valid_msgs:
                # Doble despacho
                await self._post_local_potatomesh(session, "messages", valid_msgs)
                await self.mqtt_publisher.publish_event(peer.id, "messages", valid_msgs)

            if max_rx is not None:
                current_cursor = max_rx
                new_dt = datetime.fromtimestamp(max_rx, tz=timezone.utc)
                await update_cursor_messages(self.pool, peer.id, new_dt)

            if len(data) < 100:
                break

    async def _sync_traces(
        self,
        peer: PeerConfig,
        session: aiohttp.ClientSession,
        headers: dict[str, str],
        cursor: dict[str, Any],
    ) -> None:
        """Sincroniza trazas de forma incremental."""
        since_time: datetime | None = cursor.get("last_trace_time")
        current_cursor = int(since_time.timestamp()) if since_time else None

        pages = 0
        while pages < self.config.paginas_max:
            pages += 1
            query = "?limit=100"
            if current_cursor is not None:
                query += f"&since={current_cursor}"

            url = f"{peer.url}/api/traces{query}"
            data = await self._fetch_json(session, url, headers)

            if not isinstance(data, list) or not data:
                break

            max_rx = current_cursor
            for tr in data:
                if isinstance(tr, dict):
                    rx = tr.get("rx_time")
                    if isinstance(rx, int) and (max_rx is None or rx > max_rx):
                        max_rx = rx

            await self._post_local_potatomesh(session, "traces", data)
            await self.mqtt_publisher.publish_event(peer.id, "traces", data)

            if max_rx is not None:
                current_cursor = max_rx
                new_dt = datetime.fromtimestamp(max_rx, tz=timezone.utc)
                await update_cursor_traces(self.pool, peer.id, new_dt)

            if len(data) < 100:
                break

    async def _sync_nodes(
        self,
        peer: PeerConfig,
        session: aiohttp.ClientSession,
        headers: dict[str, str],
        cursor: dict[str, Any],
    ) -> None:
        """Sincroniza nodos si ha transcurrido el intervalo configurado."""
        now = datetime.now(timezone.utc)
        last_sync: datetime | None = cursor.get("last_node_sync")

        if last_sync and (now - last_sync).total_seconds() < peer.intervalo_nodos_s:
            return

        # Arranque en frío (primera sincronización del peer): solicitar 1000 nodos
        # para registrar todo el catálogo histórico disponible en su ventana.
        # En sincronizaciones continuas de mantenimiento: solicitar 30 nodos recientes,
        # cubriendo con holgura los últimos minutos con impacto de red y CPU insignificante.
        limit = 1000 if last_sync is None else 30
        url = f"{peer.url}/api/nodes?limit={limit}"
        data = await self._fetch_json(session, url, headers)

        if not isinstance(data, (dict, list)) or not data:
            return

        # PotatoMesh POST /api/nodes requiere un objeto JSON / Hash {node_id: node_data}
        if isinstance(data, list):
            payload = {
                str(node.get("node_id")): node
                for node in data
                if isinstance(node, dict) and node.get("node_id")
            }
        else:
            payload = data

        if not payload:
            return

        await self._post_local_potatomesh(session, "nodes", payload)
        await self.mqtt_publisher.publish_event(peer.id, "nodes", data)
        await update_cursor_nodes(self.pool, peer.id, now)

    async def _post_local_potatomesh(
        self, session: aiohttp.ClientSession, endpoint: str, body: Any
    ) -> None:
        """Envía los datos a la instancia local de PotatoMesh con cabecera de autenticación."""
        url = f"{self.config.potatomesh_url}/api/{endpoint}"
        headers = {
            "Authorization": f"Bearer {self.config.potatomesh_api_token}",
            "Content-Type": "application/json",
            "User-Agent": self.config.user_agent,
        }

        try:
            async with session.post(url, json=body, headers=headers) as resp:
                if resp.status in (200, 201):
                    self.potatomesh_status = "ok"
                    return

                if resp.status in (401, 403):
                    self.potatomesh_status = "token_rechazado"
                    logger.error("Token de PotatoMesh local rechazado (HTTP %d)", resp.status)
                    return

                if resp.status >= 500:
                    self._potatomesh_last_fail = asyncio.get_event_loop().time()
                    self.potatomesh_status = "error_servidor"
                    logger.warning("Fallo interno en PotatoMesh local: HTTP %d", resp.status)

        except Exception as exc:
            self._potatomesh_last_fail = asyncio.get_event_loop().time()
            self.potatomesh_status = "desconectado"
            logger.warning("Fallo conectando a PotatoMesh local (%s): %s", url, exc)

    def stop(self) -> None:
        """Detiene todas las tareas activas."""
        self._running = False
        for task in self._peer_tasks.values():
            if not task.done():
                task.cancel()
