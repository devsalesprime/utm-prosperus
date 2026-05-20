<?php
/**
 * Debug: Encontrar Email Correto do Usuário
 */

session_start();
require 'db.php';

echo "<h2>🔍 Debug - Estrutura da Tabela Users</h2>";
echo "<hr>";

$sessionUsername = $_SESSION['username'] ?? 'NENHUM';
echo "<p><strong>SESSION['username']:</strong> " . htmlspecialchars($sessionUsername) . "</p>";
echo "<hr>";

// Descobrir as colunas da tabela users
echo "<h3>📋 Colunas da Tabela 'users':</h3>";
$stmt = $pdo->query("DESCRIBE users");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
foreach ($columns as $col) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
    echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
    echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
    echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
    echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($col['Extra']) . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";

// Agora buscar todos os usuários
echo "<h3>👥 Todos os usuários no banco:</h3>";
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($users)) {
    die("<p>❌ Nenhum usuário encontrado no banco!</p>");
}

if (count($users) > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr>";
    foreach (array_keys($users[0]) as $key) {
        echo "<th>" . htmlspecialchars($key) . "</th>";
    }
    echo "</tr>";

    foreach ($users as $user) {
        echo "<tr>";
        foreach ($user as $value) {
            $isMatch = '';
            if (strtolower($value) === strtolower($sessionUsername)) {
                $isMatch = ' ← <strong style="color: blue;">ENCONTRADO!</strong>';
            }
            echo "<td>" . htmlspecialchars($value) . $isMatch . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}
?>
