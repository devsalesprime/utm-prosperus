<?php
/**
 * API: Resolver URL Remotamente (Micro-Redirecionadores Cross-Domain)
 * Recebe requests de hospedagens externas (ex: Prosperus Club) 
 * Registra o clique asssociado àquele IP/User-Agent remoto
 * Retorna a long_url final para que o servidor externo faça o Header Location.
 * Versão: 1.0 (Para suporte Multi-Domínio V3)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // Permite GET de qualquer IP/Server

require dirname(__DIR__) . '/includes/db.php';

// O código encurtado que a pessoa acessou no outro site
$code = isset($_GET['code']) ? trim($_GET['code']) : '';

// Validar código
if (empty($code) || !preg_match('/^[a-zA-Z0-9_\-]+$/', $code)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'invalid_code']);
    exit;
}

// O micro script remoto pode mandar os dados do usuário final que clicou (opcionalmente)
// Para que nosso histórico de cliques seja exato e não grave apenas o IP do servidor externo
$remote_ip = isset($_GET['ip']) ? trim($_GET['ip']) : '';
$remote_ua = isset($_GET['ua']) ? trim($_GET['ua']) : '';
$remote_ref = isset($_GET['ref']) ? trim($_GET['ref']) : '';

// Buscar a URL
$stmt = $pdo->prepare("SELECT id, long_url, is_enabled FROM urls WHERE shortened_url = ?");
$stmt->execute([$code]);
$url = $stmt->fetch(PDO::FETCH_ASSOC);

if ($url) {
    // Se a campanha foi pausada
    if (!$url['is_enabled']) {
        echo json_encode([
            'success' => true,
            'long_url' => 'https://salesprime.com.br/' // Fallback centralizado se tiver inativo
        ]);
        exit;
    }

    // Incrementar contagem agregada de Cliques
    $stmt = $pdo->prepare("UPDATE urls SET clicks = COALESCE(clicks, 0) + 1 WHERE id = ?");
    $stmt->execute([$url['id']]);

    // Registrar clique detalhado na tabela clicks
    // Tentar executar, mas mascarar erro silencioso se a tabela `clicks` ainda não foi criada (Retrocompatibilidade)
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO clicks (utm_id, ip_address, user_agent, referer)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $url['id'],
            substr($remote_ip, 0, 45),
            substr($remote_ua, 0, 500),
            substr($remote_ref, 0, 500)
        ]);
    } catch (PDOException $e) {
        // Silencioso se a tabela clicks sumir ou não existir
    }

    // Redirecionar para a URL longa preservando colchetes
    $redirect_url = $url['long_url'];
    $redirect_url = str_replace(['%5B', '%5D'], ['[', ']'], $redirect_url);

    echo json_encode(['success' => true, 'long_url' => $redirect_url]);
    exit;
}

// Código não existe
http_response_code(404);
echo json_encode(['success' => false, 'error' => 'not_found']);
exit;
