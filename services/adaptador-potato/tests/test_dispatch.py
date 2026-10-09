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
            channel_name="SFNarrow",
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
        channel_name="SFNarrow",
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
        channel_name="SFNarrow",
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


def test_dispatch_direct_message_filtering(processor: tuple[MqttProcessor, FakeSender]) -> None:
    """Verifica que el chat directo se descarta pero los traceroutes y vecinos directos se admiten."""
    proc, sender = processor

    # 1. Chat directo (to != 0xFFFFFFFF) -> descartado para proteger privacidad
    sender.enqueued_items.clear()
    envelope_direct_chat = mqtt_pb2.ServiceEnvelope(
        channel_id="SFNarrow",
        gateway_id="!11223344",
    )
    pkt_chat = envelope_direct_chat.packet
    pkt_chat.id = 501
    setattr(pkt_chat, "from", 0x11223344)
    pkt_chat.to = 0x22334455  # Mensaje directo privado
    pkt_chat.decoded.portnum = 1  # TEXT_MESSAGE_APP
    pkt_chat.decoded.payload = b"Mensaje secreto entre dos usuarios"

    proc.process_message("msh/EU_868/2/e/SFNarrow/!11223344", envelope_direct_chat.SerializeToString())
    assert len(sender.enqueued_items) == 0, "Los mensajes de texto directos no deben llegar a PotatoMesh"

    # 2. Chat de difusión (to == 0xFFFFFFFF) -> encolado
    pkt_chat.id = 502
    pkt_chat.to = 0xFFFFFFFF
    pkt_chat.decoded.payload = b"Mensaje publico en canal"

    proc.process_message("msh/EU_868/2/e/SFNarrow/!11223344", envelope_direct_chat.SerializeToString())
    assert len(sender.enqueued_items) == 1
    assert sender.enqueued_items[0][0] == "messages"
    assert sender.enqueued_items[0][1]["channel_name"] == "SFNarrow"

    # 3. Traceroute directo (to != 0xFFFFFFFF) -> admitido
    sender.enqueued_items.clear()
    envelope_trace = mqtt_pb2.ServiceEnvelope(
        channel_id="SFNarrow",
        gateway_id="!11223344",
    )
    pkt_trace = envelope_trace.packet
    pkt_trace.id = 601
    setattr(pkt_trace, "from", 0x11223344)
    pkt_trace.to = 0x22334455  # Destino específico de la ruta
    pkt_trace.decoded.portnum = 70  # TRACEROUTE_APP
    route = mesh_pb2.RouteDiscovery(route=[0x33445566])
    pkt_trace.decoded.payload = route.SerializeToString()

    proc.process_message("msh/EU_868/2/e/SFNarrow/!11223344", envelope_trace.SerializeToString())
    assert len(sender.enqueued_items) == 1
    assert sender.enqueued_items[0][0] == "traces"
    assert sender.enqueued_items[0][1]["to"] == 0x22334455

    # 4. NeighborInfo con to=1 -> admitido
    sender.enqueued_items.clear()
    envelope_neighbor = mqtt_pb2.ServiceEnvelope(
        channel_id="SFNarrow",
        gateway_id="!11223344",
    )
    pkt_neighbor = envelope_neighbor.packet
    pkt_neighbor.id = 701
    setattr(pkt_neighbor, "from", 0x11223344)
    pkt_neighbor.to = 1  # Destino habitual de NeighborInfo
    pkt_neighbor.decoded.portnum = 71  # NEIGHBORINFO_APP
    n_info = mesh_pb2.NeighborInfo()
    n = n_info.neighbors.add()
    n.node_id = 0x99887766
    n.snr = 9.25
    pkt_neighbor.decoded.payload = n_info.SerializeToString()

    proc.process_message("msh/EU_868/2/e/SFNarrow/!11223344", envelope_neighbor.SerializeToString())
    assert len(sender.enqueued_items) == 1
    assert sender.enqueued_items[0][0] == "neighbors"
    assert sender.enqueued_items[0][1]["neighbors"][0]["node_id"] == "!99887766"


def test_dispatch_raw_map_report(processor: tuple[MqttProcessor, FakeSender]) -> None:
    """Verifica que un MapReport publicado directamente en /2/map/ se procesa y encola."""
    proc, sender = processor
    sender.enqueued_items.clear()

    mr = mqtt_pb2.MapReport(
        short_name="Rau0",
        long_name="Raupulus Base",
        role=config_pb2.Config.DeviceConfig.Role.CLIENT,
        hw_model=mesh_pb2.HardwareModel.RPI_PICO2,
        latitude_i=367362048,
        longitude_i=-64323584,
        altitude=54,
    )

    proc.process_message("msh/EU_868/2/map/!5f3a3a29", mr.SerializeToString())
    assert len(sender.enqueued_items) == 2  # nodes y positions

    nodes_item = next(it for it in sender.enqueued_items if it[0] == "nodes")
    node_entry = nodes_item[1]["!5f3a3a29"]
    assert node_entry["user"]["shortName"] == "Rau0"
    assert node_entry["user"]["longName"] == "Raupulus Base"
    assert node_entry["user"]["role"] == "CLIENT"
    assert node_entry["user"]["hwModel"] == "RPI_PICO2"

    pos_item = next(it for it in sender.enqueued_items if it[0] == "positions")
    pos_entry = pos_item[1]
    assert pos_entry["node_id"] == "!5f3a3a29"
    assert pos_entry["from"] == "!5f3a3a29"
    assert pos_entry["latitude"] == pytest.approx(36.7362, abs=1e-3)
    assert pos_entry["longitude"] == pytest.approx(-6.4323, abs=1e-3)
    assert pos_entry["altitude"] == 54


