// Refatorado por desenvolvedor sênior - Versão v6

// Inicialização
const initTheme = () => {
  const themeSwitch = document.getElementById("themeSwitch");
  const body = document.body;
  const table = document.querySelector("table");
  const tableRows = document.querySelectorAll("table tbody tr");
  const links = document.querySelectorAll(".theme-link");
  const modals = document.querySelectorAll(".modal-content");
  const lightOption = themeSwitch.querySelector(".light-option");
  const darkOption = themeSwitch.querySelector(".dark-option");
  const inputs = document.querySelectorAll(".theme-input");

  const applyTheme = (isDark) => {
    themeSwitch.classList.toggle("dark-active", isDark);
    themeSwitch.classList.toggle("light-active", !isDark);
    lightOption.classList.toggle("active", !isDark);
    darkOption.classList.toggle("active", isDark);
    body.classList.toggle("bg-dark", isDark);
    body.classList.toggle("text-light", isDark);
    table?.classList.toggle("table-dark", isDark);

    tableRows.forEach(row => row.classList.toggle("text-light", isDark));

    inputs.forEach(input => {
      input.classList.toggle("dark-input", isDark);
      input.classList.toggle("dark-placeholder", isDark);
      input.classList.toggle("light-input", !isDark);
      input.classList.toggle("light-placeholder", !isDark);
    });

    links.forEach(link => {
      link.classList.toggle("dark-link", isDark);
      link.classList.toggle("light-link", !isDark);
    });

    modals.forEach(modal => {
      modal.classList.toggle("bg-dark", isDark);
      modal.classList.toggle("text-light", isDark);
    });

    localStorage.setItem("darkMode", isDark);
  };

  const isDark = localStorage.getItem("darkMode") === "true";
  applyTheme(isDark);

  lightOption.addEventListener("click", () => applyTheme(false));
  darkOption.addEventListener("click", () => applyTheme(true));
};

// Tooltips
const initTooltips = () => {
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
};

// Copiar texto
const copyToClipboard = (icon, text) => {
  navigator.clipboard.writeText(text).then(() => {
    const tooltip = bootstrap.Tooltip.getInstance(icon);
    tooltip.setContent({ ".tooltip-inner": "Copiado!" });
    tooltip.show();
    icon.classList.replace("bi-clipboard", "bi-clipboard-check");

    setTimeout(() => {
      tooltip.setContent({ ".tooltip-inner": "Copiar" });
      tooltip.hide();
      icon.classList.replace("bi-clipboard-check", "bi-clipboard");
    }, 2000);
  }).catch(err => console.error("Erro ao copiar: ", err));
};

// Função para sanitizar nome personalizado (mesma lógica do PHP)
const sanitizeCustomName = (name) => {
  if (!name) return '';
  
  name = name.trim();
  name = name.toLowerCase();
  name = name.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  name = name.replace(/\s+/g, '-');
  name = name.replace(/[^a-z0-9\-_]/g, '');
  name = name.replace(/-+/g, '-');
  name = name.replace(/^[-_]+|[-_]+$/g, '');
  name = name.substring(0, 50);
  
  return name;
};

// Normalização UPPERCASE, underscores, remove especiais
const normalizeForUtm = (text) => {
  if (!text) return '';
  text = text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  text = text.toUpperCase().trim();
  text = text.replace(/[^A-Z0-9\s\-_]/g, '');
  text = text.replace(/\s+/g, '_');
  text = text.replace(/__+/g, '_');
  text = text.replace(/^_+|_+$/g, '');
  return text;
};

