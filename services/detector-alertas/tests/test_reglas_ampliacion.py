"""Pruebas unitarias para las reglas de ampliación del Detector de Alertas.

Cubre las reglas:
- airtime-high (recalibrada: > 4% bajo, > 6% medio, > 8% alto, saneamiento <= 3%)
- gateway-no-traffic (> 1h sin recepciones LoRa, riesgo bajo, tipo infraestructura)
- key-security (entropía estructural y cambio de clave; routers alto, clientes medio)
- router-moving (desplazamiento > 5 km en 24h para roles de infraestructura)
- router-cluster (enlace directo simultáneo con >= 3 routers; excluye ROUTER_LATE)
- asymmetric-link (delta SNR > 6 dB entre CLIENT, CLIENT_BASE y ROUTER; excluye CLIENT_MUTE)
"""

import base64
from datetime import UTC, datetime, timedelta

from detector.modelos import PaqueteDecodificado
from detector.motor.bases import LineasBase
from detector.motor.estado import EstadoMotor
from detector.motor.protocolos import AlertaAbierta, ConfigGeneral, Contexto, Tick
from detector.reglas.airtime_high import ConfigAirtimeHigh, ReglaAirtimeHigh
from detector.reglas.asymmetric_link import ConfigAsymmetricLink, ReglaAsymmetricLink
from detector.reglas.gateway_no_traffic import ConfigGatewayNoTraffic, ReglaGatewayNoTraffic
from detector.reglas.key_security import ConfigKeySecurity, ReglaKeySecurity
from detector.reglas.router_cluster import ConfigRouterCluster, ReglaRouterCluster
from detector.reglas.router_moving import ConfigRouterMoving, ReglaRouterMoving


def test_airtime_high_umbrales_y_resolucion() -> None:
    """Verifica umbrales de airtime-high (4% bajo, 6% medio, 8% alto) y resolución (<= 3%)."""
    regla = ReglaAirtimeHigh(ConfigAirtimeHigh(bajo=4.0, medio=6.0, alto=8.0, resolver=3.0))
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    nodo = estado.obtener_o_crear_nodo("!rtr1", t0)
    nodo.role = "ROUTER"

    def crear_ctx(air_val: float) -> Contexto:
        pkt = PaqueteDecodificado(
            packet_id=101,
            from_node_id="!rtr1",
            to="^all",
            portnum="telemetry",
            payload={"device_metrics": {"air_util_tx": air_val}},
            rx_first=t0.isoformat(),
        )
        return Contexto(
            ahora=t0,
            evento=pkt,
            nodo=nodo,
            estado=estado,
            bases=bases,
            config=regla.config,
            general=ConfigGeneral(),
            es_infraestructura=lambda _: True,
        )

    # 1. Por debajo del umbral bajo (3.5%) -> 0 alertas
    assert len(regla.comprobar(crear_ctx(3.5))) == 0

    # 2. Umbral bajo (> 4.0%, ej. 4.8%) -> 1 alerta bajo
    alts_bajo = regla.comprobar(crear_ctx(4.8))
    assert len(alts_bajo) == 1
    assert alts_bajo[0].riesgo == "bajo"
    assert alts_bajo[0].datos["air_util_tx"] == 4.8

    # 3. Umbral medio (> 6.0%, ej. 6.5%) -> 1 alerta medio
    alts_medio = regla.comprobar(crear_ctx(6.5))
    assert len(alts_medio) == 1
    assert alts_medio[0].riesgo == "medio"

    # 4. Umbral alto (> 8.0%, ej. 9.1%) -> 1 alerta alto
    alts_alto = regla.comprobar(crear_ctx(9.1))
    assert len(alts_alto) == 1
    assert alts_alto[0].riesgo == "alto"

    # 5. Soporte en local_stats si device_metrics no tiene air_util_tx
    pkt_ls = PaqueteDecodificado(
        packet_id=102,
        from_node_id="!rtr1",
        to="^all",
        portnum="telemetry",
        payload={"local_stats": {"air_util_tx": 5.0}},
        rx_first=t0.isoformat(),
    )
    ctx_ls = Contexto(
        ahora=t0,
        evento=pkt_ls,
        nodo=nodo,
        estado=estado,
        bases=bases,
        config=regla.config,
        general=ConfigGeneral(),
        es_infraestructura=lambda _: True,
    )
    alts_ls = regla.comprobar(ctx_ls)
    assert len(alts_ls) == 1
    assert alts_ls[0].riesgo == "bajo"

    # 6. Saneamiento: sigue_activa da False si air_util_tx <= 3.0%
    abierta = AlertaAbierta(
        id="01TESTAIRTIME0000000000001",
        regla=regla.id,
        nodo="!rtr1",
        abierta_en=t0,
        actualizada_en=t0,
        riesgo="alto",
        mensaje="Airtime alto",
        tipo="infraestructura",
    )
    assert regla.sigue_activa(crear_ctx(5.0), abierta) is True
    assert regla.sigue_activa(crear_ctx(3.0), abierta) is False
    assert regla.sigue_activa(crear_ctx(2.1), abierta) is False


