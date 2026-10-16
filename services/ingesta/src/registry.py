"""Registro en memoria de nodos y gateways para snm-ingesta.

Mantiene el estado vivo más reciente de cada nodo y gateway de la red para:
1. Generar el bloque 'from_node' en el flujo MQTT 'decoded'.
2. Calcular distancias de enlaces RF directos.
3. Evaluar reinicios de nodos.
4. Volcar cambios por lote a la base de datos PostgreSQL cada 5 segundos.
"""

from __future__ import annotations

import re
import statistics
from dataclasses import dataclass, field
from datetime import datetime, timezone
from typing import Any


@dataclass
class NodeRecord:
    """Ficha viva de un nodo en memoria."""

    id: str
    first_seen: datetime
    last_seen: datetime
    node_num: int | None = None
    short_name: str | None = None
    long_name: str | None = None
    role: str | None = None
    hw_model: str | None = None
    firmware: str | None = None
    public_key_fp: str | None = None
    latitude: float | None = None
    longitude: float | None = None
    position_precision_m: float | None = None
    position_source: str | None = None
    last_position_at: datetime | None = None
    province: str | None = None
    border_uncertain: bool = False
    hop_start_last: int | None = None
    hops_min_last: int | None = None
    is_gateway: bool = False
    gateway_first_at: datetime | None = None
    battery_level: int | None = None
    voltage: float | None = None
    battery_at: datetime | None = None
    channel_utilization: float | None = None
    air_util_tx: float | None = None
    metrics_at: datetime | None = None
    uptime_seconds: int | None = None
    uptime_at: datetime | None = None
    last_reboot_at: datetime | None = None

    def to_from_node_dict(self) -> dict[str, Any]:
        """Genera el diccionario 'from_node' para el flujo 'decoded'.

        Campos desconocidos van a None; is_gateway siempre es booleano.
        """
        return {
            "short": self.short_name,
            "long": self.long_name,
            "role": self.role,
            "hw": self.hw_model,
            "is_gateway": self.is_gateway,
            "province": self.province,
        }

    def update_from_peer_item(self, item: dict[str, Any]) -> None:
        """Actualiza los metadatos de un nodo desde un objeto de sincronización de peers.

        Protege campos clave (nombre corto, largo y rol) contra sobreescrituras por
        datos sintéticos o placeholders genéricos emitidos por PotatoMesh u otros peers.
        """
        node_id = self.id
        node_hex = node_id.lstrip("!").lower()
        is_synthetic = bool(item.get("synthetic"))
        peer_short = str(item.get("short_name") or "").strip()
        peer_long = str(item.get("long_name") or "").strip()
        peer_role = str(item.get("role") or "").strip()

        is_generic_long = bool(re.match(r"^Meshtastic\s+[0-9a-fA-F]{4}$", peer_long, re.IGNORECASE))
        is_generic_short = len(node_hex) >= 4 and peer_short.lower() == node_hex[-4:]
        is_placeholder = is_synthetic or is_generic_long or (is_generic_short and not peer_long)

        # 1. Nombre corto y largo: no degradar a genérico si ya tenemos nombre real propio
        is_local_generic_short = (
            len(node_hex) >= 4
            and bool(self.short_name and self.short_name.lower() == node_hex[-4:])
        )
        is_local_generic_long = bool(
            self.long_name
            and re.match(r"^Meshtastic\s+[0-9a-fA-F]{4}$", self.long_name, re.IGNORECASE)
        )
        has_real_local_name = bool(
            (self.long_name and not is_local_generic_long)
            or (self.short_name and not is_local_generic_short)
        )

        if peer_short:
            if not (has_real_local_name and is_generic_short):
                self.short_name = peer_short

        if peer_long:
            if not (has_real_local_name and is_generic_long):
                self.long_name = peer_long

        if item.get("hw_model"):
            self.hw_model = str(item["hw_model"])

        # 2. Rol: no degradar routers de infraestructura ni aceptar CLIENT_HIDDEN sintético
        infra_roles = {"ROUTER", "ROUTER_LATE", "REPEATER"}
        is_local_infra = self.role in infra_roles

        if peer_role:
            if is_local_infra and peer_role not in infra_roles:
                pass
            elif peer_role == "CLIENT_HIDDEN" and self.role:
                pass
            elif is_placeholder and self.role:
                pass
            else:
                self.role = peer_role

        if item.get("firmware"):
            self.firmware = str(item["firmware"])


