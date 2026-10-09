# Registro de Errores Graves e Incidentes (Post-Mortem)

Este documento registra los incidentes críticos, fallos graves y análisis post-mortem del sistema, detallando la causa raíz técnica, el impacto, las acciones de mitigación inmediatas y las salvaguardas preventivas definitivas para garantizar que no vuelvan a ocurrir.

---

## Catálogo de Incidentes

| ID | Fecha | Componente | Severidad | Resumen | Estado |
|---|---|---|---|---|---|
| `INC-01` | 2026-10-08 | PotatoMesh / Limpieza Host | Crítico | Vaciado accidental de la tabla `nodes` en SQLite por incompatibilidad de tipos `INTEGER < TEXT` durante la purga automática de las 03:30 h | Resuelto / Salvaguardado |
| `INC-02` | 2026-10-09 | adaptador-potato / ingesta | Alta | Nodos Meshtastic atascados como `CLIENT_HIDDEN` por fallo al acceder a `mesh_pb2.Config` (`AttributeError`), evaluación de `Role.CLIENT == 0` y discrepancia camelCase en telemetría | Resuelto / Salvaguardado |

---

## Detalle de Incidentes

### INC-01 · Vaciado completo de la tabla `nodes` en PotatoMesh SQLite por comparación de tipos en `limpieza.sql`

- **Fecha y hora del incidente:** 2026-10-08 03:30:06 CEST (01:30:06 UTC)
- **Componente:** `integrations/potatomesh/limpieza.sql` y `infrastructure/host/snm-potato-limpieza.service`
- **Severidad:** **Crítico** (pérdida temporal de inventario histórico de nodos en la base de datos de producción)

#### 1. Síntomas observados
- El usuario reportó que el censo de nodos en la interfaz web de PotatoMesh cayó abruptamente de ~2.164 nodos (registrados tras la migración del día anterior) a ~300-370 nodos.
- La consulta SQL directa `SELECT count(*) FROM nodes;` en `/srv/potatomesh/datos/mesh.db` devolvía únicamente 374 nodos, mientras que las tablas de `messages` (2.564), `traces` (9.122) y `positions` (13.164) permanecían con sus volúmenes intactos.
- Todos los nodos presentes en la base de datos tenían `last_heard >= 2026-10-08 01:30:00 UTC` (es decir, capturados exclusivamente después de las 03:30 h).

#### 2. Causa Raíz Técnica
1. **Afinidad de tipos en SQLite (Regla TR-12):**
   El script [`integrations/potatomesh/limpieza.sql`](../../integrations/potatomesh/limpieza.sql), ejecutado a diario a las 03:30 h por el temporizador systemd `snm-potato-limpieza.timer`, contenía la siguiente instrucción de purga:
   ```sql
   DELETE FROM nodes WHERE last_heard < datetime('now', '-30 days');
   ```
2. **Incompatibilidad de tipos de datos:**
   - La columna `last_heard` en PotatoMesh v0.7.5 es de tipo numérico entero (`INTEGER`), conteniendo marcas de época UNIX en segundos (ej. `1791443015`).
   - La función `datetime('now', '-30 days')` devuelve una cadena de texto formateada (`TEXT`, ej. `'2026-09-08 01:30:00'`).
   - En SQLite, la regla de ordenación de tipos establece que **cualquier número numérico o entero es estrictamente menor que cualquier valor de texto** (`NULL < INTEGER/REAL < TEXT < BLOB`).
   - Por tanto, la expresión `1791443015 < '2026-09-08 01:30:00'` evalúa como `1` (`TRUE`) para **todas y cada una de las filas de la tabla**.
3. **Disparo automático:**
   A las 03:30:06 AM, systemd ejecutó el script y vació íntegramente la tabla `nodes`. Las tablas de `messages`, `positions`, `telemetry` y `traces` no fueron borradas en ese ciclo únicamente porque fallaron con un error sintáctico previo (`no such column: created_at` en lugar de `rx_time`), abortando el script antes de su ejecución.
4. **Por qué había ~370 nodos:**
   Eran los nodos vivos retransmitiendo por radio que el receptor local y `adaptador-potato` habían reingestado de forma legítima durante las 6 horas transcurridas entre las 03:30 h y las 09:25 h.

#### 3. Impacto
- Pérdida temporal de visibilidad de 1.790 nodos en PotatoMesh (los nodos que no habían emitido paquetes entre las 03:30 h y las 09:25 h).
- Cero pérdida en el bus MQTT, en el servicio de `ingesta` ni en TimescaleDB / PostgreSQL (que operan de forma independiente y aislada).
- Cero corrupción de ficheros SQLite (el modo WAL y las cabeceras quedaron intactos).

