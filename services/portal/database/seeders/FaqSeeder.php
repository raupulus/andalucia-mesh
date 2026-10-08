<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

/**
 * Seeder con 10 preguntas frecuentes de ejemplo multidioma (ES, EN, PT) para Andalucía Mesh.
 */
class FaqSeeder extends Seeder
{
    /**
     * Ejecuta las inserciones de preguntas frecuentes en la base de datos local.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => [
                    'es' => '¿Qué es Andalucía Mesh y cuál es el canal oficial de radio?',
                    'en' => 'What is Andalucía Mesh and what is the official radio channel?',
                    'pt' => 'O que é a Andalucía Mesh e qual é o canal oficial de rádio?',
                ],
                'answer' => [
                    'es' => "Andalucía Mesh es una red ciudadana, experimental y abierta basada en tecnología **LoRa Meshtastic** (banda EU_868).\n\nEl canal primario oficial de la comunidad es **SFNarrow**:\n- **Frecuencia / Slot:** 869.618 MHz (Slot 4)\n- **Ancho de banda:** 62.5 kHz\n- **Factor de dispersión:** SF7\n- **Coding Rate:** 4/5\n\nPuedes consultar la configuración detallada en la guía de [Configura tu nodo](/configura-tu-nodo).",
                    'en' => "Andalucía Mesh is an open, experimental community network based on **LoRa Meshtastic** technology (EU_868 band).\n\nThe official primary channel is **SFNarrow**:\n- **Frequency / Slot:** 869.618 MHz (Slot 4)\n- **Bandwidth:** 62.5 kHz\n- **Spread Factor:** SF7\n- **Coding Rate:** 4/5\n\nCheck out the full guide at [Configure your node](/configura-tu-nodo).",
                    'pt' => "A Andalucía Mesh é uma rede comunitária, experimental e aberta baseada na tecnologia **LoRa Meshtastic** (faixa EU_868).\n\nO canal primário oficial da comunidade é o **SFNarrow**:\n- **Frequência / Slot:** 869.618 MHz (Slot 4)\n- **Largura de banda:** 62.5 kHz\n- **Fator de dispersão:** SF7\n- **Taxa de codificação:** 4/5\n\nConsulte o guia completo em [Configure o seu nó](/configura-tu-nodo).",
                ],
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'question' => [
                    'es' => '¿Por qué mi nodo no enlaza ni ve a otros repetidores en la malla?',
                    'en' => 'Why does my node not connect or see other repeaters in the mesh?',
                    'pt' => 'Por que o meu nó não conecta nem vê outros repetidores na malha?',
                ],
                'answer' => [
                    'es' => "Si acabas de encender tu dispositivo y no recibes paquetes de otros nodos, suele deberse a alguno de estos factores:\n\n1. **Línea de vista (LOS):** A 868 MHz los obstáculos físicos (edificios, cerros, muros) atenúan la señal. Prueba en una azotea o ventana despejada.\n2. **Región LoRa:** Verifica que en los ajustes de radio esté seleccionada la región `EU_868`.\n3. **Antena:** Usa una antena sintonizada para 868 MHz y nunca emitas sin ella conectada para no dañar el circuito RF.",
                    'en' => "If you just turned on your device and cannot hear other nodes, it is usually due to one of these reasons:\n\n1. **Line of sight (LOS):** At 868 MHz physical obstacles (buildings, hills, concrete walls) block signals. Try an open window or rooftop.\n2. **LoRa Region:** Verify that the `EU_868` region is selected in your radio settings.\n3. **Antenna:** Make sure to use an antenna tuned for 868 MHz and never transmit without an antenna connected.",
                    'pt' => "Se acabou de ligar o dispositivo e não recebe pacotes de outros nós, normalmente deve-se a:\n\n1. **Linha de vista (LOS):** A 868 MHz obstáculos físicos (edifícios, colinas, betão) atenuam fortemente o sinal. Experimente numa janela desimpedida ou telhado.\n2. **Região LoRa:** Verifique se a região `EU_868` está selecionada nas configurações de rádio.\n3. **Antena:** Utilize uma antena sintonizada para 868 MHz e nunca transmita sem antena para não danificar o módulo RF.",
                ],
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'question' => [
                    'es' => '¿Qué rol debo configurar en mi nodo: CLIENT, CLIENT_MUTE o ROUTER?',
                    'en' => 'Which role should I configure on my node: CLIENT, CLIENT_MUTE, or ROUTER?',
                    'pt' => 'Qual papel devo configurar no meu nó: CLIENT, CLIENT_MUTE ou ROUTER?',
                ],
                'answer' => [
                    'es' => "Para mantener la red fluida y eficiente según las buenas prácticas:\n\n- **CLIENT:** Rol estándar para la mayoría de usuarios. Recibe, emite y colabora retransmitiendo paquetes.\n- **CLIENT_MUTE:** Ideal si tu nodo está en interiores, planta baja o es un tracker. No repite tráfico ajeno, ahorrando batería y liberando el aire.\n- **ROUTER / REPEATER:** **Exclusivo para repetidores fijos** en puntos elevados con alimentación solar o continua.",
                    'en' => "To keep the mesh efficient and healthy:\n\n- **CLIENT:** Standard role for most users. Receives, transmits, and helps relay packets.\n- **CLIENT_MUTE:** Recommended if your node is indoors, on ground floors, or used as a tracker. It does not retransmit third-party traffic, saving battery and airtime.\n- **ROUTER / REPEATER:** **Reserved for permanent repeaters** on high ground with solar or grid power.",
                    'pt' => "Para manter a rede eficiente e saudável:\n\n- **CLIENT:** Papel padrão para a maioria dos utilizadores. Recebe, emite e ajuda a retransmitir pacotes.\n- **CLIENT_MUTE:** Recomendado se o seu nó estiver em interiores, pisos térreos ou for um tracker. Não repete tráfego alheio, poupando bateria e canal.\n- **ROUTER / REPEATER:** **Exclusivo para repetidores fixos** em locais elevados com energia solar ou contínua.",
                ],
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'question' => [
                    'es' => '¿Cómo conecto mi nodo con internet a la pasarela MQTT comunitaria?',
                    'en' => 'How do I connect my internet-enabled node to the community MQTT gateway?',
                    'pt' => 'Como posso ligar o meu nó com internet ao gateway MQTT comunitário?',
                ],
                'answer' => [
                    'es' => "Si tu nodo dispone de conexión WiFi doméstica hacia internet, puedes vincularlo como Gateway comunitario:\n\n- **Servidor:** `mqtt.mesh.desdechipiona.es`\n- **Puerto:** `8883` (**TLS obligatorio con certificado válido**)\n- **Root Topic:** `msh/EU_868`\n- **Uplink:** Activado (`ON`)\n- **Downlink:** **Siempre desactivado (`OFF`)**\n\n> **Seguridad:** El puerto plano `1883` está **cerrado** al exterior. Solo se admiten conexiones seguras por TLS en el `8883`. Asimismo, el servidor bloquea todo Downlink para blindar las frecuencias de radio. Guía en [Conecta tu gateway](/conecta-tu-gateway).",
                    'en' => "If your node has home WiFi and internet access, you can connect it as a community Gateway:\n\n- **Server:** `mqtt.mesh.desdechipiona.es`\n- **Port:** `8883` (**Mandatory TLS with valid certificate**)\n- **Root Topic:** `msh/EU_868`\n- **Uplink:** Enabled (`ON`)\n- **Downlink:** **Always disabled (`OFF`)**\n\n> **Security:** Plain port `1883` is **closed** to the internet. Only encrypted TLS connections on port `8883` are accepted. Downlink traffic is also strictly rejected to protect radio airtime. Guide at [Connect your gateway](/conecta-tu-gateway).",
                    'pt' => "Se o seu nó possui ligação WiFi à internet, pode integrá-lo como Gateway comunitário:\n\n- **Servidor:** `mqtt.mesh.desdechipiona.es`\n- **Porta:** `8883` (**TLS obrigatório com certificado válido**)\n- **Root Topic:** `msh/EU_868`\n- **Uplink:** Ativado (`ON`)\n- **Downlink:** **Sempre desativado (`OFF`)**\n\n> **Segurança:** A porta simples `1883` está **encerrada** ao exterior. Apenas são aceites ligações encriptadas por TLS na porta `8883`. O Downlink é sempre bloqueado para proteger o canal de rádio. Guia em [Conecte o seu gateway](/conecta-tu-gateway).",
                ],
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'question' => [
                    'es' => '¿Cuáles son los intervalos de telemetría y posición recomendados?',
                    'en' => 'What are the recommended telemetry and GPS broadcast intervals?',
                    'pt' => 'Quais são os intervalos recomendados de telemetria e posição GPS?',
                ],
                'answer' => [
                    'es' => "Para mantener la malla fluida y cumplir la normativa legal (*duty cycle*):\n\n- **Telemetría de dispositivo (`device_metrics` / batería):** Configurar entre **2 y 4 horas** (7200 a 14400 s) en nodos solares, **≥ 6 horas** en repetidores troncales, o **desactivada** si está enchufado a la red.\n- **Información del nodo (`NodeInfo`):** Intervalo de **72 horas** en estaciones fijas.\n- **Posición GPS (`Position`):** **72 horas** en nodos fijos (o desactivada), y mínimo **15 minutos** (900 s) en móviles o senderismo.\n- **Límite de saltos (`Hop Limit`):** Siempre en **3 saltos** (máximo 4 en zonas remotas aisladas).",
                    'en' => "To keep the mesh healthy and respect airtime duty cycle regulations:\n\n- **Device Telemetry (`device_metrics` / battery):** Set between **2 and 4 hours** (7200 to 14400 s) for solar nodes, **≥ 6 hours** for infrastructure repeaters, or **disabled** if connected to AC power.\n- **Node Information (`NodeInfo`):** Interval of **72 hours** for fixed base nodes.\n- **GPS Position (`Position`):** **72 hours** for fixed stations (or disabled), and at least **15 minutes** (900 s) for mobile or hiking nodes.\n- **Hop Limit:** Always set to **3 hops** (maximum 4 in isolated remote valleys).",
                    'pt' => "Para manter a malha eficiente e respeitar os limites de ocupação (*duty cycle*):\n\n- **Telemetria de dispositivo (`device_metrics` / bateria):** Definir entre **2 e 4 horas** (7200 a 14400 s) em nós solares, **≥ 6 horas** em repetidores tronco, ou **desativada** se ligado à rede elétrica.\n- **Informações do nó (`NodeInfo`):** Intervalo de **72 horas** em estações fixas.\n- **Posição GPS (`Position`):** **72 horas** em nós fixos (ou desativada), e no mínimo **15 minutos** (900 s) em nós móveis.\n- **Limite de saltos (`Hop Limit`):** Definir sempre em **3 saltos** (máximo 4 em áreas remotas isoladas).",
                ],
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'question' => [
                    'es' => '¿Por qué mi nodo no aparece en los mapas públicos (MeshView o PotatoMesh)?',
                    'en' => 'Why does my node not show up on public maps (MeshView or PotatoMesh)?',
                    'pt' => 'Por que o meu nó não aparece nos mapas públicos (MeshView ou PotatoMesh)?',
                ],
                'answer' => [
                    'es' => "Los visores cartográficos procesan los paquetes captados por los gateways MQTT. Si tu nodo aún no figura:\n\n1. Tu equipo debe haber emitido al menos un paquete recibido por un gateway de la red.\n2. Asegúrate de tener activada la difusión de posición y que el canal coincida con `SFNarrow`.\n3. Comprueba el estado de actividad de los repetidores de tu comarca en [Alertas](/alertas).",
                    'en' => "Map viewers process packets received by gateways connected to MQTT. If your node has not appeared yet:\n\n1. Your device must transmit at least one packet successfully heard by a community gateway.\n2. Ensure position broadcasting is enabled and your primary channel is set to `SFNarrow`.\n3. Check the operating status of regional repeaters under [Alerts](/alertas).",
                    'pt' => "Os mapas processam os pacotes captados pelos gateways ligados ao MQTT. Se o seu nó ainda não aparece:\n\n1. O seu rádio deve ter emitido pelo menos um pacote recebido por um gateway da rede.\n2. Certifique-se de que a difusão de localização está ativa e o canal coincide com `SFNarrow`.\n3. Verifique o estado operacional dos repetidores locais em [Alertas](/alertas).",
                ],
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'question' => [
                    'es' => '¿Qué antena y cable coaxial ofrecen mejor rendimiento en 868 MHz?',
                    'en' => 'Which antenna and coaxial cable offer the best performance at 868 MHz?',
                    'pt' => 'Qual antena e cabo coaxial oferecem melhor desempenho em 868 MHz?',
                ],
                'answer' => [
                    'es' => "Para lograr el mayor alcance posible en enlaces LoRa:\n\n- **Antena:** Emplea antenas verticales de fibra de vidrio para **868 MHz** de 3 a 5.8 dBi. En zonas de montaña o relieve escarpado, ganancias moderadas evitan lóbulos demasiado estrechos.\n- **Cable coaxial:** El cable RG-58 sufre alta atenuación a 868 MHz. Emplea cables de bajas pérdidas como **LMR-400** o **RG-213** para bajadas de mástil.",
                    'en' => "For optimal coverage and reach in LoRa links:\n\n- **Antenna:** Use a fiberglass vertical antenna tuned for **868 MHz** with 3 to 5.8 dBi gain. In hilly terrain, moderate gain avoids radiation lobes that are too narrow.\n- **Coaxial Cable:** Standard RG-58 has high signal loss at 868 MHz. Use low-loss coax such as **LMR-400** or **RG-213** for antenna runs.",
                    'pt' => "Para obter o melhor alcance possível nas ligações LoRa:\n\n- **Antena:** Utilize antenas verticais de fibra de vidro sintonizadas em **868 MHz** com ganho entre 3 e 5.8 dBi. Em relevo acidentado, ganhos moderados proporcionam cobertura mais equilibrada.\n- **Cabo coaxial:** O cabo RG-58 sofre perdas elevadas a 868 MHz. Utilize cabos de baixa atenuação como **LMR-400** ou **RG-213**.",
                ],
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'question' => [
                    'es' => '¿Es legal emitir con Meshtastic en España y Andalucía?',
                    'en' => 'Is it legal to transmit with Meshtastic in Spain and Andalucía?',
                    'pt' => 'É legal transmitir com Meshtastic em Espanha e na Andaluzia?',
                ],
                'answer' => [
                    'es' => "Sí, totalmente. Meshtastic opera en la banda ISM europea sin requerir licencia de radioaficionado (conforme al CNAF):\n\n- **Frecuencias autorizadas:** 868.0 – 868.6 MHz y 869.4 – 869.65 MHz.\n- **Potencia máxima:** Hasta 500 mW PRA (27 dBm) en el segmento 869.4–869.65 MHz.\n- **Ciclo de trabajo (Duty Cycle):** Limitado por ley al 1% o 10% según la subbanda. Ajusta siempre tus cadencias con sensatez.",
                    'en' => "Yes, completely. Meshtastic operates on the European license-free ISM band without requiring an amateur radio license:\n\n- **Authorized Frequencies:** 868.0 – 868.6 MHz and 869.4 – 869.65 MHz.\n- **Maximum Output Power:** Up to 500 mW ERP (27 dBm) in 869.4–869.65 MHz.\n- **Duty Cycle:** Legally restricted to 1% or 10% depending on the sub-band. Please configure broadcast rates responsibly.",
                    'pt' => "Sim, absolutamente. O Meshtastic opera na banda ISM europeia sem necessidade de licença de radioamador:\n\n- **Frequências autorizadas:** 868.0 – 868.6 MHz e 869.4 – 869.65 MHz.\n- **Potência máxima:** Até 500 mW PRA (27 dBm) na faixa 869.4–869.65 MHz.\n- **Ciclo de trabalho (Duty Cycle):** Limitado por lei a 1% ou 10% consoante a sub-faixa. Configure sempre intervalos responsáveis.",
                ],
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'question' => [
                    'es' => '¿Cómo funcionan las notificaciones y los bots de la red?',
                    'en' => 'How do the community bots and alerts work?',
                    'pt' => 'Como funcionam os bots e alertas da rede?',
                ],
                'answer' => [
                    'es' => "Andalucía Mesh cuenta con servicios automáticos conectados a la telemetría en tiempo real:\n\n- **Bot de Telegram:** `{TELEGRAM_BOT_USERNAME}` te avisa de repetidores con batería crítica o caídas de enlaces.\n- **Comunidad Discord:** Espacio para soporte técnico, consultas de cobertura y proyectos de nodos.\n\nPuedes conocer más y configurar tus alertas en [Bots y Notificaciones](/bots).",
                    'en' => "Andalucía Mesh provides automated services connected to live mesh telemetry:\n\n- **Telegram Bot:** `{TELEGRAM_BOT_USERNAME}` sends notifications for low battery warnings and repeater outages.\n- **Discord Community:** Space for technical discussions, coverage tests, and hardware builds.\n\nLearn more and configure your alerts at [Bots and Notifications](/bots).",
                    'pt' => "A Andalucía Mesh disponibiliza serviços automáticos ligados à telemetria em direto:\n\n- **Bot do Telegram:** `{TELEGRAM_BOT_USERNAME}` envia avisos de bateria baixa e quebras de ligação em repetidores.\n- **Comunidade Discord:** Ponto de encontro para debater hardware, montagens e cobertura.\n\nConsulte mais informações em [Bots e Notificações](/bots).",
                ],
                'sort_order' => 9,
                'is_active' => true,
            ],
            [
                'question' => [
                    'es' => '¿Cómo puedo comprobar si la configuración de mi nodo es adecuada?',
                    'en' => 'How can I check whether my node configuration is optimal?',
                    'pt' => 'Como posso verificar se a configuração do meu nó está adequada?',
                ],
                'answer' => [
                    'es' => "Puedes utilizar el evaluador interactivo integrado en el portal:\n\n1. Accede a [Revisa tu nodo](/revisa-tu-nodo).\n2. Introduce el identificador hexadecimal de tu dispositivo (ejemplo: `!a1b2c3d4`) o su nombre visible.\n3. El sistema analizará los paquetes de los últimos 7 días y te mostrará sugerencias sobre consumo, cadencia y saturación.",
                    'en' => "You can use the interactive evaluator built into the portal:\n\n1. Visit [Check your node](/revisa-tu-nodo).\n2. Type your hex node ID (e.g. `!a1b2c3d4`) or its broadcast name.\n3. The system inspects packet traffic from the last 7 days and delivers tailored suggestions regarding battery drain, rate limits, and channel health.",
                    'pt' => "Pode utilizar o diagnóstico interativo disponível no portal:\n\n1. Aceda a [Verifique o seu nó](/revisa-tu-nodo).\n2. Introduza o identificador hexadecimal do nó (exemplo: `!a1b2c3d4`) ou o nome público.\n3. O sistema avaliará os pacotes dos últimos 7 dias e fornecerá recomendações práticas sobre bateria, cadência e ocupação do canal.",
                ],
                'sort_order' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($faqs as $faqData) {
            Faq::updateOrCreate(
                ['sort_order' => $faqData['sort_order']],
                $faqData
            );
        }
    }
}
