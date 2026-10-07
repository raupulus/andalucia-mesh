"""Regla rafaga-masiva: detección de emisión simultánea masiva en la malla."""

from typing import ClassVar, Literal

from pydantic import BaseModel

from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar


class ConfigRafagaMasiva(BaseModel):
    """Configuración para la regla rafaga-masiva."""

    activa: bool = True
    ventana_s: int = 120
    nodos_minimos: int = 50
    factor_linea_base: float = 5.0
    resolver_min: int = 15


@registrar
class ReglaRafagaMasiva:
    """Detecta ráfagas colectivas donde un número anómalo de nodos emite en un corto intervalo."""

    id: ClassVar[str] = "rafaga-masiva"
    nombre: ClassVar[str] = "Ráfaga masiva simultánea"
    descripcion: ClassVar[str] = "Múltiples nodos emitiendo de forma sincronizada en la malla"
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "mvp"
    afecta_malla: ClassVar[bool] = True
    suscrita_a: ClassVar[frozenset[str]] = frozenset({"all"})
    Config: ClassVar[type[BaseModel]] = ConfigRafagaMasiva

    def __init__(self, config: ConfigRafagaMasiva | None = None) -> None:
        """Inicializa la regla."""
        self.config = config or ConfigRafagaMasiva()

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa el recuento de nodos únicos observados en los últimos 120 segundos."""
        total_nodos, lista_nodos = ctx.bases.actividad_malla_reciente(
            ctx.estado,
            ctx.ahora,
            ventana_s=self.config.ventana_s,
        )

        # Base nominal de actividad o aprendida
        base = 10.0
        umbral = max(self.config.nodos_minimos, int(self.config.factor_linea_base * base))

        if total_nodos >= umbral:
            lista_recortada = tuple(lista_nodos[: ctx.general.nodos_max_lista])
            return [
                Alerta(
                    regla=self.id,
                    riesgo="alto",
                    mensaje=f"{total_nodos} nodos han emitido a la vez en {self.config.ventana_s} s",
                    nodo="all",
                    nodos=lista_recortada,
                    datos={
                        "nodos_total": total_nodos,
                        "ventana_s": self.config.ventana_s,
                        "base": base,
                        "umbral": umbral,
                    },
                )
            ]

        return []

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """La alerta se resuelve tras resolver_min bajo el umbral de nodos simultáneos."""
        total_nodos, _ = ctx.bases.actividad_malla_reciente(
            ctx.estado,
            ctx.ahora,
            ventana_s=self.config.ventana_s,
        )

        return not (
            total_nodos < self.config.nodos_minimos
            and (ctx.ahora - abierta.evidencia_en).total_seconds() >= self.config.resolver_min * 60
        )
