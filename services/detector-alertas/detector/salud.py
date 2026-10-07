"""Servidor HTTP aiohttp para métricas y comprobación de salud en GET /health."""

import logging
from datetime import UTC, datetime
from typing import Any

from aiohttp import web

from detector.config import Settings
from detector.entrada import EntradaMQTT
from detector.motor.motor import MotorAlertas
from detector.persistencia import GestorPersistencia
from detector.socket_alertas import ServidorSocketAlertas

logger = logging.getLogger("detector.salud")


class ServidorSalud:
    """Expone el endpoint HTTP /health para el monitoreo del portal y orquestador."""

    def __init__(
        self,
        settings: Settings,
        entrada: EntradaMQTT,
        persistencia: GestorPersistencia,
        socket_alertas: ServidorSocketAlertas,
        motor: MotorAlertas,
    ) -> None:
        """Inicializa el servidor de salud."""
        self.settings = settings
        self.entrada = entrada
        self.persistencia = persistencia
        self.socket_alertas = socket_alertas
        self.motor = motor
        self.inicio_at = datetime.now(UTC)
        self.app = web.Application()
        self.app.router.add_get("/health", self._handle_health)
        self.app.router.add_get("/", self._handle_health)
        self.runner: web.AppRunner | None = None
        self.site: web.TCPSite | None = None

    async def iniciar(self) -> None:
        """Arranca el servidor HTTP en el puerto configurado."""
        self.runner = web.AppRunner(self.app)
        await self.runner.setup()
        self.site = web.TCPSite(self.runner, host="0.0.0.0", port=self.settings.health_port)
        await self.site.start()
        logger.info("Servidor de salud HTTP escuchando en http://0.0.0.0:%d/health", self.settings.health_port)

    async def detener(self) -> None:
        """Detiene de forma limpia el servidor HTTP."""
        if self.runner:
            await self.runner.cleanup()
            self.runner = None
            self.site = None
            logger.info("Servidor de salud HTTP detenido.")

    async def _handle_health(self, request: web.Request) -> web.Response:
        """Genera el JSON de estado del servicio y métricas operativas."""
        ahora = datetime.now(UTC)
        uptime = int((ahora - self.inicio_at).total_seconds())

        # Contadores de alertas abiertas
        abiertas = self.motor.ciclo.alertas_abiertas
        por_riesgo: dict[str, int] = {"critico": 0, "alto": 0, "medio": 0, "bajo": 0, "info": 0}
        por_tipo: dict[str, int] = {"infraestructura": 0, "usuario": 0}
        for a in abiertas.values():
            por_riesgo[a.riesgo] = por_riesgo.get(a.riesgo, 0) + 1
            por_tipo[a.tipo] = por_tipo.get(a.tipo, 0) + 1

        mqtt_ok = self.entrada.conectado
        db_ok = self.persistencia.conectado

        # Consideramos degraded si falla DB o MQTT pero hay contingencia activa
        esta_ok = mqtt_ok and db_ok
        status = "healthy" if esta_ok else "degraded"
        status_code = 200 if (esta_ok or uptime < 60) else 503

        cuerpo: dict[str, Any] = {
            "status": status,
            "ok": esta_ok,
            "servicio": "detector-alertas",
            "uptime_s": uptime,
            "hora": ahora.strftime("%Y-%m-%dT%H:%M:%SZ"),
            "mqtt": {
                "conectado": mqtt_ok,
                "ultimo_paquete_at": self.entrada.ultimo_paquete_at.strftime("%Y-%m-%dT%H:%M:%SZ")
                if self.entrada.ultimo_paquete_at
                else None,
                "paquetes_recibidos": self.entrada.paquetes_recibidos,
                "descartados_dedup": self.entrada.paquetes_descartados_dedup,
                "descartados_saturacion": self.entrada.paquetes_descartados_saturacion,
                "ultimo_error": self.entrada.ultimo_error,
            },
            "postgres": {
                "conectado": db_ok,
                "pendientes_contingencia": len(self.persistencia.cola_contingencia),
                "ultimo_error": self.persistencia.ultimo_error,
            },
            "cola_entrada": {
                "tamano": self.entrada.tamano_cola,
                "max": self.settings.cola_max,
            },
            "socket": {
                "clientes_conectados": self.socket_alertas.total_clientes,
                "path": self.settings.alertas_socket,
            },
            "alertas": {
                "abiertas_total": len(abiertas),
                "por_riesgo": por_riesgo,
                "por_tipo": por_tipo,
            },
            "motor": {
                "nodos_rastreados": len(self.motor.estado.nodes),
                "gateways_rastreados": len(self.motor.estado.gateways),
                "reglas_activas": len(self.motor.recarga.reglas_activas),
            },
        }

        return web.json_response(cuerpo, status=status_code)
