# 02 · Broker MQTT

> Mosquitto que recibe lo que suben los gateways, lo reparte a los servicios internos y no devuelve nada a la radio. Cada gateway publica solo en su propio topic y solo en los 14 canales de la lista blanca. **Tipo:** Comunidad (Mosquitto nativo en el host) · **Fase:** 2 · **Complejidad:** media · **Monorepo:** `integrations/mosquitto/`

## 1. Contexto

- **Problema que resuelve:** un broker con usuario compartido y lectura/escritura en `msh/EU_868/#` permite suplantar gateways o inyectar tráfico en la radio a través de un gateway con downlink. Aquí cada gateway tiene usuario propio, solo escribe en su topic y nadie de fuera lee.
- **Objetivo:** broker propio e independiente (sin bridge con ningún broker externo), en `mqtt.${PROJECT_DOMAIN}`.
- **Consumidores:** MeshView (`svc-meshview`), adaptador-potato (`svc-potato`) e ingesta (`svc-ingest`) leen `msh/EU_868/#`; la ingesta publica `snm/v1/decoded/#`, que lee detector-alertas (`svc-detector`); el chat en directo lee `snm/v1/decoded/text` (`svc-chatws`); el panel comprueba la conexión (`svc-panel`).

## 2. Alcance

**Incluye:** servicio Mosquitto nativo en el host (`systemd: mosquitto.service`), listeners 1883, 1884 y local 1885, 8883 vía Nginx `stream` (TLS terminado, TCP hacia `127.0.0.1:1883`), archivos de contraseñas y ACL generadas desde `ALLOWED_CHANNELS`, límites, persistencia, logs, herramientas de usuarios y de pruebas de ACL, tabla de configuración del nodo para el portal.

**Fuera de esta entrega:** bridges con cualquier broker, LWT y topics de estado (el firmware no los publica), JSON del firmware (`/2/json/`), topics públicos para terceros, límite de tasa por cliente (lo detecta la regla `flood` de `../detector-alertas/README.md`), WebSockets, certificados de cliente, plugin de seguridad dinámica, decodificación (`../ingesta/`).

## 3. Stack y versiones

| Componente | Versión | Origen |
|---|---|---|
| Mosquitto | `mosquitto` de Debian 13 (trixie) | Repositorio oficial de Debian |
| TLS del 8883 | Nginx nativo, módulo `stream` (`../infrastructure/03-nginx-dns.md`) | — |
| Herramientas | Bash + `mosquitto_passwd`, `mosquitto_pub`, `mosquitto_sub` nativos | Paquete `mosquitto-clients` |
| Protocolo de gateways | MQTT 3.1.1, QoS 0, `ServiceEnvelope` protobuf cifrado | Firmware |

## 4. Contratos

### Listeners

| Listener | Puerto | Alcance | Quién | Autenticación |
|---|---|---|---|---|
| Público | 1883 | Publicado en el host (IPv4 e IPv6) | Gateways | Usuario = id del gateway (`!a1b2c3d4`) + contraseña; `passwd-gateways` + `acl-gateways` |
| Público TLS | 8883 | Nginx termina TLS → `127.0.0.1:1883` | Gateways con TLS | Igual que el 1883 |
| Interno | 1884 | Solo red `mesh` (`172.30.0.1:1884`) | Servicios | Usuario por servicio; `passwd-servicios` + `acl-servicios` |
| Local | 1885 | `127.0.0.1` del host | Host | Healthcheck, vigilancia, diagnóstico; anónimo, solo lectura (`acl-local`) |

Con `per_listener_settings true` cada listener tiene su archivo de contraseñas y su ACL: un usuario de servicio no entra por el 1883 ni un gateway por el 1884.

### Usuarios y permisos

