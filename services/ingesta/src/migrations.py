"""Ejecutor autónomo de migraciones SQL para snm_ingest en TimescaleDB.

Soporta:
1. Migraciones versionadas (V001__*.sql, V002__*.sql) ejecutadas en orden secuencial
   con validación estricta de sumas de verificación SHA-256.
2. Migraciones repetibles (R__*.sql) que se re-ejecutan solo si su contenido
   o variables de plantilla han cambiado.
3. Sustitución de variables de entorno ({{TZ}}, {{INFRA_ROLES}}).
4. Bloqueo consultivo a nivel de base de datos (pg_advisory_lock) para
   evitar ejecuciones concurrentes.
5. Ejecución individual de sentencias SQL fuera de bloque transaccional
   cuando TimescaleDB lo requiere (e.g. continuos aggregates).
"""

from __future__ import annotations

import glob
import hashlib
import logging
import os
import re
from typing import Final

import asyncpg

logger = logging.getLogger("ingesta.migrations")

# Identificador constante para pg_advisory_lock
MIGRATION_ADVISORY_LOCK_ID: Final[int] = 987654321


def split_sql_statements(sql: str) -> list[str]:
    """Divide un script SQL en sentencias individuales respetando literales y bloques dollar-quoted.

    Args:
        sql: Contenido completo del archivo SQL.

    Returns:
        Lista de sentencias SQL limpias listas para ejecución.
    """
    statements: list[str] = []
    current: list[str] = []
    in_single_quote = False
    in_dollar_quote = False
    dollar_tag = ""
    i = 0
    n = len(sql)

    while i < n:
        c = sql[i]

        # Manejo de comillas simples '...'
        if not in_dollar_quote:
            if c == "'":
                if in_single_quote and i + 1 < n and sql[i + 1] == "'":
                    current.append("''")
                    i += 2
                    continue
                in_single_quote = not in_single_quote
                current.append(c)
                i += 1
                continue

        # Manejo de dollar-quoting $$...$$ o $tag$...$tag$
        if not in_single_quote and c == "$":
            match = re.match(r"\$([A-Za-z0-9_]*)\$", sql[i:])
            if match:
                tag = match.group(0)
                if in_dollar_quote:
                    if tag == dollar_tag:
                        in_dollar_quote = False
                        dollar_tag = ""
                else:
                    in_dollar_quote = True
                    dollar_tag = tag
                current.append(tag)
                i += len(tag)
                continue

        # Separador de sentencia punto y coma fuera de literales
        if c == ";" and not in_single_quote and not in_dollar_quote:
            stmt = "".join(current).strip()
            if stmt:
                statements.append(stmt)
            current = []
            i += 1
            continue

        current.append(c)
        i += 1

    last = "".join(current).strip()
    if last:
        statements.append(last)

    return statements


def compute_sha256(content: str) -> str:
    """Calcula el hash SHA-256 de una cadena de texto en formato hexadecimal."""
    return hashlib.sha256(content.encode("utf-8")).hexdigest()


