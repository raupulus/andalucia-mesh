"""Regla private-chaff: detección de tráfico cifrado privado o de sensores saturando la malla pública."""

from datetime import timedelta
from typing import ClassVar, Literal

from pydantic import BaseModel

from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar


class ConfigPrivateChaff(BaseModel):
    """Configuración de umbrales para la regla private-chaff."""

    activa: bool = True
    ventana_10m_min: int = 10
    umbral_10m_medio: int = 10
    ventana_1h_min: int = 60
    umbral_1h_medio: int = 30
    umbral_1h_alto: int = 60
    resolver_min: int = 30


@registrar
class ReglaPrivateChaff:
    """Supervisa paquetes no descifrados o canales privados ('other') que saturan la red LoRa compartida."""

    id: ClassVar[str] = "private-chaff"
    nombre: ClassVar[str] = "Tráfico cifrado privado en malla pública"
    descripcion: ClassVar[str] = "Paquetes privados o de sensores saturando repetidamente la red LoRa pública"
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "mvp"
    afecta_malla: ClassVar[bool] = True
    suscrita_a: ClassVar[frozenset[str]] = frozenset({"other"})
    Config: ClassVar[type[BaseModel]] = ConfigPrivateChaff

    def __init__(self, config: ConfigPrivateChaff | None = None) -> None:
        """Inicializa la regla."""
        self.config = config or ConfigPrivateChaff()

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa si el nodo emite volumen excesivo de paquetes encriptados privados."""
        if ctx.nodo is None:
            return []

        limite_10m = ctx.ahora - timedelta(minutes=self.config.ventana_10m_min)
        limite_1h = ctx.ahora - timedelta(minutes=self.config.ventana_1h_min)

        recientes_10m = [t for t in ctx.nodo.private_chaff_1h if t >= limite_10m]
        recientes_1h = [t for t in ctx.nodo.private_chaff_1h if t >= limite_1h]

        cuenta_10m = len(recientes_10m)
        cuenta_1h = len(recientes_1h)

        riesgo: str | None = None
        if cuenta_1h > self.config.umbral_1h_alto:
            riesgo = "alto"
        elif cuenta_10m > self.config.umbral_10m_medio or cuenta_1h > self.config.umbral_1h_medio:
            riesgo = "medio"

        if riesgo is not None:
            nombre_corto = ctx.nodo.short or ctx.nodo.node_id
            return [
                Alerta(
                    regla=self.id,
                    riesgo=riesgo,
                    mensaje=f"{nombre_corto} emite tráfico privado o sensorizado intensivo ({cuenta_10m} en 10 min, {cuenta_1h}/h)",
                    nodo=ctx.nodo.node_id,
                    datos={
                        "paquetes_10m": cuenta_10m,
                        "paquetes_1h": cuenta_1h,
                        "provincia": ctx.nodo.province or "FUERA",
                        "dentro_andalucia": ctx.nodo.dentro_andalucia,
                    },
                )
            ]
        return []

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """Determina si la alerta sigue activa o si el tráfico privado ha cesado."""
        nodo = ctx.estado.nodes.get(abierta.nodo)
        if nodo is None:
            return True

        limite = ctx.ahora - timedelta(minutes=self.config.resolver_min)
        recientes = [t for t in nodo.private_chaff_1h if t >= limite]
        return len(recientes) > 5
