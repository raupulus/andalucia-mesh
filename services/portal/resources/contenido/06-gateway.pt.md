# 06 · Liga o teu gateway

> `/conecta-tu-gateway` · Instruções para ligar um nó com acesso à Internet e enviar dados para o servidor comunitário, com TLS e downlink estritamente desativado.

## SEO

- **Título:** `Liga o teu gateway · {PROJECT_NAME}`
- **Descripción:** `Envia para a malha o que o teu nó escuta: credenciais MQTT abertas, parâmetros exatos e a razão pela qual o downlink deve estar sempre desligado.`

## Borrador del texto

**H1:** Liga o teu gateway

Um gateway é um nó com ligação à Internet que envia para o nosso servidor comunitário o que capta por radiofrequência. Quantos mais gateways existirem ativos na região, mais abrangentes e precisos se tornam os mapas, estatísticas e alertas. O fluxo funciona apenas num sentido: nenhum dado proveniente da Internet é retransmitido para o espectro de rádio.

### O que necessitas

- Um nó Meshtastic com WiFi ou Ethernet, ou emparelhado via Bluetooth com a app móvel atuando como ponte MQTT.
- Configurado segundo o nosso [guia de configuração de nós](/configura-tu-nodo).
- Ligação direta imediata: podes ligar-te utilizando as credenciais comunitárias abertas de apenas subida.

### Passos

1. **Acede às definições de MQTT** na aplicação Meshtastic (`Module Configuration → MQTT`).
2. **Aplica os parâmetros** apresentados na tabela abaixo.
3. **Verifica:** Em poucos minutos o teu nó surgirá a enviar pacotes no MeshView e PotatoMesh.

### Parâmetros de Configuração

| Parâmetro | Valor Exigido |
|---|---|
| MQTT Ativado | Sim |
| Servidor (Address) | `mqtt.{PROJECT_DOMAIN}` |
| Porta | `8883` (com TLS obrigatório) |
| Utilizador (Username) | `{MQTT_GATEWAY_USER}` |
| Palavra-passe (Password) | `{MQTT_GATEWAY_PASSWORD}` |
| Cifragem | Ativada |
| JSON | Desativado |
| TLS | Ativado |
| Tópico Raiz (Root Topic) | `{MQTT_TOPIC_ROOT}` |
| Map reporting | Ativado |
| Uplink | Ativado em `{PRIMARY_CHANNEL}` e nos canais autorizados |
| Downlink | Desativado em todos os canais (Sempre desativado) |
| OK to MQTT | Ativado |
| Ignore MQTT | Ativado |

### Canais Autorizados no Servidor

O servidor de ingestão apenas aceita pacotes nos seguintes canais autorizados, com a grafia e maiúsculas exatas:

`{ALLOWED_CHANNELS}`

Todos estes canais são públicos: as suas mensagens são visíveis no PotatoMesh e MeshView e podem ser indexadas. Não transmitas dados privados ou sensíveis em canais públicos.

### Justificação de cada parâmetro

- **Cifragem ativada:** Os pacotes viajam em formato nativo protobuf. Apenas os canais públicos com chave predefinida são descifrados pelo servidor.
- **JSON desativado:** O firmware moderno utiliza protobuf binário; o JSON é obsoleto e rejeitado.
- **Root topic `{MQTT_TOPIC_ROOT}`:** Deve ser introduzido manualmente para garantir o encaminhamento correto.
- **TLS obrigatório (Porta 8883):** Todas as ligações ao broker são cifradas para impedir interceções ou adulterações em trânsito.
- **Map reporting:** Divulga a identificação e coordenadas do nó para alimentar os mapas comunitários.
- **OK to MQTT:** Identifica os pacotes como autorizados para ingestão na nuvem.
- **Ignore MQTT:** Evita que o teu rádio retransmita pacotes injetados na malha através da Internet.

### Acesso Seguro e Sem Downlink

- O broker comunitário opera estritamente em modo de receção (apenas subida). Os gateways não têm permissões para subscrever fluxos globais. Mesmo que o downlink seja ativado por engano no dispositivo, nenhum tráfego da Internet será transmitido para as ondas de rádio.

### Perguntas Frequentes

**O meu nó não surge nos mapas.** Confirma que o Root Topic corresponde exatamente a `{MQTT_TOPIC_ROOT}`, a porta é `8883` com TLS ativo, o uplink está ligado e os nomes dos canais coincidem com a lista autorizada. Se a situação persistir, contacta-nos em [{PROJECT_CONTACT}](mailto:{PROJECT_CONTACT}).

**Posso ligar-me com as credenciais públicas?** Sim, as credenciais `{MQTT_GATEWAY_USER}` / `{MQTT_GATEWAY_PASSWORD}` estão abertas a toda a comunidade com permissões de apenas subida.

**O que acontece se o meu gateway for desligado?** Nada de grave. Se interromper a publicação, o sistema regista um aviso de infraestrutura que é cancelado automaticamente quando o nó regressar.

**Como desativar o envio de dados?** Basta desativar o módulo MQTT na aplicação do nó.

### Proteção de Dados

Os pacotes enviados alimentam o PotatoMesh, o MeshView e as análises estatísticas da malha, sendo eliminados periodicamente conforme a nossa [política de privacidade](/legal/privacidad).
