# UTM Generator System - Documentação Atualizada

> **Sistema de Geração e Gerenciamento de UTMs para Sales Prime**  
> Última atualização: 06 de Fevereiro de 2026 (15:42 BRT)  
> Localização: `c:\xampp\htdocs\utm`

---

## 📝 Changelog - Fevereiro 2026

### Versão 2.2 - 09/02/2026

#### 🏗️ Arquitetura & Backend
1. **Singleton Database** — `db.php` refatorado com classe `Database::getInstance()` + backward-compatible `$pdo`
2. **`config.php`** *(NOVO)* — Configuração central: sessões, constantes de segurança, helpers (`sanitize()`, `isLoggedIn()`, `generateCsrfToken()`, `validateShortCode()`, `validateUrl()`)
3. **`api/create_utm.php`** *(NOVO)* — Endpoint JSON para criação de UTMs via AJAX com validações server-side
4. **`api/check_shortcode.php`** *(NOVO)* — Verificação AJAX de disponibilidade de short code
5. **`redirect.php`** *(NOVO)* — Redirect alternativo com tracking detalhado (tabela `clicks`)
6. **`export.php`** *(NOVO)* — Exportação CSV de UTMs com contagem de cliques (UTF-8 BOM, separador `;`)

#### 📊 Banco de Dados
7. **`migrations/v2.2_add_tables.sql`** *(NOVO)* — Tabelas: `clicks` (FK→urls), `login_attempts` (rate limiting), `settings` (key/value)

#### 🎨 Frontend
8. **`utm-generator.js`** *(NOVO)* — Módulo JS com padrões Strategy + Observer, preview com debounce, validações client-side
9. **CSS Design System v2.2** — Variáveis `:root` consolidadas (cores, transições, sombras, border-radius), suporte dark mode, estilos URL Preview, animação slide-in, print styles
10. **URL Preview Card** — Preview em tempo real no formulário com word-break e monospace
11. **Botão Exportar CSV** — Adicionado ao formulário (visível apenas para usuários logados)

#### 🔒 Segurança
12. **Rate Limiting** — Login bloqueado após 5 tentativas em 15 min (tabela `login_attempts`)
13. **Session Fixation** — `session_regenerate_id(true)` após login bem-sucedido
14. **XSS** — `htmlspecialchars()` em todas as entradas e saídas
15. **Bcrypt Cost** — Aumentado de default para 12
16. **Error Masking** — Mensagens de erro de PDO não são mais expostas ao usuário

#### 📈 Click Tracking
17. **`go.php` aprimorado** — Agora registra cliques detalhados (IP, User Agent, Referer) na tabela `clicks`

#### 📊 Dashboard Analytics
18. **`dashboard.php`** *(NOVO)* — Página de analytics com Chart.js: 4 KPI cards (gradiente), Doughnut Chart (por fonte), Line Chart (tendência), Top 10 ranking, tabela por usuário
19. **`api/dashboard_data.php`** *(NOVO)* — Endpoint JSON com KPIs, distribuição por fonte, tendência temporal, top UTMs e stats por usuário
20. **`assets/css/dashboard.css`** *(NOVO)* — Estilos para cards KPI, gráficos, tabelas ranking com badges de medalha, dark mode, responsivo
21. **Botão Analytics** — Adicionado ao nav de `index.php` (visível apenas para usuários logados)

#### ✨ Polish & Production
22. **Acessibilidade** — `lang="pt-BR"`, ARIA labels em 12 botões icon-only, `role="radiogroup"` em grupos, `aria-live="polite"` no preview, `aria-hidden` em ícones decorativos
23. **Preview Premium** — Badges coloridos por parâmetro UTM (campaign=azul, source=verde, medium=blue, content=roxo, term=amber) com dark mode
24. **Performance** — `loading="lazy"` em QR Codes, índices compostos SQL (`v2.3_indexes.sql`), `focus-visible` para keyboard nav
25. **Polish Visual** — `scroll-behavior: smooth`, transições hover na tabela, `:focus-visible` outline global

