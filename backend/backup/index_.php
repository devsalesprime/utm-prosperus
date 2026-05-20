<?php
session_start();
require 'db.php';

// Verifique se o usuário está autenticado
if (!isset($_SESSION['username'])) {
    $showModal = true;
} else {
    $showModal = false;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="utf-8">
<meta name="language" content="pt-BR">
<title>Gerador de UTM</title>
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="script.js?v15"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <div class="p-3 fixed-top z-0 mt-5" style="z-index: 0!important;">
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
    <div class="container mt-2">
        <?php if (isset($_SESSION['username'])): ?>
            <div class="z-3 float-md-none float-sm-end">
                <span class="me-3">Usuário: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <form action="logout.php" method="POST" style="display: inline;">
                    <button type="submit" class="btn btn-danger btn-sm">Sair</button>
                </form>
            </div>
        <?php endif; ?>
        <div class="row">
            <h1 class="text-center">Gerador de UTM</h1>
        </div>
        <!-- Formulário para desktop -->
        <div class="container mt-2 d-none d-md-block">
            <form id="utmForm" action="generate.php" method="POST" class="mt-4">
                <div class="input-group mb-3">
                    <span class="input-group-text p5" id="website_url"><i class="bi bi-link me-1"></i> URL do site:</span>
                    <input type="text" class="form-control theme-input" placeholder="Insira sua url válida" aria-label="website_url" aria-describedby="website_url" id="website_url" name="website_url" required>
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text p5" id="utm_campaign"><i class="bi bi-megaphone me-1"></i> UTM Campaing:</span>
                    <input type="text" class="form-control theme-input" placeholder="Exemplo de UTM Campaing: [Time][Evento][Nome_Do_Evento][Mes][In/Outbound][Perfil][Periodo]" aria-label="utm_campaign" aria-describedby="utm_campaign" id="utm_campaign" name="utm_campaign" required>
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text p5" id="utm_source"><i class="bi bi-menu-up me-1"></i> UTM Source:</span>
                    <input type="text" class="form-control theme-input" placeholder="Define a fonte de tráfego" aria-label="utm_source" aria-describedby="utm_source" id="utm_source" name="utm_source" required>
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text p5" id="utm_medium"><i class="bi bi-link-45deg me-1"></i> UTM Medium:</span>
                    <input type="text" class="form-control theme-input" placeholder="Por exemplo, uma postagem de blog, um vídeo, uma postagem de mídia social, etc" aria-label="utm_medium" aria-describedby="utm_medium" id="utm_medium" name="utm_medium" required>
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text p5" id="utm_term"><i class="bi bi-key me-1"></i> UTM Term:</span>
                    <input type="text" class="form-control theme-input" placeholder="Significa a palavra-chave usada para a promoção, gerando tráfego" aria-label="utm_term" aria-describedby="utm_term" id="utm_term" name="utm_term" required>
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text p5" id="custom_name"><i class="bi bi-pencil me-1"></i> Nome Personalizado:</span>
                    <input type="text" class="form-control theme-input" placeholder="Nome Personalizado da url encurtada (opcional)" aria-label="custom_name" aria-describedby="custom_name" id="custom_name" name="custom_name">
                </div>
                <button type="submit" class="btn btn-dark border-light">Gerar UTM</button>
            </form>
        </div>
        <!-- Formulário para mobile -->
        <div class="container mt-5 d-md-none">
            <form action="generate.php" method="POST">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control theme-input" id="website_url_mobile" name="website_url" placeholder="" required>
                    <label for="website_url_mobile">URL do site:</label>
                    <p class="help-text">O endereço completo da URL <strong>(ex. https://www.example.com)</strong></p>
                </div>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control theme-input" id="utm_campaign_mobile" name="utm_campaign" placeholder="" required>
                    <label for="utm_campaign_mobile">UTM Campaing:</label>
                    <p class="help-text">UTM Campaing: [Time][Evento][Nome_Do_Evento][Mes][In/Outbound][Perfil][Periodo]</p>
                </div>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control theme-input" id="utm_source_mobile" name="utm_source" placeholder="" required>
                    <label for="utm_source_mobile">UTM Source:</label>
                    <p class="help-text">Define a fonte de tráfego</p>
                </div>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control theme-input" id="utm_medium_mobile" name="utm_medium" placeholder="" required>
                    <label for="utm_medium_mobile">UTM Medium:</label>
                    <p class="help-text">Por exemplo, uma postagem de blog, um vídeo, uma postagem de mídia social, etc</p>
                </div>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control theme-input" id="utm_term_mobile" name="utm_term" placeholder="" required>
                    <label for="utm_term_mobile">UTM Term:</label>
                    <p class="help-text">Significa a palavra-chave usada para a promoção, gerando tráfego</p>
                </div>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control theme-input" id="custom_name_mobile" placeholder="" name="custom_name">
                    <label for="custom_name_mobile">Nome Personalizado:</label>
                    <p class="help-text">Nome Personalizado da URL encurtada (opcional)</p>
                </div>
                <button type="submit" class="btn btn-dark border-light">Gerar UTM</button>
            </form>
        </div>
        <h2 class="mt-5">Histórico de URLs</h2>
        <div class="row mb-3">
            <div class="col-md-6 mb-2">
                <div class="input-group">
                    <span class="input-group-text" id="searchInput"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchInputField" class="form-control" placeholder="Buscar UTM ou Nome Personalizado" aria-label="Buscar UTM" aria-describedby="searchInput">
                </div>
            </div>
            <div class="input-group col mb-2">
                <span class="input-group-text" id="dateFilterLabel"><i class="bi bi-calendar"></i></span>
                <input type="date" id="dateFilter" class="form-control" aria-label="Filtrar por data" aria-describedby="dateFilterLabel">
            </div>
            <div class="input-group col mb-2">
                <span class="input-group-text" id="clicksFilterLabel"><i class="bi bi-list-ol"></i></span>
                <input type="number" id="clicksFilter" class="form-control" placeholder="Filtrar por número de cliques" aria-label="Filtrar por número de cliques" aria-describedby="clicksFilterLabel">
            </div>
        </div>
        <!-- Tabela de URLs -->
        <div class="table-responsive">
            <table class="table table-bordered table-light">
                <thead>
                    <tr>
                        <th><i class="bi bi-qr-code"></i> QR Code</th>
                        <th><i class="bi bi-link"></i> Link Original com UTM</th>
                        <th><i class="bi bi-link-45deg"></i> Link Encurtado</th>
                        <th><i class="bi bi-hand-index"></i> Clicks</th>
                        <th><i class="bi bi-trash"></i> Excluir</th>
                        <th><i class="bi bi-calendar2-check"></i> Data de Geração</th>
                        <th><i class="bi bi-person"></i> Usuário</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Consulta para obter todas as URLs ordenadas pela data de geração em ordem decrescente
                    $query = $pdo->query("SELECT * FROM urls ORDER BY generation_date DESC");
                    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                        // Gerar a URL do QR Code
                        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode("https://salesprime.com.br/utm/" . $row['shortened_url']);

                        // Formatar a data para o formato dia-mês-ano hora:minutos
                        $formattedDate = date('d-m-Y H:i', strtotime($row['generation_date']));

                        // Exibir a linha da tabela com os dados da URL
                        echo "<tr>
                    <td data-label='QR Code' class='text-center align-middle'>
                        <span data-bs-toggle='tooltip' title='Clique para abrir o QRCode'>
                            <img src='" . $qrCodeUrl . "' alt='QR Code' style='width: 50px; height: 50px; cursor: pointer;' data-bs-toggle='modal' data-bs-target='#qrModal" . $row['shortened_url'] . "'>
                        </span>
                        <div class='modal fade' id='qrModal" . $row['shortened_url'] . "' tabindex='-1' aria-hidden='true'>
                            <div class='modal-dialog modal-dialog-centered'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <h5 class='modal-title'>QR Code</h5>
                                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                    </div>
                                    <div class='modal-body text-center'>
                                        <div style='position: relative; display: inline-block;'>
                                            <img id='qrCodeImage" . $row['shortened_url'] . "' src='" . $qrCodeUrl . "' alt='QR Code' style='width: 300px; height: 300px;'>
                                            <img id='logoOverlay" . $row['shortened_url'] . "' src='' alt='Logo' style='position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 80px; height: 80px; display: none;'>
                                        </div>
                                        <div class='mt-3'>
                                            <label for='logoSelect" . $row['shortened_url'] . "'>Escolha uma logo para aplicar no QR Code:</label>
                                            <select id='logoSelect" . $row['shortened_url'] . "' class='form-select mt-2' onchange='applyLogo(\"" . $row['shortened_url'] . "\")'>
                                                <option value=''>Nenhuma</option>
                                                <option value='images/logo_prosperus_club.png'>Logo Prosperus Club</option>
                                                <option value='images/logo_sales_prime.png'>Logo Sales Prime</option>
                                            </select>
                                        </div>
                                        <div class='mt-3'>
                                            <button class='btn btn-dark border-light' onclick='downloadQRCodeWithLogo(\"" . $row['shortened_url'] . "\", \"https://api.qrserver.com/v1/create-qr-code/?size=256x256&data=" . urlencode("https://salesprime.com.br/utm/" . $row['shortened_url']) . "\")'>
                                                <i class='bi bi-download' aria-hidden='true'></i> Baixar QRCode
                                            </button>
                                        </div>
                                        <script>
                                            function downloadQRCodeWithLogo(shortenedUrl, qrCodeUrl) {
                                                var logo = document.getElementById('logoSelect' + shortenedUrl).value;
                                                var canvas = document.createElement('canvas');
                                                var context = canvas.getContext('2d');
                                                var qrImage = new Image();
                                                qrImage.crossOrigin = 'Anonymous';
                                                qrImage.onload = function() {
                                                    canvas.width = qrImage.width;
                                                    canvas.height = qrImage.height;
                                                    context.drawImage(qrImage, 0, 0);
                                                    if (logo) {
                                                        var logoImage = new Image();
                                                        logoImage.crossOrigin = 'Anonymous';
                                                        logoImage.onload = function() {
                                                            var logoSize = canvas.width * 0.2; // 20% do tamanho do QR Code
                                                            var x = (canvas.width - logoSize) / 2;
                                                            var y = (canvas.height - logoSize) / 2;
                                                            context.drawImage(logoImage, x, y, logoSize, logoSize);
                                                            var link = document.createElement('a');
                                                            link.href = canvas.toDataURL();
                                                            link.download = shortenedUrl + '_with_logo.png';
                                                            link.click();
                                                        };
                                                        logoImage.src = logo;
                                                    } else {
                                                        var link = document.createElement('a');
                                                        link.href = canvas.toDataURL();
                                                        link.download = shortenedUrl + '.png';
                                                        link.click();
                                                    }
                                                };
                                                qrImage.src = qrCodeUrl;
                                            }
                                        </script>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <script>
                            function applyLogo(shortenedUrl) {
                                var logo = document.getElementById('logoSelect' + shortenedUrl).value;
                                var logoOverlay = document.getElementById('logoOverlay' + shortenedUrl);
                                if (logo) {
                                    logoOverlay.src = logo;
                                    logoOverlay.style.display = 'block';
                                } else {
                                    logoOverlay.style.display = 'none';
                                }
                            }
                        </script>
                    </td>
                    <td data-label='Link Original com UTM' class='col-lg-4'>
                        <a href='" . htmlspecialchars($row['long_url']) . "' target='_blank' class='theme-link' data-bs-toggle='tooltip' title='Copiar'>
                            " . htmlspecialchars($row['long_url']) . "
                        </a>
                        <br><i class='bi bi-clipboard copy-icon' data-bs-toggle='tooltip' title='Copiar' onclick='copyToClipboard(this, \"" . htmlspecialchars($row['long_url']) . "\")'></i>
                    </td>
                    <td data-label='Link Encurtado' class='align-middle'>
                        <a href='/utm/" . $row['shortened_url'] . "' target='_blank' class='theme-link' data-bs-toggle='tooltip' title='Copiar'>
                            https://salesprime.com.br/utm/" . $row['shortened_url'] . "
                        </a>
                        <br><i class='bi bi-clipboard copy-icon' data-bs-toggle='tooltip' title='Copiar' onclick='copyToClipboard(this, \"https://salesprime.com.br/utm/" . $row['shortened_url'] . "\")'></i>
                    </td>
                    <td data-label='Clicks' class='text-center align-middle'>" . ($row['clicks'] ?? 0) . "</td>
                    <td data-label='Excluir' class='text-center align-middle'>
                        <button class='btn btn-danger btn-sm delete-btn' data-id='" . $row['id'] . "'>
                            <i class='bi bi-trash'></i>
                        </button>
                    </td>
                    <td data-label='Data da UTM' class='text-center align-middle'>" . $formattedDate . "</td>
                    <td data-label='Usuário' class='text-center align-middle'>" . htmlspecialchars($row['username']) . "</td>
                    </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($showModal): ?>
        <script>
            // Inicializa e exibe o modal
            document.addEventListener('DOMContentLoaded', function() {
                var loginModal = new bootstrap.Modal(document.getElementById('loginModal'), {
                    backdrop: 'static',
                    keyboard: false
                });
                loginModal.show();
            });
        </script>
    <?php endif; ?>
    <?php if ($showModal): ?>
        <!-- Modal de Login -->
        <div class="modal" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="loginModalLabel">Login</h5>
                    </div>
                    <div class="modal-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?php echo $_SESSION['error'];
                                unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success">
                                <?php echo $_SESSION['success'];
                                unset($_SESSION['success']); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Abas de Navegação -->
                        <ul class="nav nav-tabs" id="loginTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab" aria-controls="login" aria-selected="true">Login</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab" aria-controls="register" aria-selected="false">Cadastro</button>
                            </li>
                        </ul>

                        <!-- Conteúdo das Abas -->
                        <div class="tab-content" id="loginTabContent">
                            <div class="tab-pane fade show active" id="login" role="tabpanel" aria-labelledby="login-tab">
                                <!-- Formulário de Login -->
                                <form action="login.php" method="POST" class="mt-3">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Senha</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    <div class="d-flex mb-3">
                                        <div class="p-2">
                                            <button type="submit" name="login" class="btn btn-light">Entrar</button>
                                        </div>
                                        <div class="ms-auto p-2">
                                            <a href="https://salesprime.com.br" class="btn btn-danger ms-auto">Sair</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane fade" id="register" role="tabpanel" aria-labelledby="register-tab">
                                <!-- Formulário de Cadastro -->
                                <form action="login.php" method="POST" class="mt-3">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nome</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Senha</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <p class="small fw-light">Verifique se a senha tem pelo menos 8 caracteres</p>
                                    </div>
                                    <div class="d-flex mb-3">
                                        <div class="p-2"><button type="submit" name="register" class="btn btn-light">Cadastrar</button></div>
                                        <div class="ms-auto p-2"><a href="https://salesprime.com.br" class="btn btn-danger ms-auto">Sair</a></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <!-- Remover os botões de fechar -->
                    </div>
                </div>
            </div>
        </div>
        <script>
            // Inicializa e exibe o modal usando Bootstrap
            document.addEventListener('DOMContentLoaded', function() {
                var loginModal = new bootstrap.Modal(document.getElementById('loginModal'), {
                    backdrop: 'static',
                    keyboard: false
                });
                loginModal.show();
            });
        </script>
    <?php else: ?>
        <!-- Conteúdo da página principal para usuários autenticados -->
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>