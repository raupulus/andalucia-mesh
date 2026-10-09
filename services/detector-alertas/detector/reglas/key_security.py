"""Regla key-security: supervisión de entropía y cambios de claves públicas."""

import base64
import binascii
from typing import ClassVar, Literal

from pydantic import BaseModel

from detector.modelos import PaqueteDecodificado
from detector.motor.protocolos import Alerta, AlertaAbierta, Contexto, registrar

INFRA_ROLES = frozenset({"ROUTER", "REPEATER", "ROUTER_LATE"})


def decodificar_clave(b64_str: str | None) -> bytes | None:
    """Decodifica una clave pública base64 asegurando longitud de 32 bytes."""
    if not b64_str:
        return None
    try:
        raw = base64.b64decode(b64_str, validate=True)
        return raw if len(raw) == 32 else None
    except (binascii.Error, ValueError):
        return None


def motivo_clave_debil(raw: bytes) -> str | None:
    """Evalúa la entropía estructural de una clave pública Curve25519 de 32 bytes."""
    distintos = len(set(raw))
    if distintos == 1:
        return f"todos los bytes iguales (0x{raw[0]:02x})"
    if distintos < 16:
        return f"baja entropía ({distintos} bytes distintos de 32)"
    if all((raw[i + 1] - raw[i]) % 256 == 1 for i in range(len(raw) - 1)):
        return "secuencia de bytes consecutivos"
    for periodo in (2, 4, 8):
        if raw == raw[:periodo] * (32 // periodo):
            return f"patrón repetido de {periodo} bytes"
    return None


class ConfigKeySecurity(BaseModel):
    """Configuración para la regla key-security."""

    activa: bool = True


@registrar
class ReglaKeySecurity:
    """Detecta claves públicas con entropía estructural defectuosa o cambios imprevistos de clave."""

    id: ClassVar[str] = "key-security"
    nombre: ClassVar[str] = "Seguridad de claves públicas"
    descripcion: ClassVar[str] = (
        "Detección de claves públicas de baja entropía, defectuosas o sustituciones imprevistas"
    )
    fase: ClassVar[Literal["mvp", "ampliacion"]] = "ampliacion"
    afecta_malla: ClassVar[bool] = True
    suscrita_a: ClassVar[frozenset[str]] = frozenset({"nodeinfo", "telemetry", "position", "all"})
    Config: ClassVar[type[BaseModel]] = ConfigKeySecurity

    def __init__(self, config: ConfigKeySecurity | None = None) -> None:
        """Inicializa la regla con su configuración."""
        self.config = config or ConfigKeySecurity()

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa si el paquete contiene una clave pública débil o un cambio de clave."""
        if ctx.nodo is None or not isinstance(ctx.evento, PaqueteDecodificado):
            return []

        payload = ctx.evento.payload or {}
        raw_key_str = payload.get("public_key")
        if not raw_key_str or not isinstance(raw_key_str, str):
            return []

        raw_bytes = decodificar_clave(raw_key_str)
        if raw_bytes is None:
            return []

        motivo_debil = motivo_clave_debil(raw_bytes)
        cambio_clave = bool(ctx.nodo.previous_keys and raw_key_str not in ctx.nodo.previous_keys)

        motivo: str | None = None
        tipo_anomalia: str = "debilidad_clave"

        if motivo_debil:
            motivo = f"clave pública débil o defectuosa ({motivo_debil})"
            tipo_anomalia = "clave_debil"
        elif cambio_clave:
            motivo = "ha cambiado su clave pública inesperadamente"
            tipo_anomalia = "cambio_clave"

        if motivo is None:
            return []

        es_infra = bool(
            (ctx.nodo.role and ctx.nodo.role.upper() in INFRA_ROLES)
            or ctx.es_infraestructura(ctx.nodo.node_id)
        )
        riesgo = "alto" if es_infra else "medio"

        nombre_corto = ctx.nodo.short or ctx.nodo.node_id
        rol_txt = ctx.nodo.role or "CLIENT"
        fingerprint = f"{int.from_bytes(raw_bytes[:4], 'big'):08x}"

        return [
            Alerta(
                regla=self.id,
                riesgo=riesgo,
                mensaje=f"{nombre_corto} ({rol_txt}) presenta anomalía de clave pública: {motivo}",
                nodo=ctx.nodo.node_id,
                datos={
                    "motivo": motivo,
                    "rol": rol_txt,
                    "clave_fingerprint": fingerprint,
                    "tipo_anomalia": tipo_anomalia,
                },
                tipo="infraestructura" if es_infra else "clientes",
            )
        ]

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """Determina si la alerta sigue activa evaluando la clave actual del nodo."""
        nodo = ctx.estado.nodes.get(abierta.nodo)
        if nodo is None or nodo.public_key is None:
            return False

        raw = decodificar_clave(nodo.public_key)
        if (
            raw is not None
            and motivo_clave_debil(raw) is None
            and abierta.datos.get("tipo_anomalia") == "clave_debil"
        ):
            # Si era por clave débil y ahora tiene una clave válida, se resuelve
            return False

        # Si han pasado 24 horas sin reincidencia del cambio, se resuelve
        return (ctx.ahora - abierta.actualizada_en).total_seconds() <= 86400
