<?php
/**
 * Verificar Status de Admin
 */

session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    die("❌ Usuário não autenticado");
}

$username = $_SESSION['username'];

echo "<h2>🔍 Status de Admin</h2>";
echo "<hr>";

// Buscar usuário
$stmt = $pdo->prepare("SELECT email, is_admin FROM users WHERE email = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("❌ Usuário não encontrado!");
}

echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
echo "<p><strong>Is Admin:</strong> " . ($user['is_admin'] ? '✅ SIM' : '❌ NÃO') . "</p>";

if (!$user['is_admin']) {
    echo "<hr>";
    echo "<p style='color: red;'><strong>❌ Você NÃO é administrador!</strong></p>";
    echo "<p>Para acessar o painel de admin, você precisa ser marcado como admin no banco.</p>";
} else {
    echo "<p style='color: green;'><strong>✅ Você é administrador!</strong></p>";
}
?>
