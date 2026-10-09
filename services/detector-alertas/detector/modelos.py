"""Modelos de datos para el flujo decoded y contratos internos del Detector."""

from datetime import datetime
from typing import Any

from pydantic import BaseModel, ConfigDict, Field


class FromNodeInfo(BaseModel):
    """Información del nodo emisor agregada por el servicio de ingesta."""

    model_config = ConfigDict(extra="ignore")

    short: str | None = None
    long: str | None = None
    role: str | None = None
    hw: str | None = None
    firmware: str | None = None
    is_gateway: bool = False
    province: str | None = None


class RecepcionGateway(BaseModel):
    """Metadatos de recepción de un gateway particular para un paquete."""

    model_config = ConfigDict(extra="ignore")

    gateway: str
    snr: float | None = None
    rssi: float | None = None
    hops: int | None = None
    at: datetime | str | None = None


class DeviceMetrics(BaseModel):
    """Métricas de telemetría de dispositivo Meshtastic."""

    model_config = ConfigDict(extra="ignore")

    battery_level: int | None = None
    voltage: float | None = None
    channel_utilization: float | None = None
    air_util_tx: float | None = None
    uptime_seconds: int | None = None


class LocalStats(BaseModel):
    """Estadísticas operativas locales de nodo."""

    model_config = ConfigDict(extra="ignore")

    uptime_seconds: int | None = None
    channel_utilization: float | None = None
    air_util_tx: float | None = None
    num_packets_tx: int | None = None
    num_packets_rx: int | None = None
    num_tx_dropped: int | None = None
    noise_floor: float | None = None


class PaquetePayload(BaseModel):
    """Carga útil decodificada del paquete."""

    model_config = ConfigDict(extra="allow")

    device_metrics: DeviceMetrics | None = None
    local_stats: LocalStats | None = None
    latitude_i: int | None = None
    longitude_i: int | None = None
    public_key: str | None = None
    text: str | None = None


class PaqueteDecodificado(BaseModel):
    """Estructura de paquete JSON publicado en snm/v1/decoded/#."""

    model_config = ConfigDict(populate_by_name=True, extra="ignore")

    v: int = 1
    packet_id: int
    from_node_id: str = Field(validation_alias="from", serialization_alias="from")
    from_node: FromNodeInfo | None = None
    to: str | None = None
    portnum: str
    channel: str | None = None
    rx_first: datetime | str
    hop_start: int | None = None
    hops_min: int | None = None
    via_mqtt: bool = False
    ok_to_mqtt: bool | None = None
    airtime_ms: float | None = None
    receptions: list[RecepcionGateway] = Field(default_factory=list)
    payload: dict[str, Any] = Field(default_factory=dict)

    @property
    def parsed_rx_first(self) -> datetime:
        """Devuelve la fecha de primera recepción parseada como objeto datetime en UTC."""
        if isinstance(self.rx_first, datetime):
            return self.rx_first
        try:
            return datetime.fromisoformat(self.rx_first.replace("Z", "+00:00"))
        except Exception:
            return datetime.now()


class NodoInfoResumen(BaseModel):
    """Resumen de nodo serializado en la alerta."""

    model_config = ConfigDict(extra="ignore")

    corto: str | None = None
    largo: str | None = None
    rol: str | None = None
    provincia: str | None = None


class AlertaObjeto(BaseModel):
    """Representación completa del objeto alerta emitido por el socket y guardado en DB."""

    model_config = ConfigDict(extra="ignore")

    id: str
    regla: str
    riesgo: str
    tipo: str
    mensaje: str
    nodo: str
    nodos: list[str] = Field(default_factory=list)
    nodo_info: NodoInfoResumen | None = None
    datos: dict[str, Any] = Field(default_factory=dict)
    estado: str  # 'abierta' | 'resuelta'
    abierta_en: str
    actualizada_en: str
    resuelta_en: str | None = None


class TransicionMensaje(BaseModel):
    """Mensaje NDJSON de transición emitido por el socket UNIX."""

    v: int = 1
    transicion_id: str
    transicion: str  # 'abierta' | 'actualizada' | 'resuelta'
    alerta: AlertaObjeto