def test_gateway_no_traffic() -> None:
    """Verifica detección de pasarela con radio LoRa sin tráfico durante más de 1 hora."""
    regla = ReglaGatewayNoTraffic(ConfigGatewayNoTraffic(ventana_s=3600, umbral_min=60.0))
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 14, 0, 0, tzinfo=UTC)

    ctx_tick = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=None,
        estado=estado,
        bases=bases,
        config=regla.config,
        general=ConfigGeneral(),
        es_infraestructura=lambda _: True,
    )

    # 1. Gateway sin tráfico previo -> no alerta
    gw_nuevo = estado.obtener_o_crear_gateway("!gw_nuevo", t0)
    gw_nuevo.has_prior_traffic = False
    assert len(regla.comprobar(ctx_tick)) == 0

    # 2. Gateway con tráfico previo reciente (hace 10 minutos) -> no alerta
    gw_activo = estado.obtener_o_crear_gateway("!gw_activo", t0)
    gw_activo.has_prior_traffic = True
    gw_activo.last_lora_rx_at = t0 - timedelta(minutes=10)
    assert len(regla.comprobar(ctx_tick)) == 0

    # 3. Gateway con tráfico previo pero silente hace 70 minutos (> 60 min) -> alerta bajo
    gw_sordo = estado.obtener_o_crear_gateway("!gw_sordo", t0)
    gw_sordo.has_prior_traffic = True
    gw_sordo.last_lora_rx_at = t0 - timedelta(minutes=70)

    nodo_gw = estado.obtener_o_crear_nodo("!gw_sordo", t0)
    nodo_gw.short = "GWSOR"

    alts = regla.comprobar(ctx_tick)
    assert len(alts) == 1
    assert alts[0].regla == "gateway-no-traffic"
    assert alts[0].riesgo == "bajo"
    assert alts[0].tipo == "infraestructura"
    assert alts[0].nodo == "!gw_sordo"
    assert alts[0].datos["silencio_min"] == 70.0

    # 4. Saneamiento: si llega una nueva recepción LoRa posterior a la alerta
    abierta = AlertaAbierta(
        id="01TESTGWNOTRAFFIC000000001",
        regla=regla.id,
        nodo="!gw_sordo",
        abierta_en=t0,
        actualizada_en=t0,
        riesgo="bajo",
        mensaje="Gateway sordo",
        tipo="infraestructura",
    )
    # Aún no ha recibido paquete nuevo
    assert regla.sigue_activa(ctx_tick, abierta) is True

    # Llega nueva recepción después de abierta
    gw_sordo.last_lora_rx_at = t0 + timedelta(minutes=2)
    assert regla.sigue_activa(ctx_tick, abierta) is False


