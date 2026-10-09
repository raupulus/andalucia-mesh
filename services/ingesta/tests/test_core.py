"""Pruebas unitarias de los módulos del motor central de ingesta.

Cubre:
- validator.py: Tópicos, normalización de canales y listas blancas.
- crypto.py: Descifrado AES-CTR con clave comunitaria y regla TR-02.
- airtime.py: Tiempo en el aire SFNarrow según tabla Semtech.
- links.py: Enlaces directos y distancias haversine.
- reboot.py: Detección de reinicios por uptime.
- decoder.py: Decodificación de protobufs y bit OK to MQTT.
- dedup.py: Agrupación en ventana de 2s, recepciones tardías y descarte de duplicados.
"""

import asyncio
import base64
from datetime import datetime, timezone
import pytest

from meshtastic.protobuf import config_pb2, mesh_pb2, portnums_pb2, telemetry_pb2

from src.validator import (
    TopicValidator,
    normalize_channel_name,
    validate_gateway_id,
)
from src.crypto import PacketDecryptor, expand_channel_key
from src.airtime import calculate_airtime_ms
from src.links import (
    calculate_haversine_distance_km,
    evaluate_direct_link,
)
from src.reboot import detect_node_reboot
from src.decoder import decode_data_payload, check_ok_to_mqtt
from src.dedup import (
    PacketDeduplicator,
    UnifiedPacket,
    ReceptionItem,
)


def test_normalize_channel_name() -> None:
    """Verifica la normalización canónica de canales sin acentos ni diacríticos."""
    assert normalize_channel_name("Cádiz") == "cadiz"
    assert normalize_channel_name("Jaén") == "jaen"
    assert normalize_channel_name("Málaga") == "malaga"
    assert normalize_channel_name("SFNarrow") == "sfnarrow"
    assert normalize_channel_name("  Almería  ") == "almeria"


def test_validate_gateway_id() -> None:
    """Verifica el formato estricto de identificadores de gateway."""
    assert validate_gateway_id("!1a2b3c4d") == "!1a2b3c4d"
    assert validate_gateway_id("!1A2B3C4D") == "!1a2b3c4d"
    assert validate_gateway_id("!12345678") == "!12345678"

    assert validate_gateway_id("1a2b3c4d") is None  # Sin !
    assert validate_gateway_id("!1a2b3c4") is None   # 7 chars
    assert validate_gateway_id("!1a2b3c4de") is None # 9 chars
    assert validate_gateway_id("!1a2b3czz") is None  # No hex


def test_topic_validator() -> None:
    """Verifica el análisis de tópicos y descarte de canales no autorizados."""
    allowed = ["SFNarrow", "Iberia", "Andalucia", "Cadiz", "Sevilla", "sos"]
    val = TopicValidator(allowed, topic_root="msh/EU_868", topic_prefix="snm")

    # 1. Topic de radio estándar
    res1 = val.parse_topic("msh/EU_868/2/e/Cadiz/!1a2b3c4d", payload_len=50)
    assert res1.topic_type == "envelope"
    assert res1.channel == "Cadiz"
    assert res1.gateway_id == "!1a2b3c4d"

    # Con tilde en el topic (Cádiz -> canal canónico Cadiz)
    res2 = val.parse_topic("msh/EU_868/2/e/Cádiz/!1a2b3c4d", payload_len=50)
    assert res2.channel == "Cadiz"

    # Canal no permitido (Madrid)
    res3 = val.parse_topic("msh/EU_868/2/e/Madrid/!1a2b3c4d", payload_len=50)
    assert res3.topic_type == "unknown"

    # Map report
    res_map = val.parse_topic("msh/EU_868/2/map/", payload_len=80)
    assert res_map.topic_type == "map"

    # Peer federado
    res_peer = val.parse_topic("snm/v1/peer/madrid-mesh/messages", payload_len=200)
    assert res_peer.topic_type == "peer"
    assert res_peer.peer_id == "madrid-mesh"
    assert res_peer.event_type == "messages"

    # Sobrecarga de tamaño (> 1024 B)
    res_huge = val.parse_topic("msh/EU_868/2/e/Cadiz/!1a2b3c4d", payload_len=2048)
    assert res_huge.topic_type == "unknown"


