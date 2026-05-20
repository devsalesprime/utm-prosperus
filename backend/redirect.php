<?php
header("X-Debug: redirect_php_executing");
/**
 * Redirecionamento de Short Codes com Tracking Detalhado
 * Registra cliques na tabela `clicks` com IP, User Agent e Referer
 * Mantém compatibilidade com go.php existente
 * Versão: 2.2
 */

require_once __DIR__ . '/includes/db.php';

// Obter o código e validar
$code = isset($_GET['code']) ? trim($_GET['code']) : '';
if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $code)) {
    header("X-Accel-Redirect: /@wordpress_internal/" . $code);
    exit;
}

$pdo = Database::getInstance();

// Buscar a URL associada e verificar se está habilitada
$stmt = $pdo->prepare("SELECT id, long_url, is_enabled FROM urls WHERE shortened_url = ?");
$stmt->execute([$code]);
$url = $stmt->fetch(PDO::FETCH_ASSOC);

if ($url) {
    // Verificar se a UTM está habilitada
    if (!$url['is_enabled']) {
        header("Location: https://prosperusclub.com.br/");
        exit;
    }

    // Incrementar contador de cliques (atômico)
    $stmt = $pdo->prepare("UPDATE urls SET clicks = COALESCE(clicks, 0) + 1 WHERE id = ?");
    $stmt->execute([$url['id']]);

    // Registrar clique detalhado na tabela clicks
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO clicks (utm_id, ip_address, user_agent, referer)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $url['id'],
            substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45),
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            substr($_SERVER['HTTP_REFERER'] ?? '', 0, 500)
        ]);
    } catch (PDOException $e) {
        // Não bloquear o redirect se o tracking falhar
        // (tabela clicks pode não existir ainda)
        error_log("Click tracking error: " . $e->getMessage());
    }

    // Redirecionar para a URL longa preservando colchetes
    $redirect_url = $url['long_url'];
    $redirect_url = str_replace(['%5B', '%5D'], ['[', ']'], $redirect_url);

    header("Location: $redirect_url", true, 302);
    exit;
}

// Código não encontrado - repassa para o WordPress internamente via Nginx
header("X-Accel-Redirect: /@wordpress_internal/" . $code);
exit;
