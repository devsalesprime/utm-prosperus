<?php
/**
 * Script para Tornar o Usuário Admin
 * Atualiza is_admin = 1 no banco
 */

session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    die("❌ Usuário não autenticado");
}

$username = $_SESSION['username'];

try {
    echo "<h2>✅ Promovendo para Admin</h2>";
    echo "<hr>";
    echo "<p><strong>Usuário:</strong> $username</p>";

    // Atualizar is_admin para 1
    $stmt = $pdo->prepare("UPDATE users SET is_admin = 1 WHERE email = ?");
    $result = $stmt->execute([$username]);

    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'><strong>✅ Sucesso! Você agora é administrador!</strong></p>";
        echo "<p><a href='admin-domains.php'>→ Acessar Painel de Admin</a></p>";
    } else {
        echo "<p style='color: orange;'><strong>⚠️ Nenhuma linha foi atualizada.</strong></p>";
        echo "<p>Verifique se o email está correto no banco.</p>";
    }

    echo "<hr>";
    echo "<p><a href='check-admin.php'>← Verificar status novamente</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Erro:</strong> " . $e->getMessage() . "</p>";
}
?>
