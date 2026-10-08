"""Formato neutro de avisos y alertas independiente de la plataforma."""

from dataclasses import dataclass
from datetime import UTC, datetime
from typing import Any
from zoneinfo import ZoneInfo

from nucleo.catalogo import GestorCatalogo

PROVINCIAS: dict[str, str] = {
    "ES-AL": "Almería",
    "ES-CA": "Cádiz",
    "ES-CO": "Córdoba",
    "ES-GR": "Granada",
    "ES-H": "Huelva",
    "ES-J": "Jaén",
    "ES-MA": "Málaga",
    "ES-SE": "Sevilla",
    "FUERA": "Fuera de Andalucía",
}

PROVINCIAS_ANDALUCIA: frozenset[str] = frozenset(
    {"ES-AL", "ES-CA", "ES-CO", "ES-GR", "ES-H", "ES-J", "ES-MA", "ES-SE"}
)


def es_nodo_exterior(nodo_id: str, nodo_info: dict[str, Any] | None) -> bool:
    """Indica si un nodo está ubicado fuera de Andalucía según su provincia asignada."""
    if nodo_id == "all" or not nodo_info:
        return False
    prov = nodo_info.get("provincia")
    if not prov:
        return False
    prov_upper = str(prov).strip().upper()
    return prov_upper == "FUERA" or prov_upper not in PROVINCIAS_ANDALUCIA


@dataclass
class AvisoNeutro:
    """Representación neutra de un aviso para que cada plataforma lo pinte."""

    icono: str
    titulo: str
    lineas: list[str]
    enlace: str
    color_token: str  # 'critico', 'aviso', 'info', 'correcto'
    fecha: datetime
    es_silencioso: bool
    transicion: str
    alerta_id: str | None
    nodo_id: str | None


def formatear_duracion(inicio: datetime, fin: datetime) -> str:
    """Calcula y formatea la duración transcurrida en horas y minutos en español."""
    segundos = int((fin - inicio).total_seconds())
    if segundos < 0:
        return "0 min"
    minutos = segundos // 60
    horas = minutos // 60
    min_rest = minutos % 60
    if horas > 0:
        return f"{horas} h {min_rest} min" if min_rest > 0 else f"{horas} h"
    return f"{minutos} min"


def formatear_fecha_hora(dt: datetime, tz_nombre: str = "Europe/Madrid") -> str:
    """Formatea la hora en Europe/Madrid: HH:MM si es hoy, dd/mm HH:MM si es otro día."""
    tz = ZoneInfo(tz_nombre)
    dt_local = dt.astimezone(tz)
    ahora_local = datetime.now(tz)
    if dt_local.date() == ahora_local.date():
        return dt_local.strftime("%H:%M")
    return dt_local.strftime("%d/%m %H:%M")


def obtener_nombre_provincia(codigo: str | None) -> str | None:
    """Obtiene el nombre en español de la provincia."""
    if not codigo:
        return None
    return PROVINCIAS.get(codigo.upper(), codigo)


def construir_sujeto(nodo: str, nodos: list[str] | None, nodo_info: dict[str, str | None] | None) -> str:
    """Construye la línea descriptiva del sujeto sin incluir jamás coordenadas."""
    if nodo == "all":
        cant = len(nodos) if nodos else 0
        return f"{cant} nodos afectados"

    partes: list[str] = []
    if nodo_info:
        corto = nodo_info.get("corto")
        largo = nodo_info.get("largo")
        if corto and largo:
            partes.append(f"{corto} ({largo})")
        elif corto:
            partes.append(corto)
        elif largo:
            partes.append(largo)

    # Identificador de nodo siempre presente si no es 'all'
    partes.append(nodo)

    if nodo_info:
        rol = nodo_info.get("rol")
        if rol:
            partes.append(rol)
        prov = nodo_info.get("provincia")
        nombre_prov = obtener_nombre_provincia(prov)
        if nombre_prov:
            partes.append(nombre_prov)

    return " · ".join(partes)


