# Normas y Buenas Prácticas de la Malla

Para preservar la salud del espectro radioeléctrico en la banda europea de 868 MHz y asegurar que las alertas y mensajes lleguen a su destino, todos los colaboradores deben observar las siguientes pautas:

## 1. Uso Responsable del Espectro
- **Intervalos de Telemetría Adecuados:** Configura la telemetría de dispositivo y entorno con intervalos prudentes (mínimo 900 s / 15 minutos en nodos móviles; 1800 s / 30 minutos en nodos base fijos).
- **Control de Saltos (Hop Limit):** No utilices más de 3 saltos (`hop_limit = 3`) salvo en repetidores ubicados en cotas excepcionales de montaña que enlacen comarcas lejanas.

## 2. Gateways y Conexión MQTT
- **Downlink Desactivado:** Si configuras tu nodo como puerta de enlace MQTT, **desactiva siempre el downlink** en todos los canales. La retransmisión de tráfico desde internet hacia la radio congestiona el medio e impide la comunicación legítima en emergencias.
- **Canales Autorizados:** Transmite únicamente en el canal primario regional (`SFNarrow`) y en los canales comarcales y de emergencia de la lista blanca.

## 3. Respeto y Privacidad
- La banda abierta es compartida por radioaficionados, entusiastas y servicios de voluntariado. Mantén las conversaciones en un tono constructivo y evita difundir datos personales privados de terceros por el aire.
