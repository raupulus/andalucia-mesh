"""Regla text-flood: supervisión de ráfagas masivas de mensajes de texto."""

from datetime import timedelta
from typing import ClassVar, Literal

from pydantic import BaseModel

from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar


class ConfigTextFlood(BaseModel):
    """Configuración de umbrales para la regla text-flood."""

    activa: bool = True
    ventana_s: int = 60
    umbral_bajo: int = 5
    umbral_medio: int = 6
    umbral_alto: int = 10
    resolver_min: int = 5


@registrar
class ReglaTextFlood:
    """Supervisa el ritmo de emisión de mensajes de texto en ventana de 1 minuto."""

    id: ClassVar[str] = "text-flood"
    nombre: ClassVar[str] = "Ráfaga de mensajes de texto"
    descripcion: ClassVar[str] = "Emisión excesiva de mensajes de texto en 1 min (> 5 bajo, 6-10 medio, > 10 alto)"
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "mvp"
    afecta_malla: ClassVar[bool] = True
    suscrita_a: ClassVar[frozenset[str]] = frozenset({"text"})
    Config: ClassVar[type[BaseModel]] = ConfigTextFlood

    def __init__(self, config: ConfigTextFlood | None = None) -> None:
        """Inicializa la regla."""
        self.config = config or ConfigTextFlood()

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa si el nodo ha enviado más mensajes de texto de los permitidos en 1 minuto."""
        if ctx.nodo is None:
            return []

        limite = ctx.ahora - timedelta(seconds=self.config.ventana_s)
        mensajes_1m = [t for t in ctx.nodo.text_messages_10m if t >= limite]
        cuenta = len(mensajes_1m)

        riesgo: str | None = None
        if cuenta > self.config.umbral_alto:
            riesgo = "alto"
        elif cuenta >= self.config.umbral_medio:
            riesgo = "medio"
        elif cuenta > self.config.umbral_bajo:
            riesgo = "bajo"

        if riesgo is not None:
            nombre_corto = ctx.nodo.short or ctx.nodo.node_id
            return [
                Alerta(
                    regla=self.id,
                    riesgo=riesgo,
                    mensaje=f"{nombre_corto} ha enviado {cuenta} mensajes de texto en 1 minuto",
                    nodo=ctx.nodo.node_id,
                    datos={
                        "mensajes_1m": cuenta,
                        "ventana_s": self.config.ventana_s,
                        "provincia": ctx.nodo.province or "FUERA",
                        "dentro_andalucia": ctx.nodo.dentro_andalucia,
                    },
                )
            ]
        return []

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """Determina si la alerta sigue activa o si han pasado 5 min con ritmo normal."""
        nodo = ctx.estado.nodes.get(abierta.nodo)
        if nodo is None:
            return True

        limite = ctx.ahora - timedelta(minutes=self.config.resolver_min)
        recientes = [t for t in nodo.text_messages_10m if t >= limite]
        return len(recientes) > self.config.umbral_bajo
