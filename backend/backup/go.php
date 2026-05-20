<?php
require 'db.php';

// Obter o código e validar
$code = isset($_GET['code']) ? trim($_GET['code']) : '';
if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $code)) {
    header("Location: index.php?error=invalid_code");
    exit;
}

// Buscar a URL longa associada e verificar se está habilitada
$stmt = $pdo->prepare("SELECT long_url, is_enabled FROM urls WHERE shortened_url = ?");
$stmt->execute([$code]);
$url = $stmt->fetch(PDO::FETCH_ASSOC);

if ($url) {
    // Verificar se a UTM está habilitada
    if (!$url['is_enabled']) {
        header("Location: https://salesprime.com.br/");
        exit;
    }
    // Incrementar cliques
    $stmt = $pdo->prepare("UPDATE urls SET clicks = COALESCE(clicks, 0) + 1 WHERE shortened_url = ?");
    $stmt->execute([$code]);

    // Redirecionar para a URL longa preservando colchetes
    $redirect_url = $url['long_url'];
    $redirect_url = str_replace(['%5B', '%5D'], ['[', ']'], $redirect_url);

    header("Location: $redirect_url", true, 302);
    exit;
}

// Código não encontrado
header("Location: index.php?error=not_found");
exit;