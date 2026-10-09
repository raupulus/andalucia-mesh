"""Pruebas unitarias del despachador y decodificador MQTT de adaptador-potato.

Verifica la traducción de paquetes protobuf a los contratos esperados por PotatoMesh,
incluyendo la conversión correcta de roles (CLIENT=0, ROUTER=2, CLIENT_HIDDEN=8),
campos snake_case y camelCase de telemetría y metadatos de nodos.
"""

from typing import Any
import pytest
from meshtastic.protobuf import config_pb2, mesh_pb2, mqtt_pb2, telemetry_pb2

from src.config import Config
from src.dedup import Deduplicator
from src.mqtt import MqttProcessor
from src.sender import PotatoSender


class FakeSender(PotatoSender):
    """Sender mock para capturar las llamadas enqueue sin realizar peticiones HTTP."""

    def __init__(self) -> None:
        super().__init__(base_url="http://fake:41447", api_token="test_token")
        self.enqueued_items: list[tuple[str, Any, int]] = []

    def enqueue(self, endpoint: str, payload: dict[str, Any], priority: int = 1) -> bool:
        self.enqueued_items.append((endpoint, payload, priority))
        return True


@pytest.fixture
def processor() -> tuple[MqttProcessor, FakeSender]:
    cfg = Config()
    dedup = Deduplicator()
    sender = FakeSender()
    proc = MqttProcessor(cfg, dedup, sender)
    return proc, sender


def test_dispatch_nodeinfo_roles(processor: tuple[MqttProcessor, FakeSender]) -> None:
    """Verifica que el rol se decodifica correctamente para CLIENT (0), ROUTER (2) y CLIENT_HIDDEN (8)."""
    proc, sender = processor

    test_roles = [
        (config_pb2.Config.DeviceConfig.Role.CLIENT, "CLIENT"),
        (config_pb2.Config.DeviceConfig.Role.ROUTER, "ROUTER"),
        (config_pb2.Config.DeviceConfig.Role.CLIENT_HIDDEN, "CLIENT_HIDDEN"),
        (config_pb2.Config.DeviceConfig.Role.REPEATER, "REPEATER"),
    ]

    for role_enum, expected_role in test_roles:
        sender.enqueued_items.clear()

        user = mesh_pb2.User(
            id="!5f3a3a29",
            short_name="Rau0",
            long_name="Raupulus Base",
            hw_model=mesh_pb2.HardwareModel.RPI_PICO2,
            role=role_enum,
        )
        data = mesh_pb2.Data(
            portnum=4,  # NODEINFO_APP
            payload=user.SerializeToString(),
        )
        packet = mesh_pb2.MeshPacket(
            id=12345,
            rx_snr=8.5,
        )
        setattr(packet, "from", 0x5F3A3A29)

        proc._dispatch_payload(
            packet=packet,
            data=data,
            channel_index=0,
            rx_time=1700000000,
            node_id_str="!5f3a3a29",
            from_node=0x5F3A3A29,
        )

        assert len(sender.enqueued_items) == 1
        endpoint, payload, priority = sender.enqueued_items[0]
        assert endpoint == "nodes"
        assert priority == 0
        node_entry = payload["!5f3a3a29"]
        assert node_entry["num"] == 0x5F3A3A29
        assert node_entry["user"]["role"] == expected_role
        assert node_entry["user"]["shortName"] == "Rau0"
        assert node_entry["user"]["longName"] == "Raupulus Base"
        assert node_entry["user"]["hwModel"] == "RPI_PICO2"


def test_dispatch_telemetry_metrics(processor: tuple[MqttProcessor, FakeSender]) -> None:
    """Verifica que la telemetría incluye campos en camelCase y snake_case para PotatoMesh."""
    proc, sender = processor

    tel = telemetry_pb2.Telemetry(
        time=1700000000,
        device_metrics=telemetry_pb2.DeviceMetrics(
            battery_level=101,
            voltage=4.2,
            channel_utilization=0.4,
            air_util_tx=0.01,
            uptime_seconds=3600,
        ),
    )
    data = mesh_pb2.Data(
        portnum=67,  # TELEMETRY_APP
        payload=tel.SerializeToString(),
    )
    packet = mesh_pb2.MeshPacket(id=9999)
    setattr(packet, "from", 0x5F3A3A29)

    proc._dispatch_payload(
        packet=packet,
        data=data,
        channel_index=0,
        rx_time=1700000000,
        node_id_str="!5f3a3a29",
        from_node=0x5F3A3A29,
    )

    assert len(sender.enqueued_items) == 1
    endpoint, payload, priority = sender.enqueued_items[0]
    assert endpoint == "telemetry"
    assert payload["node_id"] == "!5f3a3a29"
    assert payload["node_num"] == 0x5F3A3A29

    # Verificar presencia de snake_case para update_node_from_telemetry
    assert "device_metrics" in payload
    assert payload["device_metrics"]["battery_level"] == 101
    assert payload["device_metrics"]["voltage"] == pytest.approx(4.2, abs=1e-3)
    assert payload["device_metrics"]["uptime_seconds"] == 3600

    # Verificar presencia de camelCase para telemetry.deviceMetrics
    assert "deviceMetrics" in payload["telemetry"]
    assert payload["telemetry"]["deviceMetrics"]["batteryLevel"] == 101


def test_dispatch_map_report_role(processor: tuple[MqttProcessor, FakeSender]) -> None:
    """Verifica que MapReport decodifica roles correctamente sin AttributeError."""
    proc, sender = processor

    mr = mqtt_pb2.MapReport(
        short_name="Rep1",
        long_name="Repetidor Cadiz",
        role=config_pb2.Config.DeviceConfig.Role.ROUTER,
        latitude_i=365000000,
        longitude_i=-62000000,
    )
    data = mesh_pb2.Data(
        portnum=73,  # MAP_REPORT_APP
        payload=mr.SerializeToString(),
    )
    packet = mesh_pb2.MeshPacket(id=7777)
    setattr(packet, "from", 0x11223344)

    proc._dispatch_payload(
        packet=packet,
        data=data,
        channel_index=0,
        rx_time=1700000000,
        node_id_str="!11223344",
        from_node=0x11223344,
    )

    assert len(sender.enqueued_items) == 2  # nodes y positions
    nodes_item = next(it for it in sender.enqueued_items if it[0] == "nodes")
    node_entry = nodes_item[1]["!11223344"]
    assert node_entry["num"] == 0x11223344
    assert node_entry["user"]["role"] == "ROUTER"
    assert node_entry["user"]["shortName"] == "Rep1"
