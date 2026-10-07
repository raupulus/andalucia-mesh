"""Gestor de base de datos PostgreSQL (peersync) para cursores de sincronización.

Mantiene el estado persistente y transaccional de los cursores temporales
de mensajes, trazas y nodos para cada peer.
"""

from __future__ import annotations

import logging
from datetime import datetime, timezone
from typing import Any

import asyncpg

logger = logging.getLogger("sync-peers.db")

SCHEMA_SQL = """
CREATE TABLE IF NOT EXISTS peer_cursor (
    peer_id TEXT PRIMARY KEY,
    last_message_time TIMESTAMPTZ,
    last_trace_time TIMESTAMPTZ,
    last_node_sync TIMESTAMPTZ,
    ultimo_ok TIMESTAMPTZ,
    fallos_consecutivos INT DEFAULT 0,
    ultimo_error TEXT,
    actualizado TIMESTAMPTZ DEFAULT now()
);
"""


async def init_db(pool: asyncpg.Pool) -> None:
    """Aplica las migraciones iniciales de esquema en la base de datos."""
    async with pool.acquire() as conn:
        await conn.execute(SCHEMA_SQL)
    logger.info("Esquema de base de datos peersync verificado/inicializado.")


async def get_or_create_cursor(pool: asyncpg.Pool, peer_id: str) -> dict[str, Any]:
    """Obtiene el registro de cursor para un peer, creándolo si no existe."""
    query = """
    INSERT INTO peer_cursor (peer_id)
    VALUES ($1)
    ON CONFLICT (peer_id) DO NOTHING;
    """
    select_query = """
    SELECT peer_id, last_message_time, last_trace_time, last_node_sync,
           ultimo_ok, fallos_consecutivos, ultimo_error, actualizado
    FROM peer_cursor
    WHERE peer_id = $1;
    """
    async with pool.acquire() as conn:
        await conn.execute(query, peer_id)
        row = await conn.fetchrow(select_query, peer_id)
        return dict(row) if row else {"peer_id": peer_id}


async def update_cursor_messages(
    pool: asyncpg.Pool, peer_id: str, new_time: datetime
) -> None:
    """Actualiza de forma atómica el cursor de mensajes."""
    query = """
    UPDATE peer_cursor
    SET last_message_time = $2, actualizado = now()
    WHERE peer_id = $1;
    """
    async with pool.acquire() as conn:
        await conn.execute(query, peer_id, new_time)


async def update_cursor_traces(
    pool: asyncpg.Pool, peer_id: str, new_time: datetime
) -> None:
    """Actualiza de forma atómica el cursor de trazas."""
    query = """
    UPDATE peer_cursor
    SET last_trace_time = $2, actualizado = now()
    WHERE peer_id = $1;
    """
    async with pool.acquire() as conn:
        await conn.execute(query, peer_id, new_time)


async def update_cursor_nodes(
    pool: asyncpg.Pool, peer_id: str, new_time: datetime
) -> None:
    """Actualiza de forma atómica el cursor de nodos."""
    query = """
    UPDATE peer_cursor
    SET last_node_sync = $2, actualizado = now()
    WHERE peer_id = $1;
    """
    async with pool.acquire() as conn:
        await conn.execute(query, peer_id, new_time)


async def record_peer_success(pool: asyncpg.Pool, peer_id: str) -> None:
    """Registra la conclusión exitosa de un ciclo de sincronización."""
    query = """
    UPDATE peer_cursor
    SET ultimo_ok = now(), fallos_consecutivos = 0, ultimo_error = NULL, actualizado = now()
    WHERE peer_id = $1;
    """
    async with pool.acquire() as conn:
        await conn.execute(query, peer_id)


async def record_peer_failure(
    pool: asyncpg.Pool, peer_id: str, error_msg: str
) -> None:
    """Registra un fallo en la sincronización e incrementa el contador de fallos consecutivos."""
    short_error = error_msg[:250] if error_msg else "Error desconocido"
    query = """
    UPDATE peer_cursor
    SET fallos_consecutivos = fallos_consecutivos + 1,
        ultimo_error = $2,
        actualizado = now()
    WHERE peer_id = $1;
    """
    async with pool.acquire() as conn:
        await conn.execute(query, peer_id, short_error)


async def get_all_peer_states(pool: asyncpg.Pool) -> list[dict[str, Any]]:
    """Recupera el estado de todos los peers registrados para el endpoint /health."""
    query = """
    SELECT peer_id, ultimo_ok, fallos_consecutivos, ultimo_error
    FROM peer_cursor
    ORDER BY peer_id;
    """
    async with pool.acquire() as conn:
        rows = await conn.fetch(query)
        res = []
        for r in rows:
            estado = "ok" if r["fallos_consecutivos"] == 0 else "caido"
            ult_ok_iso = (
                r["ultimo_ok"].isoformat() if r["ultimo_ok"] is not None else None
            )
            res.append(
                {
                    "id": r["peer_id"],
                    "estado": estado,
                    "ultimo_ok": ult_ok_iso,
                    "fallos_consecutivos": r["fallos_consecutivos"],
                }
            )
        return res
