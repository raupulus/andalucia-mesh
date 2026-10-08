# 05 · Configura o teu nó

> `/configura-tu-nodo` · Guia para configurar qualquer nó Meshtastic em minutos, compreender os parâmetros recomendados e proteger o espectro de rádio da malha comunitária.

## SEO

- **Título:** `Configura o teu nó · {PROJECT_NAME}`
- **Descripción:** `Parâmetros recomendados para o teu nó na malha da Andaluzia: rádio, funções, saltos, intervalos de transmissão e privacidade.`

## Borrador del texto

**H1:** Configura o teu nó

Com estes parâmetros, o teu nó sintoniza a malha comunitária sem sobrecarregar o espectro de rádio. Se o teu equipamento é recente, o processo demora poucos minutos; se já se encontra em funcionamento, revê os valores e corrige discrepâncias.

**Porque é importante:** Um dispositivo de rádio não consegue transmitir e escutar simultaneamente, e cada pacote ocupa o canal para todos os nós vizinhos. A maior parte do tráfego não corresponde a mensagens de texto, mas sim a telemetria periódica automática: identidade do nó, localização GPS e métricas de sistema. Quanto menor for o volume emitido, mais capacidade livre existe para comunicações úteis.

### 1. Rádio

A grande maioria da malha comunitária na Península utiliza este perfil manual de banda estreita. Se o teu nó utilizar parâmetros distintos, não conseguirá comunicar com os restantes. A banda estreita oferece maior alcance e menor suscetibilidade a colisões face às predefinições de fábrica.

| Parâmetro | Valor Recomendado |
|---|---|
| Região (Region) | `{LORA_REGION}` (European Union 868 MHz) |
| Usar preset (Predefined) | Desativado (para desbloquear campos manuais) |
| Largura de banda (Bandwidth) | `{LORA_BANDWIDTH}` (ou {LORA_BANDWIDTH_KHZ} kHz) |
| Spreading factor | `{LORA_SPREAD_FACTOR}` |
| Coding rate | `{LORA_CODING_RATE}` (4/5) |
| Frequency slot | `{LORA_FREQUENCY_SLOT}` |
| Frequency override | `{LORA_FREQUENCY_MHZ}` MHz (ou 869.6188 MHz) |
| Nome do preset | `SFNarrow` |
| Canal principal (0): Nome | `{PRIMARY_CHANNEL}` |
| Canal principal (0): Chave PSK | `AQ==` (chave pública comunitária predefinida) |
| Limite de saltos (Hop Limit) | 3 a 4 (5 para `CLIENT_MUTE` ou zonas periféricas) |

> ⚠️ **Aviso de Segurança Fundamental:** Nunca ligues a alimentação nem inicializes a tua placa LoRa sem ter a antena de 868 MHz devidamente instalada. A emissão sem carga de antena pode queimar o amplificador de potência de radiofrequência (PA).

Como configurar na aplicação Meshtastic:

1. Na aplicação, acede a `Radio Configuration → LoRa` e seleciona a região `{LORA_REGION}`.
2. Desativa a opção **Usar preset (Predefined)** para desbloquear os campos de edição manual.
3. Define Bandwidth para `{LORA_BANDWIDTH}` (ou 62.5 kHz), Spreading Factor para `{LORA_SPREAD_FACTOR}` e Coding Rate para `{LORA_CODING_RATE}`.
4. Ajusta o **Frequency slot** para `{LORA_FREQUENCY_SLOT}` ou preenche `{LORA_FREQUENCY_MHZ}` MHz em **Frequency override**.
5. Em `Channels`, renomeia o Canal 0 para `{PRIMARY_CHANNEL}` e confirma que a chave PSK é `AQ==`.
6. Define o Hop Limit para 3 ou 4 (utiliza 5 apenas em localizações periféricas isoladas ou em nós `CLIENT_MUTE`).

Se estás a configurar um nó remotamente por rádio, altera as definições pela seguinte ordem para não perderes a ligação: rádio do nó remoto → rádio do nó local → canal do nó remoto → canal do nó local.

### 2. Função Operacional (Role)

| Função | Quando aplicar |
|---|---|
| `CLIENT_MUTE` | Recomendado para a maioria: nós pessoais, instalações interiores ou locais sem linha de vista ampla. Recebe e emite localmente sem repetir pacotes alheios |
| `CLIENT` | Estações exteriores elevadas em terraços ou telhados desobstruídos. Repete pacotes |
| `ROUTER` | Exclusivo para infraestruturas fixas em pontos orográficos estratégicos, coordenadas previamente com a comunidade regional. O backbone existente já se encontra estabelecido; nós router adicionais não coordenados provocam colisões graves |
| `ROUTER_LATE`, `CLIENT_BASE` | Não recomendadas |

Não é necessário que todos os nós atuem como repetidores: quando todos repetem, o mesmo pacote ecoa indefinidamente no canal. Em caso de dúvida, seleciona `CLIENT_MUTE`.

### 3. Limite de Saltos (Hop Limit)

- **Recomendado: 3 a 4 saltos.** Na Andaluzia, 3 a 4 saltos são plenamente eficazes para propagar mensagens sem saturar o espectro.
- **5 saltos:** Válido apenas para nós interiores em `CLIENT_MUTE` ou localizações comarcais isoladas nos limites da malha.
- **6 ou mais saltos:** Prejudica a rede ao multiplicar transmissões redundantes; o detetor de anomalias assinala esta condição automaticamente (`hops-high`).

### 4. Intervalos de Emissão

| Tipo de Pacote | Intervalo Recomendado |
|---|---|
| NodeInfo | 72 horas |
| Posição (estação fixa) | 72 horas |
| Posição (nó móvel) | Mínimo 1 hora |
| Telemetria do dispositivo (solar) | 4 horas ou superior |
| Telemetria do dispositivo (router) | 6 horas ou superior |
| Telemetria (alimentação fixa) | Desativada |
| Telemetria ambiental (sensores) | Desativada, ou 4+ horas |
| Telemetria elétrica | Desativada |

As transmissões automáticas compõem a maior parte da ocupação do canal. Nós que transmitem em cadências curtas surgem no topo da [tabela de consumo de espectro](/rankings).

### 5. Posição e Privacidade

- Desativa bandeiras de posição adicionais para reduzir o tamanho dos pacotes.
- Desativa o modo smart position.
- Se pretendes proteger a tua localização exata, reduz a precisão de coordenadas no canal.
- Mantém **OK to MQTT** ativo se pretendes que o teu nó conste nos mapas e estatísticas comunitárias.
- **Para não figurar nos mapas públicos, desativa OK to MQTT no dispositivo.** Os dados já recolhidos expiram e são eliminados de acordo com a nossa [política de privacidade](/legal/privacidad).

### 6. Práticas que prejudicam a malha

- Configurar número excessivo de saltos (> 4).
- Atribuir o papel `ROUTER` sem coordenação prévia.
- Cadências de envio mais curtas do que o recomendado.
- Deixar ativo o módulo de teste de alcance (*RangeTest*).
- Ativar o modo de radioamador (que remove o cifrado dos canais).

### Passo seguinte

O teu nó tem acesso à Internet? Liga-o como gateway MQTT e ajuda a monitorizar a malha regional.

[Liga o teu gateway](/conecta-tu-gateway)
