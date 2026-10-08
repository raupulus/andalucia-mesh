"""Prueba unitaria del cálculo de HMAC y correspondencia con el vector de prueba canónico."""

from datetime import UTC, datetime
from typing import Any

from webhooks.cuerpo import calcular_firma_hmac, construir_cuerpo_webhook


def test_vector_prueba_canonico() -> None:
    """Verifica que el cuerpo y la firma coinciden exactamente con el vector de prueba del contrato."""
    secreto_vector = "secreto-de-prueba-no-usar"
    transicion_id = "01JABCF2222222222222222222"
    fecha = datetime(2026, 10, 3, 12, 0, 0, tzinfo=UTC)

    cuerpo_str, cuerpo_bytes = construir_cuerpo_webhook(
        transicion_id=transicion_id,
        transicion_es="ping",
        first_delivery=False,
        alerta_dict=None,
        dominio_proyecto="mesh.desdechipiona.es",
        fecha_creacion=fecha,
    )

    cuerpo_esperado = (
        '{"v":1,"transition_id":"01JABCF2222222222222222222","transition":"ping",'
        '"first_delivery":false,"created_at":"2026-10-03T12:00:00Z","alert":null}'
    )
    firma_esperada = "5bcfe873e8b8936754a06e0a94ba5e87545def23bd484de6df8f9bc3c04ee0e5"

    assert cuerpo_str == cuerpo_esperado
    assert len(cuerpo_bytes) == 144

    firma_calculada = calcular_firma_hmac(secreto_vector, cuerpo_bytes)
    assert firma_calculada == firma_esperada


def test_cuerpo_alerta_completa() -> None:
    """Verifica la serialización de una alerta completa con claves en inglés."""
    alerta: dict[str, Any] = {
        "id": "01JABCDXYZ7Q8R9S0T1V2W3X4Y",
        "regla": "reboot-loop",
        "riesgo": "alto",
        "tipo": "infraestructura",
        "mensaje": "CAD1 se ha reiniciado 7 veces en la última hora",
        "nodo": "!a1b2c3d4",
        "nodos": [],
        "nodo_info": {"corto": "CAD1", "largo": "Repetidor Sierra Cádiz", "rol": "ROUTER", "provincia": "ES-CA"},
        "datos": {"reinicios": 7, "ventana_min": 60},
        "abierta_en": "2026-10-01T15:00:00Z",
        "actualizada_en": "2026-10-01T15:00:00Z",
        "resuelta_en": None,
    }

    cuerpo_str, _ = construir_cuerpo_webhook(
        transicion_id="01JABCF1111111111111111111",
        transicion_es="abierta",
        first_delivery=True,
        alerta_dict=alerta,
        dominio_proyecto="mesh.desdechipiona.es",
        fecha_creacion=datetime(2026, 10, 1, 15, 0, 2, tzinfo=UTC),
    )

    assert '"transition":"opened"' in cuerpo_str
    assert '"first_delivery":true' in cuerpo_str
    assert '"state":"open"' in cuerpo_str
    assert '"url":"https://mesh.desdechipiona.es/alertas/01JABCDXYZ7Q8R9S0T1V2W3X4Y"' in cuerpo_str