| Usuario | Archivo | Permisos | Credenciales en |
|---|---|---|---|
| `!<id>` (cada gateway) | `passwd-gateways` | Escritura en `msh/EU_868/2/e/<canal>/<!id>` para cada canal de `ALLOWED_CHANNELS` y en `msh/EU_868/2/map/#`. **Sin lectura** | Las entrega el operador del proyecto |
| `svc-meshview` | `passwd-servicios` | Lectura `msh/EU_868/#` | `/srv/meshview/.env` (variable de `../meshview/README.md`) |
| `svc-potato` | `passwd-servicios` | Lectura `msh/EU_868/#` | `/srv/adaptador-potato/.env` |
| `svc-ingest` | `passwd-servicios` | Lectura `msh/EU_868/#`; escritura `snm/v1/decoded/#` | `/srv/ingesta/.env` |
| `svc-detector` | `passwd-servicios` | Lectura `snm/v1/decoded/#` | `/srv/detector-alertas/.env` |
| `svc-chatws` | `passwd-servicios` | Lectura `snm/v1/decoded/text` | `/srv/chat-ws/.env` (`../chat-ws/README.md`) |
| `svc-panel` | `passwd-servicios` | Ninguno: solo conecta (salud) | `/srv/portal/.env` |

### Topics

| Topic | Productor | Consumidores | Contenido |
|---|---|---|---|
| `msh/EU_868/2/e/<canal>/<!id>` | Gateways | MeshView, adaptador-potato, ingesta | `ServiceEnvelope` protobuf |
| `msh/EU_868/2/map/` | Gateways | MeshView, ingesta | Map report (`MAP_REPORT_APP`) |
| `snm/v1/decoded/<portnum>` | ingesta | detector-alertas | JSON del flujo `decoded` (`../integration.md` §6), QoS 0, sin retained |

- **Downlink imposible:** ninguna cuenta de gateway lee y ningún servicio escribe en `msh/#`; aunque un nodo tenga el downlink activado, no recibe nada que retransmitir.
- El topic de map reports no lleva el id del gateway y no se puede atar a su usuario; el `gateway_id` va dentro del sobre.
- Paquetes que el firmware sube a canales fuera de la lista (p. ej. `PKI` o un canal privado) se deniegan en silencio: QoS 0 no informa al cliente.

### ACL generadas (`tools/generate-acl.sh`)

Se generan desde `MQTT_TOPIC_ROOT`, `MQTT_TOPIC_PREFIX`, `ALLOWED_CHANNELS` (`/srv/comun/.env`). Resultado con los valores actuales:

```text
# credenciales/acl-gateways — generado, no editar
pattern write msh/EU_868/2/e/SFNarrow/%u
pattern write msh/EU_868/2/e/Iberia/%u
pattern write msh/EU_868/2/e/Andalucia/%u
pattern write msh/EU_868/2/e/Cadiz/%u
pattern write msh/EU_868/2/e/Huelva/%u
pattern write msh/EU_868/2/e/Almeria/%u
pattern write msh/EU_868/2/e/Granada/%u
pattern write msh/EU_868/2/e/Jaen/%u
pattern write msh/EU_868/2/e/Sevilla/%u
pattern write msh/EU_868/2/e/Cordoba/%u
pattern write msh/EU_868/2/e/Malaga/%u
pattern write msh/EU_868/2/e/Ceuta/%u
pattern write msh/EU_868/2/e/Melilla/%u
pattern write msh/EU_868/2/e/sos/%u
pattern write msh/EU_868/2/map/#

# credenciales/acl-servicios — generado, no editar
user svc-meshview
topic read msh/EU_868/#
user svc-potato
topic read msh/EU_868/#
user svc-ingest
topic read msh/EU_868/#
topic write snm/v1/decoded/#
user svc-detector
topic read snm/v1/decoded/#
user svc-chatws
topic read snm/v1/decoded/text
# svc-panel: sin líneas (solo conecta)

# credenciales/acl-local — anónimo, listener 1885
topic read $SYS/#
topic read msh/EU_868/#
topic read snm/v1/decoded/#
```

