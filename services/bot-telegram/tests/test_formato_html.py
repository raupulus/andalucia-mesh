"""Pruebas unitarias de serialización a HTML de Telegram para alertas y avisos."""


from bot_telegram.formato_telegram import AdaptadorFormatoTelegram
from nucleo.api_portal import ClientePortal
from nucleo.catalogo import GestorCatalogo
from nucleo.config import ConfiguracionBots
from nucleo.formato import formatear_aviso_neutro


def test_formato_apertura_html() -> None:
    """Verifica que una apertura se formatee a HTML con negritas, código y enlace."""
    config = ConfiguracionBots(
        db_name="test",
        db_user="test",
        db_password="test",
    )
    cliente_portal = ClientePortal(config, "test")
    catalogo = GestorCatalogo(cliente_portal)

    transicion = {
        "v": 1,
        "transicion_id": "01JABCDXYZ",
        "transicion": "abierta",
        "alerta": {
            "id": "01JABCDXYZ7Q8R9S0T1V2W3X4Y",
            "regla": "reboot-loop",
            "riesgo": "alto",
            "tipo": "infraestructura",
            "mensaje": "CAD1 se ha reiniciado 7 veces en la última hora",
            "nodo": "!a1b2c3d4",
            "nodos": [],
            "nodo_info": {
                "corto": "CAD1",
                "largo": "Repetidor Sierra Cádiz <test>",
                "rol": "ROUTER",
                "provincia": "ES-CA",
            },
            "abierta_en": "2026-10-01T15:00:00Z",
            "actualizada_en": "2026-10-01T15:00:00Z",
            "resuelta_en": None,
        },
    }

    aviso = formatear_aviso_neutro(
        datos_transicion=transicion,
        catalogo=catalogo,
        dominio_proyecto="mesh.desdechipiona.es",
    )

    contenido = AdaptadorFormatoTelegram.serializar_contenido(aviso)
    texto = contenido["text"]

    assert "<b>🔴 ALTO · Infraestructura · reboot-loop</b>" in texto
    assert "<code>!a1b2c3d4</code>" in texto
    assert "Cádiz" in texto
    assert "&lt;test&gt;" in texto  # Escapado HTML
    assert "https://mesh.desdechipiona.es/alertas/01JABCDXYZ7Q8R9S0T1V2W3X4Y" in texto
    assert contenido["parse_mode"] == "HTML"
    assert contenido["disable_notification"] is False
    assert contenido["link_preview_options"]["is_disabled"] is True


def test_formato_resolucion_silenciosa() -> None:
    """Verifica que las resoluciones tengan notificación silenciosa y cálculo de duración."""
    config = ConfiguracionBots(
        db_name="test",
        db_user="test",
        db_password="test",
    )
    cliente_portal = ClientePortal(config, "test")
    catalogo = GestorCatalogo(cliente_portal)

    transicion = {
        "v": 1,
        "transicion_id": "01JABCDXYZ",
        "transicion": "resuelta",
        "alerta": {
            "id": "01JABCDXYZ7Q8R9S0T1V2W3X4Y",
            "regla": "reboot-loop",
            "riesgo": "alto",
            "tipo": "infraestructura",
            "mensaje": "Resuelto",
            "nodo": "!a1b2c3d4",
            "abierta_en": "2026-10-01T15:00:00Z",
            "actualizada_en": "2026-10-01T17:12:00Z",
            "resuelta_en": "2026-10-01T17:12:00Z",
        },
    }

    aviso = formatear_aviso_neutro(
        datos_transicion=transicion,
        catalogo=catalogo,
        dominio_proyecto="mesh.desdechipiona.es",
    )

    contenido = AdaptadorFormatoTelegram.serializar_contenido(aviso)
    assert contenido["disable_notification"] is True
    assert "2 h 12 min" in contenido["text"]
    assert "✅ RESUELTA" in contenido["text"]
