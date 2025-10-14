document.addEventListener("DOMContentLoaded", () => {
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
            <a href="reportes.php" class="sidebar-link" id="nav-estadisticas">
                <svg viewBox="0 0 24 24"><path d="M3 17h3v-7H3v7zm5 0h3v-4H8v4zm5 0h3v-2h-3v2zm5 0h3v-9h-3v9z"/></svg>
                <span>Estadísticas</span>
            </a>
            <a href="#" class="sidebar-link" id="nav-notificaciones">
                <svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4a2 2 0 002 2zm6-6V9c0-3.07-1.63-5.64-4.5-6.32V2a1.5 1.5 0 00-3 0v.68C7.63 3.36 6 5.92 6 9v7l-1.29 1.29A1 1 0 006 20h12a1 1 0 00.71-1.71L18 17z"/></svg>
                <span>Notificaciones</span>
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
                <span class="user-name">Anbo Minecon</span>
                <span class="user-role">Aprendiz</span>
                <div class="user-avatar">
                    <svg width="36" height="36" viewBox="0 0 36 36" fill="none"><circle cx="18" cy="18" r="18" fill="#e8e8e8"/><path d="M18 19c-4 0-7 2-7 4v2h14v-2c0-2-3-4-7-4zm0-2a4 4 0 100-8 4 4 0 000 8z" fill="#007832"/></svg>
                </div>
                <div class="user-dropdown" id="userDropdown">
                    <a href="#" class="dropdown-item">Mi perfil</a>
                    <a href="#" class="dropdown-item">Ayuda</a>
                    <a href="../logout.php" class="dropdown-item logout">Cerrar sesión</a>
                </div>
            </div>
        </div>
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
    else if (path.includes("reportes.php")) document.getElementById("nav-estadisticas").classList.add("active");
    else if (path.includes("index.php")) document.getElementById("nav-panel").classList.add("active");

    // User dropdown toggle
    const headerUser = document.querySelector('.header-user');
    const mobileUserMenu = document.querySelector('.mobile-user-menu');
    const userDropdown = document.getElementById('userDropdown');
    if (headerUser && userDropdown) {
        headerUser.addEventListener('click', () => {
            userDropdown.classList.toggle('show');
        });
        document.addEventListener('click', (e) => {
            if (!headerUser.contains(e.target)) userDropdown.classList.remove('show');
        });
    }
    else if (mobileUserMenu && userDropdown) {
        mobileUserMenu.addEventListener('click', () => {
            userDropdown.classList.toggle('show');
        });
        document.addEventListener('click', (e) => {
            if (!mobileUserMenu.contains(e.target)) userDropdown.classList.remove('show');
        });
    }

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
            <a href="reportes.php" class="mobile-menu-item" id="mobile-estadisticas">Estadísticas</a>
            <a href="#" class="mobile-menu-item" id="mobile-notificaciones">Notificaciones</a>
        </div>
        <div class="header-user mobile-user-menu" id="userDropdown">
            <span class="user-name">Anbo Minecon</span>
            <span class="user-role">Aprendiz</span>
                <div class="user-avatar">
                <svg width="36" height="36" viewBox="0 0 36 36" fill="none"><circle cx="18" cy="18" r="18" fill="#e8e8e8"/><path d="M18 19c-4 0-7 2-7 4v2h14v-2c0-2-3-4-7-4zm0-2a4 4 0 100-8 4 4 0 000 8z" fill="#007832"/></svg>
            </div> 
            <div class="user-dropdown" id="userDropdown">
                <a href="#" class="dropdown-item">Mi perfil</a>
                <a href="#" class="dropdown-item">Ayuda</a>
                <a href="../logout.php" class="dropdown-item logout">Cerrar sesión</a>
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

});