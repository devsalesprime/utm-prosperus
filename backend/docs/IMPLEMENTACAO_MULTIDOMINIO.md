# REFACTOR MULTI-DOMÍNIO - UTM GENERATOR
## Documentação de Implementação (Parte 1 & 2)

**Data:** Abril de 2026  
**Versão:** 2.5 - Multi-Domínio Support  
**Status:** ✅ Implementado

---

## 📋 RESUMO DAS ALTERAÇÕES

### **PARTE 1: BANCO DE DADOS E BACKEND**

#### 1.1 - Migração do Banco de Dados
**Arquivo:** `migrations/v2.4_add_domain_column.sql`

```sql
ALTER TABLE `urls` ADD COLUMN `domain` VARCHAR(255) DEFAULT 'salesprime.com.br' AFTER `is_enabled`;
ALTER TABLE `urls` ADD INDEX `idx_domain` (`domain`);
ALTER TABLE `urls` ADD CHECK (`domain` IN ('salesprime.com.br', 'prosperusclub.com.br'));
```

**Resultado:** 
- ✅ Coluna `domain` adicionada com fallback para retrocompatibilidade
- ✅ Índice para melhorar performance de busca
- ✅ Constraint validando domínios permitidos

#### 1.2 - Login Multi-Domínio
**Arquivo:** `login.php`  
**Função alterada:** `validateEmail()`

Agora aceita e-mails dos domínios:
- `@salesprime.com.br`
- `@prosperusclub.com.br`

```php
function validateEmail($email) {
    if (!preg_match('/@(salesprime|prosperusclub)\.com\.br$/', $email)) {
        return false;
    }
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
```

#### 1.3 - Geração de UTM com Domínio
**Arquivo:** `generate.php`

**Mudanças:**
1. Captura do domínio do formulário (POST)
2. Validação com lista branca
3. Inserção do domínio na tabela `urls`

```php
$domain = trim($_POST['domain'] ?? 'salesprime.com.br');
$allowedDomains = ['salesprime.com.br', 'prosperusclub.com.br'];
if (!in_array($domain, $allowedDomains)) {
    $domain = 'salesprime.com.br'; // Fallback seguro
}

// Na query INSERT
"INSERT INTO urls (..., domain, ...) VALUES (..., ?, ...)"
```

#### 1.4 - Redirecionamento Inteligente
**Arquivo:** `go.php`

**Mudanças:**
1. Leitura do domínio do banco de dados
2. Redirect dinâmico baseado no domínio
3. Retrocompatibilidade com URLs antigas

```php
$stmt = $pdo->prepare("SELECT id, long_url, is_enabled, domain FROM urls WHERE shortened_url = ?");
// ... uso de $url['domain'] para redirect correto
header("Location: https://{$domain}/utm/" . $url['shortened_url']);
```

---

### **PARTE 2: FRONTEND - UI/UX DINÂMICA**

#### 2.1 - Seletor Visual de Domínio
**Arquivo:** `index.php`

**Novo componente:** Card de seleção com 2 opções
```html
<div class="domain-selector-card">
    <!-- Radio buttons com data-attributes para logo -->
    <input type="radio" id="domain-salesprime" value="salesprime.com.br" 
        data-logo-light="images/logo_sales_prime.png" 
        data-logo-dark="images/logo-dark.png" 
        data-brand="Sales Prime" checked>
    
    <input type="radio" id="domain-prosperus" value="prosperusclub.com.br" 
        data-logo-light="images/logo_prosperus_light.png" 
        data-logo-dark="images/logo_prosperus_dark.png" 
        data-brand="Prosperus Club">
</div>
```

**Campo Hidden:**
```html
<input type="hidden" id="domain-field" name="domain" value="salesprime.com.br">
```

#### 2.2 - Comportamento Dinâmico (SPA)
**Arquivo:** `script.js`

**Nova função:** `initDomainSelector()`

**Funcionalidades:**
- ✅ Troca de logo sem reload de página
- ✅ Persistência com localStorage
- ✅ Sincronização com field de domínio do formulário
- ✅ Animações suaves na mudança
- ✅ Integração com sistema de temas (light/dark)

**Pseudocódigo:**
```javascript
// Ao mudar domínio:
1. Atualizar campo hidden (domain-field)
2. Salvar em localStorage
3. Trocar logo com transição CSS
4. Aplicar efeitos visuais (cor border, sombra)
5. Log para debug
```

#### 2.3 - Estilos CSS
**Arquivo:** `assets/css/domain-selector.css` (NOVO)

**Incluições:**
- Card com gradiente e transições
- Estados hovering e checked
- Animações de pulse
- Responsividade mobile
- Suporte a dark mode
- Transições de logo

#### 2.4 - Tabela de Histórico Multi-Domínio
**Arquivo:** `index.php` (Seção de histórico)

**Mudanças:**
1. Query busca domínio com fallback
2. Link encurtado usa domínio do banco
3. QR Code gerado com domínio correto

```php
$domain = $row['domain'] ?? 'salesprime.com.br';
$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=1500x1500&data=" . 
    urlencode("https://{$domain}/utm/" . $row['shortened_url']);
```

---

## 🔄 FLUXO DE FUNCIONAMENTO

### Geração de Nova UTM

