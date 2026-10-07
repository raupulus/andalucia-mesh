# Decisiones Técnicas

Registro de decisiones deliberadas de arquitectura, diseño y convenciones técnicas que deben mantenerse estables y no revertirse sin consenso justificado.

## Registro de Decisiones

| ID | Decisión | Motivo |
|---|---|---|
| DT-01 | Reutilizar PotatoMesh y MeshView; solo se desarrolla lo que no existe | Menos código propio que mantener |
| DT-02 | Servicios independientes en Docker, cada uno con su compose; se comunican solo por MQTT, HTTP o el socket de alertas | Desplegar y fallar por separado |
| DT-03 | Código de terceros fuera del repositorio: solo referencia, versión fijada (la última probada), configuración y código de adaptación | Actualizar cambiando una etiqueta, sin forks |
| DT-04 | PostgreSQL nativo del servidor para todo lo propio, una base y un rol por servicio; nada de SQLite ni ficheros de estado en lo propio | Un solo motor, copias simples |
| DT-05 | Ninguna pieza lee la base de otra; única excepción: el portal lee vistas `api_*` de `ingest` y `alertas` con roles de solo lectura | Contratos explícitos y estables |
| DT-06 | Broker solo de subida: ningún gateway ni cliente de internet puede leer ni escribir hacia la radio | Seguridad de la malla |
| DT-07 | Sin bridges ni dependencias de brokers o servicios de otras comunidades | Independencia |
| DT-08 | Sin exclusión de nodos (opt-out) ni feed filtrado: quien no quiere aparecer desactiva OK to MQTT | Lo subido ya es lo que el nodo permite |
| DT-09 | Chat de los canales públicos de la lista, visible e indexable; WebSocket de solo lectura por canal | Datos ya públicos por radio |
| DT-10 | Alertas solo por bots y webhooks; sin SSE ni difusión pública de eventos | Tiempo real donde aporta |
| DT-11 | Nombre, dominio, contacto, canales, radio y mapa siempre por variables de entorno (`PROJECT_NAME`, `PROJECT_DOMAIN`…) | Reutilizable por cualquier instancia |
| DT-12 | Lo público no usa nombres, logos ni insignias de terceros. Excepciones: PotatoMesh, MeshView, Telegram, Discord, enlaces oficiales de firmware y apps, y lo que exigen la ley o las licencias (proveedores en privacidad, créditos de software, "© Instituto Geográfico Nacional") | Identidad propia |
| DT-13 | Saturación de provincia = 0,6 × media de routers + 0,4 × media conjunta de `CLIENT` y `CLIENT_BASE`; `CLIENT_MUTE` no cuenta | Los `CLIENT_MUTE` suelen estar en interior y miden menos ocupación |
| DT-14 | Router = rol en `INFRA_ROLES` (`ROUTER`, `ROUTER_LATE`, `REPEATER`); nodo de infraestructura = router o gateway | Definición única para mapa, detector y bots |
| DT-15 | Imágenes propias construidas en el servidor; sin CI ni registro de imágenes | Simplicidad |
| DT-16 | Copias de seguridad fuera del proyecto: las gestiona cada operador | No duplicar sistemas existentes |
| DT-17 | Retención: bruto 30 días, posiciones y telemetría 90, agregados por nodo 1 año, sin nodo indefinidos; alertas y avisos 1 año; PotatoMesh 30 días; MeshView 14 días | Privacidad y disco |
| DT-18 | Panel de operadores en Filament (`/admin`); sin página de estado pública | Una página de estado en el mismo servidor no sirve si cae |
| DT-19 | Textos del portal bajo CC BY 4.0, como los datos de la API | Reutilización con atribución |
| DT-20 | Estructura de código en inglés (vistas, tablas, columnas, variables, claves, funciones, archivos); contenedores, documentación y comentarios en español. Los identificadores que aún figuren en español en `docs/info/` se traducen antes de implementarlos | Código uniforme; nombres de servicio legibles para el operador |
| DT-21 | Todo el código tipado y comentado en español de España con el formato estándar de cada lenguaje (PHPDoc, docstrings PEP 257, JSDoc…) | Mantenibilidad |
| DT-22 | Proxy inverso = Nginx nativo del servidor con certbot (80/443 y `stream` en 8883 → `127.0.0.1:1883`); los contenedores publicados solo en `127.0.0.1`; sin Traefik ni red `proxy` | El servidor ya tiene Nginx en 80/443; una pieza menos |
| DT-23 | Hosts de servicios web con proxy CDN (Cloudflare) admiten desacoplamiento de nivel de subdominio (ej. `MESHVIEW_DOMAIN` independiente) | El certificado gratuito de Cloudflare solo cubre un nivel (`*.dominio.tld`). Evita errores SSL cuando un servicio web se publica con proxy naranja. |

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
