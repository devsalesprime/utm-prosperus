# ☑️ CHECKLIST DE DEPLOYMENT - SELETOR DE DOMÍNIO

**Versão:** 2.6.1  
**Data de Criação:** Abril 2026  
**Responsável:** Tech Lead  
**Status Atual:** PRÉ-DEPLOYMENT

---

## 🔍 FASE 1: VALIDAÇÃO PRÉ-DEPLOYMENT (30 min)

### 1.1 Verificação de Arquivos

- [ ] **Migrate v2.6 Existe**
  ```
  Arquivo: /migrations/v2.6_migrate_to_user_id.php
  Tamanho: ~2KB
  Status: Pronto para executar
  ```

- [ ] **Logos em Lugar**
  ```
  ✓ /images/logo_sales_prime.png
  ✓ /images/logo_prosperus_club.png
  ✓ /images/logo-light.png
  ✓ /images/logo-dark.png
  ```

- [ ] **CSS Domain Selector**
  ```
  Arquivo: /assets/css/domain-selector.css
  Linhas: 205
  Dark Mode: Completo
  Animations: Funcional
  ```

- [ ] **JavaScript InitDomainSelector**
  ```
  Arquivo: /script.js
  Função: initDomainSelector() [linhas 648-747]
  localStorage: Testado
  Event Listeners: Funcional
  ```

### 1.2 Verificação de Banco de Dados

- [ ] **Tabelas Existem**
  ```sql
  ✓ urls (com coluna 'domain')
  ✓ domains (com logo_light, logo_dark)
  ✓ user_domain_permissions (com user_id FK)
  ```

  **Confirmação:**
  ```bash
  mysql> SHOW TABLES;
  mysql> DESCRIBE urls;
  mysql> DESCRIBE domains;
  mysql> DESCRIBE user_domain_permissions;
  ```

- [ ] **Dados Críticos Inicializados**
  ```sql
  -- Verificar domínios
  SELECT COUNT(*) as total FROM domains;
  -- Esperado: 2 ou mais
  
  -- Verificar if logos foram preenchidos
  SELECT id, domain_url, logo_light FROM domains;
  -- Esperado: paths like 'images/logo_*.png'
  
  -- Verificar permissões
  SELECT COUNT(*) as total FROM user_domain_permissions;
  -- Esperado: >= número de usuários
  ```

- [ ] **Foreign Keys Funcionam**
  ```sql
  -- Testing referential integrity
  SELECT udp.user_id, u.name, d.name
  FROM user_domain_permissions udp
  JOIN users u ON udp.user_id = u.id
  JOIN domains d ON udp.domain_id = d.id
  LIMIT 5;
  -- Deve retornar resultados, sem erros
  ```

### 1.3 Verificação de Permissões

- [ ] **Setup Script Executado**
  ```bash
  Arquivo: /setup-permissions.php
  Ação: Atribuir permissões para todos usuários
  Status: Completado
  
  # Verificar:
  http://localhost/utm/setup-permissions.php
  # Deve exibir: "Permissões configuradas com sucesso"
  ```

- [ ] **Admin Users Definidos**
  ```bash
  # Executar:
  http://localhost/utm/make-admin.php
  
  # Verificar resultado:
  http://localhost/utm/check-admin.php
  ```

- [ ] **Validar Admin Access**
  ```bash
  # Login como admin
  # Deve ter acesso a:
  - Dashboard > Abas (Domínios, Stats, Permissões)
  - api/domains.php?action=list
  - api/permissions.php?action=list
  ```

### 1.4 Verificação de URLs

- [ ] **QR Code URL Correto**
  ```
  Arquivo: index.php
  Linha: ~965
  Deve conter: "https://{$domain}/utm/"
  ❌ NÃO DEVE: "https://salesprime.com.br/utm/"
  ```

- [ ] **Logo Data-Attributes**
  ```html
  <!-- Sales Prime -->
  data-logo-light="images/logo_sales_prime.png"
  data-logo-dark="images/logo-dark.png"
  
  <!-- Prosperus -->
  data-logo-light="images/logo_prosperus_club.png"
  data-logo-dark="images/logo-dark.png"
  ```

---

## 🧪 FASE 2: TESTES FUNCIONAIS (1 hora)

### 2.1 UI/UX Tests

