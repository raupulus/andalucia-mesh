"""Lógica unificada de ejecución y formateo de comandos de consulta y configuración."""

from datetime import UTC, datetime
from typing import Any
from zoneinfo import ZoneInfo

from nucleo.api_portal import ClientePortal
from nucleo.base import GestorBase
from nucleo.catalogo import GestorCatalogo
from nucleo.config import ConfiguracionBots
from nucleo.formato import PROVINCIAS, formatear_fecha_hora, obtener_nombre_provincia

MAPA_PROVINCIAS_ALIAS: dict[str, str] = {
    "almeria": "ES-AL",
    "almería": "ES-AL",
    "es-al": "ES-AL",
    "al": "ES-AL",
    "cadiz": "ES-CA",
    "cádiz": "ES-CA",
    "es-ca": "ES-CA",
    "ca": "ES-CA",
    "cordoba": "ES-CO",
    "córdoba": "ES-CO",
    "es-co": "ES-CO",
    "co": "ES-CO",
    "granada": "ES-GR",
    "es-gr": "ES-GR",
    "gr": "ES-GR",
    "huelva": "ES-H",
    "es-h": "ES-H",
    "h": "ES-H",
    "jaen": "ES-J",
    "jaén": "ES-J",
    "es-j": "ES-J",
    "j": "ES-J",
    "malaga": "ES-MA",
    "málaga": "ES-MA",
    "es-ma": "ES-MA",
    "ma": "ES-MA",
    "sevilla": "ES-SE",
    "es-se": "ES-SE",
    "se": "ES-SE",
    "fuera": "FUERA",
}


def formatear_decimal(valor: float | int | None) -> str:
    """Formatea números con coma decimal en español."""
    if valor is None:
        return "—"
    return f"{valor:.1f}".replace(".", ",")


def formatear_entero(valor: int | None) -> str:
    """Formatea enteros con punto como separador de miles."""
    if valor is None:
        return "—"
    return f"{valor:,}".replace(",", ".")


def formatear_relativo_reciente(fecha_str: str | None, tz_nombre: str = "Europe/Madrid") -> str:
    """Calcula tiempo relativo legible ('hace 5 min', 'hace 2 h', 'hace 1 d')."""
    if not fecha_str:
        return "hace poco"
    try:
        dt = datetime.fromisoformat(fecha_str.replace("Z", "+00:00"))
    except ValueError:
        return "hace poco"

    ahora = datetime.now(UTC)
    diff = int((ahora - dt).total_seconds())
    if diff < 60:
        return "hace 1 min"
    if diff < 3600:
        return f"hace {diff // 60} min"
    if diff < 86400:
        return f"hace {diff // 3600} h"
    return f"hace {diff // 86400} d"