---

### Versão 2.1 - 06/02/2026

#### ✅ Alterações Implementadas

1. **Canais (utm_campaign)**
   - ❌ Removidos: `Email-Marketing` e `Newsletter`
   - ✅ Mantidos: Sales-Prime, Dani-Martins, Prosperus, Lumiere, Prime, PodVender, Joel-Jota, PodCast

2. **UTM Source**
   - ➕ **Adicionados**: 
     - `email` (📧) - Para campanhas de email
     - `site` (🌐) - Para links internos do site
   - 🎨 **Interface**: Todos os botões agora exibem apenas ícones (sem texto)
   - 📋 **Sources disponíveis**: Instagram, YouTube, LinkedIn, TikTok, Threads, Spotify, WhatsApp, Apple, Amazon, Deezer, Email, Site

3. **Mídia Offline**
   - ➕ **Adicionado**: Opção `EVENTO`
   - 🔧 **Comportamento**: EVENTO funciona igual a PALESTRA (oculta Source/Medium no formulário e no código gerado)
   - 📋 **Opções disponíveis**: Livro, PDF, Palestra, Evento

4. **Lógica de Geração**
   - ✅ PALESTRA e EVENTO não exigem utm_source nem utm_medium
   - ✅ Formato gerado: `MO_PALESTRA` ou `MO_EVENTO`
   - ✅ Campos de Source e Medium são ocultados automaticamente

5. **JavaScript (script.js)**
   - ✅ Adicionado suporte para `email` e `site` no `mediumMap`
   - ✅ Opções de medium para email: EMAIL_MARKETING, EMAIL_TRANSACIONAL, EMAIL_NEWSLETTER
   - ✅ Opções de medium para site: SITE_BANNER, SITE_POPUP, SITE_FOOTER, SITE_MENU
   - ✅ Lógica de EVENTO implementada (oculta Source/Medium como PALESTRA)

---

## 🎯 Visão Geral do Sistema

O **UTM Generator** é uma aplicação web desenvolvida para a equipe Sales Prime gerenciar campanhas de marketing através da criação, rastreamento e análise de URLs com parâmetros UTM padronizados.

### Funcionalidades Principais

- ✅ **Geração de UTMs**: Criação automatizada de URLs com parâmetros UTM padronizados
- ✅ **Encurtamento de URLs**: Sistema de short codes personalizáveis
- ✅ **Rastreamento de Cliques**: Contabilização de acessos por UTM
- ✅ **Gerenciamento de Usuários**: Sistema de autenticação com aprovação administrativa
- ✅ **Painel Administrativo**: Controle de usuários e membros da equipe
- ✅ **Gestão de Equipe**: Cadastro de Closers, SDRs e Social Sellers
- ✅ **Modo Claro/Escuro**: Interface adaptável
- ✅ **Habilitação/Desabilitação**: Controle de status de UTMs

---

## 📊 Parâmetros UTM

### utm_campaign (Canais)

Identifica o canal de marketing responsável pela campanha.

**Opções disponíveis:**
- Sales-Prime
- Dani-Martins
- Prosperus
- Lumiere
- Prime
- PodVender
- Joel-Jota
- PodCast

### utm_source (Origem)

Plataforma de origem do tráfego. **Todos os botões exibem apenas ícones.**

| Source | Ícone | Descrição |
|--------|-------|-----------|
| `ig` | 📷 Instagram | Instagram |
| `yt` | ▶️ YouTube | YouTube |
| `in` | 💼 LinkedIn | LinkedIn |
| `tktk` | 🎵 TikTok | TikTok |
| `thrd` | 🧵 Threads | Threads |
| `spot` | 🎧 Spotify | Spotify |
| `wpp` | 💬 WhatsApp | WhatsApp |
| `appl` | 🍎 Apple | Apple Podcasts |
| `amz` | 📦 Amazon | Amazon Music |
| `dzr` | 🎶 Deezer | Deezer |
| `email` | 📧 Email | **NOVO** - Campanhas de email |
| `site` | 🌐 Site | **NOVO** - Links internos do site |

