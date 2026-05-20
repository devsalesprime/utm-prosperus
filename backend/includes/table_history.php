<?php
/**
 * Componente: Tabela de Histórico de URLs com Paginação Server-Side
 * Antigravity Design System - UTM Generator
 * Versão: 3.0
 *
 * Espera que as seguintes variáveis estejam disponíveis no contexto do include:
 *   $pdo          — Instância PDO (de includes/db.php)
 *   $isLoggedIn   — bool para exibir colunas protegidas
 */

// =====================================================
// 1. PARÂMETROS DE PAGINAÇÃO E FILTRO (Server-Side)
// =====================================================

$urls_por_pagina = 15;
$pagina_atual = max(1, (int) ($_GET['page'] ?? 1));

// Sanitização dos filtros
$filtro_busca = trim($_GET['search'] ?? '');
$filtro_data = trim($_GET['date'] ?? '');
$filtro_user = trim($_GET['user'] ?? '');

// =====================================================
// 2. MONTAGEM DINÂMICA DA QUERY (Segura com PDO)
// =====================================================

$where_clauses = [];
$params = [];

if ($filtro_busca !== '') {
    $where_clauses[] = "(u.long_url LIKE :search OR u.shortened_url LIKE :search OR u.comment LIKE :search)";
    $params[':search'] = '%' . $filtro_busca . '%';
}

if ($filtro_data !== '') {
    $where_clauses[] = "DATE(u.generation_date) = :filter_date";
    $params[':filter_date'] = $filtro_data;
}

if ($filtro_user !== '') {
    $where_clauses[] = "u.username = :filter_user";
    $params[':filter_user'] = $filtro_user;
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// =====================================================
// 3. COUNT TOTAL (para calcular número de páginas)
// =====================================================

$count_stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM urls u {$where_sql}"
);
foreach ($params as $key => $val) {
    $count_stmt->bindValue($key, $val);
}
$count_stmt->execute();
$total_registros = (int) $count_stmt->fetchColumn();
$total_paginas = (int) ceil($total_registros / $urls_por_pagina);
$pagina_atual = min($pagina_atual, max(1, $total_paginas)); // Clamp

// =====================================================
// 4. QUERY PAGINADA COM LIMIT / OFFSET
// =====================================================

$offset = ($pagina_atual - 1) * $urls_por_pagina;

$data_stmt = $pdo->prepare(
    "SELECT u.*, COALESCE(u.domain, 'salesprime.com.br') AS domain
     FROM urls u
     {$where_sql}
     ORDER BY u.generation_date DESC
     LIMIT :limit OFFSET :offset"
);
foreach ($params as $key => $val) {
    $data_stmt->bindValue($key, $val);
}
$data_stmt->bindValue(':limit', $urls_por_pagina, PDO::PARAM_INT);
$data_stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$data_stmt->execute();
$urls = $data_stmt->fetchAll(PDO::FETCH_ASSOC);

// =====================================================
// 5. HELPER: gera URL de paginação preservando filtros
// =====================================================

function ag_page_url(int $page, array $params = []): string
{
    $qs = array_merge($_GET, $params, ['page' => $page]);
    unset($qs['page']); // evita duplicata
    $query = http_build_query(array_filter($qs, fn($v) => $v !== ''));
    return '?' . ($query ? $query . '&' : '') . 'page=' . $page;
}

// Início do exibindo range
$inicio_registro = $total_registros === 0 ? 0 : $offset + 1;
$fim_registro = min($offset + $urls_por_pagina, $total_registros);
?>

<!-- =====================================================
     FILTROS SERVER-SIDE (substituem os filtros JS antigos)
     ===================================================== -->
<form id="historyFilters" method="GET" action="#historico" class="row g-2 mb-4 align-items-end">
    <div class="col-md-5">
        <div class="input-group ag-input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" name="search" id="searchInputField" class="ag-input form-control"
                placeholder="Buscar UTM, link ou comentário…" value="<?= htmlspecialchars($filtro_busca) ?>"
                autocomplete="off">
        </div>
    </div>
    <div class="col-md-3">
        <div class="input-group ag-input-group">
            <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
            <input type="date" name="date" id="dateFilter" class="ag-input form-control"
                value="<?= htmlspecialchars($filtro_data) ?>">
        </div>
    </div>
    <div class="col-md-2">
        <button type="submit" class="ag-btn-accent w-100">
            <i class="bi bi-funnel me-1"></i> Filtrar
        </button>
    </div>
    <?php if ($filtro_busca || $filtro_data): ?>
        <div class="col-md-2">
            <a href="?page=1" class="ag-btn-outline w-100">
                <i class="bi bi-x-circle me-1"></i> Limpar
            </a>
        </div>
    <?php endif; ?>
