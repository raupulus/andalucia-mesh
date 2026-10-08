"""Estructuras de datos, validación y formateo de mensajes del protocolo chat-ws."""

import json
from datetime import UTC, datetime
from typing import Any


def format_iso_utc(dt: datetime | str | None) -> str:
    """Normaliza y formatea una fecha/hora en formato ISO 8601 UTC ('YYYY-MM-DDTHH:MM:SSZ').

    Args:
        dt: Instancia de datetime o cadena ISO.

    Returns:
        Cadena con la marca temporal en UTC finalizada en 'Z'.
    """
    if dt is None:
        return datetime.now(UTC).strftime("%Y-%m-%dT%H:%M:%SZ")
    if isinstance(dt, str):
        # Si ya es una cadena ISO, asegurar normalización a segundos si contiene microsegundos
        try:
            parsed = datetime.fromisoformat(dt.replace("Z", "+00:00"))
            return parsed.astimezone(UTC).strftime("%Y-%m-%dT%H:%M:%SZ")
        except ValueError:
            return dt
    return dt.astimezone(UTC).strftime("%Y-%m-%dT%H:%M:%SZ")


def build_hello_message(channels: list[str], history_size: int) -> dict[str, Any]:
    """Construye el mensaje de bienvenida 'hello' al conectar un cliente.

    Args:
        channels: Lista de canales admitidos en el sistema.
        history_size: Capacidad del búfer circular de historial en memoria.

    Returns:
        Diccionario serializable a JSON con el saludo del protocolo.
    """
    return {
        "type": "hello",
        "version": 1,
        "channels": channels,
        "history_size": history_size,
    }


def build_subscribed_message(channels: list[str]) -> dict[str, Any]:
    """Construye la confirmación de canales suscritos.

    Args:
        channels: Lista de nombres de canales válidos suscritos.

    Returns:
        Diccionario con la respuesta 'subscribed'.
    """
    return {
        "type": "subscribed",
        "channels": channels,
    }


def build_unsubscribed_message(channels: list[str]) -> dict[str, Any]:
    """Construye la confirmación de canales desuscritos.

    Args:
        channels: Lista de nombres de canales eliminados de la suscripción.

    Returns:
        Diccionario con la respuesta 'unsubscribed'.
    """
    return {
        "type": "unsubscribed",
        "channels": channels,
    }


def build_history_message(channel: str, items: list[dict[str, Any]]) -> dict[str, Any]:
    """Construye el mensaje con el volcado del historial de un canal.

    Args:
        channel: Nombre del canal.
        items: Lista de mensajes históricos ordenados del más antiguo al más reciente.

    Returns:
        Diccionario con el evento 'history'.
    """
    return {
        "type": "history",
        "channel": channel,
        "items": items,
    }


def build_error_message(
    code: str,
    *,
    channels: list[str] | None = None,
    message: str | None = None,
) -> dict[str, Any]:
    """Construye un mensaje de error según el protocolo.

    Args:
        code: Código de error ('unknown_channel', 'invalid_message', 'rate_limited', 'too_many_connections').
        channels: Canales causantes del error cuando aplica.
        message: Explicación textual informativa para el cliente.

    Returns:
        Diccionario con la estructura de error estandarizada.
    """
    payload: dict[str, Any] = {
        "type": "error",
        "code": code,
    }
    if channels is not None:
        payload["channels"] = channels
    if message is not None:
        payload["message"] = message
    return payload


