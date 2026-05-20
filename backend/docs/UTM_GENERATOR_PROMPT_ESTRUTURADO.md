# UTM Generator - Prompt Estruturado + Inteligência de Design

> **Sistema de Desenvolvimento Ágil e Estruturado**  
> Para implementações rápidas, sólidas e escaláveis

---

## 🎯 CONTEXTO DO PROJETO

### Identidade do Sistema
**Nome:** UTM Generator System  
**Propósito:** Sistema de geração e gerenciamento de UTMs para Sales Prime  
**Stack:** PHP + MySQL + Bootstrap 5 + JavaScript vanilla  
**Ambiente:** XAMPP Local (c:\xampp\htdocs\utm)

### Objetivos Principais
1. **Geração automática** de URLs com parâmetros UTM padronizados
2. **Encurtamento** de URLs com short codes personalizáveis
3. **Rastreamento** de cliques e conversões
4. **Gestão de usuários** com aprovação administrativa
5. **Painel administrativo** para controle de equipe

---

## 📋 PROMPT ESTRUTURADO PARA IA

```
Você é um desenvolvedor sênior especializado em sistemas web PHP/MySQL.
Você está trabalhando no UTM Generator System para Sales Prime.

CONTEXTO DO SISTEMA:
- Sistema de geração de UTMs para tracking de campanhas de marketing
- Stack: PHP 8.x + MySQL 5.7+ + Bootstrap 5.3 + JavaScript ES6+
- Arquitetura: MVC simplificado com sessões PHP
- Segurança: Prepared statements, bcrypt, XSS protection, rate limiting

PADRÕES DE CÓDIGO OBRIGATÓRIOS:
1. PHP: PSR-12, camelCase para variáveis, PascalCase para classes
2. SQL: Prepared statements SEMPRE, snake_case para colunas
3. JavaScript: ES6+, const/let (nunca var), arrow functions
4. CSS: BEM naming quando custom, Bootstrap utilities primeiro
5. HTML: Semântico, acessível (ARIA labels), responsivo mobile-first

ESTRUTURA DE DADOS PRINCIPAL:
- utm_campaign: Canais (Sales-Prime, Dani-Martins, etc.)
- utm_source: Plataforma (ig, yt, in, email, site, etc.)
- utm_medium: Tipo de conteúdo (dinâmico por source)
- utm_content: Auto-gerado (TP, TO, MO_EVENTO, NOME_CLOSER, etc.)
- utm_term: Tags descritivas ([PROMO][TAG])

REGRAS ESPECIAIS:
- WEBINAR, PALESTRA, EVENTO: não usam utm_source nem utm_medium
- Mídia Offline: formatos MO_LIVRO_{NOME}, MO_PDF_{NOME}, MO_PALESTRA, MO_EVENTO
- Equipe Comercial: {NOME}_CLOSER, {NOME}_SDR, {NOME}_SSELL, {NOME}_CS
- Interface: Ícones apenas para sources, dropdowns dinâmicos para medium

QUANDO VOCÊ RECEBER UMA TAREFA:
1. Analise se afeta: Backend (PHP/MySQL), Frontend (HTML/CSS/JS), ou Ambos
2. Identifique dependências com arquivos existentes
3. Proponha solução seguindo os padrões acima
4. Inclua validações client-side E server-side
5. Mantenha compatibilidade com código existente
6. Documente mudanças para DOCUMENTATION.md

ENTREGA ESPERADA:
- Código limpo, comentado em português
- Validações de input completas
- Error handling robusto
- SQL injection protected
- XSS escaped outputs
- Mobile responsive
- Dark/Light mode compatible
```

---

## 🏗️ ARQUITETURA E DESIGN PATTERNS

### Estrutura de Arquivos