</form>

<!-- =====================================================
     TABELA DE HISTÓRICO
     ===================================================== -->
<div id="historico" class="ag-table-wrapper">
    <!-- Cabeçalho da tabela com contador -->
    <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom"
        style="border-color: var(--color-border) !important;">
        <span class="ag-text-muted" style="font-size:0.85rem;">
            <?php if ($total_registros === 0): ?>
                Nenhuma URL encontrada
            <?php else: ?>
                Exibindo <strong class="ag-text-accent"><?= $inicio_registro ?></strong>
                a <strong class="ag-text-accent"><?= $fim_registro ?></strong>
                de <strong><?= number_format($total_registros, 0, ',', '.') ?></strong> URLs
            <?php endif; ?>
        </span>
        <span class="ag-badge-accent">
            Página <?= $pagina_atual ?> / <?= max(1, $total_paginas) ?>
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th><i class="bi bi-qr-code"></i> QR Code</th>
                    <th><i class="bi bi-link"></i> Link Original com UTM</th>
                    <th><i class="bi bi-link-45deg"></i> Link Encurtado</th>
                    <?php if (isset($_SESSION['username'])): ?>
                        <th><i class="bi bi-hand-index"></i> Clicks</th>
                        <th><i class="bi bi-toggle-on"></i> Status</th>
                        <th><i class="bi bi-trash"></i> Excluir</th>
                        <th><i class="bi bi-calendar2-check"></i> Data</th>
                        <th><i class="bi bi-chat-left-text"></i> Comentário</th>
                        <th><i class="bi bi-person"></i> Usuário</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($urls)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size:2rem;opacity:.4;display:block;margin-bottom:.5rem;"></i>
                            <span class="ag-text-muted">Nenhuma URL encontrada para os filtros selecionados.</span>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($urls as $row): ?>
                        <?php
                        $domain = $row['domain'] ?? 'salesprime.com.br';
                        $encodedUrl = urlencode("https://{$domain}/utm/" . $row['shortened_url']);
                        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=1500x1500&data={$encodedUrl}";
                        $qrDownload = "https://api.qrserver.com/v1/create-qr-code/?size=1000x1000&data={$encodedUrl}";
                        $formattedDate = date('d/m/Y H:i', strtotime($row['generation_date']));
                        $shortUrl = "https://{$domain}/utm/" . $row['shortened_url'];
                        $slug = $row['shortened_url'];
                        ?>
                        <tr>
                            <!-- QR Code -->
                            <td data-label="QR Code" class="text-center align-middle">
                                <img src="<?= $qrCodeUrl ?>" alt="QR Code" loading="lazy"
                                    style="width:50px;height:50px;cursor:pointer;border-radius:6px;border: 3px solid #fff;"
                                    data-bs-toggle="modal" data-bs-target="#qrModal<?= $slug ?>">
                                <!-- Modal QR -->
                                <div class="modal fade" id="qrModal<?= $slug ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content ag-card">
                                            <div class="modal-header border-0">
                                                <h5 class="modal-title">QR Code — <code><?= $slug ?></code></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <div style="position:relative;display:inline-block;">
                                                    <img id="qrCodeImage<?= $slug ?>" src="<?= $qrCodeUrl ?>" alt="QR Code"
                                                        style="width:280px;height:280px;border-radius:10px;border: 20px solid #fff;box-sizing:border-box;box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                                                    <img id="logoOverlay<?= $slug ?>" src="" alt="Logo"
                                                        style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:60px;height:60px;display:none;">
                                                </div>
                                                <div class="mt-3">
                                                    <label class="form-label ag-text-muted small">Logo no QR Code:</label>
                                                    <select id="logoSelect<?= $slug ?>" class="ag-input form-select mt-1"
                                                        onchange="applyLogo('<?= $slug ?>')">
                                                        <option value="">Nenhuma</option>
                                                        <option value="assets/images/logo_prosperus_club.png">Prosperus Club
                                                        </option>
                                                        <option value="assets/images/logo_sales_prime.png">Sales Prime</option>
                                                    </select>
                                                </div>
                                                <button class="ag-btn-accent mt-3 w-100"
                                                    onclick="downloadQRCodeWithLogo('<?= $slug ?>', '<?= $qrDownload ?>')">
                                                    <i class="bi bi-download me-1"></i> Baixar QR Code
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Link longo -->
                            <td data-label="Link Original" class="col-lg-4 align-middle">
                                <a href="<?= htmlspecialchars($row['long_url']) ?>" target="_blank"
                                    class="ag-text-accent text-decoration-none small text-break">
                                    <?= htmlspecialchars($row['long_url']) ?>
                                </a>
                                <br>
                                <i class="bi bi-clipboard copy-icon mt-1 d-inline-block" style="cursor:pointer;opacity:.6;"
                                    onclick="copyToClipboard(this, '<?= htmlspecialchars($row['long_url'], ENT_QUOTES) ?>')"
                                    title="Copiar"></i>
                            </td>

                            <!-- Link encurtado -->
                            <td data-label="Link Encurtado" class="align-middle">
                                <a href="/utm/<?= $slug ?>" target="_blank" class="ag-text-accent text-decoration-none small">
                                    <?= htmlspecialchars($shortUrl) ?>
                                </a>
                                <br>
                                <i class="bi bi-clipboard copy-icon mt-1 d-inline-block" style="cursor:pointer;opacity:.6;"
                                    onclick="copyToClipboard(this, '<?= htmlspecialchars($shortUrl, ENT_QUOTES) ?>')"
                                    title="Copiar"></i>
                            </td>

                            <?php if (isset($_SESSION['username'])): ?>
                                <!-- Clicks -->
                                <td data-label="Clicks" class="text-center align-middle">
                                    <span class="badge"
                                        style="background:var(--color-bg-main);color:var(--color-text-main);border-radius:50px;padding:.4em .75em;">
                                        <?= (int) ($row['clicks'] ?? 0) ?>
                                    </span>
                                </td>

                                <!-- Status -->
                                <td data-label="Status" class="text-center align-middle">
                                    <button class="btn btn-sm toggle-status-btn" data-id="<?= $row['id'] ?>"
                                        data-status="<?= $row['is_enabled'] ?>"
                                        title="<?= $row['is_enabled'] ? 'Desabilitar' : 'Habilitar' ?>">
                                        <i class="bi <?= $row['is_enabled'] ? 'bi-toggle-on text-success' : 'bi-toggle-off text-danger' ?>"
                                            style="font-size:1.4rem;"></i>
                                    </button>
                                </td>

                                <!-- Excluir -->
                                <td data-label="Excluir" class="text-center align-middle">
                                    <button class="btn btn-sm delete-btn" data-id="<?= $row['id'] ?>"
                                        style="color:var(--bs-danger);">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>

                                <!-- Data -->
                                <td data-label="Data" class="text-center align-middle">
                                    <span class="small ag-text-muted"><?= $formattedDate ?></span>
                                </td>

                                <!-- Comentário -->
                                <td data-label="Comentário" class="align-middle">
                                    <span class="small"><?= htmlspecialchars($row['comment'] ?? '') ?></span>
                                </td>

                                <!-- Usuário -->
                                <td data-label="Usuário" class="text-center align-middle">
                                    <span class="small ag-text-muted"><?= htmlspecialchars($row['username']) ?></span>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div><!-- /.table-responsive -->

    <!-- =====================================================
         PAGINAÇÃO ANTIGRAVITY
         ===================================================== -->
    <?php if ($total_paginas > 1): ?>
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top"
            style="border-color: var(--color-border) !important; flex-wrap: wrap; gap: .5rem;">

            <!-- Contador mobile-friendly -->
            <small class="ag-text-muted">
                <?= $inicio_registro ?>–<?= $fim_registro ?> de <?= number_format($total_registros, 0, ',', '.') ?>
            </small>

            <!-- Botões de navegação -->
            <nav aria-label="Paginação do Histórico">
                <ul class="ag-pagination">

                    <!-- Anterior -->
                    <li class="ag-page-item <?= $pagina_atual <= 1 ? 'disabled' : '' ?>">
                        <a class="ag-page-link" href="<?= ag_page_url($pagina_atual - 1) ?>" aria-label="Anterior">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>

                    <?php
                    // Janela de páginas: mostra no máximo 7 botões
                    $window = 3;
                    $start = max(1, $pagina_atual - $window);
                    $end = min($total_paginas, $pagina_atual + $window);

                    if ($start > 1): ?>
                        <li class="ag-page-item">
                            <a class="ag-page-link" href="<?= ag_page_url(1) ?>">1</a>
                        </li>
                        <?php if ($start > 2): ?>
                            <li class="ag-page-item disabled"><span class="ag-page-link">…</span></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($p = $start; $p <= $end; $p++): ?>
                        <li class="ag-page-item <?= $p === $pagina_atual ? 'active' : '' ?>">
                            <a class="ag-page-link" href="<?= ag_page_url($p) ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($end < $total_paginas): ?>
                        <?php if ($end < $total_paginas - 1): ?>
                            <li class="ag-page-item disabled"><span class="ag-page-link">…</span></li>
                        <?php endif; ?>
                        <li class="ag-page-item">
                            <a class="ag-page-link" href="<?= ag_page_url($total_paginas) ?>"><?= $total_paginas ?></a>
                        </li>
                    <?php endif; ?>

                    <!-- Próximo -->
                    <li class="ag-page-item <?= $pagina_atual >= $total_paginas ? 'disabled' : '' ?>">
                        <a class="ag-page-link" href="<?= ag_page_url($pagina_atual + 1) ?>" aria-label="Próximo">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>

                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div><!-- /.ag-table-wrapper -->