#### 4. Mitigación Inmediata y Restauración
1. **Fusión y recuperación de datos:**
   Se cargó el volcado limpio verificado de la migración (`mesh_checkpointed_clean.sqlite`) y se fusionó en caliente con la base de datos activa:
   - Se preservaron las actualizaciones de estado, posiciones y marcas temporales más recientes de los 376 nodos capturados durante la mañana.
   - Se reinsertaron mediante `INSERT OR IGNORE` los 1.788 nodos históricos eliminados.
   - Censo final verificado en `mesh.db`: **2.164 nodos** (1.895 escuchados en los últimos 7 días, 2.164 en los últimos 14/30 días).
2. **Corrección de `integrations/potatomesh/limpieza.sql`:**
   Se corrigió el script de limpieza para utilizar marcas enteras UNIX y los nombres reales de columna del esquema de PotatoMesh v0.7.5:
   ```sql
   PRAGMA foreign_keys = ON;

   DELETE FROM messages WHERE rx_time < unixepoch('now', '-30 days');
   DELETE FROM positions WHERE rx_time < unixepoch('now', '-30 days');
   DELETE FROM telemetry WHERE rx_time < unixepoch('now', '-30 days');
   DELETE FROM traces WHERE rx_time < unixepoch('now', '-30 days');
   DELETE FROM trace_hops WHERE trace_id NOT IN (SELECT id FROM traces);
   DELETE FROM neighbors WHERE rx_time < unixepoch('now', '-30 days');
   DELETE FROM nodes WHERE last_heard IS NOT NULL AND last_heard < unixepoch('now', '-30 days');

   PRAGMA wal_checkpoint(TRUNCATE);
   ```
3. **Verificación en el servidor:**
   Se ejecutó manualmente el servicio `snm-potato-limpieza.service` en el host `loki`. Finalizó con código `0` (`SUCCESS`) y el censo de nodos permaneció invariable en 2.164.

#### 5. Medidas Preventivas Definitivas (Salvaguardas)
1. **Regla permanente TR-12 incorporada a [`AGENTS.md`](../../AGENTS.md):**
   Queda terminantemente prohibido comparar marcas temporales enteras (`rx_time`, `last_heard`, etc.) con funciones de texto como `datetime(...)` en cualquier script SQL para SQLite. Siempre debe utilizarse `unixepoch('now', '-N days')` o `strftime('%s', ...)` convertido a entero.
2. **Alineación de esquema estricta (UT-04.4):**
   Documentada en [`docs/info/potatomesh/README.md`](potatomesh/README.md) la obligación de verificar el esquema real de columnas (`PRAGMA table_info`) antes de modificar consultas DML sobre bases de datos embebidas de terceros.
3. **Corrección de payload en `sync-peers`:**
   Se corrigió el método `_sync_nodes` en [`services/sync-peers/src/syncer.py`](../../services/sync-peers/src/syncer.py) para que transforme listas JSON en diccionarios `{node_id: node_data}`, evitando que PotatoMesh rechace los lotes de nodos remotos con HTTP 400.

---

### INC-02 · Nodos atascados como `CLIENT_HIDDEN` y telemetría desincronizada en PotatoMesh

- **Fecha y hora del incidente:** 2026-10-09 02:25:00 CEST (00:25:00 UTC)
- **Componente:** `services/adaptador-potato/src/mqtt.py`, `services/ingesta/src/decoder.py`
- **Severidad:** **Alta** (degradación masiva de metadatos de nodos nuevos, roles de infraestructura no reconocidos y telemetría no reflejada en fichas de nodos)

#### 1. Síntomas observados
- Proliferación generalizada de nodos con rol `CLIENT_HIDDEN` en la interfaz web de PotatoMesh tras la ingesta de tráfico de radio local vía MQTT.
- Nodos propios activos (como `Rau0` / `!5f3a3a29`) registraban sus paquetes de telemetría en la línea temporal de PotatoMesh (`Battery: 101%`, `Channel Util: 0.4%`, etc.), pero la tarjeta emergente del nodo no reflejaba estos datos y mostraba `Last seen: 1d 2h`, evidenciando desincronización entre la tabla `telemetry` y la tabla `nodes`.
- Los nodos sincronizados desde instancias remotas (`sync-peers`) mantenían sus datos correctos, mientras que los nodos nuevos recibidos en local degradaban a `CLIENT_HIDDEN`.

#### 2. Causa Raíz Técnica
1. **Mecanismo de marcadores de PotatoMesh (`ensure_unknown_node`):**
   PotatoMesh crea automáticamente un registro en la tabla `nodes` con rol por defecto `CLIENT_HIDDEN` en cuanto recibe cualquier paquete inicial (vecino, salto de traza, mensaje o telemetría) de un nodo no catalogado previamente.
