document.addEventListener("DOMContentLoaded", async () => {
    // Añadir favicon (logo.svg) si no existe — busca en varias rutas posibles y usa la primera existente
    (function ensureFavicon(){
        try {
            const existing = document.querySelector('link[rel="icon"], link[rel="shortcut icon"]');
            if (existing) return;

            const origin = window.location.origin;
            const parts = window.location.pathname.split('/').filter(Boolean);
            const appRoot = parts.length ? '/' + parts[0] : '';

            const candidates = [
                `${origin}${appRoot}/img/logo.svg`,            // e.g. /invitrosoft-despliege/img/logo.svg
                `${origin}/img/logo.svg`,                      // e.g. /img/logo.svg
                '/invitrosoft/img/logo.svg'
            ];

            function testPath(path) {
                return new Promise(resolve => {
                    const img = new Image();
                    img.onload = () => resolve(path);
                    img.onerror = () => resolve(null);
                    // For absolute paths keep as-is, otherwise try to resolve relative to origin
                    img.src = path;
                });
            }

            (async function findAndInsert(){
                for (const p of candidates) {
                    try {
                        const ok = await testPath(p);
                        if (ok) {
                            const link = document.createElement('link');
                            link.rel = 'icon';
                            link.href = p;
                            link.type = 'image/svg+xml';
                            document.head.appendChild(link);
                            return;
                        }
                    } catch (e) {
                        // seguir intentando otras rutas
                    }
                }
                console.warn('favicon: no se encontró logo.svg en rutas probadas');
            })();

        } catch (e) {
            console.error('No se pudo añadir favicon automáticamente', e);
        }
    })();

    // Obtener datos del usuario
    let userData = {
        nombre: 'Usuario',
        tipo: 'aprendiz',
        foto: '/invitrosoft/img/user/default.png'
    };

    try {
        const response = await fetch('../backend/auth/perfil_get.php');
        const data = await response.json();

        
        if (data.success && data.user) {
            
            userData = {
                nombre: data.user.nombre || 'Usuario',
                tipo: data.user.tipo || 'aprendiz',
                foto: data.user.foto || '/invitrosoft/img/user/default.png'
            };
        }
    } catch (error) {
        console.error('Error al obtener datos del usuario:', error);
    }

    // Sidebar
    const sidebar = `
    <aside class="sidebar">
        <div class="sidebar-logo">
            <svg width="40" height="40" viewBox="0 0 36 36" fill="none"><circle cx="18" cy="18" r="18" fill="#007832"/><text x="50%" y="55%" text-anchor="middle" fill="#fff" font-size="16" font-family="Arial" dy=".3em">INV</text></svg>
            <span class="sidebar-title">Invitrosoft</span>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php" class="sidebar-link" id="nav-panel">
                <svg viewBox="0 0 24 24"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 8h14v-2H7v2zm0-4h14v-2H7v2zm0-6v2h14V7H7z"/></svg>
                <span>Panel</span>
            </a>
            <a href="plantas.php" class="sidebar-link" id="nav-plantas">
                <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/></svg>
                <span>Mis plantas</span>
            </a>
            <a href="contaminaciones.php" class="sidebar-link" id="nav-contaminaciones">
                <svg viewBox="0 0 24 24">
                    <circle cx="9" cy="10" r="1.5"></circle>
                    <circle cx="15" cy="10" r="1.5"></circle>
                    <path d="M12 2c-4.4 0-8 3.2-8 7.2 0 2.7 1.5 5 3.8 6.3L6 17v2h12v-2l-1.8-1.5c2.3-1.3 3.8-3.6 3.8-6.3C20 5.2 16.4 2 12 2z"></path>
                    <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <line x1="8" y1="20" x2="16" y2="18"></line>
                        <line x1="8" y1="18" x2="16" y2="20"></line>
                    </g>
                </svg>
                <span>Contaminaciones</span>
            </a>
            
            <a href="reportes.php" class="sidebar-link" id="nav-estadisticas">
                <svg viewBox="0 0 24 24"><path d="M3 17h3v-7H3v7zm5 0h3v-4H8v4zm5 0h3v-2h-3v2zm5 0h3v-9h-3v9z"/></svg>
                <span>Estadísticas</span>
            </a>
            <a href="notificaciones.php" class="sidebar-link" id="nav-notificaciones">
                <svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4a2 2 0 002 2zm6-6V9c0-3.07-1.63-5.64-4.5-6.32V2a1.5 1.5 0 00-3 0v.68C7.63 3.36 6 5.92 6 9v7l-1.29 1.29A1 1 0 006 20h12a1 1 0 00.71-1.71L18 17z"/></svg>
                <span>Notificaciones</span>
                <span class="notif-badge" id="notif-badge" style="display:none;"></span>
            </a>
        </nav>
    </aside>
    `;
    document.body.insertAdjacentHTML('afterbegin', sidebar);

    // Header
    const header = `
    <header class="header">
        <div class="header-content">
            <div class="header-title">
                <span id="page-title">Panel Principal</span>
                <span class="header-date">${new Date().toLocaleDateString()}</span>
            </div>
            <div class="header-user">
                <span class="user-name">${userData.nombre}</span>
                <span class="user-role">${userData.tipo.charAt(0).toUpperCase() + userData.tipo.slice(1)}</span>
                <div class="user-avatar">
                    ${userData.foto && userData.foto !== '/invitrosoft/img/user/default.png' ? 
                        `<img src="${userData.foto}" alt="${userData.nombre}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">` :
                        `<svg width="36" height="36" viewBox="0 0 36 36" fill="none"><circle cx="18" cy="18" r="18" fill="#e8e8e8"/><path d="M18 19c-4 0-7 2-7 4v2h14v-2c0-2-3-4-7-4zm0-2a4 4 0 100-8 4 4 0 000 8z" fill="#007832"/></svg>`
                    }
                </div>
                <div class="user-dropdown" id="userDropdown">
                    <a href="auth/perfil.php" class="dropdown-item">Mi perfil</a>
                    <a href="#" class="dropdown-item">Ayuda</a>
                    <a href="../../../src/logout.php" class="dropdown-item logout">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </header>
    `;
    document.body.insertAdjacentHTML('afterbegin', header);

    // Footer
    const footer = `
    <footer class="footer">
        <div class="footer-content">
            <span>© 2025 Invitrosoft. Todos los derechos reservados.</span>
            <nav class="footer-nav">
                <a href="#">Términos</a>
                <a href="#">Privacidad</a>
                <a href="#">Seguridad</a>
                <a href="#">Documentos</a>
                <a href="#">Contactos</a>
            </nav>
        </div>
    </footer>
    `;
    document.body.insertAdjacentHTML('beforeend', footer);

    // Sidebar active link
    const path = window.location.pathname;
    if (path.includes("plantas.php")) document.getElementById("nav-plantas").classList.add("active");
    else if (path.includes("contaminaciones.php")) document.getElementById("nav-contaminaciones").classList.add("active");
    else if (path.includes("reportes.php")) document.getElementById("nav-estadisticas").classList.add("active");
    else if (path.includes("index.php")) document.getElementById("nav-panel").classList.add("active");

    // User dropdown toggle - Enhanced functionality
    const headerUser = document.querySelector('.header-user');
    const mobileUserMenu = document.querySelector('.mobile-user-menu');
    
    // Get all dropdown elements (both desktop and mobile)
    const allDropdowns = document.querySelectorAll('.user-dropdown');
    
    // Update dropdown arrow state
    function updateDropdownArrow(headerUser, isOpen) {
        if (headerUser) {
            if (isOpen) {
                headerUser.classList.add('dropdown-open');
            } else {
                headerUser.classList.remove('dropdown-open');
            }
        }
    }
    
    // Update the toggleDropdown function to handle arrow
    function setupDropdown(triggerElement, dropdownElement) {
        if (!triggerElement || !dropdownElement) return;
        
        triggerElement.addEventListener('click', (e) => {
            e.stopPropagation();
            
            // Close all other dropdowns
            document.querySelectorAll('.user-dropdown').forEach(d => {
                if (d !== dropdownElement) {
                    d.classList.remove('show');
                }
            });
            
            // Toggle current dropdown
            dropdownElement.classList.toggle('show');
            
            // Add animation class
            if (dropdownElement.classList.contains('show')) {
                dropdownElement.style.animation = 'dropdownSlide 0.2s ease-out';
            }
        });
    }
    
    function closeAllDropdowns() {
        document.querySelectorAll('.user-dropdown').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
    }
    
    // Desktop dropdown
    if (headerUser) {
        const desktopDropdown = headerUser.querySelector('.user-dropdown');
        setupDropdown(headerUser, desktopDropdown);
    }
    
    // Mobile dropdown
    if (mobileUserMenu) {
        const mobileDropdown = mobileUserMenu.querySelector('.user-dropdown');
        if (mobileDropdown) {
            mobileUserMenu.addEventListener('click', (e) => {
                e.stopPropagation();
                toggleDropdown(mobileDropdown, mobileUserMenu);
            });
        }
    }
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.header-user') && !e.target.closest('.mobile-user-menu')) {
            closeAllDropdowns();
        }
    });
    
    // Close dropdowns on ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAllDropdowns();
        }
    });

    function setupDropdownItems() {
        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', (e) => {
                // Don't prevent default for links
                setTimeout(() => closeAllDropdowns(), 150);
            });
        });
    }
    
    // Handle dropdown item clicks
    document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', (e) => {
            // Add ripple effect
            const ripple = document.createElement('span');
            ripple.className = 'dropdown-ripple';
            const rect = item.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            item.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
            
            // Close dropdown after a short delay for better UX
            setTimeout(() => closeAllDropdowns(), 150);
        });
    });

    // Modo oscuro automático
    if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
    }

    // --- MENÚ HAMBURGUESA RESPONSIVE ---
    // Botón hamburguesa en header
    const menuBtn = document.createElement('button');
    menuBtn.className = 'menu-toggle';
    menuBtn.setAttribute('aria-label', 'Abrir menú');
    menuBtn.innerHTML = `
        <span class="menu-icon">
            <span></span>
            <span></span>
            <span></span>
        </span>
    `;
    // Insertar el botón al inicio del header-content
    document.querySelector('.header-content').prepend(menuBtn);

    // Drawer del menú hamburguesa
    const mobileMenu = document.createElement('nav');
    mobileMenu.className = 'mobile-menu';
    mobileMenu.innerHTML = `
        <div class="mobile-menu-header">
            <div class="logo-total">
                <svg width="36" height="36" viewBox="0 0 36 36" fill="none"><circle cx="18" cy="18" r="18" fill="#007832"/><text x="50%" y="55%" text-anchor="middle" fill="#fff" font-size="16" font-family="Arial" dy=".3em">INV</text></svg>
                <span class="mobile-menu-title">Invitrosoft</span>
            </div>
        </div>
        <div class="mobile-menu-content">
            <a href="index.php" class="mobile-menu-item" id="mobile-panel">Panel</a>
            <a href="plantas.php" class="mobile-menu-item" id="mobile-plantas">Mis plantas</a>
            <a href="contaminaciones.php" class="mobile-menu-item" id="mobile-contaminaciones">Contaminaciones</a>
            <a href="reportes.php" class="mobile-menu-item" id="mobile-estadisticas">Estadísticas</a>
            <a href="notificaciones.php" class="mobile-menu-item" id="mobile-notificaciones">Notificaciones</a>
        </div>
        <div class="header-user mobile-user-menu">
            <div class="mobile-user-info">
                <span class="user-name">${userData.nombre}</span>
                <span class="user-role">${userData.tipo.charAt(0).toUpperCase() + userData.tipo.slice(1)}</span>
            </div>
            <div class="user-avatar">
                ${userData.foto && userData.foto !== '/invitrosoft/img/user/default.png' ? 
                    `<img src="${userData.foto}" alt="${userData.nombre}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">` :
                    `<svg width="36" height="36" viewBox="0 0 36 36" fill="none"><circle cx="18" cy="18" r="18" fill="#e8e8e8"/><path d="M18 19c-4 0-7 2-7 4v2h14v-2c0-2-3-4-7-4zm0-2a4 4 0 100-8 4 4 0 000 8z" fill="#007832"/></svg>`
                }
            </div> 
            <div class="user-dropdown" id="mobileUserDropdown">
                <a href="auth/perfil.php" class="dropdown-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Mi perfil
                </a>
                <a href="#" class="dropdown-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="12" y1="17" x2="12.01" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Ayuda
                </a>
                <a href="../../../src/logout.php" class="dropdown-item logout">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="16 17 21 12 16 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Cerrar sesión
                </a>
            </div>
        </div>
    `;
    document.body.appendChild(mobileMenu);

    // Overlay para cerrar el menú tocando fuera
    const overlay = document.createElement('div');
    overlay.className = 'mobile-menu-overlay';
    document.body.appendChild(overlay);

    // Mostrar/ocultar menú hamburguesa
    menuBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        mobileMenu.classList.toggle('open');
        overlay.classList.toggle('open');
        menuBtn.classList.toggle('open');
    });
    overlay.addEventListener('click', () => {
        mobileMenu.classList.remove('open');
        overlay.classList.remove('open');
        menuBtn.classList.remove('open');
    });

    // Cerrar menú al hacer click en un enlace
    mobileMenu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            mobileMenu.classList.remove('open');
            overlay.classList.remove('open');
            menuBtn.classList.remove('open');
        });
    });

    // ===== Global Loading Overlay (auto-injection) =====
    (function initGlobalLoader(){
        // Si ya existe API o overlay, no duplicar ni auto-mostrar
        const alreadyApi = !!window.uiLoading;
        const existing = document.getElementById('uiLoading');

        // Inyectar estilos solo si no existen (busca por id)
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

        // Inyectar overlay si no existe
        if (!existing) {
            const wrap = document.createElement('div');
            document.body.appendChild(wrap);
        }

        // Definir API si no existe
        if (!alreadyApi) {
            window.uiLoading = {
                show: (t, img) => {
                    const el = document.getElementById('uiLoading');
                    if (!el) return;
                    if (t) { const tx = document.getElementById('uiLoadingText'); if (tx) tx.textContent = t; }
                    if (img) {
                        let tag = document.getElementById('uiLoadingImg');
                        let sp = document.getElementById('uiLoadingSpinner');
                        if (tag) tag.src = img; else if (sp) sp.outerHTML = `<img id="uiLoadingImg" class="spinner-img" src="${img}" alt="Cargando">`;
                    }
                    el.classList.add('active');
                },
                hide: () => {
                    const el = document.getElementById('uiLoading');
                    if (!el) return; el.classList.remove('active');
                }
            };
        }

        // Auto mostrar/ocultar solo si este script creó el overlay (no existe previamente)
        if (!existing) {
            window.uiLoading.show('Cargando...');
            window.addEventListener('load', () => window.uiLoading.hide());
        }
    })();
    
    // Función auxiliar para mostrar notificaciones (sin Socket.IO)
    function showToast(msg) {
        const el = document.createElement('div');
        el.style.cssText = 'position:fixed;top:20px;right:20px;padding:12px 16px;background:#111827;color:#fff;border-left:4px solid #00a844;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.25);z-index:10050;max-width:360px;font-weight:600';
        el.textContent = msg;
        document.body.appendChild(el);
        setTimeout(() => { 
            el.style.opacity = '0'; 
            el.style.transition = 'opacity .3s'; 
            setTimeout(() => el.remove(), 300); 
        }, 3500);
    }

    setupDropdownItems();

    // Cargar contador de notificaciones sin leer
    async function loadNotificationCount() {
        try {
            const response = await fetch('backend/notificaciones.php?filter=no_leidas&limit=1&offset=0', {
                credentials: 'same-origin'
            });
            
            if (!response.ok) return;
            
            const data = await response.json();
            if (data.success && data.total > 0) {
                const badge = document.getElementById('notif-badge');
                if (badge) {
                    badge.textContent = data.total > 99 ? '99+' : data.total;
                    badge.style.display = 'inline-flex';
                }
            }
        } catch (error) {
            console.error('Error cargando contador de notificaciones:', error);
        }
    }

    // Cargar contador al iniciar
    loadNotificationCount();
    
    // Recargar cada 30 segundos
    setInterval(loadNotificationCount, 30000);
});