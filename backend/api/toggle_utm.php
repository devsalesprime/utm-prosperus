<?php
/**
 * API: Toggle is_enabled de uma UTM
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$id         = intval($input['id'] ?? 0);
$isEnabled  = intval($input['is_enabled'] ?? 0);

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$isAdmin  = (bool) ($_SESSION['is_admin'] ?? false);
$username = $_SESSION['username'] ?? '';

try {
    // Admin pode desativar qualquer UTM; usuário só as próprias
    if ($isAdmin) {
        $stmt = $pdo->prepare("UPDATE urls SET is_enabled = ? WHERE id = ?");
        $stmt->execute([$isEnabled, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE urls SET is_enabled = ? WHERE id = ? AND username = ?");
        $stmt->execute([$isEnabled, $id, $username]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno']);
}
