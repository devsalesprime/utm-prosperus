/**
 * UTM Generator Module v2.2
 * Implementa Strategy + Observer Patterns
 * Complementar ao script.js existente
 */

// ============================================
// Utility: Debounce
// ============================================
const debounce = (func, wait = 300) => {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
};

// ============================================
// Observer Pattern
// ============================================
class FormObserver {
  constructor() {
    this.observers = [];
  }

  subscribe(fn) {
    this.observers.push(fn);
    return this; // encadeamento
  }

  notify(data) {
    this.observers.forEach(fn => {
      try {
        fn(data);
      } catch (e) {
        console.error('[UTMGenerator] Observer error:', e);
      }
    });
  }
}

// ============================================
// Strategy Pattern - Geração de utm_content
// ============================================
const utmStrategies = {
  simple: (type) => ({
    content: type,
    hideSourceMedium: type === 'WEBINAR'
  }),

  team: (name, role) => ({
    content: name ? `${name}_${role}` : '',
    hideSourceMedium: false
  }),

  offline: (type, name) => ({
    content: name ? `MO_${type}_${name}` : `MO_${type}`,
    hideSourceMedium: ['PALESTRA', 'EVENTO', 'WEBINAR'].includes(type)
  }),

  webinar: () => ({
    content: 'WEBINAR',
    hideSourceMedium: true
  })
};

// ============================================
// UTMGenerator Class
// ============================================
class UTMGenerator {
  constructor() {
    this.mediumMap = {
      ig: ['INSTAGRAM_FEED', 'INSTAGRAM_STORIES', 'INSTAGRAM_REELS', 'INSTAGRAM_BIO', 'INSTAGRAM_DIRECT'],
      yt: ['YOUTUBE_DESCRICAO', 'YOUTUBE_CARD', 'YOUTUBE_BIO', 'YOUTUBE_COMUNIDADE', 'YOUTUBE_VIDEO'],
      in: ['LINKEDIN_POST', 'LINKEDIN_ARTIGO', 'LINKEDIN_BIO', 'LINKEDIN_INMAIL'],
      tktk: ['TIKTOK_BIO', 'TIKTOK_REDE_ORIGEM'],
      thrd: ['THREADS_BIO'],
      spot: ['SPOTIFY_VIDEO', 'SPOTIFY_DESCRICAO'],
      wpp: ['WHATSAPP_MENSAGEM'],
      appl: ['APPLE_DESCRICAO'],
      amz: ['AMAZON_DESCRICAO'],
      dzr: ['DEEZER_DESCRICAO'],
      email: ['EMAIL_MARKETING', 'EMAIL_TRANSACIONAL', 'EMAIL_NEWSLETTER'],
      site: ['SITE_BANNER', 'SITE_POPUP', 'SITE_FOOTER', 'SITE_MENU']
    };

    this.sourceAliases = {
      linkd: 'in',
      wtt: 'wpp'
    };

    // Observer para atualizações do formulário
    this.formState = new FormObserver();
    this.formState
      .subscribe((data) => this.updateMediumOptions(data))
      .subscribe((data) => this.updateContentField(data))
      .subscribe((data) => this.generatePreview(data));

    this.init();
  }

  init() {
    this.attachPreviewListeners();
    this.loadFromLocalStorage();
    console.log('[UTMGenerator] Module initialized v2.2');
  }

  // ============================================
  // Preview em tempo real com debounce
  // ============================================
  attachPreviewListeners() {
    const form = document.getElementById('utmForm');
    if (!form) return;

    // Debounce de 300ms para preview
    const debouncedPreview = debounce(() => this.updatePreview(), 300);

    // Ouvir mudanças em todos os inputs do formulário
    form.addEventListener('input', debouncedPreview);
    form.addEventListener('change', debouncedPreview);
  }

  // ============================================
  // Atualizar seletor de Medium baseado no Source
  // ============================================
  updateMediumOptions(data) {
    if (!data || !data.source) return;

    const mediumSelect = document.getElementById('utm_medium');
    if (!mediumSelect) return;

    const key = this.sourceAliases[data.source] || data.source;
    const options = this.mediumMap[key];

    if (!options) return;

    // Não recriar se as opções já estão corretas
    const currentOptions = Array.from(mediumSelect.options).map(o => o.value);
    const newOptions = [...options, 'OUTRO'];
    if (JSON.stringify(currentOptions) === JSON.stringify(newOptions)) return;
  }

