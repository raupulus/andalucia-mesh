# 08 · Rankings

> `/rankings` · Estatísticas de consumo de espectro, nós em risco, qualidade de ligações e distribuição de tráfego na Andaluzia.

## SEO

- **Título:** `Rankings e Atividade da Malha · {PROJECT_NAME}`
- **Descripción:** `Descobre quais os nós que consomem mais tempo de ar, quais estão em risco e quem mais contribui para a malha da Andaluzia.`

## Borrador del texto

**H1:** Rankings

Como se comporta a malha e quem a sustenta: identifica que nós enfrentam anomalias neste momento, quais ocupam mais tempo de canal e quem oferece maior cobertura. Não se trata de uma competição: serve como diagnóstico comunitário para corrigir parâmetros e otimizar o espectro.

### Nós em Risco

Nós afetados por alertas abertos de risco médio ou alto: loops de reinício, bateria crítica, routers inativos, gateways desligados ou saturação de canal.

- **Risco Alto / Médio:** Assinala anomalias que requerem intervenção do operador.
- **Nó e Província:** Identificados por nome abreviado, nome longo e ID de nó.
- **Motivo e Início:** Resume a regra ativa que despoletou a incidência.

[Ver todos os alertas →](/alertas)

### Seletor de Período

Alterna entre **Hoje**, **Ontem**, **Últimos 7 dias** e **Últimos 30 dias**. Os períodos concluídos permanecem fixos; o período em curso atualiza-se em tempo real.

### Consumo de Rede por Nó

Tempo de ar ocupado pelos pacotes emitidos diretamente por cada nó (excluindo transmissões repetidas para terceiros). Consumos elevados decorrem frequentemente de intervalos excessivamente curtos de NodeInfo, posição ou telemetria. [Como ajustar intervalos →](/configura-tu-nodo#intervalos)

- **Tempo no Ar e Mix de Pacotes:** Repartição detalhada por tipo de tráfego (NodeInfo, Posição, Telemetria, Texto e Vizinhos).

*Tempo de ar estimado com base no tamanho dos pacotes e nos parâmetros de modulação LoRa. Cada pacote é contabilizado apenas uma vez, independentemente de quantos gateways o captaram.*

### Catálogo de Rankings da Rede

- **Gateways com Maior Cobertura:** Nós distintos captados por cada gateway.
- **Gateways Essenciais:** Pacotes recebidos exclusivamente por um único gateway—cobertura sem redundância alternativa.
- **Ligações Diretas Mais Longas:** Distância máxima em linha de vista entre nó e gateway sem saltos intermédios.
- **Melhores Ligações Diretas:** Maior relação sinal/ruído (SNR) média em ligações com amostragem significativa.
- **Nós Mais Conectados:** Maior número de vizinhos diretos de radiofrequência confirmados.
- **Nós Mais Estáveis:** Maior tempo contínuo de funcionamento sem reinícios.
- **Solares Mais Saudáveis:** Dispositivos solares que mantiveram a maior tensão mínima de bateria.
- **Mais Ativos em Texto:** Volume de mensagens partilhadas nos canais públicos.

### Distribuição de Tráfego (Mix de Pacotes)

Visualização da percentagem de largura de banda consumida por cada categoria de pacote. Mensagens de texto representam tipicamente uma fração muito reduzida; o tráfego automático de telemetria constitui a esmagadora maioria.
