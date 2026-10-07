"""Pruebas integrales de flujo completo del motor, salud y deduplicación."""

from datetime import UTC, datetime, timedelta

import aiohttp
import pytest

from detector.config import Settings
from detector.entrada import EntradaMQTT
from detector.modelos import DeviceMetrics, FromNodeInfo, PaqueteDecodificado, PaquetePayload
from detector.motor.motor import MotorAlertas
from detector.persistencia import GestorPersistencia
from detector.salud import ServidorSalud
from detector.socket_alertas import ServidorSocketAlertas


def test_entrada_deduplicacion_cache() -> None:
    """Verifica que la caché de deduplicación de entrada rechace duplicados en la ventana de 10 min."""
    settings = Settings()
    entrada = EntradaMQTT(settings)

    t0 = 1760000000.0  # Epoch segundos
    # Primer paquete
    assert not entrada.es_duplicado("!node1", 1234, t0)
    # Mismo paquete 30 segundos después -> duplicado
    assert entrada.es_duplicado("!node1", 1234, t0 + 30.0)
    # Mismo paquete pero de otro nodo -> no duplicado
    assert not entrada.es_duplicado("!node2", 1234, t0 + 30.0)
    # Mismo nodo pero otro packet_id -> no duplicado
    assert not entrada.es_duplicado("!node1", 1235, t0 + 30.0)
    # Mismo paquete tras 601 segundos (expirado) -> no duplicado
    assert not entrada.es_duplicado("!node1", 1234, t0 + 601.0)


def test_motor_flujo_completo_alertas_y_resolucion() -> None:
    """Verifica el flujo integral del motor desde el paquete hasta la apertura y resolución de alertas."""
    settings = Settings()
    motor = MotorAlertas(settings=settings)
    motor.inicializar()

    t0 = datetime(2026, 10, 1, 10, 0, 0, tzinfo=UTC)

    # 1. Enviar paquete con hop_start = 7 -> debe emitir alerta hops-high
    pkt_hops = PaqueteDecodificado(
        packet_id=101,
        from_node_id="!router1",
        from_node=FromNodeInfo(short="RTR1", role="ROUTER", province="ES-CA"),
        portnum="telemetry",
        rx_first=t0,
        hop_start=7,
    )
    transiciones = motor.procesar_paquete(pkt_hops, ahora=t0)
    assert len(transiciones) == 1
    assert transiciones[0].transicion == "abierta"
    assert transiciones[0].alerta.regla == "hops-high"
    assert transiciones[0].alerta.riesgo == "medio"
    assert transiciones[0].alerta.tipo == "infraestructura"

    # 2. Enviar 2 paquetes consecutivos válidos con hop_start = 3 -> debe resolverse
    t1 = t0 + timedelta(minutes=2)
    pkt_norm1 = PaqueteDecodificado(
        packet_id=102,
        from_node_id="!router1",
        from_node=FromNodeInfo(short="RTR1", role="ROUTER", province="ES-CA"),
        portnum="telemetry",
        rx_first=t1,
        hop_start=3,
    )
    motor.procesar_paquete(pkt_norm1, ahora=t1)

    t2 = t1 + timedelta(minutes=2)
    pkt_norm2 = PaqueteDecodificado(
        packet_id=103,
        from_node_id="!router1",
        from_node=FromNodeInfo(short="RTR1", role="ROUTER", province="ES-CA"),
        portnum="telemetry",
        rx_first=t2,
        hop_start=3,
    )
    trans_res = motor.procesar_paquete(pkt_norm2, ahora=t2)
    assert len(trans_res) == 1
    assert trans_res[0].transicion == "resuelta"
    assert trans_res[0].alerta.regla == "hops-high"


