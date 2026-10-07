"""Módulo criptográfico para descifrado AES-CTR de paquetes Meshtastic.

Implementa la expansión de clave por defecto (AQ==) y el descifrado
de cargas de datos con el nonce canónico de 16 bytes.
"""

from __future__ import annotations

import base64
import struct

from cryptography.hazmat.backends import default_backend
from cryptography.hazmat.primitives.ciphers import Cipher, algorithms, modes
from meshtastic.protobuf import mesh_pb2

# Constante de clave por defecto del firmware Meshtastic (16 bytes)
DEFAULT_PSK: bytes = bytes(
    [
        0xD4,
        0xF1,
        0xBB,
        0x3A,
        0x69,
        0xEF,
        0xB9,
        0x90,
        0x52,
        0x68,
        0x08,
        0x25,
        0xB9,
        0xA1,
        0xA8,
        0x01,
    ]
)


def expand_key(key_b64: str) -> bytes:
    """Expande una clave en Base64 según la especificación del firmware.

    Args:
        key_b64: Clave codificada en Base64 (ej. 'AQ==').

    Returns:
        Bytes correspondientes a la clave simétrica AES (16 o 32 bytes),
        o bytes vacíos si no hay cifrado.

    Raises:
        ValueError: Si la clave no tiene un formato o longitud soportada.
    """
    raw_key = base64.b64decode(key_b64.strip(), validate=True)
    length = len(raw_key)

    if length == 0 or (length == 1 and raw_key[0] == 0):
        return b""

    if length == 1:
        val = raw_key[0]
        # Incrementar el último byte de DEFAULT_PSK en (val - 1)
        last_byte = (DEFAULT_PSK[-1] + (val - 1)) & 0xFF
        return DEFAULT_PSK[:-1] + bytes([last_byte])

    if length in (16, 32):
        return raw_key

    raise ValueError(f"Longitud de clave no soportada: {length} bytes")


def create_nonce(packet_id: int, from_node: int) -> bytes:
    """Construye el nonce canónico de 16 bytes para AES-CTR.

    Estructura:
    - ID de paquete: entero de 64 bits little-endian (8 bytes).
    - Nodo emisor: entero de 32 bits little-endian (4 bytes).
    - Relleno a ceros: 4 bytes.

    Args:
        packet_id: Identificador numérico del paquete.
        from_node: ID numérico del nodo emisor.

    Returns:
        Nonce empaquetado de 16 bytes.
    """
    return struct.pack("<QI4x", packet_id & 0xFFFFFFFFFFFFFFFF, from_node & 0xFFFFFFFF)


def decrypt_packet(
    encrypted_bytes: bytes, packet_id: int, from_node: int, key: bytes
) -> tuple[mesh_pb2.Data | None, str]:
    """Descifra una carga binaria AES-CTR y deserializa el mensaje protobuf Data.

    Args:
        encrypted_bytes: Carga cifrada del paquete.
        packet_id: ID del paquete para el nonce.
        from_node: ID del nodo emisor para el nonce.
        key: Clave simétrica AES.

    Returns:
        Tupla (objeto Data o None, estado descriptivo 'ok' o 'cifrado_desconocido').
    """
    if not encrypted_bytes or not key:
        return None, "cifrado_desconocido"

    nonce = create_nonce(packet_id, from_node)
    cipher = Cipher(algorithms.AES(key), modes.CTR(nonce), backend=default_backend())
    decryptor = cipher.decryptor()

    try:
        decrypted = decryptor.update(encrypted_bytes) + decryptor.finalize()
        data = mesh_pb2.Data()
        data.ParseFromString(decrypted)

        # Si el portnum es UNKNOWN_APP (0), se considera descifrado fallido
        if data.portnum == 0:
            return None, "cifrado_desconocido"

        return data, "ok"
    except Exception:
        return None, "cifrado_desconocido"
