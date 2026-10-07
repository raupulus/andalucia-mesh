"""Regla reboot-loop: detección de reinicios continuos."""

from datetime import timedelta
from typing import ClassVar, Literal

from pydantic import BaseModel, Field

from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar


class ConfigRebootLoop(BaseModel):
    """Configuración de umbrales para la regla reboot-loop."""

    activa: bool = True
    ventana_min: int = 60
    uptime_arranque_s: int = 180
    resolver_min: int = 120
    umbral: dict[str, dict[str, int]] = Field(
        default_factory=lambda: {
            "infraestructura": {"alto": 3},
            "clientes": {"bajo": 3},
        }
    )


@registrar
class ReglaRebootLoop:
    """Detecta nodos en bucle de reinicio continuo mediante muestras de uptime."""

    id: ClassVar[str] = "reboot-loop"
    nombre: ClassVar[str] = "Bucle de reinicio"
    descripcion: ClassVar[str] = "Detección de reinicios continuos en ventana de 60 min"
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "mvp"
    afecta_malla: ClassVar[bool] = False
    suscrita_a: ClassVar[frozenset[str]] = frozenset({"telemetry"})
    Config: ClassVar[type[BaseModel]] = ConfigRebootLoop

    def __init__(self, config: ConfigRebootLoop | None = None) -> None:
        """Inicializa la regla."""
        self.config = config or ConfigRebootLoop()

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa si el nodo emisor supera el umbral de reinicios en la ventana."""
        if ctx.nodo is None:
            return []

        limite = ctx.ahora - timedelta(minutes=self.config.ventana_min)
        reinicios_recientes = [r for r in ctx.nodo.reboots_24h if r >= limite]
        num_reinicios = len(reinicios_recientes)

        es_infra = ctx.es_infraestructura(ctx.nodo.node_id)
        categoria = "infraestructura" if es_infra else "clientes"
        umbrales_cat = self.config.umbral.get(categoria, {})

        if es_infra:
            umbral = umbrales_cat.get("alto", 3)
            riesgo = "alto"
        else:
            umbral = umbrales_cat.get("bajo", 3)
            riesgo = "bajo"

        if num_reinicios >= umbral:
            nombre_corto = ctx.nodo.short or ctx.nodo.node_id
            return [
                Alerta(
                    regla=self.id,
                    riesgo=riesgo,
                    mensaje=f"{nombre_corto} se ha reiniciado {num_reinicios} veces en la última hora",
                    nodo=ctx.nodo.node_id,
                    datos={
                        "reinicios": num_reinicios,
                        "ventana_min": self.config.ventana_min,
                        "uptime_minimo_s": ctx.nodo.last_uptime_seconds or 0,
                        "umbral": umbral,
                    },
                )
            ]
        return []

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """Determina si la alerta sigue activa o si han pasado 120 min sin reinicios."""
        nodo = ctx.estado.nodes.get(abierta.nodo)
        if nodo is None:
            return True

        limite_resolucion = ctx.ahora - timedelta(minutes=self.config.resolver_min)
        reinicios_en_ventana = [r for r in nodo.reboots_24h if r >= limite_resolucion]
        return len(reinicios_en_ventana) > 0
