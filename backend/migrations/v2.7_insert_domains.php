<?php
/**
 * =============================================================================
 * MIGRAÇÃO v2.7 - INSERIR DOMÍNIOS (Sales Prime + Prosperus Club)
 * =============================================================================
 * 
 * Objetivo: Popular a tabela 'domains' com os domínios da plataforma
 * 
 * Esta migração:
 * ✓ Insere Sales Prime (domínio principal)
 * ✓ Insere Prosperus Club (domínio secundário)
 * ✓ Configura logos e cores de marca
 * ✓ Marca ambos como active
 * ✓ Segura - verifica duplicatas antes de inserir
 * 
 * Pré-requisito: Executar v2.7_add_domain_support.php ANTES
 * 
 * Data: Abril 2026
 * =============================================================================
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurações de banco de dados
$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'utm_generator'; // ← CONFIGURE

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
echo "  MIGRAÇÃO v2.7 - INSERIR DOMÍNIOS\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "\n";

try {
    log_info("Conectado ao banco: {$db_name}");

    // Domínios a inserir
    $domains = [
        [
            'name' => 'Sales Prime',
            'domain_url' => 'salesprime.com.br',
            'logo_light' => 'images/logo_sales_prime.png',
            'logo_dark' => 'images/logo-dark.png',
            'brand_color' => '#0D6EFD',
        ],
        [
            'name' => 'Prosperus Club',
            'domain_url' => 'prosperusclub.com.br',
            'logo_light' => 'images/logo_prosperus_club.png',
            'logo_dark' => 'images/logo-dark.png',
            'brand_color' => '#FFC107',
        ],
    ];

    $inserted = 0;
    $skipped = 0;

    foreach ($domains as $domain) {
        log_info("Verificando: {$domain['name']} ({$domain['domain_url']})");

        // Verificar se já existe
        $check = $pdo->prepare("
            SELECT id FROM domains WHERE domain_url = ?
        ");
        $check->execute([$domain['domain_url']]);

        if ($check->rowCount() > 0) {
            log_warning("{$domain['name']} já existe no banco (ignorado)");
            $skipped++;
        } else {
            // Inserir domínio
            $insert = $pdo->prepare("
                INSERT INTO domains (name, domain_url, logo_light, logo_dark, brand_color, is_active)
                VALUES (?, ?, ?, ?, ?, 1)
            ");

            $insert->execute([
                $domain['name'],
                $domain['domain_url'],
                $domain['logo_light'],
                $domain['logo_dark'],
                $domain['brand_color'],
            ]);

            $domain_id = $pdo->lastInsertId();
            log_success("{$domain['name']} inserido (ID: {$domain_id})");
            $inserted++;
        }
    }

    // ═════════════════════════════════════════════════════════
    // VERIFICAR RESULTADO
    // ═════════════════════════════════════════════════════════

    echo "\n";
    log_info("Verificando domínios no banco...\n");

    $select = $pdo->query("
        SELECT id, name, domain_url, brand_color, is_active 
        FROM domains 
        ORDER BY id ASC
    ");

    $results = $select->fetchAll(PDO::FETCH_ASSOC);

    echo "┌─────┬──────────────────┬──────────────────────┬─────────┬────────┐\n";
    echo "│ ID  │ Nome             │ Domain URL           │ Cor     │ Ativo  │\n";
    echo "├─────┼──────────────────┼──────────────────────┼─────────┼────────┤\n";

    foreach ($results as $row) {
        printf(
            "│ %-3d │ %-16s │ %-20s │ %-7s │ %-6s │\n",
            $row['id'],
            $row['name'],
            $row['domain_url'],
            $row['brand_color'],
            $row['is_active'] ? 'Sim' : 'Não'
        );
    }

    echo "└─────┴──────────────────┴──────────────────────┴─────────┴────────┘\n";

    // ═════════════════════════════════════════════════════════
    // RESUMO FINAL
    // ═════════════════════════════════════════════════════════

    echo "\n";
    echo "═══════════════════════════════════════════════════════════\n";
    log_success("Domínios inseridos com sucesso!");
    echo "═══════════════════════════════════════════════════════════\n";

    echo "\n📊 Resumo:\n";
    echo "   • Inseridos: {$inserted}\n";
    echo "   • Ignorados (já existiam): {$skipped}\n";
    echo "   • Total no banco: " . count($results) . "\n";

    echo "\n📋 Próximo passo:\n";
    echo "   Execute: v2.7_create_admin_user.php (criar usuário admin)\n";
    echo "\n";

} catch (Exception $e) {
    log_error("ERRO: " . $e->getMessage());
    exit(1);
}

?>
