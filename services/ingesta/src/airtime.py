"""Cálculo analítico del tiempo en el aire (airtime_ms) para LoRa SFNarrow.

Implementa la fórmula estándar de la nota de aplicación Semtech AN1200.13
con los parámetros del preset SFNarrow (BW=62.5 kHz, SF=7, CR=4/5, n_pre=16).
"""

from __future__ import annotations

import math
from typing import Final


def calculate_airtime_ms(
    payload_len: int,
    spread_factor: int = 7,
    bandwidth_khz: float = 62.5,
    coding_rate_code: int = 5,
    preamble_symbols: int = 16,
    is_map_report: bool = False,
) -> float:
    """Calcula el tiempo de transmisión en el aire en milisegundos.

    Args:
        payload_len: Longitud en bytes de la carga útil del paquete.
        spread_factor: Factor de dispersión LoRa (SF7 por defecto).
        bandwidth_khz: Ancho de banda en kHz (62.5 kHz por defecto).
        coding_rate_code: Código de tasa de codificación (5 = 4/5).
        preamble_symbols: Número de símbolos del preámbulo (16 por defecto).
        is_map_report: Si es un MapReport de gateway (no consume radio -> 0.0 ms).

    Returns:
        Tiempo en el aire en milisegundos redondeado a 1 decimal.
    """
    if is_map_report:
        return 0.0

    # Conversión de código de ancho de banda a Hertz
    if bandwidth_khz == 62:
        bw_hz = 62500.0
    elif bandwidth_khz == 31:
        bw_hz = 31250.0
    else:
        bw_hz = bandwidth_khz * 1000.0

    # Duración del símbolo en segundos
    t_sym = (2**spread_factor) / bw_hz

    # Duración del preámbulo en segundos
    t_pre = (preamble_symbols + 4.25) * t_sym

    # Longitud total de paquete físico: 16 bytes de cabecera LoRa + carga útil
    pl = 16 + payload_len

    crc: Final[int] = 1  # CRC siempre activado
    ih: Final[int] = 0  # Cabecera explícita (IH = 0)
    de = 1 if t_sym > 0.016 else 0  # Optimización de baja tasa (Data Optimization)
    cr = coding_rate_code - 4  # Para código 5 (4/5), cr = 1

    term = (8 * pl - 4 * spread_factor + 28 + 16 * crc - 20 * ih) / (
        4 * (spread_factor - 2 * de)
    )
    n_payload = 8 + max(math.ceil(term) * (cr + 4), 0)

    total_seconds = t_pre + n_payload * t_sym
    return round(total_seconds * 1000.0, 1)