<script>
    /**
     * QR Code: Aplica logo overlay no preview do modal
     * @param {string} slug - shortened_url do registro
     */
    function applyLogo(slug) {
        const logo = document.getElementById('logoSelect' + slug)?.value;
        const overlay = document.getElementById('logoOverlay' + slug);
        if (!overlay) return;
        if (logo) {
            overlay.src = logo;
            overlay.style.display = 'block';
        } else {
            overlay.src = '';
            overlay.style.display = 'none';
        }
    }

    /**
     * QR Code: Faz download do QR Code com logo mesclada via Canvas
     * @param {string} slug       - shortened_url do registro
     * @param {string} qrCodeUrl  - URL do QR Code em alta resolução (1000x1000)
     */
    function downloadQRCodeWithLogo(slug, qrCodeUrl) {
        const logo = document.getElementById('logoSelect' + slug)?.value || '';
        const canvas = document.createElement('canvas');
        canvas.width = 1000;
        canvas.height = 1000;
        const ctx = canvas.getContext('2d');

        // Draw Rounded Rec for transparency masking
        const r = 35; // 10px * (1000/280 approx)
        ctx.beginPath();
        ctx.moveTo(r, 0);
        ctx.lineTo(1000 - r, 0);
        ctx.quadraticCurveTo(1000, 0, 1000, r);
        ctx.lineTo(1000, 1000 - r);
        ctx.quadraticCurveTo(1000, 1000, 1000 - r, 1000);
        ctx.lineTo(r, 1000);
        ctx.quadraticCurveTo(0, 1000, 0, 1000 - r);
        ctx.lineTo(0, r);
        ctx.quadraticCurveTo(0, 0, r, 0);
        ctx.closePath();
        ctx.clip(); // Mask applied

        // Fill with white background (border)
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, 1000, 1000);

        const qrImg = new Image();
        qrImg.crossOrigin = 'Anonymous';
        qrImg.onload = function () {
            // Border thickness approx 20px * (1000/280 approx scale) -> ~70px. Let's use 72px padding
            const pad = 72;
            const size = 1000 - (pad * 2);

            // Draw the QR Code slightly shrunk into the white box
            ctx.drawImage(qrImg, pad, pad, size, size);

            const doDownload = () => {
                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/png');
                link.download = slug + (logo ? '_com_logo' : '') + '.png';
                link.click();
            };

            if (logo) {
                const logoImg = new Image();
                logoImg.crossOrigin = 'Anonymous';
                logoImg.onload = function () {
                    const logoSize = 220; // Re-proportioned for the padded area
                    const x = (1000 - logoSize) / 2;
                    const y = (1000 - logoSize) / 2;
                    ctx.drawImage(logoImg, x, y, logoSize, logoSize);
                    doDownload();
                };
                logoImg.onerror = doDownload; // sem logo se falhar
                logoImg.src = logo;
            } else {
                doDownload();
            }
        };
        qrImg.onerror = () => alert('Erro ao carregar o QR Code. Tente novamente.');
        qrImg.src = qrCodeUrl;
    }
</script>