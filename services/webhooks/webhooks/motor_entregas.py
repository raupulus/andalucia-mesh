"""Motor de evaluación de transiciones, cola persistente FIFO y envío de webhooks."""

import asyncio
import contextlib
import logging
import time
from datetime import UTC, datetime, timedelta
from typing import Any

import aiohttp
from psycopg import AsyncConnection

from nucleo.base import GestorBase
from webhooks.configuracion import ConfiguracionWebhooks, DestinoWebhook
from webhooks.cuerpo import calcular_firma_hmac, construir_cuerpo_webhook
from webhooks.red_segura import resolver_y_validar_host

logger = logging.getLogger("webhooks.motor_entregas")


class MotorEntregas:
    """Orquesta la sincronización de destinos, evaluación de filtros y despacho FIFO."""

    def __init__(
        self,
        config: ConfiguracionWebhooks,
        gestor_base: GestorBase,
        destinos: list[DestinoWebhook],
    ) -> None:
        """Inicializa el motor de entregas de webhooks."""
        self.config = config
        self.gestor_base = gestor_base
        self.destinos_map: dict[str, DestinoWebhook] = {d.nombre: d for d in destinos}
        self.redes_bloqueadas = config.parsear_redes_bloqueadas()

        self._tarea_despacho: asyncio.Task[None] | None = None
        self._evento_despertar = asyncio.Event()
        self._deteniendo = False

        # Concurrencia y FIFO por destino
        self._destinos_en_vuelo: set[int] = set()
        self._semaforo_global = asyncio.Semaphore(config.webhooks_concurrencia)
        self.session: aiohttp.ClientSession | None = None

    async def iniciar(self) -> None:
        """Abre la sesión HTTP, sincroniza destinos y arranca el despachador."""
        self._deteniendo = False

        # Crear sesión HTTP segura sin proxies
        timeout = aiohttp.ClientTimeout(
            total=float(self.config.webhooks_timeout_s),
            connect=5.0,
        )
        self.session = aiohttp.ClientSession(
            timeout=timeout,
            trust_env=False,
        )

        await self.sincronizar_destinos_db()
        await self.recargar_destinos_db()
        self._tarea_despacho = asyncio.create_task(self._bucle_despacho())
        logger.info("Motor de entregas de webhooks iniciado con %d destinos.", len(self.destinos_map))

    async def detener(self) -> None:
        """Detiene de forma limpia el motor de entregas."""
        self._deteniendo = True
        self._evento_despertar.set()
        if self._tarea_despacho:
            self._tarea_despacho.cancel()
            with contextlib.suppress(asyncio.CancelledError):
                await self._tarea_despacho
            self._tarea_despacho = None

        if self.session:
            await self.session.close()
            self.session = None

        logger.info("Motor de entregas de webhooks detenido.")

    def despertar(self) -> None:
        """Despierta el bucle de entrega."""
        self._evento_despertar.set()

    async def sincronizar_destinos_db(self) -> None:
        """Sincroniza los destinos de webhooks.yaml con la tabla destino sin alterar los creados por API."""
        if not self.destinos_map:
            return

        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            # Consultar destinos existentes en base de datos
            await cur.execute("SELECT id, nombre, url_hash, activo, motivo_baja FROM destino")
            existentes_db = {fila[1]: fila for fila in await cur.fetchall()}

            # Procesar destinos cargados desde webhooks.yaml
            for nombre, d in self.destinos_map.items():
                if nombre in existentes_db:
                    _db_id, _db_nom, db_hash, db_activo, db_motivo = existentes_db[nombre]
                    cambio_url = db_hash != d.url_hash

                    # Reactivar si estaba 'retirado' o si cambió la URL en fallos/gone
                    debe_reactivar = (db_motivo == "retirado") or (cambio_url and not db_activo)
                    if debe_reactivar:
                        await cur.execute(
                            """
                            UPDATE destino
                            SET host = %s, url_hash = %s, url = %s, secreto = %s,
                                riesgos = %s, tipos = %s, provincias = %s, nodos = %s,
                                activo = true, motivo_baja = NULL,
                                fallos_seguidos = 0, actualizado_en = now()
                            WHERE nombre = %s
                            """,
                            (d.host, d.url_hash, d.url, d.secreto, d.riesgos, d.tipos, d.provincias, d.nodos, nombre),
                        )
                    else:
                        await cur.execute(
                            """
                            UPDATE destino
                            SET host = %s, url_hash = %s, url = %s, secreto = %s,
                                riesgos = %s, tipos = %s, provincias = %s, nodos = %s,
                                actualizado_en = now()
                            WHERE nombre = %s
                            """,
                            (d.host, d.url_hash, d.url, d.secreto, d.riesgos, d.tipos, d.provincias, d.nodos, nombre),
                        )
                else:
                    # Insertar nuevo destino desde YAML
                    await cur.execute(
                        """
                        INSERT INTO destino (
                            nombre, host, url, url_hash, riesgos, tipos, provincias, nodos, secreto,
                            activo, alta_en, actualizado_en
                        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, true, now(), now())
                        """,
                        (nombre, d.host, d.url, d.url_hash, d.riesgos, d.tipos, d.provincias, d.nodos, d.secreto),
                    )

    async def recargar_destinos_db(self) -> None:
        """Carga o actualiza en memoria todos los destinos activos desde la base de datos."""
        async with self.gestor_base.conexion() as conn, conn.cursor() as cur:
            await cur.execute(
                """
                SELECT nombre, url, host, url_hash, riesgos, tipos, provincias, nodos, secreto
                FROM destino
                WHERE activo = true
                """
            )
            filas = await cur.fetchall()

        nuevo_map: dict[str, DestinoWebhook] = {}
        for nombre, url, host, url_hash, riesgos, tipos, provincias, nodos, secreto in filas:
            nuevo_map[nombre] = DestinoWebhook(
                nombre=nombre,
                url=url,
                host=host,
                url_hash=url_hash,
                riesgos=list(riesgos) if riesgos else None,
                tipos=list(tipos) if tipos else None,
                provincias=list(provincias) if provincias else None,
                nodos=list(nodos) if nodos else None,
                secreto=secreto or "",
            )
        self.destinos_map = nuevo_map
        logger.info("Caché en memoria de destinos actualizada: %d destinos activos.", len(self.destinos_map))

    async def procesar_transicion(
        self,
        datos_transicion: dict[str, Any],
        conn: AsyncConnection[Any],
    ) -> None:
        """Evalúa una transición entrante y encola las entregas que cumplan los filtros."""
        transicion_id = str(datos_transicion.get("transicion_id", ""))
        transicion = str(datos_transicion.get("transicion", "abierta"))
        alerta = datos_transicion.get("alerta") or {}
        alerta_id = str(alerta.get("id", ""))
        riesgo = str(alerta.get("riesgo", "")).lower()
        tipo = str(alerta.get("tipo", "")).lower()
        nodo = str(alerta.get("nodo", ""))
        nodos_lista = alerta.get("nodos") or []
        nodo_info = alerta.get("nodo_info") or {}
        provincia = nodo_info.get("provincia")

        # Consultar destinos activos en base de datos
        async with conn.cursor() as cur:
            await cur.execute("SELECT id, nombre FROM destino WHERE activo = true")
            destinos_activos_db = await cur.fetchall()

        for dest_id, dest_nombre in destinos_activos_db:
            cfg_destino = self.destinos_map.get(dest_nombre)
            if not cfg_destino:
                continue

            # 1. Evaluar filtros
            if cfg_destino.riesgos and riesgo not in cfg_destino.riesgos:
                continue
            if cfg_destino.tipos and tipo not in cfg_destino.tipos:
                continue
            if cfg_destino.provincias and provincia and provincia not in cfg_destino.provincias:
                continue
            if cfg_destino.nodos:
                coincide_nodo = (nodo in cfg_destino.nodos) or any(n in cfg_destino.nodos for n in nodos_lista)
                if not coincide_nodo:
                    continue

            # 2. Comprobar entregas previas para esta alerta
            async with conn.cursor() as cur:
                await cur.execute(
                    """
                    SELECT count(*) FROM entrega
                    WHERE destino_id = %s AND alerta_id = %s
                    """,
                    (dest_id, alerta_id),
                )
                fila_cnt = await cur.fetchone()
                tiene_previas = (fila_cnt[0] > 0) if fila_cnt else False

            # 3. Tabla de decisión (§5.2)
            debe_entregar = False
            first_delivery = False

            if not tiene_previas:
                if transicion == "abierta" or transicion == "actualizada":
                    debe_entregar = True
                    first_delivery = True
                elif transicion == "resuelta":
                    debe_entregar = False
            else:
                if transicion in ("abierta", "actualizada", "resuelta"):
                    debe_entregar = True
                    first_delivery = False

            if not debe_entregar:
                continue

            # 4. Construir cuerpo serializado
            cuerpo_str, _ = construir_cuerpo_webhook(
                transicion_id=transicion_id,
                transicion_es=transicion,
                first_delivery=first_delivery,
                alerta_dict=alerta,
                dominio_proyecto=self.config.project_domain,
            )

            # 5. Encolar entrega
            async with conn.cursor() as cur:
                await cur.execute(
                    """
                    INSERT INTO entrega (
                        destino_id, alerta_id, transicion_id, transicion, regla,
                        riesgo, tipo, cuerpo, estado, programado_para
                    ) VALUES (
                        %s, %s, %s, %s, %s, %s, %s, %s, 'pendiente', now()
                    )
                    ON CONFLICT (destino_id, transicion_id) DO NOTHING
                    """,
                    (
                        dest_id,
                        alerta_id,
                        transicion_id,
                        transicion,
                        str(alerta.get("regla", "")),
                        riesgo,
                        tipo,
                        cuerpo_str,
                    ),
                )

        self.despertar()

    async def _bucle_despacho(self) -> None:
        """Bucle principal de consumo y entrega respetando concurrencia y FIFO por destino."""
        while not self._deteniendo:
            try:
                enviadas = await self._despachar_lote()
                espera = 0.5 if enviadas > 0 else 3.0
                with contextlib.suppress(TimeoutError):
                    await asyncio.wait_for(self._evento_despertar.wait(), timeout=espera)
                self._evento_despertar.clear()
            except asyncio.CancelledError:
                break
            except Exception as e:
                logger.error("Error en bucle de despacho de webhooks: %s", e, exc_info=True)
                await asyncio.sleep(2.0)

    async def _despachar_lote(self) -> int:
        """Selecciona las próximas entregas respetando la exclusión mutua por destino."""
        ahora = datetime.now(UTC)

        async with self.gestor_base.conexion() as conn, conn.cursor() as cur:
            await cur.execute(
                """
                SELECT DISTINCT ON (e.destino_id)
                    e.id, e.destino_id, d.nombre, e.cuerpo, e.transicion_id,
                    e.intentos, e.creado_en
                FROM entrega e
                JOIN destino d ON d.id = e.destino_id
                WHERE e.estado = 'pendiente'
                  AND e.programado_para <= %s
                  AND d.activo = true
                ORDER BY e.destino_id, e.programado_para ASC, e.id ASC
                LIMIT 20
                """,
                (ahora,),
            )
            candidatos = await cur.fetchall()

        disparados = 0
        for ent_id, dest_id, dest_nom, cuerpo, trans_id, intentos, creado_en in candidatos:
            # Garantizar FIFO: no procesar si ya hay una entrega en vuelo para este destino
            if dest_id in self._destinos_en_vuelo:
                continue

            # Comprobar caducidad de 24 horas
            if ahora - creado_en > timedelta(hours=24):
                await self._marcar_estado(ent_id, "caducada", ultimo_error="Caducada tras 24 h en cola")
                continue

            cfg_destino = self.destinos_map.get(dest_nom)
            if not cfg_destino:
                continue

            self._destinos_en_vuelo.add(dest_id)
            asyncio.create_task(
                self._ejecutar_entrega(
                    entrega_id=ent_id,
                    destino_id=dest_id,
                    cfg_destino=cfg_destino,
                    cuerpo_str=cuerpo,
                    transicion_id=trans_id,
                    intentos_previos=intentos,
                )
            )
            disparados += 1

        return disparados

    async def _ejecutar_entrega(
        self,
        entrega_id: int,
        destino_id: int,
        cfg_destino: DestinoWebhook,
        cuerpo_str: str,
        transicion_id: str,
        intentos_previos: int,
    ) -> None:
        """Ejecuta el HTTP POST firmado con validación SSRF y gestiona el resultado."""
        assert self.session is not None
        cuerpo_bytes = cuerpo_str.encode("utf-8")
        firma_hmac = calcular_firma_hmac(cfg_destino.secreto, cuerpo_bytes)
        intento_actual = intentos_previos + 1

        cabeceras = {
            "Content-Type": "application/json; charset=utf-8",
            "User-Agent": (
                f"{self.config.project_name} webhooks/1.0.0 "
                f"(+https://{self.config.project_domain}/bots; {self.config.project_contact})"
            ),
            "X-SNM-Transicion": transicion_id,
            "X-SNM-Firma": firma_hmac,
            "X-SNM-Intento": str(intento_actual),
        }

        inicio = time.monotonic()
        try:
            # 1. Resolución segura y validación SSRF
            await resolver_y_validar_host(cfg_destino.host, self.redes_bloqueadas)

            # 2. Petición POST sin seguir redirecciones (allow_redirects=False)
            async with self.session.post(
                cfg_destino.url,
                data=cuerpo_bytes,
                headers=cabeceras,
                allow_redirects=False,
            ) as resp:
                duracion_ms = int((time.monotonic() - inicio) * 1000)
                codigo = resp.status

                if 200 <= codigo < 300:
                    # Éxito: marcar entregada y limpiar fallos del destino
                    await self._marcar_entregada(entrega_id, destino_id, codigo, duracion_ms)
                elif codigo == 410:
                    # 410 Gone: dar de baja inmediata al destino
                    logger.info("Destino %s respondió 410 Gone. Desactivando destino.", cfg_destino.nombre)
                    await self._marcar_estado(entrega_id, "fallida", codigo=410, duracion_ms=duracion_ms)
                    await self._desactivar_destino(destino_id, "gone")
                elif codigo in (408, 429) or codigo >= 500:
                    # Reintentable
                    retry_after = None
                    ra_hdr = resp.headers.get("Retry-After")
                    if ra_hdr and ra_hdr.isdigit():
                        retry_after = float(ra_hdr)
                    await self._manejar_error_reintentable(
                        entrega_id, destino_id, intento_actual, codigo, f"HTTP {codigo}", retry_after
                    )
                else:
                    # Otros 4xx o 3xx: fallo terminal sin reintento
                    await self._marcar_estado(
                        entrega_id,
                        "fallida",
                        codigo=codigo,
                        duracion_ms=duracion_ms,
                        ultimo_error=f"HTTP {codigo}",
                    )
                    await self._incrementar_fallos_destino(destino_id)

        except ValueError as err_ssrf:
            # Error de red o SSRF
            logger.warning("Entrega rechazada para %s por regla de red segura: %s", cfg_destino.nombre, err_ssrf)
            duracion_ms = int((time.monotonic() - inicio) * 1000)
            await self._marcar_estado(
                entrega_id,
                "fallida",
                duracion_ms=duracion_ms,
                ultimo_error=f"ip_no_permitida: {err_ssrf}",
            )
            await self._incrementar_fallos_destino(destino_id)

        except Exception as e:
            # Timeout, red, DNS
            duracion_ms = int((time.monotonic() - inicio) * 1000)
            await self._manejar_error_reintentable(
                entrega_id, destino_id, intento_actual, None, str(e), None
            )

        finally:
            self._destinos_en_vuelo.discard(destino_id)

    async def _manejar_error_reintentable(
        self,
        entrega_id: int,
        destino_id: int,
        intento_actual: int,
        codigo: int | None,
        error_msg: str,
        retry_after: float | None,
    ) -> None:
        """Gestiona el escalonamiento de reintentos (10s, 60s, 300s) o fallo definitivo."""
        escalera_reintentos = [10.0, 60.0, 300.0]

        if intento_actual <= 3:
            espera = retry_after if (retry_after is not None and retry_after <= 300.0) else escalera_reintentos[intento_actual - 1]
            proximo_intento = datetime.now(UTC) + timedelta(seconds=espera)
            async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
                await cur.execute(
                    """
                    UPDATE entrega
                    SET programado_para = %s,
                        intentos = %s,
                        ultimo_codigo = %s,
                        ultimo_error = %s
                    WHERE id = %s
                    """,
                    (proximo_intento, intento_actual, codigo, error_msg[:500], entrega_id),
                )
        else:
            # Agotados los 4 intentos -> marcar fallida
            await self._marcar_estado(
                entrega_id,
                "fallida",
                codigo=codigo,
                ultimo_error=f"Agotados reintentos: {error_msg}",
            )
            await self._incrementar_fallos_destino(destino_id)

    async def _marcar_entregada(
        self,
        entrega_id: int,
        destino_id: int,
        codigo: int,
        duracion_ms: int,
    ) -> None:
        """Actualiza el estado de la entrega a 'entregada' y reinicia contador de fallos."""
        ahora = datetime.now(UTC)
        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                """
                UPDATE entrega
                SET estado = 'entregada',
                    entregado_en = %s,
                    ultimo_codigo = %s,
                    duracion_ms = %s,
                    ultimo_error = NULL
                WHERE id = %s
                """,
                (ahora, codigo, duracion_ms, entrega_id),
            )
            await cur.execute(
                """
                UPDATE destino
                SET fallos_seguidos = 0,
                    ultimo_ok = %s
                WHERE id = %s
                """,
                (ahora, destino_id),
            )

    async def _marcar_estado(
        self,
        entrega_id: int,
        estado: str,
        codigo: int | None = None,
        duracion_ms: int | None = None,
        ultimo_error: str | None = None,
    ) -> None:
        """Actualiza el estado terminal de una entrega."""
        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                """
                UPDATE entrega
                SET estado = %s,
                    ultimo_codigo = COALESCE(%s, ultimo_codigo),
                    duracion_ms = COALESCE(%s, duracion_ms),
                    ultimo_error = %s
                WHERE id = %s
                """,
                (estado, codigo, duracion_ms, ultimo_error[:500] if ultimo_error else None, entrega_id),
            )

    async def _incrementar_fallos_destino(self, destino_id: int) -> None:
        """Incrementa fallos_seguidos y desactiva el destino si alcanza el umbral."""
        ahora = datetime.now(UTC)
        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                """
                UPDATE destino
                SET fallos_seguidos = fallos_seguidos + 1,
                    actualizado_en = %s
                WHERE id = %s
                RETURNING fallos_seguidos
                """,
                (ahora, destino_id),
            )
            fila = await cur.fetchone()
            fallos = fila[0] if fila else 0

            if fallos >= self.config.webhooks_fallos_desactivar:
                logger.warning(
                    "Destino %d ha alcanzado %d fallos seguidos. Desactivando por fallos.",
                    destino_id,
                    fallos,
                )
                await cur.execute(
                    """
                    UPDATE destino
                    SET activo = false,
                        motivo_baja = 'fallos',
                        baja_en = %s
                    WHERE id = %s
                    """,
                    (ahora, destino_id),
                )
                await cur.execute(
                    """
                    UPDATE entrega
                    SET estado = 'caducada',
                        ultimo_error = 'Destino desactivado por fallos reiterados'
                    WHERE destino_id = %s AND estado = 'pendiente'
                    """,
                    (destino_id,),
                )

    async def _desactivar_destino(self, destino_id: int, motivo: str) -> None:
        """Desactiva un destino y pasa sus entregas pendientes a caducadas."""
        ahora = datetime.now(UTC)
        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                """
                UPDATE destino
                SET activo = false,
                    motivo_baja = %s,
                    baja_en = %s,
                    actualizado_en = %s
                WHERE id = %s
                """,
                (motivo, ahora, ahora, destino_id),
            )
            await cur.execute(
                """
                UPDATE entrega
                SET estado = 'caducada',
                    ultimo_error = 'Destino desactivado'
                WHERE destino_id = %s AND estado = 'pendiente'
                """,
                (destino_id,),
            )
