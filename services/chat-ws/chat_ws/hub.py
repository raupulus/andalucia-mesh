"""Gestor central de conexiones, suscripciones y difusión en tiempo real."""

import asyncio
import contextlib
import json
import logging
import time
from collections import deque
from typing import Any

from websockets.asyncio.server import ServerConnection
from websockets.exceptions import ConnectionClosed

from chat_ws.history import ChannelHistory

logger = logging.getLogger("chat_ws.hub")


class ClientSession:
    """Representa la sesión activa de un cliente WebSocket."""

    def __init__(
        self,
        ws: ServerConnection,
        ip: str,
        queue_size: int = 500,
        rate_limit_per_sec: int = 5,
        rate_limit_max_violation_s: float = 10.0,
        max_invalid_per_min: int = 5,
    ) -> None:
        """Inicializa la sesión de cliente.

        Args:
            ws: Conexión WebSocket activa.
            ip: Dirección IP resuelta del cliente.
            queue_size: Capacidad máxima de la cola de salida antes de desconexión por lentitud.
            rate_limit_per_sec: Límite de mensajes por segundo admitidos.
            rate_limit_max_violation_s: Segundos de infracción continua antes de cerrar con 1008.
            max_invalid_per_min: Mensajes inválidos acumulados en 1 min antes de cerrar con 1008.
        """
        self.ws = ws
        self.ip = ip
        self.queue_size = queue_size
        self.rate_limit_per_sec = rate_limit_per_sec
        self.rate_limit_max_violation_s = rate_limit_max_violation_s
        self.max_invalid_per_min = max_invalid_per_min

        self.subscribed_channels: set[str] = set()
        self.send_queue: asyncio.Queue[str | None] = asyncio.Queue(maxsize=queue_size)
        self.writer_task: asyncio.Task[None] | None = None
        self.closed = False

        # Control de ritmo (mensajes por segundo)
        self._msg_timestamps: deque[float] = deque()
        self._rate_violation_start: float | None = None

        # Control de mensajes inválidos (en ventana de 60s)
        self._invalid_msg_timestamps: deque[float] = deque()

    def start_writer(self) -> None:
        """Arranca la tarea en segundo plano que vacía la cola de salida hacia el socket."""
        self.writer_task = asyncio.create_task(self._writer_loop(), name="ws-client-writer")

    async def _writer_loop(self) -> None:
        """Bucle de consumo de la cola de salida para envío asíncrono no bloqueante."""
        try:
            while not self.closed:
                payload = await self.send_queue.get()
                if payload is None:
                    # Señal de finalización
                    break
                await self.ws.send(payload)
                self.send_queue.task_done()
        except ConnectionClosed:
            pass
        except asyncio.CancelledError:
            pass
        except Exception:
            logger.debug("Excepción en bucle de escritura de cliente", exc_info=True)
        finally:
            self.closed = True

    def enqueue(self, payload: str) -> bool:
        """Encola un mensaje JSON para su envío al cliente.

        Args:
            payload: Cadena JSON serializada.

        Returns:
            True si se encoló satisfactoriamente; False si la cola está saturada (cliente lento).
        """
        if self.closed:
            return False
        try:
            self.send_queue.put_nowait(payload)
            return True
        except asyncio.QueueFull:
            return False

    def record_incoming_message(self) -> tuple[bool, bool]:
        """Registra un mensaje entrante del cliente para comprobación de rate limiting.

        Returns:
            Tupla (es_rate_limited, debe_cerrar_1008).
        """
        now = time.time()
        self._msg_timestamps.append(now)

        # Purgar marcas con más de 1 segundo de antigüedad
        cutoff = now - 1.0
        while self._msg_timestamps and self._msg_timestamps[0] < cutoff:
            self._msg_timestamps.popleft()

        is_rate_limited = len(self._msg_timestamps) > self.rate_limit_per_sec

        if is_rate_limited:
            if self._rate_violation_start is None:
                self._rate_violation_start = now
            elif (now - self._rate_violation_start) >= self.rate_limit_max_violation_s:
                return True, True
            return True, False

        # Si el cliente modera su ritmo, se restablece el inicio de infracción
        self._rate_violation_start = None
        return False, False

    def record_invalid_message(self) -> bool:
        """Registra un mensaje inválido o malformado recibido.

        Returns:
            True si ha alcanzado el umbral de 5 mensajes inválidos en 1 minuto (debe cerrar 1008).
        """
        now = time.time()
        self._invalid_msg_timestamps.append(now)

        cutoff = now - 60.0
        while self._invalid_msg_timestamps and self._invalid_msg_timestamps[0] < cutoff:
            self._invalid_msg_timestamps.popleft()

        return len(self._invalid_msg_timestamps) >= self.max_invalid_per_min

    async def close(self, code: int = 1000, reason: str = "") -> None:
        """Cierra la sesión y la conexión WebSocket de forma limpia.

        Args:
            code: Código numérico de cierre estándar WebSocket.
            reason: Motivo textual del cierre.
        """
        if self.closed:
            return
        self.closed = True
        with contextlib.suppress(asyncio.QueueFull, Exception):
            self.send_queue.put_nowait(None)

        if self.writer_task and not self.writer_task.done():
            self.writer_task.cancel()

        with contextlib.suppress(Exception):
            await self.ws.close(code=code, reason=reason)


