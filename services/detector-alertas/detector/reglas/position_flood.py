"""Regla position-flood: detección de balizas de posición GPS hiperactivas."""

from datetime import timedelta
from typing import ClassVar, Literal

from pydantic import BaseModel

from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar


class ConfigPositionFlood(BaseModel):
    """Configuración de umbrales para la regla position-flood."""

    activa: bool = True
    ventana_5m_min: int = 5
    umbral_5m_bajo: int = 4
    ventana_10m_min: int = 10
    umbral_10m_medio: int = 8
    ventana_15m_min: int = 15
    umbral_15m_alto: int = 20
    resolver_min: int = 30


@registrar
class ReglaPositionFlood:
    """Supervisa el intervalo excesivo de envío de balizas de posición GPS."""

    id: ClassVar[str] = "position-flood"
    nombre: ClassVar[str] = "Posicionamiento hiperactivo"
    descripcion: ClassVar[str] = "Cadencia excesiva de balizas de posición (≥ 4 en 5m bajo, ≥ 8 en 10m medio, ≥ 20 en 15m alto)"
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "mvp"
    afecta_malla: ClassVar[bool] = False
    suscrita_a: ClassVar[frozenset[str]] = frozenset({"position"})
    Config: ClassVar[type[BaseModel]] = ConfigPositionFlood

    def __init__(self, config: ConfigPositionFlood | None = None) -> None:
        """Inicializa la regla."""
        self.config = config or ConfigPositionFlood()

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa si el nodo emite posiciones con una cadencia demasiado agresiva."""
        if ctx.nodo is None:
            return []

        ahora = ctx.ahora
        limite_15m = ahora - timedelta(minutes=self.config.ventana_15m_min)
        limite_10m = ahora - timedelta(minutes=self.config.ventana_10m_min)
        limite_5m = ahora - timedelta(minutes=self.config.ventana_5m_min)

        pos_15m = [t for t in ctx.nodo.position_timestamps_1h if t >= limite_15m]
        pos_10m = [t for t in ctx.nodo.position_timestamps_1h if t >= limite_10m]
        pos_5m = [t for t in ctx.nodo.position_timestamps_1h if t >= limite_5m]

        c15 = len(pos_15m)
        c10 = len(pos_10m)
        c5 = len(pos_5m)

        riesgo: str | None = None
        if c15 >= self.config.umbral_15m_alto:
            riesgo = "alto"
        elif c10 >= self.config.umbral_10m_medio:
            riesgo = "medio"
        elif c5 >= self.config.umbral_5m_bajo:
            riesgo = "bajo"

        if riesgo is not None:
            nombre_corto = ctx.nodo.short or ctx.nodo.node_id
            return [
                Alerta(
                    regla=self.id,
                    riesgo=riesgo,
                    mensaje=f"{nombre_corto} emite posición a un ritmo excesivo ({c5} en 5 min, {c15} en 15 min)",
                    nodo=ctx.nodo.node_id,
                    datos={
                        "posiciones_5m": c5,
                        "posiciones_10m": c10,
                        "posiciones_15m": c15,
                        "provincia": ctx.nodo.province or "FUERA",
                        "dentro_andalucia": ctx.nodo.dentro_andalucia,
                    },
                )
            ]
        return []

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """Determina si la alerta sigue activa o si se ha reducido el ritmo de balizas."""
        nodo = ctx.estado.nodes.get(abierta.nodo)
        if nodo is None:
            return True

        limite = ctx.ahora - timedelta(minutes=15)
        recientes = [t for t in nodo.position_timestamps_1h if t >= limite]
        return len(recientes) > 1
