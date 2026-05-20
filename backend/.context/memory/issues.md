# Problemas Conhecidos e Workarounds

- O rateLimiter `isRateLimited` de `login.php` baseia-se em IP, o que pode aglutinar bloqueios em NATs massivos empresariais, requerendo `cleanOldAttempts($pdo)` ativado.
- Não existem testes unitários base (PHPUnit). A validação deve ser feita checando logs e via `debug-*.php` off-line.
