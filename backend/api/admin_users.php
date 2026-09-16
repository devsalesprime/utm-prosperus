<?php
/**
 * API: Gerenciar usuários (admin only)
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require dirname(__DIR__) . '/includes/master.php';

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? $_GET['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            $stmt = $pdo->query(
                "SELECT id, name, email, is_admin, is_approved, created_at
                 FROM users ORDER BY is_approved ASC, created_at DESC"
            );
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'approve':
            $userId = intval($input['user_id'] ?? 0);
            if (!$userId) throw new Exception('ID inválido');
            $pdo->prepare("UPDATE users SET is_approved = 1 WHERE id = ?")->execute([$userId]);
            echo json_encode(['success' => true]);
            break;

        case 'toggle_admin':
            $userId  = intval($input['user_id'] ?? 0);
            $isAdmin = intval($input['is_admin'] ?? 0);
            if (!$userId) throw new Exception('ID inválido');
            $pdo->prepare("UPDATE users SET is_admin = ? WHERE id = ?")->execute([$isAdmin, $userId]);
            echo json_encode(['success' => true]);
            break;

        case 'delete':
            // Irreversivel: exige a senha master, como a exclusao de UTM.
            $userId   = intval($input['user_id'] ?? 0);
            $password = (string) ($input['password'] ?? '');
            if (!$userId) throw new Exception('ID inválido');
            if (!master_password_ok($pdo, $password)) {
                throw new Exception('Senha master incorreta');
            }
            if ($userId === intval($_SESSION['user_id'])) {
                throw new Exception('Você não pode excluir o próprio usuário');
            }
            $alvo = $pdo->prepare("SELECT id, is_admin FROM users WHERE id = ?");
            $alvo->execute([$userId]);
            $alvo = $alvo->fetch(PDO::FETCH_ASSOC);
            if (!$alvo) throw new Exception('Usuário não encontrado');
            if ($alvo['is_admin']) {
                $admins = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 1")->fetchColumn();
                if ($admins <= 1) throw new Exception('Não é possível excluir o último administrador');
            }
            // As UTMs criadas por essa pessoa ficam: pertencem ao time, nao a conta.
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
            echo json_encode(['success' => true]);
            break;

        case 'master_status':
            echo json_encode(['success' => true] + master_status($pdo), JSON_UNESCAPED_UNICODE);
            break;

        case 'master_set':
            // Quem troca nao precisa saber a master atual (e o caso de uso:
            // ela se perdeu). Precisa provar que e o proprio admin logado.
            $minha   = (string) ($input['admin_password'] ?? '');
            $nova    = (string) ($input['new_password'] ?? '');
            $confirm = (string) ($input['confirm_password'] ?? '');
            if ($minha === '' || $nova === '' || $confirm === '') throw new Exception('Preencha os três campos.');
            if (strlen($nova) < 8) throw new Exception('A nova senha master deve ter pelo menos 8 caracteres.');
            if ($nova !== $confirm) throw new Exception('A confirmação não coincide com a nova senha master.');
            $st = $pdo->prepare("SELECT name, password FROM users WHERE id = ? AND is_admin = 1");
            $st->execute([(int) $_SESSION['user_id']]);
            $eu = $st->fetch(PDO::FETCH_ASSOC);
            if (!$eu || !password_verify($minha, $eu['password'])) {
                http_response_code(403);
                echo json_encode(['error' => 'Sua senha de administrador está incorreta.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            master_set($pdo, $nova, $eu['name']);
            echo json_encode(['success' => true] + master_status($pdo), JSON_UNESCAPED_UNICODE);
            break;

        default:
            throw new Exception('Ação inválida');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
