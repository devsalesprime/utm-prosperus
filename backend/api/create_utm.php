<?php
/**
 * API: Criar UTM
 * Endpoint JSON para criação de UTMs via AJAX
 * Validações server-side + prepared statements
 * Versão: 2.2
 */

require_once __DIR__ . '/../includes/config.php';

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Verificar autenticação
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

try {
    // Ler dados do request (suporta JSON e form-data)
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON inválido');
        }
    } else {
        $input = $_POST;
    }

    // Extrair e sanitizar campos
    $url = trim($input['url'] ?? $input['website_url'] ?? '');
    $shortCode = trim($input['short_code'] ?? $input['custom_name'] ?? '');
    $utmCampaign = trim($input['utm_campaign'] ?? '');
    $utmSource = trim($input['utm_source'] ?? '');
    $utmMedium = trim($input['utm_medium'] ?? '');
    $utmContent = trim($input['utm_content'] ?? '');
    $utmTerm = trim($input['utm_term'] ?? '');
    $comment = trim($input['comment'] ?? $input['utm_comment'] ?? '');
    $selectedDomain = 'prosperusclub.com.br'; // Domínio fixo

    // ============================================
    // Validações Server-Side
    // ============================================

    // Validar URL
    if (empty($url)) {
        throw new Exception('URL é obrigatória');
    }
    if (!validateUrl($url)) {
        throw new Exception('URL inválida. Informe uma URL válida (ex: https://exemplo.com)');
    }

    // Validar campaign
    if (empty($utmCampaign)) {
        throw new Exception('Canal (utm_campaign) é obrigatório');
    }

    // Validar utm_term
    if (!validateUtmTerm($utmTerm)) {
        throw new Exception('utm_term muito longo (máximo 500 caracteres)');
    }

    // Processar short code
    $pdo = Database::getInstance();

    if (!empty($shortCode)) {
        // Sanitizar nome personalizado
        $shortCode = sanitizeShortCode($shortCode);

        if (!validateShortCode($shortCode)) {
            throw new Exception('Short code deve ter 3-20 caracteres (letras, números, hífens ou underscores)');
        }

        // Verificar se já existe
        $stmt = $pdo->prepare("SELECT id FROM urls WHERE shortened_url = ?");
        $stmt->execute([$shortCode]);
        if ($stmt->rowCount() > 0) {
            throw new Exception('Short code já está em uso. Escolha outro nome.');
        }
    } else {
        // Gerar código único
        $shortCode = generateUniqueShortCode($pdo);
    }

    // ============================================
    // Construir URL com UTM
    // ============================================

    $queryParams = array_filter([
        'utm_campaign' => $utmCampaign,
        'utm_source' => $utmSource,
        'utm_medium' => $utmMedium,
        'utm_content' => $utmContent,
        'utm_term' => $utmTerm,
    ], function ($v) {
        return $v !== '' && $v !== null;
    });

    $queryString = http_build_query($queryParams);
    $longUrl = $url . (strpos($url, '?') === false ? '?' : '&') . $queryString;

    // Preservar colchetes na URL
    $longUrl = str_replace(['%5B', '%5D'], ['[', ']'], $longUrl);

    // ============================================
    // Inserir no banco de dados
    // ============================================

    $username = $_SESSION['username'] ?? 'unknown';

    $stmt = $pdo->prepare(
        "INSERT INTO urls (original_url, long_url, shortened_url, username, comment, domain)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$url, $longUrl, $shortCode, $username, $comment ?: null, $selectedDomain]);

    $utmId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'UTM criada com sucesso!',
        'data' => [
            'id' => (int) $utmId,
            'short_code' => $shortCode,
            'short_url' => 'https://prosperusclub.com.br/' . $shortCode,
            'long_url' => $longUrl,
            'original_url' => $url,
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Sanitiza short code personalizado
 */
function sanitizeShortCode($name)
{
    if (empty($name))
        return '';

    $name = trim($name);
    $name = strtolower($name);
    $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
    $name = str_replace(' ', '-', $name);
    $name = preg_replace('/[^a-z0-9\-_]/', '', $name);
    $name = preg_replace('/-+/', '-', $name);
    $name = trim($name, '-_');
    $name = substr($name, 0, 20);

    return $name;
}
?>