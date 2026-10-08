"""Regla poll-abuse: detección de sondeos broadcast indiscriminados a la malla."""

from datetime import timedelta
from typing import ClassVar, Literal

from pydantic import BaseModel

from detector.modelos import PaqueteDecodificado
from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar


class ConfigPollAbuse(BaseModel):
    """Configuración de umbrales para la regla poll-abuse."""

    activa: bool = True
    ventana_min: int = 15
    umbral_bajo: int = 1
    umbral_medio: int = 3
    umbral_alto: int = 5
    resolver_min: int = 30


@registrar
class ReglaPollAbuse:
    """Supervisa el uso abusivo de sondeos y peticiones a broadcast (^all) que colapsan la malla."""

    id: ClassVar[str] = "poll-abuse"
    nombre: ClassVar[str] = "Abuso de sondeos broadcast"
    descripcion: ClassVar[str] = "Sondeos indiscriminados a broadcast (^all) que provocan respuestas masivas"
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "mvp"
    afecta_malla: ClassVar[bool] = True
    suscrita_a: ClassVar[frozenset[str]] = frozenset({"routing", "nodeinfo", "telemetry", "position", "traceroute", "admin", "other"})
    Config: ClassVar[type[BaseModel]] = ConfigPollAbuse

    def __init__(self, config: ConfigPollAbuse | None = None) -> None:
        """Inicializa la regla."""
        self.config = config or ConfigPollAbuse()

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa si el nodo ha lanzado solicitudes de sondeo por broadcast."""
        if ctx.nodo is None or not isinstance(ctx.evento, PaqueteDecodificado):
            return []

        # Solo paquetes dirigidos a broadcast
        if ctx.evento.to not in ("^all", "ffffffff", "*"):
            return []

        # Comprobar si el evento actual es un sondeo o petición
        payload = ctx.evento.payload or {}
        es_sondeo = bool(
            payload.get("request_id")
            or payload.get("want_response")
            or ctx.evento.portnum in ("traceroute", "routing")
            or (ctx.evento.portnum == "telemetry" and not any(k in payload for k in ("device_metrics", "environment_metrics", "power_metrics", "air_quality_metrics", "local_stats")))
        )

        if not es_sondeo:
            return []

        limite = ctx.ahora - timedelta(minutes=self.config.ventana_min)
        sondeos_15m = [p for p in ctx.nodo.broadcast_polls_1h if p[0] >= limite]
        cuenta = len(sondeos_15m)

        riesgo: str | None = None
        if cuenta >= self.config.umbral_alto:
            riesgo = "alto"
        elif cuenta >= self.config.umbral_medio:
            riesgo = "medio"
        elif cuenta >= self.config.umbral_bajo:
            riesgo = "bajo"

        if riesgo is not None:
            nombre_corto = ctx.nodo.short or ctx.nodo.node_id
            return [
                Alerta(
                    regla=self.id,
                    riesgo=riesgo,
                    mensaje=f"{nombre_corto} ha emitido {cuenta} sondeo(s) a broadcast (^all) en {self.config.ventana_min} min",
                    nodo=ctx.nodo.node_id,
                    datos={
                        "sondeos_15m": cuenta,
                        "tipo_sondeo": ctx.evento.portnum,
                        "ventana_min": self.config.ventana_min,
                        "provincia": ctx.nodo.province or "FUERA",
                        "dentro_andalucia": ctx.nodo.dentro_andalucia,
                    },
                )
            ]
        return []

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """Determina si la alerta sigue activa o si han pasado 30 min sin sondeos broadcast."""
        nodo = ctx.estado.nodes.get(abierta.nodo)
        if nodo is None:
            return True

        limite = ctx.ahora - timedelta(minutes=self.config.resolver_min)
        recientes = [p for p in nodo.broadcast_polls_1h if p[0] >= limite]
        return len(recientes) > 0
