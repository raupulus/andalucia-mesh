"""API HTTP interna para la gestión dinámica de destinos de webhooks desde el panel de operador."""

from collections.abc import Awaitable, Callable
import hashlib
import json
import logging
import time
from urllib.parse import urlparse

import aiohttp
from aiohttp import web

from nucleo.base import GestorBase
from webhooks.configuracion import (
    PROVINCIAS_VALIDAS,
    REGEX_NODO,
    REGEX_NOMBRE,
    ConfiguracionWebhooks,
)
from webhooks.cuerpo import calcular_firma_hmac, construir_cuerpo_webhook
from webhooks.motor_entregas import MotorEntregas
from webhooks.red_segura import resolver_y_validar_host, validar_url_carga

logger = logging.getLogger("webhooks.api_interna")


class ApiInternaWebhooks:
    """Controlador de endpoints REST internos para la administración de destinos."""

    def __init__(
        self,
        config: ConfiguracionWebhooks,
        gestor_base: GestorBase,
        motor: MotorEntregas,
    ) -> None:
        """Inicializa el controlador con sus dependencias."""
        self.config = config
        self.gestor_base = gestor_base
        self.motor = motor
        self.redes_bloqueadas = config.parsear_redes_bloqueadas()

    async def listar_destinos(self, request: web.Request) -> web.Response:
        """Devuelve la lista completa de destinos registrados y su estado operativo."""
        async with self.gestor_base.conexion() as conn, conn.cursor() as cur:
            await cur.execute(
                """
                SELECT d.nombre, d.url, d.host, d.activo, d.motivo_baja, d.fallos_seguidos, d.ultimo_ok,
                       d.riesgos, d.tipos, d.provincias, d.nodos, d.secreto,
                       (SELECT count(*) FROM entrega e WHERE e.destino_id = d.id AND e.estado = 'pendiente') AS pendientes
                FROM destino d
                ORDER BY d.nombre ASC
                """
            )
            filas = await cur.fetchall()

        destinos = []
        for fila in filas:
            ult_ok_iso = fila[6].strftime("%Y-%m-%dT%H:%M:%SZ") if fila[6] else None
            destinos.append(
                {
                    "nombre": fila[0],
                    "url": fila[1],
                    "host": fila[2],
                    "activo": bool(fila[3]),
                    "motivo_baja": fila[4],
                    "fallos_seguidos": int(fila[5]),
                    "ultimo_ok": ult_ok_iso,
                    "riesgos": list(fila[7]) if fila[7] else [],
                    "tipos": list(fila[8]) if fila[8] else [],
                    "provincias": list(fila[9]) if fila[9] else [],
                    "nodos": list(fila[10]) if fila[10] else [],
                    "secreto": fila[11] or "",
                    "pendientes": int(fila[12] or 0),
                }
            )

        return web.json_response({"ok": True, "destinos": destinos})

    async def crear_destino(self, request: web.Request) -> web.Response:
        """Registra un nuevo destino tras validar formato, secreto y reglas SSRF."""
        try:
            datos = await request.json()
        except json.JSONDecodeError:
            return web.json_response({"ok": False, "error": "Cuerpo de solicitud JSON inválido"}, status=400)

        nombre = str(datos.get("nombre", "")).strip()
        url = str(datos.get("url", "")).strip()
        secreto = str(datos.get("secreto", "")).strip()
        riesgos = datos.get("riesgos")
        tipos = datos.get("tipos")
        provincias = datos.get("provincias")
        nodos = datos.get("nodos")

        # 1. Validación de nombre
        if not REGEX_NOMBRE.match(nombre):
            return web.json_response(
                {"ok": False, "error": "El nombre debe cumplir ^[a-z0-9-]{1,40}$"},
                status=400,
            )

        # 2. Validación de URL y SSRF
        try:
            validar_url_carga(url)
        except ValueError as err:
            return web.json_response({"ok": False, "error": f"URL inválida: {err}"}, status=400)

        # 3. Validación de secreto
        if len(secreto) < 32:
            return web.json_response(
                {"ok": False, "error": "El secreto debe tener al menos 32 caracteres"},
                status=400,
            )

        # 4. Validación de filtros
        lista_riesgos: list[str] | None = None
        if riesgos is not None:
            if not isinstance(riesgos, list) or not all(isinstance(r, str) for r in riesgos):
                return web.json_response({"ok": False, "error": "El campo 'riesgos' debe ser lista de cadenas"}, status=400)
            lista_riesgos = [r.lower() for r in riesgos]

        lista_tipos: list[str] | None = None
        if tipos is not None:
            if not isinstance(tipos, list) or not all(isinstance(t, str) for t in tipos):
                return web.json_response({"ok": False, "error": "El campo 'tipos' debe ser lista de cadenas"}, status=400)
            lista_tipos = [t.lower() for t in tipos]

        lista_provincias: list[str] | None = None
        if provincias is not None:
            if not isinstance(provincias, list) or not all(isinstance(p, str) for p in provincias):
                return web.json_response({"ok": False, "error": "El campo 'provincias' debe ser lista de cadenas"}, status=400)
            for p in provincias:
                if p not in PROVINCIAS_VALIDAS:
                    return web.json_response({"ok": False, "error": f"Código de provincia no válido: {p}"}, status=400)
            lista_provincias = provincias

        lista_nodos: list[str] | None = None
        if nodos is not None:
            if not isinstance(nodos, list) or not all(isinstance(n, str) for n in nodos):
                return web.json_response({"ok": False, "error": "El campo 'nodos' debe ser lista de cadenas"}, status=400)
            for n in nodos:
                if not REGEX_NODO.match(n):
                    return web.json_response({"ok": False, "error": f"Formato de nodo inválido (esperado !hex): {n}"}, status=400)
            lista_nodos = nodos

        host = str(urlparse(url).hostname)
        url_hash = hashlib.sha256(url.encode("utf-8")).hexdigest()

        # 5. Inserción en base de datos
        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute("SELECT id FROM destino WHERE nombre = %s", (nombre,))
            if await cur.fetchone():
                return web.json_response({"ok": False, "error": f"El destino '{nombre}' ya existe"}, status=409)

            await cur.execute(
                """
                INSERT INTO destino (
                    nombre, url, host, url_hash, riesgos, tipos, provincias, nodos, secreto,
                    activo, alta_en, actualizado_en
                ) VALUES (
                    %s, %s, %s, %s, %s, %s, %s, %s, %s, true, now(), now()
                )
                """,
                (nombre, url, host, url_hash, lista_riesgos, lista_tipos, lista_provincias, lista_nodos, secreto),
            )

        # 6. Invalidar caché en memoria del motor
        await self.motor.recargar_destinos_db()
        logger.info("Nuevo destino de webhook registrado desde API interna: %s", nombre)

        return web.json_response({"ok": True, "nombre": nombre}, status=201)

    async def actualizar_destino(self, request: web.Request) -> web.Response:
        """Actualiza la URL, filtros o secreto de un destino existente."""
        nombre = request.match_info.get("nombre", "").strip()
        try:
            datos = await request.json()
        except json.JSONDecodeError:
            return web.json_response({"ok": False, "error": "Cuerpo de solicitud JSON inválido"}, status=400)

        # 1. Comprobar existencia
        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                "SELECT id, url, host, url_hash, secreto, riesgos, tipos, provincias, nodos FROM destino WHERE nombre = %s",
                (nombre,),
            )
            fila = await cur.fetchone()
            if not fila:
                return web.json_response({"ok": False, "error": f"Destino '{nombre}' no encontrado"}, status=404)

            _id_db, url_actual, host_actual, hash_actual, sec_actual, r_act, t_act, p_act, n_act = fila

            # 2. Procesar y validar campos a modificar
            nueva_url = url_actual
            nuevo_host = host_actual
            nuevo_hash = hash_actual
            if "url" in datos and datos["url"]:
                candidata_url = str(datos["url"]).strip()
                try:
                    validar_url_carga(candidata_url)
                except ValueError as err:
                    return web.json_response({"ok": False, "error": f"URL inválida: {err}"}, status=400)
                nueva_url = candidata_url
                nuevo_host = str(urlparse(nueva_url).hostname)
                nuevo_hash = hashlib.sha256(nueva_url.encode("utf-8")).hexdigest()

            nuevo_secreto = sec_actual
            if "secreto" in datos and datos["secreto"]:
                cand_secreto = str(datos["secreto"]).strip()
                if len(cand_secreto) < 32:
                    return web.json_response(
                        {"ok": False, "error": "El secreto debe tener al menos 32 caracteres"},
                        status=400,
                    )
                nuevo_secreto = cand_secreto

            nuevos_riesgos = list(r_act) if r_act else None
            if "riesgos" in datos:
                riesgos = datos["riesgos"]
                if riesgos is not None:
                    if not isinstance(riesgos, list) or not all(isinstance(r, str) for r in riesgos):
                        return web.json_response({"ok": False, "error": "El campo 'riesgos' debe ser lista"}, status=400)
                    nuevos_riesgos = [r.lower() for r in riesgos]
                else:
                    nuevos_riesgos = None

            nuevos_tipos = list(t_act) if t_act else None
            if "tipos" in datos:
                tipos = datos["tipos"]
                if tipos is not None:
                    if not isinstance(tipos, list) or not all(isinstance(t, str) for t in tipos):
                        return web.json_response({"ok": False, "error": "El campo 'tipos' debe ser lista"}, status=400)
                    nuevos_tipos = [t.lower() for t in tipos]
                else:
                    nuevos_tipos = None

            nuevas_provincias = list(p_act) if p_act else None
            if "provincias" in datos:
                provincias = datos["provincias"]
                if provincias is not None:
                    if not isinstance(provincias, list) or not all(isinstance(p, str) for p in provincias):
                        return web.json_response({"ok": False, "error": "El campo 'provincias' debe ser lista"}, status=400)
                    for p in provincias:
                        if p not in PROVINCIAS_VALIDAS:
                            return web.json_response({"ok": False, "error": f"Provincia inválida: {p}"}, status=400)
                    nuevas_provincias = provincias
                else:
                    nuevas_provincias = None

            nuevos_nodos = list(n_act) if n_act else None
            if "nodos" in datos:
                nodos = datos["nodos"]
                if nodos is not None:
                    if not isinstance(nodos, list) or not all(isinstance(n, str) for n in nodos):
                        return web.json_response({"ok": False, "error": "El campo 'nodos' debe ser lista"}, status=400)
                    for n in nodos:
                        if not REGEX_NODO.match(n):
                            return web.json_response({"ok": False, "error": f"Nodo inválido: {n}"}, status=400)
                    nuevos_nodos = nodos
                else:
                    nuevos_nodos = None

            await cur.execute(
                """
                UPDATE destino
                SET url = %s, host = %s, url_hash = %s, secreto = %s,
                    riesgos = %s, tipos = %s, provincias = %s, nodos = %s,
                    actualizado_en = now()
                WHERE nombre = %s
                """,
                (nueva_url, nuevo_host, nuevo_hash, nuevo_secreto, nuevos_riesgos, nuevos_tipos, nuevas_provincias, nuevos_nodos, nombre),
            )

        # 3. Recargar en memoria
        await self.motor.recargar_destinos_db()
        logger.info("Destino de webhook actualizado desde API interna: %s", nombre)

        return web.json_response({"ok": True, "nombre": nombre})

    async def eliminar_destino(self, request: web.Request) -> web.Response:
        """Marca el destino como retirado y cancela entregas pendientes."""
        nombre = request.match_info.get("nombre", "").strip()

        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                """
                UPDATE destino
                SET activo = false, motivo_baja = 'retirado', baja_en = now(), actualizado_en = now()
                WHERE nombre = %s
                RETURNING id
                """,
                (nombre,),
            )
            fila = await cur.fetchone()
            if not fila:
                return web.json_response({"ok": False, "error": f"Destino '{nombre}' no encontrado"}, status=404)

            dest_id = fila[0]
            await cur.execute(
                """
                UPDATE entrega
                SET estado = 'caducada', ultimo_error = 'Destino retirado por operador'
                WHERE destino_id = %s AND estado = 'pendiente'
                """,
                (dest_id,),
            )

        await self.motor.recargar_destinos_db()
        logger.info("Destino de webhook retirado desde API interna: %s", nombre)

        return web.json_response({"ok": True, "mensaje": "Destino retirado y entregas purgadas"})

    async def probar_destino(self, request: web.Request) -> web.Response:
        """Envía un ping de prueba firmado fuera de cola y mide tiempo y respuesta."""
        nombre = request.match_info.get("nombre", "").strip()

        async with self.gestor_base.conexion() as conn, conn.cursor() as cur:
            await cur.execute("SELECT url, host, secreto FROM destino WHERE nombre = %s", (nombre,))
            fila = await cur.fetchone()

        if not fila:
            return web.json_response(
                {
                    "ok": False,
                    "status_code": 404,
                    "duration_ms": 0,
                    "error": f"Destino '{nombre}' no encontrado",
                },
                status=404,
            )

        url, host, secreto = fila

        # 1. Validación de DNS / SSRF
        inicio = time.monotonic()
        try:
            await resolver_y_validar_host(host, self.redes_bloqueadas)
        except Exception as err:
            duracion_ms = int((time.monotonic() - inicio) * 1000)
            return web.json_response(
                {
                    "ok": False,
                    "status_code": 0,
                    "duration_ms": duracion_ms,
                    "error": f"Fallo validación DNS/SSRF: {err}",
                }
            )

        # 2. Construcción de ping firmado
        cuerpo_str, cuerpo_bytes = construir_cuerpo_webhook(
            transicion_id="01PING00000000000000000000",
            transicion_es="ping",
            first_delivery=False,
            alerta_dict=None,
            dominio_proyecto=self.config.project_domain,
        )
        firma = calcular_firma_hmac(secreto, cuerpo_bytes)
        cabeceras = {
            "Content-Type": "application/json; charset=utf-8",
            "User-Agent": f"{self.config.project_name} webhooks/1.0.0 (+https://{self.config.project_domain}/bots; {self.config.project_contact})",
            "X-SNM-Transicion": "01PING00000000000000000000",
            "X-SNM-Firma": firma,
            "X-SNM-Intento": "1",
        }

        timeout = aiohttp.ClientTimeout(total=float(self.config.webhooks_timeout_s), connect=5.0)
        async with aiohttp.ClientSession(timeout=timeout, trust_env=False) as session:
            try:
                async with session.post(url, data=cuerpo_bytes, headers=cabeceras, allow_redirects=False) as resp:
                    duracion_ms = int((time.monotonic() - inicio) * 1000)
                    es_ok = 200 <= resp.status < 300
                    return web.json_response(
                        {
                            "ok": es_ok,
                            "status_code": resp.status,
                            "duration_ms": duracion_ms,
                            "error": None if es_ok else f"El receptor devolvió HTTP {resp.status} ({resp.reason})",
                        }
                    )
            except Exception as err:
                duracion_ms = int((time.monotonic() - inicio) * 1000)
                return web.json_response(
                    {
                        "ok": False,
                        "status_code": 0,
                        "duration_ms": duracion_ms,
                        "error": str(err),
                    }
                )

    async def reactivar_destino(self, request: web.Request) -> web.Response:
        """Reactiva un destino previamente suspendido o desactivado por fallos."""
        nombre = request.match_info.get("nombre", "").strip()

        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                """
                UPDATE destino
                SET activo = true,
                    fallos_seguidos = 0,
                    motivo_baja = NULL,
                    baja_en = NULL,
                    actualizado_en = now()
                WHERE nombre = %s
                RETURNING id
                """,
                (nombre,),
            )
            fila = await cur.fetchone()
            if not fila:
                return web.json_response({"ok": False, "error": f"Destino '{nombre}' no encontrado"}, status=404)

        await self.motor.recargar_destinos_db()
        self.motor.despertar()
        logger.info("Destino reactivado desde API interna: %s", nombre)

        return web.json_response({"ok": True, "mensaje": f"Destino '{nombre}' reactivado con éxito"})


