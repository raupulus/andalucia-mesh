"""Servidor HTTP interno para el endpoint de comprobación de salud (/health)."""

import logging

from aiohttp import web

from chat_ws.config import Settings
from chat_ws.hub import Hub
from chat_ws.mqtt_consumer import MqttConsumer

logger = logging.getLogger("chat_ws.health")


def create_health_app(settings: Settings, hub: Hub, mqtt_consumer: MqttConsumer) -> web.Application:
    """Crea y configura la aplicación web aiohttp para el endpoint de salud.

    Args:
        settings: Configuración general del microservicio.
        hub: Concentrador de conexiones WebSocket para obtener número de clientes.
        mqtt_consumer: Consumidor MQTT para consultar estado de enlace.

    Returns:
        Instancia de web.Application de aiohttp lista para ejecutarse.
    """
    app = web.Application()

    async def handle_health(_request: web.Request) -> web.Response:
        """Manejador de la ruta GET /health."""
        is_connected = mqtt_consumer.is_connected
        disconnected_seconds = mqtt_consumer.disconnected_seconds

        # Falla (HTTP 503) solo si MQTT lleva más de 60 segundos desconectado
        mqtt_ok = is_connected or (disconnected_seconds <= 60.0)
        overall_ok = mqtt_ok

        status_code = 200 if overall_ok else 503
        mqtt_status = "conectado" if is_connected else "desconectado"

        payload = {
            "ok": overall_ok,
            "servicio": "chat-ws",
            "version": "0.1.0",
            "mqtt": mqtt_status,
            "clientes": hub.active_connections_count,
            "ultimo_mensaje": mqtt_consumer.last_message_at,
        }

        return web.json_response(payload, status=status_code)

    app.router.add_get("/health", handle_health)
    return app
