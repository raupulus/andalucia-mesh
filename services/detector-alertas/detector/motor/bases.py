"""Cálculo estadístico de líneas base dinámicas para nodos, pasarelas y malla."""

import statistics
from datetime import UTC, datetime, timedelta

from detector.motor.estado import EstadoGateway, EstadoMotor, EstadoNodo


class LineasBase:
    """Motor de cálculo de líneas base estadísticas."""

    def __init__(
        self,
        historia_minima_h: int = 24,
        infra_roles: frozenset[str] | None = None,
    ) -> None:
        """Inicializa el calculador de líneas base."""
        self.historia_minima_h = historia_minima_h
        self.infra_roles = infra_roles or frozenset({"ROUTER", "ROUTER_LATE", "REPEATER"})

    def tiene_historia_minima(self, primer_visto: datetime, ahora: datetime) -> bool:
        """Comprueba si un nodo o entidad acumula el mínimo de horas de historia requeridas."""
        delta = ahora - primer_visto
        return delta.total_seconds() >= self.historia_minima_h * 3600

    def intervalo_tipico_nodo(self, nodo: EstadoNodo, ahora: datetime) -> float | None:
        """Calcula la mediana de los últimos intervalos propios entre paquetes (en segundos)."""
        if not self.tiene_historia_minima(nodo.first_seen, ahora):
            return None
        if len(nodo.intervals) < 3:
            return None
        return float(statistics.median(nodo.intervals))

    def ritmo_tipico_nodo(self, nodo: EstadoNodo, ahora: datetime) -> float | None:
        """Calcula el ritmo típico basal en paquetes por 10 minutos a partir de 168 horas."""
        if not self.tiene_historia_minima(nodo.first_seen, ahora):
            return None
        conteos_activos = [cnt for cnt in nodo.hourly_packets.values() if cnt > 0]
        if len(conteos_activos) < 3:
            return None
        # Mediana horaria dividida por 6 equivale al ritmo esperado por bloque de 10 min
        mediana_horaria = statistics.median(conteos_activos)
        return float(mediana_horaria / 6.0)

    def intervalo_tipico_gateway(self, gw: EstadoGateway, ahora: datetime) -> float | None:
        """Calcula la mediana de los intervalos entre recepciones del gateway (en segundos)."""
        if len(gw.reception_intervals) < 3:
            return None
        return float(statistics.median(gw.reception_intervals))

    def actividad_malla_reciente(
        self,
        estado: EstadoMotor,
        ahora: datetime,
        ventana_s: int = 120,
    ) -> tuple[int, list[str]]:
        """Devuelve el total de nodos únicos observados en los últimos ventana_s segundos y sus IDs."""
        limite = ahora - timedelta(seconds=ventana_s)
        nodos_unicos = {node_id for ts, node_id in estado.mesh_recent_nodes if ts >= limite}
        return len(nodos_unicos), list(nodos_unicos)

    def saturacion_provincial(
        self,
        estado: EstadoMotor,
        provincia: str,
        peso_routers: float = 0.6,
        peso_clientes: float = 0.4,
        ahora: datetime | None = None,
    ) -> float:
        """Calcula la saturación ponderada de la provincia (0.6 routers + 0.4 clientes).

        Regla de negocio DT-13: CLIENT_MUTE no cuenta en el cómputo.
        """
        ref_time = ahora or datetime.now(UTC)
        limite_12h = ref_time - timedelta(hours=12)

        routers_chutil: list[float] = []
        clientes_chutil: list[float] = []

        for nodo in estado.nodes.values():
            if nodo.province != provincia:
                continue
            if nodo.last_chutil_at is None or nodo.last_chutil_at < limite_12h:
                continue
            if nodo.last_channel_utilization is None:
                continue

            rol = (nodo.role or "").strip().upper()
            if rol in self.infra_roles:
                routers_chutil.append(nodo.last_channel_utilization)
            elif rol in ("CLIENT", "CLIENT_BASE"):
                clientes_chutil.append(nodo.last_channel_utilization)
            # CLIENT_MUTE se omite deliberadamente

        avg_routers = statistics.mean(routers_chutil) if routers_chutil else 0.0
        avg_clientes = statistics.mean(clientes_chutil) if clientes_chutil else 0.0

        if routers_chutil and clientes_chutil:
            return peso_routers * avg_routers + peso_clientes * avg_clientes
        if routers_chutil:
            return avg_routers
        if clientes_chutil:
            return avg_clientes
        return 0.0
