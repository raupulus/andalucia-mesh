"""Regla client-base-fw: detección de nodos con rol CLIENT_BASE y firmware >= 2.7.17.

A partir del firmware 2.7.17 de Meshtastic, el rol CLIENT_BASE se comporta como ROUTER_LATE:
el nodo introduce una espera forzada antes de reemitir paquetes, lo que ralentiza
significativamente la propagación de mensajes en la red mallada.
Se recomienda utilizar el rol CLIENT estándar o CLIENT_MUTE si el nodo no necesita enrutar.
"""

from __future__ import annotations

import re
from typing import ClassVar, Literal

from pydantic import BaseModel

from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar


class ConfigClientBaseFw(BaseModel):
    """Configuración de parámetros para la regla client-base-fw."""

    activa: bool = True
    version_minima: str = "2.7.17"


@registrar
class ReglaClientBaseFw:
    """Supervisa nodos con rol CLIENT_BASE que tengan versión de firmware >= 2.7.17."""

    id: ClassVar[str] = "client-base-fw"
    nombre: ClassVar[str] = "CLIENT_BASE en firmware >= 2.7.17"
    descripcion: ClassVar[str] = (
        "El rol CLIENT_BASE actúa como ROUTER_LATE a partir de firmware 2.7.17, "
        "retrasando la propagación de paquetes en la malla."
    )
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "mvp"
    afecta_malla: ClassVar[bool] = False
    suscrita_a: ClassVar[frozenset[str]] = frozenset({"nodeinfo", "telemetry"})
    Config: ClassVar[type[BaseModel]] = ConfigClientBaseFw

    def __init__(self, config: ConfigClientBaseFw | None = None) -> None:
        """Inicializa la regla con su configuración de umbrales."""
        self.config = config or ConfigClientBaseFw()

    @staticmethod
    def parse_version(v_str: str | None) -> tuple[int, int, int] | None:
        """Parsea una cadena semántica de versión de firmware a una tupla (mayor, menor, parche).

        Soporta formatos tipo '2.7.17', 'v2.7.19.56a4d6f' o '2.8.0'.
        """
        if not v_str:
            return None
        m = re.match(r"^v?(\d+)\.(\d+)(?:\.(\d+))?", v_str.strip())
        if not m:
            return None
        mayor = int(m.group(1))
        menor = int(m.group(2))
        parche = int(m.group(3)) if m.group(3) else 0
        return (mayor, menor, parche)

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa si el nodo emite con rol CLIENT_BASE y firmware igual o superior al umbral."""
        if ctx.nodo is None:
            return []

        rol = (ctx.nodo.role or "").strip().upper()
        if rol != "CLIENT_BASE":
            return []

        fw = ctx.nodo.firmware
        ver = self.parse_version(fw)
        min_ver = self.parse_version(self.config.version_minima) or (2, 7, 17)

        if ver is None or ver < min_ver:
            return []

        nombre_corto = ctx.nodo.short or ctx.nodo.node_id
        return [
            Alerta(
                regla=self.id,
                riesgo="bajo",
                tipo="clientes",
                mensaje=(
                    f"{nombre_corto} tiene rol CLIENT_BASE con firmware {fw} (>= 2.7.17), "
                    "lo que actúa como ROUTER_LATE ralentizando la propagación. "
                    "Se recomienda cambiar el rol a CLIENT o CLIENT_MUTE."
                ),
                nodo=ctx.nodo.node_id,
                datos={
                    "rol": rol,
                    "firmware": fw,
                    "version_minima": self.config.version_minima,
                    "solucion": "Config → Dispositivo → Rol → cambiar a CLIENT o CLIENT_MUTE si el nodo no necesita enrutar",
                },
            )
        ]

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """Comprueba si la alerta sigue activa o si el nodo ha cambiado de rol o bajado versión."""
        nodo = ctx.estado.nodes.get(abierta.nodo)
        if nodo is None:
            return True

        rol = (nodo.role or "").strip().upper()
        if rol != "CLIENT_BASE":
            return False

        ver = self.parse_version(nodo.firmware)
        min_ver = self.parse_version(self.config.version_minima) or (2, 7, 17)
        if ver is None or ver < min_ver:
            return False

        return True
