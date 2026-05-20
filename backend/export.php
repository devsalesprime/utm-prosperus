<?php
/**
 * Export CSV de UTMs
 * Gera arquivo CSV com dados de UTMs e contagem de cliques
 * Versão: 2.2
 */

require_once __DIR__ . '/includes/config.php';
requireLogin();

$pdo = Database::getInstance();
$username = $_SESSION['username'] ?? '';

// Headers para download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="utms_export_' . date('Y-m-d_His') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// BOM para Excel reconhecer UTF-8
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Cabeçalho
fputcsv($output, [
    'Short Code',
    'URL Original',
    'URL Completa',
    'Campaign',
    'Cliques',
    'Status',
    'Comentário',
    'Data de Criação'
], ';');

// Consulta com contagem de cliques detalhada
$query = "
    SELECT
        u.shortened_url,
        u.original_url,
        u.long_url,
        u.username,
        u.clicks,
        u.is_enabled,
        u.comment,
        u.generation_date,
        COUNT(c.id) as tracked_clicks
    FROM urls u
    LEFT JOIN clicks c ON u.id = c.utm_id
";

$params = [];

// Filtrar por usuário se não for admin
if (!isAdmin()) {
    $query .= " WHERE u.username = ?";
    $params[] = $username;
}

$query .= " GROUP BY u.id ORDER BY u.generation_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Extrair utm_campaign da URL
    $campaign = '';
    if (preg_match('/utm_campaign=([^&]+)/', $row['long_url'], $matches)) {
        $campaign = urldecode($matches[1]);
    }

    fputcsv($output, [
        $row['shortened_url'],
        $row['original_url'],
        $row['long_url'],
        $campaign,
        $row['clicks'] ?? 0,
        $row['is_enabled'] ? 'Ativo' : 'Inativo',
        $row['comment'] ?? '',
        $row['generation_date']
    ], ';');
}

fclose($output);
exit;
