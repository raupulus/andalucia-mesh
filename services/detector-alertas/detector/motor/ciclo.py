"""Gestor del ciclo de vida de alertas (aperturas, actualizaciones, resoluciones y reaperturas)."""

from datetime import datetime, timedelta
from typing import Any

from detector.motor.clasificador import Clasificador
from detector.motor.protocolos import (
    Alerta,
    AlertaAbierta,
    ConfigGeneral,
    TransicionAlerta,
    generar_ulid,
)
from detector.motor.silencios import GestorSilencios


class GestorCicloVida:
    """Gestiona el ciclo de vida reactivo de las alertas con histéresis y reaperturas."""

    def __init__(self, general: ConfigGeneral | None = None) -> None:
        """Inicializa el gestor de ciclo de vida."""
        self.general = general or ConfigGeneral()
        self.alertas_abiertas: dict[str, AlertaAbierta] = {}
        self.historial_resueltas: dict[str, AlertaAbierta] = {}

    def procesar_alerta_regla(
        self,
        alerta: Alerta,
        ahora: datetime,
        clasificador: Clasificador,
        silencios: GestorSilencios,
        afecta_malla: bool = False,
        rol: str | None = None,
        is_gateway: bool = False,
        nodo_info: dict[str, Any] | None = None,
    ) -> TransicionAlerta | None:
        """Procesa una alerta devuelta por una regla, determinando si genera una transición."""
        # 1. Comprobar silencios
        if silencios.esta_silenciado(alerta.nodo, alerta.regla, ahora):
            return None

        # 2. Clasificar riesgo y tipo
        riesgo, tipo = clasificador.clasificar(
            riesgo_propuesto=alerta.riesgo,
            nodo_id=alerta.nodo,
            afecta_malla=afecta_malla,
            rol=rol,
            is_gateway=is_gateway,
            tipo_propuesto=alerta.tipo,
        )

        clave = f"{alerta.regla}:{alerta.nodo}"
        abierta = self.alertas_abiertas.get(clave)

        if abierta is None:
            # 3. No existe alerta abierta previa: comprobar si es reapertura de una resuelta reciente
            reciente = self.historial_resueltas.get(clave)
            if (
                reciente is not None
                and reciente.resuelta_en is not None
                and (ahora - reciente.resuelta_en).total_seconds() < self.general.reapertura_min * 60
            ):
                # Reabrir alerta previa con su ID original
                reciente.reaperturas += 1
                reciente.estado = "abierta"
                reciente.abierta_en = ahora
                reciente.resuelta_en = None
                reciente.riesgo = riesgo
                reciente.tipo = tipo
                reciente.mensaje = alerta.mensaje
                reciente.nodos = list(alerta.nodos)
                reciente.nodo_info = nodo_info
                reciente.datos = dict(alerta.datos)
                reciente.actualizada_en = ahora
                reciente.evidencia_en = ahora
                reciente.ultima_transicion_en = ahora

                # Mover de historial a abiertas
                del self.historial_resueltas[clave]
                self.alertas_abiertas[clave] = reciente

                return TransicionAlerta(
                    transicion_id=generar_ulid(),
                    alerta_id=reciente.id,
                    transicion="abierta",
                    en=ahora,
                    alerta=reciente.to_objeto_alerta(),
                )

            # Nueva alerta
            nueva_id = generar_ulid()
            nueva = AlertaAbierta(
                id=nueva_id,
                regla=alerta.regla,
                nodo=alerta.nodo,
                riesgo=riesgo,
                tipo=tipo,
                mensaje=alerta.mensaje,
                nodos=list(alerta.nodos),
                nodo_info=nodo_info,
                datos=dict(alerta.datos),
                estado="abierta",
                abierta_en=ahora,
                actualizada_en=ahora,
                resuelta_en=None,
                reaperturas=0,
                evidencia_en=ahora,
                ultima_transicion_en=ahora,
            )
            self.alertas_abiertas[clave] = nueva

            return TransicionAlerta(
                transicion_id=generar_ulid(),
                alerta_id=nueva.id,
                transicion="abierta",
                en=ahora,
                alerta=nueva.to_objeto_alerta(),
            )

        # 4. Ya existe una alerta abierta: evaluar si corresponde actualización
        comp = clasificador.comparar_riesgo(riesgo, abierta.riesgo)

        if comp > 0:
            # Subida de riesgo: emisión inmediata de 'actualizada'
            abierta.riesgo = riesgo
            abierta.tipo = tipo
            abierta.mensaje = alerta.mensaje
            abierta.nodos = list(alerta.nodos)
            abierta.nodo_info = nodo_info
            abierta.datos = dict(alerta.datos)
            abierta.actualizada_en = ahora
            abierta.evidencia_en = ahora
            abierta.ultima_transicion_en = ahora

            return TransicionAlerta(
                transicion_id=generar_ulid(),
                alerta_id=abierta.id,
                transicion="actualizada",
                en=ahora,
                alerta=abierta.to_objeto_alerta(),
            )

        # Evaluar cambio > 20% en lista de nodos
        cambio_nodos_20pct = False
        if abierta.nodo == "all" and abierta.nodos:
            diff_nodos = abs(len(alerta.nodos) - len(abierta.nodos))
            if (diff_nodos / len(abierta.nodos)) > 0.20:
                cambio_nodos_20pct = True

        tiempo_desde_transicion = (ahora - abierta.ultima_transicion_en).total_seconds()
        puede_emitir_bajada = tiempo_desde_transicion >= self.general.actualizacion_min * 60

        if (comp < 0 or cambio_nodos_20pct) and puede_emitir_bajada:
            # Bajada de riesgo o variación significativa tras intervalo mínimo
            abierta.riesgo = riesgo
            abierta.tipo = tipo
            abierta.mensaje = alerta.mensaje
            abierta.nodos = list(alerta.nodos)
            abierta.nodo_info = nodo_info
            abierta.datos = dict(alerta.datos)
            abierta.actualizada_en = ahora
            abierta.evidencia_en = ahora
            abierta.ultima_transicion_en = ahora

            return TransicionAlerta(
                transicion_id=generar_ulid(),
                alerta_id=abierta.id,
                transicion="actualizada",
                en=ahora,
                alerta=abierta.to_objeto_alerta(),
            )

        # Refresco silencioso de evidencias en memoria sin transición por el socket
        abierta.mensaje = alerta.mensaje
        abierta.datos = dict(alerta.datos)
        abierta.evidencia_en = ahora
        return None

    def resolver_alerta(
        self,
        clave: str,
        motivo_cierre: str,
        ahora: datetime,
    ) -> TransicionAlerta | None:
        """Resuelve una alerta abierta emitiendo la transición correspondiente."""
        abierta = self.alertas_abiertas.pop(clave, None)
        if abierta is None:
            return None

        abierta.estado = "resuelta"
        abierta.resuelta_en = ahora
        abierta.actualizada_en = ahora
        abierta.ultima_transicion_en = ahora
        abierta.datos["cierre"] = motivo_cierre

        # Guardar en histórico para posibles reaperturas rápidas
        self.historial_resueltas[clave] = abierta

        return TransicionAlerta(
            transicion_id=generar_ulid(),
            alerta_id=abierta.id,
            transicion="resuelta",
            en=ahora,
            alerta=abierta.to_objeto_alerta(),
        )

    def purgar_historial_resueltas(self, ahora: datetime) -> None:
        """Limpia alertas resueltas que superan la ventana de reapertura máxima."""
        limite = ahora - timedelta(minutes=self.general.reapertura_min * 2)
        claves_obsoletas = [
            k
            for k, a in self.historial_resueltas.items()
            if a.resuelta_en is not None and a.resuelta_en < limite
        ]
        for k in claves_obsoletas:
            del self.historial_resueltas[k]
