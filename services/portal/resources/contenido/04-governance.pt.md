# 04 · Como é gerido

> `/como-se-gestiona` · Visão transparente sobre quem administra o projeto, como se tomam decisões, onde funciona e como a malha e a privacidade são protegidas.

## SEO

- **Título:** `Como é gerido · {PROJECT_NAME}`
- **Descripción:** `Quem administra {PROJECT_NAME}, onde está o servidor, como são tomadas as decisões e como a malha e a privacidade dos dados são salvaguardadas.`

## Borrador del texto

**H1:** Como é gerido

Um projeto independente, transparente e gerido com clareza e dedicação técnica.

### Quem o administra

{AUTOR_NOMBRE} ({AUTOR_NICK}) administra todo o sistema: servidor, serviços em contentores, integração de gateways, bots, alertas e o portal web. Os gateways pertencem a voluntários da comunidade que enviam dados com as suas credenciais. O projeto não gere os nós individuais de ninguém: cada nó é da inteira responsabilidade do seu respetivo operador.

### Como são tomadas as decisões

As decisões arquiteturais e técnicas são coordenadas por Raúl enquanto promotor e responsável pelo projeto. Propostas, sugestões e notificações de incidentes são bem-vindas em [{PROJECT_CONTACT}](mailto:{PROJECT_CONTACT}). A instalação de novos routers e repetidores estratégicos deve ser coordenada previamente para proteger a capacidade de rádio de toda a malha regional.

### Onde funciona

- Todos os serviços operam num servidor dedicado em centro de dados europeu ({HOSTING_LOCATION}). O fornecedor está indicado na [política de privacidade](/legal/privacidad).
- Cada serviço opera de forma isolada em contentores: uma falha num serviço não afeta a continuidade dos restantes.
- Combina software comunitário de código aberto, como MeshView e PotatoMesh, com desenvolvimento próprio: este portal, a ingestão de pacotes, o detetor de alertas, os bots e a API pública.
- As versões de software externo são fixadas e validadas rigorosamente antes de cada atualização.

### Como a malha é protegida

- O servidor funciona estritamente como recetor (apenas subida). Nada do que chega pela Internet é retransmitido para o rádio, mesmo que um gateway tenha downlink ativado por engano.
- Cada gateway possui credenciais MQTT próprias e só pode publicar no seu próprio canal.
- Apenas são aceites canais públicos constantes de uma lista fechada: o canal principal `SFNarrow`, os canais regionais `Andalucia` e `Iberia`, e os canais provinciais específicos (`Almeria`, `Cadiz`, `Cordoba`, `Granada`, `Huelva`, `Jaen`, `Malaga`, `Sevilla`, bem como `Ceuta` e `Melilla`). Mensagens diretas e canais privados fora desta lista são estritamente ignorados e descartados.
- Um motor inteligente de alertas monitoriza a rede continuamente, avisando de baterias fracas, loops de reinício, gateways caídos ou saturação de canal.

### Disponibilidade

Não existe garantia formal de disponibilidade (SLA). Toda a infraestrutura depende de um único servidor virtual: em caso de manutenção ou quebra, os serviços web, a API e os bots podem ficar temporariamente inacessíveis. O estado operacional é acompanhado através de um painel de administração privado.

### Custos e donativos

Os custos de alojamento, domínio e infraestrutura são suportados integralmente por Raúl. Não são aceites donativos nesta fase; essa possibilidade poderá ser equacionada futuramente.

### Os teus dados

Sobre os dados recolhidos, tempos de retenção e como solicitar a exclusão de nós, consulta a nossa [política de privacidade](/legal/privacidad).