// Map utm_source -> utm_medium options
const mediumMap = {
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

// Aliases from radio value to mediumMap key
const sourceAliases = {
  linkd: 'in',
  wtt: 'wpp'
};

// Populate utm_medium select based on selected utm_source
const populateMediumOptions = (sourceValue) => {
  const select = document.getElementById('utm_medium');
  const custom = document.getElementById('utm_medium_custom');
  if (!select) return;
  
  const key = sourceAliases[sourceValue] || sourceValue;
  const options = mediumMap[key];
  select.innerHTML = '';
  
  if (options && options.length) {
    options.forEach(opt => {
      const el = document.createElement('option');
      el.value = opt;
      el.textContent = opt;
      select.appendChild(el);
    });
    const out = document.createElement('option'); 
    out.value = 'OUTRO'; 
    out.textContent = 'Outro (personalizado)';
    select.appendChild(out);
    select.classList.remove('d-none');
    if (custom) custom.classList.add('d-none');
  } else {
    const el = document.createElement('option'); 
    el.value = 'OUTRO'; 
    el.textContent = 'Outro (personalizado)';
    select.appendChild(el);
    if (custom) custom.classList.remove('d-none');
  }
  updateUtmPreview();
};

// Update term placeholder based on source
const updateTermPlaceholder = (sourceValue) => {
  const term = document.getElementById('utm_term');
  if (!term) return;
  const mapping = {
    ig: 'Instagram: Data',
    yt: 'Youtube: Título ou Data',
    in: 'Linkedin: Título',
    spot: 'PodCast: Título ou Data',
    default: 'Ex: resumo_dia_00'
  };
  const key = sourceAliases[sourceValue] || sourceValue;
  term.placeholder = mapping[key] || mapping['default'];
};

// Atualiza valores ocultos
const updateUtmPreview = () => {
  // Função mantida para consistência
};

// Inicializa handlers para Origem / Content dinâmico
const initUtmContentHandlers = () => {
  const contentRadios = document.querySelectorAll('input[name="utm_content_select"]');
  if (!contentRadios.length) return;

  const containers = {
    COMM: document.getElementById('container_comm'),
    SDR: document.getElementById('container_sdr'),
    SSELL: document.getElementById('container_ss'),
    CS: document.getElementById('container_cs'),
    MO: document.getElementById('container_mo')
  };

  const hiddenContent = document.getElementById('utm_content');

  const hideAll = () => Object.values(containers).forEach(c => c && c.classList.add('d-none'));

  const handleSelect = (val) => {
    hideAll();
    
    const sourceGroup = document.getElementById('utm_source_group');
    const mediumGroup = document.getElementById('utm_medium_group');
    const mediumSelect = document.getElementById('utm_medium');

    // Resetar visibilidade e required por padrão
    if (sourceGroup) sourceGroup.classList.remove('d-none');
    if (mediumGroup) mediumGroup.classList.remove('d-none');
    if (mediumSelect) mediumSelect.required = true;

    // Resetar bloqueio de source quando mudar de Social Selling
    if (val !== 'SSELL') {
      document.querySelectorAll('input[name="utm_source"]').forEach(inp => inp.disabled = false);
    }
    
    // Valores simples (sem campos dinâmicos) - TP, TO, SEM, TJJ, TV, APP, WEBINAR
    if (val === 'TP' || val === 'TO' || val === 'SEM' || val === 'TJJ' || val === 'TV' || val === 'APP' || val === 'WEBINAR') {
      hiddenContent.value = val;
    } else {
      hiddenContent.value = '';
    }

    // Lógica específica para WEBINAR: esconde Source e Medium
    if (val === 'WEBINAR') {
      if (sourceGroup) sourceGroup.classList.add('d-none');
      if (mediumGroup) mediumGroup.classList.add('d-none');
      if (mediumSelect) mediumSelect.required = false;
    }
    
    // Mostrar containers dinâmicos
    if (val === 'COMM') containers.COMM?.classList.remove('d-none');
    if (val === 'SDR') containers.SDR?.classList.remove('d-none');
    if (val === 'SSELL') containers.SSELL?.classList.remove('d-none');
    if (val === 'CS') containers.CS?.classList.remove('d-none');
    if (val === 'MO') {
      containers.MO?.classList.remove('d-none');
      // Trigger updateMo para aplicar lógica de PALESTRA se já estiver selecionado
      const moType = document.getElementById('mo_type');
      if (moType) {
        moType.dispatchEvent(new Event('change'));
      }
    }

    updateUtmPreview();
  };

  contentRadios.forEach(radio => radio.addEventListener('change', () => handleSelect(radio.value)));

  // ===== COMERCIAL (CLOSER) =====
  const closer = document.getElementById('closer_name');
  if (closer) closer.addEventListener('change', () => {
    const v = normalizeForUtm(closer.value);
    hiddenContent.value = v ? `${v}_CLOSER` : '';
    updateUtmPreview();
  });

  // ===== SDR =====
  const sdr = document.getElementById('sdr_name');
  if (sdr) sdr.addEventListener('change', () => {
    const v = normalizeForUtm(sdr.value);
    hiddenContent.value = v ? `${v}_SDR` : '';
    updateUtmPreview();
  });

  // ===== SOCIAL SELLING ===== 
  const ssName = document.getElementById('ss_name');
  const ssOptions = document.getElementById('ss_content_options');
  
  if (ssName) {
    ssName.addEventListener('change', () => {
      const v = normalizeForUtm(ssName.value);
      if (v) {
        // Definir utm_content como NOME_SSELL
        hiddenContent.value = `${v}_SSELL`;
        ssOptions?.classList.remove('d-none');
      } else {
        hiddenContent.value = '';
        ssOptions?.classList.add('d-none');
      }
      updateUtmPreview();
    });
  }

  // Social Selling content radios (ATIVO_IG, PASSIVO_IG, etc.)
  document.querySelectorAll('input[name="ss_content"]').forEach(r => {
    r.addEventListener('change', (e) => {
      const val = e.target.value; // ATIVO_IG, PASSIVO_IG, ATIVO_IN, PASSIVO_IN
      const parts = val.split('_');
      const platform = parts[1]; // IG or IN
      
      // Bloquear Source conforme seleção
      if (platform === 'IG') {
        document.querySelectorAll('input[name="utm_source"]').forEach(inp => {
          if (inp.value === 'ig') { 
            inp.checked = true; 
            inp.disabled = false; 
          } else {
            inp.disabled = true;
          }
        });
        populateMediumOptions('ig');
        updateTermPlaceholder('ig');
      } else if (platform === 'IN') {
        document.querySelectorAll('input[name="utm_source"]').forEach(inp => {
          if (inp.value === 'in') { 
            inp.checked = true; 
            inp.disabled = false; 
          } else {
            inp.disabled = true;
          }
        });
        populateMediumOptions('in');
        updateTermPlaceholder('in');
      }
      
      updateUtmPreview();
    });
  });

  // ===== SUPORTE (CS) =====
  const cs = document.getElementById('cs_name');
  if (cs) cs.addEventListener('change', () => {
    const v = normalizeForUtm(cs.value);
    hiddenContent.value = v ? `${v}_CS` : '';
    updateUtmPreview();
  });

  // ===== MÍDIA OFFLINE =====
  const moType = document.getElementById('mo_type');
  const moName = document.getElementById('mo_name');
  const moNameContainer = document.getElementById('mo_name_container');
  
  if (moType && moName) {
    const updateMo = () => {
      const t = moType.value || '';
      const n = normalizeForUtm(moName.value || '');
      
      const sourceGroup = document.getElementById('utm_source_group');
      const mediumGroup = document.getElementById('utm_medium_group');
      const mediumSelect = document.getElementById('utm_medium');
      
      // Se for PALESTRA ou EVENTO, esconder Source, Medium e Nome do Arquivo
      if (t === 'PALESTRA' || t === 'EVENTO') {
        if (sourceGroup) sourceGroup.classList.add('d-none');
        if (mediumGroup) mediumGroup.classList.add('d-none');
        if (mediumSelect) mediumSelect.required = false;
        if (moNameContainer) moNameContainer.classList.add('d-none');
        // Para PALESTRA/EVENTO, formato é apenas MO_PALESTRA ou MO_EVENTO
        hiddenContent.value = t === 'PALESTRA' ? 'MO_PALESTRA' : 'MO_EVENTO';
      } else {
        // Caso contrário, mostrar Source, Medium e Nome do Arquivo
        if (sourceGroup) sourceGroup.classList.remove('d-none');
        if (mediumGroup) mediumGroup.classList.remove('d-none');
        if (mediumSelect) mediumSelect.required = true;
        if (moNameContainer) moNameContainer.classList.remove('d-none');
        // Para outros tipos, formatar como MO_TIPO_NOME
        hiddenContent.value = n ? `MO_${t}_${n}` : '';
      }
      
      updateUtmPreview();
    };
    moType.addEventListener('change', updateMo);
    moName.addEventListener('input', updateMo);
  }
};

// When utm_medium select is OUTRO, show custom input
document.addEventListener('change', function(e) {
  if (e.target && e.target.id === 'utm_medium') {
    const custom = document.getElementById('utm_medium_custom');
    if (e.target.value === 'OUTRO') {
      custom.classList.remove('d-none');
    } else if (custom) {
      custom.classList.add('d-none');
    }
    updateUtmPreview();
  }
});

// Validação em tempo real do nome personalizado
const initCustomNameValidation = () => {
  const customNameInput = document.getElementById('custom_name');
  if (!customNameInput) return;

  const previewElement = document.createElement('div');
  previewElement.id = 'custom_name_preview';
  previewElement.className = 'form-text mt-1';
  previewElement.style.display = 'none';
  customNameInput.parentNode.appendChild(previewElement);

  const updatePreview = () => {
    const originalValue = customNameInput.value;
    const sanitizedValue = sanitizeCustomName(originalValue);
    
    if (originalValue && sanitizedValue !== originalValue) {
      previewElement.innerHTML = `
        <i class="bi bi-info-circle text-warning"></i> 
        <span class="text-warning">Será ajustado para:</span> 
        <strong class="text-success">${sanitizedValue || '(vazio - será gerado automaticamente)'}</strong>
      `;
      previewElement.style.display = 'block';
    } else if (originalValue && sanitizedValue) {
      previewElement.innerHTML = `
        <i class="bi bi-check-circle text-success"></i> 
        <span class="text-success">Nome válido!</span>
      `;
      previewElement.style.display = 'block';
    } else {
      previewElement.style.display = 'none';
    }
  };

  customNameInput.addEventListener('input', updatePreview);
  customNameInput.addEventListener('blur', updatePreview);
  updatePreview();
};

// Confirmação de exclusão
const initDeleteConfirmation = () => {
  document.querySelectorAll(".delete-btn").forEach(button => {
    button.addEventListener("click", function () {
      const id = this.dataset.id;

      if (!document.getElementById("confirmDeleteModal")) {
        document.body.insertAdjacentHTML("beforeend", `
          <div class="modal fade" id="confirmDeleteModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content bg-danger-subtle border-0">
                <div class="modal-header text-dark border-danger">
                  <h5 class="modal-title">Confirmar Exclusão</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <div class="alert alert-danger border-0 m-0 h5 text-center">
                    Tem certeza que deseja excluir esta UTM?<br>
                    <strong class="lh-lg text-danger">Esta ação é irreversível!</strong>
                  </div>
                  <div class="form-group mt-3">
                    <label for="deletePassword" class="form-label text-danger">
                      Antes de inserir a senha para deletar, confirme abaixo se está ciente.
                    </label>
                    <input type="password" class="form-control" id="deletePassword" placeholder="Digite sua senha" disabled>
                  </div>
                  <div class="form-check mt-3 fw-bolder">
                    <input class="form-check-input" type="checkbox" id="confirmRadio">
                    <label class="form-check-label text-danger" for="confirmRadio">Estou ciente e quero continuar</label>
                  </div>
                </div>
                <div class="modal-footer border-danger">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="button" class="btn btn-danger" id="confirmDelete" disabled>Excluir</button>
                </div>
              </div>
            </div>
          </div>`);
      }

      const modal = new bootstrap.Modal(document.getElementById("confirmDeleteModal"));
      modal.show();

      const confirmCheckbox = document.getElementById("confirmRadio");
      const deletePassword = document.getElementById("deletePassword");
      const confirmDeleteBtn = document.getElementById("confirmDelete");

      confirmCheckbox.addEventListener("change", () => {
        const enabled = confirmCheckbox.checked;
        deletePassword.disabled = !enabled;
        confirmDeleteBtn.disabled = !enabled;
      });

      const confirmDeleteHandler = () => {
        fetch("delete.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ id, password: deletePassword.value })
        })
          .then(res => res.json())
          .then(data => {
            if (data.success) document.querySelector(`button[data-id='${id}']`).closest("tr").remove();
            else alert(data.error || "Erro ao excluir.");

            modal.hide();
            modal.dispose();
            document.getElementById("confirmDeleteModal").remove();
            location.reload();
          });

        confirmDeleteBtn.removeEventListener("click", confirmDeleteHandler);
      };

      confirmDeleteBtn.addEventListener("click", confirmDeleteHandler, { once: true });
    });
  });
};

