"""Regla chutil-high: supervisión de saturación del canal LoRa con ponderación por roles."""

from typing import ClassVar, Literal

from pydantic import BaseModel

from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar


class ConfigChutilHigh(BaseModel):
    """Configuración de umbrales para la regla chutil-high."""

    activa: bool = True
    bajo: float = 20.0
    medio: float = 30.0
    alto: float = 40.0
    resolver: float = 18.0
    peso_routers: float = 0.6
    peso_clientes: float = 0.4


@registrar
class ReglaChutilHigh:
    """Supervisa la saturación del canal LoRa ponderando routers (60%) y clientes (40%)."""

    id: ClassVar[str] = "chutil-high"
    nombre: ClassVar[str] = "Canal saturado"
    descripcion: ClassVar[str] = "Ocupación de canal LoRa excesiva (> 20% bajo, > 30% medio, > 40% alto)"
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "mvp"
    afecta_malla: ClassVar[bool] = False
    suscrita_a: ClassVar[frozenset[str]] = frozenset({"telemetry"})
    Config: ClassVar[type[BaseModel]] = ConfigChutilHigh

    def __init__(self, config: ConfigChutilHigh | None = None) -> None:
        """Inicializa la regla."""
        self.config = config or ConfigChutilHigh()

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa si el nodo emisor reporta una saturación del canal superior a los umbrales."""
        if ctx.nodo is None or ctx.nodo.last_channel_utilization is None:
            return []

        rol = (ctx.nodo.role or "").strip().upper()
        es_router = ctx.es_infraestructura(ctx.nodo.node_id) or rol in ctx.bases.infra_roles
        es_cliente = rol in ("CLIENT", "CLIENT_BASE")

        # Regla de negocio: los routers pesan el 60% y clientes el 40%; otros nodos se ignoran
        if not es_router and not es_cliente:
            return []

        chutil = ctx.nodo.last_channel_utilization
        riesgo: str | None = None

        if chutil > self.config.alto:
            riesgo = "alto"
        elif chutil > self.config.medio:
            riesgo = "medio"
        elif chutil > self.config.bajo:
            riesgo = "bajo"

        if riesgo is not None:
            nombre_corto = ctx.nodo.short or ctx.nodo.node_id
            tipo_alerta = "infraestructura" if es_router else "clientes"

            # Calcular saturación provincial ponderada (60% routers / 40% clientes)
            sat_prov = None
            if ctx.nodo.province and ctx.nodo.dentro_andalucia:
                sat_prov = ctx.bases.saturacion_provincial(
                    ctx.estado,
                    ctx.nodo.province,
                    peso_routers=self.config.peso_routers,
                    peso_clientes=self.config.peso_clientes,
                    ahora=ctx.ahora,
                )

            return [
                Alerta(
                    regla=self.id,
                    riesgo=riesgo,
                    tipo=tipo_alerta,
                    mensaje=f"{nombre_corto} reporta una saturación de canal del {chutil:.1f} %",
                    nodo=ctx.nodo.node_id,
                    datos={
                        "chutil": round(chutil, 1),
                        "rol": rol,
                        "peso_rol": self.config.peso_routers if es_router else self.config.peso_clientes,
                        "saturacion_provincial": round(sat_prov, 1) if sat_prov is not None else None,
                        "provincia": ctx.nodo.province or "FUERA",
                        "dentro_andalucia": ctx.nodo.dentro_andalucia,
                    },
                )
            ]
        return []

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """Determina si la alerta sigue activa o si el canal ha bajado del umbral de resolución."""
        nodo = ctx.estado.nodes.get(abierta.nodo)
        if nodo is None or nodo.last_channel_utilization is None:
            return True
        return nodo.last_channel_utilization > self.config.resolver
