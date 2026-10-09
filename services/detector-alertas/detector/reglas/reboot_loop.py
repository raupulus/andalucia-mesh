"""Regla reboot-loop: detección de reinicios continuos."""

from datetime import timedelta
from typing import ClassVar, Literal

from pydantic import BaseModel

from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar


class ConfigRebootLoop(BaseModel):
    """Configuración de umbrales para la regla reboot-loop."""

    activa: bool = True
    ventana_media_min: int = 5
    umbral_medio: int = 3
    ventana_alta_min: int = 10
    umbral_alto: int = 5
    resolver_min: int = 30
    uptime_arranque_s: int = 180


@registrar
class ReglaRebootLoop:
    """Detecta nodos en bucle de reinicio continuo mediante caídas de uptime y ráfagas de nodeinfo."""

    id: ClassVar[str] = "reboot-loop"
    nombre: ClassVar[str] = "Bucle de reinicio"
    descripcion: ClassVar[str] = "Detección de reinicios continuos (3 en 5 min medio, ≥ 5 en 10 min alerta roja)"
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "mvp"
    afecta_malla: ClassVar[bool] = False
    suscrita_a: ClassVar[frozenset[str]] = frozenset({"telemetry", "nodeinfo"})
    Config: ClassVar[type[BaseModel]] = ConfigRebootLoop

    def __init__(self, config: ConfigRebootLoop | None = None) -> None:
        """Inicializa la regla."""
        self.config = config or ConfigRebootLoop()

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa si el nodo emisor supera los umbrales de reinicios en 5 o 10 minutos."""
        if ctx.nodo is None:
            return []

        ahora = ctx.ahora
        limite_10m = ahora - timedelta(minutes=self.config.ventana_alta_min)
        limite_5m = ahora - timedelta(minutes=self.config.ventana_media_min)

        reinicios_10m = [r for r in ctx.nodo.reboots_24h if r >= limite_10m]
        reinicios_5m = [r for r in ctx.nodo.reboots_24h if r >= limite_5m]

        nodeinfo_10m = [r for r in ctx.nodo.nodeinfo_timestamps_1h if r >= limite_10m]
        nodeinfo_5m = [r for r in ctx.nodo.nodeinfo_timestamps_1h if r >= limite_5m]

        # Los reinicios pueden observarse por caídas de uptime o por emisiones de arranque nodeinfo
        cuenta_10m = max(len(reinicios_10m), len(nodeinfo_10m))
        cuenta_5m = max(len(reinicios_5m), len(nodeinfo_5m))

        riesgo: str | None = None
        eventos = 0
        ventana_min = 0

        if cuenta_10m >= self.config.umbral_alto:
            riesgo = "alto"
            eventos = cuenta_10m
            ventana_min = self.config.ventana_alta_min
        elif cuenta_5m >= self.config.umbral_medio:
            riesgo = "medio"
            eventos = cuenta_5m
            ventana_min = self.config.ventana_media_min
        # Soporte para bucles lentos acumulados en 60 min (>= 3 reinicios)
        elif len([r for r in ctx.nodo.reboots_24h if r >= ahora - timedelta(minutes=60)]) >= 3:
            es_infra = ctx.es_infraestructura(ctx.nodo.node_id) or (ctx.nodo.role or "").upper() in ctx.bases.infra_roles
            riesgo = "alto" if es_infra else "medio"
            eventos = len([r for r in ctx.nodo.reboots_24h if r >= ahora - timedelta(minutes=60)])
            ventana_min = 60

        if riesgo is not None:
            nombre_corto = ctx.nodo.short or ctx.nodo.node_id
            return [
                Alerta(
                    regla=self.id,
                    riesgo=riesgo,
                    mensaje=f"{nombre_corto} se ha reiniciado {eventos} veces en {ventana_min} min",
                    nodo=ctx.nodo.node_id,
                    datos={
                        "reinicios": eventos,
                        "reinicios_5m": cuenta_5m,
                        "reinicios_10m": cuenta_10m,
                        "eventos": eventos,
                        "ventana_min": ventana_min,
                        "uptime_minimo_s": ctx.nodo.last_uptime_seconds or 0,
                        "umbral": eventos,
                        "provincia": ctx.nodo.province or "FUERA",
                        "dentro_andalucia": ctx.nodo.dentro_andalucia,
                    },
                )
            ]
        return []

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """Determina si la alerta sigue activa o si han pasado 30 min sin reinicios."""
        nodo = ctx.estado.nodes.get(abierta.nodo)
        if nodo is None:
            return True

        limite_resolucion = ctx.ahora - timedelta(minutes=self.config.resolver_min)
        reinicios_en_ventana = [r for r in nodo.reboots_24h if r >= limite_resolucion]
        nodeinfo_en_ventana = [r for r in nodo.nodeinfo_timestamps_1h if r >= limite_resolucion]
        return (len(reinicios_en_ventana) + len(nodeinfo_en_ventana)) > 0
