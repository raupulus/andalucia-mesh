# Reglas de negocio

> Objetivos del proyecto y reglas que el código **debe cumplir**. Es el documento de rumbo: ninguna implementación puede contradecirlo. Cambiar una regla exige que lo pida el responsable del proyecto y se hace **primero aquí** (con fecha en el historial), después en el documento de la pieza y en el código. Detalle técnico de cada regla en el documento enlazado.

## 1. Objetivos

| ID | Objetivo | Para quién |
|---|---|---|
| OB-01 | **Enseñar la malla**: mapa y chat en vivo, visor técnico, mapa por provincias y chat en directo | Visitantes y usuarios |
| OB-02 | **Medirla**: nodos por provincia, saturación del canal, rankings por hora, día, semana y mes, consumo de red por nodo | Usuarios y operadores |
| OB-03 | **Protegerla**: detectar problemas en tiempo real, catalogarlos por riesgo y tipo y avisar a quien los pueda resolver | Operadores de nodos y gateways |
| OB-04 | **Ayudar a configurarla bien**: guía de configuración, alta de gateways, diagnóstico de cada nodo y firmware | Cualquiera con un nodo |
| OB-05 | **Ser un punto de entrada claro** (URL o QR): entender en 10 segundos qué es y llegar en un toque a cada servicio | Visitantes |
| OB-06 | **Ser reutilizable**: cualquier comunidad puede desplegar su instancia cambiando solo configuración | Otras comunidades |

## 2. Seguridad de la malla y datos

| ID | Regla | Dónde |
|---|---|---|
| RN-01 | Nada de lo que llega por internet vuelve a la radio: el broker solo acepta subidas; ningún gateway ni cliente externo lee | [mosquitto](mosquitto/README.md) |
| RN-02 | Cada gateway tiene usuario propio y solo publica en su propio topic y en los canales de `ALLOWED_CHANNELS`; el canal primario es siempre el índice 0; solo clave por defecto | [mosquitto](mosquitto/README.md) |
| RN-03 | Solo se trata lo que el nodo permite subir (OK to MQTT). No hay exclusión de nodos ni feed filtrado: quien no quiere aparecer desactiva OK to MQTT | [10-legal-privacy](portal/10-legal-privacy.md) |
| RN-04 | Nunca se publican mensajes directos (PKI ni texto con destinatario); solo difusión en canales de la lista | [ingesta](ingesta/06-decoded-stream.md), [chat-ws](chat-ws/README.md) |
| RN-05 | El portal y la API nunca exponen coordenadas: solo provincia y recuentos | [portal](portal/README.md) |
| RN-06 | Las páginas públicas no usan cookies ni hacen peticiones a terceros | [portal](portal/README.md) |
| RN-07 | Retención: bruto 30 días; posiciones y telemetría 90; agregados con nodo 1 año; sin nodo indefinidos; alertas y avisos enviados 1 año; PotatoMesh 30 días; MeshView 14 días | [04-storage-retention](ingesta/04-storage-retention.md) |
| RN-08 | Independencia: sin bridges ni dependencias de brokers o servicios de otras comunidades; `sync-peers` solo **lee** APIs públicas de otras instancias | [sync-peers](potatomesh/sync-peers.md) |

## 3. Definiciones y cálculos

| ID | Regla | Dónde |
|---|---|---|
| RN-10 | Router = rol en `INFRA_ROLES` (`ROUTER`, `ROUTER_LATE`, `REPEATER`). Nodo de infraestructura = router o gateway. `CLIENT_BASE` no es infraestructura | [integration §13](integration.md) |
| RN-11 | Saturación de provincia = 0,6 × media de routers + 0,4 × media conjunta de `CLIENT` y `CLIENT_BASE`; `CLIENT_MUTE` no cuenta; último dato de cada nodo en 12 h; nodos situados en la provincia por coordenadas; si falta un grupo, su peso pasa al otro | [02-province-map](portal/02-province-map.md) |
| RN-12 | Colores del mapa: verde ≤ 20 %, naranja > 20 % y < 40 %, rojo ≥ 40 %, gris sin datos. El mapa muestra bajo la leyenda cómo se calcula | [02-province-map](portal/02-province-map.md) |
| RN-13 | Mapa: ventana de 7 días por defecto, opción de 24 h | [02-province-map](portal/02-province-map.md) |
| RN-14 | Rankings por hora, día, semana y mes en hora local, periodo en curso y anterior; no se suman días para obtener semanas o meses | [12-stats-api](portal/12-stats-api.md) |
| RN-15 | Todos los recuentos se presentan como un mínimo (solo lo que llega con OK to MQTT) | [overview](overview.md) |