def test_key_security_entropia_y_cambio_clave() -> None:
    """Verifica detección de claves débiles y cambios inesperados de clave."""
    regla = ReglaKeySecurity(ConfigKeySecurity())
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    # Clave válida de 32 bytes con alta entropía
    clave_valida = bytes([
        0x1b, 0x4f, 0x93, 0x22, 0x8a, 0x07, 0xfe, 0x55, 0x33, 0x11, 0x77, 0x99, 0xbb, 0xdd, 0xaa, 0xcc,
        0x10, 0x25, 0x34, 0x78, 0x9a, 0xbc, 0xde, 0xf0, 0x12, 0x38, 0x47, 0x56, 0x65, 0x74, 0x83, 0x92,
    ])
    clave_valida_b64 = base64.b64encode(clave_valida).decode("ascii")

    # Clave débil 1: todos los bytes iguales (0x00)
    clave_ceros_b64 = base64.b64encode(b"\x00" * 32).decode("ascii")
    # Clave débil 2: secuencia consecutiva 0..31
    clave_secuencia_b64 = base64.b64encode(bytes(range(32))).decode("ascii")
    # Clave débil 3: baja entropía (< 16 distintos)
    clave_baja_entropia_b64 = base64.b64encode(b"\x12\x34" * 16).decode("ascii")
    # Clave débil 4: patrón repetido de 4 bytes
    clave_patron_b64 = base64.b64encode(b"ABCD" * 8).decode("ascii")

    # Caso 1: Clave de ceros en ROUTER -> riesgo alto
    rtr = estado.obtener_o_crear_nodo("!rtr1", t0)
    rtr.role = "ROUTER"
    rtr.short = "RTR1"

    pkt_ceros = PaqueteDecodificado(
        packet_id=201,
        from_node_id="!rtr1",
        to="^all",
        portnum="nodeinfo",
        payload={"public_key": clave_ceros_b64},
        rx_first=t0.isoformat(),
    )
    ctx_ceros = Contexto(
        ahora=t0,
        evento=pkt_ceros,
        nodo=rtr,
        estado=estado,
        bases=bases,
        config=regla.config,
        general=ConfigGeneral(),
        es_infraestructura=lambda _: True,
    )
    alts = regla.comprobar(ctx_ceros)
    assert len(alts) == 1
    assert alts[0].riesgo == "alto"
    assert alts[0].tipo == "infraestructura"
    assert alts[0].datos["tipo_anomalia"] == "clave_debil"

    # Caso 2: Clave de secuencia en CLIENT -> riesgo medio
    cli = estado.obtener_o_crear_nodo("!cli1", t0)
    cli.role = "CLIENT"
    cli.short = "CLI1"

    pkt_seq = PaqueteDecodificado(
        packet_id=202,
        from_node_id="!cli1",
        to="^all",
        portnum="nodeinfo",
        payload={"public_key": clave_secuencia_b64},
        rx_first=t0.isoformat(),
    )
    ctx_seq = Contexto(
        ahora=t0,
        evento=pkt_seq,
        nodo=cli,
        estado=estado,
        bases=bases,
        config=regla.config,
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )
    alts_cli = regla.comprobar(ctx_seq)
    assert len(alts_cli) == 1
    assert alts_cli[0].riesgo == "medio"
    assert alts_cli[0].tipo == "clientes"

    # Caso 3: Clave de baja entropía y patrón repetido
    pkt_baja = PaqueteDecodificado(
        packet_id=203,
        from_node_id="!cli1",
        to="^all",
        portnum="nodeinfo",
        payload={"public_key": clave_baja_entropia_b64},
        rx_first=t0.isoformat(),
    )
    assert len(regla.comprobar(Contexto(
        ahora=t0,
        evento=pkt_baja,
        nodo=cli,
        estado=estado,
        bases=bases,
        config=regla.config,
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    ))) == 1

    pkt_patron = PaqueteDecodificado(
        packet_id=204,
        from_node_id="!cli1",
        to="^all",
        portnum="nodeinfo",
        payload={"public_key": clave_patron_b64},
        rx_first=t0.isoformat(),
    )
    assert len(regla.comprobar(Contexto(
        ahora=t0,
        evento=pkt_patron,
        nodo=cli,
        estado=estado,
        bases=bases,
        config=regla.config,
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    ))) == 1

    # Caso 4: Clave válida -> sin alerta
    pkt_ok = PaqueteDecodificado(
        packet_id=205,
        from_node_id="!cli1",
        to="^all",
        portnum="nodeinfo",
        payload={"public_key": clave_valida_b64},
        rx_first=t0.isoformat(),
    )
    assert len(regla.comprobar(Contexto(
        ahora=t0,
        evento=pkt_ok,
        nodo=cli,
        estado=estado,
        bases=bases,
        config=regla.config,
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    ))) == 0

    # Caso 5: Cambio inesperado de clave en el mismo nodo
    # El nodo ya tenía una clave previa registrada
    cli.public_key = clave_valida_b64
    cli.previous_keys = [clave_valida_b64]

    otra_clave_valida = bytes([
        0x2c, 0x5a, 0x84, 0x33, 0x9b, 0x18, 0xef, 0x66, 0x44, 0x22, 0x88, 0xaa, 0xcc, 0xee, 0xbb, 0xdd,
        0x21, 0x36, 0x45, 0x89, 0xab, 0xcd, 0xef, 0x01, 0x23, 0x49, 0x58, 0x67, 0x76, 0x85, 0x94, 0xa3,
    ])
    otra_clave_valida_b64 = base64.b64encode(otra_clave_valida).decode("ascii")
    pkt_cambio = PaqueteDecodificado(
        packet_id=206,
        from_node_id="!cli1",
        to="^all",
        portnum="nodeinfo",
        payload={"public_key": otra_clave_valida_b64},
        rx_first=t0.isoformat(),
    )
    ctx_cambio = Contexto(
        ahora=t0,
        evento=pkt_cambio,
        nodo=cli,
        estado=estado,
        bases=bases,
        config=regla.config,
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )
    alts_cambio = regla.comprobar(ctx_cambio)
    assert len(alts_cambio) == 1
    assert alts_cambio[0].datos["tipo_anomalia"] == "cambio_clave"
    assert alts_cambio[0].riesgo == "medio"