  // ============================================
  // Atualizar campo de content via Strategy
  // ============================================
  updateContentField(data) {
    if (!data || !data.origin) return;
    // Delegado ao script.js existente por enquanto
  }

  // ============================================
  // Preview da URL gerada
  // ============================================
  generatePreview(data) {
    this.updatePreview();
  }

  updatePreview() {
    const form = document.getElementById('utmForm');
    if (!form) return;

    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    const preview = document.getElementById('urlPreview');
    if (!preview) return;

    const baseUrl = data.website_url || data.url || '';
    const campaign = data.utm_campaign || '';
    const source = data.utm_source || '';
    let medium = data.utm_medium || '';
    if (medium === 'OUTRO') medium = data.utm_medium_custom || '';
    const content = data.utm_content || '';
    const term = data.utm_term || '';

    // If no URL yet, show placeholder
    if (!baseUrl) {
      preview.innerHTML = '<span class="text-muted small">Preencha os campos para visualizar a URL...</span>';
      return;
    }

    // Build badge HTML
    const badges = [];

    // Base URL badge (always dark)
    let displayUrl = baseUrl;
    try { displayUrl = new URL(baseUrl).hostname; } catch(e) {}
    badges.push(`<span class="utm-badge utm-badge-url" title="URL de destino">${this.escapeHtml(displayUrl)}</span>`);

    if (campaign) {
      badges.push(`<span class="utm-badge utm-badge-campaign" title="utm_campaign"><span class="utm-badge-label">camp</span> ${this.escapeHtml(campaign)}</span>`);
    }
    if (source) {
      badges.push(`<span class="utm-badge utm-badge-source" title="utm_source"><span class="utm-badge-label">src</span> ${this.escapeHtml(source)}</span>`);
    }
    if (medium) {
      badges.push(`<span class="utm-badge utm-badge-medium" title="utm_medium"><span class="utm-badge-label">med</span> ${this.escapeHtml(medium)}</span>`);
    }
    if (content) {
      badges.push(`<span class="utm-badge utm-badge-content" title="utm_content"><span class="utm-badge-label">cont</span> ${this.escapeHtml(content)}</span>`);
    }
    if (term) {
      badges.push(`<span class="utm-badge utm-badge-term" title="utm_term"><span class="utm-badge-label">term</span> ${this.escapeHtml(term)}</span>`);
    }

    preview.innerHTML = badges.join('<span class="utm-badge-separator">•</span>');
  }

  escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
  }

  // ============================================
  // Construir URL com parâmetros UTM
  // ============================================
  buildURL(data) {
    const baseUrl = data.website_url || data.url || '';
    if (!baseUrl) return '';

    try {
      const url = new URL(baseUrl);
      const params = new URLSearchParams(url.search);

      // Obter campaign formatado
      const campaign = data.utm_campaign || '';
      const content = data.utm_content || '';
      const source = data.utm_source || '';
      const term = data.utm_term || '';

      // Obter medium (pode ser do select ou custom)
      let medium = data.utm_medium || '';
      if (medium === 'OUTRO') {
        medium = data.utm_medium_custom || '';
      }

      // Montar parâmetros
      if (campaign) params.set('utm_campaign', campaign);
      if (source) params.set('utm_source', source);
      if (medium) params.set('utm_medium', medium);
      if (content) params.set('utm_content', content);
      if (term) params.set('utm_term', term);

      const result = `${url.origin}${url.pathname}?${params.toString()}`;
      // Preservar colchetes
      return result.replace(/%5B/gi, '[').replace(/%5D/gi, ']');
    } catch (e) {
      return baseUrl; // URL inválida, retornar como está
    }
  }

  // ============================================
  // Validações Client-Side
  // ============================================
  validate(data) {
    const errors = [];

    // Validar URL
    if (!data.website_url && !data.url) {
      errors.push('URL é obrigatória');
    } else if (!this.isValidURL(data.website_url || data.url)) {
      errors.push('URL inválida');
    }

    // Validar short code (se preenchido)
    const shortCode = data.custom_name || data.short_code || '';
    if (shortCode && !/^[a-zA-Z0-9_-]{3,20}$/.test(shortCode)) {
      errors.push('Short code deve ter 3-20 caracteres (letras, números, hífens ou underscores)');
    }

    // Validar campaign
    if (!data.utm_campaign) {
      errors.push('Selecione um canal (campaign)');
    }

    // Validar utm_term
    const term = data.utm_term || '';
    if (term.length > 500) {
      errors.push('utm_term muito longo (máximo 500 caracteres)');
    }

    return {
      valid: errors.length === 0,
      errors
    };
  }

  isValidURL(string) {
    try {
      new URL(string);
      return true;
    } catch (_) {
      return false;
    }
  }

  // ============================================
  // Submit via AJAX (API)
  // ============================================
  async submitUTM(data) {
    try {
      const response = await fetch('api/create_utm.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });

      const result = await response.json();

      if (result.success) {
        this.showSuccess(result.message || 'UTM criada com sucesso!');
        this.saveToLocalStorage(data);
        return result;
      } else {
        this.showError(result.message || 'Erro ao criar UTM');
        return null;
      }
    } catch (error) {
      this.showError('Erro de conexão com o servidor');
      console.error('[UTMGenerator] Submit error:', error);
      return null;
    }
  }

  // ============================================
  // Feedback Visual (Toasts)
  // ============================================
  showSuccess(message) {
    this.showAlert(message, 'success');
  }

  showError(message) {
    this.showAlert(message, 'danger');
  }

  showAlert(message, type = 'info') {
    // Usar toast system se disponível
    if (typeof showToast === 'function') {
      showToast(message, type === 'danger' ? 'error' : type);
      return;
    }

    // Fallback: alert Bootstrap
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.setAttribute('role', 'alert');

    const icons = {
      success: 'bi-check-circle-fill',
      danger: 'bi-x-circle-fill',
      warning: 'bi-exclamation-triangle-fill',
      info: 'bi-info-circle-fill'
    };

    alertDiv.innerHTML = `
      <i class="bi ${icons[type] || icons.info}"></i>
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    const container = document.querySelector('.container') || document.querySelector('.container-fluid');
    if (container) {
      container.insertBefore(alertDiv, container.firstChild);
      setTimeout(() => alertDiv.remove(), 5000);
    }
  }

  // ============================================
  // LocalStorage
  // ============================================
  saveToLocalStorage(data) {
    try {
      localStorage.setItem('lastUTM', JSON.stringify(data));
    } catch (e) {
      console.warn('[UTMGenerator] LocalStorage save failed:', e);
    }
  }

  loadFromLocalStorage() {
    try {
      const saved = localStorage.getItem('lastUTM');
      if (saved) {
        this.lastUTM = JSON.parse(saved);
      }
    } catch (e) {
      // Silencioso
    }
  }

  // ============================================
  // Método estático de utilidade: Strategy
  // ============================================
  static getStrategy(type) {
    return utmStrategies[type] || utmStrategies.simple;
  }
}

// ============================================
// Export CSV via navegador
// ============================================
function exportCSV() {
  window.location.href = 'export.php';
}

// ============================================
// Verificar disponibilidade de short code (AJAX)
// ============================================
const checkShortCodeAvailability = debounce(async (code) => {
  if (!code || code.length < 3) return;

  try {
    const response = await fetch('api/check_shortcode.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ short_code: code })
    });
    const result = await response.json();

    const input = document.getElementById('custom_name');
    if (!input) return;

    input.classList.remove('is-valid', 'is-invalid');
    if (result.available) {
      input.classList.add('is-valid');
    } else {
      input.classList.add('is-invalid');
    }
  } catch (e) {
    // Silencioso - verificação é opcional
  }
}, 500);

// ============================================
// Inicialização
// ============================================
document.addEventListener('DOMContentLoaded', () => {
  window.utmGenerator = new UTMGenerator();
});
