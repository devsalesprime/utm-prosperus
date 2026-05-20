# UTM Generator - Guia Rápido de Implementação MVP

> **Templates prontos + Estrutura base + Deploy rápido**  
> De zero a produção em horas, não dias

---

## 🚀 QUICK START - 30 MINUTOS

### Setup Inicial

```bash
# 1. Clone/crie o diretório
cd c:\xampp\htdocs
mkdir utm
cd utm

# 2. Crie o banco de dados
mysql -u root -p
```

```sql
-- 3. Execute este script SQL
CREATE DATABASE IF NOT EXISTS utm_generator;
USE utm_generator;

-- Import completo no final deste documento
```

### Configuração Base

**config.php** (copiar e colar):
```php
<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'utm_generator');
define('DB_USER', 'root');
define('DB_PASS', '');

// App Configuration
define('APP_URL', 'http://localhost/utm');
define('SESSION_LIFETIME', 1800); // 30 minutes

// Security
define('HASH_COST', 12);
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Database Connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
session_start();

// Helper Functions
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}
?>
```

---

## 📦 TEMPLATES PRONTOS PARA USO

### Template 1: Página Base com Header/Footer

**_header.php**:
```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="<?= $_SESSION['theme'] ?? 'light' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'UTM Generator' ?> - Sales Prime</title>
    
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-link-45deg"></i> UTM Generator
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $activePage === 'generator' ? 'active' : '' ?>" href="index.php">
                                <i class="bi bi-plus-circle"></i> Gerar UTM
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $activePage === 'my-utms' ? 'active' : '' ?>" href="my_utms.php">
                                <i class="bi bi-list-ul"></i> Minhas UTMs
                            </a>
                        </li>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-gear"></i> Admin
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="user_management.php">Usuários</a></li>
                                    <li><a class="dropdown-item" href="team_management.php">Equipe</a></li>
                                    <li><a class="dropdown-item" href="analytics.php">Analytics</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <!-- Theme Toggle -->
                    <li class="nav-item">
                        <button class="btn btn-link nav-link" id="themeToggle">
                            <i class="bi bi-moon-fill"></i>
                        </button>
                    </li>
                    
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?= sanitize($_SESSION['username']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php">Perfil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Sair</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Cadastrar</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Container -->
    <div class="container-fluid mt-4">
```

**_footer.php**:
```php
    </div> <!-- End Main Container -->
    
    <!-- Footer -->
    <footer class="footer mt-5 py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">
                &copy; <?= date('Y') ?> Sales Prime - UTM Generator v2.1
            </span>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Theme Toggle Script -->
    <script>
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        
        themeToggle?.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-bs-theme', newTheme);
            themeToggle.querySelector('i').className = 
                newTheme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
            
            // Save to session
            fetch('save_theme.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({theme: newTheme})
            });
        });
    </script>
</body>
</html>
```

---

### Template 2: Formulário de Login/Registro

**login.php**:
```php
<?php
require_once 'config.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    if (!$email || empty($password)) {
        $error = 'Email e senha são obrigatórios';
    } else {
        // Check login attempts
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM login_attempts 
            WHERE ip_address = ? 
            AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
        ");
        $stmt->execute([$ip]);
        
        if ($stmt->fetchColumn() >= LOGIN_MAX_ATTEMPTS) {
            $error = 'Muitas tentativas. Tente novamente em 15 minutos.';
        } else {
            // Attempt login
            $stmt = $pdo->prepare("
                SELECT id, username, email, password, role, is_approved 
                FROM users 
                WHERE email = ?
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                if (!$user['is_approved']) {
                    $error = 'Sua conta ainda não foi aprovada por um administrador.';
                } else {
                    // Successful login
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    
                    // Clear login attempts
                    $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?")
                        ->execute([$ip]);
                    
                    header('Location: index.php');
                    exit;
                }
            } else {
                // Log failed attempt
                $pdo->prepare("INSERT INTO login_attempts (ip_address, email) VALUES (?, ?)")
                    ->execute([$ip, $email]);
                
                $error = 'Email ou senha incorretos';
            }
        }
    }
}

$pageTitle = 'Login';
include '_header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">
                    <i class="bi bi-person-circle"></i> Login
                </h3>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> <?= sanitize($error) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Lembrar-me</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right"></i> Entrar
                    </button>
                </form>
                
                <hr>
                
                <div class="text-center">
                    <p class="mb-0">Não tem uma conta?</p>
                    <a href="register.php" class="btn btn-link">Cadastre-se aqui</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '_footer.php'; ?>
```

