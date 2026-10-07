"""Regla gateway-offline: detección de pasarelas MQTT inactivas."""

from typing import ClassVar, Literal

from pydantic import BaseModel, Field

from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar


class ConfigMedioGatewayOffline(BaseModel):
    """Parámetros de umbral medio para gateway-offline."""

    minimo_min: float = 15.0
    factor_intervalo: float = 3.0


class ConfigGatewayOffline(BaseModel):
    """Configuración para la regla gateway-offline."""

    activa: bool = True
    medio: ConfigMedioGatewayOffline = Field(default_factory=ConfigMedioGatewayOffline)
    alto_min: float = 120.0


@registrar
class ReglaGatewayOffline:
    """Detecta pasarelas MQTT que dejan de reportar paquetes según su periodicidad habitual."""

    id: ClassVar[str] = "gateway-offline"
    nombre: ClassVar[str] = "Gateway desconectado"
    descripcion: ClassVar[str] = "Pasarela que deja de publicar recepciones en el broker MQTT"
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "mvp"
    afecta_malla: ClassVar[bool] = False
    suscrita_a: ClassVar[frozenset[str]] = frozenset()  # Evaluación en tick
    Config: ClassVar[type[BaseModel]] = ConfigGatewayOffline

    def __init__(self, config: ConfigGatewayOffline | None = None) -> None:
        """Inicializa la regla."""
        self.config = config or ConfigGatewayOffline()

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa todos los gateways conocidos y genera alertas si superan el tiempo de silencio."""
        alertas: list[Alerta] = []

        for gw in ctx.estado.gateways.values():
            inicio_silencio = max(gw.last_seen_at, ctx.estado.started_at)
            silencio_s = (ctx.ahora - inicio_silencio).total_seconds()
            silencio_min = silencio_s / 60.0

            intervalo_s = ctx.bases.intervalo_tipico_gateway(gw, ctx.ahora)
            if intervalo_s is not None:
                umbral_medio_min = max(
                    self.config.medio.minimo_min,
                    (self.config.medio.factor_intervalo * intervalo_s) / 60.0,
                )
            else:
                umbral_medio_min = self.config.medio.minimo_min

            riesgo: str | None = None
            umbral_aplicado: float = 0.0

            if silencio_min >= self.config.alto_min:
                riesgo = "alto"
                umbral_aplicado = self.config.alto_min
            elif silencio_min >= umbral_medio_min:
                riesgo = "medio"
                umbral_aplicado = umbral_medio_min

            if riesgo is not None:
                nodo_info = ctx.estado.nodes.get(gw.gateway_id)
                nombre_corto = (nodo_info.short if nodo_info else None) or gw.gateway_id
                alertas.append(
                    Alerta(
                        regla=self.id,
                        riesgo=riesgo,
                        mensaje=f"El gateway {nombre_corto} no publica desde hace {round(silencio_min, 1)} min",
                        nodo=gw.gateway_id,
                        datos={
                            "ultimo_mensaje": gw.last_seen_at.isoformat(),
                            "silencio_min": round(silencio_min, 1),
                            "intervalo_tipico_s": round(intervalo_s, 1) if intervalo_s else None,
                            "umbral_min": round(umbral_aplicado, 1),
                        },
                    )
                )

        return alertas

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """La alerta se resuelve cuando el gateway vuelve a subir una recepción al broker."""
        gw = ctx.estado.gateways.get(abierta.nodo)
        if gw is None:
            return False
        return gw.last_seen_at <= abierta.abierta_en
