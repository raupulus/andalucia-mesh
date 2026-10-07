"""Regla flood: detección de inundación y spam de paquetes propios."""

from datetime import timedelta
from typing import ClassVar, Literal

from pydantic import BaseModel, Field

from detector.modelos import PaqueteDecodificado
from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar


class ConfigNivelFlood(BaseModel):
    """Parámetros de un nivel de umbral para flood."""

    minimo: int
    factor_ritmo: float


class ConfigFlood(BaseModel):
    """Configuración para la regla flood."""

    activa: bool = True
    ventana_min: int = 10
    excluir: list[str] = Field(default_factory=lambda: ["routing"])
    resolver_min: int = 30
    medio: ConfigNivelFlood = Field(default_factory=lambda: ConfigNivelFlood(minimo=30, factor_ritmo=5.0))
    alto: ConfigNivelFlood = Field(default_factory=lambda: ConfigNivelFlood(minimo=100, factor_ritmo=15.0))


@registrar
class ReglaFlood:
    """Detecta emisores que inundan la malla excediendo significativamente su ritmo basal."""

    id: ClassVar[str] = "flood"
    nombre: ClassVar[str] = "Inundación de paquetes"
    descripcion: ClassVar[str] = "Nodo emitiendo tráfico propio por encima de su ritmo normal"
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "mvp"
    afecta_malla: ClassVar[bool] = True
    suscrita_a: ClassVar[frozenset[str]] = frozenset({"all"})
    Config: ClassVar[type[BaseModel]] = ConfigFlood

    def __init__(self, config: ConfigFlood | None = None) -> None:
        """Inicializa la regla."""
        self.config = config or ConfigFlood()

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa si el nodo emisor supera los umbrales de paquetes en la ventana móvil de 10 min."""
        if ctx.nodo is None:
            return []

        if isinstance(ctx.evento, PaqueteDecodificado) and ctx.evento.portnum in self.config.excluir:
            return []

        limite = ctx.ahora - timedelta(minutes=self.config.ventana_min)
        paquetes = len([ts for ts in ctx.nodo.packet_timestamps_10m if ts >= limite])

        ritmo = ctx.bases.ritmo_tipico_nodo(ctx.nodo, ctx.ahora)
        if ritmo is not None:
            umbral_alto = max(self.config.alto.minimo, int(self.config.alto.factor_ritmo * ritmo))
            umbral_medio = max(self.config.medio.minimo, int(self.config.medio.factor_ritmo * ritmo))
        else:
            umbral_alto = self.config.alto.minimo
            umbral_medio = self.config.medio.minimo

        riesgo: str | None = None
        umbral_aplicado = 0

        if paquetes >= umbral_alto:
            riesgo = "alto"
            umbral_aplicado = umbral_alto
        elif paquetes >= umbral_medio:
            riesgo = "medio"
            umbral_aplicado = umbral_medio

        if riesgo is not None:
            nombre_corto = ctx.nodo.short or ctx.nodo.node_id
            return [
                Alerta(
                    regla=self.id,
                    riesgo=riesgo,
                    mensaje=f"{nombre_corto} ha emitido {paquetes} paquetes en {self.config.ventana_min} min",
                    nodo=ctx.nodo.node_id,
                    datos={
                        "paquetes": paquetes,
                        "ventana_min": self.config.ventana_min,
                        "ritmo_tipico": round(ritmo, 1) if ritmo is not None else None,
                        "umbral": umbral_aplicado,
                    },
                )
            ]

        return []

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """La alerta se resuelve cuando el ritmo permanece bajo el umbral durante resolver_min."""
        nodo = ctx.estado.nodes.get(abierta.nodo)
        if nodo is None:
            return False

        limite = ctx.ahora - timedelta(minutes=self.config.ventana_min)
        paquetes = len([ts for ts in nodo.packet_timestamps_10m if ts >= limite])

        ritmo = ctx.bases.ritmo_tipico_nodo(nodo, ctx.ahora)
        umbral_medio = (
            max(self.config.medio.minimo, int(self.config.medio.factor_ritmo * ritmo))
            if ritmo is not None
            else self.config.medio.minimo
        )

        return not (
            paquetes < umbral_medio
            and (ctx.ahora - abierta.evidencia_en).total_seconds() >= self.config.resolver_min * 60
        )
