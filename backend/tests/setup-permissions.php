<?php
/**
 * Script para Inserir Permissões Padrão
 * Atribui permissões de todos os domínios para o usuário Fábio Soares
 */

session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    die("❌ Usuário não autenticado");
}

$username = $_SESSION['username'];
$admin = $_SESSION['username']; // Quem está executando é o admin

try {
    // Obter todos os domínios
    $stmt = $pdo->query("SELECT id FROM domains");
    $domains = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($domains)) {
        die("❌ Nenhum domínio encontrado no banco!");
    }

    echo "<h2>✅ Inserindo Permissões Padrão</h2>";
    echo "<hr>";
    echo "<p><strong>Usuário:</strong> $username</p>";
    echo "<p><strong>Total de domínios encontrados:</strong> " . count($domains) . "</p>";
    echo "<hr>";

    // Inserir permissão para cada domínio
    foreach ($domains as $domain) {
        $domain_id = $domain['id'];
        
        // Verificar se já existe
        $check = $pdo->prepare("SELECT id FROM user_domain_permissions WHERE username = ? AND domain_id = ?");
        $check->execute([$username, $domain_id]);
        
        if ($check->fetch()) {
            echo "<p>⏭️  Já existe permissão para domínio ID $domain_id - pulando...</p>";
            continue;
        }

        // Inserir com todas as permissões ativadas
        $stmt = $pdo->prepare(
            "INSERT INTO user_domain_permissions (username, domain_id, can_create, can_edit, can_delete, assigned_by) 
             VALUES (?, ?, 1, 1, 1, ?)"
        );
        
        if ($stmt->execute([$username, $domain_id, $admin])) {
            echo "<p>✅ <strong>Inserida permissão para domínio ID $domain_id</strong></p>";
        } else {
            echo "<p>❌ Erro ao inserir permissão para domínio ID $domain_id</p>";
        }
    }

    echo "<hr>";
    echo "<p style='color: green; font-size: 16px;'><strong>✅ Permissões inseridas com sucesso!</strong></p>";
    echo "<p><a href='index.php'>← Voltar para gerador de UTM</a></p>";
    echo "<p><a href='debug-permissions.php'>← Verificar permissões novamente</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Erro:</strong> " . $e->getMessage() . "</p>";
}
?>
