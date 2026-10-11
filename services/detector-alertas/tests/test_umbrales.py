"""Pruebas unitarias e integración de la centralización de umbrales dinámicos en RAM."""

from datetime import UTC, datetime, timedelta
from unittest.mock import AsyncMock, MagicMock

import pytest
from aiohttp.test_utils import TestClient, TestServer

from detector.config import Settings
from detector.entrada import EntradaMQTT
from detector.modelos import FromNodeInfo, PaqueteDecodificado
from detector.motor.bases import LineasBase
from detector.motor.estado import EstadoMotor
from detector.motor.motor import MotorAlertas
from detector.motor.protocolos import Contexto, Tick
from detector.motor.umbrales import GestorUmbrales
from detector.persistencia import GestorPersistencia
from detector.reglas.battery_low import ConfigBatteryLow, ReglaBatteryLow
from detector.reglas.hops_high import ConfigHopsHigh, ReglaHopsHigh
from detector.reglas.infra_silent import ConfigInfraSilent, ReglaInfraSilent
from detector.reglas.reboot_loop import ConfigRebootLoop, ReglaRebootLoop
from detector.reglas.sunset_battery import ConfigSunsetBattery, ReglaSunsetBattery
from detector.salud import ServidorSalud
from detector.socket_alertas import ServidorSocketAlertas


def test_gestor_umbrales_resolucion_grupos() -> None:
    """Verifica la asignación unívoca de roles de Meshtastic a los tres grupos de calibración."""
    gestor = GestorUmbrales()

    # Grupo 1: Troncal (router)
    assert gestor.resolver_grupo("ROUTER") == "router"
    assert gestor.resolver_grupo("router_late") == "router"
    assert gestor.resolver_grupo("REPEATER") == "router"

    # Grupo 2: Clientes fijos y estándar (client)
    assert gestor.resolver_grupo("CLIENT") == "client"
    assert gestor.resolver_grupo("client_hidden") == "client"
    assert gestor.resolver_grupo("CLIENT_BASE") == "client"

    # Grupo 3: Dispositivos personales y mudos (client_mute)
    assert gestor.resolver_grupo("CLIENT_MUTE") == "client_mute"
    assert gestor.resolver_grupo("TRACKER") == "client_mute"
    assert gestor.resolver_grupo("LOST_AND_FOUND") == "client_mute"
    assert gestor.resolver_grupo("SENSOR") == "client_mute"
    assert gestor.resolver_grupo(None) == "client_mute"
    assert gestor.resolver_grupo("") == "client_mute"


def test_gestor_umbrales_actualizar_desde_dict() -> None:
    """Verifica la sustitución atómica de umbrales en RAM a partir de un diccionario."""
    gestor = GestorUmbrales()
    version_original = gestor.version

    datos_nuevos = {
        "version": version_original + 1000,
        "umbrales": {
            "router": {
                "hop_limit_warning": 4,
                "battery_warning_pct": 55.0,
            },
            "client": {
                "hop_limit_warning": 5,
            },
        },
    }

    nueva_version = gestor.actualizar_desde_dict(datos_nuevos)
    assert nueva_version == version_original + 1000
    assert gestor.version == version_original + 1000
    assert gestor.config.router.hop_limit_warning == 4
    assert gestor.config.router.battery_warning_pct == 55.0
    # Los valores no especificados deben conservar el valor por defecto
    assert gestor.config.router.hop_limit_critical == 7
    assert gestor.config.client.hop_limit_warning == 5


@pytest.mark.asyncio
async def test_gestor_umbrales_fallback_db_resiliente() -> None:
    """Comprueba que ante fallo o ausencia de conexión DB, GestorUmbrales no eleva excepciones."""
    gestor = GestorUmbrales()

    # Pool None -> devuelve False limpiamente
    resultado_sin_pool = await gestor.verificar_y_recargar_db(None)
    assert resultado_sin_pool is False

    # Pool con excepción de conexión -> devuelve False limpiamente
    mock_pool = MagicMock()
    mock_pool.acquire.side_effect = ConnectionRefusedError("PostgreSQL caído")
    resultado_error = await gestor.verificar_y_recargar_db(mock_pool)
    assert resultado_error is False