```
utm/
├── index.php              # Página principal (gerador UTM)
├── login.php              # Autenticação
├── logout.php             # Encerramento de sessão
├── register.php           # Cadastro de usuários
├── team_management.php    # Gestão de equipe (admin)
├── user_management.php    # Aprovação de usuários (admin)
├── my_utms.php           # UTMs do usuário
├── redirect.php          # Redirecionamento de short codes
├── script.js             # Lógica frontend principal
├── styles.css            # Estilos customizados
├── config.php            # Configurações DB
└── DOCUMENTATION.md      # Documentação técnica
```

### Design Patterns Implementados

#### 1. **Singleton Pattern** (Database Connection)
```php
class Database {
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new PDO(
                "mysql:host=localhost;dbname=utm_generator",
                "root",
                "",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }
        return self::$instance;
    }
}
```

#### 2. **Strategy Pattern** (UTM Generation)
```javascript
const utmStrategies = {
    simple: (type) => ({ content: type }),
    team: (name, role) => ({ content: `${name}_${role}` }),
    offline: (type, name) => ({ 
        content: name ? `MO_${type}_${name}` : `MO_${type}`,
        hideSourceMedium: ['PALESTRA', 'EVENTO'].includes(type)
    }),
    webinar: () => ({ 
        content: 'WEBINAR',
        hideSourceMedium: true
    })
};
```

#### 3. **Observer Pattern** (Form Updates)
```javascript
class FormObserver {
    constructor() {
        this.observers = [];
    }
    
    subscribe(fn) {
        this.observers.push(fn);
    }
    
    notify(data) {
        this.observers.forEach(fn => fn(data));
    }
}

const formState = new FormObserver();
formState.subscribe(updateMediumOptions);
formState.subscribe(updateContentField);
formState.subscribe(generatePreview);
```

---

## 🎨 DESIGN SYSTEM

### Cores (Dark/Light Mode)

```css
:root {
    /* Light Mode */
    --bs-body-bg: #f8f9fa;
    --bs-body-color: #212529;
    --primary: #0d6efd;
    --success: #198754;
    --danger: #dc3545;
    --card-bg: #ffffff;
}

[data-bs-theme="dark"] {
    /* Dark Mode */
    --bs-body-bg: #212529;
    --bs-body-color: #dee2e6;
    --card-bg: #2b3035;
}
```

### Componentes UI

#### Source Buttons (Icon Only)
```html
<div class="btn-group" role="group">
    <input type="radio" class="btn-check" name="source" id="source_ig" value="ig">
    <label class="btn btn-outline-primary" for="source_ig">
        <i class="bi bi-instagram"></i>
    </label>
    <!-- Repeat for all sources -->
</div>
```

#### Dynamic Medium Dropdown
```html
<select class="form-select" id="mediumSelect" required>
    <option value="">Selecione o Source primeiro</option>
    <!-- Options populated by JavaScript -->
</select>
```

#### UTM Preview Card
```html
<div class="card">
    <div class="card-header">
        <h5>Preview da URL</h5>
    </div>
    <div class="card-body">
        <code id="urlPreview" class="d-block p-3 bg-light rounded"></code>
    </div>
</div>
```

---

## ⚡ IMPLEMENTAÇÃO MVP - ROADMAP

### Fase 1: Core Functionality (Semana 1)
- [x] Estrutura de banco de dados
- [x] Sistema de autenticação
- [x] Geração básica de UTMs
- [x] Interface principal

### Fase 2: Features Avançadas (Semana 2)
- [x] Encurtamento de URLs
- [x] Rastreamento de cliques
- [x] Painel administrativo
- [x] Gestão de equipe

### Fase 3: Refinamento (Semana 3)
- [x] Dark mode
- [x] Validações avançadas
- [x] Export de dados
- [x] Documentação

### Fase 4: Otimização (Ongoing)
- [ ] Cache de queries
- [ ] API REST
- [ ] Dashboard analítico
- [ ] Notificações push

---

## 🔧 GUIA DE DESENVOLVIMENTO RÁPIDO

### Adicionando Nova Source

