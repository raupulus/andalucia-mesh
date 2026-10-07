"""Cliente del socket UNIX de alertas emitidas por el detector."""

import asyncio
import contextlib
import json
import logging
import random
from collections.abc import Awaitable, Callable
from datetime import UTC, datetime
from typing import Any

from psycopg import AsyncConnection

from nucleo.base import GestorBase

logger = logging.getLogger("nucleo.socket_alertas")


class ClienteSocketAlertas:
    """Cliente asíncrono para consumir /run/snm/alertas.sock con control de cursor y latidos."""

    def __init__(
        self,
        ruta_socket: str,
        nombre_cliente: str,
        gestor_base: GestorBase,
        manejador_transicion: Callable[[dict[str, Any], AsyncConnection[Any]], Awaitable[None]],
    ) -> None:
        """Inicializa el cliente de alertas."""
        self.ruta_socket = ruta_socket
        self.nombre_cliente = nombre_cliente
        self.gestor_base = gestor_base
        self.manejador_transicion = manejador_transicion

        self.conectado: bool = False
        self.ultima_linea_at: datetime | None = None
        self.cursor_actual: str | None = None
        self._tarea: asyncio.Task[None] | None = None
        self._deteniendo: bool = False

    async def obtener_cursor_db(self, conn: AsyncConnection[Any]) -> str | None:
        """Lee el último transicion_id procesado desde la base de datos."""
        async with conn.cursor() as cur:
            await cur.execute(
                "SELECT transicion_id FROM cursor WHERE cliente = %s",
                (self.nombre_cliente,),
            )
            fila = await cur.fetchone()
            return str(fila[0]) if fila and fila[0] else None

    async def guardar_cursor_db(self, conn: AsyncConnection[Any], transicion_id: str) -> None:
        """Actualiza el cursor del cliente en la base de datos."""
        async with conn.cursor() as cur:
            await cur.execute(
                """
                INSERT INTO cursor (cliente, transicion_id, actualizado_en)
                VALUES (%s, %s, now())
                ON CONFLICT (cliente) DO UPDATE
                SET transicion_id = EXCLUDED.transicion_id,
                    actualizado_en = EXCLUDED.actualizado_en
                """,
                (self.nombre_cliente, transicion_id),
            )
        self.cursor_actual = transicion_id

    async def iniciar(self) -> None:
        """Inicia el bucle de conexión en segundo plano."""
        self._deteniendo = False
        self._tarea = asyncio.create_task(self._bucle_conexion())
        logger.info("Cliente de socket de alertas iniciado para %s.", self.nombre_cliente)

    async def detener(self) -> None:
        """Detiene de forma limpia el bucle de conexión."""
        self._deteniendo = True
        if self._tarea:
            self._tarea.cancel()
            with contextlib.suppress(asyncio.CancelledError):
                await self._tarea
            self._tarea = None
        self.conectado = False
        logger.info("Cliente de socket de alertas detenido.")

    async def _bucle_conexion(self) -> None:
        """Bucle principal de conexión, reconexión con retroceso exponencial y consumo."""
        intervalos = [1.0, 2.0, 4.0, 8.0, 16.0, 30.0]
        indice_intervalo = 0

        while not self._deteniendo:
            reader: asyncio.StreamReader | None = None
            writer: asyncio.StreamWriter | None = None
            try:
                # Leer cursor actual de la base de datos
                async with self.gestor_base.conexion() as conn:
                    self.cursor_actual = await self.obtener_cursor_db(conn)

                logger.info(
                    "Conectando al socket UNIX %s (cliente=%s, cursor=%s)...",
                    self.ruta_socket,
                    self.nombre_cliente,
                    self.cursor_actual,
                )

                # Abrir conexión UNIX con límite de línea de 1 MiB
                reader, writer = await asyncio.open_unix_connection(
                    self.ruta_socket,
                    limit=1024 * 1024,
                )

                # Saludo inmediato con el cursor
                saludo = json.dumps(
                    {"cliente": self.nombre_cliente, "desde": self.cursor_actual},
                    ensure_ascii=False,
                )
                writer.write(f"{saludo}\n".encode())
                await writer.drain()

                self.conectado = True
                indice_intervalo = 0  # Éxito: reiniciar intervalo de retroceso
                logger.info("Conectado con éxito al socket de alertas.")

                # Consumo de líneas con temporizador de latido de 90 segundos
                while not self._deteniendo:
                    try:
                        linea_bytes = await asyncio.wait_for(reader.readline(), timeout=90.0)
                    except TimeoutError:
                        logger.warning("Sin líneas en 90s desde el socket de alertas. Reconectando...")
                        break

                    if not linea_bytes:
                        logger.warning("EOF recibido en socket de alertas. Reconectando...")
                        break

                    self.ultima_linea_at = datetime.now(UTC)
                    linea_str = linea_bytes.decode("utf-8").strip()
                    if not linea_str:
                        continue

                    try:
                        datos = json.loads(linea_str)
                    except json.JSONDecodeError as err:
                        logger.error("JSON roto recibido en socket: %s", err)
                        continue

                    # Comprobar si es latido
                    if "latido" in datos:
                        continue

                    # Procesar evento de transición
                    await self._procesar_evento(datos)

            except (ConnectionRefusedError, FileNotFoundError, OSError) as e:
                logger.warning("No se pudo conectar al socket UNIX %s: %s", self.ruta_socket, e)
            except asyncio.CancelledError:
                break
            except Exception as e:
                logger.error("Error inesperado en cliente de socket de alertas: %s", e, exc_info=True)
            finally:
                self.conectado = False
                if writer:
                    try:
                        writer.close()
                        await writer.wait_closed()
                    except Exception:
                        pass

            if self._deteniendo:
                break

            base_espera = intervalos[min(indice_intervalo, len(intervalos) - 1)]
            variacion = base_espera * random.uniform(-0.20, 0.20)
            tiempo_espera = max(0.5, base_espera + variacion)
            indice_intervalo += 1

            logger.info("Reintentando conexión al socket en %.1f segundos...", tiempo_espera)
            await asyncio.sleep(tiempo_espera)

    async def _procesar_evento(self, datos: dict[str, Any]) -> None:
        """Valida y procesa una transición de alerta asegurando atomicidad con el cursor."""
        v = datos.get("v")
        transicion_id = datos.get("transicion_id")
        transicion = datos.get("transicion")
        alerta = datos.get("alerta")

        # Validación según contrato UT-08.3
        campos_validos = (
            v == 1
            and isinstance(transicion_id, str)
            and transicion in ("abierta", "actualizada", "resuelta")
            and isinstance(alerta, dict)
            and all(k in alerta for k in ("id", "regla", "riesgo", "tipo", "mensaje", "nodo"))
        )

        if not campos_validos or not transicion_id:
            logger.error("Línea de transición inválida recibida en socket: %s", datos)
            # Avanzar el cursor para evitar que una línea malformada bloquee la cola
            if transicion_id:
                async with self.gestor_base.conexion() as conn, conn.transaction():
                    await self.guardar_cursor_db(conn, transicion_id)
            return

        # Procesar la transición atómicamente con el avance del cursor
        async with self.gestor_base.conexion() as conn, conn.transaction():
            await self.manejador_transicion(datos, conn)
            await self.guardar_cursor_db(conn, transicion_id)