```bash
✓ TESTE 1: Seletor carrega
  [ ] Abrir index.php
  [ ] Card domain-selector aparece
  [ ] 2 radio buttons visíveis (Sales Prime, Prosperus)
  [ ] Textos legíveis

✓ TESTE 2: Tema Claro Funciona
  [ ] Sales Prime: card borda azul, logo correta
  [ ] Prosperus: card borda amarela, logo correta
  [ ] Transição suave (0.3s)

✓ TESTE 3: Tema Escuro Legível
  [ ] Background: escuro
  [ ] Texto: claro (não branco puro)
  [ ] Buttons: contraste adequado
  [ ] Alert info: visível e legível

✓ TESTE 4: Responsividade
  [ ] Desktop (1920px): lado a lado ✓
  [ ] Tablet (768px): adaptado ✓
  [ ] Mobile (375px): stack vertical ✓

✓ TESTE 5: Logo Animação
  [ ] Clicar em "Sales Prime": logo muda com fade
  [ ] Clicar em "Prosperus": logo muda com fade
  [ ] Sem logo quebrada ou lag
```

### 2.2 Funcionalidade Core

```bash
✓ TESTE 6: localStorage Persiste
  [ ] Selecionar "Prosperus Club"
  [ ] Recarregar página (F5)
  [ ] "Prosperus Club" ainda selecionado ✓
  
✓ TESTE 7: Gerar UTM - Sales Prime
  [ ] Selecionar "Sales Prime"
  [ ] Preencher formulário
  [ ] Clicar "Gerar"
  [ ] Verificar DB: urls.domain = "salesprime.com.br"
  
✓ TESTE 8: Gerar UTM - Prosperus
  [ ] Selecionar "Prosperus Club"
  [ ] Preencher formulário
  [ ] Clicar "Gerar"
  [ ] Verificar DB: urls.domain = "prosperusclub.com.br"
  
✓ TESTE 9: Histórico Renderiza Correto
  [ ] Criar 2 UTMs (um por domínio)
  [ ] Tabela histórico mostra ambos
  [ ] Links apontam para domínio correto
  
✓ TESTE 10: QR Code Contém Domínio
  [ ] Gerar UTM em "Prosperus Club"
  [ ] Clicar em "Ver QR"
  [ ] QR Code URL contém: "https://prosperusclub.com.br/utm/..."
  [ ] NÃO contém: "salesprime" ❌
```

### 2.3 Segurança & Fallback

```bash
✓ TESTE 11: Permissão Negada → Fallback
  [ ] User sem permissão para Prosperus
  [ ] Tentar selecionar Prosperus (se tiver acesso UI)
  [ ] Sistema fallback para "salesprime.com.br"
  [ ] Nenhuma erro = silencioso
  
✓ TESTE 12: Admin Can See Stats
  [ ] Login como admin
  [ ] Dashboard > Aba "Domínios"
  [ ] Ver tabela com logos e stats
  [ ] Aba "Permissões": gerenciar usuários
  
✓ TESTE 13: Validação Backend
  [ ] Enviar request direto com domain="hack.com"
  [ ] Sistema rejeita e fallback para "salesprime.com.br"
  [ ] Nenhuma injeção SQL
  [ ] Nenhuma erro de encoding
```

### 2.4 Browsers & Devices

```bash
✓ TESTE 14: Chrome (Windows)
  [ ] Abrir DevTools (F12)
  [ ] Console: Zero errors
  [ ] Network: Todos assets carregam
  [ ] Performance: <2s load time
  
✓ TESTE 15: Firefox (Windows)
  [ ] Mesmos testes do Chrome
  [ ] Compatibilidade: 100%
  
✓ TESTE 16: Safari (macOS)
  [ ] Temas: Claro + Escuro OK
  [ ] Logos: Todas visíveis
  [ ] Sem problemas CSS
  
✓ TESTE 17: Mobile (iOS)
  [ ] iPhone 12+: Layout OK
  [ ] Buttons: Clicáveis (touch-friendly)
  [ ] Sem zoom necessário
  
✓ TESTE 18: Mobile (Android)
  [ ] Samsung Galaxy: Layout OK
  [ ] Chrome Mobile: 100%
  [ ] Sem flicker ou lag
```

### 2.5 Performance

```bash
✓ TESTE 19: Load Time
  [ ] index.php: <2s
  [ ] Com 1000 históricos: <3s
  [ ] Dashboard: <2s
  
✓ TESTE 20: localStorage Size
  [ ] localStorage.getItem('selected-domain'): ~30 bytes
  [ ] Sem impacto de performance
  
✓ TESTE 21: API Response Time
  [ ] GET /api/domains.php?action=list: <200ms
  [ ] GET /api/permissions.php?action=list: <200ms
```

---

