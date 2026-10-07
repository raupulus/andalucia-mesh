# Adaptador MQTT → PotatoMesh (`adaptador-potato`)

Microservicio desacoplado de puente entre el tráfico MQTT crudo de la red Meshtastic y la API REST de PotatoMesh para Andalucía Mesh.

- **Documentación Canónica:** [`docs/info/potatomesh/adaptador-potato.md`](../../docs/info/potatomesh/adaptador-potato.md)
- **Lenguaje / Runtime:** Python 3.13 asíncrono sobre contenedor Docker Debian Trixie slim.

---

## 1. Funcionamiento y Arquitectura

PotatoMesh no lee MQTT de forma nativa. Este servicio actúa como puente de ingesta:

1. **Recepción MQTT:** Suscripción a `${MQTT_TOPIC_ROOT}/#` (`msh/EU_868/#`) en el broker interno (`172.30.0.1:1884`) con el usuario `svc-potato`.
2. **Filtrado inicial:** Descarta mensajes no compatibles (`/json/`, `/stat/`), mensajes directos (`to != 0xffffffff`) y canales que no figuren en `ALLOWED_CHANNELS`.
3. **Descifrado AES-CTR:** Deserializa `ServiceEnvelope` de protobuf y descifra cargas de canal mediante AES-CTR con clave por defecto `AQ==` y nonce normalizado de 16 bytes.
4. **Normalización de canal:** Asigna índice `0` al canal primario (`SFNarrow`) y orden correlativo al resto de canales autorizados.
5. **Deduplicación en memoria:** Filtro LRU con clave `(from, id)` y retención de 15 minutos (hasta 200.000 entradas) para absorber recepciones simultáneas de múltiples gateways sin saturar PotatoMesh.
6. **Despacho por lotes:** Cola desacoplada en memoria (hasta 10.000 elementos) y despachador HTTP asíncrono con envíos agrupados (hasta 100 elementos o 1 s) hacia `http://potatomesh:41447/api/*`.
7. **Punto de salud:** Endpoint HTTP en el puerto interno `8080` (`/health`) reportando métricas de cola y estado de conexión MQTT y API.

---

## 2. Variables de Entorno

| Variable | Descripción | Valor por Defecto |
|---|---|---|
| `MQTT_HOST` | Host del broker Mosquitto interno | `172.30.0.1` |
| `MQTT_PORT` | Puerto del broker Mosquitto interno | `1884` |
| `MQTT_USER` | Usuario de servicio Mosquitto | `svc-potato` |
| `MQTT_PASSWORD` | Contraseña del usuario de servicio | *(requerida)* |
| `MQTT_TOPIC_ROOT` | Raíz de topics MQTT de Meshtastic | `msh/EU_868` |
| `ALLOWED_CHANNELS` | Lista blanca de canales autorizados | `SFNarrow,Iberia,...` |
| `PRIMARY_CHANNEL` | Canal primario regional | `SFNarrow` |
| `CHANNEL_KEY_DEFAULT`| Clave por defecto en Base64 | `AQ==` |
| `POTATOMESH_URL` | URL base de la API de PotatoMesh | `http://potatomesh:41447` |
| `POTATOMESH_API_TOKEN` | Token Bearer para autenticación REST | *(requerida)* |
| `COLA_MAX` | Límite máximo de la cola en memoria | `10000` |
| `LOTE_MAX` | Tamaño máximo de lote por llamada HTTP | `100` |
| `LOTE_SEGUNDOS` | Intervalo máximo de agrupación por lote | `1.0` |
| `DEDUP_MINUTOS` | Ventana temporal de deduplicación | `15` |
| `LOG_LEVEL` | Nivel de registro de eventos | `INFO` |

---

## 3. Despliegue

```bash
# 1. Copiar y configurar variables de entorno
sudo cp .env.example /srv/adaptador-potato/.env
sudo chmod 0600 /srv/adaptador-potato/.env

# 2. Desplegar mediante el script maestro
./infrastructure/deploy.sh adaptador-potato
```

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
