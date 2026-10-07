"""Gestor de recarga en caliente de configuraciones YAML."""

import logging
from typing import Any

import yaml

from detector.config import Settings
from detector.motor.clasificador import Clasificador
from detector.motor.protocolos import REGISTRO_REGLAS, ConfigGeneral, Regla
from detector.motor.silencios import GestorSilencios

logger = logging.getLogger(__name__)


class GestorRecarga:
    """Detecta modificaciones en los archivos YAML y actualiza las instancias de reglas sin reiniciar."""

    def __init__(self, settings: Settings) -> None:
        """Inicializa el gestor de recarga guardando las rutas y marcas temporales."""
        self.settings = settings
        self.clasif_path = settings.resolve_clasificacion_path()
        self.reglas_path = settings.resolve_reglas_path()

        self._last_clasif_mtime: float = 0.0
        self._last_reglas_mtime: float = 0.0

        self.clasificador: Clasificador | None = None
        self.general: ConfigGeneral = ConfigGeneral()
        self.silencios: GestorSilencios = GestorSilencios()
        self.reglas_activas: dict[str, Regla] = {}
        self.reglas_config: dict[str, Any] = {}

        self.config_status: str = "ok"

    def cargar_inicial(self) -> None:
        """Carga inicial de configuraciones obligatoria en el arranque."""
        self._recargar(forzar=True)

    def comprobar_y_recargar(self) -> list[str]:
        """Comprueba las fechas de modificación de los YAML y recarga si han cambiado.

        Devuelve:
            Lista de IDs de reglas que han sido desactivadas durante la recarga.
        """
        clasif_mtime = self.clasif_path.stat().st_mtime if self.clasif_path.is_file() else 0.0
        reglas_mtime = self.reglas_path.stat().st_mtime if self.reglas_path.is_file() else 0.0

        if clasif_mtime != self._last_clasif_mtime or reglas_mtime != self._last_reglas_mtime:
            return self._recargar(forzar=False)
        return []

    def _recargar(self, forzar: bool = False) -> list[str]:
        """Ejecuta la recarga y validación atómica."""
        try:
            if not self.clasif_path.is_file():
                raise FileNotFoundError(f"Archivo no encontrado: {self.clasif_path}")
            if not self.reglas_path.is_file():
                raise FileNotFoundError(f"Archivo no encontrado: {self.reglas_path}")

            with open(self.clasif_path, encoding="utf-8") as f:
                clasif_data: dict[str, Any] = yaml.safe_load(f) or {}

            with open(self.reglas_path, encoding="utf-8") as f:
                reglas_data: dict[str, Any] = yaml.safe_load(f) or {}

            # 1. Validar y construir nuevo clasificador
            nuevo_clasificador = Clasificador(
                riesgos=clasif_data.get("riesgos", []),
                tipos=clasif_data.get("tipos", []),
                infraestructura_manual=clasif_data.get("infraestructura_manual", []),
                infra_roles=self.settings.parsed_infra_roles,
            )

            # 2. Validar bloque general
            gen_data = reglas_data.get("general", {})
            nuevo_general = ConfigGeneral(
                seguimiento_dias=gen_data.get("seguimiento_dias", 7),
                historia_minima_h=gen_data.get("historia_minima_h", 24),
                reapertura_min=gen_data.get("reapertura_min", 60),
                actualizacion_min=gen_data.get("actualizacion_min", 15),
                nodos_max_lista=gen_data.get("nodos_max_lista", 500),
            )

            # 3. Validar silencios
            nuevo_silencios = GestorSilencios(reglas_data.get("silencios", []))

            # 4. Instanciar reglas activas
            nuevas_reglas: dict[str, Regla] = {}
            for rule_id, rule_cls in REGISTRO_REGLAS.items():
                rule_block = reglas_data.get(rule_id, {})
                if rule_block.get("activa", False):
                    cfg_model = rule_cls.Config.model_validate(rule_block)
                    rule_instance = rule_cls(config=cfg_model)  # type: ignore[call-arg]
                    nuevas_reglas[rule_id] = rule_instance

            # Detectar reglas desactivadas
            reglas_desactivadas: list[str] = []
            if self.reglas_activas:
                for r_id in self.reglas_activas:
                    if r_id not in nuevas_reglas:
                        reglas_desactivadas.append(r_id)

            # Aplicar atómicamente
            self.clasificador = nuevo_clasificador
            self.general = nuevo_general
            self.silencios = nuevo_silencios
            self.reglas_activas = nuevas_reglas
            self.reglas_config = reglas_data

            self._last_clasif_mtime = self.clasif_path.stat().st_mtime
            self._last_reglas_mtime = self.reglas_path.stat().st_mtime
            self.config_status = "ok"

            logger.info(
                "Configuración recargada con éxito (%d reglas activas, %d silencios).",
                len(self.reglas_activas),
                len(self.silencios.silencios),
            )
            return reglas_desactivadas

        except Exception as e:
            self.config_status = f"error: {e}"
            logger.error("Error al recargar configuración YAML (se preserva la anterior): %s", e)
            if forzar:
                raise
            return []
