# UTM Generator — Plataforma SaaS de Tracking

## Visão Geral
O **UTM Generator** evoluiu de um simples gerador de URLs para uma **plataforma SaaS completa** de tracking, análise, colaboração em times e integrações com CRMs (HubSpot) e ferramentas externas.

Este README consolida **arquitetura, funcionamento, segurança, deploy e backlog**, servindo como documento técnico central do projeto.

---

## Stack Técnica

**Backend**
- PHP 8+
- PDO (prepared statements)
- MySQL / MariaDB

**Frontend**
- HTML + CSS (Design System próprio)
- JavaScript Vanilla (ES6+)
- Chart.js (Dashboard)

**Arquitetura**
- Singleton (Database)
- Strategy (UTM Content, Webhooks)
- Observer (Form + Events)

---

## Funcionalidades Principais

### Core
- Geração de UTMs padronizadas
- Short URLs
- Preview em tempo real
- Validação client + server

### Tracking
- Redirecionamento seguro
- Registro de cliques
- Contador agregado
- Preparado para GeoIP

### Dashboard
- Cliques por dia
- Top campanhas
- Top sources
- Visão por usuário / time

### Times / Multiusuário
- UTMs pessoais ou de time
- Papéis: owner / admin / member
- Escopo automático por login

### Integrações
- Webhooks orientados a eventos
- HubSpot (utm.created / utm.clicked)
- Webhook genérico (Zapier, Make, n8n)

### Segurança (Hardening)
- Rate limit por endpoint
- Bot detection
- Headers de segurança
- Logs de abuso
- WAF-ready (Cloudflare)

---

## Estrutura de Pastas

```
/
├── api/
│   ├── create_utm.php
│   └── dashboard.php
├── assets/
│   ├── css/
│   └── js/
├── helpers/
│   └── Auth.php
├── middleware/
│   └── Security.php
├── services/
│   ├── WebhookManager.php
│   ├── HubSpotWebhook.php
│   └── GenericWebhook.php
├── dashboard.php
├── export.php
├── redirect.php
├── config.php
└── README.md
```

---

## Eventos do Sistema

| Evento | Disparo | Uso |
|------|--------|-----|
| utm.created | Criação de UTM | HubSpot / CRM |
| utm.clicked | Clique em short URL | Analytics |

---

## Segurança

- Prepared statements em 100% das queries
- Regex e sanitização de inputs
- Rate limit por IP + endpoint
- Detecção básica de bots
- CSP e headers seguros

---

# Checklist de Deploy (Produção)

## Pré-deploy
- [ ] PHP >= 8.0
- [ ] HTTPS ativo
- [ ] Banco com charset utf8mb4
- [ ] Backup do banco
- [ ] Variáveis sensíveis fora do repo

## Banco de Dados
- [ ] Executar migrations (teams, webhooks, rate_limits)
- [ ] Criar índices
- [ ] Validar constraints

## Código
- [ ] Permissions corretas (uploads, cache)
- [ ] Remover debug / var_dump
- [ ] Validar .htaccess / rewrite

## Segurança
- [ ] Rate limit ativo
- [ ] Headers aplicados
- [ ] Bot detection testado
- [ ] Cloudflare (opcional) configurado

## Pós-deploy
- [ ] Criar UTM de teste
- [ ] Clicar short URL
- [ ] Verificar dashboard
- [ ] Exportar CSV
- [ ] Testar webhook HubSpot

---

# Backlog — Jira / Linear

## EPIC 1 — Core & UX
- Criar formulário UTM inteligente
- Preview em tempo real
- Suporte a mídia offline

## EPIC 2 — Tracking
- Short URL
- Registro de cliques
- Contador agregado

## EPIC 3 — Dashboard
- API de métricas
- Gráficos Chart.js
- Filtros por período

## EPIC 4 — Times & Permissões
- Criar times
- Gerenciar membros
- Escopo automático de dados

## EPIC 5 — Integrações
- Webhook manager
- HubSpot integration
- Webhook genérico

## EPIC 6 — Segurança
- Rate limit
- Bot detection
- Logs de abuso

## EPIC 7 — Produto
- Planos e limites
- Billing
- White-label
- API pública

---

## Conclusão
Este projeto está **SaaS-ready**, com base sólida para:
- Venda B2B
- Uso interno (marketing / growth)
- Evolução contínua

Próximos passos naturais: **billing, UI admin e métricas avançadas**.

---

🚀 UTM Generator — de ferramenta para plataforma.

