<?php
/**
 * UTM Generator - Configuração Central
 * Sessões, segurança, constantes e helpers
 * Versão: 2.2
 */

// Incluir conexão com banco de dados
require_once __DIR__ . '/db.php';

// Configurações da Aplicação
define('APP_URL', env('APP_URL', 'http://localhost/utm'));
define('SESSION_LIFETIME', (int) env('SESSION_LIFETIME', 1800)); // 30 min padrão

// Segurança
define('HASH_COST', 12);
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutos

// Configuração de Sessão
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', env('SESSION_SECURE', false) ? 1 : 0);
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    session_start();
}

// ============================================
// Helper Functions
// ============================================

/**
 * Sanitiza string para output HTML seguro
 */
function sanitize($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Verifica se o usuário está logado
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']) || isset($_SESSION['username']);
}

/**
 * Verifica se o usuário é administrador
 */
function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Redireciona para login se não autenticado
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Redireciona se não for admin
 */
function requireAdmin()
{
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Gera token CSRF e armazena na sessão
 */
function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida token CSRF recebido
 */
function validateCsrfToken($token)
{
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Gera short code único verificando no banco
 */
function generateUniqueShortCode($pdo, $length = 6)
{
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $maxAttempts = 10;

    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }

        $stmt = $pdo->prepare("SELECT id FROM urls WHERE shortened_url = ?");
        $stmt->execute([$code]);
        if ($stmt->rowCount() === 0) {
            return $code;
        }
    }

    // Fallback: timestamp-based
    return substr(md5(uniqid(mt_rand(), true)), 0, $length);
}

/**
 * Valida short code
 */
function validateShortCode($code)
{
    return preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $code);
}

/**
 * Valida URL
 */
function validateUrl($url)
{
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Valida utm_term (max 500 caracteres)
 */
function validateUtmTerm($term)
{
    if (empty($term))
        return true;
    return strlen($term) <= 500;
}
?>