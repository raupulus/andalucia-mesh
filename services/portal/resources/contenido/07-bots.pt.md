# 07 · Bots

> `/bots` · Como funcionam os bots comunitários e como adicioná-los, configurá-los e geri-los nos teus grupos de Telegram e servidores de Discord.

## SEO

- **Título:** `Bots de Telegram e Discord · {PROJECT_NAME}`
- **Descripción:** `Recebe alertas da malha regional da Andaluzia diretamente no teu grupo de Telegram ou servidor Discord e consulta telemetria por comandos.`

## Borrador del texto

**H1:** Bots

Leva os alertas da malha comunitária para os teus grupos de Telegram e servidores de Discord. Os bots notificam automaticamente quando o sistema deteta uma anomalia de infraestrutura (bateria fraca em repetidores, loops de reinício, gateways desligados, tempestades de canal) e quando o incidente é resolvido. Também respondem a comandos informativos de telemetria.

### O que notificam

Cada alerta possui um **nível de risco** e um **tipo de incidente**.

| Nível de Risco | Significado |
|---|---|
| Alto | Falha ativa ou dano severo na conectividade da malha |
| Médio | Risco real para a infraestrutura ou zona comarcal; intervenção recomendada |
| Baixo | Aviso informativo preventivo; sem impacto imediato |

| Tipo de Incidente | Âmbito |
|---|---|
| Infraestrutura | Routers, repetidores orográficos, gateways MQTT, congestão de canal ou falhas globais |
| Clientes | Incidentes circunscritos a nós de utilizador individual (ex: bateria baixa pessoal ou parâmetros incorretos) |

Consulta todos os alertas ativos e históricos no [Painel de Alertas](/alertas).

### Bot de Telegram

[Abrir @{TELEGRAM_BOT_USERNAME} no Telegram](https://t.me/{TELEGRAM_BOT_USERNAME})

**Num grupo**

1. Adiciona `@{TELEGRAM_BOT_USERNAME}` como membro do grupo.
2. O bot publica uma mensagem de boas-vindas e inicia o envio de alertas com os filtros predefinidos.
3. Os administradores do grupo podem ajustar filtros a qualquer momento através de `/levels` e `/types`.

**Num canal**

1. Adiciona o bot como **administrador** com permissão de publicação de mensagens.
2. Um administrador envia o comando de configuração diretamente no canal (ex: `/levels medio alto`). O bot aplica a alteração e apaga o comando para manter o canal limpo.

**Em conversa privada**

Executa comandos de diagnóstico direto (`/status`, `/battery`, `/routers`). Em mensagens diretas privadas não são enviados alertas automáticos para evitar ruído.

**Para remover o bot**, expulsa-o do grupo ou canal. O bot deteta a remoção e cancela as subscrições de imediato.

### Bot de Discord (Brevemente)

**Estado:** Em fase de homologação e testes de carga.

No Discord, o bot associa-se ao servidor completo. Depois de convidado, escolhes quais os canais que recebem alertas.

1. Abre a ligação de convite e seleciona o teu servidor.
2. No canal pretendido, um membro com permissão para gerir canais escreve `/subscribe`.
3. Ajusta filtros independentes por canal com `/levels` e `/types`.

**Para remover:** escreve `/unsubscribe` no canal ou expulsa o bot do servidor.

### Comandos Disponíveis

| Comando | O que faz | Permissões |
|---|---|---|
| `/status` | Estado geral da malha: nós ativos em 24h, routers, gateways, saturação média e alertas abertos | Qualquer utilizador |
| `/battery` | Nível de bateria dos routers e repetidores ordenados do menor para o maior | Qualquer utilizador |
| `/routers` | Lista de routers com tensão de bateria, ocupação de canal (`chutil`) e tempo de transmissão (`tx`) | Qualquer utilizador |
| `/levels [riscos...]` | Mostra ou atualiza os níveis de risco subscritos no canal. Exemplo: `/levels medio alto` | Ver: todos. Alterar: administradores |
| `/types [tipos...]` | Mostra ou atualiza os tipos de incidente subscritos. Exemplo: `/types infraestructura clientes` | Ver: todos. Alterar: administradores |
| `/settings` | Exibe a configuração ativa do canal atual | Qualquer utilizador |
| `/help` | Guia rápido de comandos e ligação para a documentação | Qualquer utilizador |
| `/subscribe` (Discord) | Ativa o envio de alertas no canal atual | Gestores de canais |
| `/unsubscribe` (Discord) | Desativa o envio de alertas no canal atual | Gestores de canais |

### Filtros Predefinidos

Novos canais iniciam com filtros padrão (`{BOT_RIESGOS_DEFECTO}` / `{BOT_TIPOS_DEFECTO}`), cobrindo incidentes moderados e críticos de infraestrutura.

### Limitação de Cadência e Anti-Spam

O bot aplica limitação de cadência por canal, agrupa notificações em rajada da mesma regra e só renova avisos abertos quando o nível de severidade se altera.

### Webhooks

Preferes receber alertas no teu próprio servidor ou sistema de monitorização? Enviamos pedidos `POST` assinados. Consulta a nossa [documentação de API e Webhooks](/api).

### Privacidade

Os bots guardam unicamente o identificador de chat e as preferências de filtros. Nunca leem conversas de membros e operam em modo de privacidade estrito no Telegram.
