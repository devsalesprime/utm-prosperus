<?php
/**
 * Login e Registro de Usuários
 * Versão: 2.2 - Rate limiting via login_attempts table
 */

session_start();
require 'includes/db.php';

function validateEmail($email)
{
    if (!preg_match('/@salesprime\.com\.br$/', $email)) {
        return false;
    }
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password)
{
    return strlen($password) >= 8;
}

/**
 * Verifica rate limiting por IP
 * Retorna true se o IP está bloqueado
 */
function isRateLimited($pdo, $ip)
{
    try {
        $lockoutTime = 900; // 15 minutos
        $maxAttempts = 5;

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) as attempts FROM login_attempts 
             WHERE ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL ? SECOND)"
        );
        $stmt->execute([$ip, $lockoutTime]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return ($result['attempts'] ?? 0) >= $maxAttempts;
    } catch (PDOException $e) {
        // Se a tabela não existe ainda, não bloquear
        return false;
    }
}

/**
 * Registra tentativa de login falhada
 */
function logFailedAttempt($pdo, $ip, $email)
{
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO login_attempts (ip_address, email) VALUES (?, ?)"
        );
        $stmt->execute([$ip, $email]);
    } catch (PDOException $e) {
        // Silencioso se tabela não existe
    }
}

/**
 * Limpa tentativas antigas (mais de 1 hora)
 */
function cleanOldAttempts($pdo)
{
    try {
        $pdo->exec("DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    } catch (PDOException $e) {
        // Silencioso
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // Limpar tentativas antigas periodicamente
    cleanOldAttempts($pdo);

    if (isset($_POST['login'])) {
        // Verificar rate limiting
        if (isRateLimited($pdo, $clientIp)) {
            $_SESSION['error'] = "Muitas tentativas de login. Tente novamente em 15 minutos.";
            header('Location: index.php');
            exit();
        }

        $email = htmlspecialchars(trim($_POST['email']), ENT_QUOTES, 'UTF-8');
        $password = $_POST['password'];

        $query = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $query->execute([$email]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if (!$user['is_approved']) {
                $_SESSION['error'] = "Sua conta ainda não foi aprovada pelo administrador.";
                header('Location: index.php');
                exit();
            }

            // Regenerar ID da sessão para prevenir session fixation
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8');
            $_SESSION['is_admin'] = $user['is_admin'];
            header('Location: index.php');
            exit();
        } else {
            // Registrar tentativa falhada
            logFailedAttempt($pdo, $clientIp, $email);
            $_SESSION['error'] = "Email ou senha inválidos";
        }

    } elseif (isset($_POST['register'])) {
        $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars(trim($_POST['email']), ENT_QUOTES, 'UTF-8');
        $password = $_POST['password'];

        if (!validateEmail($email)) {
            $_SESSION['error'] = "Email inválido. Use apenas email @salesprime.com.br";
        } elseif (!validatePassword($password)) {
            $_SESSION['error'] = "A senha deve ter pelo menos 8 caracteres";
        } else {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            $query = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $query->execute([$email]);
            if ($query->rowCount() > 0) {
                $_SESSION['error'] = "Email já está em uso";
            } else {
                $query = $pdo->prepare("INSERT INTO users (name, email, password, is_approved) VALUES (?, ?, ?, FALSE)");
                try {
                    $query->execute([$name, $email, $passwordHash]);
                    $_SESSION['success'] = "Cadastro enviado para aprovação. Aguarde o administrador liberar seu acesso.";
                } catch (PDOException $e) {
                    $_SESSION['error'] = "Erro ao cadastrar. Tente novamente.";
                    error_log("Registration error: " . $e->getMessage());
                }
            }
        }
    }
    header('Location: index.php');
    exit();
}
?>