**1. Atualizar `index.php`:**
```php
<!-- Line ~460 -->
<input type="radio" class="btn-check" name="source" id="source_nova" value="nova">
<label class="btn btn-outline-primary" for="source_nova">
    <i class="bi bi-icon-name"></i>
</label>
```

**2. Atualizar `script.js`:**
```javascript
// Line ~103
const mediumMap = {
    // ... existing sources
    nova: ['NOVA_OPTION1', 'NOVA_OPTION2', 'NOVA_OPTION3']
};
```

**3. Atualizar `DOCUMENTATION.md`:**
```markdown
| `nova` | 🆕 Nova | Nova Plataforma |
```

### Adicionando Novo Tipo de Origem

**1. Atualizar `index.php`:**
```php
<!-- Line ~330 -->
<div class="form-check">
    <input class="form-check-input" type="radio" name="origem" id="origem_novo" value="NOVO">
    <label class="form-check-label" for="origem_novo">Novo Tipo</label>
</div>
```

**2. Atualizar `script.js`:**
```javascript
// Line ~200 (updateContentField function)
case 'NOVO':
    hiddenContent.value = 'TIPO_NOVO';
    // Add special logic if needed
    break;
```

### Adicionando Validação

**Client-side (JavaScript):**
```javascript
function validateShortCode(code) {
    const regex = /^[a-zA-Z0-9_-]{3,20}$/;
    if (!regex.test(code)) {
        showError('Short code deve ter 3-20 caracteres alfanuméricos');
        return false;
    }
    return true;
}
```

**Server-side (PHP):**
```php
function validateShortCode($code) {
    if (!preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $code)) {
        throw new Exception('Short code inválido');
    }
    return true;
}
```

---

## 📊 BANCO DE DADOS

### Schema Principal

```sql
-- Tabela de UTMs
CREATE TABLE IF NOT EXISTS utms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    original_url TEXT NOT NULL,
    short_code VARCHAR(50) UNIQUE NOT NULL,
    utm_campaign VARCHAR(100),
    utm_source VARCHAR(50),
    utm_medium VARCHAR(100),
    utm_content VARCHAR(200),
    utm_term TEXT,
    click_count INT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_short_code (short_code),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);

-- Tabela de Cliques
CREATE TABLE IF NOT EXISTS clicks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utm_id INT NOT NULL,
    clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    referer TEXT,
    FOREIGN KEY (utm_id) REFERENCES utms(id) ON DELETE CASCADE,
    INDEX idx_utm_id (utm_id),
    INDEX idx_clicked_at (clicked_at)
);

-- Tabela de Membros da Equipe
CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role ENUM('CLOSER', 'SDR', 'SSELL', 'CS') NOT NULL,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_role (role)
);
```

### Queries Otimizadas

```php
// Buscar UTMs com paginação
$stmt = $pdo->prepare("
    SELECT u.*, COUNT(c.id) as total_clicks
    FROM utms u
    LEFT JOIN clicks c ON u.id = c.utm_id
    WHERE u.user_id = :user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
    LIMIT :offset, :limit
");

// Estatísticas de cliques
$stmt = $pdo->prepare("
    SELECT 
        DATE(clicked_at) as date,
        COUNT(*) as clicks
    FROM clicks
    WHERE utm_id = :utm_id
        AND clicked_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(clicked_at)
    ORDER BY date DESC
");
```

---

## 🛡️ SEGURANÇA - CHECKLIST

### Input Validation
- [ ] Validar short_code (alfanumérico, 3-20 chars)
- [ ] Validar URL (filter_var FILTER_VALIDATE_URL)
- [ ] Validar email (filter_var FILTER_VALIDATE_EMAIL)
- [ ] Sanitizar todos os inputs de texto
- [ ] Limitar tamanho de utm_term (max 500 chars)

### SQL Security
- [ ] Prepared statements em TODAS as queries
- [ ] Nunca concatenar SQL
- [ ] Validar tipos de dados
- [ ] Usar LIMIT em queries de listagem

