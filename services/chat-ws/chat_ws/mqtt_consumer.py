"""Consumidor MQTT asíncrono para el flujo de texto decodificado (snm/v1/decoded/text)."""

import asyncio
import contextlib
import json
import logging
import time
from datetime import UTC, datetime
from typing import Any

import aiomqtt

from chat_ws.config import Settings
from chat_ws.hub import Hub
from chat_ws.protocol import transform_decoded_packet

logger = logging.getLogger("chat_ws.mqtt")


class MqttConsumer:
    """Cliente MQTT asíncrono para suscripción al flujo snm/v1/decoded/text."""

    def __init__(self, settings: Settings, hub: Hub) -> None:
        """Inicializa el consumidor MQTT.

        Args:
            settings: Configuración general del microservicio.
            hub: Instancia del Hub para difusión de mensajes aceptados.
        """
        self.settings = settings
        self.hub = hub
        self.allowed_channels_set = set(settings.allowed_channels_list)

        self.topic = f"{settings.MQTT_TOPIC_PREFIX}/v1/decoded/text"
        self.is_connected = False
        self.disconnected_at: float | None = time.time()
        self.last_message_at: str | None = None

        self._running = False
        self._task: asyncio.Task[None] | None = None

    @property
    def disconnected_seconds(self) -> float:
        """Número de segundos transcurridos desde la última desconexión de MQTT."""
        if self.is_connected:
            return 0.0
        if self.disconnected_at is None:
            return 0.0
        return max(0.0, time.time() - self.disconnected_at)

    def start(self) -> None:
        """Inicia el bucle de consumo MQTT en segundo plano."""
        if self._running:
            return
        self._running = True
        self._task = asyncio.create_task(self._run_loop(), name="mqtt-consumer-loop")

    async def stop(self) -> None:
        """Detiene el consumidor MQTT."""
        self._running = False
        if self._task and not self._task.done():
            self._task.cancel()
            with contextlib.suppress(asyncio.CancelledError):
                await self._task
        self.is_connected = False
        self.disconnected_at = time.time()

    async def _run_loop(self) -> None:
        """Bucle continuo con reconexión automática y backoff exponencial."""
        backoff = 1.0
        max_backoff = 30.0

        while self._running:
            try:
                logger.info(
                    "Conectando a broker MQTT en %s:%d (topic: %s)",
                    self.settings.MQTT_HOST,
                    self.settings.MQTT_PORT,
                    self.topic,
                )

                # TR-04: En aiomqtt>=2.0 se debe usar identifier en lugar de client_id
                async with aiomqtt.Client(
                    hostname=self.settings.MQTT_HOST,
                    port=self.settings.MQTT_PORT,
                    username=self.settings.MQTT_USER,
                    password=self.settings.MQTT_PASSWORD if self.settings.MQTT_PASSWORD else None,
                    identifier="snm-chat-ws",
                ) as client:
                    self.is_connected = True
                    self.disconnected_at = None
                    backoff = 1.0
                    logger.info("Conexión establecida con Mosquitto. Suscribiendo a %s", self.topic)

                    await client.subscribe(self.topic, qos=0)

                    async for message in client.messages:
                        if not self._running:
                            break
                        await self._process_message(message.payload)

            except aiomqtt.MqttError as err:
                self.is_connected = False
                if self.disconnected_at is None:
                    self.disconnected_at = time.time()
                logger.warning(
                    "Pérdida de conexión MQTT (%s). Reintentando en %.1f s...",
                    err,
                    backoff,
                )
            except asyncio.CancelledError:
                break
            except Exception as err:
                self.is_connected = False
                if self.disconnected_at is None:
                    self.disconnected_at = time.time()
                logger.error("Error inesperado en consumidor MQTT: %s", err, exc_info=True)

            if self._running:
                await asyncio.sleep(backoff)
                backoff = min(backoff * 2, max_backoff)

    async def _process_message(self, raw_payload: str | bytes | bytearray) -> None:
        """Procesa una trama recibida desde el broker MQTT.

        Args:
            raw_payload: Carga útil recibida en el topic.
        """
        try:
            if isinstance(raw_payload, (bytes, bytearray)):
                text_content = raw_payload.decode("utf-8", errors="replace")
            else:
                text_content = str(raw_payload)

            data: Any = json.loads(text_content)
            if not isinstance(data, dict):
                return

            transformed = transform_decoded_packet(data, self.allowed_channels_set)
            if transformed is None:
                # Descartado (no texto, to!=^all, canal no permitido o texto vacío)
                return

            channel = str(transformed["channel"])
            self.last_message_at = datetime.now(UTC).strftime("%Y-%m-%dT%H:%M:%SZ")

            # Difusión hacia suscriptores y guardado en búfer circular
            await self.hub.broadcast_message(channel, transformed)

        except json.JSONDecodeError:
            logger.debug("Mensaje MQTT no decodificable como JSON descartado")
        except Exception:
            logger.debug("Error procesando mensaje MQTT entrante", exc_info=True)
