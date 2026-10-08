"""Motor central de evaluación de reglas y coordinación de eventos."""

import logging
from datetime import UTC, datetime, timedelta

from detector.config import Settings
from detector.modelos import PaqueteDecodificado
from detector.motor.bases import LineasBase
from detector.motor.ciclo import GestorCicloVida
from detector.motor.estado import EstadoMotor
from detector.motor.protocolos import Alerta, Contexto, Tick, TransicionAlerta
from detector.motor.recarga import GestorRecarga

logger = logging.getLogger(__name__)


class MotorAlertas:
    """Orquestador de reglas, estado, líneas base y ciclo de vida."""

    def __init__(
        self,
        settings: Settings,
        estado: EstadoMotor | None = None,
    ) -> None:
        """Inicializa el motor con su configuración y estado."""
        self.settings = settings
        self.estado = estado or EstadoMotor()
        self.bases = LineasBase(
            historia_minima_h=24,
            infra_roles=settings.parsed_infra_roles,
        )
        self.recarga = GestorRecarga(settings)
        self.ciclo = GestorCicloVida()

    def inicializar(self) -> None:
        """Carga inicial obligatoria de las configuraciones YAML."""
        self.recarga.cargar_inicial()
        self.ciclo.general = self.recarga.general

    def procesar_paquete(
        self,
        pkt: PaqueteDecodificado,
        ahora: datetime | None = None,
    ) -> list[TransicionAlerta]:
        """Procesa un paquete decodificado, evalúa reglas suscritas y actualiza alertas."""
        ref_time = ahora or pkt.parsed_rx_first
        transiciones: list[TransicionAlerta] = []

        # 1. Registrar paquete en el estado del motor
        self.estado.registrar_paquete(pkt, ref_time)
        nodo_estado = self.estado.nodes.get(pkt.from_node_id)

        # 2. Evaluar reglas suscritas al portnum del paquete
        for rule_id, rule in self.recarga.reglas_activas.items():
            if rule.suscrita_a and (pkt.portnum in rule.suscrita_a or "all" in rule.suscrita_a):
                rule_cfg = self.recarga.reglas_config.get(rule_id, {})
                rule_model = rule.Config.model_validate(rule_cfg)

                ctx = Contexto(
                    ahora=ref_time,
                    evento=pkt,
                    nodo=nodo_estado,
                    estado=self.estado,
                    bases=self.bases,
                    config=rule_model,
                    general=self.recarga.general,
                    es_infraestructura=lambda nid: self.recarga.clasificador.es_infraestructura(nid)
                    if self.recarga.clasificador
                    else False,
                )

                try:
                    alertas_generadas = rule.comprobar(ctx)
                except Exception as e:
                    logger.error("Error al ejecutar comprobar en regla '%s': %s", rule_id, e)
                    continue

                for alt in alertas_generadas:
                    info_dict = None
                    datos_dict = dict(alt.datos)
                    if nodo_estado:
                        info_dict = {
                            "corto": nodo_estado.short,
                            "largo": nodo_estado.long,
                            "rol": nodo_estado.role,
                            "provincia": nodo_estado.province,
                        }
                        if "provincia" not in datos_dict:
                            datos_dict["provincia"] = nodo_estado.province or "FUERA"
                        if "dentro_andalucia" not in datos_dict:
                            datos_dict["dentro_andalucia"] = nodo_estado.dentro_andalucia

                    alt_final = Alerta(
                        regla=alt.regla,
                        riesgo=alt.riesgo,
                        mensaje=alt.mensaje,
                        nodo=alt.nodo,
                        nodos=alt.nodos,
                        datos=datos_dict,
                        tipo=alt.tipo,
                    )

                    t = self.ciclo.procesar_alerta_regla(
                        alerta=alt_final,
                        ahora=ref_time,
                        clasificador=self.recarga.clasificador,  # type: ignore[arg-type]
                        silencios=self.recarga.silencios,
                        afecta_malla=rule.afecta_malla,
                        rol=nodo_estado.role if nodo_estado else None,
                        is_gateway=nodo_estado.is_gateway if nodo_estado else False,
                        nodo_info=info_dict,
                    )
                    if t:
                        transiciones.append(t)

        # 3. Evaluar sigue_activa en alertas abiertas de este nodo emisor
        claves_nodo = [
            k
            for k, a in self.ciclo.alertas_abiertas.items()
            if a.nodo == pkt.from_node_id and a.regla in self.recarga.reglas_activas
        ]

        for clave in claves_nodo:
            abierta = self.ciclo.alertas_abiertas.get(clave)
            if not abierta:
                continue

            rule = self.recarga.reglas_activas[abierta.regla]
            rule_cfg = self.recarga.reglas_config.get(abierta.regla, {})
            rule_model = rule.Config.model_validate(rule_cfg)

            ctx = Contexto(
                ahora=ref_time,
                evento=pkt,
                nodo=nodo_estado,
                estado=self.estado,
                bases=self.bases,
                config=rule_model,
                general=self.recarga.general,
                es_infraestructura=lambda nid: self.recarga.clasificador.es_infraestructura(nid)
                if self.recarga.clasificador
                else False,
            )

            try:
                sigue = rule.sigue_activa(ctx, abierta)
            except Exception as e:
                logger.error("Error en sigue_activa de regla '%s': %s", abierta.regla, e)
                continue

            if not sigue:
                t = self.ciclo.resolver_alerta(clave, motivo_cierre="condicion", ahora=ref_time)
                if t:
                    transiciones.append(t)

        return transiciones

    def ejecutar_tick(self, ahora: datetime) -> list[TransicionAlerta]:
        """Ejecuta el ciclo de reloj cada 60 s para ausencias, caducidades y resoluciones."""
        ref_time = ahora if ahora.tzinfo else ahora.replace(tzinfo=UTC)
        transiciones: list[TransicionAlerta] = []

        # 1. Comprobar recarga en caliente
        reglas_desactivadas = self.recarga.comprobar_y_recargar()
        if reglas_desactivadas:
            for r_id in reglas_desactivadas:
                for clave in list(self.ciclo.alertas_abiertas.keys()):
                    if clave.startswith(f"{r_id}:"):
                        t = self.ciclo.resolver_alerta(clave, motivo_cierre="regla_desactivada", ahora=ref_time)
                        if t:
                            transiciones.append(t)

        tick_ev = Tick(ahora=ref_time)

        # 2. Evaluar reglas de temporizador (suscrita_a vacío: ausencias)
        for rule_id, timer_rule in self.recarga.reglas_activas.items():
            if not timer_rule.suscrita_a:
                rule_cfg = self.recarga.reglas_config.get(rule_id, {})
                rule_model = timer_rule.Config.model_validate(rule_cfg)

                ctx = Contexto(
                    ahora=ref_time,
                    evento=tick_ev,
                    nodo=None,
                    estado=self.estado,
                    bases=self.bases,
                    config=rule_model,
                    general=self.recarga.general,
                    es_infraestructura=lambda nid: self.recarga.clasificador.es_infraestructura(nid)
                    if self.recarga.clasificador
                    else False,
                )

                try:
                    alertas_ausencias = timer_rule.comprobar(ctx)
                except Exception as e:
                    logger.error("Error en comprobar (tick) de regla '%s': %s", rule_id, e)
                    continue

                for alt in alertas_ausencias:
                    nodo_estado = self.estado.nodes.get(alt.nodo)
                    info_dict = None
                    datos_dict = dict(alt.datos)
                    if nodo_estado:
                        info_dict = {
                            "corto": nodo_estado.short,
                            "largo": nodo_estado.long,
                            "rol": nodo_estado.role,
                            "provincia": nodo_estado.province,
                        }
                        if "provincia" not in datos_dict:
                            datos_dict["provincia"] = nodo_estado.province or "FUERA"
                        if "dentro_andalucia" not in datos_dict:
                            datos_dict["dentro_andalucia"] = nodo_estado.dentro_andalucia

                    alt_final = Alerta(
                        regla=alt.regla,
                        riesgo=alt.riesgo,
                        mensaje=alt.mensaje,
                        nodo=alt.nodo,
                        nodos=alt.nodos,
                        datos=datos_dict,
                        tipo=alt.tipo,
                    )

                    t = self.ciclo.procesar_alerta_regla(
                        alerta=alt_final,
                        ahora=ref_time,
                        clasificador=self.recarga.clasificador,  # type: ignore[arg-type]
                        silencios=self.recarga.silencios,
                        afecta_malla=timer_rule.afecta_malla,
                        rol=nodo_estado.role if nodo_estado else None,
                        is_gateway=nodo_estado.is_gateway if nodo_estado else False,
                        nodo_info=info_dict,
                    )
                    if t:
                        transiciones.append(t)

        # 3. Evaluar sigue_activa en todas las alertas abiertas
        for clave, abierta in list(self.ciclo.alertas_abiertas.items()):
            active_rule = self.recarga.reglas_activas.get(abierta.regla)
            if not active_rule:
                continue

            nodo_estado = self.estado.nodes.get(abierta.nodo)
            rule_cfg = self.recarga.reglas_config.get(abierta.regla, {})
            rule_model = active_rule.Config.model_validate(rule_cfg)

            ctx = Contexto(
                ahora=ref_time,
                evento=tick_ev,
                nodo=nodo_estado,
                estado=self.estado,
                bases=self.bases,
                config=rule_model,
                general=self.recarga.general,
                es_infraestructura=lambda nid: self.recarga.clasificador.es_infraestructura(nid)
                if self.recarga.clasificador
                else False,
            )

            try:
                sigue = active_rule.sigue_activa(ctx, abierta)
            except Exception as e:
                logger.error("Error en sigue_activa (tick) de regla '%s': %s", abierta.regla, e)
                continue

            if not sigue:
                t = self.ciclo.resolver_alerta(clave, motivo_cierre="condicion", ahora=ref_time)
                if t:
                    transiciones.append(t)

        # 4. Comprobar superación del periodo de seguimiento (7 días) para routers o gateways
        limite_seguimiento = ref_time - timedelta(days=self.recarga.general.seguimiento_dias)
        for clave, abierta in list(self.ciclo.alertas_abiertas.items()):
            if abierta.nodo != "all":
                nodo_estado = self.estado.nodes.get(abierta.nodo)
                if nodo_estado and nodo_estado.last_seen < limite_seguimiento:
                    t = self.ciclo.resolver_alerta(clave, motivo_cierre="fuera_de_seguimiento", ahora=ref_time)
                    if t:
                        transiciones.append(t)

        # 5. Purgar historial antiguo de resueltas
        self.ciclo.purgar_historial_resueltas(ref_time)

        return transiciones