---

### Template 3: CRUD Básico (Exemplo com Team Members)

**team_management.php**:
```php
<?php
require_once 'config.php';
requireAdmin();

$message = '';
$messageType = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'add':
                $name = sanitize($_POST['name']);
                $role = $_POST['role'];
                
                if (empty($name) || !in_array($role, ['CLOSER', 'SDR', 'SSELL', 'CS'])) {
                    throw new Exception('Dados inválidos');
                }
                
                $stmt = $pdo->prepare("INSERT INTO team_members (name, role) VALUES (?, ?)");
                $stmt->execute([$name, $role]);
                
                $message = "Membro adicionado com sucesso!";
                $messageType = 'success';
                break;
                
            case 'toggle':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("UPDATE team_members SET is_active = NOT is_active WHERE id = ?");
                $stmt->execute([$id]);
                
                $message = "Status atualizado!";
                $messageType = 'success';
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM team_members WHERE id = ?");
                $stmt->execute([$id]);
                
                $message = "Membro removido!";
                $messageType = 'success';
                break;
        }
    } catch (Exception $e) {
        $message = "Erro: " . $e->getMessage();
        $messageType = 'danger';
    }
}

// Fetch team members
$stmt = $pdo->query("
    SELECT * FROM team_members 
    ORDER BY role, name
");
$members = $stmt->fetchAll();

$pageTitle = 'Gestão de Equipe';
$activePage = 'admin';
include '_header.php';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="bi bi-people"></i> Gestão de Equipe</h2>
        <hr>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
        <?= sanitize($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-plus-circle"></i> Adicionar Membro</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Função</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Selecione...</option>
                            <option value="CLOSER">Closer</option>
                            <option value="SDR">SDR</option>
                            <option value="SSELL">Social Seller</option>
                            <option value="CS">Customer Success</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save"></i> Adicionar
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-list"></i> Membros da Equipe</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Função</th>
                                <th>Status</th>
                                <th>Criado em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $member): ?>
                                <tr>
                                    <td><?= sanitize($member['name']) ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= sanitize($member['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($member['is_active']): ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($member['created_at'])) ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="id" value="<?= $member['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-primary" 
                                                    title="Ativar/Desativar">
                                                <i class="bi bi-toggle-<?= $member['is_active'] ? 'on' : 'off' ?>"></i>
                                            </button>
                                        </form>
                                        
                                        <form method="POST" class="d-inline" 
                                              onsubmit="return confirm('Tem certeza que deseja remover este membro?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $member['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Remover">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($members)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        Nenhum membro cadastrado
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '_footer.php'; ?>
```

---

## 🗄️ BANCO DE DADOS COMPLETO

```sql
-- UTM Generator Database Schema
-- Version: 2.1
-- Date: 2026-02-06

CREATE DATABASE IF NOT EXISTS utm_generator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE utm_generator;

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    is_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: utms
CREATE TABLE IF NOT EXISTS utms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    original_url TEXT NOT NULL,
    short_code VARCHAR(50) NOT NULL UNIQUE,
    utm_campaign VARCHAR(100),
    utm_source VARCHAR(50),
    utm_medium VARCHAR(100),
    utm_content VARCHAR(200),
    utm_term TEXT,
    click_count INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_short_code (short_code),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_active (is_active),
    FULLTEXT idx_search (utm_campaign, utm_source, utm_medium, utm_content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: clicks
CREATE TABLE IF NOT EXISTS clicks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utm_id INT NOT NULL,
    clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    referer TEXT,
    country VARCHAR(2),
    city VARCHAR(100),
    FOREIGN KEY (utm_id) REFERENCES utms(id) ON DELETE CASCADE,
    INDEX idx_utm_id (utm_id),
    INDEX idx_clicked_at (clicked_at),
    INDEX idx_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: team_members
CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role ENUM('CLOSER', 'SDR', 'SSELL', 'CS') NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: login_attempts
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    email VARCHAR(100),
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: settings
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
-- Password: admin123 (CHANGE THIS IN PRODUCTION!)
INSERT INTO users (username, email, password, role, is_approved) VALUES
('admin', 'admin@salesprime.com.br', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY9IiBnQqxd6y.2', 'admin', 1);

-- Insert some sample team members
INSERT INTO team_members (name, role, is_active) VALUES
('João Silva', 'CLOSER', 1),
('Maria Santos', 'SDR', 1),
('Pedro Costa', 'SSELL', 1),
('Ana Lima', 'CS', 1);

-- Insert default settings
INSERT INTO settings (setting_key, setting_value) VALUES
('app_name', 'UTM Generator'),
('app_version', '2.1'),
('maintenance_mode', '0'),
('max_utms_per_user', '100');
```

