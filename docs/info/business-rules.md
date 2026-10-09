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
| RN-02 | Acceso a gateways simplificado con credenciales compartidas públicas (`meshdev` / `large4cats`) de solo subida sin lectura en los canales de `ALLOWED_CHANNELS` y map reports por TLS 8883; canal 0 `SFNarrow` | [mosquitto](mosquitto/README.md) |
| RN-03 | Solo se trata lo que el nodo permite subir (OK to MQTT). No hay exclusión de nodos ni feed filtrado: quien no quiere aparecer desactiva OK to MQTT | [10-legal-privacy](portal/10-legal-privacy.md) |
| RN-04 | Nunca se publican mensajes directos (PKI ni texto con destinatario); solo difusión en canales de la lista | [ingesta](ingesta/06-decoded-stream.md), [chat-ws](chat-ws/README.md) |
| RN-05 | El portal y la API nunca exponen coordenadas: solo provincia y recuentos | [portal](portal/README.md) |
| RN-06 | Las páginas públicas no usan cookies ni hacen peticiones a terceros | [portal](portal/README.md) |
| RN-08 | Independencia, centralización y aislamiento: sin bridges MQTT bidireccionales ni federaciones salientes hacia redes de terceros (PotatoMesh `FEDERATION=0` forzado y MeshviewWorld sin enlaces activos); sync-peers solo lee APIs públicas de otras instancias y las canaliza a ingesta (filtro anti-duplicados) y PotatoMesh | [sync-peers](potatomesh/sync-peers.md), [customizations](customizations.md) |
| RN-09 | Toda fecha y marca temporal debe persistirse en UTC siempre que sea viable (epoch UTC o `TIMESTAMPTZ`); en interfaces de usuario y vistas se muestra en hora local peninsular (`Europe/Madrid`, CET/CEST) en formato 24 horas (`HH:mm:ss`) y fecha estándar europea (`DD/MM/YYYY`) o ISO (`YYYY-MM-DD`) | [integration](integration.md), [customizations](customizations.md) |

## 3. Definiciones y cálculos

| ID | Regla | Dónde |
|---|---|---|
| RN-10 | Router = rol en `INFRA_ROLES` (`ROUTER`, `ROUTER_LATE`, `REPEATER`). Nodo de infraestructura = router o gateway. `CLIENT_BASE` no es infraestructura | [integration §13](integration.md) |
| RN-11 | Saturación de provincia = 0,6 × media de routers + 0,4 × media conjunta de `CLIENT` y `CLIENT_BASE`; otros roles se ignoran. Umbrales: > 20 % bajo, > 30 % medio, > 40 % alto | [02-province-map](portal/02-province-map.md), [detector-alertas](detector-alertas/02-rule-catalog.md) |
| RN-12 | Colores del mapa: verde ≤ 20 %, naranja > 20 % y < 40 %, rojo ≥ 40 %, gris sin datos. El mapa muestra bajo la leyenda cómo se calcula | [02-province-map](portal/02-province-map.md) |
| RN-13 | Mapa: ventana de 7 días por defecto, opción de 24 h | [02-province-map](portal/02-province-map.md) |
| RN-14 | Rankings por hora, día, semana y mes en hora local, periodo en curso y anterior; no se suman días para obtener semanas o meses | [12-stats-api](portal/12-stats-api.md) |
| RN-15 | Todos los recuentos se presentan como un mínimo (solo lo que llega con OK to MQTT) | [overview](overview.md) |

## 4. Configuración recomendada de nodos