def test_crypto_aes_ctr_roundtrip() -> None:
    """Comprueba el descifrado AES-CTR con nonce Meshtastic y la regla TR-02."""
    from cryptography.hazmat.primitives.ciphers import Cipher, algorithms, modes

    decryptor = PacketDecryptor(channel_key_b64="AQ==")
    key = decryptor.aes_key

    packet_id = 12345678
    from_node = 0xA1B2C3D4

    # Crear Data sintético
    inner_data = mesh_pb2.Data(
        portnum=portnums_pb2.PortNum.TEXT_MESSAGE_APP,
        payload=b"Hola Andalucia",
        bitfield=1,  # OK to MQTT
    )
    serialized_data = inner_data.SerializeToString()

    # Cifrar con el mismo algoritmo
    nonce = (
        packet_id.to_bytes(8, "little")
        + from_node.to_bytes(4, "little")
        + b"\x00\x00\x00\x00"
    )
    cipher = Cipher(algorithms.AES(key), modes.CTR(nonce))
    encryptor = cipher.encryptor()
    encrypted_payload = encryptor.update(serialized_data) + encryptor.finalize()

    # Construir MeshPacket aplicando TR-02
    packet = mesh_pb2.MeshPacket()
    packet.id = packet_id
    setattr(packet, "from", from_node)
    packet.encrypted = encrypted_payload

    # Descifrar
    data_out, status = decryptor.decrypt_packet(packet)
    assert status == "descifrado"
    assert data_out is not None
    assert data_out.portnum == portnums_pb2.PortNum.TEXT_MESSAGE_APP
    assert data_out.payload == b"Hola Andalucia"


def test_airtime_reference_values() -> None:
    """Verifica el cálculo de tiempo en el aire contra los valores exactos de la tabla Semtech."""
    # Tabla Semtech: PL = 30 -> 160.3 ms, PL = 56 -> 231.9 ms, PL = 100 -> 365.1 ms, PL = 253 -> 805.4 ms
    assert calculate_airtime_ms(payload_len=14) == 160.3  # PL = 14 + 16 = 30
    assert calculate_airtime_ms(payload_len=40) == 231.9  # PL = 40 + 16 = 56
    assert calculate_airtime_ms(payload_len=84) == 365.1  # PL = 84 + 16 = 100
    assert calculate_airtime_ms(payload_len=237) == 805.4 # PL = 237 + 16 = 253

    # Map report no consume radio
    assert calculate_airtime_ms(payload_len=100, is_map_report=True) == 0.0


def test_links_haversine_and_direct() -> None:
    """Verifica el cálculo de distancia haversine y la evaluación de enlaces directos."""
    # Distancia Cádiz (36.5298, -6.2926) a Sevilla (37.3891, -5.9845) ~ 99 km
    dist = calculate_haversine_distance_km(36.5298, -6.2926, 37.3891, -5.9845)
    assert 97.0 < dist < 101.0

    # 1. Enlace directo válido (0 saltos, no mqtt)
    node_pos = (36.5298, -6.2926, 10.0)
    gw_pos = (36.5350, -6.2900, 10.0)
    is_dir, d_km, susp = evaluate_direct_link(
        hops=0,
        via_mqtt=False,
        from_id="!11112222",
        gateway_id="!33334444",
        node_coords=node_pos,
        gw_coords=gw_pos,
    )
    assert is_dir
    assert d_km is not None and d_km < 2.0
    assert not susp

    # 2. Paquete propio del gateway -> nunca es enlace
    is_dir_own, _, _ = evaluate_direct_link(
        hops=0, via_mqtt=False, from_id="!11112222", gateway_id="!11112222", node_coords=None, gw_coords=None
    )
    assert not is_dir_own

    # 3. Más de 0 saltos -> no es directo
    is_dir_hops, _, _ = evaluate_direct_link(
        hops=1, via_mqtt=False, from_id="!11112222", gateway_id="!33334444", node_coords=None, gw_coords=None
    )
    assert not is_dir_hops

    # 4. Enlace sospechoso (> 300 km)
    madrid_pos = (40.4168, -3.7038, 10.0)
    _, d_susp, is_susp = evaluate_direct_link(
        hops=0, via_mqtt=False, from_id="!11112222", gateway_id="!33334444", node_coords=node_pos, gw_coords=madrid_pos
    )
    assert is_susp
    assert d_susp is None


