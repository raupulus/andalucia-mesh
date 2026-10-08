"""Publicador MQTT para eventos sincronizados de peers.

Publica los datos extraídos de peers remotos en snm/v1/peer/<peer_id>/<tipo>
con las credenciales del usuario de servicio svc-potato.
"""

from __future__ import annotations

import asyncio
import json
import logging
from typing import Any

import aiomqtt

from .config import Config

logger = logging.getLogger("sync-peers.mqtt")


class MqttPublisher:
    """Gestiona la publicación asíncrona de eventos de mallas vecinas hacia Mosquitto."""

    def __init__(self, config: Config) -> None:
        """Inicializa el publicador MQTT.

        Args:
            config: Configuración global del servicio.
        """
        self.config = config
        self._client: aiomqtt.Client | None = None
        self._running: bool = False
        self._connected: bool = False

    async def connect(self) -> None:
        """Establece la conexión con el broker interno."""
        self._running = True
        logger.info(
            "Conectando publicador MQTT a %s:%d (usuario: %s)...",
            self.config.mqtt_host,
            self.config.mqtt_port,
            self.config.mqtt_user,
        )
        self._client = aiomqtt.Client(
            hostname=self.config.mqtt_host,
            port=self.config.mqtt_port,
            username=self.config.mqtt_user,
            password=self.config.mqtt_password,
            identifier="snm-sync-peers",
            clean_session=True,
            timeout=15.0,
        )
        try:
            await self._client.__aenter__()
            self._connected = True
            logger.info("Publicador MQTT conectado exitosamente.")
        except Exception as exc:
            self._connected = False
            logger.warning("Fallo inicial conectando a Mosquitto: %s", exc)

    async def publish_event(
        self, peer_id: str, event_type: str, data: Any
    ) -> bool:
        """Publica un lote de eventos en el topic canónico del peer.

        Topic: snm/v1/peer/<peer_id>/<event_type>

        Args:
            peer_id: Identificador del peer.
            event_type: Tipo de evento ('messages', 'nodes', 'traces').
            data: Contenido a serializar en JSON.

        Returns:
            True si se publicó; False si hubo error.
        """
        if not self._client or not self._connected:
            # Reintentar conexión si estaba caído
            try:
                if self._client:
                    await self._client.__aenter__()
                    self._connected = True
            except Exception:
                self._connected = False
                return False

        topic = f"snm/v1/peer/{peer_id}/{event_type}"

        if isinstance(data, list):
            if not data:
                return True
            chunk_size = 10
            for i in range(0, len(data), chunk_size):
                chunk = data[i : i + chunk_size]
                payload_bytes = json.dumps(chunk, ensure_ascii=False).encode("utf-8")
                if len(payload_bytes) > 12288:
                    # Si incluso 10 elementos superan 12 KB, publicar uno a uno
                    for single_item in chunk:
                        single_bytes = json.dumps([single_item], ensure_ascii=False).encode("utf-8")
                        if len(single_bytes) > 14000:
                            logger.warning(
                                "Elemento individual en %s supera 14 KB (%d B), omitido",
                                topic,
                                len(single_bytes),
                            )
                            continue
                        try:
                            await self._client.publish(topic, payload=single_bytes, qos=0, retain=False)
                        except Exception as exc:
                            logger.warning("Error publicando en MQTT (%s): %s", topic, exc)
                            self._connected = False
                            return False
                else:
                    try:
                        await self._client.publish(topic, payload=payload_bytes, qos=0, retain=False)
                    except Exception as exc:
                        logger.warning("Error publicando en MQTT (%s): %s", topic, exc)
                        self._connected = False
                        return False
            return True

        payload_bytes = json.dumps(data, ensure_ascii=False).encode("utf-8")
        if len(payload_bytes) > 14000:
            logger.warning(
                "Payload MQTT para %s excede límite de 14 KB (%d B), omitido",
                topic,
                len(payload_bytes),
            )
            return False

        try:
            await self._client.publish(topic, payload=payload_bytes, qos=0, retain=False)
            return True
        except Exception as exc:
            logger.warning("Error publicando en MQTT (%s): %s", topic, exc)
            self._connected = False
            return False

    async def close(self) -> None:
        """Cierra la conexión MQTT limpiamente."""
        self._running = False
        if self._client and self._connected:
            try:
                await self._client.__aexit__(None, None, None)
            except Exception:
                pass
            self._connected = False
