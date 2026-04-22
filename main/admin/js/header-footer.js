document.addEventListener("DOMContentLoaded", () => {
    // Configuración de títulos, descripciones e iconos por página
    const config = {
        "index.php": {
            titulo: "Panel Principal",
            descripcion: "Bienvenido a Invitrosoft",
            menuTitulo: "Panel Principal",
            icono: `<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="20" cy="20" r="18" fill="#007832"/>
                <text x="20" y="26" text-anchor="middle" font-size="16" fill="#fff" font-family="Arial">INV</text>
                </svg>`
        },
        "reactivos.php": {
            titulo: "Reactivos",
            descripcion: "Gestiona tus reactivos de laboratorio",
            menuTitulo: "Reactivos",
            icono: `<svg viewBox="0 0 24 24" fill="#007832">
                    <path d="M6 22q-.825 0-1.412-.587Q4 20.825 4 20v-3h2v-7h-.25Q5.375 10 5.188 9.625 5 9.25 5 8.75V6q0-.825.588-1.413Q6.175 4 7 4h10q.825 0 1.413.587Q19 5.175 19 6v2.75q0 .5-.188.875-.187.375-.562.375H18v7h2v3q0 .825-.587 1.413Q18.825 22 18 22Zm2-5h8v-7h-8zm-1 3h10v-1H7Z"/>
                    </svg>`
        },
        "formulaciones.html": {
            titulo: "Formulaciones",
            descripcion: "Gestiona tus formulaciones de manera eficiente",
            menuTitulo: "Formulaciones",
            icono: `<svg viewBox="0 0 24 24" fill="#007832">
                    <path d="M7 2v2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2h-1V2h-2v2H9V2H7zm0 6h10v10H7V8z"/>
                    </svg>`
        },
        "protocolo.html": {
            titulo: "Protocolos",
            descripcion: "Gestiona tus protocolos de micropropagación",
            menuTitulo: "Protocolos",
            icono: `<svg viewBox="0 0 24 24" fill="#007832" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                    </svg>`
        },
        "categoria.html": {
            titulo: "Categorías",
            descripcion: "Gestiona las categorías de reactivos",
            menuTitulo: "Categorías",
            icono: `<svg viewBox="0 0 24 24" fill="#007832">
                    <path d="M4 4h6v6H4V4zm0 10h6v6H4v-6zm10-10h6v6h-6V4zm0 10h6v6h-6v-6z"/>
                    </svg>`
        },
        "parametros.html": {
            titulo: "Parámetros",
            descripcion: "Configura los parámetros del sistema",
            menuTitulo: "Parámetros",
            icono: `<svg viewBox="0 0 24 24" fill="#007832">
                    <path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/>
                    </svg>`
        },
        "administrar.php": {
            titulo: "Administrar",
            descripcion: "Gestión del sistema",
            menuTitulo: "Administrar",
            icono: `<svg viewBox="0 0 24 24" fill="#007832"><path d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.21,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.21,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.67 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"/></svg>`
        }
    };

    const basePath = getBasePath();
    const page = window.location.pathname.split("/").pop() || "index.php";
    const pageConfig = config[page] || {
        titulo: "Invitrosoft",
        descripcion: "Sistema de gestión",
        menuTitulo: "Sistema"
    };

    document.title = pageConfig.titulo + " - Invitrosoft";
    
    const existingFavicons = document.querySelectorAll('link[rel*="icon"]');
    existingFavicons.forEach(el => el.remove());
    
    const favicon = document.createElement("link");
    favicon.rel = "icon";
    favicon.type = "image/svg+xml";
    favicon.href = "../../img/logo.svg?v=" + Date.now();
    document.head.appendChild(favicon);

    const extraNavLinks = page === "index.php" ? `
        <li class="nav-item">
            <a href="crear_usuario.php" class="nav-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin-right: 6px;">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                Crear usuario
            </a>
        </li>
    ` : '';

    // HTML del header
    const header = `
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo">
                <div class="logo-icon" id="header-svg-icon"></div>
                <span class="logo-text-subgroup">
                    <span class="logo-text">${pageConfig.titulo}</span>
                    <span class="logo-subtitle">${pageConfig.descripcion}</span>
                </span>
            </a>
            <ul class="nav-menu">
                ${extraNavLinks}
                <div class="user-menu-container">
                    <button type="button" class="user-menu-btn" id="desktop-user-btn">
                        <svg class="user-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="8" r="4" fill="#007832"/>
                            <path d="M12 14c-6 0-8 3-8 6v2h16v-2c0-3-2-6-8-6z" fill="#007832"/>
                        </svg>
                        <div class="user-info">
                            <span class="user-role">Administrador</span>
                            <span class="user-name">Adanies Basilio</span>
                        </div>
                    </button>
                    <div class="dropdown-menu" id="desktop-dropdown-menu">
                        <a href="auth/index.html" class="dropdown-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="#007832" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                            </svg>
                            Mi perfil
                        </a>
                        
                        <a href="../../src/logout.php" class="dropdown-item dropdown-item-close">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="#007832" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                            </svg>
                            Cerrar sesión
                        </a>
                    </div>
                </div>
            </ul>
            <button class="menu-toggle" id="menu-toggle" aria-label="Abrir menú">
                <span class="menu-icon">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </button>
        </div>
    </header>
    <div class="mobile-menu-overlay" id="mobile-menu-overlay"></div>
    <nav class="mobile-menu" id="mobile-menu">
        <div class="user-menu-container mobile-user-menu">
            <button type="button" class="user-menu-btn" id="mobile-user-btn">
                <svg class="user-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="8" r="4" fill="#007832"/>
                    <path d="M12 14c-6 0-8 3-8 6v2h16v-2c0-3-2-6-8-6z" fill="#007832"/>
                </svg>
                <div class="user-info">
                    <span class="user-role">Administrador</span>
                    <span class="user-name">Adanies Basilio</span>
                </div>
            </button>
            <div class="dropdown-menu" id="mobile-dropdown-menu">
                <a href="auth/index.html" class="dropdown-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="#007832" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                    </svg>
                    Mi perfil
                </a>
                
                <a href="crear_usuario.php" class="dropdown-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="#007832" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                    Crear Usuario
                </a>
                
                <a href="../../src/logout.php" class="dropdown-item dropdown-item-close">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="#007832" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                    </svg>
                    Cerrar sesión
                </a>
            </div>
        </div>
        <div class="mobile-menu-header">
            <div class="logo-total">
                <div class="logo-icon" id="header-svg-icon">
                    ${pageConfig.icono || ''}
                </div>
                <div>
                    <div class="mobile-menu-title">${pageConfig.menuTitulo}</div>
                    <div class="mobile-menu-subtitle">Gestión eficiente</div>
                </div>
            </div>
        </div>
        <div class="mobile-menu-content">
            <a href="${basePath}admin/index.php" class="mobile-menu-item ${page === 'index.php' ? 'active' : ''}">Panel Principal</a>
            <a href="${basePath}admin/reactivos.php" class="mobile-menu-item ${page === 'reactivos.php' ? 'active' : ''}">Reactivos</a>
            <a href="${basePath}admin/formulaciones.html" class="mobile-menu-item ${page === 'formulaciones.html' ? 'active' : ''}">Formulaciones</a>
            <a href="${basePath}admin/protocolo.html" class="mobile-menu-item ${page === 'protocolo.html' ? 'active' : ''}">Protocolos</a>
            <a href="${basePath}admin/categoria.html" class="mobile-menu-item ${page === 'categoria.html' ? 'active' : ''}">Categorías</a>
            <a href="${basePath}admin/parametros.html" class="mobile-menu-item ${page === 'parametros.html' ? 'active' : ''}">Parámetros</a>
            
            <div class="mobile-menu-item-with-submenu">
                <a href="${basePath}admin/administrar.php" class="mobile-menu-item ${page === 'administrar.php' ? 'active' : ''}">
                    Administrar
                    <svg class="submenu-arrow" viewBox="0 0 24 24" width="16" height="16">
                        <path fill="currentColor" d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
                    </svg>
                </a>
                <div class="mobile-submenu">
                    <a href="#" class="mobile-submenu-item" data-section="historial">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                            <path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/>
                        </svg>
                        Historial de Actividades
                    </a>
                    <a href="#" class="mobile-submenu-item" data-section="usuarios">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                            <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                        </svg>
                        Gestión de Usuarios
                    </a>
                </div>
            </div>
        </div>
        <div class="mobile-menu-footer">
            <a href="${basePath}index.php" class="mobile-access-btn">Salir</a>
        </div>
    </nav>
    `;

    // HTML del footer
    const footer = `
    <footer class="footer-container">
        <span>© 2024 Invitrosoft. Todos los derechos reservados.</span>
    </footer>
    `;

    document.body.insertAdjacentHTML("afterbegin", header);
    document.body.insertAdjacentHTML("beforeend", footer);

    function setupUserMenu(btnId, menuId) {
        const btn = document.getElementById(btnId);
        const menu = document.getElementById(menuId);
        if (!btn || !menu) return;
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            menu.classList.toggle('show');
        });
        document.addEventListener('click', (e) => {
            if (!btn.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('show');
            }
        });
    }

    setupUserMenu('desktop-user-btn', 'desktop-dropdown-menu');
    setupUserMenu('mobile-user-btn', 'mobile-dropdown-menu');

    const menuToggle = document.getElementById('menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    const overlay = document.getElementById('mobile-menu-overlay');

    function toggleMenu() {
        if (!menuToggle || !mobileMenu || !overlay) return;
        const isOpen = mobileMenu.classList.contains('open');
        [menuToggle, mobileMenu, overlay].forEach(el => el.classList.toggle('open'));
        document.body.style.overflow = isOpen ? '' : 'hidden';
    }

    function closeMenu() {
        [menuToggle, mobileMenu, overlay].forEach(el => el?.classList.remove('open'));
        document.body.style.overflow = '';
    }

    menuToggle?.addEventListener('click', toggleMenu);
    overlay?.addEventListener('click', closeMenu);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMenu(); });
    document.querySelectorAll('.mobile-menu-item').forEach(item => {
        item.addEventListener('click', (e) => {
            // No cerrar el menú si tiene submenú
            if (item.closest('.mobile-menu-item-with-submenu')) {
                e.preventDefault();
                const submenuContainer = item.closest('.mobile-menu-item-with-submenu');
                submenuContainer.classList.toggle('submenu-open');
            } else {
                closeMenu();
            }
        });
    });

    // Manejar clicks en items del submenú
    document.querySelectorAll('.mobile-submenu-item').forEach(item => {
        item.addEventListener('click', (e) => {
            const section = item.dataset.section;
            
            // Si tiene data-section, es para administrar.php
            if (section) {
                e.preventDefault();
                
                // Si estamos en administrar.php, cambiar de sección
                if (page === 'administrar.php') {
                    // Actualizar sidebar si existe
                    document.querySelectorAll('.sidebar-item').forEach(s => s.classList.remove('active'));
                    const sidebarItem = document.querySelector(`.sidebar-item[data-section="${section}"]`);
                    if (sidebarItem) sidebarItem.classList.add('active');
                    
                    // Cambiar sección
                    document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
                    const contentSection = document.getElementById(`${section}-section`);
                    if (contentSection) contentSection.classList.add('active');
                    
                    // Cargar datos según la sección
                    if (section === 'historial' && typeof loadActivities === 'function') {
                        loadActivities(1, true);
                    } else if (section === 'usuarios' && typeof loadUsers === 'function') {
                        loadUsers();
                    }
                } else {
                    // Si no estamos en administrar.php, navegar a la página con el hash
                    window.location.href = `${basePath}admin/administrar.php#${section}`;
                }
            }
            // Si no tiene data-section, es un enlace normal (como crear_usuario.php)
            // y dejamos que navegue normalmente
            
            closeMenu();
        });
    });

    const iconDiv = document.getElementById('header-svg-icon');
    if (iconDiv && pageConfig.icono) iconDiv.innerHTML = pageConfig.icono;

    (function initGlobalLoader(){
        if (document.getElementById('uiLoadingStyles')) return;
        const style = document.createElement('style');
        style.id = 'uiLoadingStyles';
        style.textContent = `.ui-loading{position:fixed;inset:0;display:none;align-items:center;justify-content:center;z-index:4000}.ui-loading.active{display:flex}.ui-loading .backdrop{position:absolute;inset:0;background:rgba(0,0,0,.5)}.ui-loading .pane{position:relative;z-index:1;background:#1b2432;border:1px solid #2d3748;border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:12px;box-shadow:0 24px 72px rgba(0,0,0,.35)}.spinner{width:22px;height:22px;border:3px solid #2e3a49;border-top-color:#00a844;border-radius:50%;animation:spin .9s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}`;
        document.head.appendChild(style);
        
        const wrap = document.createElement('div');
        wrap.id = 'uiLoading';
        wrap.className = 'ui-loading';
        wrap.innerHTML = '<div class="backdrop"></div><div class="pane"><div class="spinner"></div><strong id="uiLoadingText">Cargando...</strong></div>';
        document.body.appendChild(wrap);
        
        window.uiLoading = {
            show: (t) => { 
                if (t) document.getElementById('uiLoadingText').textContent = t;
                document.getElementById('uiLoading').classList.add('active');
            },
            hide: () => document.getElementById('uiLoading').classList.remove('active')
        };
        
        window.uiLoading.show('Cargando...');
        window.addEventListener('load', () => window.uiLoading.hide());
    })();

    if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
    }

    // Manejar navegación por hash si llegamos desde el submenú
    if (page === 'administrar.php' && window.location.hash) {
        const section = window.location.hash.substring(1);
        if (section === 'historial' || section === 'usuarios') {
            setTimeout(() => {
                document.querySelectorAll('.sidebar-item').forEach(s => s.classList.remove('active'));
                const sidebarItem = document.querySelector(`.sidebar-item[data-section="${section}"]`);
                if (sidebarItem) {
                    sidebarItem.classList.add('active');
                    sidebarItem.click();
                }
            }, 100);
        }
    }
});

function getBasePath() {
    const path = window.location.pathname;
    if (path.includes('/invitrosoft/main/admin')) {
        const afterMain = path.split('/invitrosoft/main/admin')[1];
        const depth = afterMain.split('/').length - 1;
        return depth > 0 ? '../'.repeat(depth) : './';
    }
    return './';
}