---

## ⚡ JAVASCRIPT MODULAR

**utm-generator.js** (Módulo principal):
```javascript
/**
 * UTM Generator Module
 * Handles all UTM generation logic
 */

class UTMGenerator {
    constructor() {
        this.mediumMap = {
            ig: ['INSTAGRAM_FEED', 'INSTAGRAM_STORIES', 'INSTAGRAM_REELS', 'INSTAGRAM_BIO', 'INSTAGRAM_DIRECT'],
            yt: ['YOUTUBE_DESCRICAO', 'YOUTUBE_CARD', 'YOUTUBE_BIO', 'YOUTUBE_COMUNIDADE', 'YOUTUBE_VIDEO'],
            in: ['LINKEDIN_POST', 'LINKEDIN_ARTIGO', 'LINKEDIN_BIO', 'LINKEDIN_INMAIL'],
            tktk: ['TIKTOK_BIO', 'TIKTOK_VIDEO', 'TIKTOK_COMENTARIO'],
            thrd: ['THREADS_POST', 'THREADS_BIO', 'THREADS_RESPOSTA'],
            spot: ['SPOTIFY_DESCRICAO', 'SPOTIFY_BIO', 'SPOTIFY_PLAYLIST'],
            wpp: ['WHATSAPP_STATUS', 'WHATSAPP_LISTA', 'WHATSAPP_CATALOGO', 'WHATSAPP_BIO'],
            appl: ['APPLE_DESCRICAO', 'APPLE_BIO'],
            amz: ['AMAZON_DESCRICAO', 'AMAZON_BIO'],
            dzr: ['DEEZER_DESCRICAO', 'DEEZER_BIO', 'DEEZER_PLAYLIST'],
            email: ['EMAIL_MARKETING', 'EMAIL_TRANSACIONAL', 'EMAIL_NEWSLETTER'],
            site: ['SITE_BANNER', 'SITE_POPUP', 'SITE_FOOTER', 'SITE_MENU']
        };
        
        this.init();
    }
    
    init() {
        this.attachEventListeners();
        this.loadFromLocalStorage();
    }
    
    attachEventListeners() {
        // Source change
        document.querySelectorAll('input[name="source"]').forEach(radio => {
            radio.addEventListener('change', (e) => this.handleSourceChange(e.target.value));
        });
        
        // Origin type change
        document.querySelectorAll('input[name="origem"]').forEach(radio => {
            radio.addEventListener('change', (e) => this.handleOriginChange(e.target.value));
        });
        
        // Offline media type
        const offlineSelect = document.getElementById('offlineType');
        if (offlineSelect) {
            offlineSelect.addEventListener('change', (e) => this.handleOfflineChange(e.target.value));
        }
        
        // Form submission
        const form = document.getElementById('utmForm');
        if (form) {
            form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
        
        // Real-time preview
        form?.addEventListener('input', debounce(() => this.updatePreview(), 300));
    }
    
    handleSourceChange(source) {
        const mediumSelect = document.getElementById('mediumSelect');
        if (!mediumSelect) return;
        
        mediumSelect.innerHTML = '<option value="">Selecione o Medium</option>';
        
        if (this.mediumMap[source]) {
            this.mediumMap[source].forEach(medium => {
                const option = document.createElement('option');
                option.value = medium;
                option.textContent = medium.replace(/_/g, ' ');
                mediumSelect.appendChild(option);
            });
            mediumSelect.disabled = false;
        } else {
            mediumSelect.disabled = true;
        }
        
        this.updatePreview();
    }
    
    handleOriginChange(origin) {
        const sourceGroup = document.getElementById('sourceGroup');
        const mediumGroup = document.getElementById('mediumGroup');
        const offlineGroup = document.getElementById('offlineGroup');
        const teamGroup = document.getElementById('teamGroup');
        
        // Hide all optional groups
        [sourceGroup, mediumGroup, offlineGroup, teamGroup].forEach(el => {
            if (el) el.classList.add('d-none');
        });
        
        // Show relevant groups based on origin
        if (['TP', 'TO', 'SEM'].includes(origin)) {
            sourceGroup?.classList.remove('d-none');
            mediumGroup?.classList.remove('d-none');
        } else if (origin === 'MO') {
            offlineGroup?.classList.remove('d-none');
        } else if (['COMERCIAL', 'SDR', 'SSELL', 'CS'].includes(origin)) {
            teamGroup?.classList.remove('d-none');
        }
        
        this.updatePreview();
    }
    
    handleOfflineChange(type) {
        const moNameContainer = document.getElementById('moNameContainer');
        const sourceGroup = document.getElementById('sourceGroup');
        const mediumGroup = document.getElementById('mediumGroup');
        
        if (['PALESTRA', 'EVENTO', 'WEBINAR'].includes(type)) {
            moNameContainer?.classList.add('d-none');
            sourceGroup?.classList.add('d-none');
            mediumGroup?.classList.add('d-none');
        } else {
            moNameContainer?.classList.remove('d-none');
        }
        
        this.updatePreview();
    }
    
    handleSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);
        
        // Validate
        if (!this.validate(data)) {
            return false;
        }
        
        // Save to localStorage
        this.saveToLocalStorage(data);
        
        // Submit via AJAX
        this.submitUTM(data);
    }
    
    validate(data) {
        if (!data.url || !this.isValidURL(data.url)) {
            this.showError('URL inválida');
            return false;
        }
        
        if (!data.short_code || !/^[a-zA-Z0-9_-]{3,20}$/.test(data.short_code)) {
            this.showError('Short code deve ter 3-20 caracteres alfanuméricos');
            return false;
        }
        
        if (!data.utm_campaign) {
            this.showError('Selecione um canal');
            return false;
        }
        
        return true;
    }
    
    isValidURL(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
    
    updatePreview() {
        const form = document.getElementById('utmForm');
        if (!form) return;
        
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        const url = this.buildURL(data);
        const preview = document.getElementById('urlPreview');
        
        if (preview) {
            preview.textContent = url;
        }
    }
    
    buildURL(data) {
        if (!data.url) return '';
        
        const url = new URL(data.url);
        const params = new URLSearchParams(url.search);
        
        if (data.utm_campaign) params.set('utm_campaign', data.utm_campaign);
        if (data.utm_source) params.set('utm_source', data.utm_source);
        if (data.utm_medium) params.set('utm_medium', data.utm_medium);
        if (data.utm_content) params.set('utm_content', data.utm_content);
        if (data.utm_term) params.set('utm_term', data.utm_term);
        
        return `${url.origin}${url.pathname}?${params.toString()}`;
    }
    
    async submitUTM(data) {
        try {
            const response = await fetch('api/create_utm.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showSuccess('UTM criada com sucesso!');
                this.resetForm();
            } else {
                this.showError(result.message || 'Erro ao criar UTM');
            }
        } catch (error) {
            this.showError('Erro de conexão');
            console.error(error);
        }
    }
    
    showSuccess(message) {
        this.showAlert(message, 'success');
    }
    
    showError(message) {
        this.showAlert(message, 'danger');
    }
    
    showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.container-fluid');
        container?.insertBefore(alertDiv, container.firstChild);
        
        setTimeout(() => alertDiv.remove(), 5000);
    }
    
    saveToLocalStorage(data) {
        localStorage.setItem('lastUTM', JSON.stringify(data));
    }
    
    loadFromLocalStorage() {
        const saved = localStorage.getItem('lastUTM');
        if (saved) {
            // Could populate form with last values
            console.log('Last UTM:', JSON.parse(saved));
        }
    }
    
    resetForm() {
        document.getElementById('utmForm')?.reset();
        this.updatePreview();
    }
}

// Utility: Debounce
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.utmGenerator = new UTMGenerator();
});
```