def test_router_moving() -> None:
    """Verifica detección de repetidor en movimiento (> 5 km en 24h) y filtrado de roles."""
    regla = ReglaRouterMoving(ConfigRouterMoving(umbral_km=5.0, ventana_h=24, resolver_km=3.0))
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    # 1. Nodo CLIENT que se desplaza 20 km -> ignorado
    cli = estado.obtener_o_crear_nodo("!cli_movil", t0)
    cli.role = "CLIENT"
    cli.positions_24h.extend([
        (t0 - timedelta(hours=5), 36.74, -6.35),
        (t0, 36.90, -6.35),  # ~17.8 km
    ])

    pkt_cli = PaqueteDecodificado(
        packet_id=301,
        from_node_id="!cli_movil",
        to="^all",
        portnum="position",
        payload={"latitude_i": int(36.90 * 1e7), "longitude_i": int(-6.35 * 1e7)},
        rx_first=t0.isoformat(),
    )
    ctx_cli = Contexto(
        ahora=t0,
        evento=pkt_cli,
        nodo=cli,
        estado=estado,
        bases=bases,
        config=regla.config,
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )
    assert len(regla.comprobar(ctx_cli)) == 0

    # 2. ROUTER con desplazamiento leve (1.2 km, margen de jitter / fuzzing) -> sin alerta
    rtr = estado.obtener_o_crear_nodo("!rtr_fijo", t0)
    rtr.role = "ROUTER"
    rtr.positions_24h.extend([
        (t0 - timedelta(hours=4), 36.740, -6.350),
        (t0, 36.749, -6.350),  # ~1.0 km
    ])
    pkt_rtr1 = PaqueteDecodificado(
        packet_id=302,
        from_node_id="!rtr_fijo",
        to="^all",
        portnum="position",
        payload={"latitude_i": int(36.749 * 1e7), "longitude_i": int(-6.350 * 1e7)},
        rx_first=t0.isoformat(),
    )
    ctx_rtr1 = Contexto(
        ahora=t0,
        evento=pkt_rtr1,
        nodo=rtr,
        estado=estado,
        bases=bases,
        config=regla.config,
        general=ConfigGeneral(),
        es_infraestructura=lambda _: True,
    )
    assert len(regla.comprobar(ctx_rtr1)) == 0

    # 3. ROUTER con desplazamiento > 5 km (ej. 7.5 km) -> alerta alto
    rtr.positions_24h.append((t0, 36.810, -6.350))  # ~7.7 km de latitud
    alts_rtr = regla.comprobar(ctx_rtr1)
    assert len(alts_rtr) == 1
    assert alts_rtr[0].regla == "router-moving"
    assert alts_rtr[0].riesgo == "alto"
    assert alts_rtr[0].tipo == "infraestructura"
    assert alts_rtr[0].datos["desplazamiento_km"] > 5.0

    # 4. Saneamiento: si las muestras se estabilizan y la distancia máxima cae a <= 3 km
    abierta = AlertaAbierta(
        id="01TESTROUTERMOVING00000001",
        regla=regla.id,
        nodo="!rtr_fijo",
        abierta_en=t0,
        actualizada_en=t0,
        riesgo="alto",
        mensaje="Router en movimiento",
        tipo="infraestructura",
    )
    # Sigue activa mientras esté en 7.7 km
    assert regla.sigue_activa(ctx_rtr1, abierta) is True

    # Pasan 25 horas y sólo quedan muestras agrupadas en radio de 500m
    t_futuro = t0 + timedelta(hours=25)
    rtr.positions_24h.clear()
    rtr.positions_24h.extend([
        (t_futuro - timedelta(hours=2), 36.810, -6.350),
        (t_futuro, 36.812, -6.350),  # ~220 m
    ])
    ctx_futuro = Contexto(
        ahora=t_futuro,
        evento=Tick(ahora=t_futuro),
        nodo=rtr,
        estado=estado,
        bases=bases,
        config=regla.config,
        general=ConfigGeneral(),
        es_infraestructura=lambda _: True,
    )
    assert regla.sigue_activa(ctx_futuro, abierta) is False


