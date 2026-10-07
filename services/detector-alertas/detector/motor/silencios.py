"""Gestor de silencios temporales para nodos y reglas."""

import logging
from dataclasses import dataclass
from datetime import UTC, datetime

logger = logging.getLogger(__name__)


@dataclass(frozen=True)
class ReglaSilencio:
    """Definición de un silencio temporal."""

    nodo: str  # id de nodo o 'all'
    regla: str | None  # None = aplica a todas las reglas
    hasta: datetime
    motivo: str = ""


class GestorSilencios:
    """Gestiona la lista de silencios activos y evalúa si un evento está silenciado."""

    def __init__(self, silencios_raw: list[dict[str, str | None]] | None = None) -> None:
        """Inicializa los silencios a partir de la lista cruda de reglas.yaml."""
        self.silencios: list[ReglaSilencio] = []
        if silencios_raw:
            self.cargar_silencios(silencios_raw)

    def cargar_silencios(self, silencios_raw: list[dict[str, str | None]]) -> None:
        """Parsea y carga la lista de silencios activos."""
        parsed: list[ReglaSilencio] = []
        for item in silencios_raw:
            try:
                nodo = str(item.get("nodo") or "all")
                regla = item.get("regla")
                hasta_str = str(item["hasta"])
                hasta_dt = datetime.fromisoformat(hasta_str.replace("Z", "+00:00"))
                if hasta_dt.tzinfo is None:
                    hasta_dt = hasta_dt.replace(tzinfo=UTC)
                motivo = str(item.get("motivo") or "")

                parsed.append(ReglaSilencio(nodo=nodo, regla=regla, hasta=hasta_dt, motivo=motivo))
            except Exception as e:
                logger.warning("Entrada de silencio inválida descartada (%s): %s", item, e)
        self.silencios = parsed

    def esta_silenciado(self, nodo_id: str, regla_id: str, ahora: datetime) -> bool:
        """Comprueba si un nodo o regla particular está bajo silencio temporal activo."""
        ref_time = ahora if ahora.tzinfo else ahora.replace(tzinfo=UTC)

        for s in self.silencios:
            if s.hasta < ref_time:
                continue
            if s.nodo not in (nodo_id, "all"):
                continue
            if s.regla is not None and s.regla != regla_id:
                continue
            return True
        return False
