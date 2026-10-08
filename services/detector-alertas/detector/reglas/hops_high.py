"""Regla hops-high: supervisión de límite excesivo de saltos en origen."""

from typing import ClassVar, Literal

from pydantic import BaseModel

from detector.modelos import PaqueteDecodificado
from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar


class ConfigHopsHigh(BaseModel):
    """Configuración para la regla hops-high."""

    activa: bool = True
    bajo_desde: int = 6
    alto_desde: int = 7
    resolver_paquetes: int = 2


@registrar
class ReglaHopsHigh:
    """Supervisa el hop_start en origen alertando ante valores excesivos (6 bajo, >= 7 alto)."""

    id: ClassVar[str] = "hops-high"
    nombre: ClassVar[str] = "Saltos excesivos"
    descripcion: ClassVar[str] = "Nodo originando paquetes con hop_start superior a lo recomendado (6 bajo, >= 7 alto)"
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "mvp"
    afecta_malla: ClassVar[bool] = False
    suscrita_a: ClassVar[frozenset[str]] = frozenset({"all"})
    Config: ClassVar[type[BaseModel]] = ConfigHopsHigh

    def __init__(self, config: ConfigHopsHigh | None = None) -> None:
        """Inicializa la regla."""
        self.config = config or ConfigHopsHigh()

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa si el hop_start del paquete es 6 (bajo) o mayor/igual a 7 (alto)."""
        if ctx.nodo is None or not isinstance(ctx.evento, PaqueteDecodificado):
            return []

        hop_start = ctx.evento.hop_start
        if hop_start is None or hop_start == 0:
            return []

        if hop_start >= self.config.alto_desde:
            riesgo = "alto"
        elif hop_start >= self.config.bajo_desde:
            riesgo = "bajo"
        else:
            return []

        nombre_corto = ctx.nodo.short or (
            ctx.evento.from_node.short if ctx.evento.from_node and ctx.evento.from_node.short else None
        ) or ctx.nodo.node_id
        return [
            Alerta(
                regla=self.id,
                riesgo=riesgo,
                mensaje=f"{nombre_corto} usa {hop_start} saltos (recomendado 3, máximo 5)",
                nodo=ctx.nodo.node_id,
                datos={
                    "hop_start": hop_start,
                    "recomendado": 3,
                    "maximo_valido": 5,
                },
            )
        ]

        return []

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """La alerta se resuelve cuando el nodo emite 2 paquetes seguidos con hop_start <= 5."""
        nodo = ctx.estado.nodes.get(abierta.nodo)
        if nodo is None or len(nodo.hop_starts) < self.config.resolver_paquetes:
            return True

        ultimos_saltos = list(nodo.hop_starts)[-self.config.resolver_paquetes :]
        return not all(h <= 5 for h in ultimos_saltos)
