<?php
/**
 * Dashboard Analytics API - JSON endpoint
 * Returns KPIs, clicks by source, 30-day trend, and top UTMs
 * Version: 2.2
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

// Auth check
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

require dirname(__DIR__) . '/includes/db.php';

$period = isset($_GET['period']) ? (int) $_GET['period'] : 30;
$validPeriods = [7, 30, 90, 365];
if (!in_array($period, $validPeriods)) {
    $period = 30;
}

try {
    $response = [];

    // === KPIs ===
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total_utms,
        SUM(clicks) as total_clicks,
        ROUND(AVG(clicks), 1) as avg_clicks,
        SUM(CASE WHEN is_enabled = 1 THEN 1 ELSE 0 END) as active_utms
    FROM urls");
    $response['kpis'] = $stmt->fetch(PDO::FETCH_ASSOC);
    $response['kpis']['total_clicks'] = (int) ($response['kpis']['total_clicks'] ?? 0);
    $response['kpis']['avg_clicks'] = (float) ($response['kpis']['avg_clicks'] ?? 0);

    // === Clicks by Source ===
    // Extract utm_source from long_url using regex-like SQL
    $stmt = $pdo->query("
        SELECT 
            CASE 
                WHEN long_url LIKE '%utm_source=ig%' THEN 'Instagram'
                WHEN long_url LIKE '%utm_source=yt%' THEN 'YouTube'
                WHEN long_url LIKE '%utm_source=site%' THEN 'Site'
                WHEN long_url LIKE '%utm_source=msg%' THEN 'Mensagem'
                WHEN long_url LIKE '%utm_source=spot%' OR long_url LIKE '%utm_source=sptf%' THEN 'Spotify'
                WHEN long_url LIKE '%utm_source=linkd%' OR long_url LIKE '%utm_source=in%' THEN 'LinkedIn'
                WHEN long_url LIKE '%utm_source=wtt%' THEN 'WhatsApp'
                WHEN long_url LIKE '%utm_source=ggl%' OR long_url LIKE '%utm_source=goo%' THEN 'Google'
                WHEN long_url LIKE '%utm_source=og%' THEN 'Orgânico'
                WHEN long_url LIKE '%utm_source=email%' THEN 'Email'
                WHEN long_url LIKE '%utm_source=form%' THEN 'Formulário'
                WHEN long_url LIKE '%utm_source=rec%' THEN 'Recomendação'
                WHEN long_url LIKE '%utm_source=vd%' THEN 'QR Code'
                WHEN long_url LIKE '%utm_source=tck%' THEN 'Ticket'
                WHEN long_url LIKE '%utm_source=thrd%' THEN 'Threads'
                ELSE 'Outros'
            END as source_name,
            SUM(clicks) as total_clicks,
            COUNT(*) as utm_count
        FROM urls
        WHERE is_enabled = 1
        GROUP BY source_name
        HAVING total_clicks > 0
        ORDER BY total_clicks DESC
    ");
    $response['clicks_by_source'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // === Clicks Last N Days (from clicks table) ===
    $clicksFromTable = [];
    try {
        $stmt = $pdo->prepare("
            SELECT DATE(clicked_at) as click_date, COUNT(*) as click_count
            FROM clicks
            WHERE clicked_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(clicked_at)
            ORDER BY click_date ASC
        ");
        $stmt->execute([$period]);
        $clicksFromTable = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Silencioso: Se a tabela não existe, cairemos automaticamente no fallback abaixo
    }

    // If clicks table has data, use it; otherwise fall back to generation_date
    if (count($clicksFromTable) > 0) {
        $response['clicks_trend'] = $clicksFromTable;
        $response['trend_source'] = 'clicks_table';
    } else {
        $stmt = $pdo->prepare("
            SELECT DATE(generation_date) as click_date, SUM(clicks) as click_count
            FROM urls
            WHERE generation_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(generation_date)
            ORDER BY click_date ASC
        ");
        $stmt->execute([$period]);
        $response['clicks_trend'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response['trend_source'] = 'generation_date_fallback';
    }

    // === Top 10 UTMs ===
    $stmt = $pdo->query("
        SELECT 
            shortened_url,
            clicks,
            comment,
            username,
            generation_date,
            SUBSTRING_INDEX(SUBSTRING_INDEX(long_url, 'utm_campaign=', -1), '&', 1) as campaign_raw
        FROM urls
        WHERE is_enabled = 1 AND clicks > 0
        ORDER BY clicks DESC
        LIMIT 10
    ");
    $response['top_utms'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // === UTMs by User ===
    $stmt = $pdo->query("
        SELECT username, COUNT(*) as total, SUM(clicks) as clicks
        FROM urls
        WHERE is_enabled = 1
        GROUP BY username
        ORDER BY clicks DESC
    ");
    $response['by_user'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // === Period Info ===
    $response['period'] = $period;

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar dados']);
    error_log("Dashboard API error: " . $e->getMessage());
}
