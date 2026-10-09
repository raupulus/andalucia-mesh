"""Punto de entrada principal para la ejecución del Detector de Alertas."""

import argparse
import asyncio
import contextlib
import json
import logging
import signal
import sys
from typing import Any

from detector.config import Settings
from detector.entrada import EntradaMQTT
from detector.motor.instantaneas import GestorInstantaneas
from detector.motor.motor import MotorAlertas
from detector.motor.protocolos import TransicionAlerta
from detector.motor.temporizador import TemporizadorMotor
from detector.persistencia import GestorPersistencia
from detector.retencion import TareaRetencion
from detector.salud import ServidorSalud
from detector.socket_alertas import ServidorSocketAlertas

logger = logging.getLogger("detector")


async def ejecutar_servicio(settings: Settings) -> None:
    """Ejecuta el demonio del Detector de Alertas en producción."""
    logger.info("Iniciando servicio Detector de Alertas (Andalucía Mesh)...")

    # 1. Capa de persistencia en PostgreSQL
    persistencia = GestorPersistencia(settings)
    await persistencia.conectar()

    if persistencia.conectado:
        try:
            from detector.migraciones import run_migrations
            await run_migrations(settings)
        except Exception as e:
            logger.error("Error al aplicar migraciones de base de datos: %s", e)

    # 2. Inicialización del motor de alertas
    motor = MotorAlertas(settings=settings)
    motor.inicializar()

    # 3. Restaurar último snapshot si existe en DB
    if persistencia.conectado and persistencia.pool:
        async with persistencia.pool.acquire() as conn:
            estado_previo = await GestorInstantaneas.restaurar_desde_db(conn)
            if estado_previo:
                motor.estado = estado_previo
                logger.info("Estado previo restaurado exitosamente en el motor.")
            await GestorInstantaneas.restaurar_ciclo_desde_db(conn, motor.ciclo)

    # 4. Servidor de Socket UNIX de salida
    socket_alertas = ServidorSocketAlertas(settings, persistencia)
    await socket_alertas.iniciar()

    # 5. Entrada MQTT
    entrada = EntradaMQTT(settings)

    # 6. Servidor HTTP de salud
    salud = ServidorSalud(settings, entrada, persistencia, socket_alertas, motor)
    await salud.iniciar()

    # 7. Tarea diaria de retención
    retencion = TareaRetencion(settings, persistencia)
    await retencion.iniciar()

    # Callback para manejar transiciones generadas por ticks periódicos
    async def manejar_transiciones(transiciones: list[TransicionAlerta]) -> None:
        for t in transiciones:
            await persistencia.guardar_transicion(t)
            socket_alertas.emitir_transicion(t)

    # 8. Temporizador de reloj sincronizado (ticks cada 60s)
    temporizador = TemporizadorMotor(motor, on_transiciones=manejar_transiciones)
    await temporizador.start()

    # Iniciar consumo de paquetes MQTT
    await entrada.iniciar()

    # Bucle de procesamiento de paquetes encolados
    detenido = asyncio.Event()

    async def bucle_paquetes() -> None:
        while not detenido.is_set():
            try:
                pkt = await entrada.cola.get()
                transiciones = motor.procesar_paquete(pkt)
                for t in transiciones:
                    await persistencia.guardar_transicion(t)
                    socket_alertas.emitir_transicion(t)
                entrada.cola.task_done()
            except asyncio.CancelledError:
                break
            except Exception as e:
                logger.error("Error al procesar paquete en motor: %s", e, exc_info=True)

    tarea_paquetes = asyncio.create_task(bucle_paquetes())

    # Bucle periódico de guardado de snapshots
    async def bucle_snapshots() -> None:
        while not detenido.is_set():
            try:
                await asyncio.sleep(settings.snapshot_s)
                if persistencia.conectado and persistencia.pool:
                    async with persistencia.pool.acquire() as conn:
                        await GestorInstantaneas.guardar_en_db(conn, motor.estado)
            except asyncio.CancelledError:
                break
            except Exception as e:
                logger.warning("Error periódico al guardar snapshot: %s", e)

    tarea_snapshots = asyncio.create_task(bucle_snapshots())

    # Manejo de señales de parada limpia
    loop = asyncio.get_running_loop()

    def solicitar_parada() -> None:
        logger.info("Señal de parada recibida. Iniciando apagado limpio...")
        detenido.set()

    for sig in (signal.SIGINT, signal.SIGTERM):
        with contextlib.suppress(NotImplementedError, RuntimeError):
            loop.add_signal_handler(sig, solicitar_parada)

    logger.info("Detector de Alertas operando a pleno rendimiento.")
    await detenido.wait()

    # Secuencia ordenada de apagado
    logger.info("Deteniendo componentes...")
    tarea_paquetes.cancel()
    tarea_snapshots.cancel()
    await entrada.detener()
    await temporizador.stop()
    await retencion.detener()

    # Guardar snapshot final al apagar
    if persistencia.conectado and persistencia.pool:
        try:
            async with persistencia.pool.acquire() as conn:
                await GestorInstantaneas.guardar_en_db(conn, motor.estado)
                logger.info("Instantánea final de estado guardada exitosamente.")
        except Exception as e:
            logger.warning("No se pudo guardar la instantánea final: %s", e)

    await socket_alertas.detener()
    await salud.detener()
    await persistencia.cerrar()
    logger.info("Detector de Alertas finalizado correctamente.")


