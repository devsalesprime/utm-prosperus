<?php
/**
 * Detalhamento de um dia da tendencia: quais UTMs receberam clique
 * naquela data e quantos. Fonte: tabela `clicks` (eventos reais),
 * a mesma que alimenta a curva em dashboard_data.php.
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

require dirname(__DIR__) . '/includes/db.php';

$date = $_GET['date'] ?? '';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !strtotime($date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Data inválida. Use AAAA-MM-DD.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT
            u.id,
            u.shortened_url,
            u.comment,
            u.username,
            COUNT(*) AS clicks_day,
            SUBSTRING_INDEX(SUBSTRING_INDEX(u.long_url, 'utm_campaign=', -1), '&', 1) AS campaign,
            SUBSTRING_INDEX(SUBSTRING_INDEX(u.long_url, 'utm_source=', -1), '&', 1) AS source
        FROM clicks c
        JOIN urls u ON u.id = c.utm_id
        WHERE DATE(c.created_at) = ?
        GROUP BY u.id
        ORDER BY clicks_day DESC, u.shortened_url ASC
    ");
    $stmt->execute([$date]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total = 0;
    foreach ($rows as &$r) {
        $r['id'] = (int) $r['id'];
        $r['clicks_day'] = (int) $r['clicks_day'];
        $total += $r['clicks_day'];
        // campo que nao continha o parametro devolve o URL inteiro: vira vazio
        if (strpos($r['campaign'], '://') !== false) $r['campaign'] = '';
        if (strpos($r['source'], '://') !== false)   $r['source']   = '';
    }
    unset($r);

    echo json_encode([
        'date'  => $date,
        'total' => $total,
        'utms'  => $rows,
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao consultar o dia.']);
}
