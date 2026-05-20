# 🚀 UTM Generator - Prompt Estruturado + Design Intelligence

> **Sistema de Geração de UTMs | Sales Prime**  
> Framework de desenvolvimento rápido para melhorias e novas features

---

## 📋 Template de Prompt Estruturado

### 🎯 CONTEXTO DO PROJETO

```markdown
# SISTEMA UTM GENERATOR - SALES PRIME

## Stack Tecnológica
- **Backend:** PHP 7.4+ (Procedural)
- **Frontend:** HTML5, CSS3 (Bootstrap 5.3), JavaScript (Vanilla)
- **Banco de Dados:** MySQL 8.0
- **Servidor:** Apache (XAMPP)
- **Localização:** c:\xampp\htdocs\utm

## Arquitetura Atual
├── index.php              # Página principal do gerador
├── script.js              # Lógica de interface e validações
├── style.css              # Estilos customizados
├── db.php                 # Configuração do banco
├── login.php              # Sistema de autenticação
├── admin.php              # Painel administrativo
├── generate.php           # Geração de UTMs
├── go.php                 # Sistema de redirecionamento
├── delete.php             # API de exclusão
└── disable.php            # API de status

## Design System Atual
- Framework: Bootstrap 5.3
- Tema: Claro/Escuro (toggle manual)
- Cores Primárias: 
  - Primary: #0d6efd (Bootstrap Blue)
  - Success: #198754
  - Danger: #dc3545
  - Warning: #ffc107
- Ícones: Bootstrap Icons 1.11
- Tipografia: System fonts (San Francisco, Segoe UI, Roboto)

## Versão Atual: 2.1
- ✅ Canais sem Email-Marketing e Newsletter
- ✅ UTM Source com Email e Site (apenas ícones)
- ✅ Mídia Offline com opção EVENTO
- ✅ PALESTRA e EVENTO ocultam Source/Medium
```

---

## 🎨 DESIGN SYSTEM - SALES PRIME

### Paleta de Cores Proposta

```css
/* Cores Principais */
:root {
  /* Brand Colors */
  --sp-primary: #1E40AF;        /* Azul profissional */
  --sp-primary-dark: #1E3A8A;   /* Azul escuro */
  --sp-primary-light: #3B82F6;  /* Azul claro */
  
  /* Accent Colors */
  --sp-accent: #F59E0B;         /* Laranja/Dourado */
  --sp-accent-light: #FCD34D;   /* Amarelo suave */
  
  /* Status Colors */
  --sp-success: #10B981;        /* Verde sucesso */
  --sp-danger: #EF4444;         /* Vermelho erro */
  --sp-warning: #F59E0B;        /* Laranja alerta */
  --sp-info: #3B82F6;           /* Azul informação */
  
  /* Neutral Colors - Light Mode */
  --sp-bg-primary: #FFFFFF;
  --sp-bg-secondary: #F9FAFB;
  --sp-bg-tertiary: #F3F4F6;
  --sp-text-primary: #111827;
  --sp-text-secondary: #6B7280;
  --sp-text-tertiary: #9CA3AF;
  --sp-border: #E5E7EB;
  
  /* Shadows */
  --sp-shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --sp-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  --sp-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  --sp-shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

/* Dark Mode */
[data-theme="dark"] {
  --sp-bg-primary: #111827;
  --sp-bg-secondary: #1F2937;
  --sp-bg-tertiary: #374151;
  --sp-text-primary: #F9FAFB;
  --sp-text-secondary: #D1D5DB;
  --sp-text-tertiary: #9CA3AF;
  --sp-border: #374151;
}
```

---

## 🚀 ROADMAP DE MELHORIAS

### Fase 1: Interface Premium (2 dias)
- [ ] Redesign dos botões de Source (cards agrupados)
- [ ] UTM Preview Card com badges coloridos
- [ ] Copy button com feedback animado
- [ ] Toast notification system
- [ ] Syntax highlighting nos parâmetros
- [ ] Responsividade mobile testada