@pytest.mark.asyncio
async def test_gestor_umbrales_recarga_desde_db_exito() -> None:
    """Verifica que GestorUmbrales sincronice correctamente los umbrales persistidos en PostgreSQL."""
    gestor = GestorUmbrales()
    assert gestor.version == 0

    mock_pool = MagicMock()
    mock_conn = AsyncMock()
    mock_pool.acquire.return_value.__aenter__.return_value = mock_conn

    db_timestamp = datetime(2026, 10, 11, 10, 0, 0, tzinfo=UTC)
    mock_conn.fetch.return_value = [
        {
            "role_group": "router",
            "parameter": "hop_limit_warning",
            "value_numeric": 4.0,
            "updated_at": db_timestamp,
        },
        {
            "role_group": "client",
            "parameter": "battery_warning_pct",
            "value_numeric": 40.0,
            "updated_at": db_timestamp,
        },
    ]

    recargado = await gestor.verificar_y_recargar_db(mock_pool)
    assert recargado is True
    assert gestor.version == int(db_timestamp.timestamp())
    assert gestor.config.router.hop_limit_warning == 4
    assert gestor.config.client.battery_warning_pct == 40.0



@pytest.mark.asyncio
async def test_endpoint_recargar_umbrales_y_health() -> None:
    """Comprueba el endpoint interno de recarga HTTP y la presencia de métricas de umbrales en /health."""
    settings = Settings(
        internal_api_secret="secreto_seguro_12345",
        health_port=0,
    )
    motor = MotorAlertas(settings=settings)
    motor.inicializar()

    mock_entrada = AsyncMock(spec=EntradaMQTT)
    mock_entrada.conectado = True
    mock_entrada.ultimo_paquete_at = None
    mock_entrada.paquetes_recibidos = 10
    mock_entrada.paquetes_descartados_dedup = 0
    mock_entrada.paquetes_descartados_saturacion = 0
    mock_entrada.ultimo_error = None
    mock_entrada.tamano_cola = 0

    mock_persistencia = AsyncMock(spec=GestorPersistencia)
    mock_persistencia.conectado = True
    mock_persistencia.cola_contingencia = []
    mock_persistencia.ultimo_error = None

    mock_socket = AsyncMock(spec=ServidorSocketAlertas)
    mock_socket.total_clientes = 2

    salud = ServidorSalud(
        settings=settings,
        entrada=mock_entrada,
        persistencia=mock_persistencia,
        socket_alertas=mock_socket,
        motor=motor,
    )

    client = TestClient(TestServer(salud.app))
    await client.start_server()

    try:
        # 1. Petición sin cabecera X-Internal-Secret -> 401
        resp_no_auth = await client.post("/internal/recargar-umbrales", json={})
        assert resp_no_auth.status == 401

        # 2. Petición con cabecera incorrecta -> 401
        resp_bad_auth = await client.post(
            "/internal/recargar-umbrales",
            headers={"X-Internal-Secret": "clave_erronea"},
            json={},
        )
        assert resp_bad_auth.status == 401

        # 3. Petición autorizada con nuevos umbrales -> 200 y recarga en RAM
        payload = {
            "version": 1728650000,
            "umbrales": {
                "router": {
                    "hop_limit_warning": 4,
                },
            },
        }
        resp_ok = await client.post(
            "/internal/recargar-umbrales",
            headers={"X-Internal-Secret": "secreto_seguro_12345"},
            json=payload,
        )
        assert resp_ok.status == 200
        body = await resp_ok.json()
        assert body["ok"] is True
        assert body["status"] == "reloaded"
        assert body["version"] == 1728650000

        # Verificar que el motor en RAM se actualizó de inmediato
        assert motor.umbrales.config.router.hop_limit_warning == 4

        # 4. Comprobar /health
        resp_health = await client.get("/health")
        assert resp_health.status == 200
        data_health = await resp_health.json()
        assert "umbrales" in data_health
        assert data_health["umbrales"]["version"] == 1728650000
    finally:
        await client.close()


