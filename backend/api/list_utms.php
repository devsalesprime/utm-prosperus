<?php
/**
 * API: Listar UTMs com paginação e busca
 * Versão: 1.0 — UTM Prosperus
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autenticado']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$page     = max(1, intval($_GET['page'] ?? 1));
$perPage  = 20;
$search   = trim($_GET['search'] ?? '');
$offset   = ($page - 1) * $perPage;
$isAdmin  = (bool) ($_SESSION['is_admin'] ?? false);
$username = $_SESSION['username'] ?? '';

try {
    $conditions = [];
    $params     = [];

    // Admin vê tudo; usuário normal só vê os próprios
    if (!$isAdmin) {
        $conditions[] = "username = ?";
        $params[]     = $username;
    }

    if ($search !== '') {
        $conditions[] = "(shortened_url LIKE ? OR long_url LIKE ? OR comment LIKE ? OR username LIKE ?)";
        $like = "%{$search}%";
        $params = array_merge($params, [$like, $like, $like, $like]);
    }

    $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

    // Total
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM urls $where");
    $stmtCount->execute($params);
    $total = (int) $stmtCount->fetchColumn();

    // Data
    $stmtData = $pdo->prepare(
        "SELECT id, original_url, long_url, shortened_url, username, comment,
                clicks, is_enabled, domain, generation_date
         FROM urls $where
         ORDER BY generation_date DESC
         LIMIT $perPage OFFSET $offset"
    );
    $stmtData->execute($params);
    $rows = $stmtData->fetchAll(PDO::FETCH_ASSOC);

    // Montar short_url
    foreach ($rows as &$row) {
        $row['short_url'] = 'https://utm.prosperusclub.com.br/go/' . $row['shortened_url'];
        $row['is_enabled'] = (bool) $row['is_enabled'];
    }

    echo json_encode([
        'data'  => $rows,
        'total' => $total,
        'pages' => (int) ceil($total / $perPage),
        'page'  => $page,
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar UTMs']);
    error_log("list_utms error: " . $e->getMessage());
}