### utm_medium (Meio)

Formato ou tipo de conteúdo. Opções dinâmicas baseadas no `utm_source` selecionado.

#### Instagram (ig)
- INSTAGRAM_FEED
- INSTAGRAM_STORIES
- INSTAGRAM_REELS
- INSTAGRAM_BIO
- INSTAGRAM_DIRECT

#### YouTube (yt)
- YOUTUBE_DESCRICAO
- YOUTUBE_CARD
- YOUTUBE_BIO
- YOUTUBE_COMUNIDADE
- YOUTUBE_VIDEO

#### LinkedIn (in)
- LINKEDIN_POST
- LINKEDIN_ARTIGO
- LINKEDIN_BIO
- LINKEDIN_INMAIL

#### Email (email) - **NOVO**
- EMAIL_MARKETING
- EMAIL_TRANSACIONAL
- EMAIL_NEWSLETTER

#### Site (site) - **NOVO**
- SITE_BANNER
- SITE_POPUP
- SITE_FOOTER
- SITE_MENU

### utm_content (Tipo de Origem)

Gerado automaticamente baseado na seleção de Origem/Fonte.

#### Tipos Simples
- `TP` - Tráfego Pago
- `TO` - Tráfego Orgânico
- `SEM` - Search Engine Marketing
- `TJJ` - Time Joel Jota
- `TV` - Televisão
- `APP` - Aplicativo
- `WEBINAR` - Webinar (sem Source/Medium)

#### Equipe Comercial
- `{NOME}_CLOSER` - Comercial (Closer)
- `{NOME}_SDR` - SDR
- `{NOME}_SSELL` - Social Selling
- `{NOME}_CS` - Customer Success

#### Mídia Offline
- `MO_LIVRO_{NOME}` - Livro
- `MO_PDF_{NOME}` - PDF/eBook
- `MO_PALESTRA` - Palestra (sem Source/Medium)
- `MO_EVENTO` - **NOVO** - Evento (sem Source/Medium)

> **⚠️ Importante:** WEBINAR, PALESTRA e EVENTO não utilizam utm_source nem utm_medium.

---

## 🔧 Arquivos Modificados

### 1. `index.php`

**Linhas 247-259**: Removidos botões Email-Marketing e Newsletter
```php
// REMOVIDO:
// <input type="radio" ... id="profile_emailmkt" value="Email-Marketing">
// <input type="radio" ... id="profile_newsletter" value="Newsletter">
```

**Linhas 408-467**: Atualizado UTM Source
- Removidos textos dos labels (mantidos apenas ícones)
- Adicionados botões Email e Site
```php
// NOVO:
<input type="radio" ... id="source_email" value="email">
<label ... for="source_email"><i class="bi bi-envelope"></i></label>

<input type="radio" ... id="source_site" value="site">
<label ... for="source_site"><i class="bi bi-globe"></i></label>
```

**Linha 390**: Adicionada opção EVENTO
```php
<option value="EVENTO">Evento</option>
```

**Documentação atualizada:**
- Linha 586: Mídia Offline agora inclui "Eventos"
- Linha 629: Nota atualizada para incluir EVENTO
- Linha 724: Badge MO_EVENTO adicionado aos exemplos

### 2. `script.js`

**Linhas 103-115**: Adicionado suporte para email e site
```javascript
const mediumMap = {
  // ... outros sources
  email: ['EMAIL_MARKETING', 'EMAIL_TRANSACIONAL', 'EMAIL_NEWSLETTER'],
  site: ['SITE_BANNER', 'SITE_POPUP', 'SITE_FOOTER', 'SITE_MENU']
};
```

