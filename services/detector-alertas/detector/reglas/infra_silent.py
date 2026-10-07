"""Regla infra-silent: detección de routers de infraestructura inactivos."""

from typing import ClassVar, Literal

from pydantic import BaseModel, Field

from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar


class ConfigMedioInfraSilent(BaseModel):
    """Parámetros de umbral medio para infra-silent."""

    minimo_h: float = 6.0
    factor_intervalo: float = 3.0


class ConfigInfraSilent(BaseModel):
    """Configuración para la regla infra-silent."""

    activa: bool = True
    medio: ConfigMedioInfraSilent = Field(default_factory=ConfigMedioInfraSilent)
    alto_h: float = 24.0


@registrar
class ReglaInfraSilent:
    """Detecta routers que dejan de emitir paquetes en base a su periodicidad habitual."""

    id: ClassVar[str] = "infra-silent"
    nombre: ClassVar[str] = "Router silente"
    descripcion: ClassVar[str] = "Router de infraestructura que deja de oírse según su intervalo típico"
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "mvp"
    afecta_malla: ClassVar[bool] = False
    suscrita_a: ClassVar[frozenset[str]] = frozenset()  # Evaluación en tick
    Config: ClassVar[type[BaseModel]] = ConfigInfraSilent

    def __init__(self, config: ConfigInfraSilent | None = None) -> None:
        """Inicializa la regla."""
        self.config = config or ConfigInfraSilent()

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa todos los routers registrados y genera alertas si superan los umbrales de silencio."""
        alertas: list[Alerta] = []

        for nodo in ctx.estado.nodes.values():
            rol = (nodo.role or "").strip().upper()
            if rol not in ctx.bases.infra_roles:
                continue

            # El silencio se computa desde max(último visto, arranque del detector)
            inicio_silencio = max(nodo.last_seen, ctx.estado.started_at)
            silencio_s = (ctx.ahora - inicio_silencio).total_seconds()
            silencio_h = silencio_s / 3600.0

            intervalo_s = ctx.bases.intervalo_tipico_nodo(nodo, ctx.ahora)
            if intervalo_s is not None:
                umbral_medio_h = max(
                    self.config.medio.minimo_h,
                    (self.config.medio.factor_intervalo * intervalo_s) / 3600.0,
                )
            else:
                umbral_medio_h = self.config.medio.minimo_h

            riesgo: str | None = None
            umbral_aplicado: float = 0.0

            if silencio_h >= self.config.alto_h:
                riesgo = "alto"
                umbral_aplicado = self.config.alto_h
            elif silencio_h >= umbral_medio_h:
                riesgo = "medio"
                umbral_aplicado = umbral_medio_h

            if riesgo is not None:
                nombre_corto = nodo.short or nodo.node_id
                alertas.append(
                    Alerta(
                        regla=self.id,
                        riesgo=riesgo,
                        mensaje=f"{nombre_corto} no se oye desde hace {round(silencio_h, 1)} h",
                        nodo=nodo.node_id,
                        datos={
                            "ultimo_visto": nodo.last_seen.isoformat(),
                            "silencio_h": round(silencio_h, 1),
                            "intervalo_tipico_s": round(intervalo_s, 1) if intervalo_s else None,
                            "umbral_h": round(umbral_aplicado, 1),
                        },
                    )
                )

        return alertas

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """La alerta se resuelve en cuanto el router vuelve a emitir un paquete propio."""
        nodo = ctx.estado.nodes.get(abierta.nodo)
        if nodo is None:
            return False
        # Si el último paquete visto es posterior a cuando se abrió la alerta, se resuelve
        return nodo.last_seen <= abierta.abierta_en
