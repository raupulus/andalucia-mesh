# 06.10 · Legal y privacidad

> Aviso legal, política de privacidad, política de cookies, avisos de responsabilidad y créditos. Textos en `pages/11-legal-notice.md`, `12-privacy.md` y `13-cookies.md`. Borrador orientativo: conviene revisión por un profesional del derecho.

## Objetivo

Cumplir RGPD, LOPDGDD y LSSI-CE con textos claros, explicar cómo no aparecer (OK to MQTT) y dar los créditos que exigen las licencias, sin cookies en las páginas públicas.

## Especificación

### Datos personales que se tratan

| Dato | Dónde | Riesgo |
|---|---|---|
| Id y nombres de nodo | Todos los servicios | Nombres reales o indicativos |
| Posición | PotatoMesh, MeshView; el portal solo da provincia y recuentos | Puede revelar el domicilio |
| Mensajes de los canales públicos de la lista (incluido `sos`) | PotatoMesh, MeshView y chat en directo por WebSocket (sin almacenamiento), visibles e indexables | Contenido personal |
| Telemetría | Visores y estadísticas | Bajo |
| Id de gateway (usuario MQTT) | Registros del broker | Identifica al operador |
| IP de visitantes | Sin registros de acceso en Nginx; teselas de mapa en los visores (OSM, CARTO) | Bajo |
| Alertas con nombre de nodo | Portal, API, bots de Telegram y Discord, webhooks | Publicación en plataformas de terceros |

### Aviso legal (`/legal/aviso-legal`)

- Titular: Raúl Caro Pastorino (@raupulus), contacto `PROJECT_CONTACT`. Proyecto sin ánimo de lucro; LSSI-CE de forma limitada al no haber actividad económica (revisar si cambia).
- Objeto, condiciones de uso, exclusión de responsabilidad, proyecto independiente (sin afiliación con proyectos de software, fabricantes ni otras comunidades).
- Propiedad intelectual: textos del sitio bajo CC BY 4.0 (igual que los datos de la API); diseño y logo sin licencia abierta.
- **Créditos (obligatorios):** Laravel y Filament (MIT), Nginx (BSD-2-Clause), Mosquitto (EPL-2.0/EDL-1.0), PostgreSQL (licencia PostgreSQL), TimescaleDB (Timescale License, Community), MeshView (AGPL-3.0), PotatoMesh (Apache-2.0), meshconfig (GNU GPLv3), cada uno enlazado a su proyecto; servicios propios bajo AGPL-3.0; **límites provinciales "© Instituto Geográfico Nacional"** (CNIG, CC BY 4.0, simplificados). Nombrarlos es atribución exigida, no uso de marca.

### Privacidad (`/legal/privacidad`)

- Responsable: Raúl Caro Pastorino, `PROJECT_CONTACT`.
- Finalidad: mostrar el estado de la malla, estadísticas y avisos técnicos para su mantenimiento.
- Base jurídica: interés legítimo sobre datos difundidos por radio en canales públicos y subidos con OK to MQTT activado por el propio nodo. Ponderación: solo canales públicos conocidos, sin mensajes directos, recuentos agregados en el portal, retención limitada; registro público del canal `sos` por su interés.
- Conservación:

| Dónde | Plazo |
|---|---|
| MeshView | 14 días |
| PotatoMesh | 30 días |
| Histórico propio (`ingest`) | Paquetes y recepciones 30 días; posiciones y telemetría 90 días |
| Rankings y agregados con nodo | 1 año; después solo agregados sin nodo |
| Agregados sin nodo | Sin límite |
| Alertas | 1 año |
| Avisos enviados por bots y webhooks | 1 año |
| Registros del servidor con IP | 30 días |
| Correos y datos de altas de gateways | Mientras dure el alta |

- Destinatarios: público general; Telegram y Discord para avisos (posibles transferencias fuera del EEE según sus condiciones); proveedor del servidor (`HOSTING_PROVIDER`, `HOSTING_LOCATION`); instancias públicas vecinas de PotatoMesh **solo como origen** de datos (`sync-peers` no les envía nada).
- Derechos: acceso, rectificación, supresión, oposición y limitación por correo a `PROJECT_CONTACT`; reclamación ante la AEPD.
- Cómo no aparecer: desactivar OK to MQTT. No hay otra exclusión; lo ya subido se borra al cumplir los plazos.

### Cookies (`/legal/cookies`)

- Páginas públicas sin cookies (rutas sin sesión); la preferencia de modo claro/oscuro va en almacenamiento local (técnica, sin banner).
- `/admin`: cookies técnicas de sesión y CSRF de Laravel (nombre derivado de `APP_NAME`, duración `SESSION_LIFETIME` = 8 h), exentas de consentimiento.
- Visores (PotatoMesh, MeshView): teselas de OpenStreetMap y CARTO (destinatarios de la IP) y lo que guarden en el navegador sus versiones fijadas (comprobar al fijar versión y reflejarlo).
- Analítica: no hay. Si se añade, sin cookies y autoalojada.

### Avisos de responsabilidad (pie y páginas)

No es un servicio de emergencias; sin garantía de entrega ni disponibilidad; datos "tal cual"; cada usuario responde de que su equipo cumpla la normativa radioeléctrica (868 MHz, potencia, ciclo de trabajo); los avisos automáticos son orientativos y no implican culpa del operador del nodo.

## Contratos propios

- Plazos de conservación: deben coincidir con `../overview.md`, `../potatomesh/README.md`, `../meshview/README.md`, `../ingesta/04-storage-retention.md`, `../detector-alertas/` y `../bots-webhooks/`. Si cambia uno, cambia aquí.
- Textos en `resources/contenido/legal/{aviso-legal,privacidad,cookies}.md`.

## Unidades de trabajo

- **UT-06.10.1 — Tres páginas legales.** Desde `pages/`, enlazadas en el pie de todas las páginas. *Aceptación:* accesibles desde cualquier página en un clic.
- **UT-06.10.2 — Créditos.** Lista de licencias y crédito del IGN en el aviso legal, con enlaces. *Aceptación:* el crédito "© Instituto Geográfico Nacional" aparece en el aviso legal.
- **UT-06.10.3 — Sin cookies.** Prueba automática que recorre las rutas públicas y falla si alguna devuelve `Set-Cookie`. *Aceptación:* pruebas en verde.
- **UT-06.10.4 — Coherencia de plazos.** Revisión en cada cambio de retención (lista de comprobación en el PR). *Aceptación:* la tabla coincide con las fichas.

## Escenarios de prueba

1. **Dado** cualquier página pública, **cuando** se abre con las herramientas del navegador, **entonces** no hay cookies; en `/admin`, solo las de sesión y CSRF.
2. **Dado** la política de privacidad, **cuando** se busca cómo no aparecer, **entonces** explica OK to MQTT y el correo de derechos.
3. **Dado** el aviso legal, **cuando** se buscan créditos, **entonces** aparecen las licencias del software y "© Instituto Geográfico Nacional".
4. **Dado** cualquier página del portal, **cuando** se inspecciona el HTML, **entonces** no hay coordenadas de nodos.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-09