def test_reboot_detection() -> None:
    """Verifica el algoritmo de detección de reinicios de nodos."""
    now = 10000.0

    # 1. Sin lectura previa -> no reinicio
    assert not detect_node_reboot(3600, now, None, None)

    # 2. Transcurso normal (3.600 s previo, pasó 1 h (3600 s) -> actual 7.200 s)
    assert not detect_node_reboot(7200, now + 3600, 3600, now)

    # 3. Reinicio claro (previo 86.400 s, pasó 1 h (3600 s), actual 120 s)
    assert detect_node_reboot(120, now + 3600, 86400, now)

    # 4. Deriva dentro de tolerancia (pequeño retraso o drift)
    # Esperado: 3600 + 1000 = 4600. Tolerancia = max(300, 50) = 300. Mínimo = 4300.
    assert not detect_node_reboot(4350, now + 1000, 3600, now)


def test_decoder_ok_to_mqtt_and_text() -> None:
    """Verifica la decodificación de mensajes y el cumplimiento del consentimiento OK to MQTT."""
    # Paquete de tercero sin bit OK to MQTT (bitfield = 0) -> descartado
    data_no_consent = mesh_pb2.Data(
        portnum=portnums_pb2.PortNum.TEXT_MESSAGE_APP,
        payload=b"Mensaje privado",
        bitfield=0,
    )
    res_no = decode_data_payload("!11112222", "!99998888", data_no_consent)
    assert not res_no.is_valid_consent

    # Paquete de tercero con consentimiento (bitfield = 1) -> permitido
    data_with_consent = mesh_pb2.Data(
        portnum=portnums_pb2.PortNum.TEXT_MESSAGE_APP,
        payload="Hola malla Cádiz".encode("utf-8"),
        bitfield=1,
    )
    res_ok = decode_data_payload("!11112222", "!99998888", data_with_consent)
    assert res_ok.is_valid_consent
    assert res_ok.portnum_name == "text"
    assert res_ok.payload_dict["text"] == "Hola malla Cádiz"

    # Paquete propio del gateway sin bitfield -> siempre permitido
    res_gw = decode_data_payload("!99998888", "!99998888", data_no_consent)
    assert res_gw.is_valid_consent

    # Paquete de tercero sin bitfield pero con ignorar_ok_to_mqtt=True -> permitido
    res_ignorado = decode_data_payload(
        "!11112222", "!99998888", data_no_consent, ignorar_ok_to_mqtt=True
    )
    assert res_ignorado.is_valid_consent
    assert res_ignorado.portnum_name == "text"
    assert res_ignorado.payload_dict["text"] == "Mensaje privado"


