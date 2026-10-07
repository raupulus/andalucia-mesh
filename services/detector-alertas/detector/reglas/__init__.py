"""Paquete de reglas de supervisión del Detector de Alertas."""

from detector.reglas.battery_low import ReglaBatteryLow
from detector.reglas.flood import ReglaFlood
from detector.reglas.gateway_offline import ReglaGatewayOffline
from detector.reglas.hops_high import ReglaHopsHigh
from detector.reglas.infra_silent import ReglaInfraSilent
from detector.reglas.rafaga_masiva import ReglaRafagaMasiva
from detector.reglas.reboot_loop import ReglaRebootLoop

__all__ = [
    "ReglaBatteryLow",
    "ReglaFlood",
    "ReglaGatewayOffline",
    "ReglaHopsHigh",
    "ReglaInfraSilent",
    "ReglaRafagaMasiva",
    "ReglaRebootLoop",
]
