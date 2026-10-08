"""Carga y validación estricta de la configuración de destinos desde webhooks.yaml."""

import hashlib
import ipaddress
import os
import re
import sys
from dataclasses import dataclass
from pathlib import Path
from urllib.parse import urlparse

import yaml

from nucleo.config import ConfiguracionBase
from webhooks.red_segura import validar_url_carga

REGEX_NOMBRE = re.compile(r"^[a-z0-9-]{1,40}$")
REGEX_NODO = re.compile(r"^![0-9a-f]{8}$")
CLAVES_PERMITIDAS = {"nombre", "url", "riesgos", "tipos", "provincias", "nodos"}
PROVINCIAS_VALIDAS = {"ES-AL", "ES-CA", "ES-CO", "ES-GR", "ES-H", "ES-J", "ES-MA", "ES-SE", "FUERA"}


@dataclass
class DestinoWebhook:
    """Representa la configuración cargada de un destino registrado."""

    nombre: str
    url: str
    host: str
    url_hash: str
    riesgos: list[str] | None
    tipos: list[str] | None
    provincias: list[str] | None
    nodos: list[str] | None
    secreto: str


class ConfiguracionWebhooks(ConfiguracionBase):
    """Variables de entorno específicas del microservicio de Webhooks."""

    webhooks_archivo: str = "/app/config/webhooks.yaml"
    webhooks_timeout_s: int = 10
    webhooks_concurrencia: int = 10
    webhooks_fallos_desactivar: int = 20
    webhooks_ips_bloqueadas: str = ""
    internal_api_secret: str = ""

    def parsear_redes_bloqueadas(self) -> list[ipaddress.IPv4Network | ipaddress.IPv6Network]:
        """Convierte la cadena de IPs y subredes bloqueadas en objetos de red."""
        redes: list[ipaddress.IPv4Network | ipaddress.IPv6Network] = []
        if not self.webhooks_ips_bloqueadas.strip():
            return redes

        for item in self.webhooks_ips_bloqueadas.split(","):
            limpio = item.strip()
            if not limpio:
                continue
            try:
                redes.append(ipaddress.ip_network(limpio, strict=False))
            except ValueError as e:
                sys.stderr.write(f"ERROR: Formato de IP o red inválido en WEBHOOKS_IPS_BLOQUEADAS: {limpio} ({e})\n")
                sys.exit(1)
        return redes

    def cargar_destinos(self) -> list[DestinoWebhook]:
        """Carga y valida los destinos definidos en webhooks.yaml."""
        ruta = Path(self.webhooks_archivo)
        if not ruta.exists():
            return []

        try:
            contenido = ruta.read_text(encoding="utf-8")
            datos = yaml.safe_load(contenido) or {}
        except Exception as e:
            sys.stderr.write(f"ERROR: No se pudo leer el archivo YAML {ruta}: {e}\n")
            sys.exit(1)

        destinos_raw = datos.get("destinos", [])
        if not isinstance(destinos_raw, list):
            sys.stderr.write("ERROR: La clave 'destinos' en webhooks.yaml debe ser una lista\n")
            sys.exit(1)

        destinos: list[DestinoWebhook] = []
        nombres_vistos: set[str] = set()

        for idx, entrada in enumerate(destinos_raw):
            if not isinstance(entrada, dict):
                sys.stderr.write(f"ERROR: Entrada #{idx + 1} en webhooks.yaml debe ser un mapa\n")
                sys.exit(1)

            # Comprobar claves no permitidas
            desconocidas = set(entrada.keys()) - CLAVES_PERMITIDAS
            if desconocidas:
                sys.stderr.write(
                    f"ERROR en destino #{idx + 1}: claves no permitidas: {', '.join(sorted(desconocidas))}\n"
                )
                sys.exit(1)

            nombre = entrada.get("nombre")
            if not isinstance(nombre, str) or not REGEX_NOMBRE.match(nombre):
                sys.stderr.write(
                    f"ERROR en destino #{idx + 1}: campo 'nombre' debe coincidir con ^[a-z0-9-]{{1,40}}$: {nombre}\n"
                )
                sys.exit(1)

            if nombre in nombres_vistos:
                sys.stderr.write(f"ERROR en destino '{nombre}': nombre duplicado en webhooks.yaml\n")
                sys.exit(1)
            nombres_vistos.add(nombre)

            url = entrada.get("url")
            if not isinstance(url, str):
                sys.stderr.write(f"ERROR en destino '{nombre}': campo 'url' obligatorio\n")
                sys.exit(1)

            try:
                validar_url_carga(url)
            except ValueError as e:
                sys.stderr.write(f"ERROR en destino '{nombre}', campo 'url': {e}\n")
                sys.exit(1)

            # Comprobar secreto en variables de entorno
            clave_env_secreto = f"WEBHOOK_{nombre.upper().replace('-', '_')}_SECRETO"
            secreto = os.environ.get(clave_env_secreto)
            if not secreto or len(secreto.strip()) < 32:
                sys.stderr.write(
                    f"ERROR en destino '{nombre}': Variable de entorno {clave_env_secreto} obligatoria y con longitud >= 32\n"
                )
                sys.exit(1)

            # Filtros
            riesgos = entrada.get("riesgos")
            if riesgos is not None and not isinstance(riesgos, list):
                sys.stderr.write(f"ERROR en destino '{nombre}', campo 'riesgos': debe ser una lista de cadenas\n")
                sys.exit(1)

            tipos = entrada.get("tipos")
            if tipos is not None and not isinstance(tipos, list):
                sys.stderr.write(f"ERROR en destino '{nombre}', campo 'tipos': debe ser una lista de cadenas\n")
                sys.exit(1)

            provincias = entrada.get("provincias")
            if provincias is not None:
                if not isinstance(provincias, list):
                    sys.stderr.write(f"ERROR en destino '{nombre}', campo 'provincias': debe ser una lista\n")
                    sys.exit(1)
                for p in provincias:
                    if p not in PROVINCIAS_VALIDAS:
                        sys.stderr.write(f"ERROR en destino '{nombre}', provincia inválida: {p}\n")
                        sys.exit(1)

            nodos = entrada.get("nodos")
            if nodos is not None:
                if not isinstance(nodos, list):
                    sys.stderr.write(f"ERROR en destino '{nombre}', campo 'nodos': debe ser una lista\n")
                    sys.exit(1)
                for n in nodos:
                    if not isinstance(n, str) or not REGEX_NODO.match(n):
                        sys.stderr.write(f"ERROR en destino '{nombre}', identificador de nodo inválido: {n}\n")
                        sys.exit(1)

            host = str(urlparse(url).hostname)
            url_hash = hashlib.sha256(url.encode("utf-8")).hexdigest()

            destinos.append(
                DestinoWebhook(
                    nombre=nombre,
                    url=url,
                    host=host,
                    url_hash=url_hash,
                    riesgos=[r.lower() for r in riesgos] if riesgos else None,
                    tipos=[t.lower() for t in tipos] if tipos else None,
                    provincias=provincias,
                    nodos=nodos,
                    secreto=secreto.strip(),
                )
            )

        return destinos