2. **Fallo de deserialización Protobuf (`Role` en `config_pb2`):**
   El enum `Role` de Meshtastic se define en `config_pb2.Config.DeviceConfig.Role`. En `adaptador-potato` e `ingesta`, el código intentaba resolverlo mediante `mesh_pb2.Config.DeviceConfig.Role.Name(user.role)`:
   - Para nodos con rol distinto de cliente (`ROUTER`=2, `REPEATER`=4, etc.): provocaba un `AttributeError: module 'meshtastic.protobuf.mesh_pb2' has no attribute 'Config'`, capturado en `except` y descartando por completo el paquete `NodeInfo`. El nodo permanecía permanentemente con el rol `CLIENT_HIDDEN` del marcador.
   - Para nodos con rol cliente (`Role.CLIENT == 0`): en Python `if user.role:` evaluaba `0` como falsy, asignando `role: None`. Al enviar `POST /api/nodes`, PotatoMesh ejecutaba `role = COALESCE(excluded.role, nodes.role)`, conservando el `CLIENT_HIDDEN` previo.
3. **Discrepancia de nombrado en métricas de telemetría:**
   El endpoint interno de PotatoMesh `update_node_from_telemetry` busca las claves en formato `snake_case` (`battery_level`, `voltage`, `uptime_seconds`, `channel_utilization`, `air_util_tx`). `adaptador-potato` enviaba únicamente `camelCase` (`batteryLevel`, etc.), por lo que PotatoMesh registraba la telemetría en su tabla histórica pero omitía actualizar el registro en la tabla `nodes` y no actualizaba `last_heard`.
4. **Incompatibilidad de `MapReport`:**
   `MapReport` se intentaba instanciar desde `mesh_pb2` bajo portnum 72 (correspondiente a `ATAK_PLUGIN`), cuando en Meshtastic Protobuf pertenece a `mqtt_pb2.MapReport` bajo portnum 73 (`MAP_REPORT_APP`).

#### 3. Impacto
- Cero pérdida de paquetes crudos de radio ni de trazas en Mosquitto o TimescaleDB.
- Todos los nodos nuevos y routers de infraestructura en PotatoMesh figuraban erróneamente como `CLIENT_HIDDEN` y sin métricas directas en su ficha resumen.

#### 4. Mitigación Inmediata y Corrección
1. **Corrección de deserialización Protobuf:**  
   Se corrigió en [`services/adaptador-potato/src/mqtt.py`](../../services/adaptador-potato/src/mqtt.py) e [`services/ingesta/src/decoder.py`](../../services/ingesta/src/decoder.py) la importación de `config_pb2` y la resolución segura de `Role` con fallback `"CLIENT"` para soportar adecuadamente el enum `0`.
2. **Formateo dual de telemetría:**  
   Emisión simultánea de métricas en camelCase (para la API histórica de telemetría) y en snake_case (para la actualización directa de `nodes`).
3. **Identificadores numéricos explícitos:**  
   Adición de `num`, `node_num` y `node_id` en todas las cargas HTTP hacia PotatoMesh.
4. **Corrección de `MapReport`:**  
   Ajuste a portnum 73 y uso de `mqtt_pb2.MapReport`.
5. **Remediación y saneamiento de bases de datos (SQLite y PostgreSQL):**  
   - Se ejecutó un proceso de saneamiento cruzado contra PostgreSQL `snm_ingest`, restaurando 619 nodos degradados a sus roles legítimos en PotatoMesh (`CLIENT`, `ROUTER`, `CLIENT_MUTE`, `CLIENT_BASE`, `ROUTER_LATE`).
   - Se convirtieron 230 marcadores sintéticos residuales de `CLIENT_HIDDEN` a `CLIENT` en SQLite y 210 en PostgreSQL, eliminando por completo el rol artificial `CLIENT_HIDDEN` del sistema y desbloqueando la visibilidad de 145 nodos con coordenadas GPS y sus respectivos enlaces de red en el mapa.

#### 5. Medidas Preventivas Definitivas (Salvaguardas)
1. **Regla permanente TR-19 incorporada a [`AGENTS.md`](../../AGENTS.md):**  
   Obligación de importar siempre `config_pb2` para resolver roles de Meshtastic, comprobar `Role.CLIENT == 0` sin depender de falsedad booleana y usar portnum 73 con `mqtt_pb2.MapReport`.
2. **Suite unitaria de despacho:**  
   Creación de [`services/adaptador-potato/tests/test_dispatch.py`](../../services/adaptador-potato/tests/test_dispatch.py) cubriendo la serialización de todos los roles y estructuras de telemetría.
3. **Ampliación de tests en ingesta:**  
   Incorporación de pruebas de deserialización de roles en [`services/ingesta/tests/test_core.py`](../../services/ingesta/tests/test_core.py).

---
> Creado: 2026-10-08 · Última revisión: 2026-10-09
