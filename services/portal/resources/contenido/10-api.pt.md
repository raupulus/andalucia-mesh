# 10 · API Pública e Webhooks

> `/api` · Documentação técnica completa da API pública JSON de apenas leitura e dos webhooks de alertas.

## SEO

- **Título:** `API Pública e Webhooks · {PROJECT_NAME}`
- **Descripción:** `API pública de {PROJECT_NAME}: estado da malha, províncias, rankings, routers e alertas em JSON, sem registo, e webhooks assinados.`

## Borrador del texto

**H1:** API Pública

Todos os dados públicos de {PROJECT_NAME} acessíveis através de endpoints JSON limpos e estruturados: estado geral da rede, nós ativos, ocupação de canal por província, rankings, repetidores e alertas. Trata-se da mesma API que alimenta este portal e os bots comunitários. É estritamente de leitura e não exige registo prévio.

### Aspetos Fundamentais

| Parâmetro | Especificação |
|---|---|
| URL Base | `https://{PROJECT_DOMAIN}/api/v1` |
| Métodos HTTP | Apenas `GET` |
| Autenticação | Nenhuma |
| Formato | `application/json` |
| Limite de pedidos | 60 pedidos por minuto por endereço IP |
| CORS | Ativo (`*`) para pedidos `GET` |
| Datas e horas | ISO 8601 em UTC |
| Províncias | Códigos ISO 3166-2: `ES-AL`, `ES-CA`, `ES-CO`, `ES-GR`, `ES-H`, `ES-J`, `ES-MA`, `ES-SE` |

Todas as respostas contêm:
- `generated_at`: Momento exato em que os dados foram gerados.
- `notes`: Notas explicativas sobre a amostragem (ex: apenas constam nós com OK to MQTT ativo).
- `stale: true`: Indica se a resposta foi obtida a partir de cache durante reinícios de serviços.

*A API nunca divulga coordenadas GPS exatas. Fornece apenas agregações estatísticas, identificadores públicos e províncias associadas.*

### Resumo dos Endpoints

| Rota | Descrição | Intervalo de Atualização |
|---|---|---|
| `GET /stats/summary` | Visão geral da saúde e atividade da malha | 60 s |
| `GET /stats/provinces` | Nós e ocupação de canal discriminados por província | 60 s |
| `GET /stats/rankings` | Catálogo de tabelas estatísticas disponíveis | 1 h |
| `GET /stats/rankings/{id}` | Métricas detalhadas de um ranking específico | 60 s – 15 min |
| `GET /stats/traffic-mix` | Repartição do tráfego por categoria de pacote | 5 min |
| `GET /routers` | Lista de routers com bateria, canal e emissão | 60 s |
| `GET /alerts` | Listagem filtrável de alertas abertos e históricos | 30 s |
| `GET /alerts/{id}` | Ficha de alerta individual com ciclo de vida | 30 s |
| `GET /alerts/catalog` | Catálogo de regras e tipos de incidentes ativos | 1 h |
| `GET /nodes/at-risk` | Nós com alertas abertos neste momento | 30 s |

### Webhooks

Para servidores externos e sistemas de monitorização que pretendam receber alertas em tempo real logo que uma incidência é detetada ou concluída, disponibilizamos webhooks HTTP `POST` assinados.

- **Payload:** Esquema JSON completo do alerta com metadados de transição de estado.
- **Segurança:** Assinatura digital no cabeçalho `X-Hub-Signature-256` calculada com HMAC SHA-256.

Para solicitar o registo de um webhook para a tua infraestrutura, envia uma mensagem para [{PROJECT_CONTACT}](mailto:{PROJECT_CONTACT}).
