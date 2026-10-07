"""Detección de reinicios de nodos a partir de métricas de telemetría de dispositivo.

Compara el tiempo de actividad informado (uptime_seconds) con el valor
teórico esperado en función del intervalo transcurrido desde la última lectura.
"""

from __future__ import annotations


def detect_node_reboot(
    current_uptime: int | None,
    current_time_s: float,
    previous_uptime: int | None,
    previous_time_s: float | None,
) -> bool:
    """Evalúa si un nodo ha sufrido un reinicio entre dos lecturas de telemetría.

    Fórmula:
    esperado = previous_uptime + (current_time_s - previous_time_s)
    tolerancia = max(300 s, 0.05 * intervalo_transcurrido)
    reboot = current_uptime < esperado - tolerancia

    Args:
        current_uptime: Tiempo de actividad informado en segundos.
        current_time_s: Marca temporal actual en segundos epoch.
        previous_uptime: Último tiempo de actividad conocido en segundos.
        previous_time_s: Marca temporal de la lectura previa en segundos epoch.

    Returns:
        True si se detectó una caída no explicable por deriva de reloj (reinicio).
    """
    if current_uptime is None or current_uptime <= 0:
        return False
    if previous_uptime is None or previous_time_s is None:
        return False

    elapsed = current_time_s - previous_time_s
    if elapsed <= 0:
        return False

    expected_uptime = previous_uptime + elapsed
    tolerance = max(300.0, 0.05 * elapsed)

    if current_uptime < (expected_uptime - tolerance):
        return True

    return False