def test_regla_hops_high_adaptacion_dinamica() -> None:
    """Verifica que ReglaHopsHigh reacciona dinámicamente a la modificación de umbrales en RAM."""
    regla = ReglaHopsHigh()
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    # Router que emite con 5 saltos
    nodo_rtr = estado.obtener_o_crear_nodo("!rtr_test", t0)
    nodo_rtr.role = "ROUTER"
    nodo_rtr.short = "RTRTEST"

    pkt_5 = PaqueteDecodificado(
        packet_id=1,
        from_node_id="!rtr_test",
        from_node=FromNodeInfo(short="RTRTEST", role="ROUTER"),
        portnum="telemetry",
        rx_first=t0,
        hop_start=5,
    )

    gestor = GestorUmbrales()
    # Con umbral por defecto (router hop_limit_warning=5) -> dispara bajo
    ctx = Contexto(
        ahora=t0,
        evento=pkt_5,
        nodo=nodo_rtr,
        estado=estado,
        bases=bases,
        config=ConfigHopsHigh(),
        general=MagicMock(),
        es_infraestructura=lambda _: True,
        umbrales=gestor,
    )
    alertas = regla.comprobar(ctx)
    assert len(alertas) == 1
    assert alertas[0].riesgo == "bajo"

    # Si elevamos hop_limit_warning a 6, 5 saltos ya NO generan alerta
    gestor.actualizar_desde_dict({
        "version": 1728651000,
        "umbrales": {"router": {"hop_limit_warning": 6}},
    })
    alertas_elevado = regla.comprobar(ctx)
    assert len(alertas_elevado) == 0


def test_regla_infra_silent_adaptacion_dinamica() -> None:
    """Verifica que ReglaInfraSilent respeta el umbral de silencio dinámico del grupo router."""
    regla = ReglaInfraSilent()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)
    estado = EstadoMotor(started_at=t0 - timedelta(hours=20))
    bases = LineasBase()

    # Router silenciado desde hace 7 horas
    r_nodo = estado.obtener_o_crear_nodo("!r_test", t0 - timedelta(hours=7))
    r_nodo.role = "ROUTER"
    r_nodo.last_seen = t0 - timedelta(hours=7)

    gestor = GestorUmbrales()
    ctx = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=None,
        estado=estado,
        bases=bases,
        config=ConfigInfraSilent(),
        general=MagicMock(),
        es_infraestructura=lambda _: True,
        umbrales=gestor,
    )

    # 1. Con silencio aviso de 6h (por defecto), 7h genera alerta medio
    alts = regla.comprobar(ctx)
    assert len(alts) == 1
    assert alts[0].riesgo == "medio"

    # 2. Si el operador sube el umbral de silencio a 8h, 7h ya no genera alerta
    gestor.actualizar_desde_dict({
        "version": 1728652000,
        "umbrales": {"router": {"silence_warning_h": 8.0}},
    })
    alts_subido = regla.comprobar(ctx)
    assert len(alts_subido) == 0

    # 3. Si el operador desactiva el silencio poniendo <= 0, no alerta
    gestor.actualizar_desde_dict({
        "version": 1728653000,
        "umbrales": {"router": {"silence_warning_h": 0.0, "silence_critical_h": 0.0}},
    })
    assert len(regla.comprobar(ctx)) == 0


