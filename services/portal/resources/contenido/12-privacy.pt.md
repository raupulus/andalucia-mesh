# 12 · Política de Privacidade

> `/legal/privacidad` · Transparência sobre dados recolhidos por rádio, prazos de retenção, política estrita de zero cookies e instruções de exclusão voluntária.

## SEO

- **Título:** `Política de Privacidade · {PROJECT_NAME}`
- **Descripción:** `Tratamento de telemetria, retenção de dados, navegação sem cookies e opções de exclusão da malha comunitária {PROJECT_NAME}.`

## Borrador del texto

**H1:** Política de Privacidade

Última atualização: 8 de outubro de 2026

Em resumo:
- Apresentamos informação que os nós emitem por radiofrequência em canais públicos e que autorizam explicitamente através de **OK to MQTT**.
- As páginas públicas deste portal funcionam com uma política rigorosa de **zero cookies e sem rastreadores**.
- Se não desejas que o teu nó conste nos mapas, desativa a opção **OK to MQTT** no equipamento.
- Para exercer direitos sobre os teus dados: [{PROJECT_CONTACT}](mailto:{PROJECT_CONTACT}).

### 1. Responsável pelo Tratamento

Raúl Caro Pastorino (@raupulus). Contacto: [{PROJECT_CONTACT}](mailto:{PROJECT_CONTACT}).

### 2. Categorias de Dados e Origem

A malha de rádio é pública, mas alguns identificadores técnicos podem relacionar-se com indivíduos.

| Categoria de Dados | Origem | Âmbito de Divulgação |
|---|---|---|
| ID do Nó, Nome Curto e Longo | Emissões de rádio captadas por gateways comunitários | PotatoMesh, MeshView, rankings, alertas, bots e API pública |
| Posição e Coordenadas Aproximadas | Transmitidas com a precisão configurada pelo operador | PotatoMesh e MeshView. No portal e API pública, apenas agregações por província |
| Mensagens em Canais Públicos | Transmissões de rádio nos canais autorizados | PotatoMesh, MeshView e chat direto por WebSocket. Indexáveis por motores de busca |
| Telemetria (tensão, ocupação, uptime) | Pacotes de rádio com OK to MQTT ativo | Painéis estatísticos, rankings, alertas, bots e API |
| Identificador de Gateway | Autenticação no servidor MQTT | Registos de auditoria e alertas de infraestrutura |
| Endereço IP de Visitantes Web | Pedidos HTTP ao servidor web | Registos de segurança e controlo de cadência da API |

*O que nunca tratamos: Mensagens privadas diretas, canais privados com chave própria ou coordenadas residenciais exatas.*

### 3. Finalidades do Tratamento

- Exibir o estado da malha, métricas de conectividade e rankings regionais.
- Detetar anomalias operacionais (bateria em descarga crítica, loops de reinício, quebras de gateways).
- Gerir ligações de gateways e disponibilizar a API pública comunitária.
- Proteger os servidores contra abusos de pedidos e ataques de negação de serviço.

### 4. Base Jurídica

O tratamento assenta no interesse legítimo de operar, manter e proteger uma rede cidadã de telecomunicações de rádio:
- Os dados são difundidos voluntariamente pelo próprio dispositivo nas frequências de rádio e com a opção "OK to MQTT" ativa.
- São tratados apenas canais públicos autorizados.
- O portal web apresenta contagens agregadas a nível de província.
- Aplicam-se prazos de retenção e eliminação estritamente definidos.

### 5. Prazos de Retenção

| Repositório | Prazo de Conservação |
|---|---|
| MeshView | 14 dias |
| PotatoMesh | 30 dias |
| Pacotes e Telemetria em Bruto | 30 dias para pacotes; 90 dias para telemetria |
| Histórico de Alertas | 1 ano |
| Registos do Servidor Web (IP) | 30 dias |
| Estatísticas Agregadas Anónimas | Ilimitado |

### 6. Alojamento e Partilha de Dados

Os serviços são alojados em centro de dados na União Europeia ({HOSTING_LOCATION}) fornecido por {HOSTING_PROVIDER}. Não são efetuadas transferências de dados para fora do Espaço Económico Europeu nem qualquer comercialização de informação.

### 7. Como não constar no portal da malha

Se pretendes que o teu nó não seja indexado nem conste nos mapas:
1. Abre a aplicação Meshtastic no teu telemóvel ou computador.
2. Nas definições de LoRa ou MQTT, **desativa a opção OK to MQTT**.
3. Os gateways respeitam esta bandeira e deixarão imediatamente de enviar pacotes do teu nó para a nuvem.
4. Os dados previamente armazenados serão eliminados automaticamente ao atingir o prazo de retenção. Para solicitar eliminação antecipada, escreve para [{PROJECT_CONTACT}](mailto:{PROJECT_CONTACT}).

### 8. Navegação no Portal Web

As páginas públicas deste portal funcionam sem cookies de sessão, sem armazenamento persistente de sessão no servidor e sem ferramentas analíticas de terceiros. Os registos de acesso do servidor mantêm os endereços IP durante 30 dias unicamente para segurança e controlo de tráfego abusivo.
