# Integración: MeshConfig (Configurador Web Meshtastic)

Integración y despliegue del configurador web de dispositivos Meshtastic para **Andalucía Mesh**, basado en el proyecto de código abierto [`pdxlocations/meshconfig`](https://github.com/pdxlocations/meshconfig).

## Propósito
Permitir a los usuarios configurar sus nodos Meshtastic directamente desde el navegador (vía Web Serial USB, Web Bluetooth BLE o HTTP local) o generar y descargar la configuración óptima (fichero YAML, código QR para la app móvil oficial y comandos CLI), aplicando por defecto los estándares oficiales de radio (**SFNarrow**) y buenas prácticas de la comunidad andaluza.

## Principios de la Integración
1. **Sin duplicar código ajeno en el repositorio**: La imagen Docker se construye directamente desde el repositorio upstream anclado al commit `658f46111da28d4444e45ab3eacd89fd53cc2ab3`.
2. **Actualizaciones manuales y controladas**: Cualquier salto de versión se realiza explícitamente probando compatibilidad antes de actualizar la referencia del commit.
3. **Personalizaciones desacopladas**: Las plantillas visuales ([`DESIGN.md`](../../docs/info/DESIGN.md)), los presets oficiales y la lógica del asistente interactivo residen en `custom/` y `configs/` y se montan como volúmenes de solo lectura (`:ro`) en el contenedor. Catalogado formalmente en [`docs/info/customizations.md`](../../docs/info/customizations.md) como `CUST-03`.

## Estructura de Ficheros

```text
integrations/meshconfig/
├── .env.example              Variables de entorno de ejemplo
├── compose.yaml              Definición de servicio Docker Compose (anclado a commit)
├── README.md                 Este documento
├── configs/                  Presets YAML oficiales de Andalucía Mesh
│   ├── andalucia-sfnarrow-client-mute.yaml  (Predeterminado: móvil/bolsillo, 4 saltos, pos 6h, nodeinfo 72h)
│   └── andalucia-sfnarrow-client.yaml       (Base exterior: azotea, 3 saltos, pos 72h, nodeinfo 72h)
└── custom/                   Adaptaciones visuales y funcionales de Andalucía Mesh
    ├── index.html            Estructura HTML oxigenada con doble modo (Asistente y Avanzado)
    ├── styles.css            Estilos Material acordes a DESIGN.md (modo claro/oscuro)
    └── configurador.js       Motor JS (WebSerial, BLE, QR dinámico, selector provincial)
```

## Configuración de Entorno

| Variable | Por defecto | Descripción |
|---|---|---|
| `MESHCONFIG_PORT` | `8420` | Puerto en `127.0.0.1` donde escucha el contenedor Nginx |
| `CONFIG_DOMAIN` | `config.mesh.example.org` | Dominio virtual para el reverse proxy de Nginx |

## Despliegue Local y Producción

Para compilar la imagen y levantar el servicio en Docker:

```bash
cd integrations/meshconfig
docker compose up -d --build
```

El servicio responde en `http://127.0.0.1:8420/`.

---
> Creado: 2026-10-08 · Última revisión: 2026-10-08
