// includes/ui_loader.js
// Loader global para todo el sistema (admin y aprendiz)
// Inyecta estilos, overlay y expone window.uiLoading

(function(){
  if (window.__UILOADER_INIT__) return; // evitar doble init
  window.__UILOADER_INIT__ = true;

  // Estilos
  if (!document.getElementById('uiLoadingStyles')) {
    const style = document.createElement('style');
    style.id = 'uiLoadingStyles';
    style.textContent = `
      .ui-loading{position:fixed; inset:0; display:none; align-items:center; justify-content:center; z-index:4000}
      .ui-loading.active{display:flex}
      .ui-loading .backdrop{position:absolute; inset:0; background:rgba(0,0,0,.5)}
      .ui-loading .pane{position:relative; z-index:1; background:#1b2432; border:1px solid #2d3748; border-radius:14px; padding:18px 20px; display:flex; align-items:center; gap:12px; box-shadow:0 24px 72px rgba(0,0,0,.35)}
      .spinner{width:22px; height:22px; border:3px solid #2e3a49; border-top-color:#00a844; border-radius:50%; animation:spin .9s linear infinite}
      .spinner-img{width:28px; height:28px; display:block; object-fit:contain; animation:spin 1s linear infinite}
      @keyframes spin{to{transform:rotate(360deg)}}
    `;
    document.head.appendChild(style);
  }

  // Overlay
  if (!document.getElementById('uiLoading')) {
    const wrap = document.createElement('div');
    wrap.id = 'uiLoading';
    wrap.className = 'ui-loading';
    wrap.setAttribute('role','status');
    wrap.setAttribute('aria-live','polite');
    wrap.innerHTML = `
      <div class="backdrop" aria-hidden="true"></div>
      <div class="pane"><div id="uiLoadingSpinner" class="spinner"></div><strong id="uiLoadingText">Cargando...</strong></div>
    `;
    document.body.appendChild(wrap);
  }

  // API
  if (!window.uiLoading) {
    window.uiLoading = {
      show: function(text, img){
        const el = document.getElementById('uiLoading');
        if (!el) return;
        if (text){ const tx = document.getElementById('uiLoadingText'); if (tx) tx.textContent = text; }
        if (img){
          let tag = document.getElementById('uiLoadingImg');
          let sp = document.getElementById('uiLoadingSpinner');
          if (tag) tag.src = img; else if (sp) sp.outerHTML = `<img id="uiLoadingImg" class="spinner-img" src="${img}" alt="Cargando">`;
        }
        el.classList.add('active');
      },
      hide: function(){
        const el = document.getElementById('uiLoading');
        if (!el) return; el.classList.remove('active');
      }
    };
  }

  // Auto: mostrar al inicio y ocultar en load si nadie lo maneja
  if (!document.body.hasAttribute('data-ui-loader-noauto')){
    window.uiLoading.show('Cargando...');
    window.addEventListener('load', function(){ window.uiLoading.hide(); });
  }
})();
