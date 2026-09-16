<?php
/**
 * Perfil do usuario logado: dados da conta, troca de nome e de senha,
 * resumo da propria atividade. Tudo restrito a propria sessao.
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

require dirname(__DIR__) . '/includes/db.php';

$input = [];
if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
} else {
    $input = $_POST;
}
$action = $input['action'] ?? $_GET['action'] ?? 'get';
$meuId  = (int) $_SESSION['user_id'];

try {
    switch ($action) {
        case 'get':
            $stmt = $pdo->prepare("SELECT id, name, email, is_admin, is_approved, created_at FROM users WHERE id = ?");
            $stmt->execute([$meuId]);
            $u = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$u) throw new Exception('Usuário não encontrado');

            // A UTM guarda o autor pelo nome exibido (urls.username e texto).
            $st = $pdo->prepare("
                SELECT COUNT(*) AS total_utms,
                       COALESCE(SUM(clicks), 0) AS total_clicks,
                       MAX(generation_date) AS ultima_utm,
                       SUM(CASE WHEN is_enabled = 1 THEN 1 ELSE 0 END) AS ativas
                FROM urls WHERE username = ?");
            $st->execute([$u['name']]);
            $a = $st->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'user' => [
                    'id'          => (int) $u['id'],
                    'name'        => $u['name'],
                    'email'       => $u['email'],
                    'is_admin'    => (bool) $u['is_admin'],
                    'is_approved' => (bool) $u['is_approved'],
                    'created_at'  => $u['created_at'],
                ],
                'activity' => [
                    'total_utms'   => (int) $a['total_utms'],
                    'total_clicks' => (int) $a['total_clicks'],
                    'ativas'       => (int) $a['ativas'],
                    'ultima_utm'   => $a['ultima_utm'],
                ],
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'update_name':
            $name = trim((string) ($input['name'] ?? ''));
            $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
            if (mb_strlen($name) < 3 || mb_strlen($name) > 80) {
                throw new Exception('O nome precisa ter entre 3 e 80 caracteres.');
            }
            $cur = $pdo->prepare("SELECT name FROM users WHERE id = ?");
            $cur->execute([$meuId]);
            $antigo = $cur->fetchColumn();

            // O autor da UTM e o nome exibido; renomear sem atualizar as UTMs
            // faria o historico da pessoa sumir do proprio perfil e do painel.
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE users SET name = ? WHERE id = ?")->execute([$name, $meuId]);
            if ($antigo !== null && $antigo !== $name) {
                $pdo->prepare("UPDATE urls SET username = ? WHERE username = ?")->execute([$name, $antigo]);
            }
            $pdo->commit();
            $_SESSION['username'] = $name;
            echo json_encode(['success' => true, 'name' => $name], JSON_UNESCAPED_UNICODE);
            break;

        case 'change_password':
            $atual   = (string) ($input['current_password'] ?? '');
            $nova    = (string) ($input['new_password'] ?? '');
            $confirm = (string) ($input['confirm_password'] ?? '');
            if ($atual === '' || $nova === '' || $confirm === '') throw new Exception('Preencha os três campos.');
            if (strlen($nova) < 8) throw new Exception('A nova senha deve ter pelo menos 8 caracteres.');
            if ($nova !== $confirm) throw new Exception('A confirmação não coincide com a nova senha.');
            if ($nova === $atual) throw new Exception('A nova senha precisa ser diferente da atual.');

            $st = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $st->execute([$meuId]);
            $hashAtual = $st->fetchColumn();
            if (!$hashAtual || !password_verify($atual, $hashAtual)) {
                http_response_code(403);
                echo json_encode(['error' => 'Senha atual incorreta.']);
                exit;
            }
            $hash = password_hash($nova, PASSWORD_BCRYPT, ['cost' => 12]); // mesmo custo de auth.php
            $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $meuId]);
            session_regenerate_id(true);
            echo json_encode(['success' => true]);
            break;

        default:
            throw new Exception('Ação inválida');
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
