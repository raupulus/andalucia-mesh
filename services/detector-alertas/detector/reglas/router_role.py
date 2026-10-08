"""Regla router-role: detección de routers no coordinados en Andalucía."""

from typing import ClassVar, Literal

from pydantic import BaseModel, Field

from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar


class ConfigRouterRole(BaseModel):
    """Configuración de umbrales para la regla router-role."""

    activa: bool = True
    routers_coordinados: list[str] = Field(default_factory=list)


@registrar
class ReglaRouterRole:
    """Supervisa nodos con rol ROUTER en Andalucía que no figuren en la coordinación oficial."""

    id: ClassVar[str] = "router-role"
    nombre: ClassVar[str] = "Router no coordinado"
    descripcion: ClassVar[str] = "Nodo con rol ROUTER en Andalucía sin coordinación previa con la comunidad"
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "mvp"
    afecta_malla: ClassVar[bool] = False
    suscrita_a: ClassVar[frozenset[str]] = frozenset({"nodeinfo", "telemetry"})
    Config: ClassVar[type[BaseModel]] = ConfigRouterRole

    def __init__(self, config: ConfigRouterRole | None = None) -> None:
        """Inicializa la regla."""
        self.config = config or ConfigRouterRole()

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa si un nodo emite como ROUTER en Andalucía sin estar en la lista de aprobados."""
        if ctx.nodo is None:
            return []

        rol = (ctx.nodo.role or "").strip().upper()
        if rol not in ("ROUTER", "ROUTER_LATE", "REPEATER"):
            return []

        # Regla geográfica estricta: Descartar nodos fuera de Andalucía
        if not ctx.nodo.dentro_andalucia or ctx.nodo.province == "FUERA":
            return []

        coordinados = set(self.config.routers_coordinados)
        if ctx.nodo.node_id in coordinados:
            return []

        nombre_corto = ctx.nodo.short or ctx.nodo.node_id
        return [
            Alerta(
                regla=self.id,
                riesgo="medio",
                tipo="infraestructura",
                mensaje=f"{nombre_corto} tiene rol {rol} en {ctx.nodo.province} sin estar coordinado en Andalucía Mesh",
                nodo=ctx.nodo.node_id,
                datos={
                    "rol": rol,
                    "provincia": ctx.nodo.province,
                    "dentro_andalucia": True,
                    "coordinado": False,
                },
            )
        ]

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """Determina si la alerta sigue activa o si el nodo se ha coordinado o cambiado de rol."""
        nodo = ctx.estado.nodes.get(abierta.nodo)
        if nodo is None:
            return True

        rol = (nodo.role or "").strip().upper()
        if rol not in ("ROUTER", "ROUTER_LATE", "REPEATER"):
            return False

        if not nodo.dentro_andalucia or nodo.province == "FUERA":
            return False

        coordinados = set(self.config.routers_coordinados)
        return nodo.node_id not in coordinados
