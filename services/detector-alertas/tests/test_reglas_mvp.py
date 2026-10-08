"""Pruebas unitarias completas de las 7 reglas MVP activas."""

from datetime import UTC, datetime, timedelta

from detector.modelos import FromNodeInfo, PaqueteDecodificado
from detector.motor.bases import LineasBase
from detector.motor.estado import EstadoMotor
from detector.motor.protocolos import AlertaAbierta, ConfigGeneral, Contexto, Tick
from detector.reglas.battery_low import ConfigBatteryLow, ReglaBatteryLow
from detector.reglas.flood import ConfigFlood, ReglaFlood
from detector.reglas.gateway_offline import ConfigGatewayOffline, ReglaGatewayOffline
from detector.reglas.hops_high import ConfigHopsHigh, ReglaHopsHigh
from detector.reglas.infra_silent import ConfigInfraSilent, ReglaInfraSilent
from detector.reglas.rafaga_masiva import ConfigRafagaMasiva, ReglaRafagaMasiva
from detector.reglas.reboot_loop import ConfigRebootLoop, ReglaRebootLoop


def test_regla_reboot_loop() -> None:
    """Verifica detección de reinicios continuos y resolución tras 120 min."""
    regla = ReglaRebootLoop()
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    nodo = estado.obtener_o_crear_nodo("!cad1", t0)
    nodo.role = "ROUTER"
    nodo.reboots_24h.extend([
        t0 - timedelta(minutes=40),
        t0 - timedelta(minutes=20),
        t0 - timedelta(minutes=5),
    ])

    ctx = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=nodo,
        estado=estado,
        bases=bases,
        config=ConfigRebootLoop(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: True,
    )

    alertas = regla.comprobar(ctx)
    assert len(alertas) == 1
    assert alertas[0].regla == "reboot-loop"
    assert alertas[0].riesgo == "alto"
    assert alertas[0].datos["reinicios"] == 3

    # Resolución tras 120 min sin reinicios
    abierta = AlertaAbierta(
        id="01JAC0Q4M1K2J3H4G5F6E7D8C9",
        regla="reboot-loop",
        nodo="!cad1",
        riesgo="alto",
        tipo="infraestructura",
        mensaje=alertas[0].mensaje,
        abierta_en=t0,
    )

    # A los 60 min sigue activa
    ctx_60m = Contexto(
        ahora=t0 + timedelta(minutes=60),
        evento=Tick(ahora=t0 + timedelta(minutes=60)),
        nodo=nodo,
        estado=estado,
        bases=bases,
        config=ConfigRebootLoop(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: True,
    )
    assert regla.sigue_activa(ctx_60m, abierta) is True

    # A los 125 min se resuelve
    ctx_125m = Contexto(
        ahora=t0 + timedelta(minutes=125),
        evento=Tick(ahora=t0 + timedelta(minutes=125)),
        nodo=nodo,
        estado=estado,
        bases=bases,
        config=ConfigRebootLoop(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: True,
    )
    assert regla.sigue_activa(ctx_125m, abierta) is False


def test_regla_battery_low() -> None:
    """Verifica umbrales de batería baja para router y cliente, y resolución al superar 50%."""
    regla = ReglaBatteryLow()
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    # 1. Router al 35% en 2 muestras consecutivas -> medio
    r_nodo = estado.obtener_o_crear_nodo("!r1", t0)
    r_nodo.role = "ROUTER"
    r_nodo.battery_samples.extend([
        (t0 - timedelta(minutes=5), 38, 3.7),
        (t0, 35, 3.65),
    ])

    ctx_r = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=r_nodo,
        estado=estado,
        bases=bases,
        config=ConfigBatteryLow(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: True,
    )
    alertas_r = regla.comprobar(ctx_r)
    assert len(alertas_r) == 1
    assert alertas_r[0].riesgo == "medio"

    # 2. Cliente al 18% en 2 muestras -> bajo
    c_nodo = estado.obtener_o_crear_nodo("!c1", t0)
    c_nodo.role = "CLIENT"
    c_nodo.battery_samples.extend([
        (t0 - timedelta(minutes=5), 19, 3.4),
        (t0, 18, 3.38),
    ])

    ctx_c = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=c_nodo,
        estado=estado,
        bases=bases,
        config=ConfigBatteryLow(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )
    alertas_c = regla.comprobar(ctx_c)
    assert len(alertas_c) == 1
    assert alertas_c[0].riesgo == "bajo"

    # 3. Resolución cuando la batería sube a 55%
    r_nodo.battery_samples.append((t0 + timedelta(hours=1), 55, 3.9))
    abierta = AlertaAbierta(
        id="01JAC0Q4M1K2J3H4G5F6E7D8C9",
        regla="battery-low",
        nodo="!r1",
        riesgo="medio",
        tipo="infraestructura",
        mensaje="",
    )
    assert regla.sigue_activa(ctx_r, abierta) is False


def test_regla_infra_silent() -> None:
    """Verifica detección de router inactivo y resolución cuando transmite."""
    regla = ReglaInfraSilent()
    estado = EstadoMotor(started_at=datetime(2026, 10, 1, 0, 0, 0, tzinfo=UTC))
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    # Router que no se oye desde hace 7 horas
    r_nodo = estado.obtener_o_crear_nodo("!r_silent", t0 - timedelta(hours=7))
    r_nodo.role = "ROUTER"
    r_nodo.last_seen = t0 - timedelta(hours=7)

    ctx = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=None,
        estado=estado,
        bases=bases,
        config=ConfigInfraSilent(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: True,
    )

    alertas = regla.comprobar(ctx)
    assert len(alertas) == 1
    assert alertas[0].regla == "infra-silent"
    assert alertas[0].riesgo == "medio"

    # Resolución cuando vuelve a oírse
    abierta = AlertaAbierta(
        id="01JAC0Q4M1K2J3H4G5F6E7D8C9",
        regla="infra-silent",
        nodo="!r_silent",
        riesgo="medio",
        tipo="infraestructura",
        mensaje="",
        abierta_en=t0,
    )
    assert regla.sigue_activa(ctx, abierta) is True

    # El router emite un paquete a las 12:05
    r_nodo.last_seen = t0 + timedelta(minutes=5)
    assert regla.sigue_activa(ctx, abierta) is False


def test_regla_gateway_offline() -> None:
    """Verifica detección de gateway desconectado en tick y resolución al reconectar."""
    regla = ReglaGatewayOffline()
    estado = EstadoMotor(started_at=datetime(2026, 10, 1, 0, 0, 0, tzinfo=UTC))
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    # Gateway sin recepciones desde hace 20 min
    gw = estado.obtener_o_crear_gateway("!gw01", t0 - timedelta(minutes=20))
    gw.last_seen_at = t0 - timedelta(minutes=20)

    ctx = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=None,
        estado=estado,
        bases=bases,
        config=ConfigGatewayOffline(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: True,
    )

    alertas = regla.comprobar(ctx)
    assert len(alertas) == 1
    assert alertas[0].regla == "gateway-offline"
    assert alertas[0].riesgo == "medio"

    abierta = AlertaAbierta(
        id="01JAC0Q4M1K2J3H4G5F6E7D8C9",
        regla="gateway-offline",
        nodo="!gw01",
        riesgo="medio",
        tipo="infraestructura",
        mensaje="",
        abierta_en=t0,
    )
    assert regla.sigue_activa(ctx, abierta) is True

    # Gateway reporta recepción posterior
    gw.last_seen_at = t0 + timedelta(minutes=2)
    assert regla.sigue_activa(ctx, abierta) is False


def test_regla_flood() -> None:
    """Verifica detección de spam/inundación y resolución tras 30 min normales."""
    regla = ReglaFlood()
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    nodo = estado.obtener_o_crear_nodo("!spammer", t0)
    # Simular 35 paquetes en los últimos 5 minutos
    nodo.packet_timestamps_10m.extend([t0 - timedelta(seconds=i * 5) for i in range(35)])

    pkt = PaqueteDecodificado(
        packet_id=99,
        from_node_id="!spammer",
        portnum="telemetry",
        rx_first=t0,
    )

    ctx = Contexto(
        ahora=t0,
        evento=pkt,
        nodo=nodo,
        estado=estado,
        bases=bases,
        config=ConfigFlood(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )

    alertas = regla.comprobar(ctx)
    assert len(alertas) == 1
    assert alertas[0].regla == "flood"
    assert alertas[0].riesgo == "medio"

    abierta = AlertaAbierta(
        id="01JAC0Q4M1K2J3H4G5F6E7D8C9",
        regla="flood",
        nodo="!spammer",
        riesgo="medio",
        tipo="infraestructura",
        mensaje="",
        evidencia_en=t0,
    )

    # 10 min después sigue activa
    ctx_10m = Contexto(
        ahora=t0 + timedelta(minutes=10),
        evento=Tick(ahora=t0 + timedelta(minutes=10)),
        nodo=nodo,
        estado=estado,
        bases=bases,
        config=ConfigFlood(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )
    # Vaciar timestamps para simular retorno a la normalidad
    nodo.packet_timestamps_10m.clear()
    assert regla.sigue_activa(ctx_10m, abierta) is True

    # 35 min después se resuelve
    ctx_35m = Contexto(
        ahora=t0 + timedelta(minutes=35),
        evento=Tick(ahora=t0 + timedelta(minutes=35)),
        nodo=nodo,
        estado=estado,
        bases=bases,
        config=ConfigFlood(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )
    assert regla.sigue_activa(ctx_35m, abierta) is False


def test_regla_rafaga_masiva() -> None:
    """Verifica detección de ráfaga masiva simultánea en la malla."""
    regla = ReglaRafagaMasiva()
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    # Simular 60 nodos distintos emitiendo en los últimos 60 segundos
    for i in range(60):
        estado.mesh_recent_nodes.append((t0 - timedelta(seconds=i), f"!node{i:03d}"))

    ctx = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=None,
        estado=estado,
        bases=bases,
        config=ConfigRafagaMasiva(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: True,
    )

    alertas = regla.comprobar(ctx)
    assert len(alertas) == 1
    assert alertas[0].regla == "rafaga-masiva"
    assert alertas[0].nodo == "all"
    assert alertas[0].riesgo == "alto"
    assert len(alertas[0].nodos) == 60


def test_regla_hops_high() -> None:
    """Verifica detección de hop_start >= 6 y resolución con 2 paquetes consecutivos <= 5."""
    regla = ReglaHopsHigh()
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    nodo = estado.obtener_o_crear_nodo("!hop_bad", t0)
    pkt_bad = PaqueteDecodificado(
        packet_id=1,
        from_node_id="!hop_bad",
        from_node=FromNodeInfo(short="BADHOP"),
        portnum="telemetry",
        rx_first=t0,
        hop_start=7,
    )
    nodo.hop_starts.append(7)

    ctx = Contexto(
        ahora=t0,
        evento=pkt_bad,
        nodo=nodo,
        estado=estado,
        bases=bases,
        config=ConfigHopsHigh(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )

    alertas = regla.comprobar(ctx)
    assert len(alertas) == 1
    assert alertas[0].regla == "hops-high"
    assert alertas[0].riesgo == "alto"
    assert "BADHOP usa 7 saltos" in alertas[0].mensaje

    # Probar con hop_start = 6 (debe ser bajo)
    pkt_6 = PaqueteDecodificado(
        packet_id=102,
        from_node_id="!hop_bad",
        from_node=FromNodeInfo(short="BADHOP", role="CLIENT"),
        portnum="telemetry",
        rx_first=t0,
        hop_start=6,
    )
    ctx_6 = Contexto(
        ahora=t0,
        evento=pkt_6,
        nodo=nodo,
        estado=estado,
        bases=bases,
        config=ConfigHopsHigh(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )
    alertas_6 = regla.comprobar(ctx_6)
    assert len(alertas_6) == 1
    assert alertas_6[0].riesgo == "bajo"

    abierta = AlertaAbierta(
        id="01JAC0Q4M1K2J3H4G5F6E7D8C9",
        regla="hops-high",
        nodo="!hop_bad",
        riesgo="alto",
        tipo="clientes",
        mensaje="",
    )

    # 1 solo paquete bueno: sigue activa
    nodo.hop_starts.append(3)
    assert regla.sigue_activa(ctx, abierta) is True

    # 2 paquetes buenos consecutivos: se resuelve
    nodo.hop_starts.append(3)
    assert regla.sigue_activa(ctx, abierta) is False