def transform_decoded_packet(
    data: dict[str, Any],
    allowed_channels: set[str],
) -> dict[str, Any] | None:
    """Valida y transforma un paquete del flujo MQTT snm/v1/decoded/text a formato público de chat.

    Filtra estrictamente según las reglas del proyecto (RN-04 y RN-45):
    - Requiere versión v == 1
    - portnum == "text"
    - to == "^all" (difusión estricta; descarta mensajes directos privados)
    - channel en la lista de canales permitidos
    - payload con campo "text" no vacío tras trimado

    Args:
        data: Diccionario del sobre JSON decoded recibido por MQTT.
        allowed_channels: Conjunto de nombres de canales admitidos.

    Returns:
        Diccionario con el mensaje de chat público listo para difusión, o None si se descarta.
    """
    # 1. Validación de esquema y tipo
    if data.get("v") != 1:
        return None
    if data.get("portnum") != "text":
        return None

    # 2. Difusión estricta: nunca mensajes directos ni PKI
    if data.get("to") != "^all":
        return None

    # 3. Canal admitido exacto
    channel = data.get("channel")
    if not isinstance(channel, str) or channel not in allowed_channels:
        return None

    # 4. Contenido del texto
    payload = data.get("payload")
    if not isinstance(payload, dict):
        return None

    text_content = payload.get("text")
    if not isinstance(text_content, str) or not text_content.strip():
        return None

    # 5. Extracción de metadatos de remitente (sin coordenadas ni claves)
    sender_id = str(data.get("from") or "")
    from_node_data = data.get("from_node")
    short_name: str | None = None
    long_name: str | None = None

    if isinstance(from_node_data, dict):
        short_name = from_node_data.get("short")
        long_name = from_node_data.get("long")

    # 6. Cálculo de saltos y pasarelas
    hops_min = data.get("hops_min")
    hop_start = data.get("hop_start")
    hops = hops_min if hops_min is not None else (hop_start if hop_start is not None else 0)

    receptions = data.get("receptions")
    gateways_count = 1
    if isinstance(receptions, list) and len(receptions) > 0:
        # Contar pasarelas únicas que recibieron la trama
        unique_gws = {r.get("gateway") for r in receptions if isinstance(r, dict) and r.get("gateway")}
        gateways_count = len(unique_gws) if unique_gws else len(receptions)

    packet_id = data.get("packet_id", 0)
    rx_first = data.get("rx_first")

    return {
        "type": "message",
        "channel": channel,
        "id": int(packet_id),
        "from": {
            "id": sender_id,
            "short": short_name,
            "long": long_name,
        },
        "text": text_content,
        "reply_id": payload.get("reply_id"),
        "emoji": bool(payload.get("emoji", False)),
        "at": format_iso_utc(rx_first),
        "hops": int(hops),
        "gateways": int(gateways_count),
    }


def parse_client_message(raw_data: str | bytes) -> tuple[dict[str, Any] | None, str | None]:
    """Parsea y valida la estructura básica de un mensaje entrante de un cliente WebSocket.

    Args:
        raw_data: Cadena de texto o bytes recibidos por la conexión.

    Returns:
        Tupla (datos_parseados, motivo_error). Si hay error, datos_parseados es None.
    """
    if len(raw_data) > 1024:
        return None, "El tamaño del mensaje supera el límite máximo de 1024 bytes"

    if isinstance(raw_data, bytes):
        try:
            raw_text = raw_data.decode("utf-8")
        except UnicodeDecodeError:
            return None, "Codificación no válida (se requiere UTF-8)"
    else:
        raw_text = raw_data

    try:
        data = json.loads(raw_text)
    except json.JSONDecodeError:
        return None, "JSON inválido o malformado"

    if not isinstance(data, dict):
        return None, "El cuerpo del mensaje debe ser un objeto JSON"

    msg_type = data.get("type")
    if not isinstance(msg_type, str) or msg_type not in ("subscribe", "unsubscribe"):
        return None, f"Tipo de mensaje '{msg_type}' no soportado (solo 'subscribe' o 'unsubscribe')"

    channels = data.get("channels")
    if not isinstance(channels, list):
        return None, "El campo 'channels' es obligatorio y debe ser una lista de cadenas"

    for ch in channels:
        if not isinstance(ch, str):
            return None, "Todos los elementos de 'channels' deben ser cadenas de texto"

    return data, None
