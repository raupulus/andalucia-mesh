"""Pruebas unitarias de serialización a Embeds de Discord para alertas y avisos."""

from bot_discord.formato_discord import COLORES_DISCORD, AdaptadorFormatoDiscord
from nucleo.api_portal import ClientePortal
from nucleo.catalogo import GestorCatalogo
from nucleo.config import ConfiguracionBots
from nucleo.formato import formatear_aviso_neutro


def test_colores_tokens_design() -> None:
    """Verifica que los enteros RGB coincidan con los tokens de modo oscuro de DESIGN.md."""
    assert COLORES_DISCORD["critico"] == 0xFFB4AE
    assert COLORES_DISCORD["aviso"] == 0xFFD48A
    assert COLORES_DISCORD["info"] == 0xA9C9FF
    assert COLORES_DISCORD["correcto"] == 0x9CF1BA


def test_formato_apertura_embed() -> None:
    """Verifica que una apertura se formatee como Embed con título, URL, nodo con backticks y color crítico."""
    config = ConfiguracionBots(
        db_name="test",
        db_user="test",
        db_password="test",
        project_name="Sur Nodos en Mallas",
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
                "largo": "Repetidor Sierra Cádiz",
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

    adaptador = AdaptadorFormatoDiscord(project_name="Sur Nodos en Mallas")
    contenido = adaptador.serializar_contenido(aviso)

    assert "embeds" in contenido
    assert len(contenido["embeds"]) == 1
    embed = contenido["embeds"][0]

    assert embed["title"] == "🔴 ALTO · Infraestructura · reboot-loop"
    assert embed["url"] == "https://mesh.desdechipiona.es/alertas/01JABCDXYZ7Q8R9S0T1V2W3X4Y"
    assert embed["color"] == 0xFFB4AE  # critico
    assert "`!a1b2c3d4`" in embed["description"]
    assert "Repetidor Sierra Cádiz" in embed["description"]
    assert embed["footer"]["text"] == "Sur Nodos en Mallas"
    assert contenido["allowed_mentions"] == {"parse": []}


def test_formato_resolucion_embed() -> None:
    """Verifica que una resolución use el color correcto y muestre la duración."""
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

    adaptador = AdaptadorFormatoDiscord(project_name="Sur Nodos en Mallas")
    contenido = adaptador.serializar_contenido(aviso)

    embed = contenido["embeds"][0]
    assert embed["title"] == "✅ RESUELTA · reboot-loop"
    assert embed["color"] == 0x9CF1BA  # correcto
    assert "2 h 12 min" in embed["description"]
