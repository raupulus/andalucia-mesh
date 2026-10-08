"""Motor de evaluación de transiciones, cola persistente y envío anti-ruido para bots."""

import asyncio
import contextlib
import json
import logging
from datetime import UTC, datetime, timedelta
from typing import Any, Protocol

from psycopg import AsyncConnection

from nucleo.base import GestorBase
from nucleo.catalogo import GestorCatalogo
from nucleo.config import ConfiguracionBots
from nucleo.formato import AvisoNeutro, es_nodo_exterior, formatear_aviso_neutro, formatear_resumen_neutro

logger = logging.getLogger("nucleo.motor_envios")


class ErrorTransitorio(Exception):  # noqa: N818
    """Fallo temporal de red, servidor o límite 429 de la plataforma."""

    def __init__(self, mensaje: str = "", retry_after: float | None = None) -> None:
        super().__init__(mensaje)
        self.retry_after = retry_after


class ErrorDestino(Exception):  # noqa: N818
    """Error al entregar en el destino concreto (falta de permisos, etc.)."""

    pass


class DestinoPerdido(Exception):  # noqa: N818
    """El bot fue expulsado o el chat/canal ya no existe."""

    def __init__(self, motivo: str) -> None:
        super().__init__(motivo)
        self.motivo = motivo


class DestinoMigrado(Exception):  # noqa: N818
    """El grupo ha migrado a supergrupo con un nuevo identificador."""

    def __init__(self, nuevo_id: int) -> None:
        super().__init__(f"Migrado a {nuevo_id}")
        self.nuevo_id = nuevo_id


class AdaptadorPlataforma(Protocol):
    """Protocolo que debe implementar cada bot para serializar y emitir el aviso."""

    def serializar_contenido(self, aviso: AvisoNeutro) -> dict[str, Any]:
        """Convierte el aviso neutro en el dict específico de la plataforma."""
        ...

    async def enviar(
        self,
        plataforma_id: int,
        contenido: dict[str, Any],
        responder_a_mensaje_id: int | None,
        chat_envio_id_hilo: int | None,
    ) -> int:
        """Emite el mensaje a la API de la plataforma y retorna el identificador del mensaje."""
        ...