---

## 📱 RESPONSIVE UTILITIES

**styles.css** (Complementar ao Bootstrap):
```css
/**
 * UTM Generator - Custom Styles
 * Complementa o Bootstrap 5.3
 */

/* Variables */
:root {
    --transition-speed: 0.3s;
    --border-radius: 0.5rem;
    --box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Dark Mode Variables */
[data-bs-theme="dark"] {
    --box-shadow: 0 0.125rem 0.25rem rgba(255, 255, 255, 0.075);
}

/* Global */
body {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.container-fluid {
    flex: 1;
}

/* Cards */
.card {
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    transition: transform var(--transition-speed), box-shadow var(--transition-speed);
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

/* Buttons */
.btn {
    transition: all var(--transition-speed);
}

.btn-group > .btn-check:checked + .btn {
    transform: scale(1.05);
}

/* Source Icons (Icon-only buttons) */
.source-icons .btn {
    font-size: 1.5rem;
    padding: 0.75rem 1rem;
    min-width: 60px;
}

@media (max-width: 768px) {
    .source-icons .btn {
        font-size: 1.25rem;
        padding: 0.5rem 0.75rem;
        min-width: 50px;
    }
}

/* UTM Preview */
#urlPreview {
    word-break: break-all;
    font-size: 0.875rem;
    max-height: 200px;
    overflow-y: auto;
}

/* Table Responsive */
.table-responsive {
    border-radius: var(--border-radius);
}

/* Badges */
.badge {
    font-weight: 500;
    padding: 0.35em 0.65em;
}

/* Loading Spinner */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 0.15rem;
}

/* Alert Auto-dismiss Animation */
@keyframes slideInDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.alert {
    animation: slideInDown 0.3s ease-out;
}

/* Footer */
.footer {
    margin-top: auto;
    border-top: 1px solid var(--bs-border-color);
}

/* Mobile Optimizations */
@media (max-width: 576px) {
    .container-fluid {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
    
    h1, h2 {
        font-size: 1.5rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .btn-group {
        flex-wrap: wrap;
    }
}

/* Print Styles */
@media print {
    .navbar,
    .footer,
    .btn,
    .alert {
        display: none !important;
    }
    
    .card {
        box-shadow: none;
        border: 1px solid #000;
    }
}

/* Accessibility */
.visually-hidden-focusable:not(:focus):not(:focus-within) {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    padding: 0 !important;
    margin: -1px !important;
    overflow: hidden !important;
    clip: rect(0, 0, 0, 0) !important;
    white-space: nowrap !important;
    border: 0 !important;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}

::-webkit-scrollbar-track {
    background: var(--bs-secondary-bg);
}

::-webkit-scrollbar-thumb {
    background: var(--bs-border-color);
    border-radius: 5px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--bs-primary);
}
```

