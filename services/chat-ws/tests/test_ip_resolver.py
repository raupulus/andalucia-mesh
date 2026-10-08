"""Pruebas unitarias para la resolución de IP de clientes tras proxy inverso."""

from chat_ws.ip_resolver import resolve_client_ip


def test_ip_resolver_direct_untrusted() -> None:
    """Verifica que si la IP remota no está en trusted_proxies, se ignore X-Forwarded-For."""
    trusted = {"172.30.0.1", "127.0.0.1"}
    headers = {"X-Forwarded-For": "198.51.100.5"}

    # IP de cliente directo no confiable
    resolved = resolve_client_ip("203.0.113.10", headers, trusted)
    assert resolved == "203.0.113.10"


def test_ip_resolver_trusted_proxy_single_ip() -> None:
    """Verifica que desde un proxy de confianza se extraiga la IP de X-Forwarded-For."""
    trusted = {"172.30.0.1", "127.0.0.1"}
    headers = {"X-Forwarded-For": "198.51.100.5"}

    resolved = resolve_client_ip("172.30.0.1", headers, trusted)
    assert resolved == "198.51.100.5"


def test_ip_resolver_trusted_proxy_multiple_ips() -> None:
    """Verifica que se tome la primera IP (cliente original) si hay cadena de proxies."""
    trusted = {"172.30.0.1", "127.0.0.1"}
    headers = {"X-Forwarded-For": "198.51.100.5, 10.0.0.1, 172.16.0.1"}

    resolved = resolve_client_ip("172.30.0.1", headers, trusted)
    assert resolved == "198.51.100.5"


def test_ip_resolver_malformed_xff() -> None:
    """Verifica que cabeceras malformadas o no IP se ignoren de forma segura."""
    trusted = {"172.30.0.1", "127.0.0.1"}
    headers = {"X-Forwarded-For": "malicious<script>"}

    resolved = resolve_client_ip("172.30.0.1", headers, trusted)
    assert resolved == "172.30.0.1"
