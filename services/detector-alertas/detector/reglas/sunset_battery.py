"""Regla sunset-battery: detección de routers solares con batería insuficiente al anochecer."""

from datetime import timedelta
from typing import ClassVar, Literal
from zoneinfo import ZoneInfo

from pydantic import BaseModel

from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar


class ConfigSunsetBattery(BaseModel):
    """Configuración de umbrales para la regla sunset-battery."""

    activa: bool = True
    hora_evaluacion: int = 20  # 20:00 hora peninsular española
    umbral_medio: int = 60
    umbral_alto: int = 40
    resolver: int = 60
    tz: str = "Europe/Madrid"


@registrar
class ReglaSunsetBattery:
    """Supervisa el nivel de batería de los routers solares de Andalucía al inicio de la noche."""

    id: ClassVar[str] = "sunset-battery"
    nombre: ClassVar[str] = "Batería crítica al anochecer"
    descripcion: ClassVar[str] = "Router solar con batería < 60% a las 20:00 h peninsulares sin carga nocturna"
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "mvp"
    afecta_malla: ClassVar[bool] = False
    suscrita_a: ClassVar[frozenset[str]] = frozenset()  # Evaluación en tick
    Config: ClassVar[type[BaseModel]] = ConfigSunsetBattery

    def __init__(self, config: ConfigSunsetBattery | None = None) -> None:
        """Inicializa la regla."""
        self.config = config or ConfigSunsetBattery()

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa a las 20:00 h locales si algún router en Andalucía no tiene suficiente carga para pasar la noche."""
        try:
            tz = ZoneInfo(self.config.tz)
            hora_local = ctx.ahora.astimezone(tz).hour
        except Exception:
            hora_local = ctx.ahora.hour

        # Se evalúa en la ventana de las 20:00 h (20:00 - 20:59)
        if hora_local != self.config.hora_evaluacion:
            return []

        alertas: list[Alerta] = []
        limite_reciente = ctx.ahora - timedelta(hours=3)

        for nodo in ctx.estado.nodes.values():
            # Solo infraestructura (routers/repetidores)
            rol = (nodo.role or "").strip().upper()
            es_infra = ctx.es_infraestructura(nodo.node_id) or rol in ctx.bases.infra_roles
            if not es_infra:
                continue

            # Regla geográfica: Descartar nodos fuera de Andalucía
            if not nodo.dentro_andalucia or nodo.province == "FUERA":
                continue

            if not nodo.battery_samples:
                continue

            ult_ts, ult_bat, ult_volt = nodo.battery_samples[-1]
            if ult_ts < limite_reciente:
                continue

            # Si está alimentado externamente continua (> 100%), no hay riesgo
            if ult_bat > 100:
                continue

            riesgo: str | None = None
            if ult_bat < self.config.umbral_alto:
                riesgo = "alto"
            elif ult_bat < self.config.umbral_medio:
                riesgo = "medio"

            if riesgo is not None:
                nombre_corto = nodo.short or nodo.node_id
                alertas.append(
                    Alerta(
                        regla=self.id,
                        riesgo=riesgo,
                        tipo="infraestructura",
                        mensaje=f"{nombre_corto} llega al anochecer con la batería al {ult_bat} % (riesgo de corte nocturno)",
                        nodo=nodo.node_id,
                        datos={
                            "bateria": ult_bat,
                            "voltaje": round(ult_volt, 2),
                            "umbral_medio": self.config.umbral_medio,
                            "umbral_alto": self.config.umbral_alto,
                            "rol": rol,
                            "provincia": nodo.province,
                            "dentro_andalucia": True,
                        },
                    )
                )

        return alertas

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """Determina si la alerta sigue activa o si la batería se ha recuperado."""
        nodo = ctx.estado.nodes.get(abierta.nodo)
        if nodo is None or not nodo.battery_samples:
            return True

        _, ult_bat, _ = nodo.battery_samples[-1]
        if ult_bat > 100:
            return False

        # Se resuelve si el nivel supera el umbral de resolución o sale el sol y recarga (> 60%)
        return ult_bat <= self.config.resolver
