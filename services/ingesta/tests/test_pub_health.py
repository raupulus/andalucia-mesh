"""Pruebas unitarias para el publicador de flujo decoded y el servidor de salud."""

from datetime import datetime, timezone
import pytest

from src.dedup import UnifiedPacket, ReceptionItem
from src.registry import NodeRegistry
from src.publisher import DecodedPublisher
from src.health import HealthReporter


def test_format_decoded_message() -> None:
    """Verifica que el formateo del mensaje 'decoded' coincida exactamente con la especificación."""
    registry = NodeRegistry()
    now = datetime(2026, 10, 1, 15, 0, 0, 120000, tzinfo=timezone.utc)

    # Registrar nodo emisor con metadatos
    node = registry.get_or_create_node("!a1b2c3d4", now)
    node.short_name = "CAD1"
    node.long_name = "Repetidor Sierra Cádiz"
    node.role = "ROUTER"
    node.hw_model = "HELTEC_V3"
    node.province = "ES-CA"
    node.is_gateway = False

    publisher = DecodedPublisher(mqtt_client=None, registry=registry, topic_prefix="snm")

    r1 = ReceptionItem(
        gateway_id="!0badc0de",
        snr=6.5,
        rssi=-98,
        hop_limit=2,
        hops=1,
        relay_node=None,
        gw_rx_time=None,
        at=now,
        own=False,
        direct=False,
        distance_km=None,
    )

    packet = UnifiedPacket(
        rx_first=now,
        from_id="!a1b2c3d4",
        to_id="^all",
        packet_id=3735928559,
        channel="SFNarrow",
        portnum="telemetry",
        portnum_num=67,
        variant="device_metrics",
        decrypt_status="descifrado",
        hop_start=3,
        hops_min=1,
        want_ack=False,
        via_mqtt=False,
        ok_to_mqtt=True,
        size_bytes=64,
        airtime_ms=231.9,
        first_gateway="!0badc0de",
        province="ES-CA",
        payload={
            "device_metrics": {
                "battery_level": 37,
                "voltage": 3.61,
                "channel_utilization": 18.2,
                "air_util_tx": 2.1,
                "uptime_seconds": 84,
            }
        },
        receptions=[r1],
    )

    msg = publisher.format_decoded_message(packet)

    # Validar campos canónicos de 06-decoded-stream.md
    assert msg["v"] == 1
    assert msg["packet_id"] == 3735928559
    assert msg["from"] == "!a1b2c3d4"
    assert msg["from_node"] == {
        "short": "CAD1",
        "long": "Repetidor Sierra Cádiz",
        "role": "ROUTER",
        "hw": "HELTEC_V3",
        "is_gateway": False,
        "province": "ES-CA",
    }
    assert msg["to"] == "^all"
    assert msg["portnum"] == "telemetry"
    assert msg["channel"] == "SFNarrow"
    assert msg["rx_first"] == "2026-10-01T15:00:00.120000Z"
    assert msg["hop_start"] == 3
    assert msg["hops_min"] == 1
    assert msg["via_mqtt"] is False
    assert msg["ok_to_mqtt"] is True
    assert msg["airtime_ms"] == 231.9
    assert len(msg["receptions"]) == 1
    assert msg["receptions"][0] == {
        "gateway": "!0badc0de",
        "snr": 6.5,
        "rssi": -98,
        "hops": 1,
        "at": "2026-10-01T15:00:00.120000Z",
    }
    assert msg["payload"]["device_metrics"]["battery_level"] == 37


def test_health_reporter_states() -> None:
    """Verifica la generación de la respuesta de salud y los códigos de estado HTTP."""
    reporter = HealthReporter()

    # Estado inicial: MQTT desconectado -> status 503
    data_init, code_init = reporter.generate_health_dict()
    assert code_init == 503
    assert not data_init["ok"]
    assert data_init["mqtt"] == "desconectado"
    assert data_init["base_datos"] == "conectada"

    # Conectar MQTT -> status 200
    reporter.mqtt_connected = True
    reporter.record_incoming_message()
    data_ok, code_ok = reporter.generate_health_dict()
    assert code_ok == 200
    assert data_ok["ok"]
    assert data_ok["mqtt"] == "conectado"
    assert data_ok["contadores"]["mensajes_recibidos"] == 1
    assert data_ok["segundos_desde_ultimo"] is not None
    assert data_ok["segundos_desde_ultimo"] <= 1

    # Fallo de base de datos -> status 503
    reporter.db_status = "error"
    data_err, code_err = reporter.generate_health_dict()
    assert code_err == 503
    assert not data_err["ok"]
    assert data_err["base_datos"] == "error"


def test_discard_tracker_and_health_integration() -> None:
    """Verifica el funcionamiento del rastreador ligero de descartes y su integración en /health."""
    from src.discard_logger import DiscardTracker

    tracker = DiscardTracker(rate_limit_seconds=10.0, ring_buffer_size=5)

    # Registrar varios descartes de distintos motivos
    tracker.record_discard(
        reason="cifrado_desconocido",
        topic="msh/EU_868/2/e/SFNarrow/!1531b526",
        from_id="!12345678",
        gateway_id="!1531b526",
        detail="enc_len=48",
    )
    tracker.record_discard(
        reason="cifrado_desconocido",
        topic="msh/EU_868/2/e/SFNarrow/!1531b526",
        from_id="!12345678",
        gateway_id="!1531b526",
        detail="enc_len=48",
    )
    tracker.record_discard(
        reason="posicion_sin_coords",
        topic="msh/EU_868/2/e/SFNarrow/!5f3a3a29",
        from_id="!8fa78924",
        gateway_id="!5f3a3a29",
        detail="keys=['time', 'precision_bits']",
    )

    summary = tracker.get_summary()
    assert summary["total_acumulado"] == 3
    assert summary["por_motivo"]["cifrado_desconocido"] == 2
    assert summary["por_motivo"]["posicion_sin_coords"] == 1
    assert len(summary["ultimos_50"]) == 3
    assert summary["ultimos_50"][0]["reason"] == "posicion_sin_coords"

    # Verificar integración con HealthReporter
    reporter = HealthReporter()
    reporter.mqtt_connected = True
    reporter.discard_tracker = tracker

    data, code = reporter.generate_health_dict()
    assert code == 200
    assert "descartes" in data
    assert data["descartes"]["total_acumulado"] == 3
    assert data["descartes"]["por_motivo"]["cifrado_desconocido"] == 2
