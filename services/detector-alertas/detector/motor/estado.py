"""Modelo de estado en memoria para nodos, pasarelas y métricas de malla."""

from collections import deque
from dataclasses import dataclass, field
from datetime import UTC, datetime, timedelta

from detector.modelos import PaqueteDecodificado


ANDALUCIA_PROVINCES = frozenset({
    "ES-AL",  # Almería
    "ES-CA",  # Cádiz
    "ES-CO",  # Córdoba
    "ES-GR",  # Granada
    "ES-H",   # Huelva
    "ES-J",   # Jaén
    "ES-MA",  # Málaga
    "ES-SE",  # Sevilla
})


@dataclass
class EstadoNodo:
    """Estado y series temporales operativas de un nodo individual."""

    node_id: str
    short: str | None = None
    long: str | None = None
    role: str | None = None
    hw: str | None = None
    is_gateway: bool = False
    province: str | None = None

    first_seen: datetime = field(default_factory=lambda: datetime.now(UTC))
    last_seen: datetime = field(default_factory=lambda: datetime.now(UTC))

    @property
    def dentro_andalucia(self) -> bool:
        """Indica si el nodo está geolocalizado dentro de Andalucía."""
        return bool(self.province and self.province.strip().upper() in ANDALUCIA_PROVINCES)

    # Reinicios y actividad de telemetría
    last_uptime_seconds: int | None = None
    reboots_24h: deque[datetime] = field(default_factory=lambda: deque(maxlen=100))
    nodeinfo_timestamps_1h: deque[datetime] = field(default_factory=lambda: deque(maxlen=100))

    # Batería: tuplas de (timestamp, battery_level, voltage)
    battery_samples: deque[tuple[datetime, int, float]] = field(default_factory=lambda: deque(maxlen=80))

    # Paquetes y ritmo de emisión
    packet_timestamps_10m: deque[datetime] = field(default_factory=lambda: deque(maxlen=500))
    hourly_packets: dict[int, int] = field(default_factory=dict)  # epoch_hour -> count (últimas 168 h)

    # Series especializadas para reglas de anomalías
    text_messages_10m: deque[datetime] = field(default_factory=lambda: deque(maxlen=100))
    telemetry_emissions_1h: deque[tuple[datetime, str]] = field(default_factory=lambda: deque(maxlen=500))
    traceroute_timestamps_1h: deque[datetime] = field(default_factory=lambda: deque(maxlen=100))
    broadcast_polls_1h: deque[tuple[datetime, str]] = field(default_factory=lambda: deque(maxlen=100))
    private_chaff_1h: deque[datetime] = field(default_factory=lambda: deque(maxlen=200))
    position_timestamps_1h: deque[datetime] = field(default_factory=lambda: deque(maxlen=200))

    # Intervalos propios entre emisiones sucesivas (últimos 50 intervalos en segundos)
    intervals: deque[float] = field(default_factory=lambda: deque(maxlen=50))
    _last_packet_time: datetime | None = None

    # Saltos de origen (hop_start)
    hop_starts: deque[int] = field(default_factory=lambda: deque(maxlen=20))

    # Emisiones a ^all (timestamp, portnum)
    all_emissions: deque[tuple[datetime, str]] = field(default_factory=lambda: deque(maxlen=20))

    # Ocupación de canal reportada
    last_channel_utilization: float | None = None
    last_chutil_at: datetime | None = None

    def registrar_paquete(self, pkt: PaqueteDecodificado, ahora: datetime) -> None:
        """Actualiza el estado del nodo tras la llegada de un paquete propio."""
        self.last_seen = ahora

        # Intervalo entre paquetes propios
        if self._last_packet_time is not None:
            delta_s = (ahora - self._last_packet_time).total_seconds()
            if delta_s > 0:
                self.intervals.append(delta_s)
        self._last_packet_time = ahora

        # Metadatos de nodo
        if pkt.from_node:
            if pkt.from_node.short:
                self.short = pkt.from_node.short
            if pkt.from_node.long:
                self.long = pkt.from_node.long
            if pkt.from_node.role:
                self.role = pkt.from_node.role
            if pkt.from_node.hw:
                self.hw = pkt.from_node.hw
            if pkt.from_node.is_gateway:
                self.is_gateway = True
            if pkt.from_node.province:
                self.province = pkt.from_node.province

        # Hop start
        if pkt.hop_start is not None and pkt.hop_start > 0:
            self.hop_starts.append(pkt.hop_start)

        # Emisión a broadcast (^all) y detección de sondeos indiscriminados
        payload = pkt.payload or {}
        es_broadcast = pkt.to in ("^all", "ffffffff", "*")
        if es_broadcast:
            self.all_emissions.append((ahora, pkt.portnum))
            # Identificar si es un sondeo o petición broadcast
            es_sondeo = bool(
                payload.get("request_id")
                or payload.get("want_response")
                or pkt.portnum in ("traceroute", "routing")
                or (pkt.portnum == "telemetry" and not any(k in payload for k in ("device_metrics", "environment_metrics", "power_metrics", "air_quality_metrics", "local_stats")))
            )
            if es_sondeo:
                self.broadcast_polls_1h.append((ahora, pkt.portnum))

        # Registro por tipo de aplicación (portnum)
        if pkt.portnum == "text":
            self.text_messages_10m.append(ahora)
        elif pkt.portnum == "position":
            self.position_timestamps_1h.append(ahora)
            self.telemetry_emissions_1h.append((ahora, "position"))
        elif pkt.portnum == "nodeinfo":
            self.nodeinfo_timestamps_1h.append(ahora)
            self.telemetry_emissions_1h.append((ahora, "nodeinfo"))
        elif pkt.portnum == "telemetry":
            variant = "device_metrics"
            if "environment_metrics" in payload:
                variant = "environment_metrics"
            elif "power_metrics" in payload:
                variant = "power_metrics"
            elif "air_quality_metrics" in payload:
                variant = "air_quality_metrics"
            elif "local_stats" in payload:
                variant = "local_stats"
            self.telemetry_emissions_1h.append((ahora, variant))
        elif pkt.portnum == "traceroute":
            self.traceroute_timestamps_1h.append(ahora)
        elif pkt.portnum == "other":
            self.private_chaff_1h.append(ahora)

        # Ritmo de paquetes (10 min y horario)
        self.packet_timestamps_10m.append(ahora)
        epoch_hour = int(ahora.timestamp() // 3600)
        self.hourly_packets[epoch_hour] = self.hourly_packets.get(epoch_hour, 0) + 1

        # Limpiar horas antiguas (> 168 horas = 7 días)
        cutoff_hour = epoch_hour - 168
        for h in list(self.hourly_packets.keys()):
            if h < cutoff_hour:
                del self.hourly_packets[h]

        # Procesar telemetría
        dm = payload.get("device_metrics") or {}
        ls = payload.get("local_stats") or {}

        uptime = dm.get("uptime_seconds") or ls.get("uptime_seconds")
        if uptime is not None:
            self._procesar_uptime(int(uptime), ahora)

        bat = dm.get("battery_level")
        volt = dm.get("voltage")
        if bat is not None and volt is not None:
            self._procesar_bateria(int(bat), float(volt), ahora)

        chutil = dm.get("channel_utilization") or ls.get("channel_utilization")
        if chutil is not None:
            self.last_channel_utilization = float(chutil)
            self.last_chutil_at = ahora

    def _procesar_uptime(self, uptime: int, ahora: datetime) -> None:
        """Detecta reinicios mediante caídas de uptime_seconds o arranques menores a 180s."""
        if self.last_uptime_seconds is not None:
            # Si el uptime cae abruptamente, es un reinicio
            if uptime < self.last_uptime_seconds or uptime < 180 and (not self.reboots_24h or (ahora - self.reboots_24h[-1]).total_seconds() > 180):
                self.reboots_24h.append(ahora)
        elif uptime < 180:
            # Primera muestra recibida con uptime de arranque reciente
            self.reboots_24h.append(ahora)

        self.last_uptime_seconds = uptime

        # Purgar reinicios con más de 24 horas
        limite_24h = ahora - timedelta(hours=24)
        while self.reboots_24h and self.reboots_24h[0] < limite_24h:
            self.reboots_24h.popleft()

    def _procesar_bateria(self, bat: int, volt: float, ahora: datetime) -> None:
        """Almacena muestras de nivel de batería y voltaje."""
        # Se ignoran lecturas inválidas (sensor no presente)
        if bat == 0 and volt == 0.0:
            return
        self.battery_samples.append((ahora, bat, volt))


@dataclass
class EstadoGateway:
    """Estado y periodicidad de publicación de un gateway en MQTT."""

    gateway_id: str
    last_seen_at: datetime = field(default_factory=lambda: datetime.now(UTC))
    reception_intervals: deque[float] = field(default_factory=lambda: deque(maxlen=50))
    _last_reception_time: datetime | None = None

    def registrar_recepcion(self, rx_at: datetime) -> None:
        """Registra la recepción de un paquete subido por este gateway."""
        self.last_seen_at = rx_at
        if self._last_reception_time is not None:
            delta_s = (rx_at - self._last_reception_time).total_seconds()
            if delta_s > 0:
                self.reception_intervals.append(delta_s)
        self._last_reception_time = rx_at


class EstadoMotor:
    """Almacén global en memoria de todos los nodos, pasarelas y actividad de red."""

    def __init__(self, started_at: datetime | None = None) -> None:
        """Inicializa el estado del motor."""
        self.started_at = started_at or datetime.now(UTC)
        self.nodes: dict[str, EstadoNodo] = {}
        self.gateways: dict[str, EstadoGateway] = {}
        self.known_ids_90d: dict[str, datetime] = {}
        # Muestras de nodos únicos en ventana de 2 minutos para actividad de malla
        self.mesh_recent_nodes: deque[tuple[datetime, str]] = deque(maxlen=10000)

    def obtener_o_crear_nodo(self, node_id: str, ahora: datetime) -> EstadoNodo:
        """Obtiene el estado de un nodo o lo inicializa si es nuevo."""
        if node_id not in self.nodes:
            self.nodes[node_id] = EstadoNodo(node_id=node_id, first_seen=ahora, last_seen=ahora)
            self.known_ids_90d[node_id] = ahora
        return self.nodes[node_id]

    def obtener_o_crear_gateway(self, gw_id: str, ahora: datetime) -> EstadoGateway:
        """Obtiene el estado de un gateway o lo inicializa si es nuevo."""
        if gw_id not in self.gateways:
            self.gateways[gw_id] = EstadoGateway(gateway_id=gw_id, last_seen_at=ahora)
        return self.gateways[gw_id]

    def registrar_paquete(self, pkt: PaqueteDecodificado, ahora: datetime | None = None) -> None:
        """Actualiza el estado completo del motor con un paquete decodificado."""
        current_time = ahora or pkt.parsed_rx_first

        # 1. Nodo emisor
        nodo = self.obtener_o_crear_nodo(pkt.from_node_id, current_time)
        nodo.registrar_paquete(pkt, current_time)
        self.known_ids_90d[pkt.from_node_id] = current_time

        # 2. Gateways receptores
        for rx in pkt.receptions:
            gw = self.obtener_o_crear_gateway(rx.gateway, current_time)
            gw_rx_at = current_time
            if rx.at:
                try:
                    if isinstance(rx.at, datetime):
                        gw_rx_at = rx.at
                    else:
                        gw_rx_at = datetime.fromisoformat(str(rx.at).replace("Z", "+00:00"))
                except Exception:
                    gw_rx_at = current_time
            gw.registrar_recepcion(gw_rx_at)

        # 3. Registro para cálculo de ráfagas simultáneas de malla
        self.mesh_recent_nodes.append((current_time, pkt.from_node_id))

    def purgar_obsoletos(self, ahora: datetime, dias: int = 30) -> int:
        """Elimina nodos inactivos durante más de 'dias' días. Devuelve el número de eliminados."""
        limite = ahora - timedelta(days=dias)
        a_borrar = [node_id for node_id, nodo in self.nodes.items() if nodo.last_seen < limite]
        for node_id in a_borrar:
            del self.nodes[node_id]

        # Purgar IDs conocidos > 90 días
        limite_90d = ahora - timedelta(days=90)
        a_borrar_90d = [node_id for node_id, dt in self.known_ids_90d.items() if dt < limite_90d]
        for node_id in a_borrar_90d:
            del self.known_ids_90d[node_id]

        return len(a_borrar)