Los nombres de canal coinciden exactamente (mayúsculas incluidas, sin tildes). Añadir un canal = cambiar `ALLOWED_CHANNELS` (primero en `../integration.md` §12), `generate-acl.sh` y `SIGHUP`.

### Configuración del nodo gateway (para `../portal/04-gateway-connection.md`)

| Ajuste del nodo | Valor |
|---|---|
| MQTT activado | Sí |
| Dirección | `mqtt.${PROJECT_DOMAIN}` (`mqtt.mesh.example.org`); el firmware usa 1883 sin TLS y 8883 con TLS |
| Usuario / contraseña | `!<id>` del propio nodo (minúsculas, con `!`) / la entregada |
| Cifrado activado | Sí (se sube el paquete cifrado) |
| JSON activado | No |
| TLS | Opcional (8883) |
| Root topic | `msh/EU_868`, **escrito a mano** |
| Informe de mapa (map reporting) | Sí |
| Uplink | Activado en el canal primario (`SFNarrow`) y en los canales de la lista que use el nodo |
| Downlink | **Desactivado en todos los canales** |
| Canales fuera de la lista | Uplink desactivado (se denegarían) |
| LoRa: OK to MQTT | Sí |
| LoRa: Ignore MQTT | Sí |

Radio y canal 0 según (página "Configura tu nodo"). Alta: el operador escribe a `PROJECT_CONTACT` con el id de su nodo; recibe usuario y contraseña por privado.

### Salud

- Panel: conexión a `mosquitto:1884` con `svc-panel`; `CONNACK` correcto = arriba (`../integration.md` §11).
- Docker: `mosquitto_sub` al listener local leyendo `$SYS/broker/uptime`.

## 5. Configuración

| Variable | Valor o ejemplo | Común / propia | Secreto |
|---|---|---|---|
| `PROJECT_DOMAIN` | `mesh.example.org` | Común | No |
| `MQTT_TOPIC_ROOT` / `MQTT_TOPIC_PREFIX` | `msh/EU_868` / `snm` | Común | No |
| `ALLOWED_CHANNELS` / `PRIMARY_CHANNEL` | 14 canales / `SFNarrow` | Común | No |
| `MQTT_HOST` / `MQTT_PORT` | `mosquitto` / `1884` (para los servicios) | Común | No |
| `MQTT_USER` / `MQTT_PASSWORD` | `svc-…` / generada | Propia de cada servicio | Contraseña sí |
| `passwd-gateways`, `passwd-servicios` | Hashes de `mosquitto_passwd` | `/srv/mosquitto/credenciales/` | Sí |

`integrations/mosquitto/config/mosquitto.conf` (completo, enlazado o copiado a `/etc/mosquitto/conf.d/snm.conf`):

```text
per_listener_settings true

persistence true
persistence_location /var/lib/mosquitto/
autosave_interval 1800
persistent_client_expiration 1d

max_packet_size 16384
max_queued_messages 10000
sys_interval 10

log_dest syslog
log_dest stdout
log_type error
log_type warning
log_type notice
log_type information
connection_messages true
log_timestamp_format %Y-%m-%dT%H:%M:%SZ

# Público: gateways (host :1883 y destino del 8883 de Nginx)
listener 1883
allow_anonymous false
password_file /srv/mosquitto/credenciales/passwd-gateways
acl_file /srv/mosquitto/credenciales/acl-gateways
max_connections 200

# Interno: servicios (solo IP de la puerta red mesh 172.30.0.1, sin publicar hacia internet)
listener 1884 172.30.0.1
allow_anonymous false
password_file /srv/mosquitto/credenciales/passwd-servicios
acl_file /srv/mosquitto/credenciales/acl-servicios
max_connections 50

# Local del host: healthcheck, vigilancia y diagnóstico
listener 1885 127.0.0.1
allow_anonymous true
acl_file /srv/mosquitto/credenciales/acl-local
```

