<?php
/**
 * Senha master: guardada no banco como hash bcrypt (tabela settings),
 * editavel pelo painel de admin. O valor MASTER_PASSWORD do .env vale
 * so ate a primeira troca pelo sistema, como semente.
 */

function master_ensure_table(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        chave VARCHAR(64) NOT NULL PRIMARY KEY,
        valor TEXT NULL,
        atualizado_em TIMESTAMP NULL DEFAULT NULL,
        atualizado_por VARCHAR(255) NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function master_row(PDO $pdo): ?array
{
    master_ensure_table($pdo);
    $st = $pdo->prepare("SELECT valor, atualizado_em, atualizado_por FROM settings WHERE chave = 'master_password_hash'");
    $st->execute();
    $r = $st->fetch(PDO::FETCH_ASSOC);
    return $r ?: null;
}

/** true se a senha informada e a master vigente (banco; senao, .env). */
function master_password_ok(PDO $pdo, string $senha): bool
{
    if ($senha === '') return false;
    $r = master_row($pdo);
    if ($r && !empty($r['valor'])) {
        return password_verify($senha, $r['valor']);
    }
    $env = (string) env('MASTER_PASSWORD', '');
    return $env !== '' && hash_equals($env, $senha);
}

/** de onde vem a master hoje e quando foi trocada pela ultima vez. */
function master_status(PDO $pdo): array
{
    $r = master_row($pdo);
    if ($r && !empty($r['valor'])) {
        return ['origem' => 'banco', 'atualizado_em' => $r['atualizado_em'], 'atualizado_por' => $r['atualizado_por']];
    }
    return ['origem' => env('MASTER_PASSWORD', '') !== '' ? 'env' : 'nenhuma', 'atualizado_em' => null, 'atualizado_por' => null];
}

function master_set(PDO $pdo, string $nova, string $porNome): void
{
    master_ensure_table($pdo);
    $hash = password_hash($nova, PASSWORD_BCRYPT, ['cost' => 12]);
    $pdo->prepare("INSERT INTO settings (chave, valor, atualizado_em, atualizado_por)
                   VALUES ('master_password_hash', ?, NOW(), ?)
                   ON DUPLICATE KEY UPDATE valor = VALUES(valor), atualizado_em = NOW(), atualizado_por = VALUES(atualizado_por)")
        ->execute([$hash, $porNome]);
}