| ID | Regla | Dónde |
|---|---|---|
| RN-20 | Radio recomendada (configurable): EU_868, sin preset, BW 62, SF 7, CR 5, slot 4 (869,618 MHz), canal 0 `SFNarrow`, 3 saltos | [03-node-setup-guide](portal/03-node-setup-guide.md) |
| RN-21 | Roles: `CLIENT_MUTE` por defecto, `CLIENT` en exterior bien situado, `ROUTER` solo coordinado | Ídem |
| RN-22 | Saltos: 3 recomendado; 4–5 válidos en extremos o casos justificados; 6 genera alerta de riesgo `bajo`; ≥ 7 genera alerta de riesgo `alto`. En nodos cliente clasifica como `clientes` y en routers/gateways como `infraestructura` | Ídem, [02-rule-catalog](detector-alertas/02-rule-catalog.md) |
| RN-23 | Intervalos: NodeInfo 72 h; posición fija 72 h, móvil ≥ 1 h; telemetría de dispositivo solar ≥ 4 h, troncal ≥ 6 h, enchufado desactivada; entorno desactivada o > 4 h | [03-node-setup-guide](portal/03-node-setup-guide.md) |
| RN-24 | Una sola fuente de umbrales: guía, diagnóstico ("Revisa tu nodo") y detector usan los mismos valores; un nodo que sigue la guía no recibe alertas de configuración ni hallazgos | [13-nodes-alerts-api](portal/13-nodes-alerts-api.md) |

## 5. Alertas y avisos

| ID | Regla | Dónde |
|---|---|---|
| RN-30 | Riesgos `bajo`, `medio`, `alto` y tipos `infraestructura`, `clientes`, ampliables solo por configuración | [detector-alertas](detector-alertas/README.md) |
| RN-31 | Tipo `infraestructura` si el nodo es de infraestructura o la alerta afecta a la malla; si no, `clientes` | [01-rule-engine](detector-alertas/01-rule-engine.md) |
| RN-32 | Reglas de anomalías y umbrales: batería baja en routers (< 60 % medio, < 40 % crítico) y clientes (< 35 % bajo, < 15 % medio); reinicios (3 en 5 min medio, ≥ 5 en 10 min alto; resolución 30 min); saturación LoRa (60 % routers / 40 % clientes: > 20 % bajo, > 30 % medio, > 40 % alto); texto (> 5/min bajo, 6-10 medio, > 10 alto); telemetría (≥ 2/min bajo, > 50/h alto); sondeos (^all: 1 bajo, ≥ 3/15m medio, ≥ 5/15m alto); atardecer (20:00 h peninsular solar < 60 % medio, < 40 % alto); traceroute (10-19/30m medio, ≥ 20/30m alto); tráfico privado (> 10/10m o > 30/h medio, > 60/h alto); posición GPS repetitiva (4/5m bajo, 8/10m medio, 20/15m alto); router sin coordinar en Andalucía; airtime de transmisión propio (> 4 % bajo, > 6 % medio, > 8 % alto). Descartados expresamente el desfase de reloj (falsos positivos en fix viejos) y el cese de GPS (apagado voluntario). Cuando un router coordinado actualice firmware o cambie de identidad, los operadores lo gestionan manualmente en el panel `/admin/coordinated-routers` | [02-rule-catalog](detector-alertas/02-rule-catalog.md) |
| RN-33 | Las alertas solo salen por bots y webhooks dirigidos a operadores; no hay difusión pública de eventos, no se avisa a usuarios individuales ni se transmite ningún mensaje hacia los nodos de la malla (observador pasivo estricto, RN-01) | [bots-webhooks](bots-webhooks/README.md) |
| RN-34 | Filtros por defecto al añadir un bot: riesgo `alto` (para evitar spam en grupos); tipo `infraestructura`; nodos de fuera de Andalucía excluidos por defecto (solo Andalucía). Cada destino puede personalizar los suyos con `/levels`, `/types` y `/disableExterior` / `/enableExterior` | Ídem |
| RN-35 | Anti-ruido: actualizaciones como respuesta al mensaje original; máximo 10 mensajes/min por destino | Ídem |
| RN-36 | Los bots no leen conversaciones ni guardan nombres de grupos o usuarios; guardan lo que envían | Ídem |
| RN-37 | Panel de operadores privado con estado de todas las piezas; sin página de estado pública | [14-operator-panel](portal/14-operator-panel.md) |
| RN-38 | Los comandos de consulta de routers (`/battery` y `/routers`) agrupan obligatoriamente por provincia andaluza y nunca muestran nodos ubicados fuera de Andalucía | [bots-webhooks](bots-webhooks/README.md) |
| RN-39 | Ámbito geográfico y coordinación de infraestructura: toda alerta identifica si el nodo pertenece a Andalucía (`dentro_andalucia: true`) y su provincia (`ES-AL`..`ES-SE`); las anomalías de gobernanza (`router-role`) y solares (`sunset-battery`) descartan estrictamente nodos de fuera (`FUERA`). El panel operador gestiona la infraestructura coordinada agrupada por provincias andaluzas | [portal](portal/14-operator-panel.md), [detector-alertas](detector-alertas/02-rule-catalog.md) |

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
| RN-48 | Soporte multidioma exclusivo del portal (frontend público y panel operador `/admin`): español como idioma base/fallback y por defecto; detección automática del navegador (`Accept-Language`) entre español (`es`), inglés (`en`) y portugués (`pt`). Selector de idioma en el navbar con icono redondo mostrando la bandera de Andalucía para representar la variante y enfoque andaluz (en lugar de la bandera de España), además de selector en backend. Las demás piezas/servicios no se traducen | [portal](portal/README.md) |