- `max_packet_size 16384`: un `ServiceEnvelope` real no pasa de ~600 B; el JSON `decoded` con muchas recepciones cabe. Quien lo supera es desconectado.
- `retain_available` se deja por defecto: en 2.0, con `false`, se desconecta a quien publique con retain, y un firmware antiguo podría hacerlo. UT-02.4 comprueba que no hay retenidos.
- `SIGHUP` recarga contraseñas y ACL sin cortar conexiones; **no desconecta** a un usuario borrado.

## 6. Datos

| Qué | Dónde | Copia | Retención |
|---|---|---|---|
| `mosquitto.conf` | `/srv/mosquitto/config/` (del monorepo, enlazado en `/etc/mosquitto/conf.d/snm.conf`) | git | — |
| Contraseñas, ACL generadas, `gateways.tsv` (id, fecha de alta/baja, nota sin datos personales) | `/srv/mosquitto/credenciales/` (propietario `mosquitto:mosquitto`, 0750/0640) | `config` diaria y en cada cambio | 30 días |
| Persistencia (`mosquitto.db`) | `/var/lib/mosquitto/` | No (regenerable) | — |
| Logs (conexiones con IP, desconexiones, errores) | `journalctl -u mosquitto`, `/var/log/mosquitto/` | No | Rotación logrotate |

## 7. Unidades de trabajo


| UT | Comportamiento | Detalle | Casos borde | Aceptación |
|---|---|---|---|---|
| **UT-02.1 — Configuración y servicio host** | `mosquitto.conf`, servicio `mosquitto.service` y healthcheck en 1885; arranca con archivos de contraseñas vacíos | `systemctl is-active mosquitto`; `mosquitto_sub -h 127.0.0.1 -p 1885 -t '$SYS/broker/version' -C 1` | Contraseñas o ACL ausentes → no arranca (lo evita `generate-acl.sh --init`); permisos de mundo en credenciales → aviso en el log | `active`; el 1884 solo escucha en `172.30.0.1:1884` (no en `0.0.0.0`) |
| **UT-02.2 — Generador de ACL** | `generate-acl.sh [--init]` escribe `acl-gateways`, `acl-servicios` y `acl-local` de forma atómica (temporal + `mv`) y envía `SIGHUP` (`systemctl reload mosquitto`); `--init` crea además `passwd-*` vacíos | Valida canal `^[A-Za-z0-9_-]{1,11}$`, sin duplicados, `PRIMARY_CHANNEL` presente; raíz y prefijo sin `+`, `#` ni espacios | `ALLOWED_CHANNELS` vacío o inválido → no toca los archivos y sale con error | Con los valores actuales genera exactamente las ACL de §4; repetirlo no cambia nada |
| **UT-02.3 — Gestión de usuarios** | `mqtt-users.sh` con `add '!id' [nota]`, `remove`, `rotate`, `service <svc-…>`, `list` | Id `^![0-9a-f]{8}$`; contraseña de 24 caracteres alfanuméricos mostrada una vez; `mosquitto_passwd -b …` ejecutado en el host; `systemctl reload mosquitto`; `remove` y `rotate` reinician `mosquitto.service` para cortar la sesión (segundos; todos reconectan); registro en `gateways.tsv` | Alta de un id existente o baja de uno inexistente → error; id con mayúsculas → rechazado (el topic usa minúsculas) | Un alta conecta al momento sin reiniciar; tras una baja el gateway no vuelve a entrar |
| **UT-02.4 — Pruebas de ACL** | `test-acl.sh` crea gateways temporales `!ffff0001` y `!ffff0002`, ejecuta los escenarios de §10 contra 1883 y 1884 y los borra | Clientes con `mosquitto_pub` y `mosquitto_sub` del host; observador anónimo en 1885; denegado = el observador no recibe nada en 3 s; mensajes vacíos (`-n`), que los consumidores descartan; servicios con las credenciales de `/srv/<servicio>/.env` si existen; `mosquitto_sub --retained-only -t 'msh/#' -W 3` sin resultados | Sin credenciales de un servicio → prueba "omitida", no "superada" | Todo en verde; código de salida 0 |
| **UT-02.5 — Usuarios de servicio** | Los seis `svc-*` creados con `mqtt-users.sh service`; cada contraseña al `.env` indicado en §4 | Crear el usuario antes de desplegar el servicio consumidor | Rotación: usuario → `.env` → desplegar el servicio, sin pausa | Cada servicio conecta a `172.30.0.1:1884`; ninguno obtiene permisos de otro |
| **UT-02.6 — Datos para "Conecta tu gateway"** | Tabla de configuración del nodo (§4) y procedimiento de alta entregados al portal | El portal construye dirección, root topic y canales desde `PROJECT_DOMAIN`, `MQTT_TOPIC_ROOT` y `ALLOWED_CHANNELS` | Cambio de un canal → la página lo refleja sin tocar código | Un gateway configurado solo con la página publica y sus paquetes llegan a la ingesta |

