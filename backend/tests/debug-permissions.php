<?php
/**
 * Script de Debug - Validação de Permissões de Domínio
 * Verifica se as permissões estão sendo encontradas corretamente
 */

session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    die("❌ Usuário não autenticado");
}

$username = $_SESSION['username'];
echo "<h2>🔍 Debug: Validação de Permissões de Domínio</h2>";
echo "<hr>";

// 1. Verificar usuário
echo "<h3>1️⃣ Usuário Logado:</h3>";
echo "<p><strong>Username:</strong> $username</p>";

// 2. Ver dados na tabela domains
echo "<h3>2️⃣ Domínios Cadastrados:</h3>";
$stmt = $pdo->query("SELECT id, name, domain_url FROM domains");
$domains = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Nome</th><th>Domain URL</th></tr>";
foreach ($domains as $d) {
    echo "<tr><td>{$d['id']}</td><td>{$d['name']}</td><td>{$d['domain_url']}</td></tr>";
}
echo "</table>";

// 3. Ver permissões do usuário
echo "<h3>3️⃣ Permissões Atribuídas para '$username':</h3>";
$stmt = $pdo->prepare(
    "SELECT udp.*, d.name, d.domain_url 
     FROM user_domain_permissions udp
     JOIN domains d ON udp.domain_id = d.id
     WHERE udp.username = ?"
);
$stmt->execute([$username]);
$permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($permissions)) {
    echo "<p style='color: red;'><strong>❌ NENHUMA PERMISSÃO ATRIBUÍDA!</strong></p>";
} else {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Domain Name</th><th>Domain URL</th><th>Can Create</th><th>Can Edit</th><th>Can Delete</th><th>Assigned By</th></tr>";
    foreach ($permissions as $p) {
        echo "<tr>";
        echo "<td>{$p['id']}</td>";
        echo "<td>{$p['name']}</td>";
        echo "<td>{$p['domain_url']}</td>";
        echo "<td>" . ($p['can_create'] ? '✅' : '❌') . "</td>";
        echo "<td>" . ($p['can_edit'] ? '✅' : '❌') . "</td>";
        echo "<td>" . ($p['can_delete'] ? '✅' : '❌') . "</td>";
        echo "<td>{$p['assigned_by']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 4. Testar a query de validação para prosperusclub.com.br
echo "<h3>4️⃣ Teste de Validação - 'prosperusclub.com.br':</h3>";
$stmt = $pdo->prepare(
    "SELECT COUNT(*) as has_permission 
     FROM user_domain_permissions udp
     JOIN domains d ON udp.domain_id = d.id
     WHERE udp.username = ? AND d.domain_url = ? AND udp.can_create = 1"
);
$stmt->execute([$username, 'prosperusclub.com.br']);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<p><strong>Resultado:</strong> " . ($result['has_permission'] > 0 ? '✅ TEM PERMISSÃO' : '❌ SEM PERMISSÃO') . "</p>";

// 5. Testar a query de validação para salesprime.com.br
echo "<h3>5️⃣ Teste de Validação - 'salesprime.com.br':</h3>";
$stmt = $pdo->prepare(
    "SELECT COUNT(*) as has_permission 
     FROM user_domain_permissions udp
     JOIN domains d ON udp.domain_id = d.id
     WHERE udp.username = ? AND d.domain_url = ? AND udp.can_create = 1"
);
$stmt->execute([$username, 'salesprime.com.br']);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<p><strong>Resultado:</strong> " . ($result['has_permission'] > 0 ? '✅ TEM PERMISSÃO' : '❌ SEM PERMISSÃO') . "</p>";

// 6. Debugging - queries raw
echo "<h3>6️⃣ Debug Raw - Tabela user_domain_permissions completa:</h3>";
$stmt = $pdo->query("SELECT * FROM user_domain_permissions");
$all_perms = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Username</th><th>Domain ID</th><th>Can Create</th><th>Can Edit</th><th>Can Delete</th><th>Assigned At</th><th>Assigned By</th></tr>";
foreach ($all_perms as $p) {
    echo "<tr>";
    echo "<td>{$p['id']}</td>";
    echo "<td>{$p['username']}</td>";
    echo "<td>{$p['domain_id']}</td>";
    echo "<td>" . ($p['can_create'] ? '✅' : '❌') . "</td>";
    echo "<td>" . ($p['can_edit'] ? '✅' : '❌') . "</td>";
    echo "<td>" . ($p['can_delete'] ? '✅' : '❌') . "</td>";
    echo "<td>{$p['assigned_at']}</td>";
    echo "<td>{$p['assigned_by']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<p><a href='index.php'>← Voltar</a></p>";
?>
