"""Pruebas unitarias para el protocolo de mensajería y transformación de paquetes."""

from chat_ws.protocol import (
    build_error_message,
    build_hello_message,
    build_history_message,
    build_subscribed_message,
    build_unsubscribed_message,
    format_iso_utc,
    parse_client_message,
    transform_decoded_packet,
)


def test_protocol_builders() -> None:
    """Verifica los generadores de mensajes del servidor."""
    hello = build_hello_message(["Canal1"], 20)
    assert hello == {
        "type": "hello",
        "version": 1,
        "channels": ["Canal1"],
        "history_size": 20,
    }

    sub = build_subscribed_message(["Cadiz", "sos"])
    assert sub == {"type": "subscribed", "channels": ["Cadiz", "sos"]}

    unsub = build_unsubscribed_message(["sos"])
    assert unsub == {"type": "unsubscribed", "channels": ["sos"]}

    hist = build_history_message("Cadiz", [{"id": 1}])
    assert hist == {"type": "history", "channel": "Cadiz", "items": [{"id": 1}]}

    err = build_error_message("unknown_channel", channels=["Madrid"])
    assert err == {"type": "error", "code": "unknown_channel", "channels": ["Madrid"]}


def test_transform_decoded_packet_valid() -> None:
    """Verifica la transformación correcta de un paquete válido de snm/v1/decoded/text."""
    allowed = {"SFNarrow", "Cadiz"}
    raw_packet = {
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
        "portnum": "text",
        "channel": "Cadiz",
        "rx_first": "2026-10-04T12:00:00.120Z",
        "hop_start": 3,
        "hops_min": 1,
        "via_mqtt": False,
        "ok_to_mqtt": True,
        "airtime_ms": 231.9,
        "receptions": [
            {"gateway": "!0badc0de", "snr": 6.5, "rssi": -98, "hops": 1, "at": "2026-10-04T12:00:00.120Z"},
            {"gateway": "!0c0ffee0", "snr": -4.25, "rssi": -117, "hops": 2, "at": "2026-10-04T12:00:01.480Z"},
        ],
        "payload": {
            "text": "Buenas desde la sierra",
            "reply_id": None,
            "emoji": False,
        },
    }

    result = transform_decoded_packet(raw_packet, allowed)
    assert result is not None
    assert result["type"] == "message"
    assert result["channel"] == "Cadiz"
    assert result["id"] == 3735928559
    assert result["from"] == {
        "id": "!a1b2c3d4",
        "short": "CAD1",
        "long": "Repetidor Sierra Cádiz",
    }
    assert result["text"] == "Buenas desde la sierra"
    assert result["reply_id"] is None
    assert result["emoji"] is False
    assert result["at"] == "2026-10-04T12:00:00Z"
    assert result["hops"] == 1
    assert result["gateways"] == 2


def test_transform_decoded_packet_discard_rules() -> None:
    """Verifica las reglas de descarte estricto (RN-04 y RN-45)."""
    allowed = {"SFNarrow", "Cadiz"}

    base = {
        "v": 1,
        "packet_id": 1234,
        "from": "!11223344",
        "to": "^all",
        "portnum": "text",
        "channel": "Cadiz",
        "rx_first": "2026-10-04T12:00:00Z",
        "payload": {"text": "Hola"},
    }

    # 1. Mensaje directo privado (to != ^all) -> Descartado
    direct = dict(base)
    direct["to"] = "!99887766"
    assert transform_decoded_packet(direct, allowed) is None

    # 2. Portnum distinto de "text" -> Descartado
    telemetry = dict(base)
    telemetry["portnum"] = "telemetry"
    assert transform_decoded_packet(telemetry, allowed) is None

    # 3. Canal fuera de lista permitida -> Descartado
    unknown_ch = dict(base)
    unknown_ch["channel"] = "Madrid"
    assert transform_decoded_packet(unknown_ch, allowed) is None

    # 4. Texto vacío o en blanco -> Descartado
    empty_text = dict(base)
    empty_text["payload"] = {"text": "   "}
    assert transform_decoded_packet(empty_text, allowed) is None

    # 5. Versión de esquema distinta de 1 -> Descartado
    bad_v = dict(base)
    bad_v["v"] = 2
    assert transform_decoded_packet(bad_v, allowed) is None


def test_parse_client_message() -> None:
    """Verifica el parseo y validación de mensajes recibidos de clientes."""
    # Válido subscribe
    data, err = parse_client_message('{"type": "subscribe", "channels": ["Cadiz", "sos"]}')
    assert err is None
    assert data == {"type": "subscribe", "channels": ["Cadiz", "sos"]}

    # Válido unsubscribe
    data, err = parse_client_message('{"type": "unsubscribe", "channels": ["sos"]}')
    assert err is None
    assert data == {"type": "unsubscribe", "channels": ["sos"]}

    # Supera 1024 bytes
    huge_msg = '{"type": "subscribe", "channels": [' + ','.join([f'"{i}"' for i in range(300)]) + ']}'
    data, err = parse_client_message(huge_msg)
    assert data is None
    assert "supera el límite" in str(err)

    # JSON malformado
    data, err = parse_client_message("{invalid_json")
    assert data is None
    assert "JSON inválido" in str(err)

    # Tipo no soportado
    data, err = parse_client_message('{"type": "publish", "channels": ["sos"]}')
    assert data is None
    assert "no soportado" in str(err)

    # Channels no es lista
    data, err = parse_client_message('{"type": "subscribe", "channels": "Cadiz"}')
    assert data is None
    assert "debe ser una lista" in str(err)


def test_format_iso_utc() -> None:
    """Verifica la normalización de cadenas de tiempo a ISO UTC."""
    assert format_iso_utc("2026-10-04T12:00:00.123456Z") == "2026-10-04T12:00:00Z"
    assert format_iso_utc(None).endswith("Z")