---

## 🎯 DEPLOY CHECKLIST

### Pré-Deploy

- [ ] Backup do banco de dados
- [ ] Testar em ambiente local
- [ ] Validar todos os formulários
- [ ] Testar dark/light mode
- [ ] Testar responsividade (mobile/tablet/desktop)
- [ ] Revisar código de segurança
- [ ] Atualizar DOCUMENTATION.md

### Deploy

- [ ] Criar banco de dados em produção
- [ ] Importar schema SQL
- [ ] Upload de arquivos PHP
- [ ] Upload de assets (CSS/JS)
- [ ] Configurar config.php (credenciais corretas)
- [ ] Ajustar permissões de arquivos (755 para diretórios, 644 para arquivos)
- [ ] Criar usuário admin
- [ ] Testar login/logout

### Pós-Deploy

- [ ] Monitorar logs de erro
- [ ] Testar funcionalidades críticas
- [ ] Validar HTTPS (se aplicável)
- [ ] Configurar backup automático
- [ ] Documentar processo de deploy

---

## 📞 SUPORTE RÁPIDO

### Problemas Comuns

**1. Erro de conexão com banco de dados**
```php
// Verificar config.php
echo "Host: " . DB_HOST . "<br>";
echo "Database: " . DB_NAME . "<br>";
// Testar conexão
try {
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    echo "Conexão OK!";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
```

**2. Sessão não persiste**
```php
// Verificar se sessão está iniciada
var_dump(session_status() === PHP_SESSION_ACTIVE); // deve ser true
var_dump($_SESSION); // deve conter dados do usuário
```

**3. JavaScript não funciona**
```javascript
// Abrir console do navegador (F12)
console.log('UTM Generator carregado');
console.log(window.utmGenerator); // deve existir
```

**4. Dark mode não alterna**
```javascript
// Verificar tema atual
console.log(document.documentElement.getAttribute('data-bs-theme'));
// Forçar mudança
document.documentElement.setAttribute('data-bs-theme', 'dark');
```

---

**Guia criado em:** 06 de Fevereiro de 2026  
**Tempo estimado de implementação:** 4-8 horas (MVP completo)  
**Próxima revisão:** Conforme necessário
