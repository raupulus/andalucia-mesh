"""Órdenes de operador en línea de comandos para el servicio de Webhooks."""

import sys
import time

import aiohttp

from nucleo.base import GestorBase
from webhooks.configuracion import ConfiguracionWebhooks, DestinoWebhook
from webhooks.cuerpo import calcular_firma_hmac, construir_cuerpo_webhook
from webhooks.red_segura import resolver_y_validar_host


async def orden_listar(config: ConfiguracionWebhooks, gestor_base: GestorBase) -> None:
    """Muestra el listado de destinos registrados y su estado actual."""
    await gestor_base.conectar()
    try:
        async with gestor_base.conexion() as conn, conn.cursor() as cur:
            await cur.execute(
                """
                SELECT d.nombre, d.host, d.activo, d.motivo_baja, d.fallos_seguidos, d.ultimo_ok,
                       (SELECT count(*) FROM entrega e WHERE e.destino_id = d.id AND e.estado = 'pendiente') AS pendientes
                FROM destino d
                ORDER BY d.nombre ASC
                """
            )
            filas = await cur.fetchall()

        if not filas:
            print("No hay destinos registrados en la base de datos.")
            return

        print(f"{'NOMBRE':<25} {'HOST':<25} {'ESTADO':<10} {'FALLOS':<8} {'PENDIENTES':<10} {'ÚLTIMO OK'}")
        print("-" * 95)
        for nombre, host, activo, motivo_baja, fallos, ultimo_ok, pendientes in filas:
            estado_txt = "ACTIVO" if activo else f"BAJA ({motivo_baja})"
            ult_ok_txt = ultimo_ok.strftime("%Y-%m-%d %H:%M:%S") if ultimo_ok else "—"
            print(f"{nombre:<25} {host:<25} {estado_txt:<10} {fallos:<8} {pendientes:<10} {ult_ok_txt}")
    finally:
        await gestor_base.cerrar()


async def orden_reactivar(config: ConfiguracionWebhooks, gestor_base: GestorBase, nombre: str) -> None:
    """Reactiva manualmente un destino desactivado."""
    await gestor_base.conectar()
    try:
        async with gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
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
                print(f"ERROR: Destino no encontrado: {nombre}")
                sys.exit(1)
        print(f"Destino '{nombre}' reactivado con éxito.")
    finally:
        await gestor_base.cerrar()


async def orden_probar(config: ConfiguracionWebhooks, nombre: str) -> None:
    """Envía un ping de prueba firmado de forma inmediata sin encolar."""
    cfg_destino: DestinoWebhook | None = None
    gestor_base = GestorBase(config, "webhooks-cli")
    try:
        await gestor_base.conectar()
        async with gestor_base.conexion() as conn, conn.cursor() as cur:
            await cur.execute(
                """
                SELECT nombre, url, host, url_hash, riesgos, tipos, provincias, nodos, secreto
                FROM destino
                WHERE nombre = %s
                """,
                (nombre,),
            )
            fila = await cur.fetchone()
            if fila:
                cfg_destino = DestinoWebhook(
                    nombre=fila[0],
                    url=fila[1],
                    host=fila[2],
                    url_hash=fila[3],
                    riesgos=list(fila[4]) if fila[4] else None,
                    tipos=list(fila[5]) if fila[5] else None,
                    provincias=list(fila[6]) if fila[6] else None,
                    nodos=list(fila[7]) if fila[7] else None,
                    secreto=fila[8] or "",
                )
    except Exception:
        pass
    finally:
        await gestor_base.cerrar()

    if not cfg_destino:
        destinos = config.cargar_destinos()
        dest_map = {d.nombre: d for d in destinos}
        cfg_destino = dest_map.get(nombre)

    if not cfg_destino:
        print(f"ERROR: Destino '{nombre}' no encontrado en base de datos ni en webhooks.yaml.")
        sys.exit(1)

    redes_bloqueadas = config.parsear_redes_bloqueadas()
    print(f"Probando envío de ping firmado a '{nombre}' ({cfg_destino.url})...")

    # 1. Validación SSRF del host
    try:
        ips = await resolver_y_validar_host(cfg_destino.host, redes_bloqueadas)
        print(f"DNS resuelto de forma segura a IP(s): {', '.join(ips)}")
    except Exception as e:
        print(f"ERROR: Fallo de validación DNS / SSRF: {e}")
        sys.exit(1)

    # 2. Construcción del ping y firma
    cuerpo_str, cuerpo_bytes = construir_cuerpo_webhook(
        transicion_id="01PING00000000000000000000",
        transicion_es="ping",
        first_delivery=False,
        alerta_dict=None,
        dominio_proyecto=config.project_domain,
    )
    firma = calcular_firma_hmac(cfg_destino.secreto, cuerpo_bytes)

    cabeceras = {
        "Content-Type": "application/json; charset=utf-8",
        "User-Agent": f"{config.project_name} webhooks/1.0.0 (+https://{config.project_domain}/bots; {config.project_contact})",
        "X-SNM-Transicion": "01PING00000000000000000000",
        "X-SNM-Firma": firma,
        "X-SNM-Intento": "1",
    }

    inicio = time.monotonic()
    timeout = aiohttp.ClientTimeout(total=float(config.webhooks_timeout_s), connect=5.0)
    async with aiohttp.ClientSession(timeout=timeout, trust_env=False) as session:
        try:
            async with session.post(cfg_destino.url, data=cuerpo_bytes, headers=cabeceras, allow_redirects=False) as resp:
                duracion_ms = int((time.monotonic() - inicio) * 1000)
                print(f"Resultado: HTTP {resp.status} ({resp.reason}) en {duracion_ms} ms")
                if 200 <= resp.status < 300:
                    print("Prueba completada con éxito.")
                else:
                    print(f"Aviso: El receptor devolvió un código fuera de 2xx (HTTP {resp.status}).")
        except Exception as e:
            duracion_ms = int((time.monotonic() - inicio) * 1000)
            print(f"ERROR: Fallo en la conexión HTTP tras {duracion_ms} ms: {e}")
            sys.exit(1)