## 📋 FASE 3: VERIFICAÇÃO FINAL (30 min)

### 3.1 Documentação

- [ ] **Todos 5 Documentos Presentes**
  ```
  ✓ IMPLEMENTACAO_SELETOR_DOMINIO.md
  ✓ GUIA_TESTES_SELETOR.md
  ✓ SUMARIO_TECNICO_v2.6.1.md
  ✓ CODIGO_PRINCIPAL_v2.6.1.md
  ✓ RESUMO_EXECUTIVO_STAKEHOLDERS.md
  ```

- [ ] **README Atualizado**
  ```
  Arquivo: /README.md
  Conteúdo: Menção ao seletor de domínio
  Instruções: Setup + testes básicos
  ```

- [ ] **Changelog Documentado**
  ```
  Versão: 2.6.1
  Features: "Seletor de Domínio Multi-Tenant"
  Breaking Changes: Nenhum
  Migration Required: v2.6 (user_id FK)
  ```

### 3.2 Segurança

- [ ] **Nenhum Hard-coded Password**
  ```bash
  grep -r "password" --include="*.php" /utm/
  # Não deve retornar senhas em plain text
  ```

- [ ] **Nenhuma SQL Injection**
  ```bash
  # Todos prepared statements?
  grep -r "\$_POST\['domain'\]" --include="*.php" /utm/
  # Deve usar validateUserDomainPermission()
  ```

- [ ] **Nenhuma XSS Vulnerability**
  ```bash
  # Todos echo/print com htmlspecialchars()?
  # Verificar em: index.php (histórico table)
  ```

### 3.3 Rollback Plan

- [ ] **Backup Completo**
  ```bash
  # Database Backup
  mysqldump -u root -p db_utm > backup_v2.6_before.sql
  
  # File Backup
  tar -czf utm_v2.6_before.tar.gz /xampp/htdocs/utm/
  ```

- [ ] **Checkpoints Identificados**
  ```
  Checkpoint 1: Pré-migração
  Checkpoint 2: Pós-migração DB
  Checkpoint 3: Pré-deployment code
  Checkpoint 4: Post-deployment validation
  ```

- [ ] **Rollback Script Pronto**
  ```bash
  # Se algo der errado:
  1. Restaurar DB: mysql db_utm < backup_v2.6_before.sql
  2. Restaurar files: tar -xzf utm_v2.6_before.tar.gz
  3. Testar: http://localhost/utm/index.php (deve funcionar)
  ```

---

## 🚀 FASE 4: DEPLOYMENT (15 min)

### 4.1 Ordem Exata de Execução

```bash
STEP 1: Backup
  [ ] mysqldump db_utm > backup_$(date +%Y%m%d_%H%M%S).sql
  [ ] tar -czf utm_backup_$(date +%Y%m%d_%H%M%S).tar.gz /xampp/htdocs/utm/

STEP 2: Execute Migration (se ainda não feito)
  [ ] http://localhost/utm/migrations/v2.6_migrate_to_user_id.php
  [ ] Verificar: "Migration completed successfully"
  [ ] DB backup automático gerado

STEP 3: Code Deployment
  [ ] Git pull (ou copiar arquivos)
  [ ] Verificar: index.php, script.js, domain-selector.css
  [ ] Verificar: api/domains.php, api/permissions.php

STEP 4: Clear Cache
  [ ] Browser cache limpar ou Shift+F5
  [ ] Servidor cache (se Memcached): clear
  [ ] CDN cache (se aplicável): purge

STEP 5: Final Validation
  [ ] Abrir index.php (normal user)
  [ ] Abrir dashboard.php (admin user)
  [ ] Verificar console: Zero errors
  [ ] Verificar Db: domains e permissions OK

STEP 6: Monitor
  [ ] Verificar error_log por 30 min
  [ ] Observar metrics de performance
  [ ] Estar pronto para rollback se necessário
```

### 4.2 Horário Recomendado

```
✓ Melhor momento: sexta-feira às 16h
✓ Motivo: Fim do dia útil, tempo para monitorar

✓ Alternativa: domingo à noite (off-peak)
✓ Motivo: Menos usuário ativos, menos impacto

❌ NÃO fazer: segunda-feira de manhã (pico)
❌ NÃO fazer: meio da semana durante expediente
```

---

## ⏰ FASE 5: PÓS-DEPLOYMENT (2 horas)

### 5.1 Monitoramento Imediato (15 min)

