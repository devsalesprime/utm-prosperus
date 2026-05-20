# PARTE 3: ADMIN PANEL MULTI-DOMÍNIO
## Documentação de Implementação

**Data:** Abril de 2026  
**Versão:** 2.5 - Multi-Domínio Admin Panel  
**Status:** ✅ Implementado

---

## 📋 RESUMO DA PARTE 3

Criamos um **Admin Panel completo** para gerenciar domínios, configurações e permissões de usuários de forma centralizada e intuitiva.

### Componentes Criados:

1. **Tabelas de Banco de Dados**
   - `domains` - Configuração de domínios
   - `user_domain_permissions` - Permissões de usuário x domínio

2. **Interface Admin**
   - `admin-domains.php` - Dashboard de administração

3. **APIs de Gerenciamento**
   - `api/domains.php` - CRUD de domínios
   - `api/permissions.php` - CRUD de permissões

4. **Backend**
   - `generate.php` - Validação de permissões

---

## 🗄️ TABELAS DE BANCO DE DADOS

### Tabela: `domains`

**Propósito:** Armazenar todos os domínios disponíveis e suas configurações

```sql
CREATE TABLE `domains` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL UNIQUE,           -- Nome exibível (Sales Prime)
  `domain_url` VARCHAR(255) NOT NULL UNIQUE,     -- URL (salesprime.com.br)
  `logo_light` VARCHAR(255),                     -- Caminho logo light
  `logo_dark` VARCHAR(255),                      -- Caminho logo dark
  `brand_color` VARCHAR(7) DEFAULT '#0D6EFD',    -- Cor primária
  `is_active` TINYINT(1) DEFAULT 1,              -- Status ativo/inativo
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_active` (`is_active`),
  INDEX `idx_domain_url` (`domain_url`)
)
```

**Dados Iniciais:**
```sql
INSERT INTO `domains` VALUES
(1, 'Sales Prime', 'salesprime.com.br', 'images/logo_sales_prime.png', 'images/logo-dark.png', '#0D6EFD', 1, NOW(), NOW()),
(2, 'Prosperus Club', 'prosperusclub.com.br', 'images/logo_prosperus_light.png', 'images/logo_prosperus_dark.png', '#FFC107', 1, NOW(), NOW());
```

---

### Tabela: `user_domain_permissions`

**Propósito:** Controlar quais usuários podem usar quais domínios e com que permissões

```sql
CREATE TABLE `user_domain_permissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(255) NOT NULL,              -- Email do usuário
  `domain_id` INT NOT NULL,                      -- Referência para domains
  `can_create` TINYINT(1) DEFAULT 1,             -- Pode criar URLs
  `can_edit` TINYINT(1) DEFAULT 0,               -- Pode editar URLs
  `can_delete` TINYINT(1) DEFAULT 0,             -- Pode deletar URLs
  `assigned_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `assigned_by` VARCHAR(255),                    -- Admin que atribuiu
  UNIQUE KEY `unique_user_domain` (`username`, `domain_id`),
  FOREIGN KEY (`domain_id`) REFERENCES `domains`(`id`) ON DELETE CASCADE,
  INDEX `idx_username` (`username`),
  INDEX `idx_domain_id` (`domain_id`)
)
```

---

## 🎨 INTERFACE ADMIN (`admin-domains.php`)

### Features:

✅ **Dashboard com Estatísticas**
- Total de domínios ativos
- Total de usuários gerenciados
- Visualização em tempo real

✅ **Aba 1: Domínios**
- Tabela com todos os domínios configurados
- Status ativo/inativo
- Botões para editar e deletar
- Modal para criar novo domínio

✅ **Aba 2: Estatísticas**
- Total de URLs por domínio
- Total de cliques por domínio
- Número de usuários por domínio
- Média de cliques por URL

✅ **Aba 3: Permissões**
- Visualizar permissões de todos os usuários
- Tabela com granularidade (criar, editar, deletar)
- Buttons para editar ou remover permissões

### Modals (Popups):

1. **Nova Domínio**
   - Nome
   - URL do domínio
   - Logo Light/Dark
   - Cor da marca

2. **Editar Domínio**
   - Atualizar configurações
   - Ativar/desativar domínio

3. **Nova Permissão**
   - Email do usuário
   - Domínio a permitir
   - Checkboxes para criar/editar/deletar

---

## 🔌 APIs DE GERENCIAMENTO

### `api/domains.php`

Gerencia CRUD de domínios. Requer autenticação como admin.

**Endpoints:**

#### CREATE - Novo Domínio
```bash
POST /api/domains.php
Content-Type: application/x-www-form-urlencoded

