# 13 · Política de Cookies

> `/legal/cookies` · Esclarecimento sobre a política estrita de zero cookies nas páginas públicas, armazenamento local no navegador e cookies técnicos do painel privado.

## SEO

- **Título:** `Política de Cookies · {PROJECT_NAME}`
- **Descripción:** `As páginas públicas de {PROJECT_NAME} não utilizam cookies. Apenas o painel privado de administração utiliza cookies técnicos de sessão.`

## Borrador del texto

**H1:** Política de Cookies

Última atualização: 8 de outubro de 2026

As páginas públicas deste portal funcionam com **zero cookies e sem rastreadores**. Por essa razão, não encontrarás qualquer aviso intrusivo de consentimento de cookies.

### O que guardamos no teu navegador

Apenas as tuas preferências de tema visual e de idioma, caso as alteres expressamente nos seletores da barra superior:
- **Tema visual:** Guardado no armazenamento local do navegador (`localStorage` com a chave `snm_theme`). Não é um cookie, não é enviado para o servidor e não serve para te identificar. Por defeito, a página adota automaticamente o tema do teu sistema operativo.
- **Idioma selecionado:** Guardado no `localStorage` com a chave `portal_locale` para manter a língua escolhida sem emitir cookies de sessão (`RN-06` / `RN-48`).

### Painel de Administração

A área `/admin` é privada e reservada exclusivamente aos operadores técnicos do projeto. Apenas ao aceder a essa consola privada são emitidos dois cookies técnicos de sessão estritamente necessários:

| Cookie | Finalidade | Duração |
|---|---|---|
| `andalucia_mesh_session` | Mantém a sessão autenticada do operador técnico | 120 minutos de inatividade ou fecho do navegador |
| `XSRF-TOKEN` | Proteção contra falsificação de pedidos em sites cruzados (anti-CSRF) | Sessão ativa (máx. 120 minutos) |

*Para os visitantes das páginas públicas comunitárias, estes cookies nunca são enviados nem instalados.*

### PotatoMesh e MeshView

Estes serviços operam software comunitário de código aberto em subdomínios dedicados. Não instalam cookies publicitários nem de rastreio de terceiros. O PotatoMesh utiliza armazenamento local (`IndexedDB` e `localStorage`) para acelerar a apresentação de pacotes e mapas em dispositivos móveis.

### Analítica e Rastreio

Não utilizamos Google Analytics, Meta Pixel nem qualquer serviço externo de monitorização comportamental.

### Como apagar dados locais

Podes limpar o armazenamento local e quaisquer dados temporários a qualquer momento nas definições de Privacidade e Segurança do teu navegador.