def registrar_api_interna(
    app: web.Application,
    config: ConfiguracionWebhooks,
    gestor_base: GestorBase,
    motor: MotorEntregas,
) -> None:
    """Configura el middleware de autenticación y registra las rutas internas en la aplicación aiohttp."""
    api = ApiInternaWebhooks(config, gestor_base, motor)

    @web.middleware
    async def middleware_auth_interna(
        request: web.Request,
        handler: Callable[[web.Request], Awaitable[web.StreamResponse]],
    ) -> web.StreamResponse:
        if request.path.startswith("/internal"):
            secreto_esperado = config.internal_api_secret.strip()
            secreto_recibido = request.headers.get("X-Internal-Secret", "").strip()

            if not secreto_esperado or secreto_recibido != secreto_esperado:
                return web.json_response(
                    {"ok": False, "error": "Acceso no autorizado: credenciales internas inválidas o ausentes"},
                    status=401,
                )
        return await handler(request)

    app.middlewares.append(middleware_auth_interna)

    app.router.add_get("/internal/destinos", api.listar_destinos)
    app.router.add_post("/internal/destinos", api.crear_destino)
    app.router.add_put("/internal/destinos/{nombre}", api.actualizar_destino)
    app.router.add_delete("/internal/destinos/{nombre}", api.eliminar_destino)
    app.router.add_post("/internal/destinos/{nombre}/probar", api.probar_destino)
    app.router.add_post("/internal/destinos/{nombre}/reactivar", api.reactivar_destino)
