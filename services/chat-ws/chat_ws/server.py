"""Servidor principal WebSocket y orquestador del ciclo de vida de chat-ws."""

import asyncio
import json
import logging
import urllib.parse

from aiohttp import web
from websockets.asyncio.server import ServerConnection, serve
from websockets.exceptions import ConnectionClosed

from chat_ws.config import Settings
from chat_ws.health import create_health_app
from chat_ws.history import ChannelHistory
from chat_ws.hub import ClientSession, Hub
from chat_ws.ip_resolver import resolve_client_ip
from chat_ws.mqtt_consumer import MqttConsumer
from chat_ws.protocol import (
    build_error_message,
    build_hello_message,
    build_history_message,
    build_subscribed_message,
    build_unsubscribed_message,
    parse_client_message,
)

logger = logging.getLogger("chat_ws.server")


class ChatServer:
    """Orquestador integral del microservicio chat-ws."""

    def __init__(self, settings: Settings) -> None:
        """Inicializa el servidor con sus componentes dependientes.

        Args:
            settings: Configuración general del servicio.
        """
        self.settings = settings
        self.history = ChannelHistory(
            max_size=settings.CHAT_HISTORIAL,
            max_age_seconds=86400.0,
        )
        self.hub = Hub(
            allowed_channels=settings.allowed_channels_list,
            history=self.history,
            max_connections=settings.CHAT_MAX_CONEXIONES,
            max_per_ip=settings.CHAT_MAX_POR_IP,
            queue_size=settings.CLIENT_QUEUE_SIZE,
            rate_limit_per_sec=settings.RATE_LIMIT_MSGS_PER_SEC,
            rate_limit_max_violation_s=settings.RATE_LIMIT_MAX_VIOLATION_SECONDS,
            max_invalid_per_min=settings.MAX_INVALID_MSGS_PER_MINUTE,
        )
        self.mqtt_consumer = MqttConsumer(settings=settings, hub=self.hub)
        self._stop_event = asyncio.Event()

    async def handle_connection(self, ws: ServerConnection) -> None:
        """Maneja el ciclo de vida de una conexión WebSocket entrante.

        Args:
            ws: Objeto de conexión ServerConnection de websockets.
        """
        # 1. Determinar IP del cliente considerando proxies de confianza
        peer_ip = "127.0.0.1"
        if ws.remote_address:
            peer_ip = str(ws.remote_address[0])

        client_ip = resolve_client_ip(
            peer_ip=peer_ip,
            headers=ws.request.headers if ws.request else {},
            trusted_proxies=self.settings.trusted_proxies_set,
        )

        # 2. Registrar en el Hub comprobando saturación
        session, error_code = self.hub.register(ws, client_ip)
        if session is None:
            # Límites superados: enviar error y cerrar con código 1013 (Try Again Later)
            err_msg = build_error_message(
                "too_many_connections",
                message="Límite de conexiones simultáneas alcanzado. Inténtelo más tarde.",
            )
            try:
                await ws.send(json.dumps(err_msg, ensure_ascii=False))
                await ws.close(code=1013, reason="Too many connections")
            except Exception:
                pass
            return

        try:
            # 3. Enviar mensaje inicial de saludo 'hello'
            hello_msg = build_hello_message(
                channels=self.settings.allowed_channels_list,
                history_size=self.settings.CHAT_HISTORIAL,
            )
            session.enqueue(json.dumps(hello_msg, ensure_ascii=False))

            # 4. Procesar suscripciones iniciales por query param (?channels=Cadiz,sos)
            if ws.request and ws.request.path:
                parsed_url = urllib.parse.urlparse(ws.request.path)
                query_params = urllib.parse.parse_qs(parsed_url.query)
                initial_channels_raw = query_params.get("channels", [])
                if initial_channels_raw:
                    requested_channels: list[str] = []
                    for item in initial_channels_raw:
                        requested_channels.extend([ch.strip() for ch in item.split(",") if ch.strip()])
                    if requested_channels:
                        await self._process_subscription(session, requested_channels)

            # 5. Bucle de recepción de mensajes del cliente
            async for raw_message in ws:
                should_disconnect = await self._handle_client_message(session, raw_message)
                if should_disconnect:
                    break

        except ConnectionClosed:
            pass
        except Exception:
            logger.debug("Excepción en conexión de cliente", exc_info=True)
        finally:
            await self.hub.unregister(session)

    async def _handle_client_message(
        self,
        session: ClientSession,
        raw_data: str | bytes,
    ) -> bool:
        """Procesa una trama recibida desde el cliente.

        Returns:
            True si la conexión debe finalizarse inmediatamente; False si continúa.
        """
        # Control de ritmo (Rate Limiting)
        is_rate_limited, should_close_1008 = session.record_incoming_message()
        if is_rate_limited:
            if should_close_1008:
                err = build_error_message(
                    "rate_limited",
                    message="Límite reiterado de mensajes por segundo superado",
                )
                session.enqueue(json.dumps(err, ensure_ascii=False))
                await session.close(code=1008, reason="Rate limit exceeded")
                return True

            err = build_error_message(
                "rate_limited",
                message="Demasiados mensajes por segundo (máximo 5/s)",
            )
            session.enqueue(json.dumps(err, ensure_ascii=False))
            return False

        # Validación sintáctica y semántica del mensaje
        data, parse_error = parse_client_message(raw_data)
        if data is None:
            should_close = session.record_invalid_message()
            err = build_error_message("invalid_message", message=parse_error or "Mensaje inválido")
            session.enqueue(json.dumps(err, ensure_ascii=False))

            if should_close:
                await session.close(code=1008, reason="Too many invalid messages")
                return True
            return False

        # Procesamiento de comandos de cliente ('subscribe', 'unsubscribe')
        msg_type = data["type"]
        channels: list[str] = data["channels"]

        if msg_type == "subscribe":
            await self._process_subscription(session, channels)
        elif msg_type == "unsubscribe":
            unsubscribed = self.hub.unsubscribe(session, channels)
            unsub_msg = build_unsubscribed_message(unsubscribed)
            session.enqueue(json.dumps(unsub_msg, ensure_ascii=False))

        return False

    async def _process_subscription(
        self,
        session: ClientSession,
        channels: list[str],
    ) -> None:
        """Aplica la suscripción a canales, emite confirmación, errores e historial.

        Args:
            session: Sesión de cliente.
            channels: Lista de canales solicitados.
        """
        valid_channels, unknown_channels = self.hub.subscribe(session, channels)

        # 1. Notificar canales desconocidos si los hubo
        if unknown_channels:
            err = build_error_message("unknown_channel", channels=unknown_channels)
            session.enqueue(json.dumps(err, ensure_ascii=False))

        # 2. Confirmar canales válidos suscritos
        if valid_channels:
            sub_msg = build_subscribed_message(valid_channels)
            session.enqueue(json.dumps(sub_msg, ensure_ascii=False))

            # 3. Emitir el historial en memoria para cada canal suscrito
            for channel in valid_channels:
                history_items = self.history.get(channel)
                history_msg = build_history_message(channel, history_items)
                session.enqueue(json.dumps(history_msg, ensure_ascii=False))

    async def run(self) -> None:
        """Ejecuta los servidores WebSocket y HTTP junto al consumidor MQTT."""
        # 1. Iniciar consumidor MQTT
        self.mqtt_consumer.start()

        # 2. Configurar e iniciar servidor HTTP /health en aiohttp
        health_app = create_health_app(
            settings=self.settings,
            hub=self.hub,
            mqtt_consumer=self.mqtt_consumer,
        )
        health_runner = web.AppRunner(health_app)
        await health_runner.setup()
        health_site = web.TCPSite(
            health_runner,
            self.settings.HEALTH_HOST,
            self.settings.HEALTH_PORT,
        )
        await health_site.start()
        logger.info(
            "Servidor de salud /health escuchando en http://%s:%d/health",
            self.settings.HEALTH_HOST,
            self.settings.HEALTH_PORT,
        )

        # 3. Iniciar servidor WebSocket
        ws_server = await serve(
            self.handle_connection,
            self.settings.CHAT_HOST,
            self.settings.CHAT_PUERTO,
            ping_interval=self.settings.PING_INTERVAL,
            ping_timeout=self.settings.PING_TIMEOUT,
            max_size=2048,
        )
        logger.info(
            "Servidor WebSocket chat-ws escuchando en ws://%s:%d/ws/chat",
            self.settings.CHAT_HOST,
            self.settings.CHAT_PUERTO,
        )

        try:
            await self._stop_event.wait()
        finally:
            logger.info("Iniciando parada ordenada de chat-ws...")
            # Cerrar servidor WebSocket
            ws_server.close()
            await ws_server.wait_closed()

            # Detener consumidor MQTT
            await self.mqtt_consumer.stop()

            # Detener servidor de salud
            await health_runner.cleanup()

            # Desconectar sesiones activas remanentes
            for session in list(self.hub.sessions):
                await session.close(code=1001, reason="Server shutting down")

            logger.info("Microservicio chat-ws detenido correctamente.")

    def stop(self) -> None:
        """Señala al servidor que debe detenerse."""
        self._stop_event.set()
