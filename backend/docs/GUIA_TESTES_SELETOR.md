# 🧪 GUIA DE TESTES - SELETOR DE DOMÍNIO

**Status:** Pronto para Validação  
**Complexidade:** Básico (5-10 minutos)

---

## ✅ PRÉ-REQUISITOS

- [ ] Migração v2.6 executada: `/migrations/v2.6_migrate_to_user_id.php`
- [ ] Domínios cadastrados: Sales Prime + Prosperus Club
- [ ] Permissões atribuídas ao usuário
- [ ] Logos nos arquivos: `images/logo_sales_prime.png` + `images/logo_prosperus_club.png`

---

## 🧪 TESTE 1: Seletor Visual (UI)

### **Passo 1.1 - Renderização**
```
1. Abrir: http://localhost/utm/index.php
2. Fazer login se necessário
3. Procurar seção "SELECIONE O DOMÍNIO"
```

**Esperado:**
- ✅ Card com borda azul visível
- ✅ Título com ícone de globo
- ✅ 2 botões: "Sales Prime" + "Prosperus Club"
- ✅ Alerta informativo abaixo
- ✅ "Sales Prime" pré-selecionado

**Status:** ☐ Passou ☐ Falhou

---

### **Passo 1.2 - Dark Mode**
```
1. Ativar tema escuro (botão sun/moon no topo)
2. Verificar seletor de domínio
```

**Esperado:**
- ✅ Texto legível (não branco em branco)
- ✅ Card tem borda visível (azul claro)
- ✅ Alerta tem fundo diferente (não invisível)
- ✅ Botões têm contraste adequado
- ✅ Ícones visíveis

**Status:** ☐ Passou ☐ Falhou

---

## 🎮 TESTE 2: Interatividade (JavaScript)

### **Passo 2.1 - Clique no Seletor**
```
1. Com tema claro, página index.php aberta
2. Clicar em "Prosperus Club"
3. Observar feedback visual
```