### XSS Protection
- [ ] htmlspecialchars() em todos os outputs
- [ ] ENT_QUOTES flag sempre
- [ ] UTF-8 encoding consistente
- [ ] CSP headers quando possível

### Authentication
- [ ] Bcrypt para senhas (custo 12+)
- [ ] Regenerar session_id após login
- [ ] Timeout de sessão (30 min)
- [ ] Rate limiting em login (5 tentativas/15min)

### CSRF Protection
- [ ] Token CSRF em todos os forms
- [ ] Validar token no servidor
- [ ] Regenerar token após uso

---

## 🚀 PERFORMANCE

### Frontend Optimization

```javascript
// Debounce para preview de URL
const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

const updatePreview = debounce(() => {
    // Update URL preview
}, 300);
```

### Backend Optimization

```php
// Cache de membros da equipe
session_start();
if (!isset($_SESSION['team_members_cache']) || 
    time() - $_SESSION['team_members_timestamp'] > 3600) {
    $_SESSION['team_members_cache'] = fetchTeamMembers();
    $_SESSION['team_members_timestamp'] = time();
}
$teamMembers = $_SESSION['team_members_cache'];
```

### Database Indexing

```sql
-- Índices para queries frequentes
CREATE INDEX idx_utms_user_active ON utms(user_id, is_active);
CREATE INDEX idx_clicks_utm_date ON clicks(utm_id, clicked_at);
CREATE INDEX idx_team_role_active ON team_members(role, is_active);
```

---

## 📱 RESPONSIVIDADE

### Breakpoints Bootstrap 5

```scss
// Extra small devices (portrait phones, less than 576px)
// Default behavior

// Small devices (landscape phones, 576px and up)
@media (min-width: 576px) { }

// Medium devices (tablets, 768px and up)
@media (min-width: 768px) { }

// Large devices (desktops, 992px and up)
@media (min-width: 992px) { }

// Extra large devices (large desktops, 1200px and up)
@media (min-width: 1200px) { }
```

### Mobile-First Examples

```html
<!-- Stack on mobile, row on desktop -->
<div class="row">
    <div class="col-12 col-md-6">Left Column</div>
    <div class="col-12 col-md-6">Right Column</div>
</div>

<!-- Hide on mobile, show on desktop -->
<div class="d-none d-md-block">Desktop Only</div>

<!-- Full width on mobile, auto on desktop -->
<div class="w-100 w-md-auto">Responsive Width</div>
```

---

## 🧪 TESTING

### JavaScript Unit Tests (Exemplo)

```javascript
// test-utm-generation.js
describe('UTM Generator', () => {
    test('generates correct URL for simple type', () => {
        const result = generateUTM({
            campaign: 'Sales-Prime',
            source: 'ig',
            medium: 'INSTAGRAM_FEED',
            type: 'TO'
        });
        expect(result).toContain('utm_content=TO');
    });
    
    test('hides source/medium for PALESTRA', () => {
        const result = generateUTM({
            campaign: 'Sales-Prime',
            type: 'PALESTRA'
        });
        expect(result).not.toContain('utm_source');
        expect(result).toContain('utm_content=MO_PALESTRA');
    });
});
```

### PHP Integration Tests (Exemplo)

```php
// test-utm-creation.php
class UTMTest extends TestCase {
    public function testCreateUTM() {
        $utm = createUTM([
            'user_id' => 1,
            'original_url' => 'https://example.com',
            'short_code' => 'test123',
            'utm_campaign' => 'Sales-Prime'
        ]);
        
        $this->assertNotNull($utm['id']);
        $this->assertEquals('test123', $utm['short_code']);
    }
    
    public function testDuplicateShortCode() {
        $this->expectException(PDOException::class);
        createUTM(['short_code' => 'test123']); // Duplicate
    }
}
```

---

## 📖 CONVENÇÕES DE CÓDIGO

### PHP