class GestorComandos:
    """Procesa comandos de bot y genera los textos canónicos según UT-08.7."""

    def __init__(
        self,
        config: ConfiguracionBots,
        cliente_portal: ClientePortal,
        catalogo: GestorCatalogo,
        gestor_base: GestorBase,
    ) -> None:
        """Inicializa el gestor de comandos."""
        self.config = config
        self.cliente_portal = cliente_portal
        self.catalogo = catalogo
        self.gestor_base = gestor_base
        self.tz = ZoneInfo(config.tz)

    def normalizar_provincia(self, entrada: str | None) -> tuple[bool, str | None]:
        """Normaliza el argumento de provincia; retorna (valido, codigo_o_error)."""
        if not entrada or not entrada.strip():
            return True, None
        clave = entrada.strip().lower()
        if clave in MAPA_PROVINCIAS_ALIAS:
            return True, MAPA_PROVINCIAS_ALIAS[clave]
        return False, None

    async def ejecutar_status(self) -> str:
        """Genera el mensaje de estado general de la malla (/status)."""
        resumen = await self.cliente_portal.obtener_resumen()
        if not resumen:
            return "No puedo consultar los datos ahora mismo. Las alertas siguen llegando con normalidad."

        gen_at_str = resumen.get("generated_at")
        hora_txt = "--:--"
        if gen_at_str:
            try:
                dt_gen = datetime.fromisoformat(gen_at_str.replace("Z", "+00:00"))
                hora_txt = formatear_fecha_hora(dt_gen, self.config.tz)
            except ValueError:
                pass

        nodos_24h = formatear_entero(resumen.get("nodes_active_24h"))
        nodos_7d = formatear_entero(resumen.get("nodes_active_7d"))
        routers_24h = formatear_entero(resumen.get("routers_active_24h"))
        gateways = formatear_entero(resumen.get("gateways_publishing"))

        chutil = resumen.get("channel_utilization", {})
        avg_andalucia = formatear_decimal(chutil.get("andalucia_avg"))

        lineas: list[str] = [
            f"📡 Estado de la malla · {hora_txt}",
            f"Nodos activos: {nodos_24h} en 24 h · {nodos_7d} en 7 días",
            f"Routers activos (24 h): {routers_24h}",
            f"Gateways publicando: {gateways}",
            f"Carga media del canal en Andalucía: {avg_andalucia} %",
        ]

        # Provincias de mayor a menor carga
        provincias_raw = chutil.get("provinces", [])
        prov_items: list[tuple[str, float | None]] = []
        for p in provincias_raw:
            c = p.get("code")
            val = p.get("avg")
            if c:
                prov_items.append((c, val))

        # Ordenar por carga decreciente (None al final)
        prov_items.sort(key=lambda x: (x[1] is None, -(x[1] or 0.0)))
        trozos_prov: list[str] = []
        for c, val in prov_items:
            nom = PROVINCIAS.get(c, c)
            if val is None:
                trozos_prov.append(f"⚪ {nom} sin datos")
            elif val < 20.0:
                trozos_prov.append(f"🟢 {nom} {formatear_decimal(val)} %")
            elif val < 40.0:
                trozos_prov.append(f"🟠 {nom} {formatear_decimal(val)} %")
            else:
                trozos_prov.append(f"🔴 {nom} {formatear_decimal(val)} %")

        if trozos_prov:
            lineas.append(" · ".join(trozos_prov))

        # Alertas abiertas
        alertas = resumen.get("alerts_open", {})
        por_riesgo = alertas.get("by_risk", {})
        trozos_riesgo: list[str] = []
        # De mayor a menor según el catálogo
        for r_id in reversed(self.catalogo.todos_los_riesgos()):
            cant = por_riesgo.get(r_id, 0)
            if cant > 0:
                trozos_riesgo.append(f"{cant} {r_id}")
        if not trozos_riesgo:
            trozos_riesgo = ["0 abiertas"]

        lineas.append(f"Alertas abiertas: {' · '.join(trozos_riesgo)}")

        por_tipo = alertas.get("by_type", {})
        infra = por_tipo.get("infraestructura", 0)
        cli = por_tipo.get("clientes", 0)
        lineas.append(f"Infraestructura {infra} · Clientes {cli}")
        lineas.append(f"https://{self.config.project_domain}")

        if resumen.get("stale"):
            lineas.append(f"(datos de las {hora_txt}; el portal no ha podido actualizarlos)")

        return "\n".join(lineas)

    async def ejecutar_battery(self, provincia_arg: str | None = None) -> str:
        """Genera el mensaje de estado de batería de routers (/battery)."""
        valido, cod_prov = self.normalizar_provincia(provincia_arg)
        if not valido:
            return "Provincia no reconocida. Usa el nombre o el código: Almería (ES-AL), Cádiz (ES-CA), …"

        datos = await self.cliente_portal.obtener_routers(cod_prov)
        if not datos:
            return "No puedo consultar los datos ahora mismo. Las alertas siguen llegando con normalidad."

        items = [i for i in datos.get("items", []) if str(i.get("role", "")).upper() == "ROUTER"]
        if not items:
            return "No hay routers vistos en los últimos 7 días."

        # Separar por alimentados, con batería y sin dato
        con_bateria: list[dict[str, Any]] = []
        alimentados: list[str] = []
        sin_dato: list[str] = []

        for it in items:
            nombre = it.get("short") or it.get("id")
            bat = it.get("battery", {}) or {}
            powered = bat.get("powered", False)
            nivel = bat.get("level")

            if powered:
                alimentados.append(nombre)
            elif nivel is not None:
                con_bateria.append(it)
            else:
                sin_dato.append(nombre)

        # Ordenar batería de menor a mayor
        con_bateria.sort(key=lambda x: (x.get("battery", {}).get("level", 100)))

        lineas: list[str] = ["🔋 Batería de los routers · de menor a mayor"]
        for it in con_bateria:
            nom = it.get("short") or it.get("id")
            prov_cod = it.get("province")
            prov_nom = obtener_nombre_provincia(prov_cod) or "—"
            bat = it.get("battery", {})
            nivel = bat.get("level", 0)
            voltaje = bat.get("voltage")
            at_str = bat.get("at") or it.get("last_seen")
            relativo = formatear_relativo_reciente(at_str, self.config.tz)

            if nivel < 20:
                icono = "🔴"
            elif nivel < 40:
                icono = "🟠"
            else:
                icono = "🟢"

            txt_v = f" ({formatear_decimal(voltaje)} V)" if voltaje is not None else ""
            lineas.append(f"{icono} {nom} · {prov_nom} · {nivel} %{txt_v} · {relativo}")

        if alimentados:
            lineas.append(f"Alimentados: {', '.join(sorted(alimentados))}")
        if sin_dato:
            lineas.append(f"Sin dato de batería: {', '.join(sorted(sin_dato))}")

        return "\n".join(lineas)

    async def ejecutar_routers(self, provincia_arg: str | None = None) -> str:
        """Genera el mensaje de estado detallado de routers (/routers)."""
        valido, cod_prov = self.normalizar_provincia(provincia_arg)
        if not valido:
            return "Provincia no reconocida. Usa el nombre o el código: Almería (ES-AL), Cádiz (ES-CA), …"

        datos = await self.cliente_portal.obtener_routers(cod_prov)
        if not datos:
            return "No puedo consultar los datos ahora mismo. Las alertas siguen llegando con normalidad."

        items = [i for i in datos.get("items", []) if str(i.get("role", "")).upper() == "ROUTER"]
        if not items:
            return "No hay routers vistos en los últimos 7 días."

        total_routers = len(items)
        lineas: list[str] = [f"📶 Routers vistos en 7 días · {total_routers}"]

        # Agrupar por provincia en orden alfabético
        por_provincia: dict[str, list[dict[str, Any]]] = {}
        for it in items:
            p = it.get("province") or "FUERA"
            por_provincia.setdefault(p, []).append(it)

        # Ordenar provincias alfabéticamente por su nombre visible
        provincias_ordenadas = sorted(
            por_provincia.keys(),
            key=lambda c: obtener_nombre_provincia(c) or c,
        )

        for p_cod in provincias_ordenadas:
            p_nom = obtener_nombre_provincia(p_cod) or p_cod
            lineas.append(p_nom)
            grupo = por_provincia[p_cod]
            # Ordenar por nombre corto
            grupo.sort(key=lambda x: str(x.get("short") or x.get("id")).lower())

            for it in grupo:
                nom = it.get("short") or it.get("id")
                bat = it.get("battery", {}) or {}
                if bat.get("powered"):
                    bat_txt = "🔌"
                elif bat.get("level") is not None:
                    bat_txt = f"🔋 {bat.get('level')} %"
                else:
                    bat_txt = "🔋 —"

                ch = it.get("chutil")
                tx = it.get("tx")
                ch_txt = f"chutil {formatear_decimal(ch)} %" if ch is not None else "chutil —"
                tx_txt = f"tx {formatear_decimal(tx)} %" if tx is not None else "tx —"
                rel = formatear_relativo_reciente(it.get("last_seen"), self.config.tz)

                lineas.append(f"{nom} · {bat_txt} · {ch_txt} · {tx_txt} · {rel}")

        return "\n".join(lineas)

    async def ejecutar_levels(
        self,
        plataforma_id: int,
        es_admin: bool,
        argumentos: list[str],
    ) -> str:
        """Consulta o actualiza los filtros de riesgo del chat (/levels)."""
        if not argumentos:
            # Consulta
            async with self.gestor_base.conexion() as conn, conn.cursor() as cur:
                await cur.execute(
                    "SELECT riesgos FROM destino WHERE plataforma_id = %s",
                    (plataforma_id,),
                )
                fila = await cur.fetchone()
            if not fila:
                return "Este chat no recibe alertas."
            riesgos = fila[0]
            if not riesgos:
                defecto_txt = ", ".join(self.config.lista_riesgos_defecto)
                activos_txt = f"{defecto_txt} (por defecto)"
            else:
                activos_txt = ", ".join(riesgos)

            return (
                f"Riesgos activos en este chat: {activos_txt}.\n\n"
                "Niveles disponibles:\n"
                "• alto: Fallo activo o daño directo a la malla.\n"
                "• medio: Anomalías y degradación importante.\n"
                "• bajo: Avisos informativos y preventivos.\n"
                "• todos: Activa todos los niveles de riesgo.\n\n"
                "Para cambiarlos: /levels <niveles>\n"
                "Ejemplos:\n"
                "  /levels alto\n"
                "  /levels medio alto\n"
                "  /levels todos"
            )

        # Modificación: requiere administrador
        if not es_admin:
            return "Solo los administradores pueden cambiar los filtros."

        # Evaluar 'todos'
        if any(a.lower() == "todos" for a in argumentos):
            nuevos = self.catalogo.todos_los_riesgos()
        else:
            valido, normalizados = self.catalogo.validar_riesgos(argumentos)
            if not valido:
                posibles = ", ".join(self.catalogo.todos_los_riesgos())
                invalido = normalizados[0] if normalizados else "desconocido"
                return f"Valor no válido: «{invalido}». Riesgos posibles: {posibles} (o todos)."
            nuevos = normalizados

        # Si coincide exactamente con el valor por defecto, guardar NULL
        if sorted(nuevos) == sorted(self.config.lista_riesgos_defecto):
            valor_guardar: list[str] | None = None
        else:
            valor_guardar = nuevos

        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                """
                        UPDATE destino
                        SET riesgos = %s, actualizado_en = now()
                        WHERE plataforma_id = %s
                        """,
                (valor_guardar, plataforma_id),
            )

        return f"Riesgos activos en este chat: {', '.join(nuevos)}."

    async def ejecutar_types(
        self,
        plataforma_id: int,
        es_admin: bool,
        argumentos: list[str],
    ) -> str:
        """Consulta o actualiza los filtros de tipo del chat (/types)."""
        if not argumentos:
            # Consulta
            async with self.gestor_base.conexion() as conn, conn.cursor() as cur:
                await cur.execute(
                    "SELECT tipos FROM destino WHERE plataforma_id = %s",
                    (plataforma_id,),
                )
                fila = await cur.fetchone()
            if not fila:
                return "Este chat no recibe alertas."
            tipos = fila[0]
            if not tipos:
                defecto_txt = ", ".join(self.config.lista_tipos_defecto)
                activos_txt = f"{defecto_txt} (por defecto)"
            else:
                activos_txt = ", ".join(tipos)

            return (
                f"Tipos activos en este chat: {activos_txt}.\n\n"
                "Tipos disponibles:\n"
                "• infraestructura: Routers, repetidores, gateways y degradación de red.\n"
                "• clientes: Problemas de nodos personales de usuario (batería, etc.).\n"
                "• todos: Activa todos los tipos.\n\n"
                "Para cambiarlos: /types <tipos>\n"
                "Ejemplos:\n"
                "  /types infraestructura\n"
                "  /types clientes\n"
                "  /types todos"
            )

        # Modificación: requiere administrador
        if not es_admin:
            return "Solo los administradores pueden cambiar los filtros."

        if any(a.lower() == "todos" for a in argumentos):
            nuevos = self.catalogo.todos_los_tipos()
        else:
            valido, normalizados = self.catalogo.validar_tipos(argumentos)
            if not valido:
                posibles = ", ".join(self.catalogo.todos_los_tipos())
                invalido = normalizados[0] if normalizados else "desconocido"
                return f"Valor no válido: «{invalido}». Tipos posibles: {posibles} (o todos)."
            nuevos = normalizados

        valor_guardar = None if sorted(nuevos) == sorted(self.config.lista_tipos_defecto) else nuevos

        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                """
                        UPDATE destino
                        SET tipos = %s, actualizado_en = now()
                        WHERE plataforma_id = %s
                        """,
                (valor_guardar, plataforma_id),
            )

        return f"Tipos activos en este chat: {', '.join(nuevos)}."

    async def ejecutar_pause(self, plataforma_id: int, es_admin: bool) -> str:
        """Pausa temporalmente el envío de alertas en el chat (/pause o /silenciar)."""
        if not es_admin:
            return "Solo los administradores pueden pausar o reactivar las alertas."

        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                """
                UPDATE destino
                SET activo = false, motivo_baja = 'pausado_usuario', baja_en = now(), actualizado_en = now()
                WHERE plataforma_id = %s AND activo = true
                RETURNING id
                """,
                (plataforma_id,),
            )
            fila = await cur.fetchone()
            if not fila:
                return "Las alertas ya están silenciadas o este chat no recibe alertas. Usa /resume para reactivarlas."

            dest_id = fila[0]
            await cur.execute(
                """
                UPDATE envio
                SET estado = 'caducado', ultimo_error = 'Pausado por usuario con /pause'
                WHERE destino_id = %s AND estado = 'pendiente'
                """,
                (dest_id,),
            )

        return (
            "⏸️ Alertas silenciadas en este chat.\n"
            "No se enviará ningún aviso hasta que un administrador las reanude con /resume (o /activar)."
        )

    async def ejecutar_resume(self, plataforma_id: int, es_admin: bool) -> str:
        """Reanuda el envío de alertas en el chat (/resume o /activar)."""
        if not es_admin:
            return "Solo los administradores pueden pausar o reactivar las alertas."

        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                """
                UPDATE destino
                SET activo = true, motivo_baja = NULL, baja_en = NULL, fallando_desde = NULL, actualizado_en = now()
                WHERE plataforma_id = %s
                RETURNING id, riesgos, tipos
                """,
                (plataforma_id,),
            )
            fila = await cur.fetchone()
            if not fila:
                return "Este chat no está registrado. Si el bot acaba de entrar, escribe /status para activarlo."

            _dest_id, riesgos, tipos = fila
            riesgos_txt = (
                ", ".join(riesgos) if riesgos else f"{', '.join(self.config.lista_riesgos_defecto)} (por defecto)"
            )
            tipos_txt = (
                ", ".join(tipos) if tipos else f"{', '.join(self.config.lista_tipos_defecto)} (por defecto)"
            )

        return (
            "▶️ Alertas reanudadas en este chat.\n"
            f"Filtros activos: Riesgos: {riesgos_txt} · Tipos: {tipos_txt}.\n"
            "Usa /settings para ver la configuración o /pause para pausarlas de nuevo."
        )

    async def ejecutar_enable_exterior(self, plataforma_id: int, es_admin: bool) -> str:
        """Activa la recepción de alertas de nodos de fuera de Andalucía (/enableExterior)."""
        if not es_admin:
            return "Solo los administradores pueden cambiar los filtros."

        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                """
                UPDATE destino
                SET incluir_exterior = true, actualizado_en = now()
                WHERE plataforma_id = %s
                RETURNING id
                """,
                (plataforma_id,),
            )
            fila = await cur.fetchone()
            if not fila:
                return "Este chat no recibe alertas."

        return (
            "🌍 Nodos de fuera de Andalucía: ✅ ACTIVADOS en este chat.\n"
            "Se enviarán alertas tanto de nodos de Andalucía como de fuera de la comunidad."
        )

    async def ejecutar_disable_exterior(self, plataforma_id: int, es_admin: bool) -> str:
        """Desactiva la recepción de alertas de nodos de fuera de Andalucía (/disableExterior)."""
        if not es_admin:
            return "Solo los administradores pueden cambiar los filtros."

        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                """
                UPDATE destino
                SET incluir_exterior = false, actualizado_en = now()
                WHERE plataforma_id = %s
                RETURNING id
                """,
                (plataforma_id,),
            )
            fila = await cur.fetchone()
            if not fila:
                return "Este chat no recibe alertas."

        return (
            "🌍 Nodos de fuera de Andalucía: ❌ DESACTIVADOS en este chat.\n"
            "Solo se enviarán alertas de nodos ubicados en Andalucía."
        )

    async def ejecutar_exterior(self, plataforma_id: int, es_admin: bool, opcion: str | None = None) -> str:
        """Consulta o cambia el filtro de nodos de fuera de Andalucía (/exterior [on|off])."""
        if opcion:
            opc = opcion.strip().lower()
            if opc in ("on", "si", "sí", "activar", "enable", "permitir", "true", "1"):
                return await self.ejecutar_enable_exterior(plataforma_id, es_admin)
            elif opc in ("off", "no", "desactivar", "disable", "bloquear", "false", "0"):
                return await self.ejecutar_disable_exterior(plataforma_id, es_admin)
            else:
                return "Opción no válida. Usa /disableExterior para solo Andalucía o /enableExterior para incluir nodos de fuera."

        # Sin argumentos: consultar estado actual
        async with self.gestor_base.conexion() as conn, conn.cursor() as cur:
            await cur.execute("SELECT incluir_exterior FROM destino WHERE plataforma_id = %s", (plataforma_id,))
            dest = await cur.fetchone()
            if not dest:
                return "Este chat no recibe alertas."
            incluir_exterior = dest[0]

        if incluir_exterior is not None:
            estado_txt = "✅ Activados (se reciben avisos de fuera)" if incluir_exterior else "❌ Desactivados (solo Andalucía)"
        else:
            def_txt = "✅ Activados (por defecto)" if self.config.bot_exterior_defecto else "❌ Desactivados: solo Andalucía (por defecto)"
            estado_txt = def_txt

        return (
            f"🌍 Filtro de nodos de fuera de Andalucía en este chat:\n"
            f"Estado: {estado_txt}\n\n"
            "Comandos para administradores:\n"
            "• /disableExterior — solo recibir alertas de Andalucía\n"
            "• /enableExterior — recibir también alertas de fuera de Andalucía"
        )

    async def ejecutar_settings(self, plataforma_id: int) -> str:
        """Devuelve la configuración y estadísticas del chat (/settings)."""
        async with self.gestor_base.conexion() as conn, conn.cursor() as cur:
            await cur.execute(
                """
                    SELECT id, activo, motivo_baja, alta_en, baja_en, riesgos, tipos, incluir_exterior
                    FROM destino
                    WHERE plataforma_id = %s
                    """,
                (plataforma_id,),
            )
            dest = await cur.fetchone()

            if not dest:
                return "Este chat no recibe alertas."

            dest_id, activo, motivo_baja, alta_en, baja_en, riesgos, tipos, incluir_exterior = dest

            if not activo:
                if motivo_baja == "pausado_usuario":
                    return (
                        "⚙️ Configuración de este chat\n"
                        "Estado: ⏸️ Silenciado / Pausado por un administrador.\n\n"
                        "Para volver a recibir alertas de la red, usa /resume (o /activar)."
                    )
                baja_txt = alta_en.strftime("%d/%m/%Y") if alta_en else "desconocido"
                motivo = motivo_baja or "desactivado"
                return (
                    f"Este chat no recibe alertas desde el {baja_txt} ({motivo}). "
                    "Para reactivarlo, quita el bot y vuelve a añadirlo."
                )

            # Consultar avisos enviados
            await cur.execute(
                """
                    SELECT count(*), max(enviado_en)
                    FROM envio
                    WHERE destino_id = %s AND estado = 'enviado'
                    """,
                (dest_id,),
            )
            stats = await cur.fetchone()
            total_enviados = stats[0] if stats else 0
            ultimo_enviado_at = stats[1] if stats else None

        riesgos_txt = (
            ", ".join(riesgos) if riesgos else f"{', '.join(self.config.lista_riesgos_defecto)} (por defecto)"
        )
        tipos_txt = (
            ", ".join(tipos) if tipos else f"{', '.join(self.config.lista_tipos_defecto)} (por defecto)"
        )
        if incluir_exterior is not None:
            exterior_txt = "✅ Permitidos (Andalucía y exterior)" if incluir_exterior else "❌ Solo Andalucía"
        else:
            def_ext = "✅ Permitidos (por defecto)" if self.config.bot_exterior_defecto else "❌ Solo Andalucía (por defecto)"
            exterior_txt = def_ext

        alta_txt = alta_en.strftime("%d/%m/%Y") if alta_en else "desconocida"

        if ultimo_enviado_at:
            ult_txt = f"hoy {formatear_fecha_hora(ultimo_enviado_at, self.config.tz)}"
        else:
            ult_txt = "ninguno todavía"

        return (
            "⚙️ Configuración de este chat\n"
            f"Riesgos: {riesgos_txt} (opciones: bajo, medio, alto, todos)\n"
            f"Tipos: {tipos_txt} (opciones: infraestructura, clientes, todos)\n"
            f"Nodos exterior: {exterior_txt}\n"
            "Estado: ✅ Activo (recibiendo alertas)\n"
            f"Activo desde el {alta_txt}\n"
            f"Avisos enviados aquí: {total_enviados} (último: {ult_txt})\n\n"
            "Comandos de control:\n"
            "• /levels — ver u opciones de riesgo\n"
            "• /types — ver u opciones de tipo\n"
            "• /disableExterior — silenciar nodos de fuera de Andalucía\n"
            "• /enableExterior — permitir nodos de fuera de Andalucía\n"
            "• /pause — silenciar/pausar alertas de la malla\n"
            "• /resume — reanudar alertas de la malla"
        )

    def ejecutar_help(self) -> str:
        """Devuelve el texto de ayuda general (/help)."""
        riesgos_txt = ", ".join(self.catalogo.todos_los_riesgos())
        tipos_txt = ", ".join(self.catalogo.todos_los_tipos())
        return (
            f"Bot de alertas de {self.config.project_name}\n"
            "/status — estado general de la malla\n"
            "/battery [provincia] — batería de los routers\n"
            "/routers [provincia] — routers con batería, chutil y tx\n"
            "/levels [riesgos] — ver o cambiar niveles (bajo, medio, alto, todos)\n"
            "/types [tipos] — ver o cambiar tipos (infraestructura, clientes, todos)\n"
            "/disableExterior — solo alertas de Andalucía (administradores)\n"
            "/enableExterior — incluir nodos de fuera de Andalucía (administradores)\n"
            "/pause — silenciar o pausar las alertas (administradores)\n"
            "/resume — reanudar las alertas (administradores)\n"
            "/settings — configuración y estado de este chat\n"
            f"Riesgos: {riesgos_txt} · Tipos: {tipos_txt}\n"
            f"Más información: https://{self.config.project_domain}/bots"
        )

    @staticmethod
    def partir_en_mensajes(texto: str, max_caracteres: int = 4000) -> list[str]:
        """Trocea respuestas largas por líneas respetando un máximo de 3 mensajes."""
        if len(texto) <= max_caracteres:
            return [texto]

        lineas = texto.splitlines()
        mensajes: list[str] = []
        actual: list[str] = []
        longitud_actual = 0
        routers_omitidos = 0

        for idx, linea in enumerate(lineas):
            linea_len = len(linea) + 1  # considerando el salto de línea
            if longitud_actual + linea_len > max_caracteres:
                if len(mensajes) < 2:
                    mensajes.append("\n".join(actual))
                    actual = [linea]
                    longitud_actual = linea_len
                else:
                    # En el tercer mensaje, acumular hasta donde quepa y truncar
                    aviso_final = "… y el resto. Usa /routers <provincia>."
                    if longitud_actual + linea_len + len(aviso_final) + 2 <= max_caracteres:
                        actual.append(linea)
                        longitud_actual += linea_len
                    else:
                        routers_omitidos = len(lineas) - idx
                        break
            else:
                actual.append(linea)
                longitud_actual += linea_len

        if routers_omitidos > 0:
            actual.append(f"… y {routers_omitidos} routers más. Usa /routers <provincia>.")

        if actual:
            mensajes.append("\n".join(actual))

        return mensajes[:3]
