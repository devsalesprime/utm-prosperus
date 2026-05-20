<?php
/**
 * API: Excluir UTM (requer senha master)
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

$input    = json_decode(file_get_contents('php://input'), true) ?? [];
$id       = intval($input['id'] ?? 0);
$password = $input['password'] ?? '';

if (!$id || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID e senha são obrigatórios']);
    exit;
}

// Verificar senha master
$deletePassword = env('MASTER_PASSWORD', '');
if ($password !== $deletePassword) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Senha incorreta']);
    exit;
}

try {
    // Excluir cliques relacionados
    try {
        $pdo->prepare("DELETE FROM clicks WHERE utm_id = ?")->execute([$id]);
    } catch (PDOException $e) {}

    $stmt = $pdo->prepare("DELETE FROM urls WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'UTM não encontrada']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'UTM excluída com sucesso']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno']);
}