// Filtro de busca e data
const initSearchFilters = () => {
  const searchInput = document.getElementById('searchInputField');
  const dateFilter = document.getElementById('dateFilter');
  const clicksFilter = document.getElementById('clicksFilter');

  const filterTable = () => {
    const searchVal = searchInput.value.toLowerCase();
    const dateVal = dateFilter.value;
    const clicksVal = clicksFilter.value;
    const rows = document.querySelectorAll('.table-responsive table tbody tr');

    rows.forEach(row => {
      const cells = row.querySelectorAll('td');
      let match = !searchVal || Array.from(cells).some(cell => {
        if (cell.querySelector('.copy-icon') || cell.querySelector('.delete-btn')) return false;
        return cell.textContent.toLowerCase().includes(searchVal);
      });
      
      if (dateVal && match) {
        const dateCell = row.querySelector('td[data-label="Data da UTM"]');
        if (dateCell) {
          const [d, m, y] = dateCell.textContent.trim().split(' ')[0].split('-');
          const rowDate = new Date(`${y}-${m}-${d}`);
          const filterDate = new Date(dateVal);
          match = rowDate.toDateString() === filterDate.toDateString();
        } else match = false;
      }

      if (clicksVal && match) {
        const clicks = parseInt(row.querySelector('td[data-label="Clicks"]').textContent.trim(), 10);
        match = clicks >= parseInt(clicksVal, 10);
      }

      row.style.display = match ? '' : 'none';
    });
  };

  searchInput.addEventListener('keyup', filterTable);
  dateFilter.addEventListener('change', filterTable);
  clicksFilter.addEventListener('input', filterTable);
};

