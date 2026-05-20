# 🚀 QUICK START - REFACTOR MULTI-DOMÍNIO v2.5

**Versão:** 2.5  
**Data:** Abril de 2026  
**Status:** ✅ Pronto para Usar

---

## ✅ JÁ IMPLEMENTADO E TESTADO

Toda a **Parte 1, 2 e 3** foi completada com sucesso! ✨

```
✅ Banco de Dados                    (2 novas tabelas)
✅ Backend Multi-Domínio              (login, generate, go)
✅ Frontend SPA Dinâmico              (seletor + logo)
✅ Admin Panel Completo               (dashboard + CRUD)
✅ APIs de Gerenciamento              (domains + permissions)
✅ Segurança & Validações             (whitelist + fallback)
✅ Retrocompatibilidade 100%          (URLs antigas funcionam)
✅ Documentação                       (3 arquivos .md)
```

---

## 🎯 PRIMEIROS PASSOS

### 1️⃣ Acessar o Sistema

```
URL: http://localhost/utm/index.php
Usuário: (seu email @salesprime.com.br ou @prosperusclub.com.br)
Senha: (sua senha)
```

### 2️⃣ Ver Seletor de Domínio

Logo após fazer login, você verá:

```
┌─────────────────────────────────┐
│  🌍 Selecione o Domínio          │
│                                  │
│  [Sales Prime] [Prosperus Club] │
│                                  │
│  Todas as UTMs serão geradas    │
│  para o domínio selecionado     │
└─────────────────────────────────┘
```

### 3️⃣ Escolher Domínio

- Clique em **Sales Prime** ou **Prosperus Club**
- A logo do cabeçalho muda automaticamente ✨ (SPA)
- Sua seleção é salva automaticamente

### 4️⃣ Gerar UTM

1. Preencha o formulário normalmente
2. Clique em "Gerar UTM"
3. A UTM será criada **no domínio selecionado**

### 5️⃣ Ver Histórico

Na seção "Histórico de URLs":
- Link encurtado mostra o domínio correto
- QR Code usa o domínio correto
- Tudo funciona normalmente

---

## 👨‍💼 PARA ADMINISTRADORES

### Acessar Admin Panel

```
URL: http://localhost/utm/admin-domains.php
Requer: is_admin = 1
```

### Você Verá 3 Abas:

#### 🌍 Aba 1: Domínios
```
- Listar todos domínios
- Editar nome, URL, cor, status
- Deletar domínio (se não tiver URLs)
- Criar novo domínio
```

#### 📊 Aba 2: Estatísticas
```
- Total de URLs por domínio
- Total de cliques por domínio
- Número de usuários por domínio
- Média de cliques por URL
```

#### 🔐 Aba 3: Permissões
```
- Listar todos os usuários
- Ver quais domínios cada um pode usar
- Atribuir/remover permissões
- Definir granularidade (criar, editar, deletar)
```

### Exemplo: Atribuir Novo Acesso

```
1. Clique "Atribuir Permissão"
2. Email: usuario@salesprime.com.br
3. Domínio: Prosperus Club
4. Checkboxes: ✓ Criar     ✗ Editar    ✗ Deletar
5. Clique "Atribuir"
```

Pronto! Este usuário agora vê "Prosperus Club" no seletor.

---

## 🔐 COMO A SEGURANÇA FUNCIONA

### Fluxo de Validação

```
Usuário seleciona Prosperus Club
    ↓
generate.php recebe: domain=prosperusclub.com.br
    ↓
Sistema verifica:
  ✓ Domain está na whitelist?
  ✓ User tem permissão?
    ↓
SIM → Usa Prosperus Club
NÃO → Fallback para Sales Prime (default)
    ↓
URL é criada com domínio validado
```

### Resultado

- ✅ Usuário **sempre** consegue criar UTM
- ✅ Mas só em domínios **autorizados**
- ✅ Tenta usar domínio não autorizado → fallback
- ✅ Nunca quebra, sempre funciona

---

## 🧪 TESTAR A IMPLEMENTAÇÃO

### Teste 1: Seletor Básico ✅
```
1. Login em index.php
2. Ver seletor de domínio
3. Clicar em "Prosperus Club"
4. Logo muda? ✓ (SPA)
5. Campo hidden tem "prosperusclub.com.br"? ✓
```

### Teste 2: Gerar UTM ✅
```
1. Selecionar "Prosperus Club"
2. Preencher formulário
3. Clicar "Gerar UTM"
4. URL aparece na tabela com domínio correto? ✓
```

### Teste 3: QR Code ✅
```
1. Clicar no QR Code da UTM
2. Modal abre
3. Usar logo Prosperus Club
4. Baixar QR Code
5. QR Code redireciona para prosperusclub.com.br? ✓
```

### Teste 4: Link Encurtado ✅
```
1. Clicar no link encurtado
2. Verifica: https://prosperusclub.com.br/utm/{code}? ✓
3. Redireciona corretamente? ✓
```

