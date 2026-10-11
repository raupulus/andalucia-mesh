"""Regla hops-high: supervisión de límite excesivo de saltos en origen."""

from typing import ClassVar, Literal

from pydantic import BaseModel

from detector.modelos import PaqueteDecodificado
from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar
from detector.motor.umbrales import GestorUmbrales


class ConfigHopsHigh(BaseModel):
    """Configuración para la regla hops-high."""

    activa: bool = True
    bajo_desde: int = 6
    alto_desde: int = 7
    resolver_paquetes: int = 2


@registrar
class ReglaHopsHigh:
    """Supervisa el hop_start en origen alertando ante valores excesivos (según grupo de roles)."""

    id: ClassVar[str] = "hops-high"
    nombre: ClassVar[str] = "Saltos excesivos"
    descripcion: ClassVar[str] = "Nodo originando paquetes con hop_start superior a lo recomendado para su rol"
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "mvp"
    afecta_malla: ClassVar[bool] = False
    suscrita_a: ClassVar[frozenset[str]] = frozenset({"all"})
    Config: ClassVar[type[BaseModel]] = ConfigHopsHigh

    def __init__(self, config: ConfigHopsHigh | None = None) -> None:
        """Inicializa la regla."""
        self.config = config or ConfigHopsHigh()

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa si el hop_start del paquete supera el umbral de aviso o crítico para su rol."""
        if ctx.nodo is None or not isinstance(ctx.evento, PaqueteDecodificado):
            return []

        hop_start = ctx.evento.hop_start
        if hop_start is None or hop_start == 0:
            return []

        gestor = ctx.umbrales or GestorUmbrales()
        es_infra = ctx.es_infraestructura(ctx.nodo.node_id)
        rol = "ROUTER" if es_infra else ctx.nodo.role
        umbrales_grupo = gestor.obtener_grupo(rol)

        alto_u = umbrales_grupo.hop_limit_critical
        bajo_u = umbrales_grupo.hop_limit_warning

        if hop_start >= alto_u:
            riesgo = "alto"
        elif hop_start >= bajo_u:
            riesgo = "bajo"
        else:
            return []

        maximo_valido = bajo_u - 1
        nombre_corto = ctx.nodo.short or (
            ctx.evento.from_node.short if ctx.evento.from_node and ctx.evento.from_node.short else None
        ) or ctx.nodo.node_id
        return [
            Alerta(
                regla=self.id,
                riesgo=riesgo,
                mensaje=f"{nombre_corto} usa {hop_start} saltos (recomendado {umbrales_grupo.hop_limit_recommended}, máximo {maximo_valido})",
                nodo=ctx.nodo.node_id,
                datos={
                    "hop_start": hop_start,
                    "recomendado": umbrales_grupo.hop_limit_recommended,
                    "maximo_valido": maximo_valido,
                },
            )
        ]

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """La alerta se resuelve cuando el nodo emite 2 paquetes seguidos dentro de los límites válidos."""
        nodo = ctx.estado.nodes.get(abierta.nodo)
        if nodo is None or len(nodo.hop_starts) < self.config.resolver_paquetes:
            return True

        gestor = ctx.umbrales or GestorUmbrales()
        es_infra = ctx.es_infraestructura(nodo.node_id)
        rol = "ROUTER" if es_infra else nodo.role
        umbrales_grupo = gestor.obtener_grupo(rol)
        umbral_aviso = umbrales_grupo.hop_limit_warning

        ultimos_saltos = list(nodo.hop_starts)[-self.config.resolver_paquetes :]
        return not all(h < umbral_aviso for h in ultimos_saltos)
