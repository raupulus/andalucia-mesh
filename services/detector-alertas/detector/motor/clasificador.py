"""Clasificador de alertas en niveles de riesgo y tipos de afectación."""

from collections.abc import Iterable
from pathlib import Path
from typing import Any

import yaml


class Clasificador:
    """Clasificador de riesgo y tipo de alertas según reglas de precedencia canónicas."""

    def __init__(
        self,
        riesgos: list[dict[str, Any]],
        tipos: list[dict[str, Any]],
        infraestructura_manual: Iterable[str] | None = None,
        infra_roles: frozenset[str] | None = None,
    ) -> None:
        """Inicializa el clasificador con los riesgos y tipos permitidos."""
        self.riesgos = [r["id"] for r in riesgos]
        self.riesgos_orden = {r["id"]: idx for idx, r in enumerate(riesgos, start=1)}
        self.tipos = {t["id"] for t in tipos}
        self.infraestructura_manual = set(infraestructura_manual or [])
        self.infra_roles = infra_roles or frozenset({"ROUTER", "ROUTER_LATE", "REPEATER"})

        if "infraestructura" not in self.tipos or "clientes" not in self.tipos:
            raise ValueError("Los tipos 'infraestructura' y 'clientes' son obligatorios en clasificacion.yaml")

    @classmethod
    def from_yaml(
        cls,
        path: Path,
        infra_roles: frozenset[str] | None = None,
    ) -> "Clasificador":
        """Crea una instancia a partir del archivo clasificacion.yaml."""
        with open(path, encoding="utf-8") as f:
            data: dict[str, Any] = yaml.safe_load(f) or {}

        riesgos = data.get("riesgos", [])
        tipos = data.get("tipos", [])
        infra_manual = data.get("infraestructura_manual", [])

        return cls(
            riesgos=riesgos,
            tipos=tipos,
            infraestructura_manual=infra_manual,
            infra_roles=infra_roles,
        )

    def es_infraestructura(
        self,
        nodo_id: str,
        rol: str | None = None,
        is_gateway: bool = False,
    ) -> bool:
        """Determina si un nodo específico pertenece a la categoría de infraestructura."""
        if nodo_id in self.infraestructura_manual or is_gateway:
            return True
        return bool(rol and rol.strip().upper() in self.infra_roles)

    def clasificar(
        self,
        riesgo_propuesto: str,
        nodo_id: str,
        afecta_malla: bool = False,
        rol: str | None = None,
        is_gateway: bool = False,
        tipo_propuesto: str | None = None,
    ) -> tuple[str, str]:
        """Clasifica una alerta devolviendo la tupla validada (riesgo, tipo).

        Precedencia de tipo:
        1. nodo_id == "all" o afecta_malla == True -> "infraestructura"
        2. Es infraestructura (rol en infra_roles, gateway o infraestructura_manual) -> "infraestructura"
        3. tipo_propuesto registrado en tipos -> tipo_propuesto
        4. Por defecto -> "clientes"
        """
        # Validación estricta de riesgo
        if riesgo_propuesto not in self.riesgos_orden:
            raise ValueError(f"Riesgo desconocido '{riesgo_propuesto}'. Valores válidos: {self.riesgos}")

        # Determinación de tipo
        if nodo_id == "all" or afecta_malla or self.es_infraestructura(nodo_id, rol=rol, is_gateway=is_gateway):
            tipo_final = "infraestructura"
        elif tipo_propuesto and tipo_propuesto in self.tipos:
            tipo_final = tipo_propuesto
        else:
            tipo_final = "clientes"

        return riesgo_propuesto, tipo_final

    def comparar_riesgo(self, riesgo_a: str, riesgo_b: str) -> int:
        """Compara dos riesgos según su orden jerárquico.

        Devuelve:
            > 0 si riesgo_a > riesgo_b
            < 0 si riesgo_a < riesgo_b
            0 si riesgo_a == riesgo_b
        """
        orden_a = self.riesgos_orden.get(riesgo_a, 0)
        orden_b = self.riesgos_orden.get(riesgo_b, 0)
        return orden_a - orden_b
