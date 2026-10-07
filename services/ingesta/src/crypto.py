"""Descifrado criptográfico AES-CTR de paquetes Meshtastic para snm-ingesta.

Descifra paquetes recibidos en el canal comunitario utilizando la clave
por defecto (AQ==), expandida conforme al estándar del firmware de Meshtastic.
Respeta estrictamente la regla TR-02 para el acceso a 'packet.from'.
"""

from __future__ import annotations

import base64
import logging
from typing import Final

from cryptography.hazmat.primitives.ciphers import Cipher, algorithms, modes
from meshtastic.protobuf import mesh_pb2

logger = logging.getLogger("ingesta.crypto")

# Clave simétrica predeterminada de 16 bytes de Meshtastic (Canal Primario / LongFast / SFNarrow)
# Corresponde a la constante firmware defaultpsk: d4 f1 bb 3a 20 29 07 59 f0 bc ff ab cf 4e 69 01
DEFAULT_PSK_16: Final[bytes] = bytes([
    0xD4, 0xF1, 0xBB, 0x3A, 0x20, 0x29, 0x07, 0x59,
    0xF0, 0xBC, 0xFF, 0xAB, 0xCF, 0x4E, 0x69, 0x01,
])


def expand_channel_key(channel_key_b64: str) -> bytes:
    """Expande una clave de canal en Base64 al formato binario de clave AES (16 o 32 bytes).

    Reglas de firmware Meshtastic:
    - Clave de 1 byte con valor n >= 1: Toma DEFAULT_PSK_16 y suma (n - 1) al último byte.
    - Clave de 16 bytes: Clave AES-128 directa.
    - Clave de 32 bytes: Clave AES-256 directa.

    Args:
        channel_key_b64: Cadena base64 de la clave (ej. 'AQ==' para clave 1).

    Returns:
        Bytes de clave AES listos para el algoritmo de cifrado.
    """
    raw_key = base64.b64decode(channel_key_b64)
    if len(raw_key) == 1:
        n = raw_key[0]
        if n == 0:
            return b""
        key_list = list(DEFAULT_PSK_16)
        key_list[-1] = (key_list[-1] + (n - 1)) & 0xFF
        return bytes(key_list)
    elif len(raw_key) in (16, 32):
        return raw_key
    else:
        raise ValueError(f"Longitud de clave de canal inválida ({len(raw_key)} bytes)")


class PacketDecryptor:
    """Descifrador de paquetes Meshtastic con clave AES-CTR comunitaria."""

    def __init__(self, channel_key_b64: str = "AQ==") -> None:
        """Inicializa el descifrador expandiendo la clave configurada."""
        self.aes_key = expand_channel_key(channel_key_b64)

    def decrypt_packet(
        self,
        packet: mesh_pb2.MeshPacket,
    ) -> tuple[mesh_pb2.Data | None, str]:
        """Descifra el payload de un MeshPacket o recupera su Data si ya viene en claro.

        Args:
            packet: Objeto MeshPacket decodificado del ServiceEnvelope.

        Returns:
            Tupla (data_object, decrypt_status) donde decrypt_status es:
            'claro', 'descifrado', 'cifrado_desconocido' o 'pki'.
        """
        # 1. Caso PKI (mensaje directo cifrado con clave asimétrica)
        if getattr(packet, "pki_encrypted", False):
            return None, "pki"

        # 2. Caso en claro (ya decodificado por el gateway o map report)
        if packet.HasField("decoded"):
            return packet.decoded, "claro"

        # 3. Caso sin carga cifrada
        if not packet.encrypted:
            return None, "cifrado_desconocido"

        # 4. Descifrado AES-CTR con clave comunitaria
        try:
            # Regla TR-02: Acceso seguro al campo 'from' de Protobuf
            from_node: int = getattr(packet, "from", 0)
            packet_id: int = packet.id

            # Construcción de nonce de 16 bytes:
            # 8 bytes little-endian packet_id + 4 bytes little-endian from_node + 4 bytes cero
            nonce = (
                packet_id.to_bytes(8, byteorder="little")
                + from_node.to_bytes(4, byteorder="little")
                + b"\x00\x00\x00\x00"
            )

            cipher = Cipher(algorithms.AES(self.aes_key), modes.CTR(nonce))
            decryptor = cipher.decryptor()
            decrypted_bytes = decryptor.update(packet.encrypted) + decryptor.finalize()

            data = mesh_pb2.Data()
            data.ParseFromString(decrypted_bytes)

            # Si el portnum resultante es 0 (UNKNOWN_APP), se considera falso descifrado
            if data.portnum == 0:
                return None, "cifrado_desconocido"

            return data, "descifrado"

        except Exception as e:
            logger.debug("Error descifrando paquete %s: %s", getattr(packet, "id", 0), e)
            return None, "cifrado_desconocido"
