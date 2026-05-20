<?php
/**
 * API: Verificar disponibilidade de Short Code
 * Retorna JSON {available: true/false}
 * Versão: 2.2
 */

require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['available' => false, 'message' => 'Método não permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$shortCode = trim($input['short_code'] ?? '');

if (empty($shortCode) || strlen($shortCode) < 3) {
    echo json_encode(['available' => false, 'message' => 'Short code muito curto']);
    exit;
}

if (!preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $shortCode)) {
    echo json_encode(['available' => false, 'message' => 'Caracteres inválidos']);
    exit;
}

$pdo = Database::getInstance();
$stmt = $pdo->prepare("SELECT id FROM urls WHERE shortened_url = ?");
$stmt->execute([$shortCode]);

echo json_encode([
    'available' => $stmt->rowCount() === 0,
    'short_code' => $shortCode
]);
