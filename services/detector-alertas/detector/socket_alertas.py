"""Servidor de socket UNIX para difusión NDJSON de alertas hacia bots y suscriptores."""

import asyncio
import contextlib
import json
import logging
import os
from datetime import UTC, datetime
from pathlib import Path
from typing import Any

from detector.config import Settings
from detector.motor.protocolos import TransicionAlerta
from detector.persistencia import GestorPersistencia

logger = logging.getLogger("detector.socket")


class ClienteSocket:
    """Representa la sesión de un cliente conectado al socket UNIX."""

    def __init__(self, cliente_id: str, writer: asyncio.StreamWriter, max_pendientes: int) -> None:
        """Inicializa la sesión del cliente."""
        self.cliente_id = cliente_id
        self.writer = writer
        self.queue: asyncio.Queue[bytes] = asyncio.Queue(maxsize=max_pendientes)
        self.conectado_en = datetime.now(UTC)
        self.mensajes_enviados = 0
        self.cerrado = False

    def cerrar(self) -> None:
        """Cierra el writer del cliente."""
        if not self.cerrado:
            self.cerrado = True
            with contextlib.suppress(Exception):
                self.writer.close()


class ServidorSocketAlertas:
    """Servidor de streaming NDJSON sobre socket UNIX con saludo, reenvío y latidos."""

    def __init__(self, settings: Settings, persistencia: GestorPersistencia) -> None:
        """Inicializa el servidor de socket."""
        self.settings = settings
        self.persistencia = persistencia
        self.server: asyncio.Server | None = None
        self.clientes: set[ClienteSocket] = set()
        self._tarea_latido: asyncio.Task[None] | None = None
        self._activo = False

    @property
    def total_clientes(self) -> int:
        """Devuelve el recuento de clientes actualmente conectados y autenticados."""
        return len(self.clientes)

    async def iniciar(self) -> None:
        """Inicia el servidor UNIX limpiando sockets huérfanos y aplicando permisos."""
        path = self.settings.alertas_socket
        directorio = Path(path).parent
        directorio.mkdir(parents=True, exist_ok=True)

        if os.path.exists(path):
            try:
                os.unlink(path)
                logger.debug("Socket huérfano previo eliminado: %s", path)
            except OSError as e:
                logger.warning("No se pudo eliminar socket previo en %s: %s", path, e)

        # Configurar umask para que el socket nazca con permisos seguros
        prev_umask = os.umask(0o007)
        try:
            self.server = await asyncio.start_unix_server(self._manejar_conexion, path=path)
        finally:
            os.umask(prev_umask)

        # Asignar permisos 0660 y GID del grupo snm (10500)
        try:
            os.chmod(path, 0o660)
        except OSError as e:
            logger.warning("No se pudo aplicar chmod 0660 a %s: %s", path, e)

        try:
            os.chown(path, -1, self.settings.alertas_socket_gid)
        except OSError as e:
            logger.warning("No se pudo aplicar GID %d a %s: %s", self.settings.alertas_socket_gid, path, e)

        self._activo = True
        self._tarea_latido = asyncio.create_task(self._bucle_latidos())
        logger.info("Servidor de socket UNIX activo en %s (GID %d)", path, self.settings.alertas_socket_gid)

    async def detener(self) -> None:
        """Detiene el servidor, desconecta clientes y elimina el archivo del socket."""
        self._activo = False
        if self._tarea_latido:
            self._tarea_latido.cancel()
            with contextlib.suppress(asyncio.CancelledError):
                await self._tarea_latido
            self._tarea_latido = None

        for cli in list(self.clientes):
            cli.cerrar()
        self.clientes.clear()

        if self.server:
            self.server.close()
            await self.server.wait_closed()
            self.server = None

        path = self.settings.alertas_socket
        if os.path.exists(path):
            try:
                os.unlink(path)
                logger.debug("Archivo de socket eliminado: %s", path)
            except OSError as e:
                logger.warning("Error al eliminar archivo de socket %s: %s", path, e)

        logger.info("Servidor de socket UNIX detenido.")

    async def _manejar_conexion(self, reader: asyncio.StreamReader, writer: asyncio.StreamWriter) -> None:
        """Gestiona el ciclo de vida completo de un cliente entrante."""
        cli: ClienteSocket | None = None
        try:
            # 1. Esperar saludo obligatorio dentro de SALUDO_TIMEOUT_S
            linea_saludo = await asyncio.wait_for(reader.readline(), timeout=self.settings.saludo_timeout_s)
            if not linea_saludo:
                logger.warning("Cliente cerrado antes de enviar saludo")
                writer.close()
                await writer.wait_closed()
                return

            try:
                saludo_datos: dict[str, Any] = json.loads(linea_saludo.decode("utf-8").strip())
            except Exception as e:
                logger.warning("Saludo de cliente no es JSON válido: %s", e)
                writer.close()
                await writer.wait_closed()
                return

            cliente_id = saludo_datos.get("cliente")
            if not cliente_id or not isinstance(cliente_id, str):
                logger.warning("Saludo inválido: falta campo 'cliente'")
                writer.close()
                await writer.wait_closed()
                return

            desde_ulid = saludo_datos.get("desde")
            logger.info("Cliente de alertas conectado: '%s' (desde: %s)", cliente_id, desde_ulid)

            cli = ClienteSocket(
                cliente_id=cliente_id,
                writer=writer,
                max_pendientes=self.settings.cliente_max_pendientes,
            )

            # 2. Reenvío histórico si solicita parámetro 'desde'
            if desde_ulid and isinstance(desde_ulid, str):
                transiciones = await self.persistencia.obtener_transiciones_reenvio(
                    desde=desde_ulid,
                    max_horas=self.settings.reenvio_max_h,
                )
                logger.info("Enviando reenvío de %d transiciones a cliente '%s'", len(transiciones), cliente_id)
                for t_id, t_trans, t_alerta in transiciones:
                    msg_dict = {
                        "v": 1,
                        "transicion_id": t_id,
                        "transicion": t_trans,
                        "alerta": t_alerta,
                    }
                    linea_bytes = (json.dumps(msg_dict, ensure_ascii=False) + "\n").encode("utf-8")
                    writer.write(linea_bytes)
                    cli.mensajes_enviados += 1
                await writer.drain()

            # 3. Registrar cliente para recibir emisión en directo
            self.clientes.add(cli)

            # 4. Tarea lectora (para detectar cierre remoto) y tarea escritora (drena cola)
            async def lector() -> None:
                while True:
                    datos = await reader.readline()
                    if not datos:
                        break

            async def escritor() -> None:
                assert cli is not None
                while True:
                    bloque = await cli.queue.get()
                    writer.write(bloque)
                    await writer.drain()
                    cli.mensajes_enviados += 1
                    cli.queue.task_done()

            tarea_lec = asyncio.create_task(lector())
            tarea_esc = asyncio.create_task(escritor())

            done, pending = await asyncio.wait(
                [tarea_lec, tarea_esc],
                return_when=asyncio.FIRST_COMPLETED,
            )
            for t in pending:
                t.cancel()

        except TimeoutError:
            logger.warning("Tiempo de saludo agotado (%s s); desconectando cliente", self.settings.saludo_timeout_s)
        except Exception as e:
            logger.error("Error durante conexión con cliente socket: %s", e)
        finally:
            if cli and cli in self.clientes:
                self.clientes.remove(cli)
                logger.info("Cliente '%s' desconectado (total enviados: %d)", cli.cliente_id, cli.mensajes_enviados)
            writer.close()
            with contextlib.suppress(Exception):
                await writer.wait_closed()

    def emitir_transicion(self, t: TransicionAlerta) -> None:
        """Difunde una transición a todos los clientes conectados en tiempo real."""
        if not self.clientes:
            return

        msg_str = t.to_mensaje_socket().model_dump_json() + "\n"
        bloque = msg_str.encode("utf-8")

        clientes_lentos: list[ClienteSocket] = []
        for cli in list(self.clientes):
            if cli.queue.qsize() >= self.settings.cliente_max_pendientes:
                clientes_lentos.append(cli)
            else:
                try:
                    cli.queue.put_nowait(bloque)
                except asyncio.QueueFull:
                    clientes_lentos.append(cli)

        for cli_lento in clientes_lentos:
            logger.warning(
                "Cliente lento '%s' superó límite de %d pendientes. Desconectando.",
                cli_lento.cliente_id,
                self.settings.cliente_max_pendientes,
            )
            cli_lento.cerrar()
            self.clientes.discard(cli_lento)

    async def _bucle_latidos(self) -> None:
        """Emite periódicamente un latido de sincronización a todos los clientes."""
        while self._activo:
            try:
                await asyncio.sleep(self.settings.latido_s)
                if not self.clientes:
                    continue

                ahora_iso = datetime.now(UTC).strftime("%Y-%m-%dT%H:%M:%SZ")
                latido_bytes = (json.dumps({"latido": ahora_iso}) + "\n").encode("utf-8")

                for cli in list(self.clientes):
                    with contextlib.suppress(asyncio.QueueFull):
                        cli.queue.put_nowait(latido_bytes)
            except asyncio.CancelledError:
                break
            except Exception as e:
                logger.error("Error en bucle de latidos del socket: %s", e)