def test_router_cluster() -> None:
    """Verifica detección de 3 o más routers vecinos y exclusión de ROUTER_LATE."""
    regla = ReglaRouterCluster(ConfigRouterCluster(umbral_routers=3, ventana_h=24, resolver_routers=2))
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    # Nodo principal: ROUTER
    r_central = estado.obtener_o_crear_nodo("!r_central", t0)
    r_central.role = "ROUTER"
    r_central.short = "CENTRAL"

    # Vecinos de infraestructura
    r1 = estado.obtener_o_crear_nodo("!r1", t0)
    r1.role = "ROUTER"
    r2 = estado.obtener_o_crear_nodo("!r2", t0)
    r2.role = "REPEATER"
    r_late = estado.obtener_o_crear_nodo("!r_late", t0)
    r_late.role = "ROUTER_LATE"
    r3 = estado.obtener_o_crear_nodo("!r3", t0)
    r3.role = "ROUTER"

    # Contexto base
    pkt = PaqueteDecodificado(
        packet_id=401,
        from_node_id="!r_central",
        to="^all",
        portnum="routing",
        payload={},
        rx_first=t0.isoformat(),
    )
    ctx = Contexto(
        ahora=t0,
        evento=pkt,
        nodo=r_central,
        estado=estado,
        bases=bases,
        config=regla.config,
        general=ConfigGeneral(),
        es_infraestructura=lambda _: True,
    )

    # 1. Enlazado con 2 routers -> no alerta
    r_central.direct_neighbors["!r1"] = (t0, 10.0)
    r_central.direct_neighbors["!r2"] = (t0, 8.0)
    assert len(regla.comprobar(ctx)) == 0

    # 2. Se suma un ROUTER_LATE -> NO debe contarse porque está diseñado para convivir en clusters
    r_central.direct_neighbors["!r_late"] = (t0, 12.0)
    assert len(regla.comprobar(ctx)) == 0

    # 3. Se suma un tercer ROUTER estándar (!r3) -> 3 routers vecinos -> ALERTA
    r_central.direct_neighbors["!r3"] = (t0, 5.0)
    alts = regla.comprobar(ctx)
    assert len(alts) == 1
    assert alts[0].regla == "router-cluster"
    assert alts[0].riesgo == "medio"
    assert alts[0].tipo == "infraestructura"
    assert alts[0].datos["routers_enlazados"] == 3
    assert set(alts[0].datos["vecinos"]) == {"!r1", "!r2", "!r3"}

    # 4. Saneamiento: cuando los routers vecinos caen a menos de resolver_routers (2)
    abierta = AlertaAbierta(
        id="01TESTROUTERCLUSTER0000001",
        regla=regla.id,
        nodo="!r_central",
        abierta_en=t0,
        actualizada_en=t0,
        riesgo="medio",
        mensaje="Clúster redundante",
        tipo="infraestructura",
    )
    assert regla.sigue_activa(ctx, abierta) is True

    # Se desconectan dos routers, queda solo 1
    del r_central.direct_neighbors["!r2"]
    del r_central.direct_neighbors["!r3"]
    assert regla.sigue_activa(ctx, abierta) is False


