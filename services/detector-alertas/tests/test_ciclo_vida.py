"""Pruebas unitarias del gestor de ciclo de vida de alertas."""

from datetime import UTC, datetime, timedelta

from detector.motor.ciclo import GestorCicloVida
from detector.motor.clasificador import Clasificador
from detector.motor.protocolos import Alerta, ConfigGeneral
from detector.motor.silencios import GestorSilencios


def test_apertura_nueva_alerta() -> None:
    """Verifica que una alerta nueva genera una transición 'abierta' con ULID."""
    ciclo = GestorCicloVida()
    clasificador = Clasificador(
        riesgos=[{"id": "bajo"}, {"id": "medio"}, {"id": "alto"}],
        tipos=[{"id": "infraestructura"}, {"id": "clientes"}],
    )
    silencios = GestorSilencios()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    alerta = Alerta(
        regla="battery-low",
        riesgo="medio",
        mensaje="CAD1 tiene batería al 35%",
        nodo="!a1b2c3d4",
        datos={"bateria": 35},
    )

    t = ciclo.procesar_alerta_regla(
        alerta=alerta,
        ahora=t0,
        clasificador=clasificador,
        silencios=silencios,
        rol="ROUTER",
    )

    assert t is not None
    assert t.transicion == "abierta"
    assert t.alerta.riesgo == "medio"
    assert t.alerta.tipo == "infraestructura"
    assert t.alerta.estado == "abierta"
    assert len(t.alerta.id) == 26  # Formato ULID


def test_escalada_inmediata_de_riesgo() -> None:
    """Verifica que si el riesgo sube de medio a alto se emite 'actualizada' de inmediato."""
    ciclo = GestorCicloVida()
    clasificador = Clasificador(
        riesgos=[{"id": "bajo"}, {"id": "medio"}, {"id": "alto"}],
        tipos=[{"id": "infraestructura"}, {"id": "clientes"}],
    )
    silencios = GestorSilencios()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    # 1. Apertura en medio
    a1 = Alerta(regla="battery-low", riesgo="medio", mensaje="Batería 35%", nodo="!a1b2c3d4")
    ciclo.procesar_alerta_regla(a1, t0, clasificador, silencios, rol="ROUTER")

    # 2. Emisión a alto a los 2 minutos
    t1 = t0 + timedelta(minutes=2)
    a2 = Alerta(regla="battery-low", riesgo="alto", mensaje="Batería 15%", nodo="!a1b2c3d4")
    t = ciclo.procesar_alerta_regla(a2, t1, clasificador, silencios, rol="ROUTER")

    assert t is not None
    assert t.transicion == "actualizada"
    assert t.alerta.riesgo == "alto"


def test_desescalada_con_limite_de_15_minutos() -> None:
    """Verifica que una bajada de riesgo respeta el intervalo de 15 min."""
    ciclo = GestorCicloVida(ConfigGeneral(actualizacion_min=15))
    clasificador = Clasificador(
        riesgos=[{"id": "bajo"}, {"id": "medio"}, {"id": "alto"}],
        tipos=[{"id": "infraestructura"}, {"id": "clientes"}],
    )
    silencios = GestorSilencios()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    # 1. Apertura en alto
    a1 = Alerta(regla="flood", riesgo="alto", mensaje="Inundación 150 paq", nodo="!a1b2c3d4")
    ciclo.procesar_alerta_regla(a1, t0, clasificador, silencios, afecta_malla=True)

    # 2. Baja a medio a los 5 minutos (< 15 min) -> No emite transición por socket
    t1 = t0 + timedelta(minutes=5)
    a2 = Alerta(regla="flood", riesgo="medio", mensaje="Inundación 40 paq", nodo="!a1b2c3d4")
    t_bloqueada = ciclo.procesar_alerta_regla(a2, t1, clasificador, silencios, afecta_malla=True)
    assert t_bloqueada is None

    # 3. Baja a medio a los 16 minutos (>= 15 min) -> Emite 'actualizada'
    t2 = t0 + timedelta(minutes=16)
    t_emitida = ciclo.procesar_alerta_regla(a2, t2, clasificador, silencios, afecta_malla=True)
    assert t_emitida is not None
    assert t_emitida.transicion == "actualizada"
    assert t_emitida.alerta.riesgo == "medio"


def test_resolucion_y_reapertura_conservando_id() -> None:
    """Verifica la resolución con histéresis y la reapertura dentro de 60 min conservando el mismo ID."""
    ciclo = GestorCicloVida(ConfigGeneral(reapertura_min=60))
    clasificador = Clasificador(
        riesgos=[{"id": "bajo"}, {"id": "medio"}, {"id": "alto"}],
        tipos=[{"id": "infraestructura"}, {"id": "clientes"}],
    )
    silencios = GestorSilencios()
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    # 1. Abrir alerta
    a1 = Alerta(regla="reboot-loop", riesgo="alto", mensaje="3 reinicios", nodo="!cad1")
    t_open = ciclo.procesar_alerta_regla(a1, t0, clasificador, silencios, rol="ROUTER")
    assert t_open is not None
    original_id = t_open.alerta.id

    # 2. Resolver a las 12:30
    t_res = t0 + timedelta(minutes=30)
    t_resolved = ciclo.resolver_alerta("reboot-loop:!cad1", motivo_cierre="condicion", ahora=t_res)
    assert t_resolved is not None
    assert t_resolved.transicion == "resuelta"
    assert t_resolved.alerta.datos["cierre"] == "condicion"
    assert t_resolved.alerta.id == original_id

    # 3. Reabrir a las 12:45 (15 min después de resolverse, < 60 min)
    t_reopen = t_res + timedelta(minutes=15)
    t_reopened = ciclo.procesar_alerta_regla(a1, t_reopen, clasificador, silencios, rol="ROUTER")
    assert t_reopened is not None
    assert t_reopened.transicion == "abierta"
    assert t_reopened.alerta.id == original_id  # Mismo ID conservado
    assert ciclo.alertas_abiertas["reboot-loop:!cad1"].reaperturas == 1


def test_silencios_evitan_apertura() -> None:
    """Verifica que un nodo silenciado no genera alertas abiertas."""
    ciclo = GestorCicloVida()
    clasificador = Clasificador(
        riesgos=[{"id": "bajo"}, {"id": "medio"}, {"id": "alto"}],
        tipos=[{"id": "infraestructura"}, {"id": "clientes"}],
    )
    t0 = datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC)

    silencios = GestorSilencios([
        {
            "nodo": "!silenciado",
            "regla": None,
            "hasta": "2026-10-01T15:00:00Z",
            "motivo": "Mantenimiento",
        }
    ])

    alerta = Alerta(regla="battery-low", riesgo="alto", mensaje="Batería 5%", nodo="!silenciado")
    t = ciclo.procesar_alerta_regla(alerta, t0, clasificador, silencios)

    assert t is None
    assert len(ciclo.alertas_abiertas) == 0
