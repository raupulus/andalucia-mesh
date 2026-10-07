#!/usr/bin/env python3
"""Script generador de configuración para MeshView.

Lee la plantilla config.ini.plantilla, valida las variables de entorno requeridas
y genera atómicamente el fichero /etc/meshview/config.ini con permisos 0600.
"""

from __future__ import annotations

import os
import re
import sys
from pathlib import Path
from string import Template

REQUIRED_VARS = [
    "PROJECT_NAME",
    "PROJECT_DOMAIN",
    "MQTT_HOST",
    "MQTT_PORT",
    "MQTT_TOPIC_ROOT",
    "MQTT_USER",
    "MQTT_PASSWORD",
    "DB_HOST",
    "DB_PORT",
    "DB_USER",
    "DB_PASSWORD",
    "DB_NAME",
]


def main() -> int:
    """Valida el entorno y genera el config.ini de forma atómica."""
    missing = [var for var in REQUIRED_VARS if not os.environ.get(var)]
    if missing:
        print(
            f"[ERROR] Variables de entorno ausentes para MeshView: {', '.join(missing)}",
            file=sys.stderr,
        )
        return 1

    # Validar que las contraseñas no contengan caracteres conflictivos para INI / URLs
    mqtt_pass = os.environ["MQTT_PASSWORD"]
    db_pass = os.environ["DB_PASSWORD"]

    if not re.match(r"^[A-Za-z0-9]+$", mqtt_pass):
        print(
            "[ERROR] MQTT_PASSWORD contiene caracteres no alfanuméricos. Se exige [A-Za-z0-9]+.",
            file=sys.stderr,
        )
        return 1

    if not re.match(r"^[A-Za-z0-9]+$", db_pass):
        print(
            "[ERROR] DB_PASSWORD contiene caracteres no alfanuméricos. Se exige [A-Za-z0-9]+.",
            file=sys.stderr,
        )
        return 1

    template_path = Path("/plantilla/config.ini.plantilla")
    if not template_path.exists():
        template_path = Path(__file__).parent / "config.ini.plantilla"

    if not template_path.exists():
        print(f"[ERROR] No se encuentra la plantilla en {template_path}", file=sys.stderr)
        return 1

    with open(template_path, "r", encoding="utf-8") as f:
        template_content = f.read()

    template = Template(template_content)
    rendered = template.substitute(os.environ)

    target_dir = Path("/etc/meshview")
    target_file = target_dir / "config.ini"
    tmp_file = target_dir / "config.ini.tmp"

    target_dir.mkdir(parents=True, exist_ok=True)

    with open(tmp_file, "w", encoding="utf-8") as f:
        f.write(rendered)

    # Permisos restrictivos (solo lectura/escritura por el propietario)
    os.chmod(tmp_file, 0o600)
    tmp_file.replace(target_file)

    print(f"[OK] Fichero de configuración generado exitosamente en {target_file}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
