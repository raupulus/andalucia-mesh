# Portal Web, API Pública y Panel de Operador · Andalucía Mesh

Servicio web central del proyecto **Andalucía Mesh**, implementado sobre PHP 8.4 y Laravel 12. Ofrece la interfaz pública para la ciudadanía, la API REST consumida por clientes externos y bots, y el panel privado de administración y monitorización técnica de la red.

---

## Componentes y Arquitectura

1. **Portal Web Institucional y Divulgativo (`portal`):**
   - **Cero cookies:** Las rutas públicas de navegación no emiten cabeceras `Set-Cookie` ni utilizan sesiones HTTP (cumplimiento RGPD por diseño).
   - **Accesibilidad y diseño:** Sistema visual basado en `docs/info/DESIGN.md`, contraste estricto WCAG 2.1 AAA y modo claro/oscuro automático.
   - **Páginas dinámicas:** Portada con selector 24h/7d y mapa SVG interactivo de las 8 provincias andaluzas, rankings (`/rankings`), catálogo de alertas activas (`/alertas`) y herramienta de comprobación y diagnóstico de nodos (`/revisa-tu-nodo`).
   - **Contenido estático versionado:** 15 páginas gestionadas mediante Markdown en `resources/contenido/` con compilación optimizada y caché basada en `filemtime`.

2. **API REST Pública (`/api/v1`):**
   - Base canónica bajo `/api/v1` con respuestas en JSON estructurado (claves en inglés).
   - Resiliencia y tolerancia a fallos mediante disyuntor (*circuit breaker*) de 10 segundos y entrega de datos degradados (`stale: true`) si la base de datos de ingesta experimenta caídas.
   - Soporte nativo de `ETag` y respuestas `304 Not Modified`.
   - Limitación de tasa (*rate limit*) de 60 peticiones/minuto por dirección IP.

3. **Panel de Operador Técnico (`/admin`):**
   - Implementado con **Filament 5**.
   - Acceso restringido con autenticación obligatoria mediante segundo factor TOTP (Google Authenticator, Aegis, 1Password) y códigos de recuperación.
   - Widget de estado en tiempo real con refresco reactivo cada 30 segundos, auditando los 13 servicios de la red (HTTP, MQTT y PostgreSQL).
   - Bloqueo por fuerza bruta tras 5 intentos fallidos durante 15 minutos.

4. **Daemon de Tareas en Segundo Plano (`portal-tareas`):**
   - Contenedor independiente que ejecuta `php artisan schedule:work`.
   - Audita la salud de todos los microservicios cada minuto (`ComprobarServicios`), emite el latido interno en la base de datos y purga historiales antiguos (> 90 días).

---

## Comandos Artisan Específicos

| Comando | Descripción |
|---|---|
| `php artisan portal:mapa` | Regenera el componente SVG vectorial interactivo de Andalucía a partir de GeoJSON. |
| `php artisan portal:qr` | Genera los códigos QR vectoriales de difusión en `public/qr.svg` y `public/qr.pdf` (formato A6). |
| `php artisan portal:vistas` | Audita la integridad y estructura de las vistas SQL del contrato de integración. |
| `php artisan operador:crear {email} {nombre}` | Da de alta una cuenta de operador técnico con validación de clave de al menos 8 caracteres. |

---

## Despliegue con Docker Compose

El despliegue en producción consta de dos servicios orquestados en la red `mesh`:

```bash
# Construir e iniciar contenedores
docker compose up -d --build

# Ejecutar migraciones en base snm_portal
docker compose exec portal php artisan migrate --force

# Crear cuenta de operador técnico
docker compose exec -it portal php artisan operador:crear operador@desdechipiona.es "Operador Técnico"
```

Los puertos HTTP solo se exponen en `127.0.0.1:8100` en el host local, canalizados de forma segura a través del servidor web Nginx con TLS y HTTP/2.
