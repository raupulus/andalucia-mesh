"""Gestor de base de datos asíncrono con psycopg-pool, migraciones y bloqueo de instancia."""

import asyncio
import logging
from collections.abc import AsyncIterator
from contextlib import asynccontextmanager
from pathlib import Path
from typing import Any

from psycopg import AsyncConnection
from psycopg.rows import tuple_row
from psycopg_pool import AsyncConnectionPool

from nucleo.config import ConfiguracionBase

logger = logging.getLogger("nucleo.base")


class GestorBase:
    """Gestiona el pool de conexiones a PostgreSQL, migraciones y bloqueo consultivo."""

    def __init__(self, config: ConfiguracionBase, servicio: str) -> None:
        """Inicializa el gestor de base de datos."""
        self.config = config
        self.servicio = servicio
        self.conninfo = (
            f"host={config.db_host} port={config.db_port} "
            f"dbname={config.db_name} user={config.db_user} "
            f"password={config.db_password} application_name={servicio}"
        )
        self.pool: AsyncConnectionPool[AsyncConnection[tuple[Any, ...]]] = AsyncConnectionPool(
            conninfo=self.conninfo,
            min_size=1,
            max_size=5,
            open=False,
            timeout=10.0,
            kwargs={"row_factory": tuple_row},
        )
        self._bloqueo_id: int | None = None
        self._bloqueo_conn: AsyncConnection[tuple[Any, ...]] | None = None

    async def conectar(self) -> None:
        """Abre el pool de conexiones con reintentos exponenciales ante caídas temporales."""
        espera = 1.0
        while True:
            try:
                await self.pool.open()
                logger.info("Pool de conexiones PostgreSQL abierto correctamente para %s.", self.servicio)
                break
            except Exception as e:
                logger.warning(
                    "Error conectando a PostgreSQL (%s). Reintentando en %.1fs...",
                    str(e).strip(),
                    espera,
                )
                await asyncio.sleep(espera)
                espera = min(espera * 2.0, 30.0)

    async def cerrar(self) -> None:
        """Libera el bloqueo de instancia y cierra el pool de conexiones."""
        if self._bloqueo_conn and self._bloqueo_id is not None:
            try:
                await self._bloqueo_conn.execute(
                    "SELECT pg_advisory_unlock(%s)", (self._bloqueo_id,)
                )
                await self._bloqueo_conn.close()
                logger.info("Bloqueo consultivo de instancia %d liberado.", self._bloqueo_id)
            except Exception:
                pass
            self._bloqueo_conn = None

        await self.pool.close()
        logger.info("Pool de conexiones PostgreSQL cerrado.")

    async def adquirir_bloqueo_instancia(self, bloqueo_id: int) -> None:
        """Obtiene un bloqueo consultivo de PostgreSQL; si otra instancia lo tiene, sale con error."""
        conn = await AsyncConnection.connect(self.conninfo)
        async with conn.cursor() as cur:
            await cur.execute("SELECT pg_try_advisory_lock(%s)", (bloqueo_id,))
            fila = await cur.fetchone()
            obtenido = fila[0] if fila else False

        if not obtenido:
            await conn.close()
            raise RuntimeError(
                f"Otra instancia activa del servicio {self.servicio} ya tiene el bloqueo consultivo {bloqueo_id}."
            )

        self._bloqueo_id = bloqueo_id
        self._bloqueo_conn = conn
        logger.info("Bloqueo consultivo de instancia única %d adquirido con éxito.", bloqueo_id)

    @asynccontextmanager
    async def conexion(self) -> AsyncIterator[AsyncConnection[tuple[Any, ...]]]:
        """Provee una conexión del pool dentro de un context manager asíncrono."""
        async with self.pool.connection() as conn:
            yield conn

    async def comprobar_salud(self) -> bool:
        """Verifica la conectividad básica con la base de datos."""
        try:
            async with self.pool.connection() as conn, conn.cursor() as cur:
                await cur.execute("SELECT 1")
                res = await cur.fetchone()
                return bool(res and res[0] == 1)
        except Exception as e:
            logger.warning("Fallo en comprobación de salud de PostgreSQL: %s", e)
            return False

    async def aplicar_migraciones(self, dir_migraciones: Path) -> int:
        """Aplica todas las migraciones SQL pendientes en orden numérico."""
        if not dir_migraciones.exists():
            logger.warning("Directorio de migraciones no encontrado: %s", dir_migraciones)
            return 0

        archivos = sorted(dir_migraciones.glob("*.sql"))
        if not archivos:
            return 0

        async with self.pool.connection() as conn:
            async with conn.transaction():
                await conn.execute(
                    """
                    CREATE TABLE IF NOT EXISTS esquema_migraciones (
                        version INT PRIMARY KEY,
                        aplicada_en TIMESTAMPTZ NOT NULL DEFAULT now()
                    )
                    """
                )

            # Consultar versiones ya aplicadas
            async with conn.cursor() as cur:
                await cur.execute("SELECT version FROM esquema_migraciones")
                versiones_aplicadas = {fila[0] for fila in await cur.fetchall()}

            aplicadas_ahora = 0
            for archivo in archivos:
                prefijo = archivo.name.split("_")[0]
                try:
                    version = int(prefijo)
                except ValueError:
                    continue

                if version in versiones_aplicadas:
                    continue

                logger.info("Aplicando migración %d: %s...", version, archivo.name)
                contenido_sql = archivo.read_text(encoding="utf-8")

                async with conn.transaction():
                    await conn.execute(contenido_sql)
                    await conn.execute(
                        "INSERT INTO esquema_migraciones (version) VALUES (%s)",
                        (version,),
                    )
                aplicadas_ahora += 1
                logger.info("Migración %d aplicada con éxito.", version)

            return aplicadas_ahora
