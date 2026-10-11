"""Gestor de umbrales dinámicos centralizados en memoria RAM para el detector de alertas."""

import logging
from datetime import UTC, datetime
from typing import Any

from pydantic import BaseModel, Field

logger = logging.getLogger("detector.umbrales")


class UmbralesGrupo(BaseModel):
    """Parámetros de calibración para un grupo específico de roles Meshtastic."""

    hop_limit_recommended: int = Field(default=3, description="Saltos estándar recomendados")
    hop_limit_warning: int = Field(default=6, description="Límite a partir del cual se genera aviso")
    hop_limit_critical: int = Field(default=7, description="Límite para alerta grave")

    nodeinfo_interval_min_s: int = Field(default=43200, description="Intervalo mínimo de NodeInfo (s)")
    nodeinfo_interval_recommended_s: int = Field(default=259200, description="Intervalo recomendado de NodeInfo (s)")

    telemetry_interval_min_s: int = Field(default=3600, description="Intervalo mínimo de telemetría (s)")
    telemetry_interval_recommended_s: int = Field(default=7200, description="Intervalo recomendado de telemetría (s)")

    battery_warning_pct: float = Field(default=35.0, description="Umbral de batería para aviso (%)")
    battery_critical_pct: float = Field(default=15.0, description="Umbral de batería para alerta crítica (%)")
    battery_resolution_pct: float = Field(default=45.0, description="Histéresis mínima de recarga para resolver (%)")

    sunset_battery_min_pct: float = Field(default=0.0, description="Carga mínima al anochecer 20h (%)")

    silence_warning_h: float = Field(default=0.0, description="Horas de silencio antes de aviso")
    silence_critical_h: float = Field(default=0.0, description="Horas de silencio para riesgo alto")

    reboot_loop_count_5m: int = Field(default=3, description="Reinicios en 5 minutos para aviso")
    reboot_loop_count_10m: int = Field(default=5, description="Reinicios en 10 minutos para alerta crítica")


class UmbralesConfig(BaseModel):
    """Estructura completa de configuración de umbrales para los tres grupos de roles."""

    router: UmbralesGrupo = Field(default_factory=UmbralesGrupo)
    client: UmbralesGrupo = Field(default_factory=UmbralesGrupo)
    client_mute: UmbralesGrupo = Field(default_factory=UmbralesGrupo)
    version: int = Field(default=0)
    actualizado_en: datetime = Field(default_factory=lambda: datetime.now(UTC))

    @classmethod
    def por_defecto(cls) -> "UmbralesConfig":
        """Genera la configuración con la matriz de calibración estándar de Andalucía Mesh."""
        ahora = datetime.now(UTC)
        return cls(
            router=UmbralesGrupo(
                hop_limit_recommended=3,
                hop_limit_warning=5,
                hop_limit_critical=7,
                nodeinfo_interval_min_s=86400,
                nodeinfo_interval_recommended_s=259200,
                telemetry_interval_min_s=7200,
                telemetry_interval_recommended_s=14400,
                battery_warning_pct=60.0,
                battery_critical_pct=40.0,
                battery_resolution_pct=70.0,
                sunset_battery_min_pct=60.0,
                silence_warning_h=6.0,
                silence_critical_h=24.0,
                reboot_loop_count_5m=3,
                reboot_loop_count_10m=5,
            ),
            client=UmbralesGrupo(
                hop_limit_recommended=3,
                hop_limit_warning=6,
                hop_limit_critical=7,
                nodeinfo_interval_min_s=43200,
                nodeinfo_interval_recommended_s=259200,
                telemetry_interval_min_s=3600,
                telemetry_interval_recommended_s=7200,
                battery_warning_pct=35.0,
                battery_critical_pct=15.0,
                battery_resolution_pct=45.0,
                sunset_battery_min_pct=0.0,
                silence_warning_h=0.0,
                silence_critical_h=0.0,
                reboot_loop_count_5m=3,
                reboot_loop_count_10m=5,
            ),
            client_mute=UmbralesGrupo(
                hop_limit_recommended=3,
                hop_limit_warning=6,
                hop_limit_critical=7,
                nodeinfo_interval_min_s=21600,
                nodeinfo_interval_recommended_s=86400,
                telemetry_interval_min_s=1800,
                telemetry_interval_recommended_s=3600,
                battery_warning_pct=25.0,
                battery_critical_pct=10.0,
                battery_resolution_pct=30.0,
                sunset_battery_min_pct=0.0,
                silence_warning_h=0.0,
                silence_critical_h=0.0,
                reboot_loop_count_5m=4,
                reboot_loop_count_10m=6,
            ),
            version=int(ahora.timestamp()),
            actualizado_en=ahora,
        )


