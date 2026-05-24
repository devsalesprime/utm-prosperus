<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

// ─── FORGOT PASSWORD ─────────────────────────────────────────────────────────
if ($action === 'forgot_password') {
    $email = htmlspecialchars(trim($input['email'] ?? ''), ENT_QUOTES, 'UTF-8');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'E-mail inválido.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires_at = ? WHERE id = ?")
            ->execute([$token, $expires, $user['id']]);

        $resetLink = env('FRONTEND_URL', 'https://utm.prosperusclub.com.br') . "/reset-password?token=" . $token;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = env('SMTP_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('SMTP_USER');
            $mail->Password   = env('SMTP_PASS');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = env('SMTP_PORT', 465);
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(env('SMTP_USER'), 'Prosperus Club');
            $mail->addAddress($email, $user['name']);

            $mail->isHTML(true);
            $mail->Subject = 'Redefinição de Senha - Prosperus Club';
            
            $htmlBody = '<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #010a12; margin: 0; padding: 0; color: #FCF7F0; -webkit-font-smoothing: antialiased; }
    .wrapper { width: 100%; background-color: #010a12; padding: 40px 0; }
    .container { max-width: 600px; margin: 0 auto; background-color: #031726; border-radius: 12px; overflow: hidden; border: 1px solid rgba(202,154,67,0.25); box-shadow: 0 15px 35px rgba(0,0,0,0.6); }
    .header { background-image: linear-gradient(135deg, #052B48 0%, #031A2B 100%); padding: 35px 20px; text-align: center; border-bottom: 2px solid #CA9A43; }
    .content { padding: 45px 40px; text-align: center; }
    .content h2 { font-size: 22px; color: #FCF7F0; margin-top: 0; margin-bottom: 20px; font-weight: 500; letter-spacing: 0.05em; }
    .content p { font-size: 15px; line-height: 1.7; color: #95A4B4; margin-bottom: 28px; }
    .btn { display: inline-block; padding: 16px 36px; background: linear-gradient(135deg, #FFDA71, #CA9A43); color: #031A2B !important; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 15px; text-transform: uppercase; letter-spacing: 0.05em; box-shadow: 0 4px 15px rgba(202,154,67,0.2); }
    .security-box { margin-top: 40px; padding: 24px; background-color: rgba(255,255,255,0.02); border-radius: 8px; border-left: 3px solid #CA9A43; text-align: left; }
    .security-box p { margin: 0 0 16px 0; font-size: 13px; color: #95A4B4; line-height: 1.6; }
    .security-box p:last-child { margin-bottom: 0; }
    .highlight { color: #FCF7F0; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; font-size: 11px; display: block; margin-bottom: 4px; }
    .support-link { color: #CA9A43; text-decoration: none; font-weight: 600; }
    .footer { padding: 24px; text-align: center; font-size: 12px; line-height: 1.6; color: #66788A; background-color: #010a12; border-top: 1px solid rgba(255,255,255,0.05); }
    @media (max-width: 600px) { .content { padding: 30px 20px; } .wrapper { padding: 20px 10px; } }
  </style>
</head>
<body>
<div class="wrapper">
  <div class="container">
    <div class="header">
      <img src="https://prosperusclub.com.br/wp-content/uploads/2026/03/Prosperus-Club-2.svg" alt="Prosperus Club" style="max-width: 180px; height: auto; display: block; margin: 0 auto; border: 0;">
    </div>
    <div class="content">
      <h2>Atualização de Credenciais</h2>
      <p>O sistema de segurança do ecossistema Prosperus registrou uma solicitação para redefinir as credenciais de acesso da sua conta corporativa.</p>
      <p>Para criar uma nova senha e retomar o seu acesso à rede de forma blindada, utilize o botão criptografado abaixo:</p>
      <a href="' . $resetLink . '" class="btn">Redefinir Credenciais</a>
      <div class="security-box">
        <p><span class="highlight">🔒 Protocolo de Segurança</span> Este link é de uso único e temporário (1 hora). Se você não solicitou esta alteração, ignore este e-mail.</p>
        <p><span class="highlight">⚠️ Caixa Não Monitorada (Noreply)</span> Este é um e-mail gerado de forma autônoma pelo servidor. <strong>Por favor, não responda a esta mensagem.</strong></p>
        <p><span class="highlight">🛎️ Concierge & Suporte</span> Em caso de dúvidas operacionais, acione a nossa equipe oficial: <a href="mailto:suporte@prosperusclub.com.br" class="support-link">suporte@prosperusclub.com.br</a></p>
      </div>
    </div>
    <div class="footer">
      Este e-mail contém informações confidenciais de uso exclusivo do destinatário original.<br>
      &copy; 2026 <a href="https://prosperusclub.com.br" style="color: #66788A; text-decoration: none;">Prosperus Club</a>. Todos os direitos reservados.
    </div>
  </div>
</div>
</body>
</html>';

            $mail->Body = $htmlBody;
            $mail->send();
        } catch (Exception $e) {
            error_log("Erro no envio do e-mail de recuperação: {$mail->ErrorInfo}");
        }
    }

    echo json_encode(['success' => true, 'message' => 'Se o e-mail estiver cadastrado, você receberá um link de redefinição.']);
    exit;
}

// ─── RESET PASSWORD ──────────────────────────────────────────────────────────
if ($action === 'reset_password') {
    $token = $input['token'] ?? '';
    $newPassword = $input['password'] ?? '';

    if (empty($token) || strlen($newPassword) < 8) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Token inválido ou senha muito curta (mínimo 8 caracteres).']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires_at > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'O link de recuperação expirou ou é inválido.']);
        exit;
    }

    $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires_at = NULL WHERE id = ?")
        ->execute([$hash, $user['id']]);

    echo json_encode(['success' => true, 'message' => 'Sua senha foi redefinida com sucesso. Faça login.']);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
