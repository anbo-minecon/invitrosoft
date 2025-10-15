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
        "reactivos.html": {
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
        "historial.html": {
            titulo: "Historial",
            descripcion: "Consulta el historial de acciones",
            menuTitulo: "Historial",
            icono: `<svg viewBox="0 0 24 24" fill="#007832" xmlns="http://www.w3.org/2000/svg">
            <path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/>
            </svg>`
        }
    };

    // Función para obtener la ruta base correcta
    function getBasePath() {
        const path = window.location.pathname;
        // Si estamos en /invitrosoft/main/ o subdirectorios
        if (path.includes('/invitrosoft/admin')) {
            // Contar cuántos niveles de profundidad hay después de /invitrosoft/
            const afterMain = path.split('/invitrosoft/admin')[1];
            const depth = afterMain.split('/').length - 1;
            return depth > 0 ? '../'.repeat(depth) : './';
        }
        return './';
    }

    const basePath = getBasePath();

    // Detecta la página actual
    const path = window.location.pathname;
    const page = path.split("/").pop() || "index.php";
    const pageConfig = config[page] || {
        titulo: "Invitrosoft",
        descripcion: "Sistema de gestión",
        menuTitulo: "Sistema"
    };

    // ====== AGREGA <title> Y FAVICON AUTOMÁTICOS ======
    document.title = pageConfig.titulo + " - Invitrosoft";
    
    // Eliminar favicons existentes si los hay
    const existingFavicons = document.querySelectorAll('link[rel*="icon"]');
    existingFavicons.forEach(el => el.remove());
    
    // SVG Favicon
    const favicon = document.createElement("link");
    favicon.rel = "icon";
    favicon.type = "image/svg+xml";
    favicon.href = basePath + "img/logo.svg?v=" + Date.now();
    document.head.appendChild(favicon);

    // PNG Fallback
    const fallback = document.createElement("link");
    fallback.rel = "alternate icon";
    fallback.type = "image/png";
    fallback.href = basePath + "img/logo.png?v=" + Date.now();
    document.head.appendChild(fallback);

    // Apple Touch Icon (para dispositivos iOS)
    const appleTouchIcon = document.createElement("link");
    appleTouchIcon.rel = "apple-touch-icon";
    appleTouchIcon.href = basePath + "img/logo.png?v=" + Date.now();
    document.head.appendChild(appleTouchIcon);

    // Links adicionales SOLO para index.php
    const extraNavLinks = page === "index.php" ? `
        <li class="nav-item">
            <a href="${basePath}/crear_usuario.html" class="nav-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin-right: 6px;">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                Crear usuario
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin-right: 6px;">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                </svg>
                Objetivo
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin-right: 6px;">
                    <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                </svg>
                Contactos
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin-right: 6px;">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                Quienes somos
            </a>
        </li>
    ` : '';

    // HTML del header
    const header = `
    <header class="header">
        <div class="header-content">
            <a href="${basePath}index.php" class="logo">
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
                        <a href="/invitrosoft/main/admin/auth/index.html" class="dropdown-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="#007832" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                            </svg>
                            Mi perfil
                        </a>
                        <a href="#" class="dropdown-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="#007832" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z"/>
                            </svg>
                            Ayuda
                        </a>
                        <a href="/src/logout.php" class="dropdown-item dropdown-item-close">
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
                <a href="/main/admin/auth/index.html" class="dropdown-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="#007832" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                    </svg>
                    Mi perfil
                </a>
                <a href="#" class="dropdown-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="#007832" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z"/>
                    </svg>
                    Ayuda
                </a>
                <a href="/src/logout.php" class="dropdown-item dropdown-item-close">
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
            <a href="${basePath}/main/index.php" class="mobile-menu-item ${page === 'index.php' ? 'active' : ''}">Panel Principal</a>
            <a href="${basePath}/main/reactivos.html" class="mobile-menu-item ${page === 'reactivos.html' ? 'active' : ''}">Reactivos</a>
            <a href="${basePath}/main/formulaciones.html" class="mobile-menu-item ${page === 'formulaciones.html' ? 'active' : ''}">Formulaciones</a>
            <a href="${basePath}/main/protocolo.html" class="mobile-menu-item ${page === 'protocolo.html' ? 'active' : ''}">Protocolos</a>
            <a href="${basePath}/main/categoria.html" class="mobile-menu-item ${page === 'categoria.html' ? 'active' : ''}">Categorías</a>
            <a href="${basePath}/main/parametros.html" class="mobile-menu-item ${page === 'parametros.html' ? 'active' : ''}">Parámetros</a>
            <a href="${basePath}/main/historial.html" class="mobile-menu-item ${page === 'historial.html' ? 'active' : ''}">Historial</a>
        </div>
        <div class="mobile-menu-footer">
            <a href="${basePath}/main/index.php" class="mobile-access-btn">Salir</a>
        </div>
    </nav>
    `;

    // HTML del footer
    const footer = `
    <footer class="footer-container">
        <span>© 2024 Invitrosoft. Todos los derechos reservados.</span>
    </footer>
    `;

    // Inserta el header y footer
    document.body.insertAdjacentHTML("afterbegin", header);
    document.body.insertAdjacentHTML("beforeend", footer);

    // ===== CONFIGURACIÓN DE MENÚS DE USUARIO =====
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

    // Configurar todos los menús de usuario
    setupUserMenu('desktop-user-btn', 'desktop-dropdown-menu');
    setupUserMenu('mobile-user-btn', 'mobile-dropdown-menu');

    // ===== MENÚ HAMBURGUESA =====
    const menuToggle = document.getElementById('menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    const overlay = document.getElementById('mobile-menu-overlay');

    function toggleMenu() {
        if (!menuToggle || !mobileMenu || !overlay) return;
        
        const isOpen = mobileMenu.classList.contains('open');
        
        if (isOpen) {
            menuToggle.classList.remove('open');
            mobileMenu.classList.remove('open');
            overlay.classList.remove('open');
            document.body.style.overflow = '';
        } else {
            menuToggle.classList.add('open');
            mobileMenu.classList.add('open');
            overlay.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeMenu() {
        if (menuToggle) menuToggle.classList.remove('open');
        if (mobileMenu) mobileMenu.classList.remove('open');
        if (overlay) overlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    // Event listeners del menú hamburguesa
    if (menuToggle) {
        menuToggle.addEventListener('click', toggleMenu);
    }

    if (overlay) {
        overlay.addEventListener('click', closeMenu);
    }

    // Cerrar con tecla Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMenu();
        }
    });

    // Cerrar menú al hacer clic en un item
    document.querySelectorAll('.mobile-menu-item').forEach(item => {
        item.addEventListener('click', closeMenu);
    });

    // ===== BOTONES DE ACCESO/SALIR =====
    document.querySelectorAll('.access-btn, .mobile-access-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            closeMenu();
            window.location.href = basePath + '/main/index.php';
        });
    });

    // Ejemplo para el botón de cerrar sesión
    document.querySelectorAll('.dropdown-item-close[href$="index.html"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = '/src/logout.php';
            setTimeout(() => {
                window.location.reload(true); // Fuerza recarga completa
            }, 500);
        });
    });

    // Inyectar el icono SVG en el header
    const iconDiv = document.getElementById('header-svg-icon');
    if (iconDiv && pageConfig.icono) {
        iconDiv.innerHTML = pageConfig.icono;
    }
});

// ===== FUNCIONES GLOBALES =====
window.setupUserMenu = function(btnId, menuId) {
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
};

window.toggleMobileMenu = function() {
    const menuToggle = document.getElementById('menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    const overlay = document.getElementById('mobile-menu-overlay');
    
    if (!menuToggle || !mobileMenu || !overlay) return;
    
    menuToggle.classList.toggle('open');
    mobileMenu.classList.toggle('open');
    overlay.classList.toggle('open');
    
    document.body.style.overflow = mobileMenu.classList.contains('open') ? 'hidden' : '';
};

window.closeMobileMenu = function() {
    const menuToggle = document.getElementById('menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    const overlay = document.getElementById('mobile-menu-overlay');
    
    if (menuToggle) menuToggle.classList.remove('open');
    if (mobileMenu) mobileMenu.classList.remove('open');
    if (overlay) overlay.classList.remove('open');
    
    document.body.style.overflow = '';
};

// Aplica modo oscuro si está activado en localStorage
if (localStorage.getItem('darkMode') === 'enabled') {
    document.body.classList.add('dark-mode');
}

invitrosoft