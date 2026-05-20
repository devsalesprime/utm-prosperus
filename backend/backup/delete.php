<?php
require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id']) && isset($data['password'])) {
    $id = $data['id'];
    $password = $data['password'];

    // Defina a senha correta aqui
    $correct_password = 'x@pvjjx1qq';

    if ($password !== $correct_password) {
        echo json_encode(['success' => false, 'error' => 'Senha incorreta']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM urls WHERE id = ?");
        if ($stmt->execute([$id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'ID ou senha inválido']);
}