```
1. Usuário acessa index.php
   ↓
2. Sistema carrega seletor de domínio (salesprime como default)
   ↓
3. Usuário seleciona "Prosperus Club" 
   → JavaScript atualiza logo dinamicamente (SPA)
   → Campo hidden é preenchido
   ↓
4. Usuário preenche formulário e clica "Gerar UTM"
   ↓
5. generate.php lê domínio do POST
   → Valida contra lista branca
   → Insere na tabela com domain='prosperusclub.com.br'
   ↓
6. Usuário vê URL gerada na tabela
   → Link mostra domínio correto de prosperusclub.com.br
   → QR Code usa prosperusclub.com.br
```

### Redirecionamento Existente

```
1. Usuário clica em short link da tabela
   ↓
2. go.php lê o código da URL
   ↓
3. Busca no banco: SELECT ... WHERE shortened_url = ?
   ↓
4. Obtém domain da linha (ou fallback salesprime.com.br)
   ↓
5. É redirecionado para: https://{$domain}/utm/{code}
   ↓
6. Clique é registrado normalmente
```

---

## 🛡️ RETROCOMPATIBILIDADE

Todas as URLs existentes funcionam normalmente:

- ✅ URLs antigas recebem domain='salesprime.com.br'
- ✅ Redirecionamentos continuam funcionando
- ✅ Histórico não é afetado
- ✅ Cliques continuam sendo registrados
- ✅ Dashboard segue funcionando

---

## 📊 ESTRUTURA DE DADOS

### Tabela `urls` (Alterada)

| Coluna | Tipo | Default | Descrição |
|--------|------|---------|-----------|
| id | INT | - | PK Auto-increment |
| original_url | TEXT | - | URL original |
| shortened_url | VARCHAR(255) | - | Código curto |
| long_url | TEXT | - | URL completa com UTM |
| username | VARCHAR(255) | - | Criador |
| generation_date | DATETIME | NOW() | Data de criação |
| clicks | INT | 0 | Contagem de cliques |
| is_enabled | TINYINT | 1 | Status ativo |
| **domain** | **VARCHAR(255)** | **'salesprime.com.br'** | **NOVO: Domínio** |
| comment | TEXT | NULL | Comentários |
| qr_code_path | VARCHAR(255) | NULL | Caminho QR |

---

## 🚀 CHECKLIST DE IMPLEMENTAÇÃO

### Backend (Parte 1)
- [x] Criar migração SQL
- [x] Executar migração
- [x] Validar constraint
- [x] Atualizar login.php
- [x] Atualizar generate.php
- [x] Atualizar go.php
- [x] Testar retrocompatibilidade

### Frontend (Parte 2)
- [x] Criar seletor visual em index.php
- [x] Adicionar campo hidden para domínio
- [x] Criar arquivo CSS (domain-selector.css)
- [x] Implementar JavaScript (initDomainSelector)
- [x] Integrar com localStorage
- [x] Atualizar tabela de histórico
- [x] Testar logo dinâmica
- [x] Testar comportamento SPA
- [x] Validar dark mode
- [x] Testar responsividade mobile

---

## ✨ RECURSOS PRINCIPAIS

### 1. Multi-Domínio
- Suporte simultâneo a 2 domínios principais
- Fácil extensão para mais domínios (editar lista branca)
- Fallback automático para retrocompatibilidade

### 2. Experiência Dinâmica (SPA)
- Troca de logo sem reload de página
- Persistência de seleção com localStorage
- Animações suaves nas transições
- Integração com sistema de temas

### 3. Segurança
- Validação com lista branca (whitelist)
- Constraint no banco de dados
- Sanitização de entrada

### 4. Escalabilidade
- Estrutura pronta para adicionar mais domínios
- Índices no banco para melhor performance
- Padrão reutilizável para outras configurações

---

## 🔧 MANUTENÇÃO FUTURA

### Para adicionar novo domínio:

1. **Banco de dados:** Atualizar CHECK constraint
2. **login.php:** Atualizar regex do validateEmail()
3. **index.php:** Adicionar novo radio button no seletor
4. **script.js:** Adicionar novo radio ao listener
5. **Assets:** Adicionar logos para novo domínio

**Exemplo:**
```php
// ADD domínio "novoclub.com.br"

// CHECK constraint
ALTER TABLE `urls` ADD CHECK (`domain` IN ('salesprime.com.br', 'prosperusclub.com.br', 'novoclub.com.br'));

// Regex
'/@(salesprime|prosperusclub|novoclub)\.com\.br$/'

// HTML seletor (novo radio)
<input type="radio" id="domain-novoclub" value="novoclub.com.br" 
    data-logo-light="images/logo_novoclub_light.png" 
    data-logo-dark="images/logo_novoclub_dark.png" 
    data-brand="Novo Club">
```

---

## 📝 NOTAS IMPORTANTES

1. **Imagens de Logo:** Certifique-se de que existem os arquivos:
   - `images/logo_sales_prime.png`
   - `images/logo-dark.png`
   - `images/logo_prosperus_light.png`
   - `images/logo_prosperus_dark.png`

2. **localStorage:** O localStorage persiste a seleção de domínio do usuário. Usuários diferentes podem ver domínios diferentes.

3. **Debug:** Abra console do navegador para ver logs de mudança de domínio.

4. **Performance:** Índice `idx_domain` melhora queries de busca por domínio.

---

## 🎯 PRÓXIMOS PASSOS (Opcional)

- [ ] Criar dashboard filtrado por domínio
- [ ] Adicionar relatório de performance por domínio
- [ ] Implementar segmentação de usuários por domínio
- [ ] Criar API de multi-domínio
- [ ] Adicionar suporte a marcas customizadas

---

**Desenvolvido por:** Senior Full-Stack Engineer  
**Data:** Abril 2026  
**Versão:** 2.5
