"""Pruebas unitarias de configuración, modelos y metadatos de reglas."""

from detector.config import Settings
from detector.modelos import PaqueteDecodificado
from detector.reglas_meta import METADATOS_REGLAS


def test_settings_default_values() -> None:
    """Verifica que las configuraciones por defecto coinciden con la especificación."""
    settings = Settings()

    assert settings.tz == "Europe/Madrid"
    assert settings.mqtt_port == 1884
    assert settings.db_port == 5432
    assert "ROUTER" in settings.parsed_infra_roles
    assert "REPEATER" in settings.parsed_infra_roles
    assert settings.saturacion_peso_routers == 0.6
    assert settings.saturacion_peso_clientes == 0.4
    assert settings.alertas_socket_gid == 10500


def test_settings_resolve_yaml_paths() -> None:
    """Verifica que se localizan los archivos YAML canónicos."""
    settings = Settings()

    clasif = settings.resolve_clasificacion_path()
    assert clasif.is_file(), f"No se encontró clasificacion.yaml en {clasif}"

    reglas = settings.resolve_reglas_path()
    assert reglas.is_file(), f"No se encontró reglas.yaml en {reglas}"


def test_paquete_decodificado_parsing() -> None:
    """Valida el parsing de un paquete decodificado representativo del contrato decoded."""
    raw = {
        "v": 1,
        "packet_id": 3735928559,
        "from": "!a1b2c3d4",
        "from_node": {
            "short": "CAD1",
            "long": "Repetidor Sierra Cádiz",
            "role": "ROUTER",
            "hw": "HELTEC_V3",
            "is_gateway": False,
            "province": "ES-CA",
        },
        "to": "^all",
        "portnum": "telemetry",
        "channel": "SFNarrow",
        "rx_first": "2026-10-01T15:00:00Z",
        "hop_start": 3,
        "hops_min": 1,
        "via_mqtt": False,
        "ok_to_mqtt": True,
        "airtime_ms": 61.4,
        "receptions": [
            {"gateway": "!0badc0de", "snr": 6.5, "rssi": -98, "hops": 1, "at": "2026-10-01T15:00:00Z"}
        ],
        "payload": {
            "device_metrics": {
                "battery_level": 37,
                "voltage": 3.61,
                "channel_utilization": 18.2,
                "air_util_tx": 2.1,
                "uptime_seconds": 84,
            }
        },
    }

    pkt = PaqueteDecodificado.model_validate(raw)
    assert pkt.from_node_id == "!a1b2c3d4"
    assert pkt.from_node is not None
    assert pkt.from_node.short == "CAD1"
    assert pkt.from_node.role == "ROUTER"
    assert pkt.portnum == "telemetry"
    assert len(pkt.receptions) == 1
    assert pkt.receptions[0].gateway == "!0badc0de"
    assert pkt.payload["device_metrics"]["uptime_seconds"] == 84


def test_catalogo_reglas_meta() -> None:
    """Verifica que las 7 reglas MVP y las 25 de ampliación están catalogadas."""
    assert len(METADATOS_REGLAS) == 32

    mvp_rules = [r for r, m in METADATOS_REGLAS.items() if m["fase"] == "mvp"]
    assert len(mvp_rules) == 7
    assert "reboot-loop" in mvp_rules
    assert "battery-low" in mvp_rules
    assert "infra-silent" in mvp_rules
    assert "gateway-offline" in mvp_rules
    assert "flood" in mvp_rules
    assert "rafaga-masiva" in mvp_rules
    assert "hops-high" in mvp_rules

    ampliacion_rules = [r for r, m in METADATOS_REGLAS.items() if m["fase"] == "ampliacion"]
    assert len(ampliacion_rules) == 25