def test_regla_sunset_battery_desactivacion_dinamica() -> None:
    """Verifica que sunset-battery puede desactivarse dinámicamente fijando sunset_battery_min_pct en 0."""
    regla = ReglaSunsetBattery()
    estado = EstadoMotor()
    bases = LineasBase()
    # 20:00 h peninsular (19:00 UTC en invierno)
    t_20h = datetime(2026, 11, 1, 19, 0, 0, tzinfo=UTC)

    r_cadiz = estado.obtener_o_crear_nodo("!cadiz_r", t_20h)
    r_cadiz.role = "ROUTER"
    r_cadiz.province = "ES-CA"
    r_cadiz.battery_samples.append((t_20h, 45, 3.7))

    gestor = GestorUmbrales()
    ctx = Contexto(
        ahora=t_20h,
        evento=Tick(ahora=t_20h),
        nodo=None,
        estado=estado,
        bases=bases,
        config=ConfigSunsetBattery(),
        general=MagicMock(),
        es_infraestructura=lambda _: True,
        umbrales=gestor,
    )

    # Con valor por defecto (60%), 45% genera alerta
    alts = regla.comprobar(ctx)
    assert len(alts) == 1

    # Desactivar mediante umbral 0.0
    gestor.actualizar_desde_dict({
        "version": 1728654000,
        "umbrales": {"router": {"sunset_battery_min_pct": 0.0}},
    })
    assert len(regla.comprobar(ctx)) == 0


def test_regla_battery_low_adaptacion_dinamica() -> None:
    """Verifica que ReglaBatteryLow aplica los umbrales dinámicos calibrados en RAM."""
    regla = ReglaBatteryLow()
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    rtr = estado.obtener_o_crear_nodo("!rtr_bat", t0)
    rtr.role = "ROUTER"
    # Router con batería al 55%
    rtr.battery_samples.extend([(t0 - timedelta(minutes=5), 56, 3.8), (t0, 55, 3.75)])

    gestor = GestorUmbrales()
    ctx = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=rtr,
        estado=estado,
        bases=bases,
        config=ConfigBatteryLow(),
        general=MagicMock(),
        es_infraestructura=lambda _: True,
        umbrales=gestor,
    )

    # 1. Con umbral por defecto (router warning=60%), 55% genera alerta medio
    alts = regla.comprobar(ctx)
    assert len(alts) == 1
    assert alts[0].riesgo == "medio"

    # 2. Si el operador baja el umbral de warning a 50%, 55% ya no genera alerta
    gestor.actualizar_desde_dict({
        "version": 1728655000,
        "umbrales": {"router": {"battery_warning_pct": 50.0}},
    })
    alts_mod = regla.comprobar(ctx)
    assert len(alts_mod) == 0


def test_regla_reboot_loop_adaptacion_dinamica() -> None:
    """Verifica que ReglaRebootLoop aplica los límites dinámicos de reinicios por grupo."""
    regla = ReglaRebootLoop()
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    nodo = estado.obtener_o_crear_nodo("!cli_mute_test", t0)
    nodo.role = "CLIENT_MUTE"
    # 3 reinicios en 4 minutos
    nodo.reboots_24h.extend([
        t0 - timedelta(minutes=4),
        t0 - timedelta(minutes=2),
        t0,
    ])

    gestor = GestorUmbrales()
    ctx = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=nodo,
        estado=estado,
        bases=bases,
        config=ConfigRebootLoop(),
        general=MagicMock(),
        es_infraestructura=lambda _: False,
        umbrales=gestor,
    )

    # Para client_mute el umbral medio por defecto es 4 (en clientes estándar es 3)
    # Con 3 reinicios, client_mute NO debe alertar
    alts = regla.comprobar(ctx)
    assert len(alts) == 0

    # Si se añade un 4º reinicio, alcanza el umbral de client_mute (4) -> alerta medio
    nodo.reboots_24h.append(t0 - timedelta(minutes=1))
    alts_4 = regla.comprobar(ctx)
    assert len(alts_4) == 1
    assert alts_4[0].riesgo == "medio"


