"""Serialización del cuerpo JSON en inglés y generación de firma HMAC-SHA256 para webhooks."""

import hashlib
import hmac
import json
from datetime import UTC, datetime
from typing import Any

MAPA_TRANSICION_INGLES: dict[str, str] = {
    "abierta": "opened",
    "actualizada": "updated",
    "resuelta": "resolved",
    "ping": "ping",
}


def calcular_firma_hmac(secreto: str, cuerpo_bytes: bytes) -> str:
    """Calcula la firma HMAC-SHA256 en hexadecimal en minúsculas."""
    return hmac.new(secreto.encode("utf-8"), cuerpo_bytes, hashlib.sha256).hexdigest()


def construir_cuerpo_webhook(
    transicion_id: str,
    transicion_es: str,
    first_delivery: bool,
    alerta_dict: dict[str, Any] | None,
    dominio_proyecto: str,
    fecha_creacion: datetime | None = None,
) -> tuple[str, bytes]:
    """Serializa el payload en inglés según el contrato canónico UT-08.3 y retorna (json_str, bytes_utf8)."""
    ahora_utc = fecha_creacion or datetime.now(UTC)
    ahora_iso = ahora_utc.strftime("%Y-%m-%dT%H:%M:%SZ")

    transicion_en = MAPA_TRANSICION_INGLES.get(transicion_es, transicion_es)

    payload: dict[str, Any] = {
        "v": 1,
        "transition_id": transicion_id,
        "transition": transicion_en,
        "first_delivery": first_delivery,
        "created_at": ahora_iso,
        "alert": None,
    }

    if alerta_dict is not None and transicion_en != "ping":
        alerta_id = str(alerta_dict.get("id", ""))
        nodo_info_es = alerta_dict.get("nodo_info") or {}

        nodo_info_en: dict[str, Any] = {
            "short": nodo_info_es.get("corto"),
            "long": nodo_info_es.get("largo"),
            "role": nodo_info_es.get("rol"),
            "province": nodo_info_es.get("provincia"),
        }

        estado_alerta = "resolved" if transicion_en == "resolved" else "open"

        payload["alert"] = {
            "id": alerta_id,
            "rule": alerta_dict.get("regla"),
            "risk": alerta_dict.get("riesgo"),
            "type": alerta_dict.get("tipo"),
            "message": alerta_dict.get("mensaje"),
            "node": alerta_dict.get("nodo"),
            "nodes": alerta_dict.get("nodos") or [],
            "node_info": nodo_info_en,
            "data": alerta_dict.get("datos") or {},
            "state": estado_alerta,
            "opened_at": alerta_dict.get("abierta_en"),
            "updated_at": alerta_dict.get("actualizada_en"),
            "resolved_at": alerta_dict.get("resuelta_en"),
            "url": f"https://{dominio_proyecto}/alertas/{alerta_id}",
        }

    cuerpo_str = json.dumps(payload, ensure_ascii=False, separators=(",", ":"))
    cuerpo_bytes = cuerpo_str.encode("utf-8")
    return cuerpo_str, cuerpo_bytes
