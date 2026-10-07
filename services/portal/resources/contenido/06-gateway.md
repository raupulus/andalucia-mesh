# 06 · Conecta tu gateway

> `/conecta-tu-gateway` · Que un operador con un nodo con internet empiece a subir datos sin preguntar nada más que su usuario, y sin abrir la puerta a que internet llegue a la radio · `../04-gateway-connection.md` (tabla de alta de `../../mosquitto/README.md`)

## SEO

- **Título:** `Conecta tu gateway · {PROJECT_NAME}` (40 caracteres)
- **Descripción:** `Sube a la malla lo que oye tu nodo: cómo pedir tu usuario, los ajustes MQTT exactos y por qué el downlink va siempre desactivado.` (129)

## Estructura

| # | Sección | Componente (`DESIGN.md`) |
|---|---|---|
| 1 | H1 y entradilla | Tipografía H1 + Texto |
| 2 | Qué necesitas | Texto corrido con lista |
| 3 | Pasos, con botón "Pedir mi usuario" | Bloque de ajustes (pasos numerados) + Botones (primario) |
| 4 | Ajustes | Bloque de ajustes (botón de copiar en servidor y root topic; chip `aviso` en downlink) |
| 5 | Canales que acepta el servidor | Texto + lista en línea en Ubuntu Mono |
| 6 | Por qué cada ajuste | Texto corrido con lista |
| 7 | Usuario propio y sin downlink | Texto corrido con lista |
| 9 | Preguntas frecuentes | H3 + Texto |
| 10 | Qué hacemos con tus datos | Texto corrido con enlace |

## Borrador del texto

Cada `###` es un H2 de la página.

**H1:** Conecta tu gateway

Un gateway es un nodo con internet que sube a nuestro servidor lo que oye por radio. Cuantos más gateways haya, más completa se ve la malla en los mapas, las estadísticas y las alertas. Funciona en un solo sentido: nada de lo que llega por internet vuelve a la radio.

### Qué necesitas

- Un nodo con WiFi o Ethernet, o que pueda usar la app del móvil como puente para MQTT.
- Que esté configurado según la guía [Configura tu nodo](/configura-tu-nodo).
- Conexión directa: puedes conectar tu pasarela de inmediato usando las credenciales comunitarias abiertas de solo subida.

### Pasos

1. **Abre los ajustes de MQTT** en la app de Meshtastic de tu nodo (`Module Configuration → MQTT`).
2. **Aplica los parámetros** de la tabla de abajo.
3. **Verifica.** En pocos minutos tu nodo aparecerá subiendo paquetes en MeshView y PotatoMesh.

### Ajustes

| Ajuste | Valor |
|---|---|
| MQTT activado | Sí |
| Servidor (Address) | `mqtt.{PROJECT_DOMAIN}` |
| Puerto | `8883` (con TLS obligatorio) |
| Usuario (Username) | `{MQTT_GATEWAY_USER}` |
| Contraseña (Password) | `{MQTT_GATEWAY_PASSWORD}` |
| Cifrado | Activado |
| JSON | Desactivado |
| TLS | Activado |
| Root topic | `{MQTT_TOPIC_ROOT}` |
| Map reporting | Activado |
| Uplink | Activado en `{PRIMARY_CHANNEL}` y en los canales de la lista que uses |
| Downlink | Desactivado en todos los canales · chip `aviso` "Siempre desactivado" |
| OK to MQTT | Activado |
| Ignore MQTT | Activado |

### Canales que acepta el servidor

El servidor solo acepta estos canales, escritos exactamente así, mayúsculas incluidas. Si un canal de tu nodo tiene otro nombre, sus paquetes se rechazan.

`{ALLOWED_CHANNELS}` (lista en línea, un nombre por elemento)

Todos son canales públicos: sus mensajes se ven en PotatoMesh y MeshView y los buscadores pueden indexarlos, también los del canal `sos`. No escribas en ellos nada que no quieras que se vea.

### Por qué cada ajuste

- **Cifrado activado:** los paquetes suben tal y como viajan por la radio. Solo desciframos los canales que usan la clave pública por defecto.
- **JSON desactivado:** el firmware actual ya no lo usa y el servidor no lo acepta.
- **Root topic `{MQTT_TOPIC_ROOT}`:** escríbelo a mano. Con un servidor que no es el de fábrica, el firmware no añade la región por su cuenta.
- **TLS obligatorio (puerto 8883):** toda la comunicación con el broker se realiza cifrada de extremo a extremo para evitar espionajes y manipulaciones en tránsito.
- **Map reporting:** tu nodo publica de vez en cuando su información y su posición para los mapas.
- **OK to MQTT:** marca tus paquetes como "se pueden subir". Los gateways solo suben los paquetes de los nodos que lo tienen activado.
- **Ignore MQTT:** tu nodo descarta los paquetes que otros han hecho pasar por internet, así no los repite por radio.

### Acceso seguro y sin downlink

- El servidor solo acepta subidas: ningún gateway puede recibir nada de él. Aunque actives el downlink por error, no te llegará nada que tu nodo pueda emitir por radio. Aun así, déjalo siempre desactivado.

### Preguntas frecuentes

**Mi nodo no aparece.** Revisa que el root topic sea exactamente `{MQTT_TOPIC_ROOT}`, que el puerto sea `8883` con TLS, que el uplink esté activado y que los nombres de tus canales coincidan con la lista, mayúsculas incluidas. Si sigue sin aparecer, consúltanos en {PROJECT_CONTACT}.

**¿Puedo conectarme con las credenciales públicas?** Sí, las credenciales `{MQTT_GATEWAY_USER}` / `{MQTT_GATEWAY_PASSWORD}` están abiertas a toda la comunidad con permisos estrictos de solo subida a canales autorizados.

**¿Puedo subir otros canales?** Solo los de la lista autorizada. Si crees que falta alguno para la región, escríbenos.

**¿Qué pasa si mi gateway se apaga?** Nada grave. Si deja de publicar un rato, el sistema abre una alerta de infraestructura que se cierra sola cuando vuelve.

**Quiero dejar de subir datos.** Desactiva el módulo MQTT en la app de tu nodo.

### Qué hacemos con tus datos

Los paquetes que subes se muestran en PotatoMesh y MeshView, alimentan las estadísticas y las alertas, y se borran solos según los plazos de la [política de privacidad](/legal/privacidad). El id de tu gateway queda en los registros del servidor y puede aparecer en una alerta si deja de publicar. Tu correo solo lo usamos para gestionar tu alta. Si no quieres que se suban los paquetes de tu nodo, desactiva OK to MQTT.

## Datos dinámicos y configuración

| Dato | Origen |
|---|---|
| Servidor | `mqtt.{PROJECT_DOMAIN}` (`mqtt.mesh.example.org`) |
| `{MQTT_TOPIC_ROOT}` | Configuración global, valor `msh/EU_868` |
| `{ALLOWED_CHANNELS}` | Variable global de la que se genera la ACL del broker. Se pinta tal cual, sin escribir los nombres a mano |
| `{PRIMARY_CHANNEL}` | `PRIMARY_CHANNEL` |
| Contacto y `mailto:` | `PROJECT_CONTACT` |
| Puertos | Fijos (`1883`, `8883`), según `../../mosquitto/README.md` |
| Texto | `resources/contenido/conecta-tu-gateway.md`. Sin datos de la API |

Ningún texto incluye credenciales: ni contraseñas de gateway ni de servicios (`../04-gateway-connection.md`).

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
