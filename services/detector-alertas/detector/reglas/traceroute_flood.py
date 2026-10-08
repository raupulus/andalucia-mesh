"""Regla traceroute-flood: supervisión de lanzamientos abusivos de traceroutes."""

from datetime import timedelta
from typing import ClassVar, Literal

from pydantic import BaseModel

from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar


class ConfigTracerouteFlood(BaseModel):
    """Configuración de umbrales para la regla traceroute-flood."""

    activa: bool = True
    ventana_min: int = 30
    umbral_medio: int = 10
    umbral_alto: int = 20
    resolver_min: int = 30


@registrar
class ReglaTracerouteFlood:
    """Supervisa el lanzamiento excesivo de traceroutes en ventana de 30 minutos."""

    id: ClassVar[str] = "traceroute-flood"
    nombre: ClassVar[str] = "Abuso de traceroutes"
    descripcion: ClassVar[str] = "Emisión excesiva de traceroutes en 30 min (10-19 medio, ≥ 20 alto)"
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "mvp"
    afecta_malla: ClassVar[bool] = False
    suscrita_a: ClassVar[frozenset[str]] = frozenset({"traceroute"})
    Config: ClassVar[type[BaseModel]] = ConfigTracerouteFlood

    def __init__(self, config: ConfigTracerouteFlood | None = None) -> None:
        """Inicializa la regla."""
        self.config = config or ConfigTracerouteFlood()

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa si el nodo emisor supera los umbrales de traceroutes en 30 minutos."""
        if ctx.nodo is None:
            return []

        limite = ctx.ahora - timedelta(minutes=self.config.ventana_min)
        recientes = [t for t in ctx.nodo.traceroute_timestamps_1h if t >= limite]
        cuenta = len(recientes)

        riesgo: str | None = None
        if cuenta >= self.config.umbral_alto:
            riesgo = "alto"
        elif cuenta >= self.config.umbral_medio:
            riesgo = "medio"

        if riesgo is not None:
            nombre_corto = ctx.nodo.short or ctx.nodo.node_id
            return [
                Alerta(
                    regla=self.id,
                    riesgo=riesgo,
                    mensaje=f"{nombre_corto} ha lanzado {cuenta} traceroutes en los últimos {self.config.ventana_min} min",
                    nodo=ctx.nodo.node_id,
                    datos={
                        "traceroutes_30m": cuenta,
                        "ventana_min": self.config.ventana_min,
                        "provincia": ctx.nodo.province or "FUERA",
                        "dentro_andalucia": ctx.nodo.dentro_andalucia,
                    },
                )
            ]
        return []

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """Determina si la alerta sigue activa o si han pasado 30 min sin traceroutes."""
        nodo = ctx.estado.nodes.get(abierta.nodo)
        if nodo is None:
            return True

        limite = ctx.ahora - timedelta(minutes=self.config.resolver_min)
        recientes = [t for t in nodo.traceroute_timestamps_1h if t >= limite]
        return len(recientes) > 0
