document.addEventListener('DOMContentLoaded', () => {
  const grid = document.getElementById('gridPlantas');
  const buscar = document.getElementById('buscar');
  const btnBuscar = document.getElementById('btnBuscar');
  const btnLimpiar = document.getElementById('btnLimpiar');
  const btnDecision = document.getElementById('btnDecision');
  const ordenarSel = document.getElementById('ordenar');
  const faseSel = document.getElementById('fase');

  const modal = document.getElementById('modalContaminacion');
  const backdrop = modal.querySelector('.modal-backdrop');
  const closeBtns = modal.querySelectorAll('[data-close], .modal-close');
  const stepFases = document.getElementById('stepFases');
  const stepForm = document.getElementById('stepForm');
  const listaFases = document.getElementById('listaFases');
  const form = document.getElementById('formContaminacion');
  const formMsg = document.getElementById('formMsg');
  let currentFetchController = null;
  let decisionMode = false; // false: plantas, true: contaminaciones

  const plantaIdInput = document.getElementById('plantaId');
  const faseTipoInput = document.getElementById('faseTipo');
  const faseIdInput = document.getElementById('faseId');
  const btnAtras = document.getElementById('btnAtras');

  function openModal() {
    modal.setAttribute('aria-hidden', 'false');
    modal.classList.add('open');
  }
  function closeModal() {
    modal.setAttribute('aria-hidden', 'true');
    modal.classList.remove('open');
    // reset steps
    stepFases.style.display = '';
    stepForm.style.display = 'none';
    form.reset();
    formMsg.style.display = 'none';
    listaFases.innerHTML = '';
  }
  if (closeBtns) closeBtns.forEach(b => b.addEventListener('click', closeModal));
  if (backdrop) backdrop.addEventListener('click', closeModal);

  function renderPlantaCard(p) {
    const fase = (p.fase_actual || '').toLowerCase();
    const faseClass = fase ? fase : 'sin-fase';
    const card = document.createElement('div');
    card.className = 'planta-card';
    card.innerHTML = `
      <div class="planta-card-header">
        <div class="planta-card-icon">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M5 12c1.5-4 5-6 7-9 2 3 5.5 5 7 9-2 5-6 8-7 10-1-2-5-5-7-10z"/>
          </svg>
        </div>
        <div class="planta-nombre">${p.nombre_comun}</div>
        <span class="planta-codigo">${p.codigo}</span>
      </div>
      <div class="planta-card-body">
        <div class="planta-info-row">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.761 0 5-2.239 5-5S14.761 2 12 2 7 4.239 7 7s2.239 5 5 5zm0 2c-4 0-8 2-8 6v2h16v-2c0-4-4-6-8-6z"/></svg>
          <span>ID #${p.id}</span>
        </div>
        ${typeof p.conta_count !== 'undefined' ? `<div class="planta-info-row"><svg viewBox=\"0 0 24 24\" fill=\"currentColor\"><path d=\"M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 8h14v-2H7v2zm0-4h14v-2H7v2zm0-6v2h14V7H7z\"/></svg><span>Contaminaciones: ${p.conta_count}</span></div>` : ''}
        <span class="planta-fase-badge ${faseClass}">${p.fase_actual || '-'}</span>
      </div>
      <div class="planta-card-footer">
        <button class="btn-contaminar" data-id="${p.id}">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z"/></svg>
          Reportar contaminación
        </button>
      </div>`;
    return card;
  }

  function renderContaminacionCard(c) {
    const fase = (c.fase_tipo || '').toLowerCase();
    const faseClass = fase ? fase : 'sin-fase';
    const card = document.createElement('div');
    card.className = 'conta-card';
    card.innerHTML = `
      <div class="conta-card-header">
        <div class="planta-card-icon">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M5 12c1.5-4 5-6 7-9 2 3 5.5 5 7 9-2 5-6 8-7 10-1-2-5-5-7-10z"/>
          </svg>
        </div>
        <div class="conta-titles">
          <div class="planta-nombre">${c.nombre_comun} <span class="planta-codigo">${c.codigo}</span></div>
          <span class="planta-fase-badge ${faseClass}">${c.fase_tipo || '-'}</span>
        </div>
      </div>
      <div class="conta-card-body">
        <div class="conta-grid">
          <div class="conta-item"><span class="label">Tipo</span><span class="value">${c.tipo}</span></div>
          <div class="conta-item"><span class="label">Cantidad</span><span class="value">${c.cantidad}</span></div>
          <div class="conta-item"><span class="label">Fecha</span><span class="value">${c.fecha_contaminacion}</span></div>
          <div class="conta-item wide"><span class="label">Motivo</span><span class="value">${c.motivo || '-'}</span></div>
        </div>
      </div>
    `;
    return card;
  }

  async function cargarPlantas(q = '') {
    grid.innerHTML = '<div class="empty-state">Cargando plantas...</div>';
    try {
      // abort previous request if any
      if (currentFetchController) {
        currentFetchController.abort();
      }
      currentFetchController = new AbortController();
      const { signal } = currentFetchController;
      const params = new URLSearchParams();
      params.set('q', q || '');
      if (ordenarSel) params.set('ordenar', ordenarSel.value || '');
      if (faseSel) params.set('fase', faseSel.value || '');
      const endpoint = decisionMode ? '../backend/contaminaciones/listar_contaminaciones.php' : '../backend/contaminaciones/listar_plantas.php';
      const res = await fetch(endpoint + '?' + params.toString(), { signal });
      const data = await res.json();
      if (!Array.isArray(data) || data.length === 0) {
        grid.innerHTML = '<div class="empty-state">Sin resultados</div>';
        return;
      }
      grid.innerHTML = '';
      if (decisionMode) {
        data.forEach(c => grid.appendChild(renderContaminacionCard(c)));
      } else {
        data.forEach(p => grid.appendChild(renderPlantaCard(p)));
        grid.querySelectorAll('.btn-contaminar').forEach(btn => {
          btn.addEventListener('click', () => iniciarContaminacion(btn.dataset.id));
        });
      }
    } catch (e) {
      if (e.name === 'AbortError') return; // ignore aborted requests
      grid.innerHTML = '<div class="empty-state">Error cargando plantas</div>';
      console.error(e);
    }
  }

  async function iniciarContaminacion(plantaId) {
    plantaIdInput.value = plantaId;
    stepFases.style.display = '';
    stepForm.style.display = 'none';
    listaFases.innerHTML = '<span class="pill">Cargando fases...</span>';
    openModal();
    try {
      const res = await fetch('../backend/contaminaciones/fases_por_planta.php?planta_id=' + encodeURIComponent(plantaId));
      const data = await res.json();
      listaFases.innerHTML = '';
      if (!Array.isArray(data) || data.length === 0) {
        listaFases.innerHTML = '<div class="empty-state">No hay fases activas para esta planta</div>';
        return;
      }
      data.forEach(f => {
        const chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'chip';
        chip.textContent = `${f.fase_tipo} ${f.nombre || ''}`.trim();
        chip.addEventListener('click', () => {
          faseTipoInput.value = f.fase_tipo;
          faseIdInput.value = f.fase_id;
          stepFases.style.display = 'none';
          stepForm.style.display = '';
        });
        listaFases.appendChild(chip);
      });
    } catch (e) {
      listaFases.innerHTML = '<div class="empty-state">Error cargando fases</div>';
    }
  }

  if (btnAtras) btnAtras.addEventListener('click', () => {
    stepForm.style.display = 'none';
    stepFases.style.display = '';
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    formMsg.style.display = 'none';
    const payload = {
      planta_id: plantaIdInput.value,
      fase_tipo: faseTipoInput.value,
      fase_id: faseIdInput.value,
      tipo: document.getElementById('tipo').value,
      cantidad: document.getElementById('cantidad').value,
      motivo: document.getElementById('motivo').value,
      fecha_contaminacion: document.getElementById('fecha').value
    };

    try {
      const res = await fetch('../backend/contaminaciones/registrar_contaminacion.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const result = await res.json();
      if (result.success) {
        formMsg.className = 'form-msg success';
        formMsg.textContent = 'Contaminación registrada correctamente';
        formMsg.style.display = 'block';
        setTimeout(() => closeModal(), 1200);
        cargarPlantas(buscar.value.trim());
      } else {
        formMsg.className = 'form-msg error';
        formMsg.textContent = result.error || 'No se pudo registrar';
        formMsg.style.display = 'block';
      }
    } catch (e) {
      formMsg.className = 'form-msg error';
      formMsg.textContent = 'Error de conexión';
      formMsg.style.display = 'block';
    }
  });

  if (btnBuscar) btnBuscar.addEventListener('click', () => cargarPlantas(buscar.value.trim()));
  if (btnLimpiar) btnLimpiar.addEventListener('click', () => { buscar.value=''; cargarPlantas(''); });
  if (btnDecision) btnDecision.addEventListener('click', () => {
    decisionMode = !decisionMode;
    if (decisionMode) {
      btnDecision.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12h6m8 0h6"/><path d="M12 12l-6 6M12 12l6-6"/></svg> Ver plantas';
    } else {
      btnDecision.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v6m0 8v6"/><path d="M12 12l6-6M12 12l-6 6"/></svg> Modo decisión';
    }
    cargarPlantas(buscar.value.trim());
  });
  if (ordenarSel) ordenarSel.addEventListener('change', () => cargarPlantas(buscar.value.trim()));
  if (faseSel) faseSel.addEventListener('change', () => cargarPlantas(buscar.value.trim()));
  // instant search while typing (debounced)
  function debounce(fn, delay = 300) {
    let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
  }
  const debouncedSearch = debounce(() => cargarPlantas(buscar.value.trim()), 250);
  if (buscar) buscar.addEventListener('input', debouncedSearch);
  // filtro por usuario ahora es automático desde el backend

  // init
  cargarPlantas('');
});