def test_regla_battery_low_client_mute() -> None:
    """Verifica que ReglaBatteryLow evalúa correctamente los dispositivos mudos (client_mute)."""
    regla = ReglaBatteryLow()
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    mute_node = estado.obtener_o_crear_nodo("!cli_mute_bat", t0)
    mute_node.role = "CLIENT_MUTE"
    # Dos muestras de batería al 20% (el umbral warning por defecto de client_mute es 25%, critical es 10%)
    mute_node.battery_samples.extend([
        (t0 - timedelta(minutes=5), 20, 3.6),
        (t0, 20, 3.6),
    ])

    gestor = GestorUmbrales()
    ctx = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=mute_node,
        estado=estado,
        bases=bases,
        config=ConfigBatteryLow(),
        general=MagicMock(),
        es_infraestructura=lambda _: False,
        umbrales=gestor,
    )

    # Con umbrales por defecto (warning=25%, critical=10%), 20% genera alerta con riesgo bajo
    alts = regla.comprobar(ctx)
    assert len(alts) == 1
    assert alts[0].riesgo == "bajo"

    # Si la batería cae al 8% (menor que critical 10%), genera riesgo medio
    mute_node.battery_samples.clear()
    mute_node.battery_samples.extend([
        (t0 - timedelta(minutes=2), 8, 3.4),
        (t0, 8, 3.4),
    ])
    alts_crit = regla.comprobar(ctx)
    assert len(alts_crit) == 1
    assert alts_crit[0].riesgo == "medio"

    # Si el operador baja el warning a 15%, 20% no genera alerta
    mute_node.battery_samples.clear()
    mute_node.battery_samples.extend([
        (t0 - timedelta(minutes=2), 20, 3.6),
        (t0, 20, 3.6),
    ])
    gestor.actualizar_desde_dict({
        "version": 1728656000,
        "umbrales": {"client_mute": {"battery_warning_pct": 15.0}},
    })
    alts_reducido = regla.comprobar(ctx)
    assert len(alts_reducido) == 0


def test_regla_es_infraestructura_fallback_unpopulated_roles() -> None:
    """Verifica que un nodo sin rol en paquete pero reconocido como infraestructura se enruta a 'router'."""
    gestor = GestorUmbrales()
    estado = EstadoMotor()
    bases = LineasBase()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    # 1. HopsHigh con nodo de rol None pero es_infraestructura=True
    # Router warning es 5.0 (client es 6.0). Con hop_start=5, router alerta pero client no.
    regla_hops = ReglaHopsHigh()
    nodo_infra = estado.obtener_o_crear_nodo("!infra_norole", t0)
    nodo_infra.role = None
    nodo_infra.short = "NOROLE"

    paquete = PaqueteDecodificado(
        packet_id=10,
        from_node_id="!infra_norole",
        from_node=FromNodeInfo(short="NOROLE", role=None),
        portnum="telemetry",
        rx_first=t0,
        hop_start=5,
    )

    ctx_hops = Contexto(
        ahora=t0,
        evento=paquete,
        nodo=nodo_infra,
        estado=estado,
        bases=bases,
        config=ConfigHopsHigh(),
        general=MagicMock(),
        es_infraestructura=lambda nid: nid == "!infra_norole",
        umbrales=gestor,
    )

    alts_hops = regla_hops.comprobar(ctx_hops)
    # Debe evaluar con umbral de router (warning=5), alertando con riesgo bajo
    assert len(alts_hops) == 1
    assert alts_hops[0].regla == "hops-high"

    # 2. InfraSilent con nodo de rol None pero es_infraestructura=True
    regla_silent = ReglaInfraSilent()
    estado_silent = EstadoMotor(started_at=t0 - timedelta(hours=20))
    nodo_silent = estado_silent.obtener_o_crear_nodo("!infra_silent_norole", t0 - timedelta(hours=7))
    nodo_silent.role = None
    nodo_silent.last_seen = t0 - timedelta(hours=7)

    ctx_silent = Contexto(
        ahora=t0,
        evento=Tick(ahora=t0),
        nodo=None,
        estado=estado_silent,
        bases=bases,
        config=ConfigInfraSilent(),
        general=MagicMock(),
        es_infraestructura=lambda nid: nid == "!infra_silent_norole",
        umbrales=gestor,
    )

    alts_silent = regla_silent.comprobar(ctx_silent)
    assert len(alts_silent) == 1
    assert alts_silent[0].nodo == "!infra_silent_norole"
    assert alts_silent[0].riesgo == "medio"


