<?php
/**
 * Admin Panel - Gerenciamento Multi-Domínio
 * Versão: 2.5 - Dashboard de Domínios
 * Apenas admins podem acessar
 */

session_start();
require 'includes/db.php';

// Verificar autenticação e admin
if (!isset($_SESSION['username']) || !isset($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

// Verificar se é admin (já vem do SESSION após login)
if (!$_SESSION['is_admin']) {
    die("<h1>❌ Acesso Negado</h1><p>Apenas administradores podem acessar este painel.</p>");
}

// Buscar todos os domínios
$domainsStmt = $pdo->query("SELECT * FROM domains ORDER BY is_active DESC, name");
$domains = $domainsStmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar estatísticas por domínio
$statsStmt = $pdo->prepare("
    SELECT 
        COALESCE(u.domain, 'salesprime.com.br') as domain,
        COUNT(u.id) as total_urls,
        SUM(u.clicks) as total_clicks,
        COUNT(DISTINCT u.username) as unique_users
    FROM urls u
    GROUP BY u.domain
    ORDER BY total_urls DESC
");
$statsStmt->execute();
$domainStats = $statsStmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar permissões de usuários
$permissionsStmt = $pdo->query("
    SELECT 
        udp.*,
        d.name as domain_name,
        d.domain_url
    FROM user_domain_permissions udp
    JOIN domains d ON udp.domain_id = d.id
    ORDER BY udp.username, d.name
");
$allPermissions = $permissionsStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Gerenciamento de Domínios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/theme-antigravity.css">
    <link rel="stylesheet" href="assets/css/tema.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #3D4F73 0%, #0B1426 100%);
            color: white;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        .stat-card {
            border-left: 4px solid #0D6EFD;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(13, 110, 253, 0.15);
        }
        .domain-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
        }
        .badge-active {
            background-color: #28a745;
            color: white;
        }
        .badge-inactive {
            background-color: #dc3545;
            color: white;
        }
        .tab-content {
            background: white;
            border: 1px solid #dee2e6;
            border-top: none;
            padding: 2rem;
            border-radius: 0 0 8px 8px;
        }
        .nav-tabs .nav-link {
            border: 1px solid #dee2e6;
            border-bottom: none;
            border-radius: 8px 8px 0 0;
            color: #495057;
        }
        .nav-tabs .nav-link.active {
            background: #0D6EFD;
            color: white;
            border-color: #0D6EFD;
        }
    </style>
</head>
<body class="theme-sales-prime">
    <div class="container-fluid mt-4">
        <!-- Header -->
        <div class="admin-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="bi bi-gear me-2"></i>Painel de Domínios</h1>
                    <p class="mb-0">Gerenciamento de domínios e permissões de usuários</p>
                </div>
                <div>
                    <a href="index.php" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
                </div>
            </div>
        </div>

        <!-- Dashboard Stats -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="ag-card kpi-card kpi-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="kpi-label">Domínios Ativos</p>
                                <p class="kpi-value text-white m-0"><?= count(array_filter($domains, fn($d) => $d['is_active'])) ?></p>
                            </div>
                            <div class="kpi-icon"><i class="bi bi-globe"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="ag-card kpi-card kpi-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="kpi-label">Usuários Gerenciados</p>
                                <p class="kpi-value text-white m-0"><?= count(array_unique(array_column($allPermissions, 'username'))) ?></p>
                            </div>
                            <div class="kpi-icon"><i class="bi bi-people-fill"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Abas Principais -->
        <ul class="nav nav-tabs mb-0" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab-domains">
                    <i class="bi bi-globe me-1"></i> Domínios
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-stats">
                    <i class="bi bi-chart-bar me-1"></i> Estatísticas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-permissions">
                    <i class="bi bi-lock me-1"></i> Permissões
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- TAB 1: Domínios -->
            <div id="tab-domains" class="tab-pane fade show active">
                <h4 class="mb-3"><i class="bi bi-globe me-2"></i>Domínios Configurados</h4>
                <div class="ag-table-wrapper">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th>Nome</th>
                                <th>URL</th>
                                <th>Cor</th>
                                <th>Status</th>
                                <th>Criado em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($domains as $domain): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($domain['name']) ?></strong>
                                    </td>
                                    <td>
                                        <code><?= htmlspecialchars($domain['domain_url']) ?></code>
                                    </td>
                                    <td>
                                        <span style="display: inline-block; width: 24px; height: 24px; background-color: <?= htmlspecialchars($domain['brand_color']) ?>; border-radius: 4px; border: 1px solid #ddd;"></span>
                                        <code><?= htmlspecialchars($domain['brand_color']) ?></code>
                                    </td>
                                    <td>
                                        <span class="domain-badge <?= $domain['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                                            <?= $domain['is_active'] ? '✓ Ativo' : '✗ Inativo' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($domain['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm ag-btn-outline btn-edit-domain"
                                            data-id="<?= $domain['id'] ?>"
                                            data-name="<?= htmlspecialchars($domain['name'], ENT_QUOTES) ?>"
                                            data-url="<?= htmlspecialchars($domain['domain_url'], ENT_QUOTES) ?>"
                                            data-color="<?= htmlspecialchars($domain['brand_color'], ENT_QUOTES) ?>"
                                            data-logo-light="<?= htmlspecialchars($domain['logo_light'] ?? '', ENT_QUOTES) ?>"
                                            data-logo-dark="<?= htmlspecialchars($domain['logo_dark'] ?? '', ENT_QUOTES) ?>"
                                            data-active="<?= (int)$domain['is_active'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteDomain(<?= $domain['id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                </div>
                <button class="ag-btn-accent mt-3" data-bs-toggle="modal" data-bs-target="#newDomainModal">
                    <i class="bi bi-plus me-1"></i> Novo Domínio
                </button>
            </div>

            <!-- TAB 2: Estatísticas -->
            <div id="tab-stats" class="tab-pane fade">
                <h4 class="mb-3"><i class="bi bi-chart-bar me-2"></i>Desempenho por Domínio</h4>
                <div class="ag-table-wrapper">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th>Domínio</th>
                                <th>Total de URLs</th>
                                <th>Total de Cliques</th>
                                <th>Usuários</th>
                                <th>Cliques/URL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($domainStats as $stat): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($stat['domain']) ?></strong>
                                    </td>
                                    <td>
                                        <badge class="badge bg-primary"><?= $stat['total_urls'] ?></badge>
                                    </td>
                                    <td>
                                        <badge class="badge bg-success"><?= number_format($stat['total_clicks'], 0, '.', '.') ?></badge>
                                    </td>
                                    <td>
                                        <badge class="badge bg-info"><?= $stat['unique_users'] ?></badge>
                                    </td>
                                    <td>
                                        <?= $stat['total_urls'] > 0 ? round($stat['total_clicks'] / $stat['total_urls'], 2) : 0 ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                </div>
            </div>

            <!-- TAB 3: Permissões de Usuários -->
            <div id="tab-permissions" class="tab-pane fade">
                <h4 class="mb-3"><i class="bi bi-lock me-2"></i>Permissões de Usuários</h4>
                <div class="ag-table-wrapper">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead>
                            <tr>
                                <th>Usuário</th>
                                <th>Domínio</th>
                                <th>Criar</th>
                                <th>Editar</th>
                                <th>Deletar</th>
                                <th>Atribuído em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allPermissions as $perm): ?>
                                <tr>
                                    <td>
                                        <code><?= htmlspecialchars($perm['username']) ?></code>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($perm['domain_name']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge <?= $perm['can_create'] ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $perm['can_create'] ? '✓' : '✗' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $perm['can_edit'] ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $perm['can_edit'] ? '✓' : '✗' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $perm['can_delete'] ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $perm['can_delete'] ? '✓' : '✗' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($perm['assigned_at'])) ?></small>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm ag-btn-outline btn-edit-permission"
                                            data-id="<?= $perm['id'] ?>"
                                            data-username="<?= htmlspecialchars($perm['username'], ENT_QUOTES) ?>"
                                            data-domain="<?= htmlspecialchars($perm['domain_name'], ENT_QUOTES) ?>"
                                            data-can-create="<?= (int)$perm['can_create'] ?>"
                                            data-can-edit="<?= (int)$perm['can_edit'] ?>"
                                            data-can-delete="<?= (int)$perm['can_delete'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deletePermission(<?= $perm['id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                </div>
                <button class="ag-btn-accent mt-3" data-bs-toggle="modal" data-bs-target="#newPermissionModal">
                    <i class="bi bi-plus me-1"></i> Atribuir Permissão
                </button>
            </div>
        </div>
    </div>

    <!-- MODALS -->
    <!-- Modal: Novo Domínio -->
    <div class="modal fade" id="newDomainModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Novo Domínio</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="api/domains.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nome do Domínio</label>
                            <input type="text" name="name" class="form-control" placeholder="Ex: Sales Prime" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">URL do Domínio</label>
                            <input type="text" name="domain_url" class="form-control" placeholder="Ex: salesprime.com.br" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Logo Light</label>
                            <input type="text" name="logo_light" class="form-control" placeholder="Ex: images/logo_light.png">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Logo Dark</label>
                            <input type="text" name="logo_dark" class="form-control" placeholder="Ex: images/logo_dark.png">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cor da Marca</label>
                            <input type="color" name="brand_color" class="form-control" value="#0D6EFD" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="action" value="create_domain" class="btn btn-primary">Criar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Editar Domínio —— Antigravity -->
    <div class="modal fade" id="editDomainModal" tabindex="-1" aria-labelledby="editDomainModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content ag-card" style="border:1px solid var(--color-border);">
                <div class="modal-header border-0 pb-0" style="padding:1.5rem 1.5rem 0;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:40px;height:40px;border-radius:10px;background:var(--color-accent);display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-globe" style="color:#031A2B;font-size:1.1rem;"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0" id="editDomainModalLabel" style="font-family:var(--font-heading);font-weight:700;">Editar Domínio</h5>
                            <small class="ag-text-muted">Alterações salvas via AJAX</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body" style="padding:1.5rem;">
                    <input type="hidden" id="editDomainId">
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:.85rem;">Nome do Domínio</label>
                        <input type="text" id="editDomainName" class="ag-input form-control" placeholder="Ex: Sales Prime" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:.85rem;">URL do Domínio</label>
                        <input type="text" id="editDomainUrl" class="ag-input form-control" placeholder="Ex: salesprime.com.br" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-600" style="font-size:.85rem;">Logo Light</label>
                            <input type="text" id="editDomainLogoLight" class="ag-input form-control" placeholder="assets/images/logo_light.png">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-600" style="font-size:.85rem;">Logo Dark</label>
                            <input type="text" id="editDomainLogoDark" class="ag-input form-control" placeholder="assets/images/logo_dark.png">
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-600" style="font-size:.85rem;">Cor da Marca</label>
                            <input type="color" id="editDomainColor" class="form-control form-control-color w-100" style="height:42px;border-radius:10px;" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-600" style="font-size:.85rem;">Status</label>
                            <select id="editDomainStatus" class="ag-input form-select">
                                <option value="1">✓ Ativo</option>
                                <option value="0">✗ Inativo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0" style="padding:.75rem 1.5rem 1.5rem;gap:.75rem;">
                    <button type="button" class="ag-btn-outline" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="ag-btn-accent" id="saveDomainBtn">
                        <i class="bi bi-check-lg me-1"></i> Salvar Alterações
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Editar Permissão —— Antigravity -->
    <div class="modal fade" id="editPermissionModal" tabindex="-1" aria-labelledby="editPermissionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content ag-card" style="border:1px solid var(--color-border);">
                <div class="modal-header border-0 pb-0" style="padding:1.5rem 1.5rem 0;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:40px;height:40px;border-radius:10px;background:var(--color-accent);display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-shield-lock" style="color:#031A2B;font-size:1.1rem;"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0" id="editPermissionModalLabel" style="font-family:var(--font-heading);font-weight:700;">Editar Permissão</h5>
                            <small class="ag-text-muted" id="editPermLabel">Usuário / Domínio</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body" style="padding:1.5rem;">
                    <input type="hidden" id="editPermissionId">
                    <div class="ag-card p-3 mb-4" style="background:var(--color-bg-main);border-radius:10px;">
                        <div class="d-flex gap-3">
                            <span style="opacity:.6;font-size:.8rem;">USUÁRIO</span>
                            <strong id="editPermUser" style="font-size:.9rem;"></strong>
                        </div>
                        <div class="d-flex gap-3 mt-1">
                            <span style="opacity:.6;font-size:.8rem;">DOMÍNIO</span>
                            <strong id="editPermDomain" style="font-size:.9rem;"></strong>
                        </div>
                    </div>
                    <p class="ag-text-muted mb-3" style="font-size:.85rem;">Selecione as permissões que este usuário terá neste domínio:</p>
                    <div class="d-flex flex-column gap-3">
                        <label class="d-flex align-items-center gap-3 p-3 ag-perm-toggle" style="border-radius:10px;border:1.5px solid var(--color-border);cursor:pointer;transition:var(--ag-transition);">
                            <input type="checkbox" id="editPermCreate" class="form-check-input m-0" style="width:20px;height:20px;">
                            <div>
                                <div class="fw-bold" style="font-size:.9rem;"><i class="bi bi-plus-circle me-1"></i> Criar URLs</div>
                                <small class="ag-text-muted">Pode gerar novas UTMs neste domínio</small>
                            </div>
                        </label>
                        <label class="d-flex align-items-center gap-3 p-3 ag-perm-toggle" style="border-radius:10px;border:1.5px solid var(--color-border);cursor:pointer;transition:var(--ag-transition);">
                            <input type="checkbox" id="editPermEdit" class="form-check-input m-0" style="width:20px;height:20px;">
                            <div>
                                <div class="fw-bold" style="font-size:.9rem;"><i class="bi bi-pencil-square me-1"></i> Editar URLs</div>
                                <small class="ag-text-muted">Pode modificar UTMs existentes</small>
                            </div>
                        </label>
                        <label class="d-flex align-items-center gap-3 p-3 ag-perm-toggle" style="border-radius:10px;border:1.5px solid var(--color-border);cursor:pointer;transition:var(--ag-transition);">
                            <input type="checkbox" id="editPermDelete" class="form-check-input m-0" style="width:20px;height:20px;">
                            <div>
                                <div class="fw-bold" style="font-size:.9rem;"><i class="bi bi-trash me-1"></i> Deletar URLs</div>
                                <small class="ag-text-muted">Pode excluir UTMs permanentemente</small>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0" style="padding:.75rem 1.5rem 1.5rem;gap:.75rem;">
                    <button type="button" class="ag-btn-outline" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="ag-btn-accent" id="savePermissionBtn">
                        <i class="bi bi-check-lg me-1"></i> Salvar Permissões
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Nova Permissão -->
    <div class="modal fade" id="newPermissionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Atribuir Permissão</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="api/permissions.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Usuário (Email)</label>
                            <input type="email" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Domínio</label>
                            <select name="domain_id" class="form-select" required>
                                <option value="">Selecione um domínio</option>
                                <?php foreach ($domains as $domain): ?>
                                    <option value="<?= $domain['id'] ?>"><?= htmlspecialchars($domain['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-check-label">
                                <input type="checkbox" name="can_create" value="1" class="form-check-input" checked> Criar URLs
                            </label>
                        </div>
                        <div class="mb-3">
                            <label class="form-check-label">
                                <input type="checkbox" name="can_edit" value="1" class="form-check-input"> Editar URLs
                            </label>
                        </div>
                        <div class="mb-3">
                            <label class="form-check-label">
                                <input type="checkbox" name="can_delete" value="1" class="form-check-input"> Deletar URLs
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="action" value="create_permission" class="btn btn-primary">Atribuir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        /* Antigravity Modal overrides */
        .ag-input { background: var(--color-bg-card) !important; border-color: var(--color-border) !important; color: var(--color-text-main) !important; border-radius: 10px !important; }
        .ag-input:focus { border-color: var(--color-accent) !important; box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-accent) 20%, transparent) !important; }
        .ag-perm-toggle:has(input:checked) { border-color: var(--color-accent) !important; background: color-mix(in srgb, var(--color-accent) 8%, transparent); }
        .ag-perm-toggle input:checked { accent-color: var(--color-accent); }
        .modal-content.ag-card { background: var(--color-bg-card) !important; color: var(--color-text-main) !important; }
        .modal .fw-bold { color: var(--color-text-main); }
    </style>

    <script>
        // =====================================================
        // EDITAR DOMÍNIO
        // Dados lidos dos data-* attrs da linha — sem AJAX
        // =====================================================
        document.addEventListener('DOMContentLoaded', () => {

            // Delegação: clique em qualquer .btn-edit-domain
            document.querySelectorAll('.btn-edit-domain').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.getElementById('editDomainId').value         = this.dataset.id;
                    document.getElementById('editDomainName').value        = this.dataset.name;
                    document.getElementById('editDomainUrl').value         = this.dataset.url;
                    document.getElementById('editDomainColor').value       = this.dataset.color;
                    document.getElementById('editDomainLogoLight').value   = this.dataset.logoLight || '';
                    document.getElementById('editDomainLogoDark').value    = this.dataset.logoDark  || '';
                    document.getElementById('editDomainStatus').value      = this.dataset.active;

                    new bootstrap.Modal(document.getElementById('editDomainModal')).show();
                });
            });

            // Salvar domínio via AJAX
            document.getElementById('saveDomainBtn').addEventListener('click', function () {
                const id     = document.getElementById('editDomainId').value;
                const name   = document.getElementById('editDomainName').value.trim();
                const url    = document.getElementById('editDomainUrl').value.trim();
                const color  = document.getElementById('editDomainColor').value;
                const logoL  = document.getElementById('editDomainLogoLight').value.trim();
                const logoD  = document.getElementById('editDomainLogoDark').value.trim();
                const status = document.getElementById('editDomainStatus').value;

                if (!name || !url) return agAlert('Preencha nome e URL.', 'warning');

                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Salvando…';

                fetch('api/domains.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=update_domain&domain_id=${encodeURIComponent(id)}&name=${encodeURIComponent(name)}&domain_url=${encodeURIComponent(url)}&brand_color=${encodeURIComponent(color)}&is_active=${status}&logo_light=${encodeURIComponent(logoL)}&logo_dark=${encodeURIComponent(logoD)}`
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        bootstrap.Modal.getInstance(document.getElementById('editDomainModal'))?.hide();
                        agAlert('Domínio atualizado com sucesso!', 'success', () => location.reload());
                    } else {
                        agAlert('Erro: ' + (d.error || 'Desconhecido'), 'danger');
                    }
                })
                .catch(() => agAlert('Falha na comunicação com o servidor.', 'danger'))
                .finally(() => {
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-check-lg me-1"></i> Salvar Alterações';
                });
            });

            // =====================================================
            // EDITAR PERMISSÃO
            // =====================================================
            document.querySelectorAll('.btn-edit-permission').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.getElementById('editPermissionId').value   = this.dataset.id;
                    document.getElementById('editPermUser').textContent    = this.dataset.username;
                    document.getElementById('editPermDomain').textContent  = this.dataset.domain;
                    document.getElementById('editPermLabel').textContent   = `${this.dataset.username} · ${this.dataset.domain}`;
                    document.getElementById('editPermCreate').checked  = this.dataset.canCreate === '1';
                    document.getElementById('editPermEdit').checked    = this.dataset.canEdit   === '1';
                    document.getElementById('editPermDelete').checked  = this.dataset.canDelete === '1';

                    new bootstrap.Modal(document.getElementById('editPermissionModal')).show();
                });
            });

            // Salvar permissão via AJAX
            document.getElementById('savePermissionBtn').addEventListener('click', function () {
                const id        = document.getElementById('editPermissionId').value;
                const canCreate = document.getElementById('editPermCreate').checked ? 1 : 0;
                const canEdit   = document.getElementById('editPermEdit').checked   ? 1 : 0;
                const canDelete = document.getElementById('editPermDelete').checked ? 1 : 0;

                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Salvando…';

                fetch('api/permissions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=update_permission&permission_id=${id}&can_create=${canCreate}&can_edit=${canEdit}&can_delete=${canDelete}`
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        bootstrap.Modal.getInstance(document.getElementById('editPermissionModal'))?.hide();
                        agAlert('Permissões atualizadas!', 'success', () => location.reload());
                    } else {
                        agAlert('Erro: ' + (d.error || 'Desconhecido'), 'danger');
                    }
                })
                .catch(() => agAlert('Falha na comunicação com o servidor.', 'danger'))
                .finally(() => {
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-check-lg me-1"></i> Salvar Permissões';
                });
            });
        });

        // =====================================================
        // DELETE DOMÍNIO
        // =====================================================
        function deleteDomain(id) {
            agConfirm(
                'Tem certeza que deseja deletar este domínio?',
                'Esta ação é irreversível. Domínios com URLs associadas não podem ser removidos.',
                () => {
                    fetch('api/domains.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=delete_domain&domain_id=${id}`
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) location.reload();
                        else agAlert('Erro: ' + (d.error || 'Desconhecido'), 'danger');
                    })
                    .catch(() => agAlert('Falha na comunicação com o servidor.', 'danger'));
                }
            );
        }

        // DELETE PERMISSÃO
        function deletePermission(id) {
            agConfirm(
                'Remover esta permissão?',
                'O usuário perderá acesso ao domínio vinculado.',
                () => {
                    fetch('api/permissions.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=delete_permission&permission_id=${id}`
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) location.reload();
                        else agAlert('Erro: ' + (d.error || 'Desconhecido'), 'danger');
                    })
                    .catch(() => agAlert('Falha na comunicação com o servidor.', 'danger'));
                }
            );
        }

        // =====================================================
        // HELPERS de TOAST / CONFIRM — Antigravity Style
        // =====================================================
        function agAlert(msg, type = 'success', onClose = null) {
            const colors = { success: 'var(--color-accent)', danger: '#dc3545', warning: '#f0ad4e', info: '#0dcaf0' };
            const icons  = { success: 'bi-check-circle-fill', danger: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
            const toast  = document.createElement('div');
            toast.style.cssText = `position:fixed;top:1.5rem;right:1.5rem;z-index:9999;display:flex;align-items:center;gap:.75rem;padding:1rem 1.5rem;border-radius:14px;background:var(--color-bg-card);border:1.5px solid ${colors[type] || colors.success};box-shadow:0 8px 24px rgba(0,0,0,.2);font-family:var(--font-body);font-size:.9rem;color:var(--color-text-main);min-width:260px;max-width:360px;animation:agSlideIn .3s ease;`;
            toast.innerHTML = `<i class="bi ${icons[type] || icons.success}" style="font-size:1.3rem;color:${colors[type]}"></i><span>${msg}</span>`;
            document.head.insertAdjacentHTML('beforeend', '<style>@keyframes agSlideIn{from{opacity:0;transform:translateX(40px)}to{opacity:1;transform:none}}</style>');
            document.body.appendChild(toast);
            setTimeout(() => { toast.remove(); if (onClose) onClose(); }, 2200);
        }

        function agConfirm(title, body, onConfirm) {
            const id   = 'agConfirmModal_' + Date.now();
            const html = `
            <div class="modal fade" id="${id}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content ag-card" style="border:1px solid var(--color-border);">
                        <div class="modal-body text-center" style="padding:1.75rem 1.5rem;">
                            <i class="bi bi-exclamation-triangle-fill" style="font-size:2rem;color:#f0ad4e;display:block;margin-bottom:.75rem;"></i>
                            <h6 style="font-family:var(--font-heading);font-weight:700;">${title}</h6>
                            <p class="ag-text-muted mb-0" style="font-size:.83rem;">${body}</p>
                        </div>
                        <div class="modal-footer border-0 justify-content-center" style="padding:.5rem 1.5rem 1.5rem;gap:.75rem;">
                            <button class="ag-btn-outline" data-bs-dismiss="modal">Cancelar</button>
                            <button class="ag-btn-accent" id="agCfmOk"><i class="bi bi-trash me-1"></i>Confirmar</button>
                        </div>
                    </div>
                </div>
            </div>`;
            document.body.insertAdjacentHTML('beforeend', html);
            const el  = document.getElementById(id);
            const mod = new bootstrap.Modal(el);
            mod.show();
            el.querySelector('#agCfmOk').addEventListener('click', () => {
                mod.hide();
                el.addEventListener('hidden.bs.modal', () => { el.remove(); onConfirm(); }, { once: true });
            });
            el.addEventListener('hidden.bs.modal', () => el.remove(), { once: true });
        }
    </script>
</body>
</html>