def test_motor_battery_low_y_recuperacion() -> None:
    """Verifica detección de batería baja en cliente y resolución al subir de 50%."""
    settings = Settings()
    motor = MotorAlertas(settings=settings)
    motor.inicializar()

    t0 = datetime(2026, 10, 1, 10, 0, 0, tzinfo=UTC)

    # 1. Dos lecturas consecutivas al 15% para un nodo CLIENT (bajo)
    for i in range(2):
        t = t0 + timedelta(minutes=i * 5)
        pkt = PaqueteDecodificado(
            packet_id=200 + i,
            from_node_id="!cli_node",
            from_node=FromNodeInfo(short="CLIENT1", role="CLIENT"),
            portnum="telemetry",
            rx_first=t,
            payload=PaquetePayload(
                device_metrics=DeviceMetrics(battery_level=15, voltage=3.55)
            ).model_dump(exclude_unset=True),
        )
        trans = motor.procesar_paquete(pkt, ahora=t)
        if i == 0:
            assert len(trans) == 0  # Exige mínimo 2 muestras
        else:
            assert len(trans) == 1
            assert trans[0].transicion == "abierta"
            assert trans[0].alerta.regla == "battery-low"
            assert trans[0].alerta.riesgo == "bajo"
            assert trans[0].alerta.tipo == "clientes"

    # 2. Subir batería a 80% -> debe resolverse
    t_rec = t0 + timedelta(minutes=20)
    pkt_rec = PaqueteDecodificado(
        packet_id=205,
        from_node_id="!cli_node",
        from_node=FromNodeInfo(short="CLIENT1", role="CLIENT"),
        portnum="telemetry",
        rx_first=t_rec,
        payload=PaquetePayload(
            device_metrics=DeviceMetrics(battery_level=80, voltage=4.12)
        ).model_dump(exclude_unset=True),
    )
    trans_rec = motor.procesar_paquete(pkt_rec, ahora=t_rec)
    assert len(trans_rec) == 1
    assert trans_rec[0].transicion == "resuelta"
    assert trans_rec[0].alerta.regla == "battery-low"


@pytest.mark.asyncio
async def test_servidor_salud_endpoint() -> None:
    """Verifica que el servidor HTTP de salud responda correctamente en /health."""
    test_port = 18088
    settings = Settings(health_port=test_port)
    persistencia = GestorPersistencia(settings)
    motor = MotorAlertas(settings=settings)
    motor.inicializar()
    entrada = EntradaMQTT(settings)
    socket_alertas = ServidorSocketAlertas(settings, persistencia)

    salud = ServidorSalud(
        settings=settings,
        entrada=entrada,
        persistencia=persistencia,
        socket_alertas=socket_alertas,
        motor=motor,
    )
    await salud.iniciar()

    try:
        url = f"http://127.0.0.1:{test_port}/health"
        async with aiohttp.ClientSession() as session, session.get(url) as resp:
            assert resp.status == 200
            data = await resp.json()
            assert data["servicio"] == "detector-alertas"
            assert "status" in data
            assert "mqtt" in data
            assert "postgres" in data
            assert "alertas" in data
            assert "motor" in data
            assert data["motor"]["reglas_activas"] >= 7
    finally:
        await salud.detener()


def test_motor_tick_ausencias() -> None:
    """Verifica que ejecutar_tick evalúe ausencias de routers y pasarelas."""
    settings = Settings()
    motor = MotorAlertas(settings=settings)
    motor.inicializar()

    # t0 hace 8 horas para que pase el período de gracia inicial y supere minimo_h de 6h
    t0 = datetime(2026, 10, 1, 18, 0, 0, tzinfo=UTC)
    motor.estado.started_at = t0 - timedelta(hours=8)

    # 1. Router conocido con last_seen hace 7 h e intervalo típico de 10 min
    rtr = motor.estado.obtener_o_crear_nodo("!rtr_silente", t0)
    rtr.role = "ROUTER"
    rtr.short = "SILENT"
    rtr.province = "ES-CA"
    rtr.last_seen = t0 - timedelta(hours=7)
    rtr.intervals.extend([600.0, 600.0, 600.0])

    # 2. Gateway conocido con last_seen hace 45 min
    gw = motor.estado.obtener_o_crear_gateway("!gw_silente", t0)
    gw.last_seen_at = t0 - timedelta(minutes=45)
    gw.reception_intervals.extend([120.0, 120.0, 120.0])

    transiciones = motor.ejecutar_tick(t0)
    reglas_emitidas = {t.alerta.regla for t in transiciones}
    assert "infra-silent" in reglas_emitidas
    assert "gateway-offline" in reglas_emitidas


@pytest.mark.asyncio
async def test_tarea_retencion_ejecucion() -> None:
    """Verifica la ejecución manual de la tarea de retención delegando en la persistencia."""
    from unittest.mock import AsyncMock

    from detector.retencion import TareaRetencion

    settings = Settings()
    mock_persistencia = AsyncMock(spec=GestorPersistencia)
    mock_persistencia.purgar_retencion = AsyncMock(return_value=(15, 2))

    retencion = TareaRetencion(settings=settings, persistencia=mock_persistencia)
    alertas, snapshots = await retencion.ejecutar_ahora()

    assert alertas == 15
    assert snapshots == 2
    assert retencion.ultima_ejecucion is not None

