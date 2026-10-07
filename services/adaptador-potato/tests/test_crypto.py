"""Tests unitarios para el módulo crypto de adaptador-potato."""

from __future__ import annotations

import base64
import struct

import pytest
from cryptography.hazmat.backends import default_backend
from cryptography.hazmat.primitives.ciphers import Cipher, algorithms, modes
from meshtastic.protobuf import mesh_pb2

from src.crypto import (
    DEFAULT_PSK,
    create_nonce,
    decrypt_packet,
    expand_key,
)


def test_expand_key_default() -> None:
    """Verifica que la clave por defecto AQ== produzca la constante DEFAULT_PSK oficial."""
    key = expand_key("AQ==")
    assert len(key) == 16
    assert key == DEFAULT_PSK
    assert key == bytes([
        0xD4, 0xF1, 0xBB, 0x3A, 0x20, 0x29, 0x07, 0x59,
        0xF0, 0xBC, 0xFF, 0xAB, 0xCF, 0x4E, 0x69, 0x01,
    ])


def test_expand_key_empty_or_zero() -> None:
    """Verifica claves sin cifrado (longitud 0 o byte cero)."""
    assert expand_key("") == b""
    # 'AA==' es b'\x00'
    assert expand_key("AA==") == b""


def test_expand_key_custom_lengths() -> None:
    """Verifica claves explícitas de 16 y 32 bytes."""
    raw16 = b"1234567890123456"
    b64_16 = base64.b64encode(raw16).decode("ascii")
    assert expand_key(b64_16) == raw16

    raw32 = b"12345678901234567890123456789012"
    b64_32 = base64.b64encode(raw32).decode("ascii")
    assert expand_key(b64_32) == raw32


def test_expand_key_invalid() -> None:
    """Verifica que longitudes extrañas lancen ValueError."""
    raw5 = b"12345"
    b64_5 = base64.b64encode(raw5).decode("ascii")
    with pytest.raises(ValueError):
        expand_key(b64_5)


def test_create_nonce() -> None:
    """Verifica la estructura y orden little-endian del nonce de 16 bytes."""
    packet_id = 0x0102030405060708
    from_node = 0x11223344

    nonce = create_nonce(packet_id, from_node)
    assert len(nonce) == 16

    pkt_unpacked, from_unpacked = struct.unpack("<QI4x", nonce)
    assert pkt_unpacked == packet_id
    assert from_unpacked == from_node
    assert nonce[12:] == b"\x00\x00\x00\x00"


def test_decrypt_packet_success() -> None:
    """Verifica el ciclo de cifrado y descifrado de un mensaje protobuf Data."""
    packet_id = 12345678
    from_node = 0xA1B2C3D4
    key = expand_key("AQ==")

    # Crear objeto Data protobuf
    original_data = mesh_pb2.Data()
    original_data.portnum = 1  # TEXT_MESSAGE_APP
    original_data.payload = "Mensaje de prueba para Andalucía Mesh".encode("utf-8")
    serialized = original_data.SerializeToString()

    # Cifrar manualmente con AES-CTR
    nonce = create_nonce(packet_id, from_node)
    cipher = Cipher(algorithms.AES(key), modes.CTR(nonce), backend=default_backend())
    encryptor = cipher.encryptor()
    ciphertext = encryptor.update(serialized) + encryptor.finalize()

    # Descifrar con decrypt_packet
    decrypted_data, status = decrypt_packet(ciphertext, packet_id, from_node, key)
    assert status == "ok"
    assert decrypted_data is not None
    assert decrypted_data.portnum == 1
    assert decrypted_data.payload.decode("utf-8") == "Mensaje de prueba para Andalucía Mesh"


def test_decrypt_packet_wrong_key() -> None:
    """Verifica que una clave distinta falle el descifrado y retorne cifrado_desconocido."""
    packet_id = 9999
    from_node = 0x11112222
    key_good = expand_key("AQ==")
    key_bad = b"wrongkey12345678"

    original_data = mesh_pb2.Data()
    original_data.portnum = 1
    original_data.payload = b"Top secret"
    serialized = original_data.SerializeToString()

    nonce = create_nonce(packet_id, from_node)
    cipher = Cipher(algorithms.AES(key_good), modes.CTR(nonce), backend=default_backend())
    encryptor = cipher.encryptor()
    ciphertext = encryptor.update(serialized) + encryptor.finalize()

    decrypted_data, status = decrypt_packet(ciphertext, packet_id, from_node, key_bad)
    assert status == "cifrado_desconocido"
    assert decrypted_data is None
