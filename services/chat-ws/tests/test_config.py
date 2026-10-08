"""Pruebas unitarias para la configuración del microservicio chat-ws."""

from chat_ws.config import Settings


def test_settings_defaults() -> None:
    """Verifica los valores por defecto de la configuración."""
    settings = Settings()
    assert settings.CHAT_PUERTO == 8000
    assert settings.HEALTH_PORT == 8080
    assert settings.MQTT_PORT == 1884
    assert settings.CHAT_HISTORIAL == 20
    assert settings.CHAT_MAX_CONEXIONES == 2000
    assert settings.CHAT_MAX_POR_IP == 10
    assert "SFNarrow" in settings.allowed_channels_list
    assert "Cadiz" in settings.allowed_channels_list
    assert len(settings.allowed_channels_list) == 14
    assert "172.30.0.1" in settings.trusted_proxies_set
    assert "127.0.0.1" in settings.trusted_proxies_set


def test_settings_custom_channels() -> None:
    """Verifica la correcta interpretación de canales y proxies personalizados."""
    settings = Settings(
        ALLOWED_CHANNELS="Canal1, Canal2,Canal3",
        CHAT_PROXIES_CONFIABLES="10.0.0.1, 10.0.0.2",
    )
    assert settings.allowed_channels_list == ["Canal1", "Canal2", "Canal3"]
    assert settings.trusted_proxies_set == {"10.0.0.1", "10.0.0.2"}
