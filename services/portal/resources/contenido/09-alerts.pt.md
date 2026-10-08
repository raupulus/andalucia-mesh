# 09 · Alertas

> `/alertas` · Listagem em direto de alertas abertos e históricos da malha na Andaluzia, classificados por nível de risco e tipo de incidente.

## SEO

- **Título:** `Alertas da Malha · {PROJECT_NAME}`
- **Descripción:** `Anomalias detetadas na rede Andalucía Mesh (baterias fracas, loops de reinício, gateways desligados, spam de canal) com níveis de risco e tipos.`

## Borrador del texto

**H1:** Alertas

O sistema analisa continuamente os pacotes recebidos da malha e abre um alerta sempre que deteta uma condição anómala: repetidor com bateria crítica, nó em loop de reinício, gateway que deixa de publicar, canal saturado ou emissões excessivas. Cada alerta é resolvido automaticamente logo que a anomalia cessa.

### Níveis de Risco e Tipos

Cada incidência é classificada por gravidade e âmbito operacional:

| Nível de Risco | Significado |
|---|---|
| Alto | Falha ativa ou dano severo na conectividade da malha |
| Médio | Risco real para a infraestrutura ou zona comarcal; intervenção recomendada |
| Baixo | Aviso informativo preventivo; sem impacto imediato |

| Tipo de Incidente | Âmbito |
|---|---|
| Infraestrutura | Routers, repetidores orográficos, gateways MQTT, congestão de canal ou falhas globais |
| Clientes | Incidentes circunscritos a nós de utilizador individual (ex: bateria baixa pessoal ou parâmetros incorretos) |

### Se um alerta apontar para o teu nó

Um alerta não constitui uma acusação: as deteções são automatizadas e têm finalidade exclusivamente pedagógica e preventiva. Consulta o nosso [guia de configuração de nós](/configura-tu-nodo) para rever os teus parâmetros. A grande maioria das incidências resolve-se ajustando as cadências de transmissão, ativando `CLIENT_MUTE` ou reduzindo os saltos. Se considerares que o alerta foi despoletado por engano, contacta-nos em [{PROJECT_CONTACT}](mailto:{PROJECT_CONTACT}).

### Receber notificações em direto

Mantém-te informado através de bots comunitários ou webhooks:
- [Bots de Telegram e Discord](/bots)
- [Webhooks em tempo real](/api)
