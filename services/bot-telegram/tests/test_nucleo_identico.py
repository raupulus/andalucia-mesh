"""Prueba que verifica la identidad exacta de los archivos compartidos de nucleo/ (UT-08.9)."""

import hashlib
from pathlib import Path

ARCHIVOS_COMUNES_TRES = [
    "__init__.py",
    "config.py",
    "registro.py",
    "salud.py",
    "base.py",
    "socket_alertas.py",
    "retencion.py",
]

ARCHIVOS_COMUNES_BOTS = [
    "api_portal.py",
    "catalogo.py",
    "motor_envios.py",
    "formato.py",
    "comandos.py",
]


def sha256_archivo(ruta: Path) -> str:
    """Calcula el hash SHA-256 de un archivo."""
    return hashlib.sha256(ruta.read_bytes()).hexdigest()


def test_archivos_comunes_tres_aplicaciones_identicos() -> None:
    """Comprueba que los archivos comunes a las tres aplicaciones tienen el mismo hash exacto."""
    base_services = Path(__file__).resolve().parents[2]
    nucleo_tg = base_services / "bot-telegram" / "nucleo"
    nucleo_dc = base_services / "bot-discord" / "nucleo"
    nucleo_wh = base_services / "webhooks" / "nucleo"

    for archivo in ARCHIVOS_COMUNES_TRES:
        f_tg = nucleo_tg / archivo
        f_dc = nucleo_dc / archivo
        f_wh = nucleo_wh / archivo

        assert f_tg.exists(), f"Falta {archivo} en bot-telegram/nucleo"
        assert f_dc.exists(), f"Falta {archivo} en bot-discord/nucleo"
        assert f_wh.exists(), f"Falta {archivo} en webhooks/nucleo"

        hash_tg = sha256_archivo(f_tg)
        hash_dc = sha256_archivo(f_dc)
        hash_wh = sha256_archivo(f_wh)

        assert hash_tg == hash_dc == hash_wh, (
            f"Discrepancia en {archivo}:\n"
            f"  bot-telegram: {hash_tg}\n"
            f"  bot-discord:  {hash_dc}\n"
            f"  webhooks:     {hash_wh}"
        )


def test_archivos_comunes_bots_identicos() -> None:
    """Comprueba que los archivos específicos de bots entre Telegram y Discord son idénticos."""
    base_services = Path(__file__).resolve().parents[2]
    nucleo_tg = base_services / "bot-telegram" / "nucleo"
    nucleo_dc = base_services / "bot-discord" / "nucleo"

    for archivo in ARCHIVOS_COMUNES_BOTS:
        f_tg = nucleo_tg / archivo
        f_dc = nucleo_dc / archivo

        assert f_tg.exists(), f"Falta {archivo} en bot-telegram/nucleo"
        assert f_dc.exists(), f"Falta {archivo} en bot-discord/nucleo"

        hash_tg = sha256_archivo(f_tg)
        hash_dc = sha256_archivo(f_dc)

        assert hash_tg == hash_dc, (
            f"Discrepancia en {archivo} entre bots:\n"
            f"  bot-telegram: {hash_tg}\n"
            f"  bot-discord:  {hash_dc}"
        )