**Esperado:**
- ✅ Botão fica com fundo amarelo (#FFC107)
- ✅ Card ganha sombra/borda amarelada
- ✅ Transição suave (não abrupta)
- ✅ Ícone de estrela ⭐ visível

**Status:** ☐ Passou ☐ Falhou

---

### **Passo 2.2 - Logo se Adapta**
```
1. Seletor ainda em "Prosperus Club"
2. Observar logo no topo do site
3. Mudar para "Sales Prime"
```

**Esperado:**
- ✅ Logo muda suavemente (fade ~0.3s)
- ✅ At Prosperus: logo_prosperus_club.png
- ✅ At Sales Prime: logo_sales_prime.png
- ✅ Sem erro 404 no console

**Ação:** F12 → Console → Procurar erros

**Status:** ☐ Passou ☐ Falhou

---

### **Passo 2.3 - Dark Mode + Logo**
```
1. Seletor em "Prosperus Club"
2. Ativar dark mode
3. Logo deve se adaptar
4. Trocar para "Sales Prime"
5. Logo deve trocar para versão dark
```

**Esperado:**
- ✅ Logo em light mode é clara
- ✅ Logo em dark mode é escura
- ✅ Transição suave em ambas
- ✅ Sem imagens quebradas

**Status:** ☐ Passou ☐ Falhou

---

### **Passo 2.4 - Persistência (localStorage)**
```
1. Selecionar "Prosperus Club"
2. Recarregar página (F5)
3. "Prosperus Club" deve continuar selecionado
4. Selecionar "Sales Prime"
5. Novo aba → volta a "Sales Prime"
```

**Esperado:**
- ✅ Seleção persiste após reload
- ✅ Nova aba tem seleção padrão
- ✅ Cada usuário tem seleção própria (localStorage)

**Console Check:**
```javascript
localStorage.getItem('selected-domain')
// Deve retornar: 'salesprime.com.br' ou 'prosperusclub.com.br'
```

**Status:** ☐ Passou ☐ Falhou

---

## 🗄️ TESTE 3: Backend (Database)

### **Passo 3.1 - Criar UTM em Sales Prime**
```
1. Página index.php, "Sales Prime" selecionado
2. Preencher formulário (URL, campaign, etc)
3. Clicar "Gerar UTM"
```

**Esperado:**
- ✅ URL gerada com sucesso
- ✅ Aparece na tabela "Histórico de URLs"
- ✅ Sem erro de banco de dados

**Status:** ☐ Passou ☐ Falhou

---

### **Passo 3.2 - Criar UTM em Prosperus Club**
```
1. Página index.php, trocar para "Prosperus Club"
2. Preencher formulário novamente
3. Clicar "Gerar UTM"
```

**Esperado:**
- ✅ URL gerada com sucesso
- ✅ Aparece na tabela
- ✅ Domínio correto no banco

**Verificação DB:**
```sql
SELECT id, shortened_url, domain, username FROM urls ORDER BY id DESC LIMIT 2;
-- Última linha deve ter: domain = 'prosperusclub.com.br'
```

**Status:** ☐ Passou ☐ Falhou

---

## 🔗 TESTE 4: Histórico & Links

### **Passo 4.1 - Link Encurtado Exibido**
```
1. Gerar UTM em Sales Prime
2. Olhar coluna "Link Encurtado" na tabela
3. Clicar em link
```

**Esperado:**
- ✅ Link mostra: `https://salesprime.com.br/utm/{code}`
- ✅ Link é clicável
- ✅ Abre o redirect corretamente

**Status:** ☐ Passou ☐ Falhou

---

### **Passo 4.2 - Link Encurtado Prosperus**
```
1. Gerar UTM em Prosperus Club
2. Olhar coluna "Link Encurtado"
3. Clicar em link
```

**Esperado:**
- ✅ Link mostra: `https://prosperusclub.com.br/utm/{code}`
- ✅ Diferente do anterior
- ✅ Cópia para clipboard funciona

**Status:** ☐ Passou ☐ Falhou

---

### **Passo 4.3 - QR Code**
```
1. Na tabela, clicar no QR Code (coluna primeira)
2. Modal abre mostrando QR grande
3. Ler QR com celular
```

**Esperado:**
- ✅ QR Code é válido (lê corretamente)
- ✅ URL contém domínio correto (prosperusclub ou salesprime)
- ✅ Botão "Baixar QRCode" funciona
- ✅ Imagem baixa como PNG

**Status:** ☐ Passou ☐ Falhou

---

### **Passo 4.4 - Cópia para Clipboard**
```
1. Ícone clipboard próximo ao link encurtado
2. Clicar no ícone
3. Colar em editor de texto
```

**Esperado:**
- ✅ URL completa foi copiada
- ✅ Inclui http**s**:// + domínio + /utm/ + code
- ✅ Sem caracteres extras

**Status:** ☐ Passou ☐ Falhou

---

## 👤 TESTE 5: Permissões

### **Passo 5.1 - Acesso Permissionado**
```
1. Usuário com permissão em Prosperus Club
2. Selecionar "Prosperus Club"
3. Gerar UTM
```

**Esperado:**
- ✅ UTM gerada normalmente
- ✅ Domínio 'prosperusclub.com.br' no banco

**Status:** ☐ Passou ☐ Falhou

---

### **Passo 5.2 - Acesso Negado (Fallback)**
```
1. Usuário SEM permissão em Prosperus Club
2. Tentar selecionar "Prosperus Club"
3. Gerar UTM
```

**Esperado:**
- ✅ UTM gerada (não quebra)
- ✅ Domínio cai para fallback 'salesprime.com.br'
- ✅ Sem mensagem de erro

**Status:** ☐ Passou ☐ Falhou

---

## 📊 TESTE 6: Admin Dashboard

### **Passo 6.1 - Aba Domínios**
```
1. Fazer login como ADMIN
2. Ir para: http://localhost/utm/dashboard.php
3. Clicar aba "Domínios"
```

**Esperado:**
- ✅ Tabela carrega com 2 domínios
- ✅ Coluna "Logo" mostra imagem corretamente
- ✅ Cor (#0D6EFD e #FFC107) visível
- ✅ Status ativo/inativo

**Status:** ☐ Passou ☐ Falhou

---

### **Passo 6.2 - Aba Stats**
```
1. Clicar aba "Stats por Domínio"
```

**Esperado:**
- ✅ Carrega estatísticas
- ✅ Mostra total URLs por domínio
- ✅ Mostra cliques por domínio
- ✅ Números correspondem ao banco

**Status:** ☐ Passou ☐ Falhou

---

### **Passo 6.3 - Aba Permissões**
```
1. Clicar aba "Permissões"
```

**Esperado:**
- ✅ Lista todos os usuários
- ✅ Mostra qual domínio cada um tem acesso
- ✅ Checkboxes create/edit/delete
- ✅ "Atribuído por" e data

**Status:** ☐ Passou ☐ Falhou

---

## 🐛 TESTE 7: Tratamento de Erros (Edge Cases)

### **Passo 7.1 - Sem Permissões Atribuídas**
```
1. Novo usuário, sem permissões no banco
2. Selecionar "Prosperus Club"
3. Gerar UTM
```

**Esperado:**
- ✅ Cai para 'salesprime.com.br' automaticamente
- ✅ Sem erro no console
- ✅ UTM gerada normalmente

**Status:** ☐ Passou ☐ Falhou

---

### **Passo 7.2 - Image Broken**
```
1. Renomear/deletar: images/logo_sales_prime.png
2. Recarregar página com tema claro
3. Selecionar "Sales Prime"
```

**Esperado:**
- ✅ Imagem mostra com ícone quebrado
- ✅ Página não quebra
- ✅ Console mostra aviso 404

**Status:** ☐ Passou ☐ Falhou

---

### **Passo 7.3 - localStorage Cheio**
```
1. Abrir DevTools → Application → Local Storage
2. Checar se 'selected-domain' existe
3. Limpar localStorage
4. Recarregar página
```

**Esperado:**
- ✅ Volta ao padrão (Sales Prime)
- ✅ sem erro

**Status:** ☐ Passou ☐ Falhou

---

## 📈 RESUMO FINAL

**Total de Testes:** 23  
**Status Global:**

```
☐ Todos passaram (✅ Pronto para produção)
☐ Alguns falharam (⚠️ Ajustes necessários)
☐ Muitos falharam (❌ Volta ao desenvolvimento)
```

**Testes Críticos (Devem passar):**
- [x] Teste 2.1 (Clique no Seletor)
- [x] Teste 3.1 (Criar UTM Sales Prime)
- [x] Teste 3.2 (Criar UTM Prosperus)
- [x] Teste 4.2 (Link Encurtado Prosperus)

**Testes Opcionais:**
- [ ] Teste 6 (Admin Dashboard)
- [ ] Teste 7 (Edge Cases)

---

## 📞 SE ALGO FALHAR

1. **Verificar no Console:**
   ```
   F12 → Console → Passar mouse sobre erros
   ```

2. **Verificar no Banco:**
   ```sql
   SELECT * FROM urls WHERE username = '{seu_usuario}' ORDER BY id DESC LIMIT 5;
   ```

3. **Limpar Cache:**
   ```
   Ctrl+Shift+Delete → Limpar dados do navegador
   ```

4. **Documentação:**
   - Ver: `/IMPLEMENTACAO_SELETOR_DOMINIO.md`

---

✨ **Boa sorte com os testes!** ✨