### Fase 2: Dashboard Analytics (2 dias)
- [ ] Criar tabela utm_analytics
- [ ] Página dashboard.php
- [ ] Cards de estatísticas
- [ ] Gráfico de cliques por source (Chart.js)
- [ ] Gráfico de cliques ao longo do tempo
- [ ] Top 5 UTMs mais clicadas

### Fase 3: Validações e UX (1 dia)
- [ ] Validação em tempo real de URL
- [ ] Validação em tempo real de short code
- [ ] Verificação de disponibilidade (AJAX)
- [ ] Feedback visual com is-valid/is-invalid
- [ ] Mensagens de erro claras
- [ ] Debounce nas verificações

### Fase 4: Polimento (1 dia)
- [ ] Revisar acessibilidade (ARIA labels)
- [ ] Testar em múltiplos navegadores
- [ ] Otimizar queries SQL
- [ ] Adicionar loading states
- [ ] Documentar alterações
- [ ] Backup antes do deploy

---

## 📊 Melhorias Futuras (Backlog)

### Curto Prazo
- [ ] Exportar relatórios em CSV/PDF
- [ ] Filtros avançados no dashboard
- [ ] Notificações por email (metas atingidas)
- [ ] QR Code para UTMs

### Médio Prazo
- [ ] API REST para integrações
- [ ] Webhooks para eventos
- [ ] Integração com Google Analytics
- [ ] A/B testing de UTMs

### Longo Prazo
- [ ] Machine Learning para sugestões
- [ ] Previsão de performance
- [ ] Detecção de anomalias
- [ ] Dashboard em tempo real (WebSockets)

---

## 💡 EXEMPLOS DE PROMPTS PARA IA

### Exemplo 1: Melhorar Componente Específico

```markdown
Preciso melhorar a seleção de UTM Source no sistema UTM Generator.

CONTEXTO:
- Sistema atual: c:\xampp\htdocs\utm
- Arquivo: index.php (linhas 408-467)
- Tecnologia: Bootstrap 5.3 + Vanilla JS
- Estado atual: Radio buttons com apenas ícones

REQUISITOS:
1. Transformar em cards interativos agrupados por categoria
2. Categorias: Redes Sociais, Podcasts, Comunicação, Website
3. Cada card: ícone 40x40px, nome, checkmark animado, hover scale 1.05
4. Manter compatibilidade com script.js (mediumMap)

OUTPUT ESPERADO:
- HTML atualizado
- CSS para cards
- JavaScript mínimo necessário
```

### Exemplo 2: Adicionar Nova Feature

```markdown
Adicionar dashboard de analytics ao UTM Generator.

CONTEXTO:
- Sistema: c:\xampp\htdocs\utm
- Banco: MySQL (hgsa7692_utm)
- Autenticação: PHP Sessions

REQUISITOS:
1. Nova tabela utm_analytics (clicks tracking)
2. Página dashboard.php com:
   - Cards de estatísticas (total UTMs, cliques 30d, CTR, top UTM)
   - Gráfico de cliques por source (Chart.js)
   - Gráfico temporal de cliques
3. Design consistente com Bootstrap 5.3

OUTPUT ESPERADO:
- SQL para criar tabela
- PHP completo do dashboard
- CSS para cards de stats
- JavaScript para gráficos
```

---

## 🎯 COMPONENTES PRONTOS PARA USO

### Toast Notification System