class GestorUmbrales:
    """Mantiene en memoria RAM los umbrales de detección y coordina recargas atómicas."""

    def __init__(self, config: UmbralesConfig | None = None) -> None:
        """Inicializa el gestor con la configuración proporcionada o los valores calibrados por defecto."""
        self.config: UmbralesConfig = config or UmbralesConfig.por_defecto()
        self.version: int = self.config.version
        self.ultima_recarga: datetime = self.config.actualizado_en

    @staticmethod
    def resolver_grupo(rol: str | None) -> str:
        """Determina el identificador de grupo para un rol de Meshtastic.

        - Grupo 1 ('router'): ROUTER, ROUTER_LATE, REPEATER.
        - Grupo 2 ('client'): CLIENT, CLIENT_HIDDEN, CLIENT_BASE.
        - Grupo 3 ('client_mute'): CLIENT_MUTE y resto de roles auxiliares (TRACKER, LOST_AND_FOUND, etc.).
        """
        if not rol:
            return "client_mute"

        rol_limpio = rol.strip().upper()
        if rol_limpio in ("ROUTER", "ROUTER_LATE", "REPEATER"):
            return "router"
        if rol_limpio in ("CLIENT", "CLIENT_HIDDEN", "CLIENT_BASE"):
            return "client"
        return "client_mute"

    def obtener_grupo(self, rol: str | None) -> UmbralesGrupo:
        """Devuelve los parámetros de umbral del grupo al que pertenece el rol indicado."""
        grupo = self.resolver_grupo(rol)
        if grupo == "router":
            return self.config.router
        if grupo == "client":
            return self.config.client
        return self.config.client_mute

    def actualizar_desde_dict(self, datos: dict[str, Any]) -> int:
        """Sustituye atómicamente la configuración en memoria RAM a partir de un diccionario."""
        ahora = datetime.now(UTC)
        version = int(datos.get("version", ahora.timestamp()))

        fuente_umbrales = datos.get("umbrales", datos)

        router_datos = fuente_umbrales.get("router", {})
        client_datos = fuente_umbrales.get("client", {})
        client_mute_datos = fuente_umbrales.get("client_mute", {})

        # Crear modelos combinando con los valores existentes para completar campos omitidos
        router_grupo = self.config.router.model_copy(update=router_datos)
        client_grupo = self.config.client.model_copy(update=client_datos)
        client_mute_grupo = self.config.client_mute.model_copy(update=client_mute_datos)

        nueva_config = UmbralesConfig(
            router=router_grupo,
            client=client_grupo,
            client_mute=client_mute_grupo,
            version=version,
            actualizado_en=ahora,
        )

        # Asignación atómica de puntero en memoria
        self.config = nueva_config
        self.version = version
        self.ultima_recarga = ahora

        logger.info(
            "Umbrales en RAM actualizados atómicamente a versión %d (recargado en %s)",
            version,
            ahora.isoformat(),
        )
        return version

    async def verificar_y_recargar_db(self, pool: Any) -> bool:
        """Comprueba de forma periódica si la base de datos contiene una versión más reciente.

        En caso de indisponibilidad de la base de datos o ausencia de la tabla,
        mantiene de forma segura los umbrales en memoria RAM sin interrumpir el servicio.
        """
        if not pool:
            return False

        query = """
        SELECT role_group, parameter, value_numeric, updated_at
        FROM alert_thresholds;
        """
        try:
            async with pool.acquire() as conn:
                filas = await conn.fetch(query)

            if not filas:
                return False

            max_updated_at: datetime | None = None
            agrupados: dict[str, dict[str, float]] = {
                "router": {},
                "client": {},
                "client_mute": {},
            }

            for f in filas:
                grp = str(f["role_group"])
                param = str(f["parameter"])
                val = float(f["value_numeric"])
                up_at = f["updated_at"]

                if up_at and (max_updated_at is None or up_at > max_updated_at):
                    max_updated_at = up_at

                if grp in agrupados:
                    agrupados[grp][param] = val

            # Si la marca de tiempo de DB es posterior a la última recarga, actualizar
            if max_updated_at and max_updated_at > self.ultima_recarga:
                v = int(max_updated_at.timestamp())
                self.actualizar_desde_dict({"version": v, "umbrales": agrupados})
                logger.info("Umbrales sincronizados desde base de datos vía fallback periódico (v%d)", v)
                return True

            return False
        except Exception as e:
            logger.debug("Sondeo periódico de umbrales omitido (DB no accesible o tabla inexistente): %s", e)
            return False
