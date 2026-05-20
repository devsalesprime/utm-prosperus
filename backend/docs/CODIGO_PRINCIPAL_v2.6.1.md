# 📝 CÓDIGO PRINCIPAL - SELETOR DE DOMÍNIO

**Referência Rápida dos Snippets Implementados**

---

## 1️⃣ HTML - Seletor Visual (index.php, linhas 209-240)

```html
<!-- Seletor de Domínio - PARTE 2.1 Multi-Domínio Support -->
<div class="container mt-4 mb-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm domain-selector-card">
                <div class="card-body">
                    <label class="form-label fw-bold d-block mb-3">
                        <i class="bi bi-globe me-2"></i>Selecione o Domínio
                    </label>
                    <div class="btn-group w-100" role="group" aria-label="Seletor de Domínio">
                        <input type="radio" class="btn-check" name="domain-selector" id="domain-salesprime" 
                            value="salesprime.com.br" checked data-logo-light="images/logo_sales_prime.png" 
                            data-logo-dark="images/logo-dark.png" data-brand="Sales Prime" data-color="#0D6EFD">
                        <label class="btn btn-outline-primary" for="domain-salesprime">
                            <i class="bi bi-building me-2"></i>Sales Prime
                        </label>

                        <input type="radio" class="btn-check" name="domain-selector" id="domain-prosperus" 
                            value="prosperusclub.com.br" data-logo-light="images/logo_prosperus_club.png" 
                            data-logo-dark="images/logo-dark.png" data-brand="Prosperus Club" data-color="#FFC107">
                        <label class="btn btn-outline-warning" for="domain-prosperus">
                            <i class="bi bi-star me-2"></i>Prosperus Club
                        </label>
                    </div>
                    <small class="text-muted d-block mt-2 text-center">
                        <i class="bi bi-info-circle me-1"></i>
                        Todas as UTMs serão geradas para o domínio selecionado
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

## 2️⃣ CSS - Styling Completo (assets/css/domain-selector.css)

```css
/* ========================================
   DOMAIN SELECTOR STYLES - Multi-Domínio
   Parte 2.6.1 CSS Refinado
   ======================================== */

.domain-selector-card {
    border: 2px solid #0D6EFD !important;
    border-radius: 12px;
    padding: 2rem;
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.05) 0%, rgba(13, 110, 253, 0.02) 100%);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.domain-selector-card:hover {
    box-shadow: 0 0.75rem 1.5rem rgba(13, 110, 253, 0.15);
    transform: translateY(-2px);
}

.domain-selector-card .form-label {
    font-size: 1.1rem;
    color: #3D4F73;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.domain-selector-card .btn-check:checked + .btn {
    font-weight: 600;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
    transform: scale(1.02);
}

/* Animação de Seleção */
@keyframes domainSelectPulse {
    0% {
        transform: scale(1);
        box-shadow: none;
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 0 0 8px rgba(13, 110, 253, 0.15);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 0.5rem 1rem rgba(13, 110, 253, 0.15);
    }
}

.domain-selector-card .btn-outline-primary:checked,
.domain-selector-card .btn-outline-warning:checked {
    animation: domainSelectPulse 0.4s ease-out;
}

/* Sales Prime */
.domain-selector-card #domain-salesprime:checked + .btn-outline-primary {
    background-color: #0D6EFD;
    border-color: #0D6EFD;
    color: white;
    box-shadow: 0 0.5rem 1rem rgba(13, 110, 253, 0.3);
}

/* Prosperus Club */
.domain-selector-card #domain-prosperus:checked + .btn-outline-warning {
    background-color: #FFC107;
    border-color: #FFC107;
    color: #212529;
    box-shadow: 0 0.5rem 1rem rgba(255, 193, 7, 0.3);
}

.domain-selector-card .btn-outline-primary:hover {
    background-color: #0D6EFD;
    border-color: #0D6EFD;
    color: white;
    transform: translateY(-2px);
}

.domain-selector-card .btn-outline-warning:hover {
    background-color: #FFC107;
    border-color: #FFC107;
    color: #212529;
    transform: translateY(-2px);
}

.domain-selector-card small {
    font-size: 0.85rem;
    display: block;
    margin-top: 1rem;
    padding: 0.75rem;
    background: rgba(13, 110, 253, 0.08);
    border-radius: 6px;
    color: #495057;
    border-left: 3px solid #0D6EFD;
}

.domain-selector-card .bi {
    font-size: 1.2rem;
    margin-right: 0.25rem;
}

/* Responsivo */
@media (max-width: 768px) {
    .domain-selector-card {
        padding: 1.5rem;
        margin: 1rem auto;
    }
    .domain-selector-card .form-label {
        font-size: 0.95rem;
    }
    .domain-selector-card .btn-group {
        flex-direction: column;
        gap: 0.5rem;
    }
    .domain-selector-card .btn {
        width: 100%;
    }
}

