"""Despachador asíncrono y gestor de lotes para la API de PotatoMesh.

Gestiona una cola en memoria acotada con prioridades, agrupa paquetes por lotes
y realiza envíos HTTP POST autenticados con reintentos exponenciales.
"""

from __future__ import annotations

import asyncio
import logging
from collections import deque
from datetime import datetime, timezone
from typing import Any

import aiohttp

logger = logging.getLogger("adaptador-potato.sender")


class PotatoSender:
    """Envía paquetes de forma desacoplada y por lotes a PotatoMesh."""

    def __init__(
        self,
        base_url: str,
        api_token: str,
        cola_max: int = 10_000,
        lote_max: int = 100,
        lote_segundos: float = 1.0,
    ) -> None:
        """Inicializa el despachador.

        Args:
            base_url: URL base de PotatoMesh (ej. 'http://potatomesh:41447').
            api_token: Token Bearer de la API.
            cola_max: Capacidad máxima de la cola interna.
            lote_max: Número máximo de elementos por lote HTTP.
            lote_segundos: Tiempo máximo de retención antes de enviar un lote.
        """
        self.base_url = base_url.rstrip("/")
        self.api_token = api_token
        self.cola_max = cola_max
        self.lote_max = lote_max
        self.lote_segundos = lote_segundos

        self._queue: asyncio.Queue[tuple[str, dict[str, Any], int]] = asyncio.Queue(
            maxsize=cola_max
        )
        self._node_metrics_cache: dict[str, dict[str, Any]] = {}
        self._running: bool = False
        self._dropped_history: deque[float] = deque()

        # Métricas de salud
        self.potatomesh_status: str = "ok"
        self.ultimo_envio_ok: str | None = None
        self.ultimo_intento: float = 0.0

    def update_node_metrics(self, node_id: str, metrics: dict[str, Any]) -> None:
        """Actualiza la caché de métricas de dispositivo para un nodo."""
        if not metrics:
            return
        cached = self._node_metrics_cache.setdefault(node_id, {})
        cached.update({k: v for k, v in metrics.items() if v is not None})

    def get_cached_node_metrics(self, node_id: str) -> dict[str, Any]:
        """Obtiene las últimas métricas de dispositivo conocidas de un nodo."""
        return self._node_metrics_cache.get(node_id, {})

    def enqueue(
        self, endpoint: str, payload: dict[str, Any], priority: int = 1
    ) -> bool:
        """Encola un elemento para su envío a PotatoMesh.

        Prioridades:
        - 0: Alta (nodeinfo, messages, traces). Se retienen con preferencia.
        - 1: Normal (positions, telemetry, neighbors, waypoints). Se descartan si la cola se llena.

        Args:
            endpoint: Ruta relativa de la API (ej. 'messages', 'nodes').
            payload: Datos JSON a enviar.
            priority: 0 para alta prioridad, 1 para prioridad normal.

        Returns:
            True si fue encolado; False si fue descartado por saturación.
        """
        now = asyncio.get_event_loop().time()

        if self._queue.qsize() >= self.cola_max:
            if priority > 0:
                self._record_drop(now)
                logger.warning(
                    "Cola saturada (%d/%d). Descartando elemento baja prioridad para /api/%s",
                    self._queue.qsize(),
                    self.cola_max,
                    endpoint,
                )
                return False

            # Para alta prioridad, si la cola está estrictamente llena, descartar
            self._record_drop(now)
            logger.error(
                "Cola desbordada (%d/%d). No hay espacio ni para elemento prioritario /api/%s",
                self._queue.qsize(),
                self.cola_max,
                endpoint,
            )
            return False

        try:
            self._queue.put_nowait((endpoint, payload, priority))
            return True
        except asyncio.QueueFull:
            self._record_drop(now)
            return False

    def _record_drop(self, now: float) -> None:
        """Registra un descarte y limpia entradas de más de 1 hora."""
        self._dropped_history.append(now)
        threshold = now - 3600.0
        while self._dropped_history and self._dropped_history[0] < threshold:
            self._dropped_history.popleft()

    @property
    def descartados_1h(self) -> int:
        """Devuelve el número de descartes ocurridos en la última hora."""
        now = asyncio.get_event_loop().time()
        threshold = now - 3600.0
        while self._dropped_history and self._dropped_history[0] < threshold:
            self._dropped_history.popleft()
        return len(self._dropped_history)

    @property
    def queue_size(self) -> int:
        """Tamaño actual de la cola."""
        return self._queue.qsize()

    async def run(self) -> None:
        """Bucle principal de procesamiento y envío por lotes."""
        self._running = True
        headers = {
            "Authorization": f"Bearer {self.api_token}",
            "Content-Type": "application/json",
            "User-Agent": "AndaluciaMesh-AdaptadorPotato/1.0",
        }

        # Búferes por endpoint: endpoint -> lista de payloads
        batches: dict[str, list[dict[str, Any]]] = {}
        last_flush_time = asyncio.get_event_loop().time()

        async with aiohttp.ClientSession(
            timeout=aiohttp.ClientTimeout(total=10.0)
        ) as session:
            while self._running:
                now = asyncio.get_event_loop().time()
                timeout = max(0.05, self.lote_segundos - (now - last_flush_time))

                try:
                    item = await asyncio.wait_for(self._queue.get(), timeout=timeout)
                    endpoint, payload, _ = item
                    batches.setdefault(endpoint, []).append(payload)
                    self._queue.task_done()
                except asyncio.TimeoutError:
                    pass

                now = asyncio.get_event_loop().time()
                should_flush = (now - last_flush_time >= self.lote_segundos) or any(
                    len(b) >= self.lote_max for b in batches.values()
                )

                if should_flush and batches:
                    await self._flush_batches(session, batches, headers)
                    batches.clear()
                    last_flush_time = asyncio.get_event_loop().time()

    async def _flush_batches(
        self,
        session: aiohttp.ClientSession,
        batches: dict[str, list[dict[str, Any]]],
        headers: dict[str, str],
    ) -> None:
        """Despacha todos los búferes acumulados a la API de PotatoMesh."""
        for endpoint, items in list(batches.items()):
            if not items:
                continue

            url = f"{self.base_url}/api/{endpoint}"

            # /api/nodes espera un objeto { node_id: data, ... }
            if endpoint == "nodes":
                body: Any = {}
                for it in items:
                    body.update(it)
            else:
                body = items

            await self._send_with_retry(session, url, body, headers, endpoint)

    async def _send_with_retry(
        self,
        session: aiohttp.ClientSession,
        url: str,
        body: Any,
        headers: dict[str, str],
        endpoint: str,
    ) -> None:
        """Envía una petición HTTP con reintentos exponenciales ante fallos transitorios."""
        backoff = 1.0
        max_backoff = 60.0
        max_attempts = 5

        for attempt in range(1, max_attempts + 1):
            try:
                self.ultimo_intento = asyncio.get_event_loop().time()
                async with session.post(url, json=body, headers=headers) as resp:
                    if resp.status in (200, 201):
                        self.ultimo_envio_ok = datetime.now(timezone.utc).isoformat()
                        self.potatomesh_status = "ok"
                        return

                    if resp.status in (401, 403):
                        self.potatomesh_status = "token_rechazado"
                        logger.error(
                            "Error de autenticación contra PotatoMesh (%s): HTTP %d",
                            url,
                            resp.status,
                        )
                        return

                    if 400 <= resp.status < 500 and resp.status != 429:
                        text = await resp.text()
                        logger.warning(
                            "Petición rechazada por PotatoMesh (%s, HTTP %d): %s. Descartando lote.",
                            url,
                            resp.status,
                            text[:200],
                        )
                        return

                    # 429 o 5xx: error temporal
                    text = await resp.text()
                    logger.warning(
                        "Error temporal en PotatoMesh (HTTP %d, intento %d/%d): %s",
                        resp.status,
                        attempt,
                        max_attempts,
                        text[:150],
                    )

            except (aiohttp.ClientError, asyncio.TimeoutError) as exc:
                self.potatomesh_status = "desconectado"
                logger.warning(
                    "Fallo de conexión con PotatoMesh (%s, intento %d/%d): %s",
                    url,
                    attempt,
                    max_attempts,
                    exc,
                )

            if attempt < max_attempts:
                await asyncio.sleep(backoff)
                backoff = min(backoff * 2.0, max_backoff)

    def stop(self) -> None:
        """Detiene el despachador."""
        self._running = False