## 4. Configuración recomendada de nodos

| ID | Regla | Dónde |
|---|---|---|
| RN-20 | Radio recomendada (configurable): EU_868, sin preset, BW 62, SF 7, CR 5, slot 4 (869,618 MHz), canal 0 `SFNarrow`, 3 saltos | [03-node-setup-guide](portal/03-node-setup-guide.md) |
| RN-21 | Roles: `CLIENT_MUTE` por defecto, `CLIENT` en exterior bien situado, `ROUTER` solo coordinado | Ídem |
| RN-22 | Saltos: 3; 4–5 válidos en casos justificados; ≥ 6 genera alerta | Ídem, [02-rule-catalog](detector-alertas/02-rule-catalog.md) |
| RN-23 | Intervalos: NodeInfo 72 h; posición fija 72 h, móvil ≥ 1 h; telemetría de dispositivo solar ≥ 4 h, troncal ≥ 6 h, enchufado desactivada; entorno desactivada o > 4 h | [03-node-setup-guide](portal/03-node-setup-guide.md) |
| RN-24 | Una sola fuente de umbrales: guía, diagnóstico ("Revisa tu nodo") y detector usan los mismos valores; un nodo que sigue la guía no recibe alertas de configuración ni hallazgos | [13-nodes-alerts-api](portal/13-nodes-alerts-api.md) |

## 5. Alertas y avisos

| ID | Regla | Dónde |
|---|---|---|
| RN-30 | Riesgos `bajo`, `medio`, `alto` y tipos `infraestructura`, `clientes`, ampliables solo por configuración | [detector-alertas](detector-alertas/README.md) |
| RN-31 | Tipo `infraestructura` si el nodo es de infraestructura o la alerta afecta a la malla; si no, `clientes` | [01-rule-engine](detector-alertas/01-rule-engine.md) |
| RN-32 | Reglas MVP y sus umbrales: catálogo de reglas (forma parte de estas reglas de negocio) | [02-rule-catalog](detector-alertas/02-rule-catalog.md) |
| RN-33 | Las alertas solo salen por bots y webhooks; no hay difusión pública de eventos | [bots-webhooks](bots-webhooks/README.md) |
| RN-34 | Filtros por defecto al añadir un bot: riesgos `medio`, `alto`; tipo `infraestructura`. Cada destino elige los suyos | Ídem |
| RN-35 | Anti-ruido: actualizaciones como respuesta al mensaje original; máximo 10 mensajes/min por destino | Ídem |
| RN-36 | Los bots no leen conversaciones ni guardan nombres de grupos o usuarios; guardan lo que envían | Ídem |
| RN-37 | Panel de operadores privado con estado de todas las piezas; sin página de estado pública | [14-operator-panel](portal/14-operator-panel.md) |

## 6. Portal, API y chat

| ID | Regla | Dónde |
|---|---|---|
| RN-40 | Portada en este orden: tres tarjetas (MeshView, PotatoMesh, consumo y nodos en peligro), mapa, configura tu nodo, sube tus datos, autoría, aviso de que no es un servicio de emergencias | [01-structure-home](portal/01-structure-home.md) |
| RN-41 | Páginas obligatorias: proyecto, quién lo impulsa, cómo se gestiona, configura tu nodo, conecta tu gateway, bots, rankings, alertas, revisa tu nodo, firmware, API, legal | [pages](portal/pages/README.md) |
| RN-42 | Sin marcas de terceros en lo público salvo PotatoMesh, MeshView, Telegram, Discord, enlaces oficiales de firmware y lo que exijan ley o licencias | [decisiones-tecnicas](decisiones-tecnicas.md) |
| RN-43 | Firmware: Alpha = pruebas; Beta = la más estable; ante la duda, la última beta | [07-firmware-page](portal/07-firmware-page.md) |
| RN-44 | API pública solo de lectura, sin autenticación, 60 peticiones/min por IP, claves JSON en inglés | [11-public-api](portal/11-public-api.md) |
| RN-45 | Chat en directo por WebSocket: solo lectura, solo canales admitidos, sin almacenamiento | [chat-ws](chat-ws/README.md) |
| RN-46 | Nombre, dominio, contacto, canales, radio y mapa siempre por configuración, nunca escritos en código ni textos | [integration §12](integration.md) |
| RN-47 | Autoría visible: Raúl Caro Pastorino · @raupulus · public@raupulus.dev · https://raupulus.dev | [05-authorship](portal/05-authorship.md) |

## Historial de cambios de reglas

| Fecha | Regla | Cambio | Pedido por |
|---|---|---|---|
| 2026-10-07 | Todas | Versión inicial | Responsable del proyecto |

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
