"""Punto de entrada principal del microservicio de Webhooks y CLI de operador."""

import asyncio
import contextlib
import signal
import sys
from datetime import UTC, datetime
from pathlib import Path
from typing import Any

from nucleo.base import GestorBase
from nucleo.config import validar_o_salir
from nucleo.registro import configurar_registro
from nucleo.retencion import GestorRetencion
from nucleo.salud import ServidorSalud
from nucleo.socket_alertas import ClienteSocketAlertas
from webhooks.api_interna import registrar_api_interna
from webhooks.cli import orden_listar, orden_probar, orden_reactivar
from webhooks.configuracion import ConfiguracionWebhooks
from webhooks.motor_entregas import MotorEntregas

ID_BLOQUEO_WEBHOOKS = 10830


async def ejecutar_servicio(config: ConfiguracionWebhooks) -> None:
    """Arranca el demonio de Webhooks con sus componentes asíncronos."""
    logger = configurar_registro("webhooks", config.log_level)
    logger.info("Iniciando microservicio de Webhooks...")

    # Cargar y validar destinos de webhooks.yaml
    destinos = config.cargar_destinos()
    logger.info("%d destinos cargados y validados desde la configuración.", len(destinos))

    # Base de datos y migraciones
    gestor_base = GestorBase(config, "webhooks")
    await gestor_base.conectar()

    dir_migraciones = Path(__file__).resolve().parents[1] / "migrations"
    migradas = await gestor_base.aplicar_migraciones(dir_migraciones)
    logger.info("Migraciones de base de datos verificadas (%d aplicadas).", migradas)

    # Bloqueo consultivo de instancia única
    await gestor_base.adquirir_bloqueo_instancia(ID_BLOQUEO_WEBHOOKS)

    # Motor de entregas
    motor = MotorEntregas(config, gestor_base, destinos)
    await motor.iniciar()

    # Cliente del socket UNIX
    cliente_socket = ClienteSocketAlertas(
        ruta_socket=config.alertas_socket,
        nombre_cliente="webhooks",
        gestor_base=gestor_base,
        manejador_transicion=motor.procesar_transicion,
    )
    await cliente_socket.iniciar()

    # Gestor de retención de 365 días
    retencion = GestorRetencion(
        gestor_base=gestor_base,
        tabla_principal="entrega",
        dias_retencion=config.retencion_dias,
        zona_horaria=config.tz,
    )
    await retencion.iniciar()

    # Servidor de salud HTTP en 8080
    async def obtener_estado_salud() -> dict[str, Any]:
        base_ok = await gestor_base.comprobar_salud()
        socket_ok = cliente_socket.conectado

        # Métricas de entregas y destinos
        pendientes = 0
        mas_antiguo_s = 0
        ultima_ok_iso = None
        activos = 0
        inactivos = 0

        if base_ok:
            try:
                async with gestor_base.conexion() as conn, conn.cursor() as cur:
                    # Entregas pendientes y más antiguo
                    await cur.execute(
                        """
                        SELECT count(*), min(creado_en)
                        FROM entrega
                        WHERE estado = 'pendiente'
                        """
                    )
                    fila_ent = await cur.fetchone()
                    if fila_ent:
                        pendientes = fila_ent[0] or 0
                        if fila_ent[1]:
                            mas_antiguo_s = int((datetime.now(UTC) - fila_ent[1]).total_seconds())

                    # Última entrega ok
                    await cur.execute("SELECT max(ultimo_ok) FROM destino")
                    fila_ok = await cur.fetchone()
                    if fila_ok and fila_ok[0]:
                        ultima_ok_iso = fila_ok[0].strftime("%Y-%m-%dT%H:%M:%SZ")

                    # Conteo de destinos
                    await cur.execute("SELECT count(*) FROM destino WHERE activo = true")
                    r_act = await cur.fetchone()
                    activos = r_act[0] if r_act else 0

                    await cur.execute("SELECT count(*) FROM destino WHERE activo = false")
                    r_inact = await cur.fetchone()
                    inactivos = r_inact[0] if r_inact else 0
            except Exception as err:
                logger.error("Error consultando métricas de salud: %s", err)

        # 503 solo por base o socket según contrato §4.7 y 03-webhooks.md
        es_ok = base_ok and socket_ok

        ult_linea_iso = (
            cliente_socket.ultima_linea_at.strftime("%Y-%m-%dT%H:%M:%SZ")
            if cliente_socket.ultima_linea_at
            else None
        )

        return {
            "ok": es_ok,
            "servicio": "webhooks",
            "version": "1.0.0",
            "socket": {
                "conectado": socket_ok,
                "ultima_linea": ult_linea_iso,
                "cursor": cliente_socket.cursor_actual,
            },
            "base": {"ok": base_ok},
            "entregas": {
                "pendientes": pendientes,
                "mas_antiguo_s": mas_antiguo_s,
                "ultima_ok": ultima_ok_iso,
            },
            "destinos": {"activos": activos, "inactivos": inactivos},
        }

    servidor_salud = ServidorSalud(puerto=config.health_port, proveedor_estado=obtener_estado_salud)
    registrar_api_interna(servidor_salud.app, config, gestor_base, motor)
    await servidor_salud.iniciar()

    # Bucle de espera hasta señal de parada
    loop = asyncio.get_running_loop()
    parada_evento = asyncio.Event()

    for sig in (signal.SIGINT, signal.SIGTERM):
        with contextlib.suppress(NotImplementedError):
            loop.add_signal_handler(sig, parada_evento.set)

    logger.info("Microservicio de Webhooks en ejecución y a la espera de eventos.")
    await parada_evento.wait()

    logger.info("Señal de parada recibida. Deteniendo servicios...")
    await servidor_salud.detener()
    await retencion.detener()
    await cliente_socket.detener()
    await motor.detener()
    await gestor_base.cerrar()
    logger.info("Microservicio de Webhooks detenido limpiamente.")


def main() -> None:
    """Función de arranque CLI."""
    config = validar_o_salir(ConfiguracionWebhooks)
    assert isinstance(config, ConfiguracionWebhooks)

    args = sys.argv[1:]
    comando = args[0] if args else "servicio"

    if comando == "servicio":
        asyncio.run(ejecutar_servicio(config))
    elif comando == "listar":
        gestor_base = GestorBase(config, "webhooks-cli")
        asyncio.run(orden_listar(config, gestor_base))
    elif comando == "probar":
        if len(args) < 2:
            print("Uso: python -m webhooks probar <nombre>")
            sys.exit(1)
        asyncio.run(orden_probar(config, args[1]))
    elif comando == "reactivar":
        if len(args) < 2:
            print("Uso: python -m webhooks reactivar <nombre>")
            sys.exit(1)
        gestor_base = GestorBase(config, "webhooks-cli")
        asyncio.run(orden_reactivar(config, gestor_base, args[1]))
    else:
        print(f"Orden desconocida: {comando}. Órdenes válidas: servicio, listar, probar, reactivar")
        sys.exit(1)


if __name__ == "__main__":
    main()