/* Dark Mode Refinado */
body.dark-theme .domain-selector-card {
    border-color: #4D9EFF;
    background: linear-gradient(135deg, rgba(77, 158, 255, 0.1) 0%, rgba(77, 158, 255, 0.05) 100%);
}

body.dark-theme .domain-selector-card .form-label {
    color: #E0E7FF;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

body.dark-theme .domain-selector-card .btn-outline-primary {
    color: #B0C4FF;
    border-color: #4D9EFF;
}

body.dark-theme .domain-selector-card .btn-outline-primary:hover {
    background-color: #0D6EFD;
    border-color: #0D6EFD;
    color: #FFFFFF;
}

body.dark-theme .domain-selector-card #domain-salesprime:checked + .btn-outline-primary {
    background-color: #0D6EFD;
    border-color: #0D6EFD;
    color: #FFFFFF;
    box-shadow: 0 0.5rem 1.5rem rgba(13, 110, 253, 0.4);
}

body.dark-theme .domain-selector-card .btn-outline-warning {
    color: #FFCC00;
    border-color: #FFB300;
}

body.dark-theme .domain-selector-card .btn-outline-warning:hover {
    background-color: #FFC107;
    border-color: #FFC107;
    color: #1A1A1A;
}

body.dark-theme .domain-selector-card #domain-prosperus:checked + .btn-outline-warning {
    background-color: #FFC107;
    border-color: #FFC107;
    color: #1A1A1A;
    box-shadow: 0 0.5rem 1.5rem rgba(255, 193, 7, 0.4);
}

body.dark-theme .domain-selector-card small {
    background: rgba(77, 158, 255, 0.2);
    color: #D0D8E8;
    border-left-color: #4D9EFF;
}

/* Transição de Logo */
#logo-light, #logo-dark {
    opacity: 1;
    transition: opacity 0.3s ease-in-out;
}
```

---

## 3️⃣ JavaScript - Interatividade (script.js, linhas 648-747)

```javascript
/**
 * Inicializar Seletor de Domínio
 * - Renderiza radio buttons
 * - Escuta mudanças
 * - Alterna logo dinamicamente
 * - Salva seleção no localStorage
 * - Atualiza campo hidden do formulário
 */
const initDomainSelector = () => {
  const domainRadios = document.querySelectorAll('input[name="domain-selector"]');
  const domainField = document.getElementById('domain-field');
  const logoLight = document.getElementById('logo-light');
  const logoDark = document.getElementById('logo-dark');
  const themeSwitchContainer = document.querySelector('.theme-switch-container');

  if (!domainRadios.length || !domainField) return;

  // Restaurar seleção anterior do localStorage
  const savedDomain = localStorage.getItem('selected-domain') || 'salesprime.com.br';
  const savedRadio = document.getElementById(
    savedDomain === 'salesprime.com.br' ? 'domain-salesprime' : 'domain-prosperus'
  );
  
  if (savedRadio) {
    savedRadio.checked = true;
    domainField.value = savedDomain;
    updateDomainVisuals(savedRadio);
  }

  // Listener para mudanças no seletor
  domainRadios.forEach(radio => {
    radio.addEventListener('change', function() {
      // Atualizar campo hidden do formulário
      domainField.value = this.value;
      
      // Salvar seleção no localStorage
      localStorage.setItem('selected-domain', this.value);
      
      // Atualizar visuais (logo, detalhes) - SPA sem reload
      updateDomainVisuals(this);
    });
  });

  /**
   * Função para atualizar visuais do domínio selecionado
   * Troca logo e aplica efeitos visuais sutis
   */
  function updateDomainVisuals(radio) {
    const brand = radio.dataset.brand;
    const isDarkMode = themeSwitchContainer?.classList.contains('dark-active');
    
    // Obter logo apropriada (light ou dark)
    const logoSrc = isDarkMode ? radio.dataset.logoDark : radio.dataset.logoLight;
    
    // Trocar logo com transição suave
    if (logoLight && logoDark && logoSrc) {
      // Detectar qual logo usar baseado no tema
      if (logoSrc.includes('light')) {
        logoLight.src = logoSrc;
        logoLight.style.opacity = '0';
        setTimeout(() => {
          logoLight.style.transition = 'opacity 0.3s ease-in-out';
          logoLight.style.opacity = '1';
        }, 10);
      } else {
        logoDark.src = logoSrc;
        logoDark.style.opacity = '0';
        setTimeout(() => {
          logoDark.style.transition = 'opacity 0.3s ease-in-out';
          logoDark.style.opacity = '1';
        }, 10);
      }
    }

    // Efeito visual sutil: mudar cor de destaque do seletor
    const selectorCard = document.querySelector('.domain-selector-card');
    if (selectorCard) {
      selectorCard.style.transition = 'all 0.3s ease-in-out';
      
      if (brand === 'Prosperus Club') {
        selectorCard.style.borderColor = '#FFC107';
        selectorCard.style.boxShadow = '0 0.5rem 1rem rgba(255, 193, 7, 0.15)';
      } else {
        selectorCard.style.borderColor = '#0D6EFD';
        selectorCard.style.boxShadow = '0 0.5rem 1rem rgba(13, 110, 253, 0.15)';
      }
    }

    console.log(`[UTM Generator] Domínio alterado para: ${brand} (${radio.value})`);
  }

  // Hook: quando o tema é alterado, atualizar logo do domínio selecionado
  const originalInitTheme = window.initTheme;
  if (originalInitTheme) {
    window.initTheme = function(...args) {
      originalInitTheme.apply(this, args);
      setTimeout(() => {
        const activeRadio = document.querySelector('input[name="domain-selector"]:checked');
        if (activeRadio) updateDomainVisuals(activeRadio);
      }, 100);
    };
  }
};

