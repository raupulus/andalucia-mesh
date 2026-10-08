"""Pruebas unitarias de las reglas de supervisión añadidas y reajustadas."""

from datetime import UTC, datetime, timedelta

from detector.modelos import FromNodeInfo, PaqueteDecodificado
from detector.motor.bases import LineasBase
from detector.motor.estado import EstadoMotor
from detector.motor.protocolos import AlertaAbierta, ConfigGeneral, Contexto, Tick
from detector.reglas.battery_low import ConfigBatteryLow, ReglaBatteryLow
from detector.reglas.chutil_high import ConfigChutilHigh, ReglaChutilHigh
from detector.reglas.poll_abuse import ConfigPollAbuse, ReglaPollAbuse
from detector.reglas.position_flood import ConfigPositionFlood, ReglaPositionFlood
from detector.reglas.private_chaff import ConfigPrivateChaff, ReglaPrivateChaff
from detector.reglas.reboot_loop import ConfigRebootLoop, ReglaRebootLoop
from detector.reglas.router_role import ConfigRouterRole, ReglaRouterRole
from detector.reglas.sunset_battery import ConfigSunsetBattery, ReglaSunsetBattery
from detector.reglas.telemetry_burst import ConfigTelemetryBurst, ReglaTelemetryBurst
from detector.reglas.text_flood import ConfigTextFlood, ReglaTextFlood
from detector.reglas.traceroute_flood import ConfigTracerouteFlood, ReglaTracerouteFlood


def test_battery_low_roles_y_umbrales() -> None:
    """Verifica que battery-low solo alerte para routers y clientes con sus umbrales respectivos."""
    regla = ReglaBatteryLow()
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    # 1. Router al 35% -> alto (< 40%)
    rtr = estado.obtener_o_crear_nodo("!rtr", t0)
    rtr.role = "ROUTER"
    rtr.province = "ES-CA"
    rtr.battery_samples.extend([(t0 - timedelta(minutes=5), 36, 3.6), (t0, 35, 3.55)])

    ctx_rtr = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=rtr,
        estado=estado,
        bases=bases,
        config=ConfigBatteryLow(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: True,
    )
    alts = regla.comprobar(ctx_rtr)
    assert len(alts) == 1
    assert alts[0].riesgo == "alto"
    assert alts[0].datos["provincia"] == "ES-CA"
    assert alts[0].datos["dentro_andalucia"] is True

    # 2. Cliente al 30% -> bajo (< 35%, >= 15%)
    cli = estado.obtener_o_crear_nodo("!cli", t0)
    cli.role = "CLIENT"
    cli.battery_samples.extend([(t0 - timedelta(minutes=5), 32, 3.6), (t0, 30, 3.58)])

    ctx_cli = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=cli,
        estado=estado,
        bases=bases,
        config=ConfigBatteryLow(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )
    alts_cli = regla.comprobar(ctx_cli)
    assert len(alts_cli) == 1
    assert alts_cli[0].riesgo == "bajo"

    # 3. Sensor o Tracker con batería baja -> ignorado
    sensor = estado.obtener_o_crear_nodo("!sensor", t0)
    sensor.role = "SENSOR"
    sensor.battery_samples.extend([(t0 - timedelta(minutes=5), 10, 3.3), (t0, 10, 3.3)])

    ctx_sensor = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=sensor,
        estado=estado,
        bases=bases,
        config=ConfigBatteryLow(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )
    assert len(regla.comprobar(ctx_sensor)) == 0


