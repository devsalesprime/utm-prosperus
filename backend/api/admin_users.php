<?php
/**
 * API: Gerenciar usuários (admin only)
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? $_GET['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            $stmt = $pdo->query(
                "SELECT id, name, email, is_admin, is_approved, created_at
                 FROM users ORDER BY is_approved ASC, created_at DESC"
            );
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'approve':
            $userId = intval($input['user_id'] ?? 0);
            if (!$userId) throw new Exception('ID inválido');
            $pdo->prepare("UPDATE users SET is_approved = 1 WHERE id = ?")->execute([$userId]);
            echo json_encode(['success' => true]);
            break;

        case 'toggle_admin':
            $userId  = intval($input['user_id'] ?? 0);
            $isAdmin = intval($input['is_admin'] ?? 0);
            if (!$userId) throw new Exception('ID inválido');
            $pdo->prepare("UPDATE users SET is_admin = ? WHERE id = ?")->execute([$isAdmin, $userId]);
            echo json_encode(['success' => true]);
            break;

        default:
            throw new Exception('Ação inválida');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
