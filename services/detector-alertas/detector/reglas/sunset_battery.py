"""Regla sunset-battery: detección de routers solares con batería insuficiente al anochecer."""

from datetime import timedelta
from typing import ClassVar, Literal
from zoneinfo import ZoneInfo

from pydantic import BaseModel

from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar
from detector.motor.umbrales import GestorUmbrales


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

        gestor = ctx.umbrales or GestorUmbrales()
        umbrales_rtr = gestor.obtener_grupo("ROUTER")

        if ctx.umbrales is not None:
            min_sunset = umbrales_rtr.sunset_battery_min_pct
            if min_sunset <= 0:
                return []
            umbral_medio = min_sunset
            umbral_alto = min(umbrales_rtr.battery_critical_pct, min_sunset)
        else:
            umbral_medio = float(self.config.umbral_medio)
            umbral_alto = float(self.config.umbral_alto)

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
            if ult_bat < umbral_alto:
                riesgo = "alto"
            elif ult_bat < umbral_medio:
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
                            "umbral_medio": round(umbral_medio, 1),
                            "umbral_alto": round(umbral_alto, 1),
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

        if ctx.umbrales is not None:
            gestor = ctx.umbrales
            umbrales_rtr = gestor.obtener_grupo("ROUTER")
            umbral_resolucion = (
                umbrales_rtr.sunset_battery_min_pct
                if umbrales_rtr.sunset_battery_min_pct > 0
                else float(self.config.resolver)
            )
        else:
            umbral_resolucion = float(self.config.resolver)

        # Se resuelve si el nivel supera el umbral de resolución o sale el sol y recarga (> 60%)
        return ult_bat <= umbral_resolucion
