<?php
session_start();
require 'includes/db.php';

// Verifica se o usuário está logado e é admin
if (!isset($_SESSION['username']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit();
}

// Mensagens de feedback
$message = '';
$messageType = '';

// Aprovar usuário
if (isset($_POST['approve'])) {
    $userId = $_POST['user_id'];
    $stmt = $pdo->prepare("UPDATE users SET is_approved = TRUE WHERE id = ?");
    if ($stmt->execute([$userId])) {
        $message = "Usuário aprovado com sucesso!";
        $messageType = "success";
    } else {
        $message = "Erro ao aprovar usuário.";
        $messageType = "danger";
    }
}

// Rejeitar usuário
if (isset($_POST['reject'])) {
    $userId = $_POST['user_id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$userId])) {
        $message = "Usuário rejeitado com sucesso!";
        $messageType = "success";
    } else {
        $message = "Erro ao rejeitar usuário.";
        $messageType = "danger";
    }
}

// Promover usuário a administrador
if (isset($_POST['make_admin'])) {
    $userId = $_POST['user_id'];
    $stmt = $pdo->prepare("UPDATE users SET is_admin = TRUE WHERE id = ?");
    if ($stmt->execute([$userId])) {
        $message = "Usuário promovido a administrador com sucesso!";
        $messageType = "success";
    } else {
        $message = "Erro ao promover usuário a administrador.";
        $messageType = "danger";
    }
}

// Remover privilégios de administrador
if (isset($_POST['remove_admin'])) {
    $userId = $_POST['user_id'];
    $stmt = $pdo->prepare("UPDATE users SET is_admin = FALSE WHERE id = ?");
    if ($stmt->execute([$userId])) {
        $message = "Privilégios de administrador removidos com sucesso!";
        $messageType = "success";
    } else {
        $message = "Erro ao remover privilégios de administrador.";
        $messageType = "danger";
    }
}

// deletar usuário
if (isset($_POST['delete_user'])) {
    $userId = $_POST['user_id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$userId])) {
        $message = "Usuário excluído com sucesso!";
        $messageType = "success";
    } else {
        $message = "Erro ao excluir usuário.";
        $messageType = "danger";
    }
}

// Adicionar membro da equipe
if (isset($_POST['add_team_member'])) {
    $memberName = trim($_POST['member_name']);
    $memberType = $_POST['member_type'];

    if (!empty($memberName)) {
        $stmt = $pdo->prepare("INSERT INTO team_members (name, type) VALUES (?, ?)");
        if ($stmt->execute([$memberName, $memberType])) {
            $message = "Membro da equipe adicionado com sucesso!";
            $messageType = "success";
        } else {
            $message = "Erro ao adicionar membro da equipe.";
            $messageType = "danger";
        }
    } else {
        $message = "Nome do membro não pode estar vazio.";
        $messageType = "warning";
    }
}

// Deletar membro da equipe
if (isset($_POST['delete_team_member'])) {
    $memberId = $_POST['member_id'];
    $stmt = $pdo->prepare("DELETE FROM team_members WHERE id = ?");
    if ($stmt->execute([$memberId])) {
        $message = "Membro da equipe excluído com sucesso!";
        $messageType = "success";
    } else {
        $message = "Erro ao excluir membro da equipe.";
        $messageType = "danger";
    }
}


// Filtro de visualização
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Buscar usuários com base no filtro
switch ($filter) {
    case 'pending':
        $stmt = $pdo->query("SELECT id, name, email, created_at, is_approved, is_admin FROM users WHERE is_approved = FALSE ORDER BY created_at DESC");
        break;
    case 'approved':
        $stmt = $pdo->query("SELECT id, name, email, created_at, is_approved, is_admin FROM users WHERE is_approved = TRUE ORDER BY created_at DESC");
        break;
    case 'admins':
        $stmt = $pdo->query("SELECT id, name, email, created_at, is_approved, is_admin FROM users WHERE is_admin = TRUE ORDER BY created_at DESC");
        break;
    default: // 'all'
        $stmt = $pdo->query("SELECT id, name, email, created_at, is_approved, is_admin FROM users ORDER BY created_at DESC");
        break;
}

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar todos os membros da equipe
$teamMembersStmt = $pdo->query("SELECT id, name, type, created_at FROM team_members ORDER BY type, name");
$teamMembers = $teamMembersStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="language" content="pt-BR">
    <title>Painel Administrativo - Gerador de UTM</title>
    <meta name="description" content="Gerador de UTMs da equipe Sales Prime">
    <meta name="robots" content="none">
    <meta name="author" content="Rugemtugem">
    <meta name="keywords" content="#geradordeutm #utm #salesprime">

    <meta property="og:type" content="page">
    <meta property="og:url" content="https://salesprime.com.br/utm/">
    <meta property="og:title" content="Gerador de UTM">
    <meta property="og:image" content="https://salesprime.com.br/wp-content/uploads/2024/09/thumbnail-sales.jpg">
    <meta property="og:description" content="Gerador de UTMs da equipe Sales Prime">

    <meta property="article:author" content="Rugemtugem">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@">
    <meta name="twitter:title" content="Gerador de UTM">
    <meta name="twitter:creator" content="@">
    <meta name="twitter:description" content="Gerador de UTMs da equipe Sales Prime">
    <link rel="icon" href="https://lp.salesprime.com.br/wp-content/uploads/2024/08/cropped-Favicon-32px-32x32.png.webp"
        sizes="32x32" />
    <link rel="icon"
        href="https://lp.salesprime.com.br/wp-content/uploads/2024/08/cropped-Favicon-32px-192x192.png.webp"
        sizes="192x192" />
    <link rel="apple-touch-icon"
        href="https://lp.salesprime.com.br/wp-content/uploads/2024/08/cropped-Favicon-32px-180x180.png.webp" />
    <meta name="msapplication-TileImage"
        content="https://lp.salesprime.com.br/wp-content/uploads/2024/08/cropped-Favicon-32px-270x270.png" />
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/componentes.css">
    <link rel="stylesheet" href="assets/css/utm-btn.css">
    <link rel="stylesheet" href="assets/css/theme-antigravity.css">
    <link rel="stylesheet" href="assets/css/tema.css">
    <link rel="stylesheet" href="assets/css/responsivo.css">
    <script src="assets/js/script.js?v1"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="theme-sales-prime">
    <div class="p-3 fixed-top mt-5" style="width: 10%;">
        <div id="themeSwitch" class="theme-switch-container light-active">
            <div class="theme-switch-bg"></div>
            <div class="theme-switch-option light-option active">
                <i class="bi bi-sun"></i>
                <span>Light</span>
            </div>
            <div class="theme-switch-option dark-option">
                <i class="bi bi-moon-stars-fill"></i>
                <span>Dark</span>
            </div>
        </div>
    </div>
    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col">
                <img src="assets/images/logo-light.png" alt="Logo" class="img-fluid d-none" id="logo-light"
                    style="max-width: 150px;">
                <img src="assets/images/logo-dark.png" alt="Logo" class="img-fluid d-block" id="logo-dark"
                    style="max-width: 150px;">
                <script>
                    // Troca a logo conforme o tema e salva no localStorage
                    function updateLogo() {
                        // Detecta tema salvo no localStorage ou pelo body
                        let theme = localStorage.getItem('theme');
                        if (!theme) {
                            theme = document.body.classList.contains('dark-theme') ||
                                document.querySelector('.theme-switch-container')?.classList.contains('dark-active')
                                ? 'dark' : 'light';
                        }
                        const isDark = theme === 'dark';
                        document.getElementById('logo-light').classList.toggle('d-none', isDark);
                        document.getElementById('logo-dark').classList.toggle('d-none', !isDark);
                    }
                    // Detecta troca de tema e salva no localStorage
                    document.addEventListener('DOMContentLoaded', updateLogo);
                    document.querySelectorAll('.theme-switch-option').forEach(opt => {
                        opt.addEventListener('click', function () {
                            setTimeout(() => {
                                // Detecta tema atual e salva
                                const isDark = document.body.classList.contains('dark-theme') ||
                                    document.querySelector('.theme-switch-container')?.classList.contains('dark-active');
                                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                                updateLogo();
                            }, 10);
                        });
                    });
                </script>
                <h2 class="mt-4">Painel Administrativo</h2>
            </div>
            <div class="col text-end">
                <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-90deg-left"></i> Voltar</a>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="mb-4">
            <div class="d-flex gap-2" role="group">
                <a href="?filter=all" class="ag-btn-outline <?= $filter === 'all' ? 'active' : '' ?>">
                    Todos os Usuários
                </a>
                <a href="?filter=pending" class="ag-btn-outline <?= $filter === 'pending' ? 'active' : '' ?>">
                    Pendentes de Aprovação
                </a>
                <a href="?filter=approved" class="ag-btn-outline <?= $filter === 'approved' ? 'active' : '' ?>">
                    Aprovados
                </a>
                <a href="?filter=admins" class="ag-btn-outline <?= $filter === 'admins' ? 'active' : '' ?>">
                    Administradores
                </a>
            </div>
        </div>

        <?php if (empty($users)): ?>
            <div class="alert alert-info">Não há usuários para exibir com o filtro selecionado.</div>
        <?php else: ?>
            <div class="ag-table-wrapper p-3">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Data de Cadastro</th>
                                <th>Status</th>
                                <th>Função</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <?php if ($user['is_approved']): ?>
                                            <span class="badge bg-success">Aprovado</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pendente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['is_admin']): ?>
                                            <span class="badge bg-danger">Administrador</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Usuário</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if (!$user['is_approved']): ?>
                                                <form method="POST" class="d-inline me-1">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <button type="submit" name="approve" class="btn btn-success btn-sm"
                                                        title="Aprovar">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" class="d-inline me-1">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <button type="submit" name="reject" class="btn btn-danger btn-sm"
                                                        title="Rejeitar"
                                                        onclick="return confirm('Tem certeza que deseja excluir este usuário?')">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if (!$user['is_admin']): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <button type="submit" name="make_admin" class="btn btn-primary btn-sm"
                                                        title="Promover a Administrador">
                                                        <i class="bi bi-shield-plus"></i>
                                                    </button>
                                                    <form method="POST" class="d-inline me-1">
                                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                        <button type="submit" name="delete_user" class="btn btn-danger btn-sm"
                                                            title="Deletar"
                                                            onclick="return confirm('Tem certeza que deseja excluir este usuário?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                        <button type="submit" name="remove_admin" class="btn btn-warning btn-sm"
                                                            title="Remover privilégios de Administrador">
                                                            <i class="bi bi-shield-minus"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Seção de Gerenciamento de Membros da Equipe -->
        <h2 class="mt-5">Gerenciar Membros da Equipe</h2>

        <!-- Formulário para adicionar novo membro -->
        <div class="ag-card my-4">
            <div class="card-header p-3">
                <h5>Adicionar Novo Membro</h5>
            </div>
            <div class="card-body px-3 pb-3">
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label for="member_name" class="form-label">Nome do Membro</label>
                        <input type="text" class="form-control" id="member_name" name="member_name" required>
                    </div>
                    <div class="col-md-4">
                        <label for="member_type" class="form-label">Tipo</label>
                        <select class="form-select" id="member_type" name="member_type" required>
                            <option value="Closer">Closer</option>
                            <option value="SDR">SDR</option>
                            <option value="SocialSeller">Social Seller</option>
                            <option value="CS">CS/Suporte</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" name="add_team_member" class="ag-btn-accent w-100">
                            <i class="bi bi-plus-circle"></i> Adicionar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de membros da equipe -->
        <div class="ag-table-wrapper p-3">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>Data de Cadastro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($teamMembers)): ?>
                            <tr>
                                <td colspan="4" class="text-center">Nenhum membro cadastrado.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($teamMembers as $member): ?>
                                <tr>
                                    <td><?= htmlspecialchars($member['name']) ?></td>
                                    <td>
                                        <?php
                                        $typeLabels = [
                                            'Closer' => 'Closer',
                                            'SDR' => 'SDR',
                                            'SocialSeller' => 'Social Seller',
                                            'CS' => 'CS/Suporte'
                                        ];
                                        $badgeColors = [
                                            'Closer' => 'bg-primary',
                                            'SDR' => 'bg-info',
                                            'SocialSeller' => 'bg-success',
                                            'CS' => 'bg-warning text-dark'
                                        ];
                                        ?>
                                        <span class="badge <?= $badgeColors[$member['type']] ?>">
                                            <?= $typeLabels[$member['type']] ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($member['created_at'])) ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
                                            <button type="submit" name="delete_team_member" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Tem certeza que deseja excluir este membro?')">
                                                <i class="bi bi-trash"></i> Excluir
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>