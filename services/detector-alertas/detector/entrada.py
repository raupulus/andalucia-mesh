"""Módulo de entrada MQTT para suscripción al flujo snm/v1/decoded/# y deduplicación."""

import asyncio
import contextlib
import logging
from datetime import UTC, datetime
from typing import Any

import aiomqtt
from pydantic import ValidationError

from detector.config import Settings
from detector.modelos import PaqueteDecodificado

logger = logging.getLogger("detector.entrada")


class EntradaMQTT:
    """Consume mensajes desde el broker MQTT, deduplica y encola para el motor de alertas."""

    def __init__(self, settings: Settings) -> None:
        """Inicializa la entrada MQTT."""
        self.settings = settings
        self.cola: asyncio.Queue[PaqueteDecodificado] = asyncio.Queue(maxsize=settings.cola_max)
        self.conectado = False
        self.ultimo_paquete_at: datetime | None = None
        self.paquetes_recibidos = 0
        self.paquetes_descartados_dedup = 0
        self.paquetes_descartados_saturacion = 0
        self.ultimo_error: str | None = None

        # Caché de deduplicación: (from_node_id, packet_id) -> timestamp_epoch
        self._dedup_cache: dict[tuple[str, int], float] = {}
        self._dedup_ttl_s: float = 600.0  # 10 minutos
        self._activo = False
        self._tarea_consumo: asyncio.Task[None] | None = None

    @property
    def tamano_cola(self) -> int:
        """Devuelve el número actual de paquetes en cola pendientes de procesar."""
        return self.cola.qsize()

    def es_duplicado(self, from_id: str, packet_id: int, ahora_epoch: float) -> bool:
        """Comprueba si el paquete ya ha sido visto en los últimos 10 minutos."""
        clave = (from_id, packet_id)
        prev_ts = self._dedup_cache.get(clave)
        if prev_ts is not None and (ahora_epoch - prev_ts) < self._dedup_ttl_s:
            return True

        self._dedup_cache[clave] = ahora_epoch

        # Limpieza periódica de caché si sobrepasa 50.000 entradas
        if len(self._dedup_cache) > 50000:
            limite = ahora_epoch - self._dedup_ttl_s
            claves_caducadas = [k for k, ts in self._dedup_cache.items() if ts < limite]
            for k in claves_caducadas:
                del self._dedup_cache[k]

        return False

    async def iniciar(self) -> None:
        """Inicia el bucle asíncrono de suscripción MQTT."""
        self._activo = True
        self._tarea_consumo = asyncio.create_task(self._bucle_consumidor())
        logger.info("Entrada MQTT iniciada hacia %s:%d", self.settings.mqtt_host, self.settings.mqtt_port)

    async def detener(self) -> None:
        """Detiene el consumidor MQTT."""
        self._activo = False
        if self._tarea_consumo:
            self._tarea_consumo.cancel()
            with contextlib.suppress(asyncio.CancelledError):
                await self._tarea_consumo
            self._tarea_consumo = None
        self.conectado = False
        logger.info("Entrada MQTT detenida.")

    async def _bucle_consumidor(self) -> None:
        """Bucle de reconexión continua y lectura del topic decoded."""
        topic_filtro = f"{self.settings.mqtt_topic_prefix}/v1/decoded/#"
        reintento_s = 1.0

        while self._activo:
            try:
                client_kwargs: dict[str, Any] = {
                    "hostname": self.settings.mqtt_host,
                    "port": self.settings.mqtt_port,
                    "identifier": self.settings.mqtt_client_id,
                }
                if self.settings.mqtt_user:
                    client_kwargs["username"] = self.settings.mqtt_user
                    client_kwargs["password"] = self.settings.mqtt_password

                async with aiomqtt.Client(**client_kwargs) as client:
                    self.conectado = True
                    self.ultimo_error = None
                    reintento_s = 1.0
                    logger.info("Conectado a MQTT broker. Suscribiendo a '%s'...", topic_filtro)
                    await client.subscribe(topic_filtro, qos=0)

                    async for message in client.messages:
                        if not self._activo:
                            break

                        self.paquetes_recibidos += 1
                        self.ultimo_paquete_at = datetime.now(UTC)

                        try:
                            # Validación y parseo Pydantic
                            payload_raw = message.payload
                            if isinstance(payload_raw, (bytes, bytearray)):
                                payload_str = payload_raw.decode("utf-8")
                            else:
                                payload_str = str(payload_raw)

                            pkt = PaqueteDecodificado.model_validate_json(payload_str)
                        except (ValidationError, Exception) as val_err:
                            logger.debug("Error validando JSON en topic %s: %s", message.topic, val_err)
                            continue

                        ahora_epoch = datetime.now(UTC).timestamp()
                        if self.es_duplicado(pkt.from_node_id, pkt.packet_id, ahora_epoch):
                            self.paquetes_descartados_dedup += 1
                            continue

                        # Gestión de contrapresión si la cola está al 80%
                        limite_80 = int(self.settings.cola_max * 0.8)
                        if self.cola.qsize() >= limite_80 and pkt.portnum in ("position", "nodeinfo"):
                            self.paquetes_descartados_saturacion += 1
                            continue

                        try:
                            self.cola.put_nowait(pkt)
                        except asyncio.QueueFull:
                            self.paquetes_descartados_saturacion += 1
                            logger.warning("Cola de entrada llena (%d). Paquete descartado.", self.settings.cola_max)

            except asyncio.CancelledError:
                break
            except Exception as e:
                self.conectado = False
                self.ultimo_error = str(e)
                logger.warning("Error de conexión MQTT: %s. Reintentando en %.1f s...", e, reintento_s)
                await asyncio.sleep(reintento_s)
                reintento_s = min(reintento_s * 1.5, 30.0)