```bash
[ ] Verificar /var/log/apache2/error.log (se Linux)
[ ] Verificar Windows Event Viewer (se Windows)
[ ] Monitorar MySQL error log
[ ] Dashboard: Nenhum erro 500
[ ] Usuários: Relatam funcionamento normal
```

### 5.2 Testes Críticos (30 min)

```bash
✓ Gerar 5 URLs em Sales Prime
✓ Gerar 5 URLs em Prosperus Club
✓ Verificar DB: domínios corretos
✓ Verificar histórico: renderiza OK
✓ Verificar tema claro: legível
✓ Verificar tema escuro: legível
✓ Testar em 2 browsers diferentes
✓ Testar em 1 mobile device
```

### 5.3 Comunicação Pós-Deploy

```bash
[ ] Notificar stakeholders: "Deploy completo"
[ ] Enviar: RESUMO_EXECUTIVO_STAKEHOLDERS.md
[ ] Agendar: Training session (1 hora)
[ ] Disponibilizar: GUIA_TESTES_SELETOR.md para QA
[ ] Estar disponível: Para troubleshooting (2 horas)
```

### 5.4 Documentação Pós-Deploy

```bash
[ ] Atualizar CHANGELOG com data do deploy
[ ] Documentar: Qualquer issue encontrado + fix
[ ] Documentar: Feedback dos usuários
[ ] Criar: Follow-up tasks se necessário
```

---

## 🆘 TROUBLESHOOTING RÁPIDO

### Problema: "Logo não carrega"
```
Solução 1: Verificar/images/
  ls -la /xampp/htdocs/utm/images/logo_*
  
Solução 2: CSS path
  Abrir DevTools > Assets > Check 404s
  
Solução 3: Cache
  Shift+F5 (hard refresh)
```

### Problema: "Theme não muda"
```
Solução 1: CSS file loaded?
  DevTools > Network > domain-selector.css (200 status?)
  
Solução 2: JavaScript console errors?
  DevTools > Console > Any red messages?
  
Solução 3: initTheme hook?
  Verificar: script.js window.initTheme definition
```

### Problema: "Dark mode ilegível"
```
Solução 1: Check CSS rules
  Grep: body.dark-theme .domain-selector-card
  
Solução 2: Color values?
  Deve ter: #E0E7FF, #B0C4FF, #D0D8E8
  
Solução 3: Clear browser cache
  Ctrl+Shift+Del > Clear cached images
```

### Problema: "Permission Denied ao gerar"
```
Solução 1: Permissions assigned?
  SELECT * FROM user_domain_permissions
  WHERE user_id = (ID aqui)
  
Solução 2: Migration v2.6 executed?
  SELECT * FROM user_domain_permissions LIMIT 1
  # Deve ter column 'user_id'
  
Solução 3: Admin flag set?
  SELECT is_admin FROM users WHERE id = (USERID)
```

---

## ✅ SIGN-OFF

### Deve Ser Assinado Antes do Deploy

```
Preparação Técnica:
  __________________     Data: _________
  Tech Lead

Validação de Testes:
  __________________     Data: _________
  QA Lead

Aprovação Stakeholder:
  __________________     Data: _________
  Product Manager

Go-Live Authorization:
  __________________     Data: _________
  Operations Lead
```

---

## 📞 ESCALATION

```
Durante Deploy:
  | Hora 0-15min  | Tech Lead (no command center)
  | Hora 15-30min | Tech Lead + QA
  | Hora 30-60min | Tech Lead + Product Manager
  | Hora 60-90min | Preparar Rollback se necessário

Se Rollback Necessário:
  [ ] Notificar Product Manager
  [ ] Restaurar DB backup
  [ ] Restaurar files backup
  [ ] Send: Root cause analysis ao time
  [ ] Schedule: Post-mortem em 24h
```

---

## 🎉 CONCLUSÃO

**Checklist Completo = Deploy Seguro**

Se todos itens acima forem checkados ✅, você pode fazer o deploy com **confiança 100%** de que:

✅ Sistema está funcionando  
✅ Nenhuma regressão foi introduzida  
✅ Segurança está OK  
✅ Performance está OK  
✅ Rollback está pronto  
✅ Equipe foi treinada  
✅ Documentação está completa  

---

**Status Atual:** 🟡 AWAITING DEPLOYMENT  
**Próxima Ação:** Preencher toda checklist antes de deploy  
**Tempo Estimado:** 3 horas (verificação + testes + deploy)  
**Go-Live (Estimado):** [DATA A DEFINIR]

---

*Checklist v2.6.1 - Production Grade*  
*Última atualização: Abril 2026*  
*Responsável: Senior Full-Stack Engineer*