## 8. Despliegue

Servicio nativo en el host: `mosquitto.service` (paquetes Debian `mosquitto` y `mosquitto-clients`).

| Servicio | Tipo | Puertos | TLS 8883 | Healthcheck |
|---|---|---|---|---|
| `mosquitto` | Servicio systemd nativo | 1883 (`0.0.0.0`), 1884 (`172.30.0.1`), 1885 (`127.0.0.1`) | Nginx `stream` `8883 → 127.0.0.1:1883` (`infrastructure/nginx/streams/snm-mqtts.conf`) | `mosquitto_sub -h 127.0.0.1 -p 1885 -t '$SYS/broker/uptime' -C 1 -W 15` cada 30 s |

1. Requisitos: `../infrastructure/` completo (red `mesh`, Nginx con `snm-mqtts.conf` apuntando a `127.0.0.1:1883`, DNS de `mqtt.`).
2. `apt-get install -y mosquitto mosquitto-clients`.
3. `install -d -o mosquitto -g mosquitto -m 0750 /srv/mosquitto/credenciales`.
4. `/srv/repo/integrations/mosquitto/tools/generate-acl.sh --init`.
5. Enlazar o copiar `/srv/repo/integrations/mosquitto/config/mosquitto.conf` a `/etc/mosquitto/conf.d/snm.conf` y `systemctl restart mosquitto`.
6. `mqtt-users.sh service` para los seis `svc-*` (UT-02.5).
7. `test-acl.sh` en verde; prueba de 8883 desde fuera.

**Copias de seguridad:** fuera del proyecto (sistema del operador). Qué copiar: `/srv/mosquitto/credenciales/` (tras cada cambio de usuarios).

## 9. Definición de hecho

- [ ] Servicio `mosquitto.service` activo en el host; solo 1883 abierto directamente al exterior; 8883 por Nginx `stream`.
- [ ] Sin acceso anónimo en 1883, 8883 y 1884; el 1885 solo es accesible en `127.0.0.1` del host.
- [ ] ACL generadas desde `ALLOWED_CHANNELS` coinciden con §4; `test-acl.sh` en verde.
- [ ] Ningún gateway recibe nada al suscribirse; ningún servicio puede publicar en `msh/#`.
- [ ] Alta y baja de gateways sin reiniciar para el alta; la baja corta la sesión.
- [ ] Seis usuarios de servicio creados y sus contraseñas solo en los `.env`.
- [ ] Tabla de configuración del nodo entregada al portal.
- [ ] El panel ve el broker en `172.30.0.1:1884` con `svc-panel`; la vigilancia lee `$SYS` por el 1885.

## 10. Escenarios de prueba

