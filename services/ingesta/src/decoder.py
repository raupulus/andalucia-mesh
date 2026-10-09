"""Decodificación de paquetes Protobuf Meshtastic y validación de OK to MQTT.

Convierte los mensajes internos de Data en diccionarios serializables limpios,
extrae entidades especializadas (Position, User, Telemetry, NeighborInfo, etc.)
y aplica la validación del bit de consentimiento OK to MQTT.
"""

from __future__ import annotations

import hashlib
import logging
from dataclasses import dataclass
from typing import Any

from meshtastic.protobuf import (
    config_pb2,
    mesh_pb2,
    portnums_pb2,
    telemetry_pb2,
)

from .geo import calculate_precision_m

logger = logging.getLogger("ingesta.decoder")


@dataclass(slots=True)
class DecodedPayload:
    """Resultado de la decodificación de una carga Data."""

    portnum_name: str
    portnum_num: int
    variant: str | None
    payload_dict: dict[str, Any]
    ok_to_mqtt: bool | None
    is_valid_consent: bool


def check_ok_to_mqtt(
    from_id: str,
    gateway_id: str,
    data: mesh_pb2.Data | None,
) -> tuple[bool | None, bool]:
    """Valida el bit OK to MQTT conforme al contrato de ingesta (05.2).

    Regla:
    - Si el paquete es propio del gateway (from_id == gateway_id): siempre permitido (True, True).
    - Sin objeto Data (no descifrado o map report): ok_to_mqtt es None, permitido (None, True).
    - Con Data de un tercero: bit 0 de bitfield debe ser 1. Si es 0 o ausente -> descartado.

    Returns:
        Tupla (ok_to_mqtt_valor, is_valid_consent).
    """
    if from_id == gateway_id:
        return True, True

    if data is None:
        return None, True

    bitfield = getattr(data, "bitfield", 0)
    has_consent = bool(bitfield & 1)
    return has_consent, has_consent


