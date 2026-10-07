"""Ejecutor de migraciones SQL y sincronización del catálogo en PostgreSQL."""

import contextlib
import logging
from pathlib import Path
from typing import Any

import asyncpg
import yaml

from detector.config import Settings

logger = logging.getLogger(__name__)

ADVISORY_LOCK_ID = 84729104  # Bloqueo consultivo único para migraciones de detector-alertas


async def run_migrations(settings: Settings) -> None:
    """Aplica las migraciones pendientes en PostgreSQL bajo un bloqueo consultivo."""
    logger.info("Iniciando comprobación de migraciones de base de datos...")

    conn = await asyncpg.connect(
        host=settings.db_host,
        port=settings.db_port,
        database=settings.db_name,
        user=settings.db_user,
        password=settings.db_password,
    )

    try:
        # 1. Adquirir bloqueo consultivo
        await conn.execute("SELECT pg_advisory_lock($1)", ADVISORY_LOCK_ID)

        # 2. Asegurar existencia de la tabla esquema_version
        await conn.execute(
            """
            CREATE TABLE IF NOT EXISTS esquema_version (
                version INTEGER PRIMARY KEY,
                aplicada_en TIMESTAMPTZ NOT NULL DEFAULT now()
            )
            """
        )

        # 3. Obtener versiones ya aplicadas
        rows = await conn.fetch("SELECT version FROM esquema_version ORDER BY version")
        applied_versions = {r["version"] for r in rows}

        # 4. Localizar archivos SQL
        migrations_dir = Path(__file__).resolve().parent.parent / "migrations"
        if not migrations_dir.is_dir():
            migrations_dir = Path("/app/migrations")

        sql_files = sorted(migrations_dir.glob("*.sql"))

        for sql_file in sql_files:
            prefix = sql_file.name.split("_")[0]
            try:
                version = int(prefix)
            except ValueError:
                continue

            if version not in applied_versions:
                logger.info("Aplicando migración %s (versión %d)...", sql_file.name, version)
                content = sql_file.read_text(encoding="utf-8")

                async with conn.transaction():
                    await conn.execute(content)
                    await conn.execute(
                        "INSERT INTO esquema_version (version) VALUES ($1)",
                        version,
                    )
                logger.info("Migración %s aplicada correctamente.", sql_file.name)
            else:
                logger.debug("Migración %s ya aplicada previamente.", sql_file.name)

        # 5. Sincronizar catálogo
        await sync_catalogo(conn, settings)

    finally:
        with contextlib.suppress(Exception):
            await conn.execute("SELECT pg_advisory_unlock($1)", ADVISORY_LOCK_ID)
        await conn.close()


async def sync_catalogo(conn: asyncpg.Connection, settings: Settings) -> None:
    """Sincroniza la tabla catalogo con clasificacion.yaml y reglas.yaml."""
    clasif_path = settings.resolve_clasificacion_path()
    reglas_path = settings.resolve_reglas_path()

    if not clasif_path.is_file() or not reglas_path.is_file():
        logger.warning("No se encontraron los archivos YAML para sincronizar el catálogo.")
        return

    with open(clasif_path, encoding="utf-8") as f:
        clasif_data: dict[str, Any] = yaml.safe_load(f) or {}

    with open(reglas_path, encoding="utf-8") as f:
        reglas_data: dict[str, Any] = yaml.safe_load(f) or {}

    logger.info("Sincronizando tabla 'catalogo' desde archivos YAML...")

    async with conn.transaction():
        # Limpiar catálogo previo para actualización atómica
        await conn.execute("DELETE FROM catalogo")

        # 1. Insertar Riesgos
        riesgos = clasif_data.get("riesgos", [])
        for orden, r in enumerate(riesgos, start=1):
            await conn.execute(
                """
                INSERT INTO catalogo (clase, id, orden, nombre, descripcion, actualizado_en)
                VALUES ('riesgo', $1, $2, $3, $4, now())
                """,
                r["id"],
                orden,
                r.get("nombre", r["id"]),
                r.get("descripcion", ""),
            )

        # 2. Insertar Tipos
        tipos = clasif_data.get("tipos", [])
        for orden, t in enumerate(tipos, start=1):
            await conn.execute(
                """
                INSERT INTO catalogo (clase, id, orden, nombre, descripcion, actualizado_en)
                VALUES ('tipo', $1, $2, $3, $4, now())
                """,
                t["id"],
                orden,
                t.get("nombre", t["id"]),
                t.get("descripcion", ""),
            )

        # 3. Metadatos fijos de reglas del catálogo oficial
        from detector.reglas_meta import METADATOS_REGLAS

        for orden, (rule_id, meta) in enumerate(METADATOS_REGLAS.items(), start=1):
            rule_cfg = reglas_data.get(rule_id, {})
            is_active = rule_cfg.get("activa", False)

            await conn.execute(
                """
                INSERT INTO catalogo (
                    clase, id, orden, nombre, descripcion, activa, fase, afecta_malla, riesgos, tipos, actualizado_en
                ) VALUES ('regla', $1, $2, $3, $4, $5, $6, $7, $8, $9, now())
                """,
                rule_id,
                orden,
                meta["nombre"],
                meta["descripcion"],
                is_active,
                meta["fase"],
                meta["afecta_malla"],
                meta["riesgos"],
                meta["tipos"],
            )

    logger.info("Sincronización de catálogo completada exitosamente.")
