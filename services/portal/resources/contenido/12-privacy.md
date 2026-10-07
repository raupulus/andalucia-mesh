# 12 · Privacidad

> `/legal/privacidad` · Explicar qué datos trata el proyecto, de dónde salen, para qué, cuánto tiempo, con quién se comparten, cómo ejercer derechos y cómo no aparecer · `../10-legal-privacy.md`

## SEO

- **Título:** `Política de privacidad · {PROJECT_NAME}` (44 caracteres)
- **Descripción:** `Qué datos de la malla y de las visitas trata {PROJECT_NAME}, para qué, durante cuánto tiempo, con quién se comparten y cómo no aparecer.` (141)

## Estructura

| # | Sección | Componente (`DESIGN.md`) |
|---|---|---|
| 1 | H1, fecha y resumen | Tipografía H1 + texto secundario + lista |
| 2 | Responsable | Texto corrido |
| 3 | Qué datos tratamos y de dónde salen | Tablas |
| 4 | Para qué | Texto corrido con lista |
| 5 | Base jurídica | Texto corrido con lista |
| 6 | Cuánto tiempo | Tablas |
| 7 | Con quién se comparten | Texto corrido con lista |
| 8 | Transferencias fuera de la UE | Texto corrido |
| 9 | Tus derechos | Texto corrido |
| 10 | Cómo no aparecer | Texto corrido (destacado con chip `info` "Importante") |
| 11 | Si visitas este portal | Texto corrido |
| 12 | Cambios | Texto corrido |

Enlazada desde el pie de todas las páginas y desde Configura tu nodo, Conecta tu gateway y Bots (RF-PO-LG-1, RF-PO-LG-2).

## Borrador del texto

> **Borrador orientativo.** Este texto no sustituye la revisión de un profesional del derecho. Revisarlo antes de publicar.

Cada `###` es un H2 de la página.

**H1:** Política de privacidad

Última actualización: [fecha de publicación]

En resumen:

- Mostramos lo que los nodos emiten por radio en canales públicos y permiten subir con "OK to MQTT".
- Las páginas de este portal no usan cookies ni rastreadores.
- Si no quieres aparecer, desactiva "OK to MQTT" en tu nodo.
- Para cualquier petición sobre tus datos: {PROJECT_CONTACT}.

### 1. Responsable

Raúl Caro Pastorino (@raupulus). Contacto: {PROJECT_CONTACT}.

### 2. Qué datos tratamos y de dónde salen

La malla es pública, pero algunos de sus datos pueden identificar a una persona.

| Dato | De dónde sale | Dónde se ve |
|---|---|---|
| Id del nodo y sus nombres largo y corto | Lo que emite el nodo por radio, subido por gateways voluntarios | PotatoMesh, MeshView, rankings, alertas, bots y API |
| Posición del nodo, con la precisión que configure su dueño | Igual | PotatoMesh y MeshView. En el portal y en la API, solo como recuento por provincia |
| Mensajes de los canales públicos de la lista, incluido `sos` | Igual | PotatoMesh, MeshView y el chat en directo por WebSocket, a la vista de cualquiera e indexables por buscadores. El chat en directo no guarda nada |
| Telemetría (batería, utilización del canal, tiempo encendido…) | Igual | Servicios, rankings, alertas, bots y API |
| Nodos, mensajes y trazas de instancias públicas vecinas | Sus webs públicas, que solo leemos | PotatoMesh |
| Id de cada gateway (su usuario) | La conexión del gateway al servidor | Registros del servidor y alertas |
| Alertas con el nombre del nodo | Las genera el sistema a partir de lo anterior | Portal, API, bots de Telegram y Discord y webhooks |
| Dirección IP de quien visita las webs | La navegación | Registros del servidor y control de abusos de la API |
| Tu correo y lo que nos cuentes | Tus mensajes (alta de gateway o webhook, consultas, derechos) | Solo el responsable |
| Id del grupo o canal donde está un bot, sus filtros y los avisos enviados | Al añadir el bot | Solo el responsable |
| URL y filtros de tu webhook | Tu alta | Solo el responsable |

Lo que **no** tratamos: mensajes directos ni de canales privados (solo se descifran los canales públicos con la clave por defecto) ni ubicaciones exactas en este portal.

### 3. Para qué

- Mostrar el estado de la malla, sus estadísticas y sus rankings.
- Detectar problemas técnicos y avisar de ellos para mantener la red.
- Gestionar las altas de gateways, bots y webhooks, y contestar a tus mensajes.
- Proteger el servidor frente a abusos.

### 4. Base jurídica

El interés legítimo en hacer funcionar y proteger una red comunitaria. Lo hemos ponderado así:

- Son datos que cada nodo difunde de forma voluntaria por radio en canales públicos, y que se suben porque el propio nodo tiene activado "OK to MQTT".
- Solo se tratan canales públicos y conocidos, nunca mensajes directos.
- El portal muestra recuentos agregados, no ubicaciones.
- Los datos se conservan un tiempo limitado.
- En el canal `sos`, el registro público tiene interés para poder consultar lo que pasó en un suceso.

Para tus correos y altas, la base es atender lo que tú mismo nos pides.

### 5. Cuánto tiempo

| Dónde | Plazo |
|---|---|
| MeshView | 14 días |
| PotatoMesh | 30 días |
| Histórico propio para estadísticas | Paquetes y recepciones, 30 días; posiciones y telemetría, 90 días |
| Estadísticas agregadas y anónimas | Sin límite |
| Rankings de periodos cerrados, con nombre de nodo | 1 año; después, solo agregados sin nodo |
| Alertas | 1 año |
| Registros del servidor (IP) | 30 días |
| Avisos enviados por los bots y webhooks | 1 año |
| Correos y datos de altas de gateways | Mientras dure el alta |

### 6. Con quién se comparten

- **Con cualquiera:** lo que muestran las webs y la API es público.
- **Telegram y Discord:** reciben las alertas que los bots envían a los grupos y canales donde se han añadido.
- **Quien da de alta un webhook:** recibe las alertas que pasan sus filtros.
- **{HOSTING_PROVIDER}:** el proveedor del servidor, situado en un centro de datos de {HOSTING_LOCATION}. Trata los datos por nuestra cuenta.
- **Proveedores de mapas:** al abrir PotatoMesh o MeshView, tu navegador descarga los mapas de OpenStreetMap y CARTO, que reciben tu dirección IP. Este portal no hace peticiones a terceros.

### 7. Transferencias fuera de la UE

El servidor está en la Unión Europea. Telegram, Discord y los proveedores de mapas pueden tratar datos fuera del Espacio Económico Europeo según sus propias condiciones.

### 8. Tus derechos

Puedes pedir el acceso, la rectificación, la supresión, la oposición o la limitación del tratamiento de tus datos escribiendo a {PROJECT_CONTACT}. Si la petición es sobre un nodo, indica su id. Si no te contestamos o no estás de acuerdo con la respuesta, puedes reclamar ante la Agencia Española de Protección de Datos (aepd.es).

### 9. Cómo no aparecer

Desactiva "OK to MQTT" en tu nodo: los gateways dejarán de subir sus paquetes. No hay lista de exclusión ni otra forma de ocultar un nodo, porque lo que se sube es lo que cada nodo permite. Lo que ya se haya subido se borra al cumplirse los plazos de arriba; si quieres que lo borremos antes, escríbenos.

Los mensajes de los canales públicos, incluido `sos`, se publican y los buscadores pueden indexarlos. No escribas en ellos nada que no quieras que se vea.

### 10. Si visitas este portal

Las páginas públicas no usan cookies, ni analítica, ni recursos de terceros. Tu preferencia de modo claro u oscuro se guarda solo en tu navegador. El servidor registra la dirección IP de cada visita por seguridad y para aplicar el límite de peticiones de la API. Más en la [política de cookies](/legal/cookies).

### 11. Cambios

Si esta política cambia, lo verás en la fecha de actualización de arriba.

## Datos dinámicos y configuración

| Dato | Origen |
|---|---|
| Responsable | `config/autoria.php` |
| Contacto | `PROJECT_CONTACT` |
| Plazos | `../../overview.md` (MeshView 14 días, PotatoMesh 30 días, `ingesta` 30/90 días + agregados) y `../10-legal-privacy.md` (alertas, 1 año). Si cambia una retención, se cambia aquí |
| Proveedores de mapas | `../../potatomesh/README.md` (OSM HOT y CARTO en PotatoMesh) |
| Texto | `resources/contenido/legal/privacidad.md` |

Notas para desarrollo:

- Verificar qué proveedor de mapas usa la versión fijada de MeshView y ajustar la sección 6 si no es OpenStreetMap o CARTO.
- La fila de "instancias públicas vecinas" sale de `sync-peers` (`../../potatomesh/sync-peers.md`); si se desactiva, se quita.

## Supuestos aplicados

- Plazos: registros del servidor con IP, 30 días; avisos enviados por los bots, 1 año; correos de alta de gateways, mientras dure el alta; rankings por nodo de periodos cerrados, 1 año y después solo agregados sin nodo.
- Se nombran OpenStreetMap y CARTO como destinatarios de la IP al cargar mapas: es información legal obligatoria, no uso de marca.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
