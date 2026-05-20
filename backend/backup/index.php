<?php
session_start();
require 'db.php';

// Buscar membros da equipe do banco de dados
$teamMembersStmt = $pdo->query("SELECT id, name, type FROM team_members ORDER BY type, name");
$teamMembers = $teamMembersStmt->fetchAll(PDO::FETCH_ASSOC);

// Organizar membros por tipo
$closers = array_filter($teamMembers, fn($m) => $m['type'] === 'Closer');
$sdrs = array_filter($teamMembers, fn($m) => $m['type'] === 'SDR');
$socialSellers = array_filter($teamMembers, fn($m) => $m['type'] === 'SocialSeller');
$csMembers = array_filter($teamMembers, fn($m) => $m['type'] === 'CS');

// Mostrar modal automaticamente se houver mensagens de erro ou sucesso
$showModal = false;
if (isset($_SESSION['error']) || isset($_SESSION['success'])) {
    $showModal = true;
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
    <link rel="icon" href="https://lp.salesprime.com.br/wp-content/uploads/2024/08/cropped-Favicon-32px-32x32.png.webp"
        sizes="32x32" />
    <link rel="icon"
        href="https://lp.salesprime.com.br/wp-content/uploads/2024/08/cropped-Favicon-32px-192x192.png.webp"
        sizes="192x192" />
    <link rel="apple-touch-icon"
        href="https://lp.salesprime.com.br/wp-content/uploads/2024/08/cropped-Favicon-32px-180x180.png.webp" />
    <meta name="msapplication-TileImage"
        content="https://lp.salesprime.com.br/wp-content/uploads/2024/08/cropped-Favicon-32px-270x270.png" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/componentes.css">
    <link rel="stylesheet" href="assets/css/utm-btn.css">
    <link rel="stylesheet" href="assets/css/tema.css">
    <link rel="stylesheet" href="assets/css/responsivo.css">
    <script src="script.js?v1"></script>
    <!-- Jquery JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.slim.min.js"></script>
</head>

<body>
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
    <div class="container mt-2">
        <!-- Botões de Login e Cadastro no topo -->
        <?php if (!isset($_SESSION['username'])): ?>
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-dark border-light me-2" data-bs-toggle="modal" data-bs-target="#loginModal"
                    onclick="showLoginTab()">Entrar</button>
                <button class="btn btn-dark " data-bs-toggle="modal" data-bs-target="#loginModal"
                    onclick="showRegisterTab()">Cadastro</button>
            </div>
        <?php else: ?>
            <div class="z-3 float-md-none float-sm-end text-end">
                <span class="me-3">Usuário: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                    <a href="admin.php" class="btn btn-success btn-sm"><i class="bi bi-gear"></i> Painel Administrador</a>
                <?php endif; ?>
                <form action="logout.php" method="POST" style="display: inline;">
                    <button type="submit" class="btn btn-danger btn-sm">Sair</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Modal de Login e Cadastro -->
        <div class="modal" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="loginModalLabel">Autenticação do usuário</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

                        <div class="container" id="container">
                            <div class="form-container sign-up-container">
                                <form action="login.php" method="POST">
                                    <h1>Crie sua conta</h1>
                                    <input type="text" name="name" placeholder="Nome Completo" required />
                                    <input type="email" name="email" placeholder="Email" required />
                                    <input type="password" name="password" placeholder="Senha" required />
                                    <button type="submit" name="register">Cadastrar</button>
                                </form>
                            </div>
                            <div class="form-container sign-in-container">
                                <form action="login.php" method="POST">
                                    <h1>Logar</h1>
                                    <input type="email" name="email" placeholder="Email" required />
                                    <input type="password" name="password" placeholder="Senha" required />
                                    <a href="#">Perdeu sua senha?</a>
                                    <button type="submit" name="login">Logar</button>
                                </form>
                            </div>
                            <div class="overlay-container">
                                <div class="overlay">
                                    <div class="overlay-panel overlay-left">
                                        <h1>Seja Bem vindo!</h1>
                                        <p>Para continuar conectado conosco, faça login com suas informações pessoais
                                        </p>
                                        <button class="ghost" id="signIn">Logar com meu usuário</button>
                                    </div>
                                    <div class="overlay-panel overlay-right">
                                        <h1>Olá, tudo bem?</h1>
                                        <p>Insira seus dados pessoais e crie sua UTM da <strong>Sales Prime</strong>!
                                        </p>
                                        <button class="ghost" id="signUp">Criar Usuário</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <img src="images/logo-light.png" alt="Logo" class="img-fluid d-none mx-auto" id="logo-light"
                style="max-width: 150px;">
            <img src="images/logo-dark.png" alt="Logo" class="img-fluid d-block mx-auto" id="logo-dark"
                style="max-width: 150px;">
            <script>
                // Troca a logo conforme o tema e salva no localStorage
                function updateLogo() {
                    // Detecta tema salvo no localStorage ou pelo body
                    let theme = localStorage.getItem('theme');
                    if (!theme) {
                        theme = document.body.classList.contains('dark-theme') ||
                            document.querySelector('.theme-switch-container')?.classList.contains('dark-active') ?
                            'dark' : 'light';
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
            <h1 class="text-center mt-3">Gerador de UTM</h1>
        </div>
        <!-- Formulário para desktop -->
        <div class="container mt-2">
            <form id="utmForm" action="generate.php" method="POST" class="mt-4">
                <!-- Verifica se o usuário está logado -->
                <?php $isDisabled = !isset($_SESSION['username']) ? 'disabled' : ''; ?>

                <div class="input-group mb-3">
                    <span class="input-group-text p5" id="website_url" <?php if ($isDisabled)
                        echo 'data-tooltip-disabled="Precisa estar logado para gerar UTMs"'; ?>><i
                            class="bi bi-link me-1"></i> URL do site:</span>
                    <input type="text" class="form-control theme-input disabled-field"
                        placeholder="Insira sua url válida" aria-label="website_url" aria-describedby="website_url"
                        id="website_url" name="website_url" required <?php echo $isDisabled; ?>>
                </div>

                <div class="input-group mb-3">
                    <div class="btn-group w-100" role="group">
                        <span class="input-group-text p5 rounded-end-0"><i class="bi bi-person me-1"></i> Canais:</span>
                        <input type="radio" class="btn-check disabled-field" name="utm_campaign" id="profile_sales"
                            value="Sales-Prime" checked <?php echo $isDisabled; ?>>
                        <label class="btn btn-outline-secondary" for="profile_sales">Sales-Prime</label>

                        <input type="radio" class="btn-check disabled-field" name="utm_campaign" id="profile_dani"
                            value="Dani-Martins" <?php echo $isDisabled; ?>>
                        <label class="btn btn-outline-secondary" for="profile_dani">Dani-Martins</label>

                        <input type="radio" class="btn-check disabled-field" name="utm_campaign" id="profile_prosperus"
                            value="Prosperus" <?php echo $isDisabled; ?>>
                        <label class="btn btn-outline-secondary" for="profile_prosperus">Prosperus</label>

                        <input type="radio" class="btn-check disabled-field" name="utm_campaign" id="profile_lumiere"
                            value="Lumiere" <?php echo $isDisabled; ?>>
                        <label class="btn btn-outline-secondary" for="profile_lumiere">Lumiere</label>

                        <input type="radio" class="btn-check disabled-field" name="utm_campaign" id="profile_prime"
                            value="Prime" <?php echo $isDisabled; ?>>
                        <label class="btn btn-outline-secondary" for="profile_prime">Prime</label>

                        <input type="radio" class="btn-check disabled-field" name="utm_campaign" id="profile_podvender"
                            value="PodVender" <?php echo $isDisabled; ?>>
                        <label class="btn btn-outline-secondary" for="profile_podvender">PodVender</label>

                        <input type="radio" class="btn-check disabled-field" name="utm_campaign" id="profile_joel"
                            value="Joel-Jota" <?php echo $isDisabled; ?>>
                        <label class="btn btn-outline-secondary" for="profile_joel">Joel-Jota</label>

                        <input type="radio" class="btn-check disabled-field" name="utm_campaign" id="profile_podcast"
                            value="PodCast" <?php echo $isDisabled; ?>>
                        <label class="btn btn-outline-secondary" for="profile_podcast">PodCast</label>
                    </div>
                </div>

                <!-- Origem / Fonte (dinâmico) -->
                <div class="mb-3">
                    <label class="form-label p5 d-block estrutura rounded-end-0"><i class="bi bi-diagram-3 me-1"></i>
                        Origem /
                        Fonte:</label>
                    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-2" id="utm_content_grid">
                        <div class="col">
                            <input type="radio" class="btn-check" name="utm_content_select" id="content_tp" value="TP"
                                <?php echo $isDisabled; ?>>
                            <label class="btn btn-outline-secondary w-100" for="content_tp">Mídia Paga (TP)</label>
                        </div>
                        <div class="col">
                            <input type="radio" class="btn-check" name="utm_content_select" id="content_to" value="TO"
                                <?php echo $isDisabled; ?>>
                            <label class="btn btn-outline-secondary w-100" for="content_to">Mídia Orgânica (TO)</label>
                        </div>
                        <div class="col">
                            <input type="radio" class="btn-check" name="utm_content_select" id="content_sem" value="SEM"
                                <?php echo $isDisabled; ?>>
                            <label class="btn btn-outline-secondary w-100" for="content_sem">Pesquisa Paga (SEM)</label>
                        </div>
                        <div class="col">
                            <input type="radio" class="btn-check" name="utm_content_select" id="content_comm"
                                value="COMM" <?php echo $isDisabled; ?>>
                            <label class="btn btn-outline-secondary w-100" for="content_comm">Comercial</label>
                        </div>
                        <div class="col">
                            <input type="radio" class="btn-check" name="utm_content_select" id="content_sdr" value="SDR"
                                <?php echo $isDisabled; ?>>
                            <label class="btn btn-outline-secondary w-100" for="content_sdr">SDR</label>
                        </div>
                        <div class="col">
                            <input type="radio" class="btn-check" name="utm_content_select" id="content_ssell"
                                value="SSELL" <?php echo $isDisabled; ?>>
                            <label class="btn btn-outline-secondary w-100" for="content_ssell">Social Selling</label>
                        </div>
                        <div class="col">
                            <input type="radio" class="btn-check" name="utm_content_select" id="content_cs" value="CS"
                                <?php echo $isDisabled; ?>>
                            <label class="btn btn-outline-secondary w-100" for="content_cs">Suporte</label>
                        </div>
                        <div class="col">
                            <input type="radio" class="btn-check" name="utm_content_select" id="content_tjj" value="TJJ"
                                <?php echo $isDisabled; ?>>
                            <label class="btn btn-outline-secondary w-100" for="content_tjj">Time Joel Jota</label>
                        </div>
                        <div class="col">
                            <input type="radio" class="btn-check" name="utm_content_select" id="content_mo" value="MO"
                                <?php echo $isDisabled; ?>>
                            <label class="btn btn-outline-secondary w-100" for="content_mo">Mídia Offline</label>
                        </div>
                        <div class="col">
                            <input type="radio" class="btn-check" name="utm_content_select" id="content_tv" value="TV"
                                <?php echo $isDisabled; ?>>
                            <label class="btn btn-outline-secondary w-100" for="content_tv">Mídia Televisiva</label>
                        </div>
                        <div class="col">
                            <input type="radio" class="btn-check" name="utm_content_select" id="content_app" value="APP"
                                <?php echo $isDisabled; ?>>
                            <label class="btn btn-outline-secondary w-100" for="content_app">APP Mobile</label>
                        </div>
                        <div class="col">
                            <input type="radio" class="btn-check" name="utm_content_select" id="content_webinar"
                                value="WEBINAR" <?php echo $isDisabled; ?>>
                            <label class="btn btn-outline-secondary w-100" for="content_webinar">WEBINAR</label>
                        </div>
                    </div>
                </div>

                <!-- Containers dinâmicos por content -->
                <div id="content_dynamic_containers">
                    <div id="container_comm" class="mb-3 d-none">
                        <label class="form-label">Nome do Closer</label>
                        <select id="closer_name" class="form-select" <?php echo $isDisabled; ?>>
                            <option value="">Selecione um Closer</option>
                            <?php foreach ($closers as $closer): ?>
                                <option value="<?= htmlspecialchars($closer['name']) ?>">
                                    <?= htmlspecialchars($closer['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="container_sdr" class="mb-3 d-none">
                        <label class="form-label">Nome do SDR</label>
                        <select id="sdr_name" class="form-select" <?php echo $isDisabled; ?>>
                            <option value="">Selecione um SDR</option>
                            <?php foreach ($sdrs as $sdr): ?>
                                <option value="<?= htmlspecialchars($sdr['name']) ?>"><?= htmlspecialchars($sdr['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="container_ss" class="mb-3 d-none">
                        <label class="form-label">Nome do Social Selling</label>
                        <select id="ss_name" class="form-select mb-2" <?php echo $isDisabled; ?>>
                            <option value="">Selecione um Social Seller</option>
                            <?php foreach ($socialSellers as $seller): ?>
                                <option value="<?= htmlspecialchars($seller['name']) ?>">
                                    <?= htmlspecialchars($seller['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="ss_content_options" class="d-none">
                            <label class="form-label mt-2">Tipo de Social Selling</label>
                            <div class="btn-group w-100" role="group" aria-label="Social Selling Content">
                                <input type="radio" class="btn-check" name="ss_content" id="ss_ig_ativo"
                                    value="ATIVO_IG">
                                <label class="btn btn-outline-secondary" for="ss_ig_ativo">Instagram: Ativo</label>

                                <input type="radio" class="btn-check" name="ss_content" id="ss_ig_passivo"
                                    value="PASSIVO_IG">
                                <label class="btn btn-outline-secondary" for="ss_ig_passivo">Instagram: Passivo</label>

                                <input type="radio" class="btn-check" name="ss_content" id="ss_in_ativo"
                                    value="ATIVO_IN">
                                <label class="btn btn-outline-secondary" for="ss_in_ativo">LinkedIn: Ativo</label>

                                <input type="radio" class="btn-check" name="ss_content" id="ss_in_passivo"
                                    value="PASSIVO_IN">
                                <label class="btn btn-outline-secondary" for="ss_in_passivo">LinkedIn: Passivo</label>
                            </div>
                        </div>
                    </div>
                    <div id="container_cs" class="mb-3 d-none">
                        <label class="form-label">Nome do CS/Suporte</label>
                        <select id="cs_name" class="form-select" <?php echo $isDisabled; ?>>
                            <option value="">Selecione um CS</option>
                            <?php foreach ($csMembers as $cs): ?>
                                <option value="<?= htmlspecialchars($cs['name']) ?>"><?= htmlspecialchars($cs['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="container_mo" class="mb-3 d-none">
                        <label class="form-label">Tipo de Material</label>
                        <select id="mo_type" class="form-select mb-2">
                            <option value="LIVRO">Livro</option>
                            <option value="PDF">PDF</option>
                            <option value="PALESTRA">Palestra</option>
                            <option value="EVENTO">Evento</option>
                        </select>
                        <div id="mo_name_container">
                            <label class="form-label">Nome do Arquivo</label>
                            <input type="text" id="mo_name" class="form-control" placeholder="Digite o nome do arquivo"
                                <?php echo $isDisabled; ?>>
                        </div>
                    </div>
                </div>

                <!-- Campo oculto final que será enviado com o content normalizado -->
                <input type="hidden" name="utm_content" id="utm_content" value="">

                <!-- Campo UTM Source atualizado para botões -->
                <div class="input-group mb-3" id="utm_source_group">
                    <div class="btn-group w-100" role="group">
                        <span class="input-group-text p5 rounded-end-0"><i class="bi bi-menu-up me-1"></i> UTM
                            Source:</span>
                        <input type="radio" class="btn-check disabled-field" name="utm_source" id="source_ig" value="ig"
                            checked <?php echo $isDisabled; ?>>
                        <label class="btn btn-outline-secondary" for="source_ig"><i class="bi bi-instagram"></i></label>

                        <input type="radio" class="btn-check disabled-field" name="utm_source" id="source_yt" value="yt"
                            <?php echo $isDisabled; ?>>
                        <label class="btn btn-outline-secondary" for="source_yt"><i class="bi bi-youtube"></i></label>

                        <input type="radio" class="btn-check disabled-field" name="utm_source" id="source_in" value="in"
                            <?php echo $isDisabled; ?>>
                        <label class="btn btn-outline-secondary" for="source_in"><i class="bi bi-linkedin"></i></label>

                        <input type="radio" class="btn-check disabled-field" name="utm_source" id="source_tktk"
                            value="tktk" <?php echo $isDisabled; ?>>
                        <label class="btn btn-outline-secondary" for="source_tktk"><i class="bi bi-tiktok"></i></label>

                        <input type="radio" class="btn-check disabled-field" name="utm_source" id="source_thrd"
                            value="thrd" <?php echo $isDisabled; ?>>
                        <label class="btn btn-outline-secondary" for="source_thrd"><i class="bi bi-threads"></i></label>

                        <input type="radio" class="btn-check disabled-field" name="utm_source" id="source_spot"
                            value="spot" <?php echo $isDisabled; ?>>
                        <label class="btn btn-outline-secondary" for="source_spot"><i class="bi bi-spotify"></i></label>

                        <input type="radio" class="btn-check disabled-field" name="utm_source" id="source_wpp"
                            value="wpp" <?php echo $isDisabled; ?>>
                        <label class="btn btn-outline-secondary" for="source_wpp"><i class="bi bi-whatsapp"></i></label>

                        <input type="radio" class="btn-check disabled-field" name="utm_source" id="source_appl"
                            value="appl" <?php echo $isDisabled; ?>>
                        <label class="btn btn-outline-secondary" for="source_appl"><i class="bi bi-apple"></i></label>

                        <input type="radio" class="btn-check disabled-field" name="utm_source" id="source_amz"
                            value="amz" <?php echo $isDisabled; ?>>
                        <label class="btn btn-outline-secondary" for="source_amz"><i class="bi bi-amazon"></i></label>

                        <input type="radio" class="btn-check disabled-field" name="utm_source" id="source_dzr"
                            value="dzr" <?php echo $isDisabled; ?>>
                        <label class="btn btn-outline-secondary" for="source_dzr"><i
                                class="bi bi-music-note-beamed"></i></label>

                        <input type="radio" class="btn-check disabled-field" name="utm_source" id="source_email"
                            value="email" <?php echo $isDisabled; ?>>
                        <label class="btn btn-outline-secondary" for="source_email"><i
                                class="bi bi-envelope"></i></label>

                        <input type="radio" class="btn-check disabled-field" name="utm_source" id="source_site"
                            value="site" <?php echo $isDisabled; ?>>
                        <label class="btn btn-outline-secondary" for="source_site"><i class="bi bi-globe"></i></label>
                    </div>
                </div>

                <!-- Campo UTM Medium atualizado (select dinâmico + custom) -->
                <div class="input-group mb-3" id="utm_medium_group">
                    <span class="input-group-text p5" id="utm_medium_label"><i class="bi bi-link-45deg me-1"></i> UTM
                        Medium:</span>
                    <select id="utm_medium" name="utm_medium" class="form-select" required <?php echo $isDisabled; ?>>
                        <option value="" selected disabled>Selecione a Source primeiro...</option>
                    </select>
                    <input type="text" id="utm_medium_custom" name="utm_medium_custom" class="form-control d-none"
                        placeholder="Digite o UTM Medium personalizado" <?php echo $isDisabled; ?>>
                </div>

                <!-- Campo UTM Term atualizado -->
                <div class="input-group mb-3">
                    <span class="input-group-text p5" id="utm_term"><i class="bi bi-key me-1"></i> UTM Term:</span>
                    <input type="text" class="form-control theme-input disabled-field"
                        placeholder="Ex: [VIDEO][NOME], [ESTATICO][NOME], [EPISODIO][CONVIDADO], [CONTATO], [RELACIONAMENTO]"
                        aria-label="utm_term" aria-describedby="utm_term" id="utm_term" name="utm_term" required <?php echo $isDisabled; ?>>
                </div>

                <!-- Campo Nome Personalizado mantido -->
                <div class="input-group mb-3">
                    <span class="input-group-text p5" id="custom_name"><i class="bi bi-pencil me-1"></i> Nome
                        Personalizado:</span>
                    <input type="text" class="form-control theme-input disabled-field"
                        placeholder="Nome Personalizado da url encurtada (opcional)" aria-label="custom_name"
                        aria-describedby="custom_name" id="custom_name" name="custom_name" <?php echo $isDisabled; ?>>
                </div>

                <!-- Adicione este campo antes do botão de submit -->
                <div class="input-group mb-3">
                    <span class="input-group-text p5" id="utm_comment"><i class="bi bi-chat-left-text me-1"></i>
                        Comentário:</span>
                    <input type="text" class="form-control theme-input disabled-field"
                        placeholder="Descrição ou observação sobre esta UTM" aria-label="utm_comment"
                        aria-describedby="utm_comment" id="utm_comment" name="utm_comment" required <?php echo $isDisabled; ?>>
                </div>
                <button type="submit" class="btn btn-dark border-light mb-3 disabled-field" <?php echo $isDisabled; ?>>Gerar UTM</button>
            </form>
        </div>

        <p>
            <a data-bs-toggle="collapse" href="#sobreUTM" class="theme-link" role="button" aria-expanded="false"
                aria-controls="sobreUTM"
                onclick="this.querySelector('i').classList.toggle('bi-caret-down-fill'); this.querySelector('i').classList.toggle('bi-caret-right-fill');">
                <i class="bi bi-caret-right-fill"></i> Sobre links de UTM
            </a>
        </p>
        <!-- Mostra texto sobre UTMs -->
        <div class="collapse mt-3" id="sobreUTM">
            <div class="card card-body">
                <h5 class="mb-3"><i class="bi bi-gear me-2"></i>Como Funciona Este Sistema</h5>
                <div class="alert alert-info">
                    <strong>Fluxo de Criação:</strong>
                    <ol class="mb-0 mt-2">
                        <li><strong>URL do Site:</strong> Insira a URL de destino</li>
                        <li><strong>Canal:</strong> Selecione o canal de marketing (Sales-Prime, Dani-Martins, etc.)
                        </li>
                        <li><strong>Origem/Fonte:</strong> Escolha o tipo de tráfego (Mídia Paga, Orgânica, Comercial,
                            etc.)</li>
                        <li><strong>Campos Dinâmicos:</strong> Dependendo da origem, campos específicos aparecerão</li>
                        <li><strong>UTM Source & Medium:</strong> Selecione a plataforma e o formato do conteúdo</li>
                        <li><strong>UTM Term:</strong> Detalhe específico da campanha</li>
                        <li><strong>Nome Personalizado:</strong> (Opcional) Nome curto para a URL encurtada</li>
                        <li><strong>Comentário:</strong> Descrição interna para referência futura</li>
                    </ol>
                </div>

                <hr class="my-4">

                <h5 class="mb-3"><i class="bi bi-list-check me-2"></i>Tipos de Origem/Fonte Disponíveis</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white">
                                <strong>Tráfego Pago</strong>
                            </div>
                            <div class="card-body">
                                <ul class="mb-0">
                                    <li><strong>TP (Mídia Paga):</strong> Tráfego pago em redes sociais</li>
                                    <li><strong>SEM (Pesquisa Paga):</strong> Google Ads, Bing Ads</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-success text-white">
                                <strong>Tráfego Orgânico</strong>
                            </div>
                            <div class="card-body">
                                <ul class="mb-0">
                                    <li><strong>TO (Mídia Orgânica):</strong> Posts orgânicos em redes sociais</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-warning">
                                <strong>Equipe Comercial</strong>
                            </div>
                            <div class="card-body">
                                <ul class="mb-0">
                                    <li><strong>Comercial (COMM):</strong> Links de Closers</li>
                                    <li><strong>SDR:</strong> Links de SDRs</li>
                                    <li><strong>Social Selling (SSELL):</strong> Vendas via redes sociais</li>
                                    <li><strong>CS (Suporte):</strong> Links do time de Customer Success</li>
                                </ul>
                                <div class="alert alert-light mt-2 mb-0">
                                    <small><i class="bi bi-lightbulb me-1"></i>
                                        <strong>Novidade:</strong> Agora você seleciona o nome do membro da equipe
                                        de uma lista pré-cadastrada pelo administrador!</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-info text-white">
                                <strong>Outros Canais</strong>
                            </div>
                            <div class="card-body">
                                <ul class="mb-0">
                                    <li><strong>TJJ (Time Joel Jota):</strong> Conteúdo do time Joel Jota</li>
                                    <li><strong>MO (Mídia Offline):</strong> Livros, PDFs, Palestras, Eventos</li>
                                    <li><strong>TV (Mídia Televisiva):</strong> Aparições em TV</li>
                                    <li><strong>APP (APP Mobile):</strong> Links do aplicativo</li>
                                    <li><strong>WEBINAR:</strong> Links de webinars (sem Source/Medium)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="mb-3"><i class="bi bi-table me-2"></i>Parâmetros UTM Detalhados</h5>
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 20%">Parâmetro</th>
                            <th style="width: 40%">Descrição</th>
                            <th style="width: 40%">Exemplos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>utm_source</code></td>
                            <td>
                                <strong>Plataforma de origem do tráfego.</strong><br>
                                O sistema oferece opções pré-definidas baseadas nas principais plataformas.
                                Segue o padrão usado por META e Google Analytics.
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <span class="badge bg-secondary">ig</span>
                                    <span class="badge bg-secondary">yt</span>
                                    <span class="badge bg-secondary">in</span>
                                    <span class="badge bg-secondary">tktk</span>
                                    <span class="badge bg-secondary">thrd</span>
                                    <span class="badge bg-secondary">spot</span>
                                    <span class="badge bg-secondary">wpp</span>
                                    <span class="badge bg-secondary">appl</span>
                                    <span class="badge bg-secondary">amz</span>
                                    <span class="badge bg-secondary">dzr</span>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <strong>Nota:</strong> Alguns tipos (WEBINAR, PALESTRA, EVENTO) não usam
                                    Source/Medium
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <td><code>utm_medium</code></td>
                            <td>
                                <strong>Formato ou tipo de conteúdo.</strong><br>
                                O sistema gera automaticamente as opções disponíveis baseado no Source selecionado.
                                Segue nomenclatura própria: inicia com maiúscula e usa "_" para espaços.
                            </td>
                            <td>
                                <div class="mb-2">
                                    <strong>Instagram:</strong>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        <span class="badge bg-info">Instagram_Feed</span>
                                        <span class="badge bg-info">Instagram_Stories</span>
                                        <span class="badge bg-info">Instagram_Reels</span>
                                        <span class="badge bg-info">Instagram_Bio</span>
                                        <span class="badge bg-info">Instagram_Direct</span>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <strong>YouTube:</strong>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        <span class="badge bg-danger">Youtube_Video</span>
                                        <span class="badge bg-danger">Youtube_Descricao</span>
                                        <span class="badge bg-danger">Youtube_Card</span>
                                        <span class="badge bg-danger">Youtube_Bio</span>
                                    </div>
                                </div>
                                <div>
                                    <strong>LinkedIn:</strong>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        <span class="badge bg-primary">Linkedin_Post</span>
                                        <span class="badge bg-primary">Linkedin_Artigo</span>
                                        <span class="badge bg-primary">Linkedin_Bio</span>
                                        <span class="badge bg-primary">Linkedin_InMail</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><code>utm_campaign</code></td>
                            <td>
                                <strong>Canal de marketing selecionado.</strong><br>
                                Identifica qual time/perfil está gerando o tráfego. Valores fixos definidos no sistema.
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <span class="badge bg-success">Sales-Prime</span>
                                    <span class="badge bg-success">Dani-Martins</span>
                                    <span class="badge bg-success">Prosperus</span>
                                    <span class="badge bg-success">Lumiere</span>
                                    <span class="badge bg-success">Prime</span>
                                    <span class="badge bg-success">PodVender</span>
                                    <span class="badge bg-success">Joel-Jota</span>
                                    <span class="badge bg-success">PodCast</span>

                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><code>utm_content</code></td>
                            <td>
                                <strong>Identificador do tipo de origem.</strong><br>
                                Gerado automaticamente pelo sistema baseado na seleção de Origem/Fonte.
                                Para equipes comerciais, inclui o nome do membro selecionado.
                            </td>
                            <td>
                                <div class="mb-2">
                                    <strong>Tipos Simples:</strong>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        <span class="badge bg-dark">TP</span>
                                        <span class="badge bg-dark">TO</span>
                                        <span class="badge bg-dark">SEM</span>
                                        <span class="badge bg-dark">TJJ</span>
                                        <span class="badge bg-dark">TV</span>
                                        <span class="badge bg-dark">APP</span>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <strong>Com Nome:</strong>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        <span class="badge bg-warning text-dark">JOAO_SILVA_CLOSER</span>
                                        <span class="badge bg-warning text-dark">MARIA_SANTOS_SDR</span>
                                        <span class="badge bg-warning text-dark">PEDRO_COSTA_SSELL</span>
                                        <span class="badge bg-warning text-dark">ANA_LIMA_CS</span>
                                    </div>
                                </div>
                                <div>
                                    <strong>Mídia Offline:</strong>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        <span class="badge bg-secondary">MO_LIVRO_VENDAS_COMPLEXAS</span>
                                        <span class="badge bg-secondary">MO_PDF_EBOOK_LEADS</span>
                                        <span class="badge bg-secondary">MO_PALESTRA</span>
                                        <span class="badge bg-secondary">MO_EVENTO</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><code>utm_term</code></td>
                            <td>
                                <strong>Termo ou detalhe específico da campanha.</strong><br>
                                Campo livre para identificar o conteúdo específico ou contexto da campanha.
                                Use formato descritivo e claro.
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-2">
                                    <code>[VIDEO][COMO_VENDER_MAIS]</code>
                                    <code>[ESTATICO][DEPOIMENTO_CLIENTE]</code>
                                    <code>[EPISODIO][CONVIDADO_JOAO]</code>
                                    <code>[CONTATO]</code>
                                    <code>[RELACIONAMENTO]</code>
                                    <code>STORIES_DIA_15</code>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <hr class="my-4">

                <h5 class="mb-3"><i class="bi bi-stars me-2"></i>Recursos Especiais</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <i class="bi bi-link-45deg me-2"></i>URL Encurtada
                            </div>
                            <div class="card-body">
                                <p>Todas as UTMs geradas são automaticamente encurtadas para facilitar o
                                    compartilhamento.</p>
                                <p class="mb-0"><strong>Exemplo:</strong><br>
                                    <code class="text-break">salesprime.com.br/utm/minha-campanha</code>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <i class="bi bi-qr-code me-2"></i>QR Code Automático
                            </div>
                            <div class="card-body">
                                <p>Cada UTM gera automaticamente um QR Code que pode ser baixado com logo personalizada.
                                </p>
                                <p class="mb-0"><strong>Logos disponíveis:</strong> Sales Prime, Prosperus Club</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-warning">
                            <div class="card-header bg-warning">
                                <i class="bi bi-graph-up me-2"></i>Rastreamento de Cliques
                            </div>
                            <div class="card-body">
                                <p>O sistema rastreia automaticamente quantos cliques cada UTM recebe.</p>
                                <p class="mb-0">Você pode filtrar e ordenar por número de cliques no histórico.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <i class="bi bi-toggle-on me-2"></i>Ativar/Desativar
                            </div>
                            <div class="card-body">
                                <p>UTMs podem ser temporariamente desativadas sem serem excluídas.</p>
                                <p class="mb-0">Links desativados redirecionam para uma página de aviso.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="alert alert-primary">
                    <h6 class="alert-heading"><i class="bi bi-lightbulb me-2"></i>Dicas de Uso</h6>
                    <ul class="mb-0">
                        <li><strong>Nome Personalizado:</strong> Use nomes curtos e descritivos para facilitar o
                            compartilhamento</li>
                        <li><strong>Comentários:</strong> Sempre adicione comentários detalhados para referência futura
                            da equipe</li>
                        <li><strong>Consistência:</strong> Mantenha um padrão de nomenclatura para facilitar análises
                        </li>
                        <li><strong>Teste:</strong> Sempre teste o link gerado antes de compartilhar em campanhas</li>
                        <li><strong>Organização:</strong> Use os filtros de busca e data para encontrar UTMs antigas
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <h2 class="mt-5">Histórico de URLs</h2>
        <div class="row mb-3">
            <div class="col-md-6 mb-2">
                <div class="input-group">
                    <span class="input-group-text" id="searchInput"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchInputField" class="form-control"
                        placeholder="Buscar UTM ou Nome Personalizado" aria-label="Buscar UTM"
                        aria-describedby="searchInput">
                </div>
            </div>
            <div class="input-group col mb-2">
                <span class="input-group-text" id="dateFilterLabel"><i class="bi bi-calendar"></i></span>
                <input type="date" id="dateFilter" class="form-control" aria-label="Filtrar por data"
                    aria-describedby="dateFilterLabel">
            </div>
            <div class="input-group col mb-2">
                <span class="input-group-text" id="clicksFilterLabel"><i class="bi bi-list-ol"></i></span>
                <input type="number" id="clicksFilter" class="form-control" placeholder="Filtrar por número de cliques"
                    aria-label="Filtrar por número de cliques" aria-describedby="clicksFilterLabel">
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
                        <?php if (isset($_SESSION['username'])): ?>
                            <th><i class="bi bi-hand-index"></i> Clicks</th>
                            <th><i class="bi bi-toggle-on"></i> Status</th>
                            <th><i class="bi bi-trash"></i> Excluir</th>
                            <th><i class="bi bi-calendar2-check"></i> Data de Geração</th>
                            <th><i class="bi bi-chat-left-text"></i> Comentário</th> <!-- Nova coluna -->
                            <th><i class="bi bi-person"></i> Usuário</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Consulta para obter todas as URLs ordenadas pela data de geração em ordem decrescente
                    $query = $pdo->query("SELECT *, is_enabled FROM urls ORDER BY generation_date DESC");
                    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                        // Gerar a URL do QR Code
                        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=1500x1500&data=' . urlencode("https://salesprime.com.br/utm/" . $row['shortened_url']);

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
                    <button class='btn btn-dark border-light' onclick='downloadQRCodeWithLogo(\"" . $row['shortened_url'] . "\", \"https://api.qrserver.com/v1/create-qr-code/?size=1000x1000&data=" . urlencode("https://salesprime.com.br/utm/" . $row['shortened_url']) . "\")'>
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
                    canvas.width = 1000;
                    canvas.height = 1000;
                    context.drawImage(qrImage, 0, 0, 1000, 1000);
                    if (logo) {
                        var logoImage = new Image();
                        logoImage.crossOrigin = 'Anonymous';
                        logoImage.onload = function() {
                        var logoSize = canvas.width * 0.25; // 25% do tamanho do QR Code
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
                <a href='" . htmlspecialchars($row['long_url']) . "' target='_blank' class='link-dark' data-bs-toggle='tooltip' title='Copiar'>
                " . htmlspecialchars($row['long_url']) . "
                </a>
                <br><i class='bi bi-clipboard copy-icon' data-bs-toggle='tooltip' title='Copiar' onclick='copyToClipboard(this, \"" . htmlspecialchars($row['long_url']) . "\")'></i>
            </td>
            <td data-label='Link Encurtado' class='align-middle'>
                <a href='/utm/" . $row['shortened_url'] . "' target='_blank' class='link-dark' data-bs-toggle='tooltip' title='Copiar'>
                https://salesprime.com.br/utm/" . $row['shortened_url'] . "
                </a>
                <br><i class='bi bi-clipboard copy-icon' data-bs-toggle='tooltip' title='Copiar' onclick='copyToClipboard(this, \"https://salesprime.com.br/utm/" . $row['shortened_url'] . "\")'></i>
            </td>";
                        if (isset($_SESSION['username'])) {
                            echo "
            <td data-label='Clicks' class='text-center align-middle'>" . ($row['clicks'] ?? 0) . "</td>
            <td data-label='Status' class='text-center align-middle'>
                <button class='btn btn-sm toggle-status-btn' data-id='" . $row['id'] . "' data-status='" . $row['is_enabled'] . "' title='" . ($row['is_enabled'] ? 'Desabilitar' : 'Habilitar') . "'>
                    <i class='bi " . ($row['is_enabled'] ? 'bi-toggle-on text-success' : 'bi-toggle-off text-danger') . "'></i>
                </button>
            </td>
            <td data-label='Excluir' class='text-center align-middle'>
                <button class='btn btn-danger btn-sm delete-btn' data-id='" . $row['id'] . "'>
                <i class='bi bi-trash'></i>
                </button>
            </td>
            <td data-label='Data da UTM' class='text-center align-middle'>" . $formattedDate . "</td>
            <td data-label='Comentário' class='align-middle'>" . htmlspecialchars($row['comment'] ?? '') . "</td>
            <td data-label='Usuário' class='text-center align-middle'>" . htmlspecialchars($row['username']) . "</td>";
                        }
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($showModal): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var loginModal = new bootstrap.Modal(document.getElementById('loginModal'), {
                    backdrop: 'static',
                    keyboard: false
                });
                loginModal.show();
            });
        </script>
    <?php endif; ?>

    <script>
        // Adicionar evento de submit para garantir que ss_content seja enviado
        document.getElementById('utmForm').addEventListener('submit', function (e) {
            // Verificar se é Social Selling
            const ssContentRadio = document.querySelector('input[name="ss_content"]:checked');
            if (ssContentRadio) {
                // Criar um campo oculto para enviar o valor do ss_content (se já não existir)
                if (!document.querySelector('input[name="ss_content"][type="hidden"]')) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'ss_content';
                    hiddenInput.value = ssContentRadio.value;
                    this.appendChild(hiddenInput);
                }
            }
        });
    </script>

    <!-- Bootstrap Bundle com Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Scripts -->
    <script>
        // Função para exibir a aba de login
        function showLoginTab() {
            const loginTab = document.getElementById('login-tab');
            const registerTab = document.getElementById('register-tab');
            loginTab.classList.add('active');
            registerTab.classList.remove('active');
            document.getElementById('login').classList.add('show', 'active');
            document.getElementById('register').classList.remove('show', 'active');
        }

        // Função para exibir a aba de cadastro
        function showRegisterTab() {
            const loginTab = document.getElementById('login-tab');
            const registerTab = document.getElementById('register-tab');
            registerTab.classList.add('active');
            loginTab.classList.remove('active');
            document.getElementById('register').classList.add('show', 'active');
            document.getElementById('login').classList.remove('show', 'active');
        }

        // Exibir mensagem de sucesso e abrir aba de login após cadastro
        <?php if (isset($_SESSION['success'])): ?>
            document.addEventListener('DOMContentLoaded', function () {
                var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                loginModal.show();
                showLoginTab();
            });
        <?php endif; ?>

        document.addEventListener('DOMContentLoaded', function () {
            <?php if ($isDisabled): ?>
                // Tooltip para elementos desabilitados individuais
                const disabledElements = document.querySelectorAll('.disabled-field[disabled]');
                disabledElements.forEach(el => {
                    new bootstrap.Tooltip(el, {
                        title: "Precisa estar logado para gerar UTMs",
                        placement: 'top'
                    });
                });

                // Tooltip para grupos de botões (como os radios)
                const disabledGroups = document.querySelectorAll('.btn-group:has(.disabled-field[disabled])');
                disabledGroups.forEach(el => {
                    new bootstrap.Tooltip(el, {
                        title: "Precisa estar logado para gerar UTMs",
                        placement: 'top'
                    });
                });

            <?php endif; ?>
        });

        document.addEventListener('DOMContentLoaded', () => {
            const signUpButton = document.getElementById('signUp');
            const signInButton = document.getElementById('signIn');
            const container = document.getElementById('container');

            if (signUpButton && signInButton && container) {
                signUpButton.addEventListener('click', () => {
                    container.classList.add("right-panel-active");
                });

                signInButton.addEventListener('click', () => {
                    container.classList.remove("right-panel-active");
                });
            } else {
                console.error('One or more elements are missing in the DOM.');
            }
        });
        document.addEventListener('DOMContentLoaded', () => {
            const selects = document.querySelectorAll('.utm-select');
            const utmCampaignInput = document.getElementById('utm_campaign');
            const profileRadios = document.getElementsByName('profile');

            let selectedProfile = 'Sales-Prime'; // Valor padrão

            // Atualiza o perfil selecionado
            profileRadios.forEach(radio => {
                if (radio.checked) selectedProfile = radio.value;
                radio.addEventListener('change', () => {
                    selectedProfile = radio.value;
                    updateCampaignValue();
                });
            });

            function updateCampaignValue() {
                const selectedSelect = Array.from(selects).find(select => select.value && select.value !== 'Selecione...');
                if (selectedSelect && selectedSelect.value !== 'Selecione...') {
                    utmCampaignInput.value = `[TO][${selectedProfile}][${selectedSelect.value}]`;
                } else {
                    utmCampaignInput.value = '';
                }
            }

            // Monitora mudanças nos selects
            selects.forEach(select => {
                select.addEventListener('change', () => {
                    // Limpa outros selects
                    selects.forEach(other => {
                        if (other !== select) other.selectedIndex = 0;
                    });

                    updateCampaignValue();
                });
            });

            // Garante que o valor seja atualizado mesmo sem interação
            setTimeout(updateCampaignValue, 500);
        });
    </script>
</body>

</html>