```php
// ✅ BOM
function createUtm(array $data): array {
    $stmt = $pdo->prepare("INSERT INTO utms (user_id, short_code) VALUES (?, ?)");
    $stmt->execute([$data['user_id'], $data['short_code']]);
    return ['id' => $pdo->lastInsertId()];
}

// ❌ RUIM
function createUtm($data) {
    $query = "INSERT INTO utms (user_id, short_code) VALUES ('{$data['user_id']}', '{$data['short_code']}')";
    mysql_query($query); // SQL injection risk!
}
```

### JavaScript

```javascript
// ✅ BOM
const generatePreview = () => {
    const params = new URLSearchParams();
    if (campaign) params.append('utm_campaign', campaign);
    return `${baseUrl}?${params.toString()}`;
};

// ❌ RUIM
function generatePreview() {
    var url = baseUrl + "?utm_campaign=" + campaign; // var, concatenation
    return url;
}
```

### SQL

```sql
-- ✅ BOM
SELECT u.*, COUNT(c.id) AS click_count
FROM utms u
LEFT JOIN clicks c ON u.id = c.utm_id
WHERE u.user_id = ?
GROUP BY u.id;

-- ❌ RUIM
SELECT * FROM utms WHERE user_id = $user_id; -- No prepared statement
```

---

## 🎓 EXEMPLOS PRÁTICOS

### Exemplo 1: Adicionar Nova Funcionalidade

**Tarefa:** Adicionar export CSV de UTMs

**1. Backend (PHP):**
```php
// export.php
<?php
require_once 'config.php';
session_start();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="utms_export.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Short Code', 'Campaign', 'URL', 'Clicks', 'Created']);

$stmt = $pdo->prepare("
    SELECT u.short_code, u.utm_campaign, u.original_url, 
           COUNT(c.id) as clicks, u.created_at
    FROM utms u
    LEFT JOIN clicks c ON u.id = c.utm_id
    WHERE u.user_id = ?
    GROUP BY u.id
");
$stmt->execute([$_SESSION['user_id']]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}
fclose($output);
```

**2. Frontend (HTML/JS):**
```html
<button class="btn btn-success" onclick="exportCSV()">
    <i class="bi bi-download"></i> Export CSV
</button>

<script>
function exportCSV() {
    window.location.href = 'export.php';
}
</script>
```

### Exemplo 2: Adicionar Validação Customizada

**Client-side:**
```javascript
function validateCustomTerm(term) {
    // Tags devem estar entre colchetes
    const tagPattern = /^\[[\w\s]+\](\[[\w\s]+\])*$/;
    
    if (term && !tagPattern.test(term)) {
        showError('utm_term deve conter tags no formato [TAG1][TAG2]');
        return false;
    }
    return true;
}

// Aplicar validação
document.getElementById('utmTerm').addEventListener('blur', (e) => {
    validateCustomTerm(e.target.value);
});
```

**Server-side:**
```php
function validateUtmTerm($term) {
    if (empty($term)) return true;
    
    if (!preg_match('/^\[[\w\s]+\](\[[\w\s]+\])*$/', $term)) {
        throw new Exception('utm_term deve conter tags no formato [TAG1][TAG2]');
    }
    
    if (strlen($term) > 500) {
        throw new Exception('utm_term muito longo (máx 500 caracteres)');
    }
    
    return true;
}
```

---

## 🔄 WORKFLOW DE DESENVOLVIMENTO

### 1. Recebimento de Tarefa
```
Input: "Adicionar suporte para utm_source=telegram"
```

### 2. Análise de Impacto
```
Arquivos afetados:
- index.php (adicionar botão)
- script.js (adicionar medium options)
- DOCUMENTATION.md (atualizar tabela)
```

### 3. Implementação
```php
// index.php - Line ~465
<input type="radio" class="btn-check" name="source" id="source_tg" value="tg">
<label class="btn btn-outline-primary" for="source_tg">
    <i class="bi bi-telegram"></i>
</label>
```

