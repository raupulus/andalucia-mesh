"""Regla gateway-no-traffic: pasarela con transceptor LoRa sordo o bloqueado."""

from typing import ClassVar, Literal

from pydantic import BaseModel

from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar


class ConfigGatewayNoTraffic(BaseModel):
    """Configuración para la regla gateway-no-traffic."""

    activa: bool = True
    ventana_s: int = 3600  # 1 hora
    umbral_min: float = 60.0


@registrar
class ReglaGatewayNoTraffic:
    """Detecta pasarelas conectadas que no reciben tráfico LoRa por posible bloqueo del chip de radio."""

    id: ClassVar[str] = "gateway-no-traffic"
    nombre: ClassVar[str] = "Pasarela sin tráfico LoRa"
    descripcion: ClassVar[str] = "Pasarela conectada pero sin recepciones LoRa durante más de 1 hora (radio sorda)"
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "ampliacion"
    afecta_malla: ClassVar[bool] = True
    suscrita_a: ClassVar[frozenset[str]] = frozenset()  # Evaluación en tick
    Config: ClassVar[type[BaseModel]] = ConfigGatewayNoTraffic

    def __init__(self, config: ConfigGatewayNoTraffic | None = None) -> None:
        """Inicializa la regla con su configuración."""
        self.config = config or ConfigGatewayNoTraffic()

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa pasarelas con historial de recepciones que lleven más de 1 hora sin tráfico LoRa."""
        alertas: list[Alerta] = []

        for gw in ctx.estado.gateways.values():
            if not gw.has_prior_traffic or gw.last_lora_rx_at is None:
                continue

            silencio_s = (ctx.ahora - gw.last_lora_rx_at).total_seconds()
            silencio_min = silencio_s / 60.0

            if silencio_s >= self.config.ventana_s:
                nodo_info = ctx.estado.nodes.get(gw.gateway_id)
                nombre_corto = (nodo_info.short if nodo_info else None) or gw.gateway_id

                alertas.append(
                    Alerta(
                        regla=self.id,
                        riesgo="bajo",
                        mensaje=(
                            f"El gateway {nombre_corto} no recibe tráfico LoRa desde hace "
                            f"{round(silencio_min, 1)} min (posible radio bloqueada)"
                        ),
                        nodo=gw.gateway_id,
                        datos={
                            "ultimo_paquete": gw.last_lora_rx_at.isoformat(),
                            "silencio_min": round(silencio_min, 1),
                            "umbral_min": self.config.umbral_min,
                        },
                        tipo="infraestructura",
                    )
                )

        return alertas

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """La alerta se resuelve cuando la pasarela vuelve a registrar una recepción LoRa."""
        gw = ctx.estado.gateways.get(abierta.nodo)
        if gw is None or gw.last_lora_rx_at is None:
            return False
        return gw.last_lora_rx_at <= abierta.abierta_en