window.addEventListener('DOMContentLoaded', initDomainSelector);
```

---

## 4️⃣ PHP Backend - Validação (generate.php)

```php
/**
 * PARTE 3 - Validar permissão de domínio do usuário
 * Retorna o domínio permitido ou o default (salesprime.com.br)
 */
function validateUserDomainPermission($pdo, $userId, $requestedDomain)
{
    // Lista de domínios permitidos (whitelist)
    $allowedDomains = ['salesprime.com.br', 'prosperusclub.com.br'];
    
    // Se o domínio solicitado não está na whitelist, usar default
    if (!in_array($requestedDomain, $allowedDomains)) {
        $requestedDomain = 'salesprime.com.br';
    }

    // Verificar se o usuário tem permissões para este domínio
    try {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) as has_permission 
             FROM user_domain_permissions udp
             JOIN domains d ON udp.domain_id = d.id
             WHERE udp.user_id = ? AND d.domain_url = ? AND udp.can_create = 1"
        );
        $stmt->execute([$userId, $requestedDomain]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se tem permissão explícita, usar o domínio solicitado
        if ($result['has_permission'] > 0) {
            return $requestedDomain;
        }

        // Se não tem permissão, usar o default (salesprime)
        return 'salesprime.com.br';
    } catch (Exception $e) {
        // Se houver erro na query de permissões, usar default
        error_log("Domain permission check error: " . $e->getMessage());
        return 'salesprime.com.br';
    }
}

// === DENTRO DE if ($_SERVER['REQUEST_METHOD'] === 'POST') ===

$user_id = $_SESSION['user_id'] ?? null;

// Capturar e validar domínio do POST
$requestedDomain = trim($_POST['domain'] ?? 'salesprime.com.br');
$domain = validateUserDomainPermission($pdo, $user_id, $requestedDomain);

// ... outros validações ...

// === NA QUERY INSERT ===
$stmt = $pdo->prepare("INSERT INTO urls (original_url, long_url, shortened_url, username, comment, domain, qr_code_path) VALUES (?, ?, ?, ?, ?, ?, NULL)");

// Executar com $domain como parâmetro
$stmt->execute([$website_url, $long_url, $shortest_url, $username, $comment, $domain]);
```

---

## 5️⃣ PHP Backend - Histórico Dinâmico (index.php)

```php
<?php
// === QUERY DINAMICAMENTE SELECIONA DOMÍNIO ===
$query = $pdo->query("SELECT *, is_enabled, COALESCE(domain, 'salesprime.com.br') as domain FROM urls ORDER BY generation_date DESC");

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    // Usar o domínio do banco ou fallback para salesprime
    $domain = $row['domain'] ?? 'salesprime.com.br';
    
    // Gerar a URL do QR Code com o domínio correto
    $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=1500x1500&data=' . urlencode("https://{$domain}/utm/" . $row['shortened_url']);

    // ... restante do código usa $domain para exibir links corretos ...
    
    echo "<a href='https://" . htmlspecialchars($domain) . "/utm/" . $row['shortened_url'] . "'>";
}
?>
```

---

## 📊 RESUMO DE INTEGRAÇÕES

| Componente | Arquivo | Função | Status |
|-----------|---------|--------|--------|
| HTML | index.php | Renderiza card + radio buttons | ✅ |
| CSS | domain-selector.css | Styling + Dark Mode | ✅ |
| JS | script.js | Alternância dinâmica | ✅ |
| PHP | generate.php | Valida + salva | ✅ |
| PHP | index.php | Renderiza histórico | ✅ |

---

**Todos os snippets estão prontos para cópia/paste! 🚀**
