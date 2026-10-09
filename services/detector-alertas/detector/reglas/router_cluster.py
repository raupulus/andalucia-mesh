"""Regla router-cluster: detección de conglomerados redundantes de routers en línea de vista directa."""

from datetime import timedelta
from typing import ClassVar, Literal

from pydantic import BaseModel

from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar

# ROUTER_LATE se excluye a propósito: está diseñado específicamente para repetir tarde en clústeres
ROUTER_ROLES = frozenset({"ROUTER", "REPEATER"})


class ConfigRouterCluster(BaseModel):
    """Configuración para la regla router-cluster."""

    activa: bool = True
    umbral_routers: int = 3
    ventana_h: int = 24
    resolver_routers: int = 2


@registrar
class ReglaRouterCluster:
    """Supervisa si un router tiene enlaces directos con 3 o más routers simultáneamente."""

    id: ClassVar[str] = "router-cluster"
    nombre: ClassVar[str] = "Clúster redundante de routers"
    descripcion: ClassVar[str] = (
        "Router enlazado directamente con 3 o más routers vecinos (excluye ROUTER_LATE)"
    )
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "ampliacion"
    afecta_malla: ClassVar[bool] = True
    suscrita_a: ClassVar[frozenset[str]] = frozenset({"routing", "all"})
    Config: ClassVar[type[BaseModel]] = ConfigRouterCluster

    def __init__(self, config: ConfigRouterCluster | None = None) -> None:
        """Inicializa la regla con su configuración."""
        self.config = config or ConfigRouterCluster()

    def _obtener_routers_vecinos(self, ctx: Contexto, node_id: str) -> list[str]:
        """Obtiene la lista de IDs de otros routers enlazados directamente en la ventana temporal."""
        limite = ctx.ahora - timedelta(hours=self.config.ventana_h)
        vecinos_routers: set[str] = set()

        # 1. Vecinos directos reportados por el propio nodo
        nodo_obj = ctx.estado.nodes.get(node_id)
        if nodo_obj:
            for neighbor_id, (ts, _) in nodo_obj.direct_neighbors.items():
                if ts >= limite and neighbor_id != node_id:
                    vecino = ctx.estado.nodes.get(neighbor_id)
                    if vecino and (vecino.role or "").upper() in ROUTER_ROLES:
                        vecinos_routers.add(neighbor_id)

        # 2. Enlaces observados en el grafo RF del motor
        for (src, dst), (ts, _) in ctx.estado.rf_links.items():
            if ts >= limite and (src == node_id or dst == node_id):
                otro_id = dst if src == node_id else src
                if otro_id != node_id:
                    vecino = ctx.estado.nodes.get(otro_id)
                    if vecino and (vecino.role or "").upper() in ROUTER_ROLES:
                        vecinos_routers.add(otro_id)

        return sorted(vecinos_routers)

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa si el router emisor tiene enlace directo con 3 o más routers."""
        if ctx.nodo is None:
            return []

        rol = (ctx.nodo.role or "").upper()
        if rol not in ROUTER_ROLES:
            return []

        routers_enlazados = self._obtener_routers_vecinos(ctx, ctx.nodo.node_id)
        total = len(routers_enlazados)

        if total >= self.config.umbral_routers:
            nombre_corto = ctx.nodo.short or ctx.nodo.node_id
            labels = []
            for r_id in routers_enlazados[:4]:
                n = ctx.estado.nodes.get(r_id)
                labels.append(n.short if n and n.short else r_id)
            vecinos_txt = ", ".join(labels)
            if total > 4:
                vecinos_txt += f" y {total - 4} más"

            return [
                Alerta(
                    regla=self.id,
                    riesgo="medio",
                    mensaje=(
                        f"Router {nombre_corto} enlazado con {total} routers más "
                        f"({vecinos_txt}): posible repetición redundante"
                    ),
                    nodo=ctx.nodo.node_id,
                    datos={
                        "routers_enlazados": total,
                        "vecinos": routers_enlazados,
                        "umbral": self.config.umbral_routers,
                    },
                    tipo="infraestructura",
                )
            ]

        return []

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """Determina si la alerta sigue activa evaluando el número actual de routers enlazados."""
        routers_enlazados = self._obtener_routers_vecinos(ctx, abierta.nodo)
        if len(routers_enlazados) < self.config.resolver_routers:
            return False
        return (ctx.ahora - abierta.actualizada_en).total_seconds() <= (self.config.ventana_h * 3600)
