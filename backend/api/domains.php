<?php
/**
 * API - Gerenciamento de Domínios
 * Versão: 2.5
 * Apenas admins podem acessar
 */

session_start();
require __DIR__ . '/../includes/db.php';

// Verificar autenticação e admin
if (!isset($_SESSION['username']) || !isset($_SESSION['is_admin'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Não autenticado']));
}

if (!$_SESSION['is_admin']) {
    http_response_code(403);
    die(json_encode(['error' => 'Acesso negado']));
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        // LIST (GET)
        case 'list':
            $stmt = $pdo->query("SELECT * FROM domains ORDER BY name");
            $domains = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $domains]);
            break;

        // STATS (GET)
        case 'stats':
            $stmt = $pdo->query("SELECT COUNT(*) as total_domains, SUM(is_active) as active_domains FROM domains");
            $summary = $stmt->fetch(PDO::FETCH_ASSOC);
            $summary['inactive_domains'] = $summary['total_domains'] - $summary['active_domains'];

            $stmt = $pdo->query(
                "SELECT 
                    COALESCE(domain, 'salesprime.com.br') as domain,
                    COUNT(*) as total_urls,
                    SUM(clicks) as total_clicks,
                    COUNT(DISTINCT username) as unique_users
                FROM urls
                GROUP BY domain
                ORDER BY total_clicks DESC"
            );
            $by_domain = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'stats' => [
                'total_domains' => (int)$summary['total_domains'],
                'active_domains' => (int)$summary['active_domains'],
                'inactive_domains' => (int)$summary['inactive_domains'],
                'by_domain' => $by_domain
            ]]);
            break;

        // CREATE
        case 'create_domain':
            $name = trim($_POST['name'] ?? '');
            $domain_url = trim($_POST['domain_url'] ?? '');
            $logo_light = trim($_POST['logo_light'] ?? null);
            $logo_dark = trim($_POST['logo_dark'] ?? null);
            $brand_color = trim($_POST['brand_color'] ?? '#0D6EFD');

            if (!$name || !$domain_url) {
                throw new Exception('Nome e URL são obrigatórios');
            }

            $stmt = $pdo->prepare(
                "INSERT INTO domains (name, domain_url, logo_light, logo_dark, brand_color) 
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$name, $domain_url, $logo_light, $logo_dark, $brand_color]);

            echo json_encode(['success' => true, 'message' => 'Domínio criado com sucesso']);
            break;

        // UPDATE
        case 'update_domain':
            $domain_id = intval($_POST['domain_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $domain_url = trim($_POST['domain_url'] ?? '');
            $brand_color = trim($_POST['brand_color'] ?? '#0D6EFD');
            $is_active = intval($_POST['is_active'] ?? 1);

            if (!$domain_id || !$name || !$domain_url) {
                throw new Exception('Dados inválidos');
            }

            $stmt = $pdo->prepare(
                "UPDATE domains 
                 SET name = ?, domain_url = ?, brand_color = ?, is_active = ? 
                 WHERE id = ?"
            );
            $stmt->execute([$name, $domain_url, $brand_color, $is_active, $domain_id]);

            echo json_encode(['success' => true, 'message' => 'Domínio atualizado com sucesso']);
            break;

        // DELETE
        case 'delete_domain':
            $domain_id = intval($_POST['domain_id'] ?? 0);

            if (!$domain_id) {
                throw new Exception('ID do domínio inválido');
            }

            // Verificar se há URLs associadas
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM urls WHERE domain = (SELECT domain_url FROM domains WHERE id = ?)");
            $stmt->execute([$domain_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                throw new Exception("Não é possível deletar este domínio. Existem {$result['count']} URLs associadas.");
            }

            $stmt = $pdo->prepare("DELETE FROM domains WHERE id = ?");
            $stmt->execute([$domain_id]);

            echo json_encode(['success' => true, 'message' => 'Domínio deletado com sucesso']);
            break;

        default:
            throw new Exception('Ação inválida');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
