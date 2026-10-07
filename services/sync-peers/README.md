# Sincronizador de Mallas Vecinas (`sync-peers`)

Microservicio propio de sincronización unidireccional que consulta periódicamente las APIs REST públicas de instancias PotatoMesh vecinas configuradas en `peers.json` para Andalucía Mesh.

- **Documentación Canónica:** [`docs/info/potatomesh/sync-peers.md`](../../docs/info/potatomesh/sync-peers.md)
- **Lenguaje / Runtime:** Python 3.13 asíncrono sobre contenedor Docker Debian Trixie slim.

---

## 1. Funcionamiento y Arquitectura

1. **Lectura en caliente de `peers.json`:** Inspecciona el fichero en cada ciclo de sincronización. Si el archivo es modificado o un peer es desactivado, el cambio se aplica sin reiniciar el contenedor.
2. **Ciclos asíncronos independientes:** Cada peer configurado se consulta en su propia corrutina sin bloquear a las demás instancias.
3. **Paginación automática:** Realiza peticiones incrementales basadas en cursores temporales (`?since=...&limit=100`) descargando hasta 20 páginas consecutivas si hay actividad acumulada.
4. **Doble Despacho:**
   - **Local PotatoMesh:** Inyección REST en `http://potatomesh:41447/api/*` para visualización inmediata en mapa y chat local.
   - **Mosquitto (`snm/v1/peer/<peer_id>/<tipo>`):** Publicación MQTT con usuario `svc-potato` para ingestión regional, descarte de duplicados y generación de alertas.
5. **Cursores persistentes y transaccionales:** Los cursores se almacenan en PostgreSQL (`peersync.peer_cursor`) y solo se actualizan tras la confirmación de entrega exitosa de cada lote.
6. **Espera progresiva ante caídas:** Aplica retrocesos temporales (60 s → 5 min → 15 min) ante fallos de conexión o respuestas HTTP 5xx sin afectar a los peers sanos.
7. **Cabecera de cortesía:** Todas las peticiones HTTP remotas emiten una cabecera `User-Agent` identificando el proyecto y contacto del operador.

---

## 2. Variables de Entorno

| Variable | Descripción | Valor por Defecto |
|---|---|---|
| `PROJECT_NAME` | Nombre de la red comunitaria | `Andalucía Mesh` |
| `PROJECT_DOMAIN` | Dominio canónico del proyecto | `mesh.example.org` |
| `PROJECT_CONTACT` | Email de contacto público | `public@raupulus.dev` |
| `ALLOWED_CHANNELS` | Lista blanca de canales autorizados | `SFNarrow,Iberia,...` |
| `PRIMARY_CHANNEL` | Canal primario regional | `SFNarrow` |
| `MQTT_HOST` | Host del broker Mosquitto interno | `172.30.0.1` |
| `MQTT_PORT` | Puerto del broker Mosquitto interno | `1884` |
| `MQTT_USER` | Usuario de servicio Mosquitto | `svc-potato` |
| `MQTT_PASSWORD` | Contraseña del usuario de servicio | *(requerida)* |
| `DB_HOST` | Host de base de datos PostgreSQL | `172.30.0.1` |
| `DB_PORT` | Puerto de PostgreSQL | `5432` |
| `DB_NAME` | Nombre de la base de datos | `peersync` |
| `DB_USER` | Usuario de base de datos | `peersync` |
| `DB_PASSWORD` | Contraseña de base de datos | *(requerida)* |
| `POTATOMESH_URL` | URL base de la API local de PotatoMesh | `http://potatomesh:41447` |
| `POTATOMESH_API_TOKEN` | Token Bearer de la API local | *(requerida)* |
| `PEERS_ARCHIVO` | Ruta al fichero de configuración JSON | `/app/peers.json` |
| `CICLO_MENSAJES_S` | Intervalo de sondeo de mensajes y trazas | `60` |
| `PAGINAS_MAX` | Límite máximo de páginas por ciclo | `20` |
| `LOG_LEVEL` | Nivel de registro de eventos | `INFO` |

---

## 3. Despliegue

```bash
# 1. Copiar y configurar variables de entorno
sudo cp .env.example /srv/sync-peers/.env
sudo chmod 0600 /srv/sync-peers/.env

# 2. Crear y configurar peers.json a partir de la plantilla
sudo cp peers.example.json /srv/sync-peers/peers.json
sudo chmod 0600 /srv/sync-peers/peers.json

# 3. Desplegar mediante el script maestro
./infrastructure/deploy.sh sync-peers
```

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
