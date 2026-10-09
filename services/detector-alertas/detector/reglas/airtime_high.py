"""Regla airtime-high: supervisión del ciclo de trabajo de transmisión propio."""

from typing import ClassVar, Literal

from pydantic import BaseModel

from detector.modelos import PaqueteDecodificado
from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar


class ConfigAirtimeHigh(BaseModel):
    """Configuración de umbrales para la regla airtime-high."""

    activa: bool = True
    bajo: float = 4.0
    medio: float = 6.0
    alto: float = 8.0
    resolver: float = 3.0


@registrar
class ReglaAirtimeHigh:
    """Supervisa el porcentaje de tiempo de aire propio empleado por un nodo."""

    id: ClassVar[str] = "airtime-high"
    nombre: ClassVar[str] = "Uso de tiempo de aire excesivo"
    descripcion: ClassVar[str] = (
        "Tiempo de aire propio por encima de límites operativos (bajo > 4 %, medio > 6 %, alto > 8 %)"
    )
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "ampliacion"
    afecta_malla: ClassVar[bool] = True
    suscrita_a: ClassVar[frozenset[str]] = frozenset({"telemetry"})
    Config: ClassVar[type[BaseModel]] = ConfigAirtimeHigh

    def __init__(self, config: ConfigAirtimeHigh | None = None) -> None:
        """Inicializa la regla con su configuración."""
        self.config = config or ConfigAirtimeHigh()

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa si la métrica air_util_tx del nodo supera los umbrales configurados."""
        if ctx.nodo is None or not isinstance(ctx.evento, PaqueteDecodificado):
            return []

        payload = ctx.evento.payload or {}
        dm = payload.get("device_metrics") or {}
        ls = payload.get("local_stats") or {}

        air_val_raw = dm.get("air_util_tx") if dm.get("air_util_tx") is not None else ls.get("air_util_tx")
        if air_val_raw is None:
            return []

        try:
            air_val = float(air_val_raw)
        except (ValueError, TypeError):
            return []

        riesgo: str | None = None
        umbral_aplicado: float = 0.0

        if air_val > self.config.alto:
            riesgo = "alto"
            umbral_aplicado = self.config.alto
        elif air_val > self.config.medio:
            riesgo = "medio"
            umbral_aplicado = self.config.medio
        elif air_val > self.config.bajo:
            riesgo = "bajo"
            umbral_aplicado = self.config.bajo
        else:
            return []

        nombre_corto = ctx.nodo.short or ctx.nodo.node_id
        return [
            Alerta(
                regla=self.id,
                riesgo=riesgo,
                mensaje=f"{nombre_corto} supera el límite de ocupación de transmisión: {round(air_val, 1)}% de tiempo de aire",
                nodo=ctx.nodo.node_id,
                datos={
                    "air_util_tx": round(air_val, 2),
                    "umbral": umbral_aplicado,
                    "rol": ctx.nodo.role,
                },
            )
        ]

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """Determina si la alerta sigue activa o si el tiempo de aire ha descendido."""
        nodo = ctx.estado.nodes.get(abierta.nodo)
        if nodo is None:
            return False

        if isinstance(ctx.evento, PaqueteDecodificado) and ctx.evento.from_node_id == abierta.nodo:
            payload = ctx.evento.payload or {}
            dm = payload.get("device_metrics") or {}
            ls = payload.get("local_stats") or {}
            air_val_raw = dm.get("air_util_tx") if dm.get("air_util_tx") is not None else ls.get("air_util_tx")
            if air_val_raw is not None:
                try:
                    if float(air_val_raw) <= self.config.resolver:
                        return False
                except (ValueError, TypeError):
                    pass

        # Si han pasado más de 60 minutos sin nuevas muestras por encima del umbral, se resuelve
        return (ctx.ahora - abierta.actualizada_en).total_seconds() <= 3600
