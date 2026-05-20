# UTM Prosperus Club

Repositório principal do encurtador de URLs e gerador de UTMs para o **Prosperus Club**. O projeto consiste em um Frontend robusto utilizando Next.js e um Backend simples em PHP para manipulação do redirecionamento.

## 🛠 Arquitetura do Sistema

O sistema roda em uma VPS e convive com o ecossistema principal do WordPress (Sales Prime/Prosperus). 
- **Frontend**: Next.js na porta `3005`, gerenciado pelo `PM2` sob o nome `utm-next`.
- **Backend**: Scripts em PHP puro (via `php8.3-fpm`), acessando a base de dados `utm_prosperus` para criação/gerenciamento de URLs e captura de clicks.
- **Integração Nginx / WordPress**: A raiz do domínio `https://prosperusclub.com.br` serve tanto o site principal do WordPress (via proxy para porta `8080`) quanto o encurtador. O Nginx intercepta URLs com 6 caracteres e encaminha para o `redirect.php`. Se o código for válido, o PHP redireciona. Se for inválido, o PHP usa `X-Accel-Redirect` para devolver a requisição de forma transparente para o WordPress.

---

## 🚀 Como executar o Frontend localmente

1. Navegue até o diretório principal:
```bash
cd utm-prosperus
```
2. Instale as dependências:
```bash
npm install
```
3. Crie um arquivo `.env.local` na raiz com o endereço da API:
```env
NEXT_PUBLIC_API_URL=https://utm.prosperusclub.com.br/api
NEXT_PUBLIC_APP_URL=https://prosperusclub.com.br
```
4. Rode o ambiente de desenvolvimento:
```bash
npm run dev
```

---

## 🔧 Deploy em Produção (VPS)

As atualizações de Frontend são feitas da seguinte forma no servidor:
```bash
cd /var/www/utm.prosperusclub.com.br/frontend
# 1. Puxe as atualizações do git (git pull)
# 2. Recompile o Next.js
npm run build
# 3. Reinicie o PM2
pm2 restart utm-next
```

*(Nota: O Next.js foi configurado para rodar na porta 3005 para evitar conflitos com outros projetos já alocados na porta 3000 do servidor).*

---

## 🌐 Configuração Crítica do Nginx

A mágica do redirecionamento transparente funciona graças a esta estrutura no Nginx (no arquivo `prosperusclub.com.br.conf`):

```nginx
    # Intercepta encurtadores de links
    location ~ ^/([a-zA-Z0-9_-]+)$ {
        # Reescreve a URI localmente
        rewrite ^/([a-zA-Z0-9_-]+)$ /redirect.php?code=$1 break;
        
        # Chama o PHP-FPM
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME /var/www/utm.prosperusclub.com.br/backend/redirect.php;
        fastcgi_param DOCUMENT_ROOT /var/www/utm.prosperusclub.com.br/backend;
        include fastcgi_params;
    }

    # Bloco interno para o X-Accel-Redirect (fallback para WordPress)
    location ~ ^/@wordpress_internal/(.*)$ {
        internal;
        proxy_pass http://127.0.0.1:8080/$1$is_args$args;
        proxy_set_header Host $host;
        # ... outros headers de proxy
    }
```

---

## 💡 Histórico Recente de Correções (Changelog)

- **Port Conflict**: Migração do `utm-next` da porta 3000 para a 3005 no ecossistema PM2.
- **X-Accel-Redirect**: Resolvido o conflito com o plugin de SEO (Rank Math) do WordPress que forçava redirecionamentos inadequados.
- **Bug do Auto-Complete (Frontend)**: Adicionado campo `dummy` invisível para impedir que navegadores injetassem senhas e e-mails automaticamente na barra de busca de UTMs.
- **Bug de Crash do QRCode**: Ajuste na lib `qrcode.react` (renomeada `imageSettings` dinamicamente) e modais repadronizados para a classe `.ag-modal` a fim de evitar conflito com os estilos do Bootstrap (`display: none`).
- **Exclusão de UTM**: A variável de ambiente que guarda a senha mestre no `backend/api/delete_utm.php` foi corrigida de `DELETE_PASSWORD` para `MASTER_PASSWORD` (`utm_master2026`).

---

## 📂 Estrutura de Pastas

- `/src/` - Componentes Next.js, app router, páginas de analytics e geração.
- `/public/css/` - Estilizações antigas e unificadas, contendo o Design System do Antigravity (Sales Prime / Prosperus Club).
- `/backend/` - Scripts PHP para manipulação via PDO.
- `/backend/api/` - Endpoints REST utilizados pelo Frontend.