**Linhas 336-354**: Implementada lógica do EVENTO
```javascript
// Se for PALESTRA ou EVENTO, esconder Source, Medium e Nome do Arquivo
if (t === 'PALESTRA' || t === 'EVENTO') {
  if (sourceGroup) sourceGroup.classList.add('d-none');
  if (mediumGroup) mediumGroup.classList.add('d-none');
  if (mediumSelect) mediumSelect.required = false;
  if (moNameContainer) moNameContainer.classList.add('d-none');
  // Para PALESTRA/EVENTO, formato é apenas MO_PALESTRA ou MO_EVENTO
  hiddenContent.value = t === 'PALESTRA' ? 'MO_PALESTRA' : 'MO_EVENTO';
}
```

---

## 🎨 Interface do Usuário

### Botões de Source (Apenas Ícones)

Todos os botões de utm_source agora exibem apenas ícones para uma interface mais limpa e compacta:

```
[📷] [▶️] [💼] [🎵] [🧵] [🎧] [💬] [🍎] [📦] [🎶] [📧] [🌐]
```

### Fluxo de Uso - Mídia Offline (Evento)

1. Selecionar "Mídia Offline" em Origem/Fonte
2. Selecionar "Evento" no dropdown "Tipo de Material"
3. ✅ Campos UTM Source e UTM Medium são **automaticamente ocultados**
4. ✅ Campo "Nome do Arquivo" é **automaticamente ocultado**
5. ✅ UTM gerada: `utm_content=MO_EVENTO`

---

## 📋 Exemplos de UTMs Geradas

### Exemplo 1: Email Marketing
```
URL Base: https://lp.salesprime.com.br/oferta
utm_campaign: Sales-Prime
utm_source: email
utm_medium: EMAIL_MARKETING
utm_content: TO
utm_term: [PROMOCAO][BLACK_FRIDAY]

URL Final:
https://lp.salesprime.com.br/oferta?utm_campaign=Sales-Prime&utm_source=email&utm_medium=EMAIL_MARKETING&utm_content=TO&utm_term=[PROMOCAO][BLACK_FRIDAY]
```

### Exemplo 2: Banner do Site
```
URL Base: https://lp.salesprime.com.br/curso
utm_campaign: Dani-Martins
utm_source: site
utm_medium: SITE_BANNER
utm_content: TP
utm_term: [HOMEPAGE][TOPO]

URL Final:
https://lp.salesprime.com.br/curso?utm_campaign=Dani-Martins&utm_source=site&utm_medium=SITE_BANNER&utm_content=TP&utm_term=[HOMEPAGE][TOPO]
```

### Exemplo 3: Evento (sem Source/Medium)
```
URL Base: https://lp.salesprime.com.br/inscricao
utm_campaign: Sales-Prime
utm_content: MO_EVENTO
utm_term: [EVENTO][VENDAS_2026]

URL Final:
https://lp.salesprime.com.br/inscricao?utm_campaign=Sales-Prime&utm_content=MO_EVENTO&utm_term=[EVENTO][VENDAS_2026]
```

---

## 🔒 Segurança

### Proteções Implementadas

✅ **SQL Injection**: Prepared statements em todas as queries  
✅ **XSS**: Escape de output com `htmlspecialchars()`  
✅ **Senhas**: Hash bcrypt com salt automático  
✅ **Sessões**: Regeneração de ID de sessão  
✅ **Validação de Input**: Formato de short code e email  
✅ **Rate Limiting**: Limite de tentativas de login  

---

## 📞 Suporte

**Equipe:** Sales Prime  
**Email:** fabio.soares@salesprime.com.br  
**Localização:** `c:\xampp\htdocs\utm`

---

**Documentação atualizada em:** 06 de Fevereiro de 2026 (15:42 BRT)  
**Versão do Sistema:** 2.1 (Produção)