// Mostrar campo "Outro" dinamicamente
const initCampaignOther = () => {
  const campaignRadios = document.querySelectorAll('input[name="utm_campaign"]');
  const otherContainer = document.getElementById('campaign_other_container');
  const otherInput = document.getElementById('utm_campaign_other');

  if (!campaignRadios.length || !otherContainer || !otherInput) return;

  const toggleOther = (radio) => {
    const isOther = radio.value === 'Outro';
    otherContainer.classList.toggle('d-none', !isOther);
    otherInput.required = isOther;
    if (isOther) setTimeout(() => otherInput.focus(), 100);
  };

  campaignRadios.forEach(radio => {
    radio.addEventListener('change', () => toggleOther(radio));
    if (radio.checked) toggleOther(radio);
  });
};

// Inicialização principal
window.addEventListener("DOMContentLoaded", () => {
  initTheme();
  initTooltips();
  initDeleteConfirmation();
  initToggleStatus();
  initSearchFilters();
  initCampaignOther();
  initCustomNameValidation();
  initUtmContentHandlers();

  // Update preview when fields change
  document.querySelectorAll('input[name="utm_campaign"]').forEach(inp => 
    inp.addEventListener('change', updateUtmPreview)
  );
  
  document.querySelectorAll('input[name="utm_source"]').forEach(inp => 
    inp.addEventListener('change', (e) => {
      const val = e.target.value;
      populateMediumOptions(val);
      updateTermPlaceholder(val);
      updateUtmPreview();
    })
  );
  
  const mediumInput = document.getElementById('utm_medium');
  if (mediumInput) mediumInput.addEventListener('change', updateUtmPreview);
  
  const mediumCustom = document.getElementById('utm_medium_custom');
  if (mediumCustom) mediumCustom.addEventListener('input', updateUtmPreview);
  
  const termInput = document.getElementById('utm_term');
  if (termInput) termInput.addEventListener('input', updateUtmPreview);
});