### Teste 5: Admin Panel ✅
```
1. Acessar admin-domains.php
2. Ver 3 abas
3. Aumentar "status" no topo
4. Atribuir permissão novo usuário
5. Usuário consegue usar novo domínio? ✓
```

---

## 📊 DADOS PADRÃO

### Domínios Já Criados

| Nome | URL | Cor | Status |
|------|-----|-----|--------|
| Sales Prime | salesprime.com.br | #0D6EFD | Ativo |
| Prosperus Club | prosperusclub.com.br | #FFC107 | Ativo |

**Você pode adicionar mais domínios via Admin Panel!**

---

## 📁 ARQUIVOS IMPORTANTES

```
utm/
├── admin-domains.php              👈 Admin Dashboard
├── api/domains.php                👈 API de Domínios
├── api/permissions.php            👈 API de Permissões
├── generate.php                   👈 Validação de Permissões
├── index.php                      👈 Seletor de Domínio
├── assets/css/domain-selector.css 👈 Estilos
├── script.js                      👈 Lógica SPA
└── migrations/
    └── run_v2.5_migration.php     👈 Migração (já executada)
```

---

## ⚠️ SE HOUVER PROBLEMAS

### Erro: "Column not found: domain"
```bash
cd utm
php migrations/run_v2.5_migration.php
```

### Admin Panel mostra "Acesso Negado"
```sql
UPDATE users SET is_admin = 1 WHERE email = 'seu-email@salesprime.com.br';
```

### Seletor não muda logo
```
Abrir DevTools (F12)
Console tab
Ver erros de JavaScript
Verificar se domain-selector.css está carregando
```

### Link encurtado não funciona
```
Verificar se go.php foi atualizado
Testar: https://salesprime.com.br/utm/{code}
```

---

## 🎓 DOCUMENTAÇÃO DISPONÍVEL

1. **REFACTOR_MULTIDOMINIO_COMPLETO.md** ← **LEIA PRIMEIRO**
   - Resumo geral de tudo
   - Fluxos de funcionamento
   - Cases de uso

2. **IMPLEMENTACAO_MULTIDOMINIO.md**
   - Detalhes técnicos Parte 1 & 2
   - SQL exato das mudanças
   - JavaScript implementado

3. **PARTE_3_ADMIN_PANEL.md**
   - Tabelas de banco
   - APIs detalhadas
   - Fluxos de admin

4. **ARQUIVOS_MODIFICADOS.md**
   - Lista de mudanças
   - Git commit sugerida
   - Ordem de deployment

---

## 🚀 PRÓXIMOS PASSOS

### Imediato
- [x] Verificar se tudo está funcionando
- [x] Testar os 5 testes acima

### Curto Prazo (1-2 semanas)
- [ ] Atribuir permissões para todos os usuários
- [ ] Documentar em wiki interna
- [ ] Treinar time

### Médio Prazo (1-2 meses)
- [ ] Monitorar feedback dos usuários
- [ ] Considerar nova branding/domínio?
- [ ] Avaliar adição de estatísticas por domínio

### Longo Prazo (3+ meses)
- [ ] Dashboard com gráficos por domínio
- [ ] Relatórios em PDF/Excel
- [ ] Integração com CRM
- [ ] API pública

---

## 💡 DICAS

### 1️⃣ localStorage
Sua seleção de domínio é salva no navegador.
Se você selecionou "Prosperus Club", na próxima vez voltará para lá.

### 2️⃣ Permissão Automática
Se não tiver permissão em um domínio, o sistema usa o default automaticamente.
**Nenhum erro!** Sempre funciona.

### 3️⃣ Admin Pode Fazer Tudo
Como admin, você tem acesso a todos os domínios por padrão.
(Se quiser restringir, edite manualmente no banco)

### 4️⃣ Auditar Acesso
Tabela `user_domain_permissions` mostra:
- Quem foi atribuído
- Quem atribuiu
- Quando foi atribuído

---

## 📞 SUPORTE

Qualquer dúvida, verificar:
1. **REFACTOR_MULTIDOMINIO_COMPLETO.md** (geral)
2. **PARTE_3_ADMIN_PANEL.md** (admin)
3. **ARQUIVOS_MODIFICADOS.md** (técnico)

---

## ✨ RESUMO FINAL

Você agora tem um **sistema robusto e escalável** de gerenciamento Multi-Domínio!

```
✅ Suporta múltiplos domínios simultaneamente
✅ Interface SPA dinâmica (sem reload)
✅ Admin panel completo para gerenciar
✅ Validação de permissões em tempo real
✅ 100% retrocompatível
✅ Totalmente seguro
✅ Documentado
✅ Pronto para produção
```

**Aproveite! 🎉**

---

**Versão:** 2.5  
**Desenvolvido por:** Senior Full-Stack Engineer  
**Data:** Abril 2026