## Historial de cambios de reglas

| Fecha | Regla | Cambio | Pedido por |
|---|---|---|---|
| 2026-10-07 | Todas | Versión inicial | Responsable del proyecto |
| 2026-10-07 | RN-08, OB-02, OB-03 | Canalización de sync-peers a la ingesta para deduplicación unificada y cobertura regional completa de alertas y métricas | Responsable del proyecto |
| 2026-10-08 | RN-02 | Credenciales públicas compartidas (meshdev / large4cats) para gateways con permisos de solo subida a canales autorizados por TLS 8883 | Responsable del proyecto |
| 2026-10-08 | RN-34 | Filtros por defecto de bots: solo avisos de infraestructura altos (riesgo 'alto') al integrarlo a grupos para evitar spam | Responsable del proyecto |
| 2026-10-08 | RN-22 | Umbrales de saltos (hops-high): 6 saltos genera riesgo bajo y >= 7 genera riesgo alto; clasificación de tipo según nodo (infraestructura para routers/gateways y clientes para nodos de usuario) | Responsable del proyecto |
| 2026-10-08 | RN-34 | Filtro geográfico en bots: nodos exteriores (fuera de Andalucía) desactivados por defecto al añadir el bot a grupos; configurables con /disableExterior y /enableExterior | Responsable del proyecto |
| 2026-10-08 | RN-08, RN-09 | Persistencia estricta en UTC y visualización obligatoria en 24h peninsular (Europe/Madrid) con formato europeo; aislamiento frente a federaciones externas (PotatoMesh FEDERATION=0 forzado en compose) y registro de personalizaciones | Responsable del proyecto |
| 2026-10-08 | RN-38 | Formateo estético y agrupación provincial obligatoria en /battery y /routers, con exclusión estricta de nodos de fuera de Andalucía | Responsable del proyecto |
| 2026-10-08 | RN-48 | Soporte multidioma (ES/EN/PT) exclusivo para el portal (frontend y panel de operadores), con español por defecto y bandera de Andalucía en el selector | Responsable del proyecto |
| 2026-10-08 | RN-11, RN-32, RN-39 | Reajuste integral de umbrales del detector de alertas (baterías 60/40 y 35/15; reinicios 3/5m y 5/10m; saturación 60% routers / 40% clientes; ráfagas de telemetría y texto; sondeos; 20:00 h solar; traceroute; tráfico privado; posiciones GPS continuas) y nuevo módulo Filament de routers coordinados agrupado por provincias andaluzas con descarte de nodos exteriores | Responsable del proyecto |
| 2026-10-09 | RN-32, RN-33 | Prohibición estricta de avisos hacia usuarios o nodos (observador pasivo); descarte de cese de GPS y desfase de reloj; ajuste umbrales airtime-high (4/6/8 %); gestión manual de routers coordinados ante cambios de firmware en /admin/coordinated-routers | Responsable del proyecto |

---
> Creado: 2026-10-07 · Última revisión: 2026-10-09
