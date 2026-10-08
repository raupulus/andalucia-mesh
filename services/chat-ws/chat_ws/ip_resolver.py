"""Resolución segura de dirección IP de cliente tras proxy inverso Nginx."""

import ipaddress
from collections.abc import Mapping


def resolve_client_ip(
    peer_ip: str,
    headers: Mapping[str, str],
    trusted_proxies: set[str],
) -> str:
    """Extrae la dirección IP real del cliente considerando proxies inversos de confianza.

    Si la conexión procede de una IP perteneciente a 'trusted_proxies' (ej. Nginx en 172.30.0.1 o localhost),
    se examina la cabecera 'X-Forwarded-For' extrayendo la primera dirección IP cliente (extremo izquierdo).
    En cualquier otro caso, se utiliza estrictamente 'peer_ip'.

    Args:
        peer_ip: Dirección IP del socket remoto inmediato.
        headers: Diccionario o mapping de cabeceras HTTP de la petición de handshake.
        trusted_proxies: Conjunto de direcciones IP de proxies en los que se confía.

    Returns:
        Cadena con la dirección IP normalizada del cliente.
    """
    clean_peer = peer_ip.strip()
    if clean_peer not in trusted_proxies:
        return clean_peer

    # Búsqueda case-insensitive de la cabecera X-Forwarded-For
    xff_val: str | None = None
    for k, v in headers.items():
        if k.lower() == "x-forwarded-for":
            xff_val = v
            break

    if not xff_val:
        return clean_peer

    # Extraer la primera IP de la lista separada por comas
    parts = [part.strip() for part in xff_val.split(",") if part.strip()]
    if not parts:
        return clean_peer

    candidate = parts[0]
    # Validar sintácticamente que sea una IP válida (IPv4 o IPv6)
    try:
        ipaddress.ip_address(candidate)
        return candidate
    except ValueError:
        return clean_peer
