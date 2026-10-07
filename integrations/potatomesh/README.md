# Integración PotatoMesh

Integración del visor web y chat comunitario **PotatoMesh** para Andalucía Mesh.

- **Repositorio Upstream:** [l5yth/potato-mesh](https://github.com/l5yth/potato-mesh)
- **Imagen Fijada:** `ghcr.io/l5yth/potato-mesh-web-linux-amd64:0.7.5` (`@sha256:2ed2284f6eb96ba4473c45444f1e592a7d51a403b7ea8552f1b9c8eb20012927`)
- **Documentación Canónica:** [`docs/info/potatomesh/README.md`](../../docs/info/potatomesh/README.md)

---

## 1. Arquitectura y Componentes

PotatoMesh actúa como visor comunitario accesible con soporte para:
- Visualización cartográfica de nodos, enlaces directos y telemetría de entorno.
- Mensajería pública de canales de la malla con transmisión en vivo por Server-Sent Events (SSE).
- API REST interna protegida por Bearer token (`POTATOMESH_API_TOKEN`) para inyección de paquetes desde `adaptador-potato` y `sync-peers`.
- Base de datos embebida SQLite (`mesh.db`) en modo WAL (`/srv/potatomesh/datos/mesh.db`).

---

## 2. Puertos y Exposición

- **Puerto Local:** Enlazado estrictamente a `127.0.0.1:41447` (no expuesto a interfaces externas).
- **Publicación Pública:** Gestionada por Nginx nativo en `https://potato.mesh.${PROJECT_DOMAIN}` con soporte de streaming SSE deshabilitando el búfer de proxy (`proxy_buffering off;`).

---

## 3. Retención de Datos y Mantenimiento SQLite

Para evitar el crecimiento ilimitado de SQLite y mantener la retención a 30 días fijada en la arquitectura del proyecto:
- Se utiliza el script `limpieza.sql`.
- Se ejecuta periódicamente mediante una unidad timer de systemd en el host (`snm-potato-limpieza.timer`) a las 03:30 h todos los días.

---

## 4. Procedimiento de Despliegue

```bash
# 1. Crear directorios persistentes con UID 1000 (usuario potatomesh de la imagen)
sudo install -d -o 1000 -g 1000 -m 0700 /srv/potatomesh/datos /srv/potatomesh/config

# 2. Configurar variables de entorno con permisos restrictivos
sudo cp .env.example /srv/potatomesh/.env
sudo chmod 0600 /srv/potatomesh/.env

# 3. Desplegar mediante el script maestro
./infrastructure/deploy.sh potatomesh
```

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
