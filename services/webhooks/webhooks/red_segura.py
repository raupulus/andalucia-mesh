"""Protección contra SSRF mediante validación de URLs y resolución DNS restringida a IPs públicas."""

import asyncio
import ipaddress
import logging
import socket
from urllib.parse import urlparse

logger = logging.getLogger("webhooks.red_segura")

RED_NAT64 = ipaddress.ip_network("64:ff9b::/96")


def validar_url_carga(url: str) -> None:
    """Valida la sintaxis y el host de la URL al cargar la configuración."""
    if not url.startswith("https://"):
        raise ValueError("La URL debe utilizar el protocolo https://")

    if len(url) > 2048:
        raise ValueError("La URL excede el límite máximo de 2.048 caracteres")

    parsed = urlparse(url)
    if parsed.username or parsed.password:
        raise ValueError("La URL no debe incluir credenciales (usuario o contraseña)")

    host = parsed.hostname
    if not host:
        raise ValueError("La URL carece de un host válido")

    host_lower = host.lower()
    if host_lower == "localhost" or host_lower.endswith((".local", ".internal")):
        raise ValueError(f"Host no permitido: {host_lower}")

    if "." not in host:
        raise ValueError(f"El host debe contener al menos un punto: {host_lower}")

    # Si es una dirección IP literal, verificar que sea pública
    try:
        ip = ipaddress.ip_address(host)
        if not es_ip_global_permitida(ip, []):
            raise ValueError(f"La dirección IP {host} no es una IP pública permitida")
    except ValueError as e:
        if "La dirección IP" in str(e):
            raise


def es_ip_global_permitida(
    ip: ipaddress.IPv4Address | ipaddress.IPv6Address,
    redes_bloqueadas: list[ipaddress.IPv4Network | ipaddress.IPv6Network],
) -> bool:
    """Comprueba si una IP es pública global y no pertenece a redes privadas, NAT64 ni bloqueadas."""
    # Comprobar si es IPv4 mapeada en IPv6 (::ffff:0:0/96)
    if isinstance(ip, ipaddress.IPv6Address) and ip.ipv4_mapped:
        ip = ip.ipv4_mapped

    if not ip.is_global:
        return False

    if isinstance(ip, ipaddress.IPv6Address) and ip in RED_NAT64:
        return False

    return all(ip not in red for red in redes_bloqueadas)


async def resolver_y_validar_host(
    host: str,
    redes_bloqueadas: list[ipaddress.IPv4Network | ipaddress.IPv6Network],
) -> list[str]:
    """Resuelve A y AAAA del host en asíncrono y valida que todas las direcciones sean globales."""
    loop = asyncio.get_running_loop()
    try:
        # Resolver nombre de host en el pool de hilos
        info_addr = await loop.getaddrinfo(
            host,
            None,
            family=socket.AF_UNSPEC,
            type=socket.SOCK_STREAM,
        )
    except socket.gaierror as e:
        raise ValueError(f"Fallo de resolución DNS para {host}: {e}") from e

    ips_validadas: list[str] = []
    for _family, _socktype, _proto, _canonname, sockaddr in info_addr:
        ip_str = str(sockaddr[0])
        try:
            ip_obj = ipaddress.ip_address(ip_str)
        except ValueError:
            continue

        if not es_ip_global_permitida(ip_obj, redes_bloqueadas):
            logger.warning(
                "Dirección IP no permitida detectada para host %s: %s (posible SSRF)",
                host,
                ip_str,
            )
            raise ValueError(f"Dirección IP no permitida resuelta para host {host}: {ip_str}")

        if ip_str not in ips_validadas:
            ips_validadas.append(ip_str)

    if not ips_validadas:
        raise ValueError(f"No se obtuvieron direcciones IP válidas para {host}")

    return ips_validadas