```javascript
// toast-system.js - Sistema de notificações
class ToastNotification {
  constructor() {
    this.container = this.createContainer();
  }

  createContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container';
    document.body.appendChild(container);
    return container;
  }

  show(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icons = {
      success: 'bi-check-circle-fill',
      error: 'bi-x-circle-fill',
      warning: 'bi-exclamation-triangle-fill',
      info: 'bi-info-circle-fill'
    };
    
    toast.innerHTML = `
      <div class="toast-content">
        <i class="bi ${icons[type]}"></i>
        <span class="toast-message">${message}</span>
      </div>
      <button class="toast-close">
        <i class="bi bi-x"></i>
      </button>
    `;
    
    this.container.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    
    toast.querySelector('.toast-close').addEventListener('click', () => {
      this.hide(toast);
    });
    
    if (duration > 0) {
      setTimeout(() => this.hide(toast), duration);
    }
  }

  hide(toast) {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 300);
  }
}

// Uso: showToast('Mensagem', 'success', 3000);
const toast = new ToastNotification();
function showToast(message, type = 'info', duration = 3000) {
  toast.show(message, type, duration);
}
```

### Validação em Tempo Real

```javascript
// validation.js - Validações de formulário

function validateUrl(url) {
  const urlPattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
  return urlPattern.test(url);
}

function validateShortCode(code) {
  const codePattern = /^[a-zA-Z0-9-]{3,20}$/;
  return codePattern.test(code);
}

// Aplicar validação visual
function applyValidation(input, isValid) {
  input.classList.remove('is-valid', 'is-invalid');
  if (input.value.trim().length > 0) {
    input.classList.add(isValid ? 'is-valid' : 'is-invalid');
  }
}

// Verificar disponibilidade de short code
async function checkShortCodeAvailability(code) {
  const response = await fetch('check_shortcode.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ short_code: code })
  });
  return await response.json();
}
```

---

## 📚 REFERÊNCIAS TÉCNICAS

### Estrutura de Arquivos Atual (v2.1)

```
utm/
├── index.php              # Formulário de geração
├── script.js              # Lógica frontend (659 linhas)
├── style.css              # Estilos (1.218 linhas)
├── db.php                 # Conexão PDO
├── login.php              # Auth + registro
├── admin.php              # Gestão usuários/equipe
├── generate.php           # Backend geração
├── go.php                 # Redirecionamento
├── delete.php             # API exclusão
├── disable.php            # API status
├── DOCUMENTATION.md       # Docs completa
└── assets/
    └── css/
        ├── base.css
        ├── componentes.css
        ├── responsivo.css
        ├── tema.css
        └── utm-btn.css
```

### Parâmetros UTM (v2.1)

**utm_campaign (Canais):**
- Sales-Prime, Dani-Martins, Prosperus, Lumiere, Prime, PodVender, Joel-Jota, PodCast

**utm_source (12 opções):**
- ig, yt, in, tktk, thrd, spot, wpp, appl, amz, dzr, **email**, **site**

**utm_medium (dinâmico por source):**
- Email: EMAIL_MARKETING, EMAIL_TRANSACIONAL, EMAIL_NEWSLETTER
- Site: SITE_BANNER, SITE_POPUP, SITE_FOOTER, SITE_MENU
- (+ opções específicas de cada rede social)

**utm_content (tipos):**
- Simples: TP, TO, SEM, TJJ, TV, APP, WEBINAR
- Equipe: {NOME}_CLOSER, {NOME}_SDR, {NOME}_SSELL, {NOME}_CS
- Offline: MO_LIVRO_{NOME}, MO_PDF_{NOME}, MO_PALESTRA, **MO_EVENTO**

---

## 🔧 COMANDOS ÚTEIS

### Backup do Banco
```bash
mysqldump -u root -p hgsa7692_utm > backup_utm_$(date +%Y%m%d).sql
```

### Restaurar Banco
```bash
mysql -u root -p hgsa7692_utm < backup_utm_20260206.sql
```

### Verificar Logs Apache
```bash
tail -f C:\xampp\apache\logs\error.log
```

### Limpar Cache PHP (se necessário)
```bash
php -r "opcache_reset();"
```

---

<p align="center">
  <strong>UTM Generator v2.1</strong> • Desenvolvido para Sales Prime<br>
  Última atualização: 06/02/2026
</p>