class MotorEnvios:
    """Orquesta la decisión por destino, agrupación de ráfagas y cola de envíos."""

    def __init__(
        self,
        config: ConfiguracionBots,
        gestor_base: GestorBase,
        catalogo: GestorCatalogo,
        adaptador: AdaptadorPlataforma,
    ) -> None:
        """Inicializa el motor de envíos."""
        self.config = config
        self.gestor_base = gestor_base
        self.catalogo = catalogo
        self.adaptador = adaptador

        self._tarea_despacho: asyncio.Task[None] | None = None
        self._evento_despertar = asyncio.Event()
        self._deteniendo = False

        # Limitadores en memoria
        self._ultimo_envio_por_destino: dict[int, datetime] = {}
        self._envios_recientes_destino: dict[int, list[datetime]] = {}
        self._envios_globales_segundo: list[datetime] = []

    async def iniciar(self) -> None:
        """Inicia el despachador de envíos en segundo plano."""
        self._deteniendo = False
        self._tarea_despacho = asyncio.create_task(self._bucle_despacho())
        logger.info("Motor de envíos iniciado.")

    async def detener(self) -> None:
        """Detiene de forma limpia el motor de envíos."""
        self._deteniendo = True
        self._evento_despertar.set()
        if self._tarea_despacho:
            self._tarea_despacho.cancel()
            with contextlib.suppress(asyncio.CancelledError):
                await self._tarea_despacho
            self._tarea_despacho = None
        logger.info("Motor de envíos detenido.")

    def despertar(self) -> None:
        """Despierta el bucle de envío si estaba esperando."""
        self._evento_despertar.set()

    async def procesar_transicion(
        self,
        datos_transicion: dict[str, Any],
        conn: AsyncConnection[Any],
    ) -> None:
        """Evalúa una transición contra todos los destinos activos y encola envíos."""
        transicion = datos_transicion.get("transicion", "abierta")
        transicion_id = str(datos_transicion.get("transicion_id", ""))
        alerta = datos_transicion.get("alerta", {})
        alerta_id = str(alerta.get("id", ""))
        regla = str(alerta.get("regla", ""))
        riesgo = str(alerta.get("riesgo", "medio")).lower()
        tipo = str(alerta.get("tipo", "infraestructura")).lower()
        nodo = str(alerta.get("nodo", ""))
        nodo_info = alerta.get("nodo_info") or {}
        es_exterior = es_nodo_exterior(nodo, nodo_info)
        etiqueta = str(nodo_info.get("corto") or nodo)

        # Consultar destinos activos
        async with conn.cursor() as cur:
            await cur.execute(
                """
                SELECT id, plataforma_id, clase, riesgos, tipos, incluir_exterior
                FROM destino
                WHERE activo = true
                """
            )
            destinos = await cur.fetchall()

        for dest_id, plat_id, _clase, filtros_riesgos, filtros_tipos, dest_exterior in destinos:
            riesgos_activos = filtros_riesgos or self.config.lista_riesgos_defecto
            tipos_activos = filtros_tipos or self.config.lista_tipos_defecto
            permite_exterior = (
                dest_exterior
                if dest_exterior is not None
                else self.config.bot_exterior_defecto
            )

            pasa_exterior = (not es_exterior) or permite_exterior
            pasa_filtros = (riesgo in riesgos_activos) and (tipo in tipos_activos) and pasa_exterior

            # Consultar si ya existe hilo para esta alerta en este destino
            async with conn.cursor() as cur:
                await cur.execute(
                    """
                    SELECT id, riesgo, transicion, inicia_hilo
                    FROM envio
                    WHERE destino_id = %s AND alerta_id = %s
                    ORDER BY id DESC
                    LIMIT 1
                    """,
                    (dest_id, alerta_id),
                )
                ultimo_envio = await cur.fetchone()

            tiene_hilo = ultimo_envio is not None
            riesgo_previo = ultimo_envio[1] if ultimo_envio else None
            transicion_previa = ultimo_envio[2] if ultimo_envio else None

            # Tabla de decisión según UT-08.5 y §4.6
            debe_enviar = False
            inicia_hilo = False
            es_reapertura = False

            if not tiene_hilo or transicion_previa == "resuelta":
                if transicion == "abierta" and pasa_filtros:
                    debe_enviar = True
                    inicia_hilo = not tiene_hilo
                    es_reapertura = tiene_hilo
                elif transicion == "actualizada" and pasa_filtros:
                    debe_enviar = True
                    inicia_hilo = True
                elif transicion == "resuelta":
                    debe_enviar = False
            else:
                if transicion == "abierta":
                    if pasa_filtros:
                        debe_enviar = True
                        es_reapertura = True
                elif transicion == "actualizada":
                    # Solo enviar si cambia el riesgo respecto al último enviado y pasa filtros
                    if riesgo_previo and riesgo != riesgo_previo and pasa_filtros:
                        debe_enviar = True
                elif transicion == "resuelta":
                    debe_enviar = True

            if not debe_enviar:
                continue

            # Comprobar agrupación por ráfaga (≥ 5 aperturas de la misma regla en 2 min)
            if inicia_hilo:
                ventana_at = datetime.now(UTC) - timedelta(seconds=self.config.bot_agrupar_ventana_s)
                async with conn.cursor() as cur:
                    await cur.execute(
                        """
                        SELECT count(*)
                        FROM envio
                        WHERE destino_id = %s
                          AND regla = %s
                          AND inicia_hilo = true
                          AND creado_en >= %s
                        """,
                        (dest_id, regla, ventana_at),
                    )
                    fila_cnt = await cur.fetchone()
                    total_aperturas = fila_cnt[0] if fila_cnt else 0

                if total_aperturas >= self.config.bot_agrupar_umbral:
                    # Encolar como agrupado
                    aviso_neutro = formatear_aviso_neutro(
                        datos_transicion,
                        self.catalogo,
                        self.config.project_domain,
                        self.config.tz,
                        riesgo_previo,
                        es_reapertura,
                    )
                    contenido_json = self.adaptador.serializar_contenido(aviso_neutro)

                    prog_para = datetime.now(UTC) + timedelta(seconds=self.config.bot_agrupar_ventana_s)
                    async with conn.cursor() as cur:
                        await cur.execute(
                            """
                            INSERT INTO envio (
                                destino_id, chat_envio_id, alerta_id, transicion_id,
                                transicion, inicia_hilo, regla, riesgo, tipo, etiqueta,
                                estado, programado_para, contenido
                            ) VALUES (
                                %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, 'agrupado', %s, %s
                            )
                            ON CONFLICT (destino_id, transicion_id) DO NOTHING
                            """,
                            (
                                dest_id,
                                plat_id,
                                alerta_id,
                                transicion_id,
                                transicion,
                                True,
                                regla,
                                riesgo,
                                tipo,
                                etiqueta,
                                prog_para,
                                json.dumps(contenido_json),
                            ),
                        )
                    continue

            # Construir aviso neutro y serializar
            aviso_neutro = formatear_aviso_neutro(
                datos_transicion,
                self.catalogo,
                self.config.project_domain,
                self.config.tz,
                riesgo_previo,
                es_reapertura,
            )
            contenido_json = self.adaptador.serializar_contenido(aviso_neutro)

            # Insertar en cola de envíos
            async with conn.cursor() as cur:
                await cur.execute(
                    """
                    INSERT INTO envio (
                        destino_id, chat_envio_id, alerta_id, transicion_id,
                        transicion, inicia_hilo, regla, riesgo, tipo, etiqueta,
                        estado, programado_para, contenido
                    ) VALUES (
                        %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, 'pendiente', now(), %s
                    )
                    ON CONFLICT (destino_id, transicion_id) DO NOTHING
                    """,
                    (
                        dest_id,
                        plat_id,
                        alerta_id,
                        transicion_id,
                        transicion,
                        inicia_hilo,
                        regla,
                        riesgo,
                        tipo,
                        etiqueta,
                        json.dumps(contenido_json),
                    ),
                )

        self.despertar()

    async def _bucle_despacho(self) -> None:
        """Bucle consumidor de la cola de envíos persistente."""
        while not self._deteniendo:
            try:
                # 1. Comprobar si hay resúmenes de ráfaga listos para emitir
                await self._procesar_resumenes_agrupados()

                # 2. Despachar envíos pendientes
                procesados = await self._despachar_pendientes()

                # Esperar 1 segundo o hasta nuevo aviso
                espera = 1.0 if procesados > 0 else 5.0
                with contextlib.suppress(TimeoutError):
                    await asyncio.wait_for(self._evento_despertar.wait(), timeout=espera)
                self._evento_despertar.clear()

            except asyncio.CancelledError:
                break
            except Exception as e:
                logger.error("Error en bucle de despacho de envíos: %s", e, exc_info=True)
                await asyncio.sleep(2.0)

    async def _procesar_resumenes_agrupados(self) -> None:
        """Convierte grupos de filas 'agrupado' vencidas en un aviso de resumen."""
        ahora = datetime.now(UTC)
        async with self.gestor_base.conexion() as conn:
            async with conn.cursor() as cur:
                await cur.execute(
                    """
                    SELECT destino_id, chat_envio_id, regla, count(*), array_agg(etiqueta)
                    FROM envio
                    WHERE estado = 'agrupado' AND resumen_id IS NULL AND programado_para <= %s
                    GROUP BY destino_id, chat_envio_id, regla
                    """,
                    (ahora,),
                )
                grupos = await cur.fetchall()

            for dest_id, plat_id, regla, cant, etiquetas in grupos:
                regla_nom = self.catalogo.nombre_regla(regla)
                aviso_resumen = formatear_resumen_neutro(
                    regla_id=regla,
                    regla_nombre=regla_nom,
                    etiquetas=list(etiquetas),
                    riesgo_maximo="alto",
                    dominio_proyecto=self.config.project_domain,
                )
                contenido_json = self.adaptador.serializar_contenido(aviso_resumen)

                async with conn.transaction(), conn.cursor() as cur:
                    # Insertar fila resumen pendiente
                    await cur.execute(
                        """
                            INSERT INTO envio (
                                destino_id, chat_envio_id, transicion, regla, riesgo, tipo,
                                etiqueta, estado, programado_para, contenido
                            ) VALUES (
                                %s, %s, 'resumen', %s, 'alto', 'infraestructura',
                                %s, 'pendiente', now(), %s
                            ) RETURNING id
                            """,
                        (
                            dest_id,
                            plat_id,
                            regla,
                            f"{cant} nodos",
                            json.dumps(contenido_json),
                        ),
                    )
                    fila_res = await cur.fetchone()
                    resumen_id = fila_res[0] if fila_res else None

                    # Vincular las filas agrupadas al resumen
                    await cur.execute(
                        """
                            UPDATE envio
                            SET resumen_id = %s
                            WHERE destino_id = %s AND regla = %s AND estado = 'agrupado' AND resumen_id IS NULL
                            """,
                        (resumen_id, dest_id, regla),
                    )

    async def _despachar_pendientes(self) -> int:
        """Toma y envía lotes de filas pendientes respetando FIFO y límites."""
        ahora = datetime.now(UTC)
        async with self.gestor_base.conexion() as conn, conn.cursor() as cur:
            await cur.execute(
                """
                    SELECT e.id, e.destino_id, e.chat_envio_id, e.alerta_id,
                           e.contenido, e.intentos, e.creado_en, d.plataforma_id
                    FROM envio e
                    JOIN destino d ON d.id = e.destino_id
                    WHERE e.estado = 'pendiente'
                      AND e.programado_para <= %s
                      AND d.activo = true
                    ORDER BY e.programado_para ASC, e.id ASC
                    LIMIT 25
                    """,
                (ahora,),
            )
            pendientes = await cur.fetchall()

        if not pendientes:
            return 0

        enviados = 0
        for env_id, dest_id, _chat_envio_id, alerta_id, contenido_raw, intentos, creado_en, plat_id in pendientes:
            # 1. Comprobar caducidad (24 h)
            if ahora - creado_en > timedelta(hours=24):
                await self._marcar_estado(env_id, "caducado", ultimo_error="Caducado tras 24 h en cola")
                continue

            # 2. Comprobar límite por destino (10/min y ≥ 1s entre mensajes)
            if not self._respetar_limite_destino(plat_id):
                continue

            # 3. Comprobar límite global (25/s)
            if not self._respetar_limite_global():
                break

            contenido = json.loads(contenido_raw) if isinstance(contenido_raw, str) else contenido_raw

            # 4. Buscar identificador de mensaje previo para hilo
            hilo_msg_id = None
            hilo_chat_id = None
            if alerta_id:
                async with self.gestor_base.conexion() as conn, conn.cursor() as cur:
                    await cur.execute(
                        """
                            SELECT mensaje_id, chat_envio_id
                            FROM envio
                            WHERE destino_id = %s AND alerta_id = %s AND mensaje_id IS NOT NULL AND estado = 'enviado'
                            ORDER BY id DESC
                            LIMIT 1
                            """,
                        (dest_id, alerta_id),
                    )
                    fila_hilo = await cur.fetchone()
                    if fila_hilo:
                        hilo_msg_id = fila_hilo[0]
                        hilo_chat_id = fila_hilo[1]

            # 5. Emitir a la plataforma
            try:
                msg_id = await self.adaptador.enviar(
                    plat_id,
                    contenido,
                    responder_a_mensaje_id=hilo_msg_id,
                    chat_envio_id_hilo=hilo_chat_id,
                )
                await self._marcar_enviado(env_id, dest_id, msg_id)
                self._registrar_envio_exitoso(plat_id)
                enviados += 1

            except ErrorTransitorio as et:
                espera = et.retry_after or min(2.0 ** intentos, 300.0)
                prog = ahora + timedelta(seconds=espera)
                logger.warning("Error transitorio en destino %d (reintento en %.1fs): %s", plat_id, espera, et)
                await self._reprogramar_intento(env_id, prog, intentos + 1, str(et))

            except ErrorDestino as ed:
                logger.warning("Error en destino %d: %s", plat_id, ed)
                if intentos >= 2:
                    await self._marcar_estado(env_id, "fallido", ultimo_error=str(ed))
                    await self._registrar_fallo_destino(dest_id)
                else:
                    esperas = [10.0, 60.0]
                    espera = esperas[min(intentos, len(esperas) - 1)]
                    prog = ahora + timedelta(seconds=espera)
                    await self._reprogramar_intento(env_id, prog, intentos + 1, str(ed))
                    await self._registrar_fallo_destino(dest_id)

            except DestinoPerdido as dp:
                logger.info("Destino %d perdido (%s). Desactivando destino.", plat_id, dp.motivo)
                await self._desactivar_destino(dest_id, dp.motivo)

            except DestinoMigrado as dm:
                logger.info("Destino %d migrado a %d. Actualizando plataforma_id.", plat_id, dm.nuevo_id)
                await self._migrar_destino(dest_id, dm.nuevo_id)

        return enviados

    def _respetar_limite_destino(self, plat_id: int) -> bool:
        """Verifica que el destino no exceda 10 msg/min y que haya al menos 1s desde el último."""
        ahora = datetime.now(UTC)
        ultimo = self._ultimo_envio_por_destino.get(plat_id)
        if ultimo and (ahora - ultimo).total_seconds() < 1.0:
            return False

        recientes = self._envios_recientes_destino.get(plat_id, [])
        hace_minuto = ahora - timedelta(minutes=1)
        recientes = [t for t in recientes if t > hace_minuto]
        self._envios_recientes_destino[plat_id] = recientes

        return len(recientes) < self.config.bot_max_mensajes_minuto

    def _respetar_limite_global(self) -> bool:
        """Verifica que no se exceda el límite global de 25 mensajes por segundo."""
        ahora = datetime.now(UTC)
        hace_segundo = ahora - timedelta(seconds=1)
        self._envios_globales_segundo = [t for t in self._envios_globales_segundo if t > hace_segundo]
        return len(self._envios_globales_segundo) < self.config.bot_max_mensajes_segundo

    def _registrar_envio_exitoso(self, plat_id: int) -> None:
        """Registra marcas temporales de envío para los limitadores."""
        ahora = datetime.now(UTC)
        self._ultimo_envio_por_destino[plat_id] = ahora
        self._envios_recientes_destino.setdefault(plat_id, []).append(ahora)
        self._envios_globales_segundo.append(ahora)

    async def _marcar_enviado(self, envio_id: int, destino_id: int, mensaje_id: int) -> None:
        """Actualiza el estado del envío a 'enviado' y limpia rachas de fallos."""
        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                """
                        UPDATE envio
                        SET estado = 'enviado',
                            mensaje_id = %s,
                            enviado_en = now(),
                            ultimo_error = NULL
                        WHERE id = %s
                        """,
                (mensaje_id, envio_id),
            )
            await cur.execute(
                """
                        UPDATE destino
                        SET fallando_desde = NULL
                        WHERE id = %s
                        """,
                (destino_id,),
            )

    async def _reprogramar_intento(
        self,
        envio_id: int,
        programado_para: datetime,
        intentos: int,
        ultimo_error: str,
    ) -> None:
        """Reprograma un envío pendiente tras un error transitorio o de destino."""
        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                """
                        UPDATE envio
                        SET programado_para = %s,
                            intentos = %s,
                            ultimo_error = %s
                        WHERE id = %s
                        """,
                (programado_para, intentos, ultimo_error[:500], envio_id),
            )

    async def _marcar_estado(self, envio_id: int, estado: str, ultimo_error: str | None = None) -> None:
        """Actualiza el estado terminal de un envío ('fallido' o 'caducado')."""
        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                """
                        UPDATE envio
                        SET estado = %s, ultimo_error = %s
                        WHERE id = %s
                        """,
                (estado, ultimo_error[:500] if ultimo_error else None, envio_id),
            )

    async def _registrar_fallo_destino(self, destino_id: int) -> None:
        """Registra el primer momento de fallo de un destino y desactiva si lleva 3 días fallando."""
        ahora = datetime.now(UTC)
        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                """
                        UPDATE destino
                        SET fallando_desde = COALESCE(fallando_desde, %s)
                        WHERE id = %s
                        RETURNING fallando_desde
                        """,
                (ahora, destino_id),
            )
            fila = await cur.fetchone()
            fallando_desde = fila[0] if fila else ahora

            # Desactivar si supera BOT_DIAS_FALLO_DESACTIVAR días consecutivos de fallo
            limite_dias = timedelta(days=self.config.bot_dias_fallo_desactivar)
            if ahora - fallando_desde >= limite_dias:
                await cur.execute(
                    """
                            UPDATE destino
                            SET activo = false, motivo_baja = 'fallos', baja_en = %s
                            WHERE id = %s
                            """,
                    (ahora, destino_id),
                )
                await cur.execute(
                    """
                            UPDATE envio
                            SET estado = 'caducado', ultimo_error = 'Destino desactivado por fallos continuados'
                            WHERE destino_id = %s AND estado = 'pendiente'
                            """,
                    (destino_id,),
                )

    async def _desactivar_destino(self, destino_id: int, motivo: str) -> None:
        """Desactiva un destino y marca todos sus pendientes como caducados."""
        ahora = datetime.now(UTC)
        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                """
                        UPDATE destino
                        SET activo = false, motivo_baja = %s, baja_en = %s
                        WHERE id = %s
                        """,
                (motivo, ahora, destino_id),
            )
            await cur.execute(
                """
                        UPDATE envio
                        SET estado = 'caducado', ultimo_error = 'Destino desactivado'
                        WHERE destino_id = %s AND estado = 'pendiente'
                        """,
                (destino_id,),
            )

    async def _migrar_destino(self, destino_id: int, nuevo_id: int) -> None:
        """Actualiza el identificador de plataforma de un grupo migrado a supergrupo."""
        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                """
                        UPDATE destino
                        SET plataforma_id = %s, clase = 'supergroup', actualizado_en = now()
                        WHERE id = %s
                        """,
                (nuevo_id, destino_id),
            )