@dataclass
class GatewayRecord:
    """Ficha viva de un gateway comunitario en memoria."""

    id: str
    first_message_at: datetime
    last_message_at: datetime
    messages_total: int = 1
    typical_interval_s: float | None = None
    _intervals: list[float] = field(default_factory=list)

    def record_message(self, now: datetime) -> None:
        """Registra un nuevo mensaje del gateway y actualiza la mediana móvil de intervalos."""
        delta = (now - self.last_message_at).total_seconds()
        if delta > 0:
            self._intervals.append(delta)
            if len(self._intervals) > 100:
                self._intervals.pop(0)

            if len(self._intervals) >= 10:
                self.typical_interval_s = round(statistics.median(self._intervals), 1)

        self.last_message_at = now
        self.messages_total += 1


class NodeRegistry:
    """Registro global en memoria de nodos y gateways."""

    def __init__(self) -> None:
        """Inicializa el registro vacío."""
        self.nodes: dict[str, NodeRecord] = {}
        self.gateways: dict[str, GatewayRecord] = {}
        self.dirty_nodes: set[str] = set()
        self.dirty_gateways: set[str] = set()

    def get_or_create_node(self, node_id: str, now: datetime) -> NodeRecord:
        """Obtiene un nodo existente o lo registra si es la primera vez que se observa."""
        rec = self.nodes.get(node_id)
        if rec is None:
            rec = NodeRecord(
                id=node_id,
                first_seen=now,
                last_seen=now,
            )
            self.nodes[node_id] = rec
            self.dirty_nodes.add(node_id)
        else:
            rec.last_seen = now
            self.dirty_nodes.add(node_id)
        return rec

    def record_gateway_seen(self, gateway_id: str, now: datetime) -> None:
        """Registra actividad de un gateway y marca su nodo correspondiente como gateway."""
        # 1. Registro de gateway
        gw = self.gateways.get(gateway_id)
        if gw is None:
            gw = GatewayRecord(
                id=gateway_id,
                first_message_at=now,
                last_message_at=now,
            )
            self.gateways[gateway_id] = gw
            self.dirty_gateways.add(gateway_id)
        else:
            gw.record_message(now)
            self.dirty_gateways.add(gateway_id)

        # 2. El nodo correspondiente queda marcado como gateway
        node = self.get_or_create_node(gateway_id, now)
        if not node.is_gateway:
            node.is_gateway = True
            node.gateway_first_at = now
            self.dirty_nodes.add(gateway_id)

    def get_node_coords(
        self,
        node_id: str,
    ) -> tuple[float, float, float] | None:
        """Devuelve (lat, lon, precision_m) si el nodo tiene posición registrada."""
        node = self.nodes.get(node_id)
        if (
            node
            and node.latitude is not None
            and node.longitude is not None
            and node.position_precision_m is not None
        ):
            return node.latitude, node.longitude, node.position_precision_m
        return None

    def pop_dirty_records(self) -> tuple[list[NodeRecord], list[GatewayRecord]]:
        """Extrae y limpia las listas de nodos y gateways modificados para volcado a BD."""
        modified_nodes = [self.nodes[nid] for nid in self.dirty_nodes if nid in self.nodes]
        modified_gateways = [self.gateways[gid] for gid in self.dirty_gateways if gid in self.gateways]

        self.dirty_nodes.clear()
        self.dirty_gateways.clear()

        return modified_nodes, modified_gateways