action=create_domain
name=Novo Club
domain_url=novoclub.com.br
logo_light=images/logo_novo_light.png
logo_dark=images/logo_novo_dark.png
brand_color=#FF5733
```

**Response:**
```json
{
  "success": true,
  "message": "Domínio criado com sucesso"
}
```

#### UPDATE - Atualizar Domínio
```bash
POST /api/domains.php
action=update_domain
domain_id=1
name=Sales Prime Atualizado
domain_url=salesprime.com.br
brand_color=#0D6EFD
is_active=1
```

#### DELETE - Deletar Domínio
```bash
POST /api/domains.php
action=delete_domain
domain_id=3
```

**Validações:**
- ✅ Admin check obrigatório
- ✅ Não permite deletar se houver URLs associadas
- ✅ Validação de whitelist

---

### `api/permissions.php`

Gerencia permissões de usuários. Requer autenticação como admin.

**Endpoints:**

#### CREATE - Atribuir Permissão
```bash
POST /api/permissions.php
action=create_permission
username=usuario@salesprime.com.br
domain_id=2
can_create=1
can_edit=0
can_delete=0
```

#### UPDATE - Atualizar Permissão
```bash
POST /api/permissions.php
action=update_permission
permission_id=5
can_create=1
can_edit=1
can_delete=1
```

#### DELETE - Remover Permissão
```bash
POST /api/permissions.php
action=delete_permission
permission_id=5
```

#### GET - Listar Permissões de um Usuário
```bash
POST /api/permissions.php
action=get_user_permissions
username=usuario@salesprime.com.br
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "username": "usuario@salesprime.com.br",
      "domain_id": 1,
      "domain_name": "Sales Prime",
      "domain_url": "salesprime.com.br",
      "can_create": 1,
      "can_edit": 0,
      "can_delete": 0,
      "assigned_at": "2026-04-01 10:30:00"
    }
  ]
}
```

---

## 🔐 SEGURANÇA & VALIDAÇÃO

### Backend Validation (`generate.php`)

Nova função: `validateUserDomainPermission()`

**Lógica:**
```
1. Usuário escolhe domínio no seletor
2. POST envia domain para generate.php
3. Sistema valida contra whitelist
4. Verifica permissão na tabela user_domain_permissions
5. Se tem permissão → usa domínio solicitado
6. Se não tem → fallback para salesprime.com.br
7. Insere na coluna `domain` com valor validado
```

**Exemplo de Fluxo:**

```
Usuário: joao@salesprime.com.br
Seletor: Prosperus Club (prosperusclub.com.br)
         ↓
generate.php recebe: domain=prosperusclub.com.br
         ↓
Busca permissão:
  SELECT * FROM user_domain_permissions 
  WHERE username='joao@salesprime.com.br' 
  AND domain_url='prosperusclub.com.br' 
  AND can_create=1
         ↓
Resultado:
  ✓ Tem permissão → usa prosperusclub.com.br
  ✗ Sem permissão → usa salesprime.com.br (fallback)
         ↓
URL gravada no banco com domain correto
```

---

## 🎯 FLUXO PRÁTICO

### Cenário: Admin concede acesso a novo domínio

```
1. Admin acessa: http://localhost/utm/admin-domains.php
         ↓
2. Clica em "Atribuir Permissão" (Aba 3)
         ↓
3. Preenche:
   - Email: usuario@salesprime.com.br
   - Domínio: Prosperus Club
   - Checkboxes: ✓ Criar
         ↓
4. API recebe em api/permissions.php:
   action = create_permission
   username = usuario@salesprime.com.br
   domain_id = 2 (Prosperus Club)
   can_create = 1
         ↓
5. Insere em user_domain_permissions com UNIQUE constraint
         ↓
6. Usuário agora vê "Prosperus Club" disponível no seletor
         ↓
7. Gera UTM para prosperusclub.com.br com sucesso
```

---

## 📊 EXEMPLO DE PERMISSÕES

```
┌─────────────────────────────────────────────────────────┐
│ Tabela: user_domain_permissions (exemplo)              │
├─────────┬──────────────────────┬────┬────┬────┤
│ username│ domain               │C │E  │D  │
├────────────────────────────────────────┤
│ joao@sp │ salesprime.com.br    │✓ │✗  │✗  │
│ joao@sp │ prosperusclub.com.br │✓ │✓  │✗  │
│ maria@sp│ salesprime.com.br    │✓ │✓  │✓  │
│ maria@sp│ prosperusclub.com.br │✓ │✗  │✗  │
└────────────────────────────────────────┘

C = can_create, E = can_edit, D = can_delete

Resultado:
- João: Pode criar em ambos, editar apenas em Prosperus
- Maria: Pode criar em ambos, editar apenas em Sales Prime
```

---

## 🔧 MANUTENÇÃO & EXTENSIBILIDADE

### Adicionar Novo Domínio

1. **Via SQL:**
```sql
INSERT INTO domains (name, domain_url, logo_light, logo_dark, brand_color)
VALUES ('Novo Club', 'novoclub.com.br', 'images/logo.png', 'images/logo_dark.png', '#FF5733');
```

2. **Via Admin Panel:**
   - Acesse `admin-domains.php`
   - Clique em "Novo Domínio"
   - Preencha formulário e clique "Criar"

### Atribuir Usuário a Domínio

1. **Via API:**
```php
POST /api/permissions.php
action=create_permission
username=usuario@salesprime.com.br
domain_id=3
can_create=1
can_edit=1
can_delete=0
```

2. **Via Admin Panel:**
   - Aba "Permissões"
   - Clique em "Atribuir Permissão"
   - Selecione usuário e domínio

---

## 📈 RELATÓRIOS & ANALYTICS

Dashboard mostra:

- **Por Domínio:**
  - Total de URLs criadas
  - Total de cliques
  - Número de usuários
  - Cliques médios por URL

- **Por Usuário:**
  - Domínios permitidos
  - Datas de atribuição
  - Quem atribuiu

---

## 🚀 INTEGRAÇÃO COM FRONTEND

### Index.php atualizado

Novo botão no header para admins:
```html
<?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
    <a href="admin.php" class="btn btn-success btn-sm">
        <i class="bi bi-gear"></i> Painel Admin
    </a>
    <a href="admin-domains.php" class="btn btn-warning btn-sm">
        <i class="bi bi-globe"></i> Domínios
    </a>
<?php endif; ?>
```

---

## ✅ CHECKLIST DE IMPLEMENTAÇÃO

- [x] Criar tabela `domains`
- [x] Criar tabela `user_domain_permissions`
- [x] Inserir domínios padrão
- [x] Criar interface admin (`admin-domains.php`)
- [x] Criar API domains (`api/domains.php`)
- [x] Criar API permissions (`api/permissions.php`)
- [x] Função de validação em `generate.php`
- [x] Atualizar INSERT de URLs com `domain`
- [x] Atualizar links no frontend
- [x] Testes de permissões

---

## 🎓 RESUMO DA SOLUÇÃO

**Parte 1 (Backend):**
- Coluna `domain` na tabela URLs
- Validação multi-domínio em login.php, generate.php, go.php

**Parte 2 (Frontend):**
- Seletor visual de domínio
- Troca de logo dinâmica (SPA)
- Tabela de histórico com domínios

**Parte 3 (Admin):**
- Dashboard de gerenciamento
- Controle de permissões por usuário
- CRUD de domínios
- Relatórios por domínio

---

**Versão Final:** 2.5  
**Desenvolvido por:** Senior Full-Stack Engineer  
**Data:** Abril 2026