def test_reboot_loop_umbrales_y_saneamiento() -> None:
    """Verifica 3 reinicios en 5 min (medio), 5 en 10 min (alto) y saneamiento a 30 min."""
    regla = ReglaRebootLoop()
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    nodo = estado.obtener_o_crear_nodo("!nodo1", t0)
    nodo.role = "CLIENT"
    # 3 reinicios en 4 minutos
    nodo.reboots_24h.extend([
        t0 - timedelta(minutes=4),
        t0 - timedelta(minutes=2),
        t0,
    ])

    ctx = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=nodo,
        estado=estado,
        bases=bases,
        config=ConfigRebootLoop(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )
    alts = regla.comprobar(ctx)
    assert len(alts) == 1
    assert alts[0].riesgo == "medio"

    # 5 reinicios en 8 minutos -> alto
    nodo.reboots_24h.extend([
        t0 - timedelta(minutes=7),
        t0 - timedelta(minutes=6),
    ])
    alts_alto = regla.comprobar(ctx)
    assert len(alts_alto) == 1
    assert alts_alto[0].riesgo == "alto"

    # Saneamiento a los 30 min
    abierta = AlertaAbierta(
        id="01TESTREBOOTLOOP0000000000",
        regla="reboot-loop",
        nodo="!nodo1",
        riesgo="alto",
        tipo="clientes",
        mensaje="Reinicios continuos",
    )
    ctx_25m = Contexto(
        ahora=t0 + timedelta(minutes=25),
        evento=Tick(ahora=t0 + timedelta(minutes=25)),
        nodo=nodo,
        estado=estado,
        bases=bases,
        config=ConfigRebootLoop(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )
    assert regla.sigue_activa(ctx_25m, abierta) is True

    ctx_35m = Contexto(
        ahora=t0 + timedelta(minutes=35),
        evento=Tick(ahora=t0 + timedelta(minutes=35)),
        nodo=nodo,
        estado=estado,
        bases=bases,
        config=ConfigRebootLoop(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )
    assert regla.sigue_activa(ctx_35m, abierta) is False


def test_chutil_high_ponderacion_roles() -> None:
    """Verifica umbrales de canal saturado con 60% peso a routers y 40% a clientes."""
    regla = ReglaChutilHigh()
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    # 1. Router al 35% de chutil -> medio (> 30%)
    rtr = estado.obtener_o_crear_nodo("!rtr", t0)
    rtr.role = "ROUTER"
    rtr.province = "ES-SE"
    rtr.last_channel_utilization = 35.0
    rtr.last_chutil_at = t0

    ctx_rtr = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=rtr,
        estado=estado,
        bases=bases,
        config=ConfigChutilHigh(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: True,
    )
    alts_rtr = regla.comprobar(ctx_rtr)
    assert len(alts_rtr) == 1
    assert alts_rtr[0].riesgo == "medio"
    assert alts_rtr[0].datos["peso_rol"] == 0.6
    assert alts_rtr[0].tipo == "infraestructura"

    # 2. Cliente al 42% -> alto (> 40%)
    cli = estado.obtener_o_crear_nodo("!cli", t0)
    cli.role = "CLIENT"
    cli.last_channel_utilization = 42.0

    ctx_cli = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=cli,
        estado=estado,
        bases=bases,
        config=ConfigChutilHigh(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )
    alts_cli = regla.comprobar(ctx_cli)
    assert len(alts_cli) == 1
    assert alts_cli[0].riesgo == "alto"
    assert alts_cli[0].datos["peso_rol"] == 0.4
    assert alts_cli[0].tipo == "clientes"

    # 3. Sensor -> ignorado
    sensor = estado.obtener_o_crear_nodo("!sensor", t0)
    sensor.role = "SENSOR"
    sensor.last_channel_utilization = 50.0

    ctx_sensor = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=sensor,
        estado=estado,
        bases=bases,
        config=ConfigChutilHigh(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )
    assert len(regla.comprobar(ctx_sensor)) == 0


def test_text_flood_umbrales_un_minuto() -> None:
    """Verifica que text-flood alerte en 1 min (> 5 bajo, 6-10 medio, > 10 alto)."""
    regla = ReglaTextFlood()
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    nodo = estado.obtener_o_crear_nodo("!spam", t0)
    nodo.role = "CLIENT"
    # 7 mensajes en los últimos 40 segundos -> medio
    for s in range(7):
        nodo.text_messages_10m.append(t0 - timedelta(seconds=s * 5))

    ctx = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=nodo,
        estado=estado,
        bases=bases,
        config=ConfigTextFlood(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )
    alts = regla.comprobar(ctx)
    assert len(alts) == 1
    assert alts[0].riesgo == "medio"

    # 12 mensajes -> alto
    for s in range(5):
        nodo.text_messages_10m.append(t0 - timedelta(seconds=s * 2))
    alts_alto = regla.comprobar(ctx)
    assert len(alts_alto) == 1
    assert alts_alto[0].riesgo == "alto"


def test_telemetry_burst_variantes_y_frecuencia() -> None:
    """Verifica detección de ráfagas combinadas (>= 2 en 1m) y constante (> 50/h)."""
    regla = ReglaTelemetryBurst()
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    nodo = estado.obtener_o_crear_nodo("!burst", t0)
    nodo.role = "CLIENT"
    # Ráfaga combinada: device_metrics + environment_metrics en 10 segundos
    nodo.telemetry_emissions_1h.append((t0 - timedelta(seconds=10), "device_metrics"))
    nodo.telemetry_emissions_1h.append((t0, "environment_metrics"))

    ctx = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=nodo,
        estado=estado,
        bases=bases,
        config=ConfigTelemetryBurst(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )
    alts = regla.comprobar(ctx)
    assert len(alts) == 1
    assert alts[0].riesgo == "bajo"
    assert alts[0].datos["tipo_emision"] == "ráfaga_combinada"

    # Constante acelerada: 55 emisiones en 1 hora del mismo tipo -> alto
    nodo.telemetry_emissions_1h.clear()
    for m in range(55):
        nodo.telemetry_emissions_1h.append((t0 - timedelta(minutes=m), "device_metrics"))

    alts_alto = regla.comprobar(ctx)
    assert len(alts_alto) == 1
    assert alts_alto[0].riesgo == "alto"
    assert alts_alto[0].datos["tipo_emision"] == "constante_acelerada"


def test_poll_abuse_broadcast() -> None:
    """Verifica detección de sondeos broadcast (1 bajo, >= 3 medio, >= 5 alto)."""
    regla = ReglaPollAbuse()
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    nodo = estado.obtener_o_crear_nodo("!poller", t0)
    pkt_poll = PaqueteDecodificado(
        packet_id=77,
        from_node_id="!poller",
        to="^all",
        portnum="telemetry",
        rx_first=t0,
        payload={"request_id": 12345},
    )
    nodo.broadcast_polls_1h.append((t0, "telemetry"))

    ctx = Contexto(
        ahora=t0,
        evento=pkt_poll,
        nodo=nodo,
        estado=estado,
        bases=bases,
        config=ConfigPollAbuse(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )
    alts = regla.comprobar(ctx)
    assert len(alts) == 1
    assert alts[0].riesgo == "bajo"

    # 3 sondeos en 10 min -> medio
    nodo.broadcast_polls_1h.append((t0 - timedelta(minutes=5), "telemetry"))
    nodo.broadcast_polls_1h.append((t0 - timedelta(minutes=2), "telemetry"))
    alts_med = regla.comprobar(ctx)
    assert len(alts_med) == 1
    assert alts_med[0].riesgo == "medio"


def test_sunset_battery_andalucia() -> None:
    """Verifica que sunset-battery evalúe a las 20:00 h en Andalucía y descarte fuera."""
    regla = ReglaSunsetBattery()
    estado = EstadoMotor()
    bases = LineasBase()
    # 20:00 hora local en Madrid en invierno (UTC+1) = 19:00 UTC
    t_20h = datetime(2026, 11, 1, 19, 0, 0, tzinfo=UTC)

    # 1. Router en Cádiz al 45% -> medio (< 60%)
    r_cadiz = estado.obtener_o_crear_nodo("!cadiz", t_20h)
    r_cadiz.role = "ROUTER"
    r_cadiz.province = "ES-CA"
    r_cadiz.battery_samples.append((t_20h, 45, 3.7))

    # 2. Router fuera de Andalucía al 30% -> no debe alertar
    r_fuera = estado.obtener_o_crear_nodo("!murcia", t_20h)
    r_fuera.role = "ROUTER"
    r_fuera.province = "FUERA"
    r_fuera.battery_samples.append((t_20h, 30, 3.5))

    ctx = Contexto(
        ahora=t_20h,
        evento=Tick(ahora=t_20h),
        nodo=None,
        estado=estado,
        bases=bases,
        config=ConfigSunsetBattery(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: True,
    )
    alts = regla.comprobar(ctx)
    assert len(alts) == 1
    assert alts[0].nodo == "!cadiz"
    assert alts[0].riesgo == "medio"
    assert alts[0].datos["provincia"] == "ES-CA"


def test_router_role_descarte_fuera_andalucia() -> None:
    """Verifica que router-role alerte solo en Andalucía si no está en routers_coordinados."""
    regla = ReglaRouterRole(config=ConfigRouterRole(routers_coordinados=["!aprobado"]))
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    # 1. Router no coordinado en Sevilla -> Alerta medio
    r_sevilla = estado.obtener_o_crear_nodo("!no_coord", t0)
    r_sevilla.role = "ROUTER"
    r_sevilla.province = "ES-SE"

    ctx_sev = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=r_sevilla,
        estado=estado,
        bases=bases,
        config=regla.config,
        general=ConfigGeneral(),
        es_infraestructura=lambda _: True,
    )
    alts_sev = regla.comprobar(ctx_sev)
    assert len(alts_sev) == 1
    assert alts_sev[0].regla == "router-role"
    assert alts_sev[0].riesgo == "medio"
    assert alts_sev[0].tipo == "infraestructura"

    # 2. Router aprobado en Sevilla -> Sin alerta
    r_aprobado = estado.obtener_o_crear_nodo("!aprobado", t0)
    r_aprobado.role = "ROUTER"
    r_aprobado.province = "ES-SE"

    ctx_aprob = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=r_aprobado,
        estado=estado,
        bases=bases,
        config=regla.config,
        general=ConfigGeneral(),
        es_infraestructura=lambda _: True,
    )
    assert len(regla.comprobar(ctx_aprob)) == 0

    # 3. Router fuera de Andalucía -> Descartado
    r_fuera = estado.obtener_o_crear_nodo("!madrid", t0)
    r_fuera.role = "ROUTER"
    r_fuera.province = "FUERA"

    ctx_fuera = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=r_fuera,
        estado=estado,
        bases=bases,
        config=regla.config,
        general=ConfigGeneral(),
        es_infraestructura=lambda _: True,
    )
    assert len(regla.comprobar(ctx_fuera)) == 0


def test_traceroute_flood_y_private_chaff() -> None:
    """Verifica reglas de traceroute abusivo y tráfico privado excesivo."""
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)
    estado = EstadoMotor()
    bases = LineasBase()

    # Traceroute flood (10 a 19 medio, >= 20 alto en 30m)
    r_trace = ReglaTracerouteFlood()
    n_trace = estado.obtener_o_crear_nodo("!tracer", t0)
    for _ in range(12):
        n_trace.traceroute_timestamps_1h.append(t0 - timedelta(minutes=5))

    ctx_trace = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=n_trace,
        estado=estado,
        bases=bases,
        config=ConfigTracerouteFlood(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )
    alts_trace = r_trace.comprobar(ctx_trace)
    assert len(alts_trace) == 1
    assert alts_trace[0].riesgo == "medio"

    # Private chaff (> 10 en 10 min o > 30/h medio, > 60/h alto)
    r_chaff = ReglaPrivateChaff()
    n_chaff = estado.obtener_o_crear_nodo("!chaff", t0)
    for _ in range(15):
        n_chaff.private_chaff_1h.append(t0 - timedelta(minutes=2))

    ctx_chaff = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=n_chaff,
        estado=estado,
        bases=bases,
        config=ConfigPrivateChaff(),
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )
    alts_chaff = r_chaff.comprobar(ctx_chaff)
    assert len(alts_chaff) == 1
    assert alts_chaff[0].riesgo == "medio"
