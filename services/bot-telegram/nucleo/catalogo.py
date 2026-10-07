"""Gestor del catálogo de alertas con recarga periódica y fallback a catálogo mínimo."""

import asyncio
import contextlib
import logging
from typing import Any

from nucleo.api_portal import ClientePortal

logger = logging.getLogger("nucleo.catalogo")


class GestorCatalogo:
    """Gestiona los metadatos de riesgos, tipos y reglas obtenidos del portal."""

    def __init__(self, cliente_portal: ClientePortal) -> None:
        """Inicializa el catálogo con los valores de reserva mínimos."""
        self.cliente_portal = cliente_portal
        self._tarea_recarga: asyncio.Task[None] | None = None
        self._deteniendo: bool = False

        # Catálogo mínimo de reserva
        self.riesgos: list[dict[str, Any]] = [
            {"id": "bajo", "name": "Bajo", "description": "Conviene saberlo; no hay daño inmediato"},
            {"id": "medio", "name": "Medio", "description": "Riesgo real para un nodo o una zona; conviene actuar"},
            {"id": "alto", "name": "Alto", "description": "Fallo activo o daño a la malla"},
        ]
        self.tipos: list[dict[str, Any]] = [
            {"id": "infraestructura", "name": "Infraestructura", "description": "Routers, gateways y lo que degrada la malla"},
            {"id": "clientes", "name": "Clientes", "description": "Nodos que no son infraestructura"},
        ]
        self.reglas: list[dict[str, Any]] = []

    async def iniciar(self) -> None:
        """Carga inicial del catálogo y arranca la tarea de refresco periódico."""
        self._deteniendo = False
        await self.recargar()
        self._tarea_recarga = asyncio.create_task(self._bucle_refresco())
        logger.info("Gestor de catálogo iniciado.")

    async def detener(self) -> None:
        """Detiene la tarea de refresco."""
        self._deteniendo = True
        if self._tarea_recarga:
            self._tarea_recarga.cancel()
            with contextlib.suppress(asyncio.CancelledError):
                await self._tarea_recarga
            self._tarea_recarga = None
        logger.info("Gestor de catálogo detenido.")

    async def recargar(self) -> bool:
        """Consulta el catálogo a la API del portal y actualiza la copia en memoria."""
        datos = await self.cliente_portal.obtener_catalogo()
        if not datos:
            logger.warning("No se pudo obtener el catálogo del portal. Manteniendo versión previa.")
            return False

        if "risks" in datos and isinstance(datos["risks"], list) and datos["risks"]:
            self.riesgos = datos["risks"]
        if "types" in datos and isinstance(datos["types"], list) and datos["types"]:
            self.tipos = datos["types"]
        if "rules" in datos and isinstance(datos["rules"], list):
            self.reglas = datos["rules"]

        logger.info(
            "Catálogo actualizado: %d riesgos, %d tipos, %d reglas.",
            len(self.riesgos),
            len(self.tipos),
            len(self.reglas),
        )
        return True

    async def _bucle_refresco(self) -> None:
        """Bucle periódico: intenta recargar cada 1 hora (o cada 5 min si falla)."""
        while not self._deteniendo:
            try:
                # Esperar 1 hora
                await asyncio.sleep(3600)
                if self._deteniendo:
                    break
                exito = await self.recargar()
                if not exito:
                    await asyncio.sleep(300)  # Reintentar a los 5 minutos
            except asyncio.CancelledError:
                break
            except Exception as e:
                logger.error("Error en bucle de refresco de catálogo: %s", e)
                await asyncio.sleep(300)

    def nombre_riesgo(self, riesgo_id: str) -> str:
        """Devuelve el nombre legible de un riesgo."""
        for r in self.riesgos:
            if r["id"] == riesgo_id:
                return str(r.get("name", riesgo_id))
        return riesgo_id.capitalize()

    def nombre_tipo(self, tipo_id: str) -> str:
        """Devuelve el nombre legible de un tipo."""
        for t in self.tipos:
            if t["id"] == tipo_id:
                return str(t.get("name", tipo_id))
        return tipo_id.capitalize()

    def nombre_regla(self, regla_id: str) -> str:
        """Devuelve el nombre legible de una regla."""
        for r in self.reglas:
            if r["id"] == regla_id:
                return str(r.get("name", regla_id))
        return regla_id

    def orden_riesgo(self, riesgo_id: str) -> int:
        """Devuelve el índice de severidad de un riesgo (0 menor severidad)."""
        for i, r in enumerate(self.riesgos):
            if r["id"] == riesgo_id:
                return i
        return 999

    def todos_los_riesgos(self) -> list[str]:
        """Devuelve todos los identificadores de riesgos válidos."""
        return [r["id"] for r in self.riesgos]

    def todos_los_tipos(self) -> list[str]:
        """Devuelve todos los identificadores de tipos válidos."""
        return [t["id"] for t in self.tipos]

    def validar_riesgos(self, seleccionados: list[str]) -> tuple[bool, list[str]]:
        """Valida que todos los riesgos pertenezcan al catálogo."""
        validos = {r["id"].lower() for r in self.riesgos}
        normalizados: list[str] = []
        for s in seleccionados:
            s_clean = s.strip().lower()
            if not s_clean:
                continue
            if s_clean not in validos:
                return False, [s_clean]
            if s_clean not in normalizados:
                normalizados.append(s_clean)
        return bool(normalizados), normalizados

    def validar_tipos(self, seleccionados: list[str]) -> tuple[bool, list[str]]:
        """Valida que todos los tipos pertenezcan al catálogo."""
        validos = {t["id"].lower() for t in self.tipos}
        normalizados: list[str] = []
        for s in seleccionados:
            s_clean = s.strip().lower()
            if not s_clean:
                continue
            if s_clean not in validos:
                return False, [s_clean]
            if s_clean not in normalizados:
                normalizados.append(s_clean)
        return bool(normalizados), normalizados
