<?php
/**
 * Migração v2.6 - Converter user_domain_permissions para usar user_id
 * Muda de username (string) para user_id (int) para evitar problemas com acentos
 */

require __DIR__ . '/../includes/db.php';

try {
    echo "<h2>🔄 Migrando user_domain_permissions para usar user_id</h2>";
    echo "<hr>";

    // 1. Adicionar coluna user_id se não existir
    echo "<p>1️⃣ Adicionando coluna user_id...</p>";
    try {
        $pdo->exec("ALTER TABLE user_domain_permissions ADD COLUMN user_id INT(11) NULL AFTER id");
        echo "<p style='color: green;'>✅ Coluna user_id adicionada</p>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "<p style='color: orange;'>⏭️ Coluna user_id já existe</p>";
        } else {
            throw $e;
        }
    }

    // 2. Migrar dados: buscar user_id a partir do username em users
    echo "<p>2️⃣ Migrando dados (username -> user_id)...</p>";
    $stmt = $pdo->query("SELECT DISTINCT username FROM user_domain_permissions WHERE user_id IS NULL");
    $usernames = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $migrated = 0;
    foreach ($usernames as $username) {
        // Buscar o user_id a partir do name em users
        $findUser = $pdo->prepare("SELECT id FROM users WHERE name = ?");
        $findUser->execute([$username]);
        $user = $findUser->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $updateStmt = $pdo->prepare("UPDATE user_domain_permissions SET user_id = ? WHERE username = ? AND user_id IS NULL");
            $updateStmt->execute([$user['id'], $username]);
            $migrated += $updateStmt->rowCount();
            echo "<p style='color: green;'>✅ '{$username}' (ID: {$user['id']}) migrado</p>";
        } else {
            echo "<p style='color: red;'>❌ Usuário '{$username}' não encontrado na tabela users</p>";
        }
    }

    echo "<p>Total de registros migrados: <strong>$migrated</strong></p>";

    // 3. Droppar coluna username (opcional - comentado por segurança)
    // echo "<p>3️⃣ Removendo coluna username (não recomendado em produção)...</p>";
    // $pdo->exec("ALTER TABLE user_domain_permissions DROP COLUMN username");

    // 4. Criar Foreign Key para user_id
    echo "<p>3️⃣ Adicionando Foreign Key user_id -> users...</p>";
    try {
        $pdo->exec("ALTER TABLE user_domain_permissions ADD CONSTRAINT fk_user_domain_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
        echo "<p style='color: green;'>✅ Foreign Key criado</p>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'already exists') !== false) {
            echo "<p style='color: orange;'>⏭️ Foreign Key já existe</p>";
        } else {
            throw $e;
        }
    }

    echo "<hr>";
    echo "<p style='color: green; font-size: 16px;'><strong>✅ Migração concluída com sucesso!</strong></p>";
    echo "<p><a href='debug-permissions.php'>← Verificar permissões novamente</a></p>";
    echo "<p><a href='index.php'>← Voltar para gerador</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Erro na migração:</strong></p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
?>
