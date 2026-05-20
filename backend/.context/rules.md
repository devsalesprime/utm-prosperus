# UTM Generator: Regras de Arquitetura (DotContext)

- **Frontend:** Estritamente CSS customizado. Todos os assets estão em `assets/css/` e `assets/js/`.
- **Backend:**
  1. Qualquer operação de banco *deve imperativamente* passar pela classe `Database` (`includes/db.php`).
  2. É obrigatório o uso de *Prepared Statements*.
  3. Módulo Multi-Domínio deve validar ACL do `user_id` em tempo de execução nas chamadas a `generate.php`.
- **Paths de require:**
  - Da raiz: `require 'includes/db.php';` ou `require 'includes/config.php';`
  - De `api/`: `require __DIR__ . '/../includes/db.php';`
  - De `migrations/`: `require __DIR__ . '/../includes/db.php';`
- **Segurança Básica:**
  - Arquivos `debug-*` e `setup-*` estão em `tests/` e não devem ser acessíveis publicamente.
  - Documentação `.md` está consolidada em `docs/`.
