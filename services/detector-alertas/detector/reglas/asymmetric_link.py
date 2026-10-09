"""Regla asymmetric-link: detección de asimetría severa en enlaces de radiofrecuencia."""

from datetime import timedelta
from typing import ClassVar, Literal

from pydantic import BaseModel

from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar

# Roles permitidos para evaluación de asimetría. CLIENT_MUTE se excluye tajantemente
# porque es habitual y esperado que sufra asimetría por desplazamientos dentro de interiores.
ROLES_PERMITIDOS = frozenset({"CLIENT", "CLIENT_BASE", "ROUTER", "REPEATER"})


class ConfigAsymmetricLink(BaseModel):
    """Configuración para la regla asymmetric-link."""

    activa: bool = True
    delta_snr_db: float = 6.0
    ventana_h: int = 24
    resolver_db: float = 4.0


@registrar
class ReglaAsymmetricLink:
    """Supervisa enlaces de radiofrecuencia directos con discrepancias de SNR superiores a 6 dB."""

    id: ClassVar[str] = "asymmetric-link"
    nombre: ClassVar[str] = "Enlace RF asimétrico"
    descripcion: ClassVar[str] = (
        "Diferencia de SNR > 6 dB en ambos sentidos entre CLIENT, CLIENT_BASE y ROUTER"
    )
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "ampliacion"
    afecta_malla: ClassVar[bool] = True
    suscrita_a: ClassVar[frozenset[str]] = frozenset({"routing", "all"})
    Config: ClassVar[type[BaseModel]] = ConfigAsymmetricLink

    def __init__(self, config: ConfigAsymmetricLink | None = None) -> None:
        """Inicializa la regla con su configuración."""
        self.config = config or ConfigAsymmetricLink()

    def _es_rol_valido(self, ctx: Contexto, node_id: str) -> bool:
        """Comprueba si el nodo posee un rol autorizado (no CLIENT_MUTE ni desconocido)."""
        nodo = ctx.estado.nodes.get(node_id)
        if nodo is None or not nodo.role:
            return False
        rol = nodo.role.strip().upper()
        if rol == "CLIENT_MUTE":
            return False
        return rol in ROLES_PERMITIDOS

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa si los enlaces bidireccionales del nodo emisor presentan una asimetría mayor a 6 dB."""
        if ctx.nodo is None:
            return []

        nodo_a = ctx.nodo.node_id
        if not self._es_rol_valido(ctx, nodo_a):
            return []

        alertas: list[Alerta] = []
        limite = ctx.ahora - timedelta(hours=self.config.ventana_h)
        pares_evaluados: set[str] = set()

        # Buscar enlaces bidireccionales en el grafo RF
        for (src, dst), (ts_ab, snr_ab) in ctx.estado.rf_links.items():
            if ts_ab < limite or (src != nodo_a and dst != nodo_a):
                continue

            otro_id = dst if src == nodo_a else src
            if otro_id in pares_evaluados:
                continue
            pares_evaluados.add(otro_id)

            if not self._es_rol_valido(ctx, otro_id):
                continue

            # Comprobar si existe la observación en sentido contrario
            enlace_vuelta = ctx.estado.rf_links.get((dst, src))
            if enlace_vuelta is None or enlace_vuelta[0] < limite:
                continue

            snr_ba = enlace_vuelta[1]
            delta = abs(snr_ab - snr_ba)

            if delta > self.config.delta_snr_db:
                # El sujeto de la alerta es el extremo que oye peor (menor SNR recibido)
                # Enlace (src -> dst): dst recibe con SNR snr_ab
                # Enlace (dst -> src): src recibe con SNR snr_ba
                if snr_ab < snr_ba:
                    sujeto_id = dst
                    otro_extremo = src
                    snr_peor = snr_ab
                    snr_mejor = snr_ba
                else:
                    sujeto_id = src
                    otro_extremo = dst
                    snr_peor = snr_ba
                    snr_mejor = snr_ab

                # Solo emitir la alerta si el sujeto es el nodo evaluado en este paquete
                if sujeto_id != nodo_a:
                    continue

                nombre_corto = ctx.nodo.short or ctx.nodo.node_id
                vecino_obj = ctx.estado.nodes.get(otro_extremo)
                otro_nombre = (vecino_obj.short if vecino_obj else None) or otro_extremo

                alertas.append(
                    Alerta(
                        regla=self.id,
                        riesgo="medio",
                        mensaje=(
                            f"Enlace asimétrico en {nombre_corto} (Δ > {self.config.delta_snr_db:g} dB): "
                            f"oye a {otro_nombre} a {round(snr_peor, 1)} dB pero él le oye a {round(snr_mejor, 1)} dB"
                        ),
                        nodo=sujeto_id,
                        datos={
                            "delta_db": round(delta, 1),
                            "snr_peor": round(snr_peor, 1),
                            "snr_mejor": round(snr_mejor, 1),
                            "otro_nodo": otro_extremo,
                            "umbral_delta": self.config.delta_snr_db,
                        },
                        tipo="infraestructura",
                    )
                )

        return alertas

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """Determina si la alerta sigue activa evaluando si la asimetría se ha reducido."""
        otro_id = abierta.datos.get("otro_nodo")
        if not otro_id or not isinstance(otro_id, str):
            return False

        limite = ctx.ahora - timedelta(hours=self.config.ventana_h)
        ab = ctx.estado.rf_links.get((abierta.nodo, otro_id))
        ba = ctx.estado.rf_links.get((otro_id, abierta.nodo))

        if ab is None or ba is None or ab[0] < limite or ba[0] < limite:
            return (ctx.ahora - abierta.actualizada_en).total_seconds() <= (self.config.ventana_h * 3600)

        delta = abs(ab[1] - ba[1])
        if delta <= self.config.resolver_db:
            return False

        return (ctx.ahora - abierta.actualizada_en).total_seconds() <= (self.config.ventana_h * 3600)