def formatear_aviso_neutro(
    datos_transicion: dict[str, Any],
    catalogo: GestorCatalogo,
    dominio_proyecto: str,
    tz_nombre: str = "Europe/Madrid",
    riesgo_previo: str | None = None,
    es_reapertura: bool = False,
) -> AvisoNeutro:
    """Transforma una transición de alerta en un AvisoNeutro canónico."""
    transicion = str(datos_transicion.get("transicion", "abierta"))
    alerta = datos_transicion.get("alerta", {})
    alerta_id = str(alerta.get("id", ""))
    regla = str(alerta.get("regla", ""))
    riesgo = str(alerta.get("riesgo", "medio")).lower()
    tipo = str(alerta.get("tipo", "infraestructura")).lower()
    mensaje = str(alerta.get("mensaje", ""))
    nodo = str(alerta.get("nodo", ""))
    nodos = alerta.get("nodos")
    nodo_info = alerta.get("nodo_info")

    # Fechas
    abierta_str = alerta.get("abierta_en")
    actualizada_str = alerta.get("actualizada_en")
    resuelta_str = alerta.get("resuelta_en")

    abierta_dt = datetime.fromisoformat(abierta_str.replace("Z", "+00:00")) if abierta_str else datetime.now(UTC)
    actualizada_dt = (
        datetime.fromisoformat(actualizada_str.replace("Z", "+00:00")) if actualizada_str else abierta_dt
    )
    resuelta_dt = (
        datetime.fromisoformat(resuelta_str.replace("Z", "+00:00")) if resuelta_str else actualizada_dt
    )

    enlace = f"https://{dominio_proyecto}/alertas/{alerta_id}"
    nombre_regla_str = catalogo.nombre_regla(regla)
    nombre_riesgo_str = catalogo.nombre_riesgo(riesgo).upper()
    nombre_tipo_str = catalogo.nombre_tipo(tipo)

    # Recortar mensaje a 1.000 caracteres
    if len(mensaje) > 1000:
        mensaje = mensaje[:997] + "..."

    sujeto = construir_sujeto(nodo, nodos, nodo_info)

    # Selección de iconos y token de color según DESIGN.md
    if transicion == "resuelta":
        icono = "✅"
        color_token = "correcto"
        es_silencioso = True
    elif riesgo == "alto":
        icono = "🔴"
        color_token = "critico"
        es_silencioso = False
    elif riesgo == "medio":
        icono = "🟠"
        color_token = "aviso"
        es_silencioso = False
    elif riesgo == "bajo":
        icono = "🔵"
        color_token = "info"
        es_silencioso = True
    else:
        icono = "⚪"
        color_token = "info"
        es_silencioso = False

    lineas: list[str] = []

    # Determinar caso
    if transicion == "resuelta":
        titulo = f"{icono} RESUELTA · {nombre_regla_str}"
        lineas.append(sujeto)
        txt_abierta = formatear_fecha_hora(abierta_dt, tz_nombre)
        txt_resuelta = formatear_fecha_hora(resuelta_dt, tz_nombre)
        duracion_txt = formatear_duracion(abierta_dt, resuelta_dt)
        lineas.append(f"Abierta {txt_abierta} · resuelta {txt_resuelta} ({duracion_txt})")
    elif es_reapertura:
        titulo = f"{icono} REABIERTA · {nombre_riesgo_str} · {nombre_regla_str}"
        lineas.append(sujeto)
        lineas.append(mensaje)
    elif transicion == "actualizada" and riesgo_previo and riesgo_previo != riesgo:
        idx_prev = catalogo.orden_riesgo(riesgo_previo)
        idx_act = catalogo.orden_riesgo(riesgo)
        verbo = "SUBE A" if idx_act > idx_prev else "BAJA A"
        titulo = f"{icono} {verbo} {nombre_riesgo_str} · {nombre_regla_str}"
        lineas.append(sujeto)
        lineas.append(mensaje)
    else:
        # Apertura estándar
        titulo = f"{icono} {nombre_riesgo_str} · {nombre_tipo_str} · {nombre_regla_str}"
        lineas.append(sujeto)
        lineas.append(mensaje)

    # Marca de retraso si la transición tiene más de 5 minutos (300s)
    ahora_utc = datetime.now(UTC)
    if (ahora_utc - actualizada_dt).total_seconds() > 300:
        txt_ret = formatear_fecha_hora(actualizada_dt, tz_nombre)
        lineas.append(f"⏱ Aviso con retraso: ocurrió el {txt_ret}")

    return AvisoNeutro(
        icono=icono,
        titulo=titulo,
        lineas=lineas,
        enlace=enlace,
        color_token=color_token,
        fecha=actualizada_dt,
        es_silencioso=es_silencioso,
        transicion=transicion,
        alerta_id=alerta_id,
        nodo_id=nodo if nodo != "all" else None,
    )


def formatear_resumen_neutro(
    regla_id: str,
    regla_nombre: str,
    etiquetas: list[str],
    riesgo_maximo: str,
    dominio_proyecto: str,
) -> AvisoNeutro:
    """Construye un aviso neutro para resúmenes de ráfagas acumuladas."""
    icono = "🔴" if riesgo_maximo == "alto" else "🟠"
    color_token = "critico" if riesgo_maximo == "alto" else "aviso"
    total = len(etiquetas)
    titulo = f"{icono} {total} alertas más de {regla_nombre} en 2 min"

    # Máximo 20 etiquetas visibles
    visibles = etiquetas[:20]
    sobrantes = total - len(visibles)
    txt_etiquetas = ", ".join(visibles)
    if sobrantes > 0:
        txt_etiquetas += f" (+{sobrantes})"

    enlace = f"https://{dominio_proyecto}/alertas"
    return AvisoNeutro(
        icono=icono,
        titulo=titulo,
        lineas=[txt_etiquetas],
        enlace=enlace,
        color_token=color_token,
        fecha=datetime.now(UTC),
        es_silencioso=False,
        transicion="resumen",
        alerta_id=None,
        nodo_id=None,
    )
