"""Regla telemetry-burst: detección de emisiones repetidas y ráfagas combinadas de telemetría."""

from datetime import timedelta
from typing import ClassVar, Literal

from pydantic import BaseModel

from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar


class ConfigTelemetryBurst(BaseModel):
    """Configuración de umbrales para la regla telemetry-burst."""

    activa: bool = True
    ventana_1m_s: int = 60
    umbral_1m_bajo: int = 2
    ventana_1h_s: int = 3600
    umbral_1h_alto: int = 50
    resolver_min: int = 15


@registrar
class ReglaTelemetryBurst:
    """Detecta abusos de telemetría y distingue ráfagas combinadas de emisiones constantes aceleradas."""

    id: ClassVar[str] = "telemetry-burst"
    nombre: ClassVar[str] = "Ráfaga de telemetría"
    descripcion: ClassVar[str] = "Emisiones repetidas de telemetría o nodeinfo (≥ 2 en 1 min bajo, > 50/h alto)"
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "mvp"
    afecta_malla: ClassVar[bool] = False
    suscrita_a: ClassVar[frozenset[str]] = frozenset({"telemetry", "nodeinfo", "position"})
    Config: ClassVar[type[BaseModel]] = ConfigTelemetryBurst

    def __init__(self, config: ConfigTelemetryBurst | None = None) -> None:
        """Inicializa la regla."""
        self.config = config or ConfigTelemetryBurst()

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa si el nodo emisor emite telemetría a una cadencia perjudicial para la malla."""
        if ctx.nodo is None:
            return []

        limite_1m = ctx.ahora - timedelta(seconds=self.config.ventana_1m_s)
        limite_1h = ctx.ahora - timedelta(seconds=self.config.ventana_1h_s)

        emisiones_1m = [(t, v) for t, v in ctx.nodo.telemetry_emissions_1h if t >= limite_1m]
        emisiones_1h = [(t, v) for t, v in ctx.nodo.telemetry_emissions_1h if t >= limite_1h]

        cuenta_1m = len(emisiones_1m)
        cuenta_1h = len(emisiones_1h)

        riesgo: str | None = None
        if cuenta_1h > self.config.umbral_1h_alto:
            riesgo = "alto"
        elif cuenta_1m >= self.config.umbral_1m_bajo:
            riesgo = "bajo"

        if riesgo is not None:
            # Distinguir si es una ráfaga combinada de múltiples variantes o una constante acelerada
            variantes_1m = {v for _, v in emisiones_1m}
            if len(variantes_1m) >= 2:
                tipo_emision = "ráfaga_combinada"
            else:
                tipo_emision = "constante_acelerada"

            variantes_total = sorted(list({v for _, v in emisiones_1h[-10:]} or variantes_1m))

            nombre_corto = ctx.nodo.short or ctx.nodo.node_id
            if riesgo == "alto":
                msg = f"{nombre_corto} supera 50 emisiones de telemetría por hora ({cuenta_1h}/h)"
            else:
                msg = f"{nombre_corto} emite telemetría repetida ({cuenta_1m} en 1 min, {tipo_emision})"

            return [
                Alerta(
                    regla=self.id,
                    riesgo=riesgo,
                    mensaje=msg,
                    nodo=ctx.nodo.node_id,
                    datos={
                        "emisiones_1m": cuenta_1m,
                        "emisiones_1h": cuenta_1h,
                        "tipo_emision": tipo_emision,
                        "variantes": variantes_total,
                        "provincia": ctx.nodo.province or "FUERA",
                        "dentro_andalucia": ctx.nodo.dentro_andalucia,
                    },
                )
            ]
        return []

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """Determina si la alerta sigue activa o si el ritmo se ha normalizado."""
        nodo = ctx.estado.nodes.get(abierta.nodo)
        if nodo is None:
            return True

        limite = ctx.ahora - timedelta(minutes=self.config.resolver_min)
        recientes = [t for t, _ in nodo.telemetry_emissions_1h if t >= limite]
        return len(recientes) > 2
