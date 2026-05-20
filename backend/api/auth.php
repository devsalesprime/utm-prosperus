<?php
/**
 * API: Autenticação (Login, Register, Logout, Session Check)
 * Versão para UTM Prosperus — domínio único prosperusclub.com.br
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$input = [];
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
} else {
    $input = $_POST;
}

$action = $input['action'] ?? $_GET['action'] ?? '';

// ─── SESSION CHECK ────────────────────────────────────────────────────────────
if ($action === 'session') {
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'logged_in'  => true,
            'user_id'    => $_SESSION['user_id'],
            'username'   => $_SESSION['username'],
            'is_admin'   => (bool) $_SESSION['is_admin'],
        ]);
    } else {
        echo json_encode(['logged_in' => false]);
    }
    exit;
}

// ─── LOGOUT ──────────────────────────────────────────────────────────────────
if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

// ─── LOGIN ───────────────────────────────────────────────────────────────────
if ($action === 'login') {
    $email    = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // Rate limiting
    $lockoutTime  = 900;
    $maxAttempts  = 5;
    try {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) as attempts FROM login_attempts
             WHERE ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL ? SECOND)"
        );
        $stmt->execute([$clientIp, $lockoutTime]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (($result['attempts'] ?? 0) >= $maxAttempts) {
            http_response_code(429);
            echo json_encode(['success' => false, 'message' => 'Muitas tentativas. Tente novamente em 15 minutos.']);
            exit;
        }
    } catch (PDOException $e) {
        // tabela pode não existir — ignorar
    }

    // Buscar usuário
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        if (!$user['is_approved']) {
            echo json_encode(['success' => false, 'message' => 'Conta aguardando aprovação do administrador.']);
            exit;
        }

        session_regenerate_id(true);
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8');
        $_SESSION['is_admin'] = (bool) $user['is_admin'];

        echo json_encode(['success' => true, 'message' => 'Login realizado com sucesso.']);
    } else {
        // Registrar tentativa falha
        try {
            $pdo->prepare("INSERT INTO login_attempts (ip_address, email) VALUES (?, ?)")
                ->execute([$clientIp, $email]);
        } catch (PDOException $e) {}

        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Email ou senha inválidos.']);
    }
    exit;
}

// ─── REGISTER ────────────────────────────────────────────────────────────────
if ($action === 'register') {
    $name     = htmlspecialchars(trim($input['name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email    = htmlspecialchars(trim($input['email'] ?? ''), ENT_QUOTES, 'UTF-8');
    $password = $input['password'] ?? '';

    // Validar email — permitir @prosperusclub.com.br e @salesprime.com.br
    if (!(preg_match('/@prosperusclub\.com\.br$/', $email) || preg_match('/@salesprime\.com\.br$/', $email)) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Use apenas email @prosperusclub.com.br']);
        exit;
    }

    if (strlen($password) < 8) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'A senha deve ter pelo menos 8 caracteres.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email já está em uso.']);
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $pdo->prepare("INSERT INTO users (name, email, password, is_approved) VALUES (?, ?, ?, FALSE)")
        ->execute([$name, $email, $hash]);

    echo json_encode(['success' => true, 'message' => 'Cadastro enviado! Aguarde aprovação.']);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
