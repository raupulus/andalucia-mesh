"""Paquete de reglas de supervisión del Detector de Alertas."""

from detector.reglas.battery_low import ReglaBatteryLow
from detector.reglas.chutil_high import ReglaChutilHigh
from detector.reglas.flood import ReglaFlood
from detector.reglas.gateway_offline import ReglaGatewayOffline
from detector.reglas.hops_high import ReglaHopsHigh
from detector.reglas.infra_silent import ReglaInfraSilent
from detector.reglas.poll_abuse import ReglaPollAbuse
from detector.reglas.position_flood import ReglaPositionFlood
from detector.reglas.private_chaff import ReglaPrivateChaff
from detector.reglas.rafaga_masiva import ReglaRafagaMasiva
from detector.reglas.reboot_loop import ReglaRebootLoop
from detector.reglas.router_role import ReglaRouterRole
from detector.reglas.sunset_battery import ReglaSunsetBattery
from detector.reglas.telemetry_burst import ReglaTelemetryBurst
from detector.reglas.text_flood import ReglaTextFlood

__all__ = [
    "ReglaBatteryLow",
    "ReglaChutilHigh",
    "ReglaFlood",
    "ReglaGatewayOffline",
    "ReglaHopsHigh",
    "ReglaInfraSilent",
    "ReglaPollAbuse",
    "ReglaPositionFlood",
    "ReglaPrivateChaff",
    "ReglaRafagaMasiva",
    "ReglaRebootLoop",
    "ReglaRouterRole",
    "ReglaSunsetBattery",
    "ReglaTelemetryBurst",
    "ReglaTextFlood",
]