def test_decoder_nodeinfo_roles() -> None:
    """Verifica que el decodificador de NODEINFO maneja correctamente CLIENT, ROUTER y CLIENT_HIDDEN."""
    roles = [
        (config_pb2.Config.DeviceConfig.Role.CLIENT, "CLIENT"),
        (config_pb2.Config.DeviceConfig.Role.ROUTER, "ROUTER"),
        (config_pb2.Config.DeviceConfig.Role.CLIENT_HIDDEN, "CLIENT_HIDDEN"),
        (config_pb2.Config.DeviceConfig.Role.REPEATER, "REPEATER"),
    ]

    for role_enum, expected_role in roles:
        user = mesh_pb2.User(
            id="!5f3a3a29",
            short_name="Rau0",
            long_name="Raupulus Base",
            hw_model=mesh_pb2.HardwareModel.RPI_PICO2,
            role=role_enum,
        )
        data = mesh_pb2.Data(
            portnum=portnums_pb2.PortNum.NODEINFO_APP,
            payload=user.SerializeToString(),
            bitfield=1,
        )
        res = decode_data_payload("!5f3a3a29", "!gateway1", data)
        assert res.portnum_name == "nodeinfo"
        assert res.payload_dict["role"] == expected_role
        assert res.payload_dict["short_name"] == "Rau0"
        assert res.payload_dict["long_name"] == "Raupulus Base"
        assert res.payload_dict["hw_model"] == "RPI_PICO2"


@pytest.mark.asyncio
async def test_deduplicator_aggregation_and_late() -> None:
    """Verifica la agrupación en ventana de 2s y el tratamiento de recepciones tardías."""
    published_packets: list[UnifiedPacket] = []
    late_receptions: list[tuple[UnifiedPacket, ReceptionItem]] = []

    async def on_win_closed(pkt: UnifiedPacket) -> None:
        published_packets.append(pkt)

    async def on_late(pkt: UnifiedPacket, r: ReceptionItem) -> None:
        late_receptions.append((pkt, r))

    # Deduplicador con ventana corta (0.1s para agilizar test)
    dedup = PacketDeduplicator(
        on_window_closed=on_win_closed,
        on_late_reception=on_late,
        publish_window_s=0.1,
        dedup_window_min=1,
    )

    t0 = datetime.now(timezone.utc)
    packet_template = UnifiedPacket(
        rx_first=t0,
        from_id="!1a2b3c4d",
        to_id="^all",
        packet_id=5555,
        channel="SFNarrow",
        portnum="text",
        portnum_num=1,
        variant=None,
        decrypt_status="claro",
        hop_start=3,
        hops_min=2,
        want_ack=False,
        via_mqtt=False,
        ok_to_mqtt=True,
        size_bytes=40,
        airtime_ms=231.9,
        first_gateway="!gw000001",
        province="ES-CA",
        payload={"text": "Test deduplicador"},
    )

    r1 = ReceptionItem("!gw000001", 6.0, -90, 1, 2, None, None, t0, False, False, None)
    r2 = ReceptionItem("!gw000002", 4.0, -95, 2, 1, None, None, t0, False, False, None)

    # 1. Primera recepción -> NEW_PACKET
    status1 = await dedup.process_reception(packet_template, r1)
    assert status1 == "NEW_PACKET"

    # 2. Misma recepción del mismo gateway -> DUPLICATE_GATEWAY
    status_dup = await dedup.process_reception(packet_template, r1)
    assert status_dup == "DUPLICATE_GATEWAY"

    # 3. Segunda recepción de gateway distinto dentro de la ventana -> RECEPTION_WINDOW
    status2 = await dedup.process_reception(packet_template, r2)
    assert status2 == "RECEPTION_WINDOW"

    # Esperar a que expire la ventana (0.1s)
    await asyncio.sleep(0.15)
    assert len(published_packets) == 1
    assert len(published_packets[0].receptions) == 2
    assert published_packets[0].hops_min == 1  # Mínimo de 2 y 1

    # 4. Recepción tardía (> 0.1s) de un tercer gateway -> RECEPTION_LATE
    r3 = ReceptionItem("!gw000003", 2.0, -100, 3, 0, None, None, t0, False, False, None)
    status3 = await dedup.process_reception(packet_template, r3)
    assert status3 == "RECEPTION_LATE"
    assert len(late_receptions) == 1
    assert len(published_packets) == 1  # No se republica en decoded
