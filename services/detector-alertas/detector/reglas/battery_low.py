"""Regla battery-low: detección de nivel de batería crítico en muestras consecutivas."""

from typing import ClassVar, Literal

from pydantic import BaseModel, Field

from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar


class ConfigBatteryLow(BaseModel):
    """Configuración de umbrales para la regla battery-low."""

    activa: bool = True
    muestras_minimas: int = 2
    resolver: int = 50
    umbral: dict[str, dict[str, int]] = Field(
        default_factory=lambda: {
            "infraestructura": {"medio": 40, "alto": 20},
            "clientes": {"bajo": 20},
        }
    )


@registrar
class ReglaBatteryLow:
    """Supervisa el nivel de batería exigiendo muestras consecutivas para evitar falsos positivos."""

    id: ClassVar[str] = "battery-low"
    nombre: ClassVar[str] = "Batería baja"
    descripcion: ClassVar[str] = "Nivel de batería crítico confirmado en 2 muestras consecutivas"
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "mvp"
    afecta_malla: ClassVar[bool] = False
    suscrita_a: ClassVar[frozenset[str]] = frozenset({"telemetry"})
    Config: ClassVar[type[BaseModel]] = ConfigBatteryLow

    def __init__(self, config: ConfigBatteryLow | None = None) -> None:
        """Inicializa la regla."""
        self.config = config or ConfigBatteryLow()

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa si las últimas muestras consecutivas están por debajo de los umbrales."""
        if ctx.nodo is None or len(ctx.nodo.battery_samples) < self.config.muestras_minimas:
            return []

        ultimas = list(ctx.nodo.battery_samples)[-self.config.muestras_minimas :]
        # Si está alimentado externamente (> 100%), no hay alerta de batería baja
        if any(bat > 100 for _, bat, _ in ultimas):
            return []

        es_infra = ctx.es_infraestructura(ctx.nodo.node_id)
        umbrales_infra = self.config.umbral.get("infraestructura", {})
        umbrales_cli = self.config.umbral.get("clientes", {})

        riesgo: str | None = None
        umbral_aplicado: int = 0

        if es_infra:
            alto_u = umbrales_infra.get("alto", 20)
            medio_u = umbrales_infra.get("medio", 40)
            if all(bat < alto_u for _, bat, _ in ultimas):
                riesgo = "alto"
                umbral_aplicado = alto_u
            elif all(bat < medio_u for _, bat, _ in ultimas):
                riesgo = "medio"
                umbral_aplicado = medio_u
        else:
            bajo_u = umbrales_cli.get("bajo", 20)
            if all(bat < bajo_u for _, bat, _ in ultimas):
                riesgo = "bajo"
                umbral_aplicado = bajo_u

        if riesgo is not None:
            _, ult_bat, ult_volt = ultimas[-1]
            nombre_corto = ctx.nodo.short or ctx.nodo.node_id
            return [
                Alerta(
                    regla=self.id,
                    riesgo=riesgo,
                    mensaje=f"{nombre_corto} tiene la batería al {ult_bat} %",
                    nodo=ctx.nodo.node_id,
                    datos={
                        "bateria": ult_bat,
                        "voltaje": round(ult_volt, 2),
                        "umbral": umbral_aplicado,
                        "muestras": self.config.muestras_minimas,
                    },
                )
            ]

        return []

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """Determina si la alerta sigue activa o si la batería se ha recuperado por encima de 50%."""
        nodo = ctx.estado.nodes.get(abierta.nodo)
        if nodo is None or not nodo.battery_samples:
            return True

        _, ult_bat, _ = nodo.battery_samples[-1]
        return not (ult_bat > self.config.resolver or ult_bat > 100)
