# Integración MeshView

Integración del visor técnico comunitario **MeshView** para Andalucía Mesh.

- **Repositorio Upstream:** [pablorevilla-meshtastic/meshview](https://github.com/pablorevilla-meshtastic/meshview)
- **Versión Fijada:** `3.0.8` (`@sha256:06edaaff53e8a25a2267a0b55de591ec950809e7880f7696a26712b63643158f`)
- **Licencia:** AGPL-3.0 (utilizado sin modificar el código fuente del contenedor)
- **Documentación Canónica:** [`docs/info/meshview/README.md`](../../docs/info/meshview/README.md)

---

## 1. Arquitectura y Despliegue

MeshView se ejecuta en dos fases dentro del mismo archivo `compose.yaml`:
1. `meshview-config`: Contenedor efímero que genera `/srv/meshview/datos/config/config.ini` con permisos `0600` sustituyendo de forma atómica las variables de entorno sin exponer secretos en el repositorio Git.
2. `meshview`: Contenedor principal que se conecta a PostgreSQL 17 (`snm_meshview`), aplica migraciones Alembic automáticamente y se suscribe a `msh/EU_868/#` en Mosquitto (`svc-meshview`).

---

## 2. Puertos y Exposición

- **Puerto Local:** Enlazado estrictamente a `127.0.0.1:8081` (no expuesto directamente al exterior).
- **Publicación Pública:** Gestionada por Nginx nativo en `https://meshview.${PROJECT_DOMAIN}` mediante el sitio `/etc/nginx/sites-available/snm-meshview.conf`.

---

## 3. Procedimiento de Despliegue

```bash
# 1. Crear directorios persistentes con UID 10001 (usuario app de la imagen)
sudo install -d -o 10001 -g 10001 -m 0700 /srv/meshview/datos/config /srv/meshview/datos/logs

# 2. Configurar variables de entorno con permisos restrictivos
sudo cp .env.example /srv/meshview/.env
sudo chmod 0600 /srv/meshview/.env

# 3. Desplegar mediante el script maestro
./infrastructure/deploy.sh meshview
```

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
