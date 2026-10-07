"""Protocolos, contratos de interfaz y dataclasses del motor de reglas."""

from collections.abc import Callable, Mapping
from dataclasses import dataclass, field
from datetime import UTC, datetime
from typing import Any, ClassVar, Literal, Protocol

from pydantic import BaseModel
from ulid import StrictMonotonicPolicy, ULIDGenerator

from detector.modelos import AlertaObjeto, NodoInfoResumen, PaqueteDecodificado, TransicionMensaje
from detector.motor.bases import LineasBase
from detector.motor.estado import EstadoMotor, EstadoNodo


@dataclass(frozen=True)
class Alerta:
    """Objeto inmutable devuelto por el método comprobar de una regla."""

    regla: str
    riesgo: str
    mensaje: str
    nodo: str  # '!a1b2c3d4' o 'all'
    nodos: tuple[str, ...] = ()  # Solo poblado si nodo == 'all'
    datos: Mapping[str, Any] = field(default_factory=dict)
    tipo: str | None = None  # None = el clasificador determina el tipo


@dataclass
class AlertaAbierta:
    """Estado persistente en memoria de una alerta abierta activa."""

    id: str  # ULID original
    regla: str
    nodo: str
    riesgo: str
    tipo: str
    mensaje: str
    nodos: list[str] = field(default_factory=list)
    nodo_info: dict[str, Any] | None = None
    datos: dict[str, Any] = field(default_factory=dict)
    estado: Literal["abierta", "resuelta"] = "abierta"
    abierta_en: datetime = field(default_factory=lambda: datetime.now(UTC))
    actualizada_en: datetime = field(default_factory=lambda: datetime.now(UTC))
    resuelta_en: datetime | None = None
    reaperturas: int = 0
    evidencia_en: datetime = field(default_factory=lambda: datetime.now(UTC))
    ultima_transicion_en: datetime = field(default_factory=lambda: datetime.now(UTC))

    @property
    def clave(self) -> str:
        """Clave unívoca de deduplicación de la alerta."""
        return f"{self.regla}:{self.nodo}"

    def to_objeto_alerta(self) -> AlertaObjeto:
        """Convierte la entidad interna en el modelo serializable AlertaObjeto."""
        info = None
        if self.nodo_info:
            info = NodoInfoResumen(
                corto=self.nodo_info.get("corto"),
                largo=self.nodo_info.get("largo"),
                rol=self.nodo_info.get("rol"),
                provincia=self.nodo_info.get("provincia"),
            )

        return AlertaObjeto(
            id=self.id,
            regla=self.regla,
            riesgo=self.riesgo,
            tipo=self.tipo,
            mensaje=self.mensaje,
            nodo=self.nodo,
            nodos=list(self.nodos),
            nodo_info=info,
            datos=dict(self.datos),
            estado=self.estado,
            abierta_en=self.abierta_en.strftime("%Y-%m-%dT%H:%M:%SZ"),
            actualizada_en=self.actualizada_en.strftime("%Y-%m-%dT%H:%M:%SZ"),
            resuelta_en=self.resuelta_en.strftime("%Y-%m-%dT%H:%M:%SZ") if self.resuelta_en else None,
        )


@dataclass
class TransicionAlerta:
    """Representación de una transición emitida por el ciclo de vida."""

    transicion_id: str
    alerta_id: str
    transicion: Literal["abierta", "actualizada", "resuelta"]
    en: datetime
    alerta: AlertaObjeto

    def to_mensaje_socket(self) -> TransicionMensaje:
        """Convierte la transición a modelo NDJSON para el socket."""
        return TransicionMensaje(
            v=1,
            transicion_id=self.transicion_id,
            transicion=self.transicion,
            alerta=self.alerta,
        )


@dataclass(frozen=True)
class Tick:
    """Evento periódico de temporizador emitido cada 60 segundos."""

    ahora: datetime


@dataclass
class ConfigGeneral:
    """Parámetros de configuración general de reglas.yaml."""

    seguimiento_dias: int = 7
    historia_minima_h: int = 24
    reapertura_min: int = 60
    actualizacion_min: int = 15
    nodos_max_lista: int = 500


@dataclass
class Contexto:
    """Contexto de ejecución inyectado en cada regla durante su evaluación."""

    ahora: datetime
    evento: PaqueteDecodificado | Tick
    nodo: EstadoNodo | None
    estado: EstadoMotor
    bases: LineasBase
    config: BaseModel
    general: ConfigGeneral
    es_infraestructura: Callable[[str], bool]


class Regla(Protocol):
    """Protocolo que debe implementar toda regla de supervisión."""

    id: ClassVar[str]
    nombre: ClassVar[str]
    descripcion: ClassVar[str]
    fase: ClassVar[Literal["mvp", "ampliacion"]]
    afecta_malla: ClassVar[bool]
    suscrita_a: ClassVar[frozenset[str]]
    Config: ClassVar[type[BaseModel]]

    def comprobar(self, ctx: Contexto) -> list[Alerta]:
        """Evalúa las condiciones de apertura de la regla."""
        ...

    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool:
        """Determina si una alerta previamente abierta debe seguir activa o resolverse."""
        ...


# Registro dinámico de clases de reglas decoradas con @registrar
REGISTRO_REGLAS: dict[str, type[Regla]] = {}


def registrar(cls: type[Regla]) -> type[Regla]:
    """Decorador de clase para registrar reglas automáticamente."""
    if cls.id in REGISTRO_REGLAS:
        raise ValueError(f"Regla con id duplicado '{cls.id}' ya registrada")
    REGISTRO_REGLAS[cls.id] = cls
    return cls


_ULID_GEN = ULIDGenerator(policy=StrictMonotonicPolicy())


def generar_ulid() -> str:
    """Genera un identificador ULID monótono estrictamente creciente."""
    return str(_ULID_GEN.generate())