class MigrationRunner:
    """Gestor de ciclo de vida y aplicación de migraciones SQL."""

    def __init__(
        self,
        db_pool: asyncpg.Pool,
        migrations_dir: str,
        tz: str = "Europe/Madrid",
        infra_roles: list[str] | None = None,
    ) -> None:
        """Inicializa el runner de migraciones.

        Args:
            db_pool: Pool de conexiones asíncronas a PostgreSQL.
            migrations_dir: Ruta al directorio que contiene los archivos .sql.
            tz: Zona horaria para la sustitución de {{TZ}}.
            infra_roles: Lista de roles de infraestructura para {{INFRA_ROLES}}.
        """
        self.pool = db_pool
        self.migrations_dir = migrations_dir
        self.tz = tz
        self.infra_roles = infra_roles or ["ROUTER", "ROUTER_LATE", "REPEATER"]

    def _render_template(self, sql: str) -> str:
        """Sustituye variables de plantilla en el script SQL."""
        rendered = sql.replace("{{TZ}}", self.tz)
        formatted_roles = ", ".join(f"'{role}'" for role in self.infra_roles)
        rendered = rendered.replace("{{INFRA_ROLES}}", formatted_roles)
        return rendered

    async def run_all(self) -> None:
        """Ejecuta todas las migraciones pendientes con bloqueo consultivo."""
        logger.info("Verificando y aplicando migraciones SQL desde %s...", self.migrations_dir)

        async with self.pool.acquire() as conn:
            # 1. Adquirir bloqueo consultivo
            await conn.execute("SELECT pg_advisory_lock($1);", MIGRATION_ADVISORY_LOCK_ID)
            try:
                # 2. Asegurar que existe la tabla de control
                await conn.execute(
                    """
                    CREATE TABLE IF NOT EXISTS schema_migrations (
                        version text PRIMARY KEY,
                        checksum text NOT NULL,
                        applied_at timestamptz NOT NULL DEFAULT now()
                    );
                    """
                )

                # 3. Consultar migraciones ya aplicadas
                rows = await conn.fetch("SELECT version, checksum FROM schema_migrations;")
                applied = {row["version"]: row["checksum"] for row in rows}

                # 4. Procesar migraciones versionadas (V001__*.sql)
                versioned_files = sorted(
                    glob.glob(os.path.join(self.migrations_dir, "V[0-9]*__*.sql"))
                )

                for fpath in versioned_files:
                    fname = os.path.basename(fpath)
                    with open(fpath, "r", encoding="utf-8") as fp:
                        raw_content = fp.read()

                    rendered_content = self._render_template(raw_content)
                    checksum = compute_sha256(rendered_content)

                    if fname in applied:
                        if applied[fname] != checksum:
                            raise ValueError(
                                f"Inconsistencia en migración '{fname}': el checksum registrado no coincide."
                            )
                        logger.debug("Migración versionada '%s' ya aplicada.", fname)
                        continue

                    logger.info("Aplicando migración versionada '%s'...", fname)
                    stmts = split_sql_statements(rendered_content)
                    for stmt in stmts:
                        await conn.execute(stmt)

                    await conn.execute(
                        "INSERT INTO schema_migrations (version, checksum) VALUES ($1, $2);",
                        fname,
                        checksum,
                    )
                    logger.info("Migración '%s' aplicada exitosamente.", fname)

                # 5. Procesar migraciones repetibles (R__*.sql)
                repeatable_files = sorted(
                    glob.glob(os.path.join(self.migrations_dir, "R__*.sql"))
                )

                for fpath in repeatable_files:
                    fname = os.path.basename(fpath)
                    with open(fpath, "r", encoding="utf-8") as fp:
                        raw_content = fp.read()

                    rendered_content = self._render_template(raw_content)
                    checksum = compute_sha256(rendered_content)

                    if fname in applied and applied[fname] == checksum:
                        logger.debug("Migración repetible '%s' sin cambios.", fname)
                        continue

                    logger.info("Aplicando/actualizando migración repetible '%s'...", fname)
                    stmts = split_sql_statements(rendered_content)
                    for stmt in stmts:
                        await conn.execute(stmt)

                    await conn.execute(
                        """
                        INSERT INTO schema_migrations (version, checksum, applied_at)
                        VALUES ($1, $2, now())
                        ON CONFLICT (version) DO UPDATE
                            SET checksum = EXCLUDED.checksum, applied_at = now();
                        """,
                        fname,
                        checksum,
                    )
                    logger.info("Migración repetible '%s' aplicada exitosamente.", fname)

            finally:
                # 6. Liberar bloqueo consultivo pase lo que pase
                await conn.execute("SELECT pg_advisory_unlock($1);", MIGRATION_ADVISORY_LOCK_ID)

        logger.info("Todas las migraciones SQL están al día.")