class Hub:
    """Concentrador de conexiones, control de suscripciones y enrutamiento de difusión."""

    def __init__(
        self,
        allowed_channels: list[str],
        history: ChannelHistory,
        max_connections: int = 2000,
        max_per_ip: int = 10,
        queue_size: int = 500,
        rate_limit_per_sec: int = 5,
        rate_limit_max_violation_s: float = 10.0,
        max_invalid_per_min: int = 5,
    ) -> None:
        """Inicializa el Hub.

        Args:
            allowed_channels: Lista ordenada de canales válidos.
            history: Instancia de ChannelHistory para recuperación y registro.
            max_connections: Límite de conexiones simultáneas totales.
            max_per_ip: Límite de conexiones por dirección IP.
            queue_size: Capacidad de cola por cliente antes de considerar cliente lento.
            rate_limit_per_sec: Límite de peticiones por segundo por cliente.
            rate_limit_max_violation_s: Duración en segundos de saturación antes de desconectar.
            max_invalid_per_min: Mensajes no válidos antes de desconexión.
        """
        self.allowed_channels = allowed_channels
        self.allowed_channels_set = set(allowed_channels)
        self.history = history
        self.max_connections = max_connections
        self.max_per_ip = max_per_ip
        self.queue_size = queue_size
        self.rate_limit_per_sec = rate_limit_per_sec
        self.rate_limit_max_violation_s = rate_limit_max_violation_s
        self.max_invalid_per_min = max_invalid_per_min

        self.sessions: set[ClientSession] = set()
        self.ip_connections: dict[str, int] = {}
        self.channel_subscribers: dict[str, set[ClientSession]] = {}

    def register(self, ws: ServerConnection, ip: str) -> tuple[ClientSession | None, str | None]:
        """Intenta registrar una nueva conexión comprobando límites de saturación.

        Args:
            ws: Conexión WebSocket recién aceptada.
            ip: Dirección IP del cliente.

        Returns:
            Tupla (ClientSession, None) si es aceptada, o (None, motivo_error) si supera límites.
        """
        if len(self.sessions) >= self.max_connections:
            return None, "too_many_connections"

        current_ip_count = self.ip_connections.get(ip, 0)
        if current_ip_count >= self.max_per_ip:
            return None, "too_many_connections"

        session = ClientSession(
            ws=ws,
            ip=ip,
            queue_size=self.queue_size,
            rate_limit_per_sec=self.rate_limit_per_sec,
            rate_limit_max_violation_s=self.rate_limit_max_violation_s,
            max_invalid_per_min=self.max_invalid_per_min,
        )
        self.sessions.add(session)
        self.ip_connections[ip] = current_ip_count + 1
        session.start_writer()

        logger.info("Nueva conexión WebSocket registrada (total activas: %d)", len(self.sessions))
        return session, None

    async def unregister(self, session: ClientSession) -> None:
        """Desregistra una sesión y limpia sus suscripciones y contadores.

        Args:
            session: Sesión de cliente a eliminar.
        """
        if session not in self.sessions:
            return

        self.sessions.remove(session)

        current_ip_count = self.ip_connections.get(session.ip, 1)
        if current_ip_count <= 1:
            self.ip_connections.pop(session.ip, None)
        else:
            self.ip_connections[session.ip] = current_ip_count - 1

        for channel in session.subscribed_channels:
            subscribers = self.channel_subscribers.get(channel)
            if subscribers and session in subscribers:
                subscribers.remove(session)

        await session.close()
        logger.info("Conexión WebSocket finalizada (total activas: %d)", len(self.sessions))

    def subscribe(
        self,
        session: ClientSession,
        channels: list[str],
    ) -> tuple[list[str], list[str]]:
        """Procesa una solicitud de suscripción a canales.

        Args:
            session: Sesión solicitante.
            channels: Canales solicitados.

        Returns:
            Tupla (canales_validos_suscritos, canales_desconocidos).
        """
        valid_channels: list[str] = []
        unknown_channels: list[str] = []

        for ch in channels:
            if ch in self.allowed_channels_set:
                valid_channels.append(ch)
                session.subscribed_channels.add(ch)
                if ch not in self.channel_subscribers:
                    self.channel_subscribers[ch] = set()
                self.channel_subscribers[ch].add(session)
            else:
                unknown_channels.append(ch)

        return valid_channels, unknown_channels

    def unsubscribe(self, session: ClientSession, channels: list[str]) -> list[str]:
        """Procesa una solicitud de baja de canales.

        Args:
            session: Sesión solicitante.
            channels: Canales a desuscribir.

        Returns:
            Lista de canales de los que el cliente fue efectivamente desuscrito.
        """
        unsubscribed: list[str] = []
        for ch in channels:
            if ch in session.subscribed_channels:
                session.subscribed_channels.remove(ch)
                subscribers = self.channel_subscribers.get(ch)
                if subscribers and session in subscribers:
                    subscribers.remove(session)
                unsubscribed.append(ch)
        return unsubscribed

    async def broadcast_message(self, channel: str, message: dict[str, Any]) -> int:
        """Almacena el mensaje en el historial y lo transmite a los clientes suscritos.

        Identifica clientes lentos cuya cola de salida esté saturada y los desconecta
        con código 1013 para proteger la latencia de los demás suscriptores.

        Args:
            channel: Nombre del canal de destino.
            message: Mensaje de chat formateado.

        Returns:
            Número de clientes a los que se entregó el mensaje en cola.
        """
        # 1. Registrar en historial circular en memoria
        self.history.add(channel, message)

        subscribers = self.channel_subscribers.get(channel)
        if not subscribers:
            return 0

        payload_json = json.dumps(message, ensure_ascii=False)
        delivered_count = 0
        slow_clients: list[ClientSession] = []

        for session in list(subscribers):
            success = session.enqueue(payload_json)
            if success:
                delivered_count += 1
            else:
                slow_clients.append(session)

        # Desconectar clientes lentos
        for slow in slow_clients:
            logger.warning("Desconectando cliente lento por saturación de cola (código 1013)")
            await self.unregister(slow)

        return delivered_count

    @property
    def active_connections_count(self) -> int:
        """Número de conexiones activas en el Hub."""
        return len(self.sessions)
