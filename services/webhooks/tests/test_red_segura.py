"""Pruebas de validación de URLs y protección contra SSRF en webhooks."""

import ipaddress

import pytest

from webhooks.red_segura import es_ip_global_permitida, validar_url_carga


def test_validar_url_carga_correctas() -> None:
    """Verifica que URLs https públicas válidas sean aceptadas."""
    validar_url_carga("https://alertas.example.org/snm")
    validar_url_carga("https://hooks.example.net:8443/entrada")
    validar_url_carga("https://1.1.1.1/webhook")


@pytest.mark.parametrize(
    "url_invalida",
    [
        "http://alertas.example.org/snm",  # No https
        "https://localhost/entrada",  # localhost
        "https://127.0.0.1/entrada",  # Loopback
        "https://10.0.0.1/entrada",  # Privada RFC 1918
        "https://192.168.1.50/entrada",  # Privada RFC 1918
        "https://172.30.0.1/entrada",  # Red docker mesh
        "https://servidor.local/entrada",  # .local
        "https://servidor.internal/entrada",  # .internal
        "https://nodot/",  # Sin punto
        "https://usuario:pass@ejemplo.com/webhook",  # Credenciales embebidas
    ],
)
def test_validar_url_carga_rechazadas(url_invalida: str) -> None:
    """Verifica que URLs no seguras o privadas se rechacen al cargar."""
    with pytest.raises(ValueError):
        validar_url_carga(url_invalida)


def test_es_ip_global_permitida() -> None:
    """Comprueba el filtrado estricto de IPs globales y redes bloqueadas."""
    ip_pub = ipaddress.ip_address("8.8.8.8")
    ip_priv = ipaddress.ip_address("10.0.0.1")
    ip_loopback = ipaddress.ip_address("127.0.0.1")
    ip_nat64 = ipaddress.ip_address("64:ff9b::1")
    red_bloqueada = ipaddress.ip_network("203.0.113.0/24")
    ip_bloqueada = ipaddress.ip_address("203.0.113.15")

    assert es_ip_global_permitida(ip_pub, []) is True
    assert es_ip_global_permitida(ip_priv, []) is False
    assert es_ip_global_permitida(ip_loopback, []) is False
    assert es_ip_global_permitida(ip_nat64, []) is False
    assert es_ip_global_permitida(ip_bloqueada, [red_bloqueada]) is False
    assert es_ip_global_permitida(ip_pub, [red_bloqueada]) is True
