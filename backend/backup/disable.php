<?php
session_start();
require 'db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Acesso negado. Usuário não logado.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id']) && isset($data['status'])) {
    $id = $data['id'];
    $status = (int)$data['status']; // 1 para habilitar, 0 para desabilitar

    try {
        // Prepara a query para atualizar o status
        $stmt = $pdo->prepare("UPDATE urls SET is_enabled = ? WHERE id = ?");
        
        // Executa a query
        if ($stmt->execute([$status, $id])) {
            echo json_encode(['success' => true, 'new_status' => $status]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erro ao atualizar o status da UTM.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'ID ou status inválido.']);
}
?>
