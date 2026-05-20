<?php
session_start();
require 'includes/db.php';

// Verifique se o usuário está autenticado
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

function generateShortCode()
{
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $code;
}

/**
 * Função para sanitizar o nome personalizado
 * Remove espaços, caracteres especiais e acentos
 * Mantém apenas letras, números, hífens e underscores
 */
function sanitizeCustomName($name)
{
    if (empty($name)) {
        return '';
    }

    // Remove espaços no início e fim
    $name = trim($name);

    // Converte para minúsculas
    $name = strtolower($name);

    // Remove acentos e caracteres especiais
    $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);

    // Substitui espaços por hífens
    $name = str_replace(' ', '-', $name);

    // Remove caracteres que não sejam letras, números, hífens ou underscores
    $name = preg_replace('/[^a-z0-9\-_]/', '', $name);

    // Remove múltiplos hífens consecutivos
    $name = preg_replace('/-+/', '-', $name);

    // Remove hífens no início e fim
    $name = trim($name, '-_');

    // Limita o tamanho a 50 caracteres
    $name = substr($name, 0, 50);

    return $name;
}

/**
 * PARTE 3 - Validar permissão de domínio do usuário
 * Retorna o domínio permitido ou o default (salesprime.com.br)
 */
function validateUserDomainPermission($pdo, $userId, $requestedDomain)
{
    // Se for admin, bypass na verificação restrita de tabela
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
        return $requestedDomain;
    }

    // Verificar no banco de dados
    try {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) as has_permission 
             FROM user_domain_permissions udp
             JOIN domains d ON udp.domain_id = d.id
             WHERE udp.user_id = ? AND d.domain_url = ? AND udp.can_create = 1"
        );
        $stmt->execute([$userId, $requestedDomain]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se tem permissão explícita
        if ($result['has_permission'] > 0) {
            return $requestedDomain;
        }

        // Sem permissão - morre com aviso amigável
        die("<div style='font-family:sans-serif; padding: 20px;'><h3>Acesso Negado</h3><p>Você não tem permissão para gerar links no domínio <b>" . htmlspecialchars($requestedDomain) . "</b>.</p><p><a href='index.php'>Voltar</a></p></div>");

    } catch (Exception $e) {
        error_log("Domain permission check error: " . $e->getMessage());
        die("Aviso: As tabelas de permissões multi-domínio ainda não foram configuradas no banco de dados.");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $website_url = trim($_POST['website_url']);
    $utm_campaign = trim($_POST['utm_campaign']);
    $utm_content = trim($_POST['utm_content']);
    $utm_source = trim($_POST['utm_source']);

    // utm_medium may come from select (utm_medium) or custom (utm_medium_custom)
    $utm_medium = isset($_POST['utm_medium']) ? trim($_POST['utm_medium']) : '';
    if ($utm_medium === 'OUTRO' || $utm_medium === '') {
        $utm_medium = isset($_POST['utm_medium_custom']) ? trim($_POST['utm_medium_custom']) : $utm_medium;
    }

    $utm_term = trim($_POST['utm_term']);
    $custom_name = trim($_POST['custom_name']);
    $username = $_SESSION['username'];
    $user_id = $_SESSION['user_id'] ?? null;

    // PARTE 3 - Capturar e validar domínio do POST
    $requestedDomain = trim($_POST['domain'] ?? 'salesprime.com.br');
    $domain = validateUserDomainPermission($pdo, $user_id, $requestedDomain);

    // Validar URL base
    if (!filter_var($website_url, FILTER_VALIDATE_URL)) {
        die("URL inválida. Por favor insira uma URL válida.");
    }

    // Normalizar função
    $normalize = function ($s) {
        if (empty($s))
            return '';
        // Remove acentos
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        // Converter para maiúsculo
        $s = strtoupper(trim($s));
        // Remover caracteres especiais (manter apenas A-Z, 0-9, espaços, hífens e underscores)
        $s = preg_replace('/[^A-Z0-9\s\-_]/', '', $s);
        // Substituir espaços por underscores
        $s = preg_replace('/\s+/', '_', $s);
        // Remover múltiplos underscores
        $s = preg_replace('/_+/', '_', $s);
        // Remover underscores no início e fim
        $s = trim($s, '_');
        return $s;
    };

    // Normalizar valores básicos
    $utm_campaign_normalized = $normalize($utm_campaign);
    $utm_medium_normalized = $normalize($utm_medium);
    $utm_term_normalized = $normalize($utm_term);

    // Inicializar variáveis finais
    $utm_campaign_final = '';
    $utm_content_final = '';

    // Verificar se é Social Selling (utm_content termina com _SSELL)
    if (strpos($utm_content, '_SSELL') !== false) {
        // Extrair o nome do Social Selling (removendo _SSELL)
        $ss_name = str_replace('_SSELL', '', $utm_content);

        // Verificar se há ss_content selecionado
        $ss_activity = '';
        if (isset($_POST['ss_content']) && !empty($_POST['ss_content'])) {
            $ss_content = trim($_POST['ss_content']);
            // Separar ATIVO/PASSIVO da plataforma (IG/IN)
            $parts = explode('_', $ss_content);
            if (count($parts) === 2) {
                $ss_activity = $parts[0]; // ATIVO ou PASSIVO
            }
        }

        // Formatar utm_campaign: [PERFIL][SSELL][NOME_SSELL]
        $utm_campaign_final = '[' . $utm_campaign_normalized . '][SSELL][' . $normalize($ss_name) . ']';

        // Formatar utm_content: [ATIVO] ou [PASSIVO] (só se houver atividade)
        $utm_content_final = $ss_activity ? '[' . $ss_activity . ']' : '';
    }
    // Verificar se é Comercial (utm_content termina com _CLOSER)
    else if (strpos($utm_content, '_CLOSER') !== false) {
        // Extrair o nome do Closer (removendo _CLOSER)
        $closer_name = str_replace('_CLOSER', '', $utm_content);

        // Formatar utm_campaign: [PERFIL][VND][NOME_CLOSER]
        $utm_campaign_final = '[' . $utm_campaign_normalized . '][VND][' . $normalize($closer_name) . ']';

        // utm_content vazio
        $utm_content_final = '';
    }
    // Verificar se é SDR (utm_content termina com _SDR)
    else if (strpos($utm_content, '_SDR') !== false) {
        // Extrair o nome do SDR (removendo _SDR)
        $sdr_name = str_replace('_SDR', '', $utm_content);

        // Formatar utm_campaign: [PERFIL][SDR][NOME_SDR]
        $utm_campaign_final = '[' . $utm_campaign_normalized . '][SDR][' . $normalize($sdr_name) . ']';

        // utm_content vazio
        $utm_content_final = '';
    }
    // Verificar se é Suporte/CS (utm_content termina com _CS)
    else if (strpos($utm_content, '_CS') !== false) {
        // Extrair o nome do CS (removendo _CS)
        $cs_name = str_replace('_CS', '', $utm_content);

        // Formatar utm_campaign: [PERFIL][CS][NOME_CS]
        $utm_campaign_final = '[' . $utm_campaign_normalized . '][CS][' . $normalize($cs_name) . ']';

        // utm_content vazio
        $utm_content_final = '';
    }
    // Verificar se é Mídia Offline (utm_content começa com MO_)
    else if (strpos($utm_content, 'MO_') === 0) {
        // Remover o prefixo MO_
        $mo_content = substr($utm_content, 3);

        // Formatar utm_campaign: [PERFIL][MO][CONTENT]
        $utm_campaign_final = '[' . $utm_campaign_normalized . '][MO][' . $normalize($mo_content) . ']';

        // utm_content vazio
        $utm_content_final = '';
    }
    // Para casos TP, TO, SEM, TJJ, TV, APP
    else if (in_array($utm_content, ['TP', 'TO', 'SEM', 'TJJ', 'TV', 'APP'])) {
        // Formatar utm_campaign: [PERFIL][TP] (ou [PERFIL][TO], etc.)
        $utm_campaign_final = '[' . $utm_campaign_normalized . '][' . $utm_content . ']';

        // utm_content vazio
        $utm_content_final = '';
    }
    // Para qualquer outro caso não previsto (fallback)
    else {
        // Formatar utm_campaign: [PERFIL][VALOR] para garantir consistência
        $utm_campaign_final = '[' . $utm_campaign_normalized . '][' . $normalize($utm_content) . ']';

        // utm_content vazio por padrão
        $utm_content_final = '';
    }

    // Construir a URL com UTM
    $utm_url = $website_url;

    // Adicionar parâmetros UTM
    $query_params = [
        'utm_campaign' => $utm_campaign_final,
        'utm_source' => $utm_source,
        'utm_medium' => $utm_medium_normalized,
        'utm_term' => $utm_term_normalized
    ];

    // Adicionar utm_content se houver
    if (!empty($utm_content_final)) {
        $query_params['utm_content'] = $utm_content_final;
    }

    // Filtrar parâmetros vazios para manter a URL limpa
    $query_params = array_filter($query_params, function ($value) {
        return $value !== '' && $value !== null;
    });

    // Construir a query string
    $query_string = http_build_query($query_params);
    $utm_url .= (strpos($utm_url, '?') === false ? '?' : '&') . $query_string;

    // Corrigir caracteres codificados
    $utm_url = str_replace(['%5B', '%5D'], ['[', ']'], $utm_url);

    try {
        // Criar tabela se não existir (inclui colunas usadas em outras partes do sistema)
        $pdo->exec("CREATE TABLE IF NOT EXISTS urls (
            id INT AUTO_INCREMENT PRIMARY KEY,
            original_url TEXT NOT NULL,
            long_url TEXT NOT NULL,
            shortened_url VARCHAR(255) NOT NULL UNIQUE,
            username VARCHAR(255) NOT NULL,
            comment TEXT,
            clicks INT DEFAULT 0,
            is_enabled TINYINT(1) DEFAULT 1,
            qr_code_path VARCHAR(255) DEFAULT NULL,
            generation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Sanitizar nome personalizado
        $sanitized_custom_name = sanitizeCustomName($custom_name);

        // Verificar nome personalizado
        if (!empty($sanitized_custom_name)) {
            $stmt = $pdo->prepare("SELECT id FROM urls WHERE shortened_url = ?");
            $stmt->execute([$sanitized_custom_name]);
            if ($stmt->rowCount() > 0) {
                echo "<script>alert('Nome personalizado já está em uso. Por favor escolha outro nome.'); window.history.back();</script>";
                exit;
            }
            $short_code = $sanitized_custom_name;
        } else {
            // Gerar código curto único
            do {
                $short_code = generateShortCode();
                $stmt = $pdo->prepare("SELECT id FROM urls WHERE shortened_url = ?");
                $stmt->execute([$short_code]);
            } while ($stmt->rowCount() > 0);
        }

        // Inserir na base de dados (com coluna domain)
        $stmt = $pdo->prepare("INSERT INTO urls (original_url, long_url, shortened_url, username, comment, domain, qr_code_path) VALUES (?, ?, ?, ?, ?, ?, NULL)");
        if ($stmt->execute([$website_url, $utm_url, $short_code, $username, $_POST['utm_comment'] ?? null, $domain])) {
            // Se o nome foi sanitizado, armazenar mensagem na sessão
            if (!empty($custom_name) && $sanitized_custom_name !== $custom_name) {
                $_SESSION['utm_alert_message'] = 'O nome personalizado foi ajustado para: "' . $sanitized_custom_name . '"\n\nCaracteres especiais, espaços e acentos foram removidos para garantir compatibilidade.';
            }

            // Redirecionar para index.php
            header("Location: index.php?success=1&code=" . $short_code);
            exit();
        } else {
            die("Erro ao inserir UTM no banco de dados.");
        }
    } catch (PDOException $e) {
        die("Erro ao processar URL: " . $e->getMessage());
    }
} else {
    // Se não for POST, redirecionar para index
    header("Location: index.php");
    exit();
}
?>