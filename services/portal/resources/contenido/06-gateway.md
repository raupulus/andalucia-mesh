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
- Un usuario y una contraseña propios. Te los damos nosotros.

### Pasos

1. **Pide tu usuario.** Escribe a {PROJECT_CONTACT} con el asunto "Alta de gateway", el id de tu nodo (`!xxxxxxxx`; lo ves en la app del nodo) y tu zona aproximada (pueblo o comarca).
2. **Recibe tus credenciales.** Tu usuario es el id de tu nodo y la contraseña es solo para ti. No la compartas.
3. **Aplica los ajustes** de la tabla de abajo.
4. **Comprueba.** En unos minutos tu nodo aparece en MeshView y PotatoMesh.

**Botón primario:** "Pedir mi usuario" → `mailto:{PROJECT_CONTACT}?subject=Alta%20de%20gateway`

### Ajustes

| Ajuste | Valor |
|---|---|
| MQTT activado | Sí |
| Servidor | `mqtt.{PROJECT_DOMAIN}` [Copiar] |
| Puerto | `1883`, o `8883` con TLS |
| Usuario / contraseña | Los tuyos: `!<id>` y la que te enviamos |
| Cifrado | Activado |
| JSON | Desactivado |
| TLS | Opcional |
| Root topic | `{MQTT_TOPIC_ROOT}` [Copiar] |
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
- **TLS opcional:** usa el puerto `8883` si tu nodo lo admite; algunos dan problemas con TLS y por eso el `1883` sigue abierto.
- **Map reporting:** tu nodo publica de vez en cuando su información y su posición para los mapas.
- **OK to MQTT:** marca tus paquetes como "se pueden subir". Los gateways solo suben los paquetes de los nodos que lo tienen activado.
- **Ignore MQTT:** tu nodo descarta los paquetes que otros han hecho pasar por internet, así no los repite por radio.

### Usuario propio y sin downlink

- Con tu propio usuario, nadie puede publicar en nombre de tu nodo, y si algo va mal sabemos de qué gateway viene.
- El servidor solo acepta subidas: ningún gateway puede recibir nada de él. Aunque actives el downlink por error, no te llegará nada que tu nodo pueda emitir por radio. Aun así, déjalo desactivado.

### Preguntas frecuentes

**Mi nodo no aparece.** Revisa que el root topic sea exactamente `{MQTT_TOPIC_ROOT}`, que el uplink esté activado y que los nombres de tus canales coincidan con la lista, mayúsculas incluidas. Si sigue sin aparecer, escríbenos con el id de tu nodo y lo miramos en los registros del servidor.

**¿Puedo conectarme con un usuario compartido?** No. Cada gateway tiene su propio usuario; las credenciales compartidas desaparecen al terminar la migración.

**¿Puedo subir otros canales?** Solo los de la lista. Si crees que falta alguno, escríbenos.

**¿Qué pasa si mi gateway se apaga?** Nada grave. Si deja de publicar un rato, el sistema abre una alerta de infraestructura que se cierra sola cuando vuelve.

**Quiero dejar de subir datos.** Desactiva MQTT en tu nodo y avísanos para dar de baja tu usuario.

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
