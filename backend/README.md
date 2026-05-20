# UTM Engine System

![Version](https://img.shields.io/badge/version-2.6.1%2B-blue)
![Tech Stack](https://img.shields.io/badge/tech-PHP%20%2B%20JS-orange)
[![Context: DotContext](https://img.shields.io/badge/spec-DotContext-2ea44f)](#)

Sistema Completo Gerador de UTMs com rastreamento integrado (shortlinker proprietário), segurança por login limit rate, front-end premium minimalista, e dashboard corporativo alimentado usando ChartJS.

## Arquitetura do Repositório

### Frontend
- Todo render ocorre através do `index.php` (login, app principal) e `dashboard.php` (estatísticas).
- Estilo unificado dentro do `style.css` garantindo UI/UX polidos.
- Todo processamento AJAX roda através do `utm-generator.js` e do `script.js`.

### Backend (Core PHP)
- Engine monolítica enraizada em `db.php` para conexão blindada por PDO (`hgsa7692_utm`).
- As invocações de criação batem no `generate.php` via POST.
- A expansão de UTMs do link encurtado bate massivamente no `go.php`.

### API de Retorno
- Localizada na pasta `api/`. Retorna essencialmente status para listagens (ex: popular dropdown de multi-domínios via `domains.php` ou alimentar os charts via `dashboard_data.php`).

## Pastas e Ferramentas Secundárias (Warnings de Produção)
> [!WARNING]
> Existe uma série de arquivos listados na base do repositório prefixados com `debug-`, `make-` e `setup-`.
Esses arquivos são scripts avulsos desfeitos de conexão direta com os módulos da camada visual - servem especificamente de manutenção de sysadmin, testes ou correções cirúrgicas off-line durante migrations. **Não linkar em ambiente de produção**.

A pasta `migrations/` preserva as alterações críticas à infraestrutura em banco. Todo deploy com mudança de schema deve repassar estes aquivos. Outros docs (`.md` soltos em uppercase) são logs históricos em hard-file.

## Padronização de IA (DotContext)
Este app implementou uma estrutura [DotContext](https://github.com/gmasson/dotcontext) para portabilidade sem fricção de agentes de Inteligência Artificial pre-contextualizados. A engrenagem (.toml e .mds) situa-se dentro diretório interno do projeto para alavancar updates rápidos.
