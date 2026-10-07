"""Capa de persistencia asíncrona transaccional en PostgreSQL con asyncpg."""

import json
import logging
from collections import deque
from datetime import UTC, datetime
from typing import Any

import asyncpg

from detector.config import Settings
from detector.motor.protocolos import TransicionAlerta

logger = logging.getLogger("detector.persistencia")


class GestorPersistencia:
    """Gestiona el pool de conexiones a PostgreSQL, transacciones atómicas y cola de contingencia."""

    def __init__(self, settings: Settings) -> None:
        """Inicializa el gestor de persistencia."""
        self.settings = settings
        self.pool: asyncpg.Pool | None = None
        self.cola_contingencia: deque[TransicionAlerta] = deque(maxlen=50000)
        self.conectado: bool = False
        self.ultimo_error: str | None = None
        self.ultima_conexion: datetime | None = None

    async def conectar(self) -> bool:
        """Establece el pool de conexiones hacia PostgreSQL."""
        try:
            self.pool = await asyncpg.create_pool(
                host=self.settings.db_host,
                port=self.settings.db_port,
                user=self.settings.db_user,
                password=self.settings.db_password,
                database=self.settings.db_name,
                min_size=2,
                max_size=10,
                command_timeout=15.0,
            )
            self.conectado = True
            self.ultima_conexion = datetime.now(UTC)
            self.ultimo_error = None
            logger.info(
                "Conectado a PostgreSQL '%s' en %s:%d",
                self.settings.db_name,
                self.settings.db_host,
                self.settings.db_port,
            )
            # Intentar drenar cola de contingencia si tuviera elementos acumulados
            await self.drenar_contingencia()
            return True
        except Exception as e:
            self.conectado = False
            self.ultimo_error = str(e)
            logger.warning("No se pudo conectar a PostgreSQL: %s", e)
            return False

    async def cerrar(self) -> None:
        """Cierra de forma limpia el pool de conexiones."""
        if self.pool:
            await self.pool.close()
            self.pool = None
            self.conectado = False
            logger.info("Pool de PostgreSQL cerrado.")

    async def drenar_contingencia(self) -> None:
        """Drena y persiste transiciones acumuladas en la cola de contingencia."""
        if not self.pool or not self.cola_contingencia:
            return

        logger.info("Drenando %d transiciones acumuladas en contingencia...", len(self.cola_contingencia))
        while self.cola_contingencia:
            t = self.cola_contingencia[0]
            exito = await self._persistir_transicion_db(t)
            if exito:
                self.cola_contingencia.popleft()
            else:
                logger.warning("Fallo al drenar contingencia; se mantendrán %d pendientes", len(self.cola_contingencia))
                break

    async def guardar_transicion(self, t: TransicionAlerta) -> bool:
        """Guarda atómicamente la transición y actualiza la alerta en base de datos."""
        if not self.pool or not self.conectado:
            self.cola_contingencia.append(t)
            return False

        # Si hay elementos previos en la cola de contingencia, drenar primero para preservar orden
        if self.cola_contingencia:
            await self.drenar_contingencia()
            if self.cola_contingencia:
                self.cola_contingencia.append(t)
                return False

        exito = await self._persistir_transicion_db(t)
        if not exito:
            self.cola_contingencia.append(t)
        return exito

    async def _persistir_transicion_db(self, t: TransicionAlerta) -> bool:
        """Ejecuta la transacción SQL para escribir en alerta y transicion."""
        if not self.pool:
            return False

        alt = t.alerta
        try:
            abierta_dt = datetime.fromisoformat(alt.abierta_en.replace("Z", "+00:00"))
            actualizada_dt = datetime.fromisoformat(alt.actualizada_en.replace("Z", "+00:00"))
            resuelta_dt = datetime.fromisoformat(alt.resuelta_en.replace("Z", "+00:00")) if alt.resuelta_en else None
            evidencia_dt = t.en

            provincia = alt.nodo_info.provincia if alt.nodo_info else None
            provincias: list[str] = []
            if "provincias" in alt.datos and isinstance(alt.datos["provincias"], (list, tuple)):
                provincias = [str(p) for p in alt.datos["provincias"]]
            elif provincia:
                provincias = [provincia]

            nodo_info_json = alt.nodo_info.model_dump_json() if alt.nodo_info else None
            datos_json = json.dumps(alt.datos)
            alerta_json = alt.model_dump_json()

            async with self.pool.acquire() as conn, conn.transaction():
                # 1. Upsert en tabla alerta
                sql_alerta = """
                    INSERT INTO alerta (
                      id, regla, nodo, riesgo, tipo, mensaje, nodos, nodo_info,
                      provincia, provincias, datos, estado, abierta_en, actualizada_en,
                      resuelta_en, reaperturas, evidencia_en
                    ) VALUES (
                      $1, $2, $3, $4, $5, $6, $7, $8::jsonb,
                      $9, $10, $11::jsonb, $12, $13, $14,
                      $15, 0, $16
                    )
                    ON CONFLICT (id) DO UPDATE SET
                      riesgo = EXCLUDED.riesgo,
                      tipo = EXCLUDED.tipo,
                      mensaje = EXCLUDED.mensaje,
                      nodos = EXCLUDED.nodos,
                      nodo_info = EXCLUDED.nodo_info,
                      provincia = EXCLUDED.provincia,
                      provincias = EXCLUDED.provincias,
                      datos = EXCLUDED.datos,
                      estado = EXCLUDED.estado,
                      actualizada_en = EXCLUDED.actualizada_en,
                      resuelta_en = EXCLUDED.resuelta_en,
                      reaperturas = CASE
                        WHEN EXCLUDED.estado = 'abierta' AND alerta.estado = 'resuelta'
                        THEN alerta.reaperturas + 1
                        ELSE alerta.reaperturas
                      END,
                      evidencia_en = EXCLUDED.evidencia_en;
                    """
                await conn.execute(
                    sql_alerta,
                    alt.id,
                    alt.regla,
                    alt.nodo,
                    alt.riesgo,
                    alt.tipo,
                    alt.mensaje,
                    alt.nodos,
                    nodo_info_json,
                    provincia,
                    provincias,
                    datos_json,
                    alt.estado,
                    abierta_dt,
                    actualizada_dt,
                    resuelta_dt,
                    evidencia_dt,
                )

                # 2. Insert en tabla transicion
                sql_transicion = """
                    INSERT INTO transicion (id, alerta_id, transicion, en, alerta)
                    VALUES ($1, $2, $3, $4, $5::jsonb);
                    """
                await conn.execute(
                    sql_transicion,
                    t.transicion_id,
                    t.alerta_id,
                    t.transicion,
                    t.en,
                    alerta_json,
                )

            self.conectado = True
            return True
        except Exception as e:
            self.conectado = False
            self.ultimo_error = str(e)
            logger.error("Error al persistir transición %s en base de datos: %s", t.transicion_id, e)
            return False

    async def obtener_transiciones_reenvio(
        self, desde: str, max_horas: int = 24
    ) -> list[tuple[str, str, dict[str, Any]]]:
        """Recupera transiciones históricas posteriores a 'desde' dentro de la ventana de 24 horas."""
        if not self.pool:
            return []

        query = """
        SELECT id, transicion, alerta
        FROM transicion
        WHERE id > $1 AND en >= now() - make_interval(hours => $2)
        ORDER BY id ASC;
        """
        try:
            async with self.pool.acquire() as conn:
                filas = await conn.fetch(query, desde, max_horas)
                resultado: list[tuple[str, str, dict[str, Any]]] = []
                for f in filas:
                    alerta_val = f["alerta"]
                    alerta_dict = json.loads(alerta_val) if isinstance(alerta_val, str) else dict(alerta_val)
                    resultado.append((f["id"], f["transicion"], alerta_dict))
                return resultado
        except Exception as e:
            logger.error("Error al consultar reenvío de transiciones desde '%s': %s", desde, e)
            return []

    async def guardar_snapshot(self, formato: int, nodos: int, datos_gz: bytes) -> bool:
        """Guarda una instantánea periódica del estado del motor en PostgreSQL."""
        if not self.pool:
            return False
        query = """
        INSERT INTO estado_snapshot (formato, nodos, datos)
        VALUES ($1, $2, $3);
        """
        try:
            async with self.pool.acquire() as conn:
                await conn.execute(query, formato, nodos, datos_gz)
            return True
        except Exception as e:
            logger.error("Error al guardar snapshot en PostgreSQL: %s", e)
            return False

    async def cargar_ultimo_snapshot(self) -> bytes | None:
        """Carga el último snapshot guardado en PostgreSQL."""
        if not self.pool:
            return None
        query = """
        SELECT datos FROM estado_snapshot
        ORDER BY creado_en DESC
        LIMIT 1;
        """
        try:
            async with self.pool.acquire() as conn:
                fila = await conn.fetchrow(query)
                if fila and fila["datos"]:
                    return bytes(fila["datos"])
                return None
        except Exception as e:
            logger.warning("No se pudo cargar el snapshot de base de datos: %s", e)
            return None

    async def purgar_retencion(self, retencion_dias: int = 365) -> tuple[int, int]:
        """Purga alertas resueltas con más de retencion_dias y retiene solo los 3 últimos snapshots."""
        if not self.pool:
            return 0, 0

        alertas_purgadas = 0
        snapshots_purgados = 0

        query_alertas = """
        DELETE FROM alerta
        WHERE estado = 'resuelta'
          AND resuelta_en < now() - make_interval(days => $1);
        """
        query_snapshots = """
        DELETE FROM estado_snapshot
        WHERE id NOT IN (
          SELECT id FROM estado_snapshot ORDER BY creado_en DESC LIMIT 3
        );
        """
        try:
            async with self.pool.acquire() as conn:
                tag_a = await conn.execute(query_alertas, retencion_dias)
                # Formato tag: "DELETE <count>"
                partes_a = tag_a.split()
                if len(partes_a) == 2 and partes_a[1].isdigit():
                    alertas_purgadas = int(partes_a[1])

                tag_s = await conn.execute(query_snapshots)
                partes_s = tag_s.split()
                if len(partes_s) == 2 and partes_s[1].isdigit():
                    snapshots_purgados = int(partes_s[1])

            logger.info(
                "Retención ejecutada: %d alertas resueltas purgadas, %d snapshots antiguos purgados",
                alertas_purgadas,
                snapshots_purgados,
            )
            return alertas_purgadas, snapshots_purgados
        except Exception as e:
            logger.error("Error al purgar retención en PostgreSQL: %s", e)
            return 0, 0
