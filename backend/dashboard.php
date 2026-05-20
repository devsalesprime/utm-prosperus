<?php
/**
 * Dashboard Analytics - UTM Generator
 * Version: 2.2 - Chart.js visualizations
 */
session_start();
require 'includes/db.php';

// Auth check
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Analytics | UTM Generator</title>
    <meta name="robots" content="none">

    <link rel="icon" href="https://lp.salesprime.com.br/wp-content/uploads/2024/08/cropped-Favicon-32px-32x32.png.webp"
        sizes="32x32">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- App CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/theme-antigravity.css">
    <link rel="stylesheet" href="assets/css/tema.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>

<body class="theme-sales-prime">
    <!-- Theme Switch -->
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

    <div class="container mt-4">
        <!-- Header -->
        <div class="dashboard-header d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h1><i class="bi bi-graph-up-arrow me-2"></i>Analytics Dashboard</h1>
                <p class="text-muted mb-0">Métricas de performance das UTMs</p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <a href="index.php" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Voltar
                </a>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mb-4" id="dashboardTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="analytics-tab" data-bs-toggle="tab" data-bs-target="#analytics" type="button" role="tab" aria-controls="analytics" aria-selected="true">
                    <i class="bi bi-graph-up me-2"></i>Analytics
                </button>
            </li>
            <?php if ($_SESSION['is_admin'] ?? false): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="domains-tab" data-bs-toggle="tab" data-bs-target="#domains" type="button" role="tab" aria-controls="domains" aria-selected="false">
                    <i class="bi bi-globe me-2"></i>Domínios
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="stats-tab" data-bs-toggle="tab" data-bs-target="#stats" type="button" role="tab" aria-controls="stats" aria-selected="false">
                    <i class="bi bi-bar-chart me-2"></i>Stats por Domínio
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="permissions-tab" data-bs-toggle="tab" data-bs-target="#permissions" type="button" role="tab" aria-controls="permissions" aria-selected="false">
                    <i class="bi bi-shield-lock me-2"></i>Permissões
                </button>
            </li>
            <?php endif; ?>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="dashboardTabContent">
        <!-- Analytics Tab -->
        <div class="tab-pane fade show active" id="analytics" role="tabpanel" aria-labelledby="analytics-tab">

        <!-- KPI Cards -->
        <div class="row g-3 mb-4" id="kpiCards">
            <div class="col-6 col-md-3">
                <div class="ag-card kpi-card kpi-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="kpi-label">Total UTMs</p>
                                <p class="kpi-value" id="kpiTotalUtms">--</p>
                            </div>
                            <div class="kpi-icon"><i class="bi bi-link-45deg"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ag-card kpi-card kpi-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="kpi-label">Total Cliques</p>
                                <p class="kpi-value" id="kpiTotalClicks">--</p>
                            </div>
                            <div class="kpi-icon"><i class="bi bi-cursor-fill"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ag-card kpi-card kpi-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="kpi-label">Média/UTM</p>
                                <p class="kpi-value" id="kpiAvgClicks">--</p>
                            </div>
                            <div class="kpi-icon"><i class="bi bi-bar-chart-fill"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ag-card kpi-card kpi-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="kpi-label">UTMs Ativas</p>
                                <p class="kpi-value" id="kpiActiveUtms">--</p>
                            </div>
                            <div class="kpi-icon"><i class="bi bi-check-circle-fill"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-3 mb-4">
            <!-- Doughnut Chart -->
            <div class="col-md-5">
                <div class="chart-container h-100">
                    <h5><i class="bi bi-pie-chart-fill text-primary"></i> Cliques por Fonte</h5>
                    <div style="position: relative; max-height: 320px;">
                        <canvas id="sourceChart" role="img"
                            aria-label="Gráfico de distribuição de cliques por fonte"></canvas>
                    </div>
                </div>
            </div>
            <!-- Line Chart -->
            <div class="col-md-7">
                <div class="chart-container h-100">
                    <h5><i class="bi bi-activity text-success"></i> Tendência de Cliques</h5>
                    <div style="position: relative; max-height: 320px;">
                        <canvas id="trendChart" role="img"
                            aria-label="Gráfico de tendência de cliques ao longo do tempo"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Row: Top UTMs + Users -->
        <div class="row g-3 mb-4">
            <!-- Top 10 UTMs -->
            <div class="col-md-8">
                <div class="chart-container">
                    <h5><i class="bi bi-trophy-fill text-warning"></i> Top 10 UTMs</h5>
                    <div class="table-responsive">
                        <table class="table table-sm top-table mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Short Code</th>
                                    <th>Descrição</th>
                                    <th>Criador</th>
                                    <th class="text-end">Cliques</th>
                                </tr>
                            </thead>
                            <tbody id="topUtmsTable">
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Carregando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- By User -->
            <div class="col-md-4">
                <div class="chart-container h-100">
                    <h5><i class="bi bi-people-fill text-info"></i> Por Usuário</h5>
                    <div class="table-responsive">
                        <table class="table table-sm top-table mb-0">
                            <thead>
                                <tr>
                                    <th>Usuário</th>
                                    <th class="text-center">UTMs</th>
                                    <th class="text-end">Cliques</th>
                                </tr>
                            </thead>
                            <tbody id="byUserTable">
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Carregando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        </div><!-- End Analytics Tab -->

        <!-- Domains Tab (Admin Only) -->
        <?php if ($_SESSION['is_admin'] ?? false): ?>
        <div class="tab-pane fade" id="domains" role="tabpanel" aria-labelledby="domains-tab">
            <div class="row mb-4">
                <div class="col-12">
                    <button class="ag-btn-accent" id="btnNovoDominio">
                        <i class="bi bi-plus-circle me-2"></i>Novo Domínio
                    </button>
                </div>
            </div>
            <div class="ag-table-wrapper">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Domain URL</th>
                                <th>Logo</th>
                                <th>Cor</th>
                                <th>Status</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="domainsTableBody">
                            <tr><td colspan="6" class="text-center text-muted">Carregando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!-- End Domains Tab -->

        <!-- Stats por Domínio Tab (Admin Only) -->
        <div class="tab-pane fade" id="stats" role="tabpanel" aria-labelledby="stats-tab">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="ag-card kpi-card kpi-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="kpi-label">Total de Domínios</p>
                                    <p class="kpi-value" id="statsTotalDomains">--</p>
                                </div>
                                <div class="kpi-icon"><i class="bi bi-globe"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="ag-card kpi-card kpi-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="kpi-label">Domínios Ativos</p>
                                    <p class="kpi-value" id="statsActiveDomains">--</p>
                                </div>
                                <div class="kpi-icon"><i class="bi bi-check-circle-fill"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="ag-card kpi-card kpi-danger">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="kpi-label">Domínios Inativos</p>
                                    <p class="kpi-value" id="statsInactiveDomains">--</p>
                                </div>
                                <div class="kpi-icon"><i class="bi bi-x-circle-fill"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ag-table-wrapper">
                <div class="card-header">
                    <h5 class="mb-0">Estatísticas por Domínio</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Domínio</th>
                                <th>URLs Totais</th>
                                <th>Cliques</th>
                                <th>Usuários</th>
                                <th>Cliques/UTM</th>
                            </tr>
                        </thead>
                        <tbody id="statsTableBody">
                            <tr><td colspan="5" class="text-center text-muted">Carregando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!-- End Stats Tab -->

        <!-- Permissions Tab (Admin Only) -->
        <div class="tab-pane fade" id="permissions" role="tabpanel" aria-labelledby="permissions-tab">
            <div class="row mb-4">
                <div class="col-12">
                    <button class="ag-btn-accent" id="btnNovaPermissao">
                        <i class="bi bi-plus-circle me-2"></i>Nova Permissão
                    </button>
                </div>
            </div>
            <div class="ag-table-wrapper">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Usuário</th>
                                <th>Domínio</th>
                                <th>Create</th>
                                <th>Edit</th>
                                <th>Delete</th>
                                <th>Atribuído por</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="permissionsTableBody">
                            <tr><td colspan="7" class="text-center text-muted">Carregando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!-- End Permissions Tab -->

        <?php endif; ?>

        <!-- Footer -->
        <div class="text-center text-muted py-3">
            <small>UTM Generator Analytics v2.2 &bull; Dados atualizados em tempo real</small>
        </div>
        </div><!-- End tab-content -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js?v1"></script>

    <script>
        (function () {
            'use strict';

            // ===== Chart.js Defaults =====
            const COLORS = [
                '#3D4F73', '#F2A011', '#059669', '#3B82F6', '#EF4444',
                '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#6366F1',
                '#84CC16', '#06B6D4', '#D946EF', '#A3A3A3'
            ];

            let sourceChart = null;
            let trendChart = null;
            let currentPeriod = 30;

            // ===== Number Formatting =====
            function formatNumber(n) {
                if (n >= 1000000) return (n / 1000000).toFixed(1) + 'M';
                if (n >= 1000) return (n / 1000).toFixed(1) + 'K';
                return String(n);
            }

            // ===== Fetch Data =====
            async function loadDashboard(period) {
                try {
                    const res = await fetch(`api/dashboard_data.php?period=${period}`);
                    if (!res.ok) throw new Error('API error');
                    const data = await res.json();
                    renderKPIs(data.kpis);
                    renderSourceChart(data.clicks_by_source);
                    renderTrendChart(data.clicks_trend);
                    renderTopUtms(data.top_utms);
                    renderByUser(data.by_user);
                } catch (err) {
                    console.error('Dashboard load error:', err);
                }
            }

            // ===== KPIs =====
            function renderKPIs(kpis) {
                document.getElementById('kpiTotalUtms').textContent = formatNumber(kpis.total_utms);
                document.getElementById('kpiTotalClicks').textContent = formatNumber(kpis.total_clicks);
                document.getElementById('kpiAvgClicks').textContent = formatNumber(Math.round(kpis.avg_clicks));
                document.getElementById('kpiActiveUtms').textContent = formatNumber(kpis.active_utms);
            }

            // ===== Source Doughnut Chart =====
            function renderSourceChart(data) {
                const ctx = document.getElementById('sourceChart').getContext('2d');
                if (sourceChart) sourceChart.destroy();

                sourceChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: data.map(d => d.source_name),
                        datasets: [{
                            data: data.map(d => parseInt(d.total_clicks)),
                            backgroundColor: COLORS.slice(0, data.length),
                            borderWidth: 2,
                            borderColor: getComputedStyle(document.body).getPropertyValue('--color-bg-card').trim() || '#fff',
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        cutout: '55%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 12,
                                    usePointStyle: true,
                                    pointStyleWidth: 10,
                                    font: { size: 11 },
                                    color: getComputedStyle(document.body).getPropertyValue('--color-text-main').trim() || '#212529'
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => {
                                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                        const pct = ((ctx.raw / total) * 100).toFixed(1);
                                        return ` ${ctx.label}: ${ctx.raw.toLocaleString('pt-BR')} (${pct}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // ===== Trend Line Chart =====
            function renderTrendChart(data) {
                const ctx = document.getElementById('trendChart').getContext('2d');
                if (trendChart) trendChart.destroy();

                const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, 'rgba(61, 79, 115, 0.3)');
                gradient.addColorStop(1, 'rgba(61, 79, 115, 0.02)');

                trendChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.map(d => {
                            const dt = new Date(d.click_date + 'T00:00:00');
                            return dt.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' });
                        }),
                        datasets: [{
                            label: 'Cliques',
                            data: data.map(d => parseInt(d.click_count)),
                            borderColor: getComputedStyle(document.body).getPropertyValue('--color-secondary').trim() || '#3D4F73',
                            backgroundColor: gradient,
                            fill: true,
                            tension: 0.4,
                            pointRadius: data.length > 60 ? 0 : 3,
                            pointHoverRadius: 6,
                            pointBackgroundColor: getComputedStyle(document.body).getPropertyValue('--color-secondary').trim() || '#3D4F73',
                            borderWidth: 2.5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        interaction: { intersect: false, mode: 'index' },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: {
                                    maxTicksLimit: 10,
                                    font: { size: 10 },
                                    color: getComputedStyle(document.body).getPropertyValue('--color-text-muted').trim() || '#9CA3AF'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.05)' },
                                ticks: {
                                    font: { size: 10 },
                                    color: getComputedStyle(document.body).getPropertyValue('--color-text-muted').trim() || '#9CA3AF'
                                }
                            }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => ` ${ctx.raw.toLocaleString('pt-BR')} cliques`
                                }
                            }
                        }
                    }
                });
            }

            // ===== Top UTMs Table =====
            function renderTopUtms(data) {
                const tbody = document.getElementById('topUtmsTable');
                if (!data.length) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Nenhuma UTM com cliques</td></tr>';
                    return;
                }
                tbody.innerHTML = data.map((utm, i) => {
                    const rankClass = i < 3 ? `rank-${i + 1}` : 'rank-default';
                    const comment = utm.comment
                        ? (utm.comment.length > 50 ? utm.comment.substring(0, 50) + '…' : utm.comment)
                        : '<span class="text-muted">—</span>';
                    return `<tr>
                    <td><span class="rank-badge ${rankClass}">${i + 1}</span></td>
                    <td><code>${escapeHtml(utm.shortened_url)}</code></td>
                    <td>${comment}</td>
                    <td>${escapeHtml(utm.username)}</td>
                    <td class="text-end fw-bold">${parseInt(utm.clicks).toLocaleString('pt-BR')}</td>
                </tr>`;
                }).join('');
            }

            // ===== By User Table =====
            function renderByUser(data) {
                const tbody = document.getElementById('byUserTable');
                if (!data.length) {
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Sem dados</td></tr>';
                    return;
                }
                tbody.innerHTML = data.map(u => `<tr>
                <td>${escapeHtml(u.username)}</td>
                <td class="text-center">${u.total}</td>
                <td class="text-end fw-bold">${parseInt(u.clicks).toLocaleString('pt-BR')}</td>
            </tr>`).join('');
            }

            // ===== Helpers =====
            function escapeHtml(str) {
                const div = document.createElement('div');
                div.textContent = str || '';
                return div.innerHTML;
            }

            // ===== Period Filter =====
            document.querySelectorAll('.period-filter .btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.period-filter .btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentPeriod = parseInt(this.dataset.period);
                    loadDashboard(currentPeriod);
                });
            });

            // ===== Init =====
            document.addEventListener('DOMContentLoaded', () => {
                loadDashboard(currentPeriod);
                
                // Load admin tabs when clicked
                const domainsTab = document.getElementById('domains-tab');
                const statsTab = document.getElementById('stats-tab');
                const permissionsTab = document.getElementById('permissions-tab');
                
                if (domainsTab) {
                    domainsTab.addEventListener('shown.bs.tab', loadDomainsList);
                }
                if (statsTab) {
                    statsTab.addEventListener('shown.bs.tab', loadDomainStats);
                }
                if (permissionsTab) {
                    permissionsTab.addEventListener('shown.bs.tab', loadPermissionsList);
                }

                // Botão Novo Domínio
                document.getElementById('btnNovoDominio')?.addEventListener('click', () => {
                    ['newDomainName', 'newDomainUrl', 'newDomainLogoLight', 'newDomainLogoDark'].forEach(id => document.getElementById(id).value = '');
                    document.getElementById('newDomainColor').value = '#F2A011';
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('newDomainModal')).show();
                });

                // Botão Nova Permissão — carrega lista de domínios no select
                document.getElementById('btnNovaPermissao')?.addEventListener('click', async () => {
                    const sel = document.getElementById('newPermDomainId');
                    sel.innerHTML = '<option value="">Carregando...</option>';
                    try {
                        const r = await fetch('api/domains.php?action=list');
                        const d = await r.json();
                        sel.innerHTML = '<option value="">Selecione um domínio</option>' +
                            (d.data || []).filter(x => x.is_active).map(x =>
                                `<option value="${x.id}">${escHtml(x.name)}</option>`
                            ).join('');
                    } catch { sel.innerHTML = '<option value="">Erro ao carregar</option>'; }
                    
                    document.getElementById('newPermUser').value = '';
                    document.getElementById('newPermDomainId').value = '';
                    document.getElementById('newPermCreate').checked = true;
                    document.getElementById('newPermEdit').checked = false;
                    document.getElementById('newPermDelete').checked = false;
                    
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('newPermissionModal')).show();
                });

                // Submit: Criar Domínio
                document.getElementById('saveNewDomainBtn')?.addEventListener('click', async function(e) {
                    e.preventDefault();
                    const nameTxt = document.getElementById('newDomainName').value.trim();
                    const urlTxt  = document.getElementById('newDomainUrl').value.trim();
                    if (!nameTxt || !urlTxt) return agAlert('Preencha nome e URL.', 'warning');

                    const btn = document.getElementById('saveNewDomainBtn');
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Criando…';
                    const body = new URLSearchParams({
                        action: 'create_domain',
                        name: nameTxt,
                        domain_url: urlTxt,
                        logo_light: document.getElementById('newDomainLogoLight').value.trim(),
                        logo_dark: document.getElementById('newDomainLogoDark').value.trim(),
                        brand_color: document.getElementById('newDomainColor').value
                    });
                    try {
                        const r = await fetch('api/domains.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body });
                        const d = await r.json();
                        if (d.success) {
                            bootstrap.Modal.getInstance(document.getElementById('newDomainModal'))?.hide();
                            agAlert('Domínio criado com sucesso!', 'success', () => loadDomainsList());
                        } else agAlert('Erro: ' + (d.error || 'Desconhecido'), 'danger');
                    } catch { agAlert('Falha na comunicação.', 'danger'); }
                    finally { btn.disabled = false; btn.innerHTML = '<i class="bi bi-plus-lg me-1"></i> Criar Domínio'; }
                });

                // Submit: Criar Permissão
                document.getElementById('saveNewPermissionBtn')?.addEventListener('click', async function(e) {
                    e.preventDefault();
                    const userEmail = document.getElementById('newPermUser').value.trim();
                    const domainId = document.getElementById('newPermDomainId').value;
                    if (!userEmail || !domainId) return agAlert('Preencha E-mail e selecione o Domínio.', 'warning');

                    const btn = document.getElementById('saveNewPermissionBtn');
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Salvando…';
                    const body = new URLSearchParams({
                        action: 'create_permission',
                        username: userEmail,
                        domain_id: domainId,
                        can_create: document.getElementById('newPermCreate').checked ? 1 : 0,
                        can_edit:   document.getElementById('newPermEdit').checked   ? 1 : 0,
                        can_delete: document.getElementById('newPermDelete').checked ? 1 : 0
                    });
                    try {
                        const r = await fetch('api/permissions.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body });
                        const d = await r.json();
                        if (d.success) {
                            bootstrap.Modal.getInstance(document.getElementById('newPermissionModal'))?.hide();
                            agAlert('Permissão atribuída!', 'success', () => loadPermissionsList());
                        } else agAlert('Erro: ' + (d.error || 'Desconhecido'), 'danger');
                    } catch { agAlert('Falha na comunicação.', 'danger'); }
                    finally { btn.disabled = false; btn.innerHTML = '<i class="bi bi-shield-check me-1"></i> Atribuir Permissão'; }
                });

                // Fix para warning do aria-hidden (Bootstrap perde referência de foco)
                document.addEventListener('hide.bs.modal', () => {
                    if (document.activeElement) document.activeElement.blur();
                });
            });

        })();

        // ===== ADMIN FUNCTIONS =====
        async function loadDomainsList() {
            try {
                const res = await fetch('api/domains.php?action=list');
                const data = await res.json();
                const tbody = document.getElementById('domainsTableBody');
                
                if (!data.success) throw new Error(data.error);
                
                tbody.innerHTML = data.data.map(d => `
                    <tr>
                        <td><strong>${escHtml(d.name)}</strong></td>
                        <td><strong>${escHtml(d.domain_url)}</strong></td>
                        <td>${d.logo_light ? `<img src="${escHtml(d.logo_light)}" alt="Logo" style="height:30px;">` : '<span class="text-muted">—</span>'}</td>
                        <td><span style="display:inline-block;width:30px;height:30px;background-color:${escHtml(d.brand_color)};border-radius:4px;"></span></td>
                        <td>${d.is_active ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>'}</td>
                        <td class="text-end">
                            <button class="btn btn-sm ag-btn-outline btn-edit-domain"
                                data-id="${d.id}"
                                data-name="${escAttr(d.name)}"
                                data-url="${escAttr(d.domain_url)}"
                                data-color="${escAttr(d.brand_color)}"
                                data-logo-light="${escAttr(d.logo_light || '')}"
                                data-logo-dark="${escAttr(d.logo_dark || '')}"
                                data-active="${d.is_active ? 1 : 0}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteDomain(${d.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');

                // Reattach edit listeners after DOM insert
                attachDomainEditListeners();
            } catch (err) {
                console.error('Erro ao carregar domínios:', err);
            }
        }

        async function loadDomainStats() {
            try {
                const res = await fetch('api/domains.php?action=stats');
                const data = await res.json();
                
                if (!data.success) throw new Error(data.error);
                
                document.getElementById('statsTotalDomains').textContent = data.stats.total_domains;
                document.getElementById('statsActiveDomains').textContent = data.stats.active_domains;
                document.getElementById('statsInactiveDomains').textContent = data.stats.inactive_domains;
                
                const tbody = document.getElementById('statsTableBody');
                tbody.innerHTML = data.stats.by_domain.map(s => {
                    const avgClicks = s.total_urls > 0 ? Math.round(s.total_clicks / s.total_urls) : 0;
                    return `
                        <tr>
                            <td><strong>${s.domain}</strong></td>
                            <td>${s.total_urls}</td>
                            <td>${s.total_clicks}</td>
                            <td>${s.unique_users}</td>
                            <td>${avgClicks}</td>
                        </tr>
                    `;
                }).join('');
            } catch (err) {
                console.error('Erro ao carregar estatísticas:', err);
            }
        }

        async function loadPermissionsList() {
            try {
                const res = await fetch('api/permissions.php?action=list');
                const data = await res.json();
                const tbody = document.getElementById('permissionsTableBody');
                
                if (!data.success) throw new Error(data.error);
                
                tbody.innerHTML = data.data.map(p => `
                    <tr>
                        <td>${escHtml(p.user_name || p.username)}</td>
                        <td>${escHtml(p.domain_name)}</td>
                        <td>${p.can_create ? '<span class="badge bg-success">✓</span>' : '<span class="badge bg-secondary">✗</span>'}</td>
                        <td>${p.can_edit   ? '<span class="badge bg-success">✓</span>' : '<span class="badge bg-secondary">✗</span>'}</td>
                        <td>${p.can_delete ? '<span class="badge bg-success">✓</span>' : '<span class="badge bg-secondary">✗</span>'}</td>
                        <td>${escHtml(p.assigned_by)}</td>
                        <td class="text-end">
                            <button class="btn btn-sm ag-btn-outline btn-edit-permission"
                                data-id="${p.id}"
                                data-username="${escAttr(p.user_name || p.username)}"
                                data-domain="${escAttr(p.domain_name)}"
                                data-can-create="${p.can_create ? 1 : 0}"
                                data-can-edit="${p.can_edit ? 1 : 0}"
                                data-can-delete="${p.can_delete ? 1 : 0}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deletePermission(${p.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');

                // Reattach edit listeners after DOM insert
                attachPermissionEditListeners();
            } catch (err) {
                console.error('Erro ao carregar permissões:', err);
            }
        }

        // Escape helpers para uso nos templates JS
        function escHtml(s) { const d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }
        function escAttr(s) { return (s || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;'); }

        // ===================================
        // ATTACH EDIT LISTENERS (chamado após render)
        // ===================================
        function attachDomainEditListeners() {
            document.querySelectorAll('.btn-edit-domain').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.getElementById('editDomainId').value       = this.dataset.id;
                    document.getElementById('editDomainName').value      = this.dataset.name;
                    document.getElementById('editDomainUrl').value       = this.dataset.url;
                    document.getElementById('editDomainColor').value     = this.dataset.color;
                    document.getElementById('editDomainLogoLight').value = this.dataset.logoLight || '';
                    document.getElementById('editDomainLogoDark').value  = this.dataset.logoDark  || '';
                    document.getElementById('editDomainStatus').value    = this.dataset.active;
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('editDomainModal')).show();
                });
            });
        }

        function attachPermissionEditListeners() {
            document.querySelectorAll('.btn-edit-permission').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.getElementById('editPermissionId').value  = this.dataset.id;
                    document.getElementById('editPermUser').textContent   = this.dataset.username;
                    document.getElementById('editPermDomain').textContent = this.dataset.domain;
                    document.getElementById('editPermLabel').textContent  = `${this.dataset.username} · ${this.dataset.domain}`;
                    document.getElementById('editPermCreate').checked = this.dataset.canCreate === '1';
                    document.getElementById('editPermEdit').checked   = this.dataset.canEdit   === '1';
                    document.getElementById('editPermDelete').checked  = this.dataset.canDelete === '1';
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('editPermissionModal')).show();
                });
            });
        }

        // ===================================
        // SALVAR DOMÍNIO
        // ===================================
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('saveDomainBtn')?.addEventListener('click', function () {
                const id    = document.getElementById('editDomainId').value;
                const name  = document.getElementById('editDomainName').value.trim();
                const url   = document.getElementById('editDomainUrl').value.trim();
                const color = document.getElementById('editDomainColor').value;
                const logoL = document.getElementById('editDomainLogoLight').value.trim();
                const logoD = document.getElementById('editDomainLogoDark').value.trim();
                const status = document.getElementById('editDomainStatus').value;

                if (!name || !url) return agAlert('Preencha nome e URL.', 'warning');
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Salvando…';

                fetch('api/domains.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=update_domain&domain_id=${id}&name=${encodeURIComponent(name)}&domain_url=${encodeURIComponent(url)}&brand_color=${encodeURIComponent(color)}&is_active=${status}&logo_light=${encodeURIComponent(logoL)}&logo_dark=${encodeURIComponent(logoD)}`
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        bootstrap.Modal.getInstance(document.getElementById('editDomainModal'))?.hide();
                        agAlert('Domínio atualizado!', 'success', () => loadDomainsList());
                    } else agAlert('Erro: ' + (d.error || 'Desconhecido'), 'danger');
                })
                .catch(() => agAlert('Falha na comunicação.', 'danger'))
                .finally(() => { this.disabled = false; this.innerHTML = '<i class="bi bi-check-lg me-1"></i> Salvar Alterações'; });
            });

            // ===================================
            // SALVAR PERMISSÃO
            // ===================================
            document.getElementById('savePermissionBtn')?.addEventListener('click', function () {
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
                        agAlert('Permissões atualizadas!', 'success', () => loadPermissionsList());
                    } else agAlert('Erro: ' + (d.error || 'Desconhecido'), 'danger');
                })
                .catch(() => agAlert('Falha na comunicação.', 'danger'))
                .finally(() => { this.disabled = false; this.innerHTML = '<i class="bi bi-check-lg me-1"></i> Salvar Permissões'; });
            });
        });

        // ===================================
        // DELETE
        // ===================================
        function deleteDomain(id) {
            agConfirm('Deletar este domínio?', 'Domínios com URLs associadas não podem ser removidos. Esta ação é irreversível.', () => {
                fetch('api/domains.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `action=delete_domain&domain_id=${id}` })
                .then(r => r.json()).then(d => d.success ? loadDomainsList() : agAlert('Erro: ' + d.error, 'danger'))
                .catch(() => agAlert('Falha na comunicação.', 'danger'));
            });
        }

        function deletePermission(id) {
            agConfirm('Remover esta permissão?', 'O usuário perderá acesso ao domínio vinculado.', () => {
                fetch('api/permissions.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `action=delete_permission&permission_id=${id}` })
                .then(r => r.json()).then(d => d.success ? loadPermissionsList() : agAlert('Erro: ' + d.error, 'danger'))
                .catch(() => agAlert('Falha na comunicação.', 'danger'));
            });
        }

        // ===================================
        // HELPERS — Toast + Confirm Antigravity
        // ===================================
        function agAlert(msg, type = 'success', onClose = null) {
            const colors = { success: 'var(--color-accent)', danger: '#dc3545', warning: '#f0ad4e' };
            const icons  = { success: 'bi-check-circle-fill', danger: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill' };
            const t = document.createElement('div');
            t.style.cssText = `position:fixed;top:1.5rem;right:1.5rem;z-index:9999;display:flex;align-items:center;gap:.75rem;padding:1rem 1.5rem;border-radius:14px;background:var(--color-bg-card);border:1.5px solid ${colors[type]||colors.success};box-shadow:0 8px 24px rgba(0,0,0,.2);font-family:var(--font-body);font-size:.9rem;color:var(--color-text-main);min-width:260px;max-width:360px;animation:agSlideIn .3s ease;`;
            t.innerHTML = `<i class="bi ${icons[type]||icons.success}" style="font-size:1.3rem;color:${colors[type]}"></i><span>${msg}</span>`;
            if (!document.getElementById('agSlideInStyle')) { const s = document.createElement('style'); s.id='agSlideInStyle'; s.textContent='@keyframes agSlideIn{from{opacity:0;transform:translateX(40px)}to{opacity:1;transform:none}}'; document.head.appendChild(s); }
            document.body.appendChild(t);
            setTimeout(() => { t.remove(); if (onClose) onClose(); }, 2200);
        }

        function agConfirm(title, body, onConfirm) {
            const uid = 'agCfm_' + Date.now();
            document.body.insertAdjacentHTML('beforeend', `
            <div class="modal fade" id="${uid}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content ag-card" style="border:1px solid var(--color-border);">
                        <div class="modal-body text-center" style="padding:1.75rem 1.5rem;">
                            <i class="bi bi-exclamation-triangle-fill" style="font-size:2rem;color:#f0ad4e;display:block;margin-bottom:.75rem;"></i>
                            <h6 style="font-family:var(--font-heading);font-weight:700;">${title}</h6>
                            <p class="ag-text-muted mb-0" style="font-size:.83rem;">${body}</p>
                        </div>
                        <div class="modal-footer border-0 justify-content-center" style="padding:.5rem 1.5rem 1.5rem;gap:.75rem;">
                            <button class="ag-btn-outline" data-bs-dismiss="modal">Cancelar</button>
                            <button class="ag-btn-accent" id="${uid}_ok"><i class="bi bi-trash me-1"></i>Confirmar</button>
                        </div>
                    </div>
                </div>
            </div>`);
            const el = document.getElementById(uid);
            const mod = new bootstrap.Modal(el);
            mod.show();
            el.querySelector(`#${uid}_ok`).addEventListener('click', () => {
                mod.hide();
                el.addEventListener('hidden.bs.modal', () => { el.remove(); onConfirm(); }, { once: true });
            });
            el.addEventListener('hidden.bs.modal', () => el.remove(), { once: true });
        }
    </script>

    <!-- Modal: Editar Domínio — Antigravity -->
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:1.5rem;">
                    <input type="hidden" id="editDomainId">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.85rem;font-weight:600;">Nome do Domínio</label>
                        <input type="text" id="editDomainName" class="form-control ag-input" placeholder="Ex: Sales Prime" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.85rem;font-weight:600;">URL do Domínio</label>
                        <input type="text" id="editDomainUrl" class="form-control ag-input" placeholder="Ex: salesprime.com.br" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label" style="font-size:.85rem;font-weight:600;">Logo Light</label>
                            <input type="text" id="editDomainLogoLight" class="form-control ag-input" placeholder="assets/images/logo_light.png">
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:.85rem;font-weight:600;">Logo Dark</label>
                            <input type="text" id="editDomainLogoDark" class="form-control ag-input" placeholder="assets/images/logo_dark.png">
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label" style="font-size:.85rem;font-weight:600;">Cor da Marca</label>
                            <input type="color" id="editDomainColor" class="form-control form-control-color w-100" style="height:42px;border-radius:10px;" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:.85rem;font-weight:600;">Status</label>
                            <select id="editDomainStatus" class="form-select ag-input">
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

    <!-- Modal: Editar Permissão — Antigravity -->
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:1.5rem;">
                    <input type="hidden" id="editPermissionId">
                    <div class="p-3 mb-4" style="background:var(--color-bg-main);border-radius:10px;">
                        <div class="d-flex gap-3"><span style="opacity:.6;font-size:.8rem;">USUÁRIO</span><strong id="editPermUser" style="font-size:.9rem;"></strong></div>
                        <div class="d-flex gap-3 mt-1"><span style="opacity:.6;font-size:.8rem;">DOMÍNIO</span><strong id="editPermDomain" style="font-size:.9rem;"></strong></div>
                    </div>
                    <p class="ag-text-muted mb-3" style="font-size:.85rem;">Selecione as permissões para este usuário:</p>
                    <div class="d-flex flex-column gap-3">
                        <label class="d-flex align-items-center gap-3 p-3" style="border-radius:10px;border:1.5px solid var(--color-border);cursor:pointer;">
                            <input type="checkbox" id="editPermCreate" class="form-check-input m-0" style="width:20px;height:20px;accent-color:var(--color-accent);">
                            <div><div style="font-weight:700;font-size:.9rem;"><i class="bi bi-plus-circle me-1"></i>Criar URLs</div><small class="ag-text-muted">Pode gerar novas UTMs neste domínio</small></div>
                        </label>
                        <label class="d-flex align-items-center gap-3 p-3" style="border-radius:10px;border:1.5px solid var(--color-border);cursor:pointer;">
                            <input type="checkbox" id="editPermEdit" class="form-check-input m-0" style="width:20px;height:20px;accent-color:var(--color-accent);">
                            <div><div style="font-weight:700;font-size:.9rem;"><i class="bi bi-pencil-square me-1"></i>Editar URLs</div><small class="ag-text-muted">Pode modificar UTMs existentes</small></div>
                        </label>
                        <label class="d-flex align-items-center gap-3 p-3" style="border-radius:10px;border:1.5px solid var(--color-border);cursor:pointer;">
                            <input type="checkbox" id="editPermDelete" class="form-check-input m-0" style="width:20px;height:20px;accent-color:var(--color-accent);">
                            <div><div style="font-weight:700;font-size:.9rem;"><i class="bi bi-trash me-1"></i>Deletar URLs</div><small class="ag-text-muted">Pode excluir UTMs permanentemente</small></div>
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

    <style>
        .ag-input { background: var(--color-bg-card) !important; border-color: var(--color-border) !important; color: var(--color-text-main) !important; border-radius: 10px !important; }
        .ag-input:focus { border-color: var(--color-accent) !important; box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-accent) 20%, transparent) !important; }
        .modal-content.ag-card { background: var(--color-bg-card) !important; color: var(--color-text-main) !important; }
    </style>

    <!-- Modal: Novo Domínio -->
    <div class="modal fade" id="newDomainModal" tabindex="-1" aria-labelledby="newDomainModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content ag-card" style="border:1px solid var(--color-border);">
                <div class="modal-header border-0 pb-0" style="padding:1.5rem 1.5rem 0;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:40px;height:40px;border-radius:10px;background:var(--color-accent);display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-globe" style="color:#031A2B;font-size:1.1rem;"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0" id="newDomainModalLabel" style="font-family:var(--font-heading);font-weight:700;">Novo Domínio</h5>
                            <small class="ag-text-muted">Adicionar domínio ao sistema</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Corpo sem form para seguir modo escuro -->
                <div class="modal-body" style="padding:1.5rem;">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.85rem;font-weight:600;">Nome do Domínio</label>
                        <input type="text" id="newDomainName" class="form-control ag-input" placeholder="Ex: Sales Prime" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.85rem;font-weight:600;">URL do Domínio</label>
                        <input type="text" id="newDomainUrl" class="form-control ag-input" placeholder="Ex: salesprime.com.br" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label" style="font-size:.85rem;font-weight:600;">Logo Light</label>
                            <input type="text" id="newDomainLogoLight" class="form-control ag-input" placeholder="assets/images/logo_light.png">
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:.85rem;font-weight:600;">Logo Dark</label>
                            <input type="text" id="newDomainLogoDark" class="form-control ag-input" placeholder="assets/images/logo_dark.png">
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:.85rem;font-weight:600;">Cor da Marca</label>
                        <input type="color" id="newDomainColor" class="form-control form-control-color w-100" style="height:42px;border-radius:10px;" value="#F2A011">
                    </div>
                </div>
                <div class="modal-footer border-0" style="padding:.75rem 1.5rem 1.5rem;gap:.75rem;">
                    <button type="button" class="ag-btn-outline" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="ag-btn-accent" id="saveNewDomainBtn">
                        <i class="bi bi-plus-lg me-1"></i> Criar Domínio
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Nova Permissão -->
    <div class="modal fade" id="newPermissionModal" tabindex="-1" aria-labelledby="newPermissionModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content ag-card" style="border:1px solid var(--color-border);">
                <div class="modal-header border-0 pb-0" style="padding:1.5rem 1.5rem 0;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:40px;height:40px;border-radius:10px;background:var(--color-accent);display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-shield-plus" style="color:#031A2B;font-size:1.1rem;"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0" id="newPermissionModalLabel" style="font-family:var(--font-heading);font-weight:700;">Nova Permissão</h5>
                            <small class="ag-text-muted">Atribuir acesso a um usuário</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Corpo sem form para seguir modo escuro -->
                <div class="modal-body" style="padding:1.5rem;">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.85rem;font-weight:600;">E-mail do Usuário</label>
                        <input type="email" id="newPermUser" class="form-control ag-input" placeholder="usuario@email.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.85rem;font-weight:600;">Domínio</label>
                        <select id="newPermDomainId" class="form-select ag-input" required>
                            <option value="">Carregando domínios...</option>
                        </select>
                    </div>
                    <p class="ag-text-muted mb-3" style="font-size:.85rem;">Permissões:</p>
                    <div class="d-flex flex-column gap-3">
                        <label class="d-flex align-items-center gap-3 p-3" style="border-radius:10px;border:1.5px solid var(--color-border);cursor:pointer;">
                            <input type="checkbox" id="newPermCreate" class="form-check-input m-0" style="width:20px;height:20px;accent-color:var(--color-accent);" checked>
                            <div><div style="font-weight:700;font-size:.9rem;"><i class="bi bi-plus-circle me-1"></i>Criar URLs</div><small class="ag-text-muted">Pode gerar novas UTMs</small></div>
                        </label>
                        <label class="d-flex align-items-center gap-3 p-3" style="border-radius:10px;border:1.5px solid var(--color-border);cursor:pointer;">
                            <input type="checkbox" id="newPermEdit" class="form-check-input m-0" style="width:20px;height:20px;accent-color:var(--color-accent);">
                            <div><div style="font-weight:700;font-size:.9rem;"><i class="bi bi-pencil-square me-1"></i>Editar URLs</div><small class="ag-text-muted">Pode modificar UTMs existentes</small></div>
                        </label>
                        <label class="d-flex align-items-center gap-3 p-3" style="border-radius:10px;border:1.5px solid var(--color-border);cursor:pointer;">
                            <input type="checkbox" id="newPermDelete" class="form-check-input m-0" style="width:20px;height:20px;accent-color:var(--color-accent);">
                            <div><div style="font-weight:700;font-size:.9rem;"><i class="bi bi-trash me-1"></i>Deletar URLs</div><small class="ag-text-muted">Pode excluir UTMs permanentemente</small></div>
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0" style="padding:.75rem 1.5rem 1.5rem;gap:.75rem;">
                    <button type="button" class="ag-btn-outline" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="ag-btn-accent" id="saveNewPermissionBtn">
                        <i class="bi bi-shield-check me-1"></i> Atribuir Permissão
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>