"""Regla router-moving: supervisión de desplazamiento físico anómalo en repetidores."""

from datetime import timedelta
from typing import ClassVar, Literal

from pydantic import BaseModel

from detector.motor.estado import haversine_m
from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar

INFRA_ROLES = frozenset({"ROUTER", "REPEATER", "ROUTER_LATE"})


class ConfigRouterMoving(BaseModel):
    """Configuración para la regla router-moving."""

    activa: bool = True
    umbral_km: float = 5.0
    ventana_h: int = 24
    resolver_km: float = 3.0


@registrar
class ReglaRouterMoving:
    """Supervisa el desplazamiento geográfico en 24 horas de nodos de infraestructura."""

    id: ClassVar[str] = "router-moving"
    nombre: ClassVar[str] = "Repetidor en movimiento físico"
    descripcion: ClassVar[str] = "Router o repetidor con desplazamiento mayor a 5 km en 24 horas"
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "ampliacion"
    afecta_malla: ClassVar[bool] = True
    suscrita_a: ClassVar[frozenset[str]] = frozenset({"position"})
    Config: ClassVar[type[BaseModel]] = ConfigRouterMoving

    def __init__(self, config: ConfigRouterMoving | None = None) -> None:
        """Inicializa la regla con su configuración."""
        self.config = config or ConfigRouterMoving()

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa si un repetidor registra un desplazamiento de más de 5 km en 24 horas."""
        if ctx.nodo is None:
            return []

        rol = (ctx.nodo.role or "").upper()
        if rol not in INFRA_ROLES and not ctx.es_infraestructura(ctx.nodo.node_id):
            return []

        # Purgar posiciones más antiguas que 24 horas
        limite_24h = ctx.ahora - timedelta(hours=self.config.ventana_h)
        muestras = [p for p in ctx.nodo.positions_24h if p[0] >= limite_24h]
        if len(muestras) < 2:
            return []

        lats = [p[1] for p in muestras]
        lons = [p[2] for p in muestras]

        lat_min, lat_max = min(lats), max(lats)
        lon_min, lon_max = min(lons), max(lons)

        distancia_m = haversine_m(lat_min, lon_min, lat_max, lon_max)
        distancia_km = distancia_m / 1000.0

        if distancia_km > self.config.umbral_km:
            nombre_corto = ctx.nodo.short or ctx.nodo.node_id
            return [
                Alerta(
                    regla=self.id,
                    riesgo="alto",
                    mensaje=(
                        f"Router {nombre_corto} en movimiento físico: se ha desplazado "
                        f"{round(distancia_km, 1)} km en 24h (umbral {self.config.umbral_km:g} km)"
                    ),
                    nodo=ctx.nodo.node_id,
                    datos={
                        "desplazamiento_km": round(distancia_km, 2),
                        "umbral_km": self.config.umbral_km,
                        "muestras": len(muestras),
                        "rol": ctx.nodo.role,
                    },
                    tipo="infraestructura",
                )
            ]

        return []

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """Determina si la alerta sigue activa evaluando si el desplazamiento ha cesado."""
        nodo = ctx.estado.nodes.get(abierta.nodo)
        if nodo is None:
            return False

        limite_24h = ctx.ahora - timedelta(hours=self.config.ventana_h)
        muestras = [p for p in nodo.positions_24h if p[0] >= limite_24h]
        if len(muestras) < 2:
            return (ctx.ahora - abierta.actualizada_en).total_seconds() <= (self.config.ventana_h * 3600)

        lats = [p[1] for p in muestras]
        lons = [p[2] for p in muestras]
        distancia_km = haversine_m(min(lats), min(lons), max(lats), max(lons)) / 1000.0

        if distancia_km <= self.config.resolver_km:
            return False

        return (ctx.ahora - abierta.actualizada_en).total_seconds() <= (self.config.ventana_h * 3600)