def decode_data_payload(
    from_id: str,
    gateway_id: str,
    data: mesh_pb2.Data,
) -> DecodedPayload:
    """Decodifica un objeto Data en un DecodedPayload estructurado.

    Args:
        from_id: Identificador del nodo emisor en formato '!%08x'.
        gateway_id: Identificador del gateway que capturó el paquete.
        data: Objeto Data de Protobuf.

    Returns:
        Estructura DecodedPayload con el nombre de portnum, variante y payload JSON.
    """
    ok_to_mqtt_val, is_valid_consent = check_ok_to_mqtt(from_id, gateway_id, data)
    if not is_valid_consent:
        return DecodedPayload(
            portnum_name="other",
            portnum_num=data.portnum,
            variant=None,
            payload_dict={},
            ok_to_mqtt=False,
            is_valid_consent=False,
        )

    portnum = data.portnum
    payload_bytes = data.payload

    try:
        # 1. TEXT_MESSAGE_APP (1)
        if portnum == portnums_pb2.PortNum.TEXT_MESSAGE_APP:
            text = payload_bytes.decode("utf-8", errors="replace")
            res: dict[str, Any] = {"text": text}
            if data.reply_id:
                res["reply_id"] = data.reply_id
            if data.emoji:
                res["emoji"] = data.emoji
            return DecodedPayload("text", portnum, None, res, ok_to_mqtt_val, True)

        # 2. POSITION_APP (3)
        elif portnum == portnums_pb2.PortNum.POSITION_APP:
            pos = mesh_pb2.Position()
            pos.ParseFromString(payload_bytes)
            lat = round(pos.latitude_i * 1e-7, 6) if pos.latitude_i else None
            lon = round(pos.longitude_i * 1e-7, 6) if pos.longitude_i else None
            p_bits = pos.precision_bits if pos.precision_bits else None
            p_meters = calculate_precision_m(p_bits)

            res = {
                "latitude": lat,
                "longitude": lon,
                "altitude": pos.altitude if pos.altitude else None,
                "precision_bits": p_bits,
                "precision_m": round(p_meters, 1),
                "time": pos.time if pos.time else None,
                "sats_in_view": pos.sats_in_view if pos.sats_in_view else None,
            }
            # Filtrar claves con valores None dentro de payload
            filtered = {k: v for k, v in res.items() if v is not None}
            return DecodedPayload("position", portnum, None, filtered, ok_to_mqtt_val, True)

        # 3. NODEINFO_APP (4)
        elif portnum == portnums_pb2.PortNum.NODEINFO_APP:
            user = mesh_pb2.User()
            user.ParseFromString(payload_bytes)

            fp = None
            if user.public_key:
                fp = hashlib.sha256(user.public_key).hexdigest()[:16]

            hw_name = mesh_pb2.HardwareModel.Name(user.hw_model) if user.hw_model else None
            try:
                role_name = config_pb2.Config.DeviceConfig.Role.Name(user.role)
            except (ValueError, TypeError):
                role_name = "CLIENT"

            res = {
                "id": from_id,  # Manda from_id conforme a 05.2
                "long_name": user.long_name or None,
                "short_name": user.short_name or None,
                "hw_model": hw_name,
                "role": role_name,
                "is_licensed": user.is_licensed,
                "public_key_fp": fp,
            }
            filtered = {k: v for k, v in res.items() if v is not None}
            return DecodedPayload("nodeinfo", portnum, None, filtered, ok_to_mqtt_val, True)

        # 4. ROUTING_APP (5)
        elif portnum == portnums_pb2.PortNum.ROUTING_APP:
            routing = mesh_pb2.Routing()
            routing.ParseFromString(payload_bytes)
            reason_name = mesh_pb2.Routing.Error.Name(routing.error_reason) if routing.error_reason else "NONE"
            res = {
                "error_reason": reason_name,
                "request_id": data.request_id if data.request_id else None,
            }
            filtered = {k: v for k, v in res.items() if v is not None}
            return DecodedPayload("routing", portnum, None, filtered, ok_to_mqtt_val, True)

        # 5. TELEMETRY_APP (67)
        elif portnum == portnums_pb2.PortNum.TELEMETRY_APP:
            telem = telemetry_pb2.Telemetry()
            telem.ParseFromString(payload_bytes)

            variant = None
            telem_dict: dict[str, Any] = {}

            if telem.HasField("device_metrics"):
                variant = "device_metrics"
                dm = telem.device_metrics
                telem_dict = {
                    "battery_level": dm.battery_level if dm.battery_level else None,
                    "voltage": round(dm.voltage, 2) if dm.voltage else None,
                    "channel_utilization": round(dm.channel_utilization, 1) if dm.channel_utilization else None,
                    "air_util_tx": round(dm.air_util_tx, 1) if dm.air_util_tx else None,
                    "uptime_seconds": dm.uptime_seconds if dm.uptime_seconds else None,
                }
            elif telem.HasField("environment_metrics"):
                variant = "environment_metrics"
                em = telem.environment_metrics
                telem_dict = {
                    "temperature": round(em.temperature, 1) if em.temperature else None,
                    "relative_humidity": round(em.relative_humidity, 1) if em.relative_humidity else None,
                    "barometric_pressure": round(em.barometric_pressure, 1) if em.barometric_pressure else None,
                    "gas_resistance": round(em.gas_resistance, 1) if em.gas_resistance else None,
                    "voltage": round(em.voltage, 2) if em.voltage else None,
                    "current": round(em.current, 2) if em.current else None,
                }
            elif telem.HasField("local_stats"):
                variant = "local_stats"
                ls = telem.local_stats
                telem_dict = {
                    "uptime_seconds": ls.uptime_seconds if ls.uptime_seconds else None,
                    "channel_utilization": round(ls.channel_utilization, 1) if ls.channel_utilization else None,
                    "air_util_tx": round(ls.air_util_tx, 1) if ls.air_util_tx else None,
                    "num_packets_tx": ls.num_packets_tx if ls.num_packets_tx else None,
                    "num_packets_rx": ls.num_packets_rx if ls.num_packets_rx else None,
                    "num_packets_rx_bad": ls.num_packets_rx_bad if ls.num_packets_rx_bad else None,
                    "num_online_nodes": ls.num_online_nodes if ls.num_online_nodes else None,
                    "num_total_nodes": ls.num_total_nodes if ls.num_total_nodes else None,
                }
            elif telem.HasField("power_metrics"):
                variant = "power_metrics"
                pm = telem.power_metrics
                telem_dict = {"ch1_voltage": pm.ch1_voltage, "ch1_current": pm.ch1_current}
            elif telem.HasField("air_quality_metrics"):
                variant = "air_quality_metrics"
                aq = telem.air_quality_metrics
                telem_dict = {"pm25_standard": aq.pm25_standard, "pm10_standard": aq.pm10_standard}

            clean_telem = {k: v for k, v in telem_dict.items() if v is not None}
            res = {variant: clean_telem} if variant else {}
            return DecodedPayload("telemetry", portnum, variant, res, ok_to_mqtt_val, True)

        # 6. TRACEROUTE_APP (70)
        elif portnum == portnums_pb2.PortNum.TRACEROUTE_APP:
            route = mesh_pb2.RouteDiscovery()
            route.ParseFromString(payload_bytes)
            route_hex = [f"!{n:08x}" for n in route.route]
            route_back_hex = [f"!{n:08x}" for n in route.route_back]
            snr_towards = [round(s / 4.0, 2) for s in route.snr_towards]
            snr_back = [round(s / 4.0, 2) for s in route.snr_back]

            res = {
                "route": route_hex,
                "snr_towards": snr_towards,
                "route_back": route_back_hex,
                "snr_back": snr_back,
            }
            return DecodedPayload("traceroute", portnum, None, res, ok_to_mqtt_val, True)

        # 7. NEIGHBORINFO_APP (71)
        elif portnum == portnums_pb2.PortNum.NEIGHBORINFO_APP:
            ninfo = mesh_pb2.NeighborInfo()
            ninfo.ParseFromString(payload_bytes)
            neighbors_list = [
                {"id": f"!{n.node_id:08x}", "snr": round(n.snr / 4.0, 2)}
                for n in ninfo.neighbors
            ]
            res = {
                "broadcast_interval_s": ninfo.node_broadcast_interval_secs,
                "neighbors": neighbors_list,
            }
            return DecodedPayload("neighborinfo", portnum, None, res, ok_to_mqtt_val, True)

        # Resto de tipos no estructurados
        else:
            return DecodedPayload("other", portnum, None, {}, ok_to_mqtt_val, True)

    except Exception as e:
        logger.debug("Error decodificando payload de portnum %d: %s", portnum, e)
        return DecodedPayload("other", portnum, None, {}, ok_to_mqtt_val, True)
