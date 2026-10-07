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
| DT-24 | Canalización de `sync-peers` hacia `ingesta` (`snm/v1/peer/#`) con filtro anti-duplicados (15 min) | Permite que alertas, estadísticas de TimescaleDB y chat cubran toda la región de Andalucía sin crear bucles MQTT bidireccionales con terceros. |
| DT-25 | Protección anti-scraping y centralización (rate limiting en Nginx a 60 req/min + bloqueo en Fail2ban/UFW con aviso al operador) | Evita que instancias externas extraigan datos masivamente o abusen de los endpoints; el proyecto centraliza hacia adentro. |
| DT-26 | Omisión de `persistence` redundante en `snm.conf` de Mosquitto | En Debian 13, `/etc/mosquitto/mosquitto.conf` ya declara `persistence true` y `persistence_location`. Declararlas de nuevo en `conf.d/snm.conf` genera un error fatal de configuración duplicada en Mosquitto 2.x. |
| DT-27 | Uso de `topic write` en lugar de `pattern write` para map reports en `acl-gateways` | El topic `msh/EU_868/2/map/#` no contiene `%u` ni `%c` (el gateway se identifica dentro del protobuf). `pattern` genera un warning en el broker si no contiene variables de cliente. |
| DT-28 | Ejecución no transaccional de agregados continuos en TimescaleDB | `CREATE MATERIALIZED VIEW ... WITH (timescaledb.continuous)` no puede ejecutarse dentro de un bloque explícito `BEGIN ... COMMIT`. El ejecutor de migraciones analiza las sentencias SQL y las ejecuta individualmente fuera de bloques de transacción. |
| DT-29 | Propiedad estricta de objetos en base de datos `snm_ingest` | El usuario de aplicación `snm_ingest` debe ser el propietario de todas las tablas, vistas materializadas y procedimientos almacenados (no el superusuario `postgres`) para permitir migraciones automatizadas e introspección sin elevar privilegios. |
| DT-30 | Cero cookies y sesiones en navegación pública del portal (`removeFromGroup('web', [...])`) | Privacidad absoluta para visitantes; elimina la necesidad legal de banner de cookies y simplifica la caché HTTP en CDN/proxy. Solo `/admin` emite cookies de sesión y CSRF. |
| DT-31 | Ejecución síncrona de `ComprobarServicios` en `portal-tareas` (`php artisan schedule:work`) | Auditoría de salud ejecutada inline en ~250ms en cada tick del planificador sin requerir un worker de colas (`queue:work`) dedicado. |
| DT-32 | Proxy inverso Nginx con soporte dual HTTP/HTTPS hacia los upstreams internos (Portal 8100, MeshView 8081, PotatoMesh 41447) y `trustProxies('*')` en Laravel | Permite compatibilidad con CDN/Cloudflare tanto en modo Flexible (conexión por puerto 80) como Universal/Full SSL (443) evitando bucles de redirección 301. |
| DT-33 | Carga estática explícita de módulos de reglas (`detector.reglas`) en el gestor de recarga | Al desacoplar el catálogo de reglas en módulos individuales usando decoradores `@registrar`, se requiere la importación explícita de `detector.reglas` en tiempo de inicialización para que la metaclase / decorador registre las clases antes de instanciarlas dinámicamente desde `reglas.yaml`. |
| DT-34 | Supervisión de salud del worker `portal-tareas` mediante inspección de proceso `schedule:work` | Al ser un proceso CLI continuo sin servidor HTTP que hereda la imagen FrankenPHP, el healthcheck de Docker debe comprobar `pgrep -f 'schedule:work' || exit 1` en lugar de una sonda HTTP a localhost. |
| DT-35 | Compatibilidad dual de marcas temporales en vistas contrato (`inicio_at` y `abierta_en`) | Para mantener compatibilidad estricta con controladores del portal y APIs REST externas, la vista `api_alertas` expone `a.abierta_en` y el alias `a.abierta_en AS inicio_at`. |

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07

