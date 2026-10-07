# 06.4 · Conecta tu gateway

> Bloque "Sube los datos de tu nodo" en la portada y página `/conecta-tu-gateway`: cómo pedir usuario del broker y qué ajustes poner. Texto en `pages/06-gateway.md`. Tabla técnica de alta en `../mosquitto/README.md`.

## Objetivo

Que un operador con un nodo con internet empiece a subir datos preguntando solo por su usuario, y sin abrir la puerta a que internet llegue a la radio.

## Especificación

### Pasos

1. **Requisito:** nodo con WiFi o Ethernet (o proxy MQTT por la app del móvil) y configurado según `/configura-tu-nodo`.
2. **Pide tu usuario:** correo a `PROJECT_CONTACT` con el id del nodo (`!xxxxxxxx`) y la zona aproximada. Recibes usuario (tu id) y contraseña propios.
3. **Aplica los ajustes** de la tabla.
4. **Comprueba:** en unos minutos tu nodo aparece en MeshView y PotatoMesh; puedes verlo en `/revisa-tu-nodo`.

### Ajustes

| Ajuste | Valor | Origen |
|---|---|---|
| MQTT activado | Sí | Fijo |
| Servidor | `mqtt.${PROJECT_DOMAIN}` | `config/proyecto.php` → `mqtt.host_publico` |
| Puerto | `1883` (o `8883` con TLS) | Fijo (`../mosquitto/README.md`) |
| Usuario / contraseña | Los tuyos (`!<id>` / la enviada) | — |
| Cifrado | Activado | Fijo |
| JSON | Desactivado | Fijo |
| TLS | Opcional (puerto 8883) | Fijo |
| Root topic | `{MQTT_TOPIC_ROOT}` (`msh/EU_868`), **escrito a mano**: con un servidor propio el firmware no añade la región | `MQTT_TOPIC_ROOT` |
| Map reporting | Activado | Fijo |
| Canales: uplink | Activado en el canal primario `{PRIMARY_CHANNEL}` y en los canales de la lista que use el nodo | `PRIMARY_CHANNEL`, `ALLOWED_CHANNELS` |
| Canales: **downlink** | **Desactivado en todos** (chip de aviso) | Fijo |
| LoRa: OK to MQTT | Activado | Fijo |
| LoRa: Ignore MQTT | Activado | Fijo |

- Lista de canales admitidos (`ALLOWED_CHANNELS`) mostrada como chips, con nombres exactos (el broker rechaza otros, mayúsculas incluidas).
- Botón de copiar en servidor, root topic y nombres de canal.

### Por qué

- Usuario propio: nadie puede publicar en nombre de tu nodo (la ACL del broker solo deja publicar en topics que terminan en tu id).
- Solo subida: nada de lo que llega por internet vuelve a la radio, aunque un nodo tenga el downlink activado por error.

### Datos

Una línea sobre qué se hace con los datos y enlace a `/legal/privacidad`; "si no quieres que se suban tus paquetes, desactiva OK to MQTT". Enlace a `/bots` para recibir avisos.

## Contratos propios

- `config/proyecto.php` → `mqtt.host_publico` (`'mqtt.'.PROJECT_DOMAIN`), `mqtt.puertos` (`1883`, `8883`), `mqtt.topic_raiz` (`MQTT_TOPIC_ROOT`), `canales` (`ALLOWED_CHANNELS`), `canal_primario`.
- La tabla de ajustes es la misma que la de alta de gateways del broker: si cambia allí, cambia aquí.

## Unidades de trabajo

- **UT-06.4.1 — Bloque de portada.** Pasos y tabla completa (incluido el chip de downlink). *Aceptación:* sin credenciales en ningún texto.
- **UT-06.4.2 — Página completa.** Porqué de cada ajuste, preguntas frecuentes de `pages/`. *Aceptación:* textos idénticos a `pages/06-gateway.md` con los valores de configuración.
- **UT-06.4.3 — Coherencia.** Prueba que compara servidor, topic y canales con `.env.comun`. *Aceptación:* ningún valor de la tabla escrito a mano.

## Escenarios de prueba

1. **Dado** un operador nuevo, **cuando** sigue solo esta página con el usuario recibido, **entonces** su nodo aparece en MeshView en minutos.
2. **Dado** `ALLOWED_CHANNELS` con un canal nuevo, **cuando** se redespliega, **entonces** aparece como chip en la página.
3. **Dado** la página, **cuando** se busca cualquier contraseña, **entonces** no aparece.
4. **Dado** un lector de pantalla, **cuando** llega al ajuste de downlink, **entonces** oye "Desactivado en todos, importante" y no solo un color.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