// Função para alternar o status da UTM
const initToggleStatus = () => {
  document.querySelectorAll(".toggle-status-btn").forEach(button => {
    button.addEventListener("click", function () {
      const id = this.dataset.id;
      const currentStatus = parseInt(this.dataset.status);
      const newStatus = currentStatus === 1 ? 0 : 1;
      const actionText = newStatus === 1 ? 'habilitar' : 'desabilitar';

      if (confirm(`Tem certeza que deseja ${actionText} esta UTM?`)) {
        fetch("disable.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ id, status: newStatus })
        })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              alert(`UTM ${actionText}da com sucesso!`);
              
              this.dataset.status = data.new_status;
              
              const icon = this.querySelector('i');
              if (data.new_status === 1) {
                icon.classList.remove('bi-toggle-off', 'text-danger');
                icon.classList.add('bi-toggle-on', 'text-success');
                this.title = 'Desabilitar';
              } else {
                icon.classList.remove('bi-toggle-on', 'text-success');
                icon.classList.add('bi-toggle-off', 'text-danger');
                this.title = 'Habilitar';
              }
            } else {
              alert('Erro ao alterar status da UTM: ' + (data.error || 'Erro desconhecido'));
            }
          })
          .catch(error => {
            console.error('Erro:', error);
            alert('Erro de comunicação com o servidor.');
          });
      }
    });
  });
};

// Garantir que o campo ss_content seja enviado no formulário
document.addEventListener('DOMContentLoaded', function() {
  const utmForm = document.getElementById('utmForm');
  if (utmForm) {
    utmForm.addEventListener('submit', function(e) {
      // Verificar se é Social Selling
      const ssContentRadio = document.querySelector('input[name="ss_content"]:checked');
      // Remover qualquer campo oculto ss_content anterior
      const existingHidden = this.querySelector('input[name="ss_content"][type="hidden"]');
      if (existingHidden) {
        existingHidden.remove();
      }
      
      if (ssContentRadio) {
        // Criar um campo oculto para enviar o valor do ss_content
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'ss_content';
        hiddenInput.value = ssContentRadio.value;
        this.appendChild(hiddenInput);
      }
    });
  }
});