async def ejecutar_escuchar(settings: Settings, socket_path: str, cliente: str, desde: str | None) -> None:
    """Cliente de consola para escuchar en tiempo real el socket UNIX de alertas."""
    path = socket_path or settings.alertas_socket
    logger.info("Conectando al socket UNIX en %s como '%s'...", path, cliente)

    try:
        reader, writer = await asyncio.open_unix_connection(path=path)
    except Exception as e:
        logger.error("No se pudo conectar al socket %s: %s", path, e)
        sys.exit(1)

    # 1. Enviar saludo obligatorio
    saludo: dict[str, Any] = {"cliente": cliente}
    if desde:
        saludo["desde"] = desde

    linea_saludo = json.dumps(saludo) + "\n"
    writer.write(linea_saludo.encode("utf-8"))
    await writer.drain()
    logger.info("Saludo enviado con éxito. Esperando eventos NDJSON...")

    # 2. Escuchar flujo de líneas NDJSON
    try:
        while True:
            linea = await reader.readline()
            if not linea:
                logger.info("Servidor cerró la conexión.")
                break

            texto = linea.decode("utf-8", errors="replace").strip()
            if not texto:
                continue

            try:
                evento = json.loads(texto)
                if "latido" in evento:
                    print(f"💓 [LATIDO] {evento['latido']}")
                elif "transicion" in evento:
                    t_id = evento.get("transicion_id")
                    trans = evento.get("transicion")
                    alt = evento.get("alerta", {})
                    regla = alt.get("regla")
                    riesgo = alt.get("riesgo")
                    nodo = alt.get("nodo")
                    msj = alt.get("mensaje")
                    print(f"🚨 [{trans.upper()}] {riesgo.upper()} | {regla} | {nodo} | {msj} (TID: {t_id})")
                else:
                    print(f"ℹ️ {texto}")
            except Exception:
                print(texto)
    except KeyboardInterrupt:
        logger.info("Interrupción por usuario.")
    finally:
        writer.close()
        await writer.wait_closed()


async def ejecutar_calibrar(settings: Settings, archivo_grabacion: str, aceleracion: float) -> None:
    """Reproduce una grabación de paquetes para calibración y pruebas de estrés."""
    logger.info("Modo calibración: reproduciendo archivo %s (aceleración: %.1fx)...", archivo_grabacion, aceleracion)
    motor = MotorAlertas(settings=settings)
    motor.inicializar()

    total_paquetes = 0
    total_alertas = 0

    with open(archivo_grabacion, encoding="utf-8") as f:
        for linea in f:
            linea_txt = linea.strip()
            if not linea_txt:
                continue
            total_paquetes += 1
            try:
                from detector.modelos import PaqueteDecodificado
                pkt = PaqueteDecodificado.model_validate_json(linea_txt)
                transiciones = motor.procesar_paquete(pkt)
                for t in transiciones:
                    total_alertas += 1
                    print(f"[{t.transicion.upper()}] {t.alerta.riesgo.upper()} {t.alerta.regla} -> {t.alerta.mensaje}")
            except Exception as e:
                logger.debug("Error procesando línea de grabación: %s", e)

    print("\n--- Resumen de Calibración ---")
    print(f"Paquetes procesados: {total_paquetes}")
    print(f"Transiciones generadas: {total_alertas}")
    print(f"Alertas abiertas finales: {len(motor.ciclo.alertas_abiertas)}")


def main() -> None:
    """Punto de entrada de línea de comandos."""
    parser = argparse.ArgumentParser(
        prog="detector",
        description="Microservicio de detección de anomalías y alertas de Andalucía Mesh",
    )
    subparsers = parser.add_subparsers(dest="subcomando", help="Subcomando a ejecutar")

    # Subcomando: servicio (por defecto)
    parser_servicio = subparsers.add_parser("servicio", help="Ejecuta el demonio del detector de alertas")
    parser_servicio.add_argument("--log-level", default=None, help="Nivel de logging (DEBUG, INFO, WARNING, ERROR)")

    # Subcomando: escuchar
    parser_escuchar = subparsers.add_parser("escuchar", help="Escucha eventos del socket UNIX en consola")
    parser_escuchar.add_argument("--socket", default=None, help="Ruta alternativa al socket UNIX")
    parser_escuchar.add_argument("--cliente", default="cli-escuchar", help="Identificador del cliente")
    parser_escuchar.add_argument("--desde", default=None, help="ULID desde el cual pedir reenvío histórico")

    # Subcomando: calibrar
    parser_calibrar = subparsers.add_parser("calibrar", help="Reproduce una grabación de paquetes")
    parser_calibrar.add_argument("archivo", help="Ruta al archivo .jsonl o .ndjson de paquetes")
    parser_calibrar.add_argument("--aceleracion", type=float, default=1.0, help="Factor de aceleración de tiempo")

    args = parser.parse_args()

    settings = Settings()
    if hasattr(args, "log_level") and args.log_level:
        settings.log_level = args.log_level

    logging.basicConfig(
        level=getattr(logging, settings.log_level.upper(), logging.INFO),
        format="%(asctime)s [%(levelname)s] %(name)s: %(message)s",
    )

    subcomando = args.subcomando or "servicio"

    if subcomando == "servicio":
        asyncio.run(ejecutar_servicio(settings))
    elif subcomando == "escuchar":
        asyncio.run(ejecutar_escuchar(settings, args.socket, args.cliente, args.desde))
    elif subcomando == "calibrar":
        asyncio.run(ejecutar_calibrar(settings, args.archivo, args.aceleracion))
    else:
        parser.print_help()


if __name__ == "__main__":
    main()