def test_asymmetric_link() -> None:
    """Verifica detección de enlaces asimétricos (> 6 dB) y exclusión tajante de CLIENT_MUTE."""
    regla = ReglaAsymmetricLink(ConfigAsymmetricLink(delta_snr_db=6.0, ventana_h=24, resolver_db=4.0))
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    # Dos nodos autorizados: CLIENT y ROUTER
    cli = estado.obtener_o_crear_nodo("!cli_fijo", t0)
    cli.role = "CLIENT"
    cli.short = "CLIF"

    rtr = estado.obtener_o_crear_nodo("!rtr_cima", t0)
    rtr.role = "ROUTER"
    rtr.short = "RTRC"

    # Enlaces observados:
    # cli -> rtr: rtr recibe a cli con SNR 12.0 dB
    # rtr -> cli: cli recibe a rtr con SNR 2.0 dB
    # Delta = 10.0 dB (> 6 dB). El sujeto que sufre la asimetría (oye peor) es !cli_fijo
    estado.rf_links[("!cli_fijo", "!rtr_cima")] = (t0, 12.0)
    estado.rf_links[("!rtr_cima", "!cli_fijo")] = (t0, 2.0)

    pkt_cli = PaqueteDecodificado(
        packet_id=501,
        from_node_id="!cli_fijo",
        to="^all",
        portnum="routing",
        payload={},
        rx_first=t0.isoformat(),
    )
    ctx_cli = Contexto(
        ahora=t0,
        evento=pkt_cli,
        nodo=cli,
        estado=estado,
        bases=bases,
        config=regla.config,
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )

    alts = regla.comprobar(ctx_cli)
    assert len(alts) == 1
    assert alts[0].regla == "asymmetric-link"
    assert alts[0].riesgo == "medio"
    assert alts[0].nodo == "!cli_fijo"
    assert alts[0].datos["delta_db"] == 10.0
    assert alts[0].datos["snr_peor"] == 2.0
    assert alts[0].datos["snr_mejor"] == 12.0

    # 2. Simetría aceptable (ej. delta 3 dB <= 6 dB) -> sin alerta
    estado.rf_links[("!rtr_cima", "!cli_fijo")] = (t0, 9.0)  # delta = 3 dB
    assert len(regla.comprobar(ctx_cli)) == 0

    # 3. Exclusión de CLIENT_MUTE:
    # Si cualquiera de los nodos es CLIENT_MUTE, NUNCA se debe alertar
    mute = estado.obtener_o_crear_nodo("!mute_casa", t0)
    mute.role = "CLIENT_MUTE"
    mute.short = "MUTE"

    estado.rf_links[("!mute_casa", "!rtr_cima")] = (t0, 15.0)
    estado.rf_links[("!rtr_cima", "!mute_casa")] = (t0, 1.0)  # delta 14 dB

    pkt_mute = PaqueteDecodificado(
        packet_id=502,
        from_node_id="!mute_casa",
        to="^all",
        portnum="routing",
        payload={},
        rx_first=t0.isoformat(),
    )
    ctx_mute = Contexto(
        ahora=t0,
        evento=pkt_mute,
        nodo=mute,
        estado=estado,
        bases=bases,
        config=regla.config,
        general=ConfigGeneral(),
        es_infraestructura=lambda _: False,
    )
    assert len(regla.comprobar(ctx_mute)) == 0

    # 4. Saneamiento: cuando delta SNR cae a <= 4.0 dB
    abierta = AlertaAbierta(
        id="01TESTASYMMETRIC0000000001",
        regla=regla.id,
        nodo="!cli_fijo",
        abierta_en=t0,
        actualizada_en=t0,
        riesgo="medio",
        mensaje="Enlace asimétrico",
        datos={"otro_nodo": "!rtr_cima"},
        tipo="infraestructura",
    )
    # Delta 10 dB -> sigue activa
    estado.rf_links[("!cli_fijo", "!rtr_cima")] = (t0, 12.0)
    estado.rf_links[("!rtr_cima", "!cli_fijo")] = (t0, 2.0)
    assert regla.sigue_activa(ctx_cli, abierta) is True

    # Delta mejora a 3 dB -> se resuelve (sigue_activa False)
    estado.rf_links[("!rtr_cima", "!cli_fijo")] = (t0, 9.0)
    assert regla.sigue_activa(ctx_cli, abierta) is False