- **Dado** el gateway `!ffff0001`, **cuando** publica en `msh/EU_868/2/e/SFNarrow/!ffff0001`, **entonces** `svc-ingest` lo recibe.
- **Dado** `!ffff0001`, **cuando** publica en `msh/EU_868/2/e/SFNarrow/!ffff0002`, **entonces** nadie lo recibe.
- **Dado** `!ffff0001`, **cuando** publica en `msh/EU_868/2/e/Madrid/!ffff0001`, en `…/Cádiz/…` o en `msh/EU_868/2/json/SFNarrow/!ffff0001`, **entonces** nadie lo recibe.
- **Dado** `!ffff0001` suscrito a `#`, **cuando** `!ffff0002` publica, **entonces** `!ffff0001` no recibe nada.
- **Dado** `svc-ingest`, **cuando** publica en `snm/v1/decoded/text`, **entonces** `svc-detector` lo recibe; **cuando** `svc-detector` publica ahí o `svc-ingest` en `msh/…`, **entonces** nadie lo recibe.
- **Dado** `svc-panel`, **cuando** conecta al 1884, **entonces** `CONNACK` correcto; al suscribirse a `#` no recibe nada.
- **Dado** `svc-ingest`, **cuando** intenta conectar al 1883, **entonces** "not authorised"; **dado** un gateway en el 1884, **entonces** igual.
- **Dado** un paquete de 20 KB, **cuando** un gateway lo publica, **entonces** es desconectado.
- **Dado** un alta nueva, **cuando** el gateway conecta, **entonces** entra sin reiniciar el broker; **dada** su baja, **entonces** su sesión se corta y no vuelve a entrar.
- **Dado** el broker reiniciado, **cuando** vuelve, **entonces** todos los servicios y gateways reconectan solos.

## 11. Riesgos y limitaciones

- **Sin límite de tasa** en Mosquitto: un gateway puede inundar; lo detecta la regla `flood` y la respuesta es `mqtt-users.sh remove` o `rotar`.
- **Map reports** no atados al usuario: cualquier gateway puede publicar un map report; el sobre lleva su `gateway_id`.
- **Credenciales en claro por el 1883** (sin TLS): una clave capturada solo permite escribir en el topic de ese gateway. Se recomienda TLS cuando el firmware lo soporte bien.
- **Denegaciones silenciosas:** con QoS 0 el gateway no sabe que se le deniega; según la versión, Mosquitto solo las registra con `log_type debug` (activarlo un rato para diagnosticar). Síntoma: gateway conectado sin datos en `api_gateways`.
- **IP de origen:** por 8883 y por IPv6 se ve una IP interna; la identificación es por usuario.
- **`per_listener_settings`:** si una versión futura lo retira, se unifican contraseñas y ACL en un archivo (la ACL ya separa usuarios).
- **Reinicio en bajas y rotaciones:** corte de segundos para todos (QoS 0, sin pérdida relevante).

## 12. Referencias

- `../integration.md` §4, §5, §11, §12. Infraestructura: `../infrastructure/README.md`, `../infrastructure/03-nginx-dns.md`.

## Decisiones de detalle

1. **`per_listener_settings true`** con contraseñas y ACL separadas para gateways (1883/8883) y servicios (1884).
2. **Listener local 1885** en `127.0.0.1` del host, anónimo y de solo lectura (`$SYS`, `msh`, `snm`): healthcheck, vigilancia de msg/s y diagnóstico desde el host, sin usuarios nuevos.
3. **Mosquitto nativo y TLS en Nginx**: Nginx `stream` termina el 8883 y entrega en `127.0.0.1:1883` (sin certificados en Mosquitto). El 1884 escucha solo en `172.30.0.1`; ufw lo limita a `br-mesh`.
4. **`max_packet_size 16384`** en lugar de 4096 B: aplica a todos los clientes y el JSON `decoded` debe caber.
5. **`retain_available` por defecto**; se verifica que nadie retiene.
6. **Bajas y rotaciones reinician el broker** (`SIGHUP` no corta sesiones); las altas solo `SIGHUP`.
7. **Contraseñas de gateway de 24 caracteres alfanuméricos**, mostradas una vez; `gateways.tsv` sin datos personales.
8. **ACL generadas** también para servicios y local, para no escribir `msh/EU_868` ni `snm` a mano.
9. **Pruebas de ACL** con gateways temporales `!ffff0001`/`!ffff0002` y mensajes vacíos.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
