<?php
/**
 * =============================================================================
 * MIGRAÇÃO v2.7 - ADICIONAR SUPORTE A MULTI-DOMÍNIO
 * =============================================================================
 * 
 * Objetivo: Preparar o banco de dados existente para suportar múltiplos domínios
 * 
 * Esta migração:
 * ✓ Adiciona coluna 'domain' à tabela 'urls' (se não existir)
 * ✓ Cria tabela 'domains' (se não existir)
 * ✓ Cria tabela 'user_domain_permissions' (se não existir)
 * ✓ NÃO deleta dados existentes
 * ✓ Segura - verifica antes de modificar
 * 
 * Data: Abril 2026
 * =============================================================================
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurações de banco de dados
$db_host = 'localhost';
$db_user = 'root';          // ← CONFIGURE COM SUAS CREDENCIAIS
$db_password = '';          // ← CONFIGURE COM SUAS CREDENCIAIS
$db_name = 'utm_generator'; // ← CONFIGURE COM NOME DO SEU BANCO

// Cores para output
$colors = [
    'success' => "\033[32m",
    'error' => "\033[31m",
    'warning' => "\033[33m",
    'info' => "\033[36m",
    'reset' => "\033[0m",
];

function log_success($msg) {
    global $colors;
    echo "{$colors['success']}✓ {$msg}{$colors['reset']}\n";
}

function log_error($msg) {
    global $colors;
    echo "{$colors['error']}✗ {$msg}{$colors['reset']}\n";
}

function log_warning($msg) {
    global $colors;
    echo "{$colors['warning']}⚠ {$msg}{$colors['reset']}\n";
}

function log_info($msg) {
    global $colors;
    echo "{$colors['info']}ℹ {$msg}{$colors['reset']}\n";
}

// Conectar ao banco
$pdo = new PDO(
    "mysql:host={$db_host};dbname={$db_name}",
    $db_user,
    $db_password,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "  MIGRAÇÃO v2.7 - PREPARAR BANCO PARA MULTI-DOMÍNIO\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "\n";

try {
    log_info("Conectado ao banco: {$db_name}");

    // ═════════════════════════════════════════════════════════
    // PASSO 1: Adicionar coluna 'domain' à tabela 'urls'
    // ═════════════════════════════════════════════════════════
    
    echo "\n[1/3] Verificando tabela 'urls'...\n";
    
    // Verificar se coluna já existe
    $check_column = $pdo->query("
        SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = 'urls' AND COLUMN_NAME = 'domain'
    ");
    
    if ($check_column->rowCount() == 0) {
        log_info("Coluna 'domain' não existe. Adicionando...");
        
        $pdo->exec("
            ALTER TABLE urls 
            ADD COLUMN domain VARCHAR(255) NOT NULL DEFAULT 'salesprime.com.br' 
            AFTER shortened_url
        ");
        
        log_success("Coluna 'domain' adicionada à tabela 'urls'");
    } else {
        log_warning("Coluna 'domain' já existe em 'urls' (ignorado)");
    }

    // ═════════════════════════════════════════════════════════
    // PASSO 2: Criar tabela 'domains'
    // ═════════════════════════════════════════════════════════
    
    echo "\n[2/3] Verificando tabela 'domains'...\n";
    
    $check_domains = $pdo->query("SHOW TABLES LIKE 'domains'");
    
    if ($check_domains->rowCount() == 0) {
        log_info("Tabela 'domains' não existe. Criando...");
        
        $pdo->exec("
            CREATE TABLE domains (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                domain_url VARCHAR(255) NOT NULL UNIQUE,
                logo_light VARCHAR(255),
                logo_dark VARCHAR(255),
                brand_color VARCHAR(7),
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_domain_url (domain_url),
                INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        log_success("Tabela 'domains' criada");
    } else {
        log_warning("Tabela 'domains' já existe (ignorado)");
    }

    // ═════════════════════════════════════════════════════════
    // PASSO 3: Criar tabela 'user_domain_permissions'
    // ═════════════════════════════════════════════════════════
    
    echo "\n[3/3] Verificando tabela 'user_domain_permissions'...\n";
    
    $check_permissions = $pdo->query("SHOW TABLES LIKE 'user_domain_permissions'");
    
    if ($check_permissions->rowCount() == 0) {
        log_info("Tabela 'user_domain_permissions' não existe. Criando...");
        
        $pdo->exec("
            CREATE TABLE user_domain_permissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                domain_id INT NOT NULL,
                can_create TINYINT(1) DEFAULT 1,
                can_edit TINYINT(1) DEFAULT 1,
                can_delete TINYINT(1) DEFAULT 0,
                assigned_by VARCHAR(255),
                assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_user_domain (user_id, domain_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE,
                INDEX idx_user_id (user_id),
                INDEX idx_domain_id (domain_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        log_success("Tabela 'user_domain_permissions' criada");
    } else {
        log_warning("Tabela 'user_domain_permissions' já existe (ignorado)");
    }

    // ═════════════════════════════════════════════════════════
    // RESUMO FINAL
    // ═════════════════════════════════════════════════════════
    
    echo "\n";
    echo "═══════════════════════════════════════════════════════════\n";
    log_success("MIGRAÇÃO v2.7 CONCLUÍDA COM SUCESSO!");
    echo "═══════════════════════════════════════════════════════════\n";
    
    echo "\n📋 Próximos passos:\n";
    echo "   1. Execute: v2.7_insert_domains.php (adicionar domínios)\n";
    echo "   2. Execute: v2.7_create_admin_user.php (criar admin)\n";
    echo "   3. Execute: v2.7_assign_permissions.php (atribuir permissões)\n";
    echo "\n";

} catch (Exception $e) {
    log_error("ERRO NA MIGRAÇÃO: " . $e->getMessage());
    exit(1);
}

?>
