"""Pruebas de persistencia, contingencia, snapshots y contratos SQL."""

from datetime import UTC, datetime
from pathlib import Path

from detector.config import Settings
from detector.modelos import AlertaObjeto
from detector.motor.estado import EstadoMotor
from detector.motor.instantaneas import GestorInstantaneas
from detector.motor.protocolos import TransicionAlerta
from detector.persistencia import GestorPersistencia


def test_migraciones_sql_vistas_contrato() -> None:
    """Verifica que los archivos SQL de migración y vistas existan y contengan las definiciones canónicas."""
    base_dir = Path(__file__).resolve().parent.parent
    f_tablas = base_dir / "migrations" / "001_tablas.sql"
    f_vistas = base_dir / "migrations" / "002_vistas.sql"

    assert f_tablas.is_file(), "El archivo 001_tablas.sql debe existir"
    assert f_vistas.is_file(), "El archivo 002_vistas.sql debe existir"

    txt_tablas = f_tablas.read_text(encoding="utf-8")
    txt_vistas = f_vistas.read_text(encoding="utf-8")

    # Tablas indispensables
    assert "CREATE TABLE IF NOT EXISTS alerta" in txt_tablas
    assert "CREATE TABLE IF NOT EXISTS transicion" in txt_tablas
    assert "CREATE TABLE IF NOT EXISTS estado_snapshot" in txt_tablas
    assert "CREATE TABLE IF NOT EXISTS catalogo" in txt_tablas

    # Vistas de contrato para el Portal
    assert "CREATE OR REPLACE VIEW api_alertas" in txt_vistas
    assert "CREATE OR REPLACE VIEW api_alertas_transiciones" in txt_vistas
    assert "CREATE OR REPLACE VIEW api_alertas_resumen" in txt_vistas
    assert "CREATE OR REPLACE VIEW api_nodos_en_riesgo" in txt_vistas
    assert "CREATE OR REPLACE VIEW api_catalogo" in txt_vistas

    # Compatibilidad dual inicio_at y abierta_en
    assert "a.abierta_en AS inicio_at" in txt_vistas
    assert "a.abierta_en" in txt_vistas

    # Permisos para el usuario de solo lectura del portal
    assert "GRANT SELECT ON api_alertas" in txt_vistas
    assert "TO portal_lector_alertas" in txt_vistas


def test_instantaneas_serializacion_deserializacion() -> None:
    """Verifica que un estado completo con nodos y métricas se serialice y reconstruya fielmente."""
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)
    estado_original = EstadoMotor(started_at=t0)

    n1 = estado_original.obtener_o_crear_nodo("!a1b2c3d4", t0)
    n1.short = "CAD1"
    n1.long = "Repetidor Sierra Cádiz"
    n1.role = "ROUTER"
    n1.province = "ES-CA"
    n1.battery_samples.append((t0, 75, 4.02))
    n1.hop_starts.append(3)
    n1.reboots_24h.append(t0)

    gw1 = estado_original.obtener_o_crear_gateway("!gw010203", t0)
    gw1.reception_intervals.append(30.5)

    estado_original.known_ids_90d["!a1b2c3d4"] = t0

    # Serializar con gzip
    blob = GestorInstantaneas.serializar(estado_original)
    assert isinstance(blob, bytes)
    assert len(blob) > 0

    # Deserializar
    estado_restaurado = GestorInstantaneas.deserializar(blob)
    assert "!a1b2c3d4" in estado_restaurado.nodes
    nodo_rest = estado_restaurado.nodes["!a1b2c3d4"]
    assert nodo_rest.short == "CAD1"
    assert nodo_rest.province == "ES-CA"
    assert len(nodo_rest.battery_samples) == 1
    assert nodo_rest.battery_samples[0][1] == 75
    assert len(nodo_rest.reboots_24h) == 1

    assert "!gw010203" in estado_restaurado.gateways
    assert "!a1b2c3d4" in estado_restaurado.known_ids_90d


def test_persistencia_cola_contingencia() -> None:
    """Verifica que sin conexión a base de datos las transiciones se encolen en memoria."""
    settings = Settings(db_host="127.0.0.99", db_port=9999)
    persistencia = GestorPersistencia(settings)
    assert not persistencia.conectado
    assert len(persistencia.cola_contingencia) == 0

    t = TransicionAlerta(
        transicion_id="01JAC0Q4M1K2J3H4G5F6E7D801",
        alerta_id="01JAC0Q4M1K2J3H4G5F6E7D800",
        transicion="abierta",
        en=datetime.now(UTC),
        alerta=AlertaObjeto(
            id="01JAC0Q4M1K2J3H4G5F6E7D800",
            regla="reboot-loop",
            riesgo="alto",
            tipo="infraestructura",
            mensaje="Nodo reiniciado",
            nodo="!a1b2c3d4",
            estado="abierta",
            abierta_en="2026-10-01T12:00:00Z",
            actualizada_en="2026-10-01T12:00:00Z",
        ),
    )

    import asyncio
    exito = asyncio.run(persistencia.guardar_transicion(t))
    assert not exito
    assert len(persistencia.cola_contingencia) == 1
    assert persistencia.cola_contingencia[0].transicion_id == "01JAC0Q4M1K2J3H4G5F6E7D801"


async def test_restaurar_ciclo_desde_db() -> None:
    """Verifica que las alertas abiertas y resueltas de la BD se carguen en el ciclo de vida."""
    from unittest.mock import AsyncMock
    from detector.motor.ciclo import GestorCicloVida

    ciclo = GestorCicloVida()
    mock_conn = AsyncMock()

    t_ahora = datetime.now(UTC)
    filas_abiertas = [
        {
            "id": "01JAC0Q4M1K2J3H4G5F6E7D800",
            "regla": "hops-high",
            "nodo": "!2b39ef06",
            "riesgo": "alto",
            "tipo": "infraestructura",
            "mensaje": "herc usa 7 saltos",
            "nodos": [],
            "nodo_info": {"corto": "herc", "largo": "Hércules", "rol": "ROUTER", "provincia": "Cádiz"},
            "datos": {"hop_start": 7},
            "estado": "abierta",
            "abierta_en": t_ahora,
            "actualizada_en": t_ahora,
            "resuelta_en": None,
            "reaperturas": 0,
            "evidencia_en": t_ahora,
        }
    ]
    filas_resueltas = []

    mock_conn.fetch.side_effect = [filas_abiertas, filas_resueltas]

    await GestorInstantaneas.restaurar_ciclo_desde_db(mock_conn, ciclo)

    assert "hops-high:!2b39ef06" in ciclo.alertas_abiertas
    alerta_cargada = ciclo.alertas_abiertas["hops-high:!2b39ef06"]
    assert alerta_cargada.id == "01JAC0Q4M1K2J3H4G5F6E7D800"
    assert alerta_cargada.nodo == "!2b39ef06"
    assert alerta_cargada.riesgo == "alto"

