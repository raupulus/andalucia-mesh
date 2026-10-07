"""Pruebas unitarias de EstadoMotor y cálculo de LíneasBase."""

from datetime import UTC, datetime, timedelta

import pytest

from detector.modelos import FromNodeInfo, PaqueteDecodificado
from detector.motor.bases import LineasBase
from detector.motor.estado import EstadoMotor


def test_estado_nodo_registro_e_intervalos() -> None:
    """Verifica que el estado del nodo registra paquetes y calcula intervalos consecutivos."""
    estado = EstadoMotor()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    # Paquete 1 a las 12:00
    pkt1 = PaqueteDecodificado(
        packet_id=1,
        from_node_id="!a1b2c3d4",
        from_node=FromNodeInfo(short="CAD1", role="ROUTER", province="ES-CA"),
        portnum="telemetry",
        rx_first=t0,
        payload={"device_metrics": {"uptime_seconds": 3600, "battery_level": 80, "voltage": 4.1}},
    )
    estado.registrar_paquete(pkt1, t0)

    nodo = estado.nodes["!a1b2c3d4"]
    assert nodo.short == "CAD1"
    assert nodo.role == "ROUTER"
    assert nodo.last_uptime_seconds == 3600
    assert len(nodo.intervals) == 0

    # Paquete 2 a las 12:05 (300 s después)
    t1 = t0 + timedelta(seconds=300)
    pkt2 = PaqueteDecodificado(
        packet_id=2,
        from_node_id="!a1b2c3d4",
        portnum="telemetry",
        rx_first=t1,
        payload={"device_metrics": {"uptime_seconds": 3900}},
    )
    estado.registrar_paquete(pkt2, t1)

    assert len(nodo.intervals) == 1
    assert nodo.intervals[0] == 300.0


def test_estado_nodo_deteccion_reinicio() -> None:
    """Verifica la detección de caída de uptime como reinicio."""
    estado = EstadoMotor()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    # Uptime normal de 7200s
    pkt1 = PaqueteDecodificado(
        packet_id=1,
        from_node_id="!a1b2c3d4",
        portnum="telemetry",
        rx_first=t0,
        payload={"device_metrics": {"uptime_seconds": 7200}},
    )
    estado.registrar_paquete(pkt1, t0)
    nodo = estado.nodes["!a1b2c3d4"]
    assert len(nodo.reboots_24h) == 0

    # Caída abrupta a 60s
    t1 = t0 + timedelta(seconds=120)
    pkt2 = PaqueteDecodificado(
        packet_id=2,
        from_node_id="!a1b2c3d4",
        portnum="telemetry",
        rx_first=t1,
        payload={"device_metrics": {"uptime_seconds": 60}},
    )
    estado.registrar_paquete(pkt2, t1)
    assert len(nodo.reboots_24h) == 1
    assert nodo.reboots_24h[0] == t1


def test_lineas_base_historia_minima() -> None:
    """Verifica que sin 24 horas de historia el cálculo de intervalo o ritmo retorna None."""
    bases = LineasBase(historia_minima_h=24)
    estado = EstadoMotor()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    nodo = estado.obtener_o_crear_nodo("!a1b2c3d4", t0)
    nodo.intervals.extend([60.0, 60.0, 60.0, 60.0])

    # Con solo 1 hora transcurrida
    t1 = t0 + timedelta(hours=1)
    assert bases.intervalo_tipico_nodo(nodo, t1) is None

    # Tras 25 horas
    t2 = t0 + timedelta(hours=25)
    assert bases.intervalo_tipico_nodo(nodo, t2) == 60.0


def test_saturacion_provincial_dt13() -> None:
    """Verifica el cálculo de saturación ponderada provincial excluyendo CLIENT_MUTE (DT-13)."""
    bases = LineasBase()
    estado = EstadoMotor()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    # 1 router en Cádiz al 40%
    r1 = estado.obtener_o_crear_nodo("!router1", t0)
    r1.province = "ES-CA"
    r1.role = "ROUTER"
    r1.last_channel_utilization = 40.0
    r1.last_chutil_at = t0

    # 1 cliente en Cádiz al 20%
    c1 = estado.obtener_o_crear_nodo("!cliente1", t0)
    c1.province = "ES-CA"
    c1.role = "CLIENT"
    c1.last_channel_utilization = 20.0
    c1.last_chutil_at = t0

    # 1 cliente mute al 90% (debe ser ignorado según DT-13)
    m1 = estado.obtener_o_crear_nodo("!mute1", t0)
    m1.province = "ES-CA"
    m1.role = "CLIENT_MUTE"
    m1.last_channel_utilization = 90.0
    m1.last_chutil_at = t0

    # Cálculo esperado: 0.6 * 40 + 0.4 * 20 = 24 + 8 = 32.0
    sat = bases.saturacion_provincial(estado, "ES-CA", ahora=t0)
    assert pytest.approx(sat, 0.01) == 32.0
