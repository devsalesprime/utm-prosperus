<?php
/**
 * API: Buscar Membros da Equipe (Closers, SDRs, etc)
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/db.php';

try {
    $stmt = $pdo->query("SELECT id, name, type FROM team_members ORDER BY type, name");
    $teamMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $teamMembers]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