```javascript
// script.js - Line ~103
const mediumMap = {
    // ... existing
    tg: ['TELEGRAM_GROUP', 'TELEGRAM_CHANNEL', 'TELEGRAM_BOT', 'TELEGRAM_BIO']
};
```

### 4. Documentação
```markdown
| `tg` | 📱 Telegram | Telegram Messenger |
```

### 5. Teste
```
1. Selecionar Telegram como source
2. Verificar opções de medium
3. Gerar UTM e validar formato
4. Testar dark/light mode
5. Testar responsividade mobile
```

---

## 📞 SUPORTE E MANUTENÇÃO

### Logs e Debug

```php
// Habilitar logs em desenvolvimento
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_log("UTM criado: " . json_encode($utmData));
```

```javascript
// Debug mode no JavaScript
const DEBUG = true;

function log(...args) {
    if (DEBUG) console.log('[UTM Generator]', ...args);
}

log('Form submitted', formData);
```

### Troubleshooting Comum

**Problema:** UTMs não aparecem na lista
```sql
-- Verificar se existem registros
SELECT COUNT(*) FROM utms WHERE user_id = ?;

-- Verificar permissões
SELECT * FROM users WHERE id = ?;
```

**Problema:** Short code duplicado
```php
// Adicionar sufixo incremental
function generateUniqueShortCode($baseCode) {
    $code = $baseCode;
    $counter = 1;
    
    while (shortCodeExists($code)) {
        $code = $baseCode . $counter;
        $counter++;
    }
    
    return $code;
}
```

---

## 🎯 PRÓXIMOS PASSOS

### Features Planejadas

1. **API REST**
   - Endpoints para CRUD de UTMs
   - Autenticação via token JWT
   - Rate limiting por API key

2. **Dashboard Analítico**
   - Gráficos de cliques ao longo do tempo
   - Top UTMs por performance
   - Comparação entre campanhas

3. **Integração com Google Analytics**
   - Import automático de dados GA4
   - Sincronização de eventos
   - Relatórios consolidados

4. **Notificações**
   - Email quando UTM atingir X cliques
   - Alertas de UTMs inativas
   - Relatórios semanais automáticos

5. **Bulk Actions**
   - Import CSV de múltiplas UTMs
   - Ativar/desativar em lote
   - Atualização em massa

---

## ✅ CHECKLIST DE QUALIDADE

### Antes de Cada Deploy

- [ ] Código revisado e comentado
- [ ] Validações client + server implementadas
- [ ] SQL injection protected
- [ ] XSS escaped
- [ ] Responsivo testado (mobile/tablet/desktop)
- [ ] Dark mode funcional
- [ ] Error handling implementado
- [ ] DOCUMENTATION.md atualizado
- [ ] Backup de banco de dados
- [ ] Testes manuais realizados

### Code Review Questions

1. Este código está vulnerável a SQL injection?
2. Todos os outputs estão escapados contra XSS?
3. A validação está tanto no client quanto no server?
4. O código é mobile-friendly?
5. O código funciona em dark mode?
6. Há error handling adequado?
7. A documentação está atualizada?

---

## 📚 RECURSOS E REFERÊNCIAS

### Documentação Oficial
- [Bootstrap 5.3](https://getbootstrap.com/docs/5.3/)
- [PHP 8 Manual](https://www.php.net/manual/en/)
- [MySQL 5.7](https://dev.mysql.com/doc/refman/5.7/en/)
- [MDN Web Docs](https://developer.mozilla.org/)

### Ferramentas
- [Bootstrap Icons](https://icons.getbootstrap.com/)
- [PHP CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)
- [ESLint](https://eslint.org/)
- [XAMPP](https://www.apachefriends.org/)

### Segurança
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)

---

**Documento criado em:** 06 de Fevereiro de 2026  
**Versão:** 1.0  
**Autor:** Sales Prime Development Team  
**Contato:** fabio.soares@salesprime.com.br
