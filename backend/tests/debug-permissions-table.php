<?php
/**
 * Debug: Verificar user_domain_permissions
 */

session_start();
require 'db.php';

echo "<h2>🔍 Debug - user_domain_permissions</h2>";
echo "<hr>";

// Estrutura da tabela
echo "<h3>📋 Colunas da Tabela 'user_domain_permissions':</h3>";
$stmt = $pdo->query("DESCRIBE user_domain_permissions");
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

// Ver os dados inseridos anteriormente
echo "<h3>👥 Dados em user_domain_permissions:</h3>";
$stmt = $pdo->query("SELECT * FROM user_domain_permissions");
$perms = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($perms)) {
    echo "<p>❌ Nenhuma permissão encontrada</p>";
} else {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr>";
    foreach (array_keys($perms[0]) as $key) {
        echo "<th>" . htmlspecialchars($key) . "</th>";
    }
    echo "</tr>";
    
    foreach ($perms as $perm) {
        echo "<tr>";
        foreach ($perm as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}
?>
