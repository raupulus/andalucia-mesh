"""Publicador del flujo MQTT normalizado 'decoded' para snm-ingesta.

Serializa paquetes unificados conforme a la especificación de 06-decoded-stream.md
y los emite en tiempo real a 'snm/v1/decoded/<portnum>' con QoS 0.
"""

from __future__ import annotations

import json
import logging
from typing import Any

from aiomqtt import Client

from .dedup import UnifiedPacket
from .registry import NodeRegistry

logger = logging.getLogger("ingesta.publisher")


class DecodedPublisher:
    """Publicador de mensajes JSON en topics snm/v1/decoded/<portnum>."""

    def __init__(
        self,
        mqtt_client: Client | None,
        registry: NodeRegistry,
        topic_prefix: str = "snm",
    ) -> None:
        """Inicializa el publicador.

        Args:
            mqtt_client: Cliente aiomqtt activo para publicación.
            registry: Registro vivo de nodos para enriquecer el bloque 'from_node'.
            topic_prefix: Prefijo de tópicos del sistema ('snm').
        """
        self.mqtt_client = mqtt_client
        self.registry = registry
        self.topic_prefix = topic_prefix.rstrip("/")

        # Contadores de publicación
        self.decoded_enviados: int = 0
        self.decoded_perdidos: int = 0

    def format_decoded_message(self, packet: UnifiedPacket) -> dict[str, Any]:
        """Construye el diccionario conforme a la versión 1 del flujo 'decoded'.

        Args:
            packet: Objeto UnifiedPacket tras el cierre de su ventana de acumulación.

        Returns:
            Diccionario estructurado listo para volcado JSON.
        """
        # 1. Obtener metadatos enriquecidos del emisor desde el registro
        node_rec = self.registry.nodes.get(packet.from_id)
        if node_rec is not None:
            from_node_meta = node_rec.to_from_node_dict()
        else:
            from_node_meta = {
                "short": None,
                "long": None,
                "role": None,
                "hw": None,
                "is_gateway": False,
                "province": packet.province,
            }

        # 2. Formatear lista de recepciones
        receptions_list = [r.to_decoded_reception_dict() for r in packet.receptions]

        # 3. Construir mensaje canónico
        msg: dict[str, Any] = {
            "v": 1,
            "packet_id": packet.packet_id,
            "from": packet.from_id,
            "from_node": from_node_meta,
            "to": packet.to_id,
            "portnum": packet.portnum or "other",
            "channel": packet.channel,
            "rx_first": packet.rx_first.isoformat().replace("+00:00", "Z"),
            "hop_start": packet.hop_start,
            "hops_min": packet.hops_min,
            "via_mqtt": packet.via_mqtt,
            "ok_to_mqtt": packet.ok_to_mqtt,
            "airtime_ms": packet.airtime_ms,
            "receptions": receptions_list,
            "payload": packet.payload,
        }

        return msg

    async def publish_packet(self, packet: UnifiedPacket) -> bool:
        """Publica el paquete en el topic snm/v1/decoded/<portnum>.

        Args:
            packet: Objeto UnifiedPacket que expira su ventana.

        Returns:
            True si se publicó exitosamente; False si el broker no estaba accesible.
        """
        # Descarte de paquetes PKI (mensajes directos cifrados por privacidad)
        if packet.decrypt_status == "pki":
            return False

        portnum_topic = packet.portnum or "other"
        topic = f"{self.topic_prefix}/v1/decoded/{portnum_topic}"

        msg_dict = self.format_decoded_message(packet)
        payload_bytes = json.dumps(msg_dict, ensure_ascii=False, separators=(",", ":")).encode("utf-8")

        if self.mqtt_client is None:
            self.decoded_perdidos += 1
            return False

        try:
            await self.mqtt_client.publish(
                topic=topic,
                payload=payload_bytes,
                qos=0,
                retain=False,
            )
            self.decoded_enviados += 1
            return True
        except Exception as e:
            logger.warning("Fallo al publicar flujo decoded en topic %s: %s", topic, e)
            self.decoded_perdidos += 1
            return False
