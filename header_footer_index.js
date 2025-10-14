document.addEventListener("DOMContentLoaded", () => {
    // HEADER HTML
    const headerHTML = `
    <header class="header">
        <div class="header-content">
            <a href="index.html" class="logo">
                <div class="logo-icon" style="filter: brightness(0) invert(1);"></div>
                <span class="logo-text-subgroup">
                    <span class="logo-text">Invitrosoft</span>
                    <span class="logo-subtitle">Gestión moderna de laboratorios</span>
                </span>
            </a>
            <ul class="nav-menu">
                <li class="nav-item"><a href="inicio.html" class="active">Inicio</a></li>
                <li class="nav-item"><a href="proposito.html">Propósito</a></li>
                <li class="nav-item"><a href="acerca.html">Acerca de</a></li>
                <li class="nav-item"><a href="contacto.html">Contacto</a></li>
                <li class="nav-item"><a href="src/index.html" class="access-btn">Acceso</a></li>
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
        <div class="mobile-menu-header">
            <div class="logo-icon" style="filter: brightness(0) invert(1);"></div>
            <div>
                <div class="mobile-menu-title">Invitrosoft</div>
                <div class="mobile-menu-subtitle">Gestión moderna de laboratorios</div>
            </div>
        </div>
        <div class="mobile-menu-content">
            <a href="inicio.html" class="mobile-menu-item active">Inicio</a>
            <a href="proposito.html" class="mobile-menu-item">Propósito</a>
            <a href="acerca.html" class="mobile-menu-item">Acerca de</a>
            <a href="contacto.html" class="mobile-menu-item">Contacto</a>
        </div>
        <div class="mobile-menu-footer">
            <a href="src/index.html" class="mobile-access-btn">Acceso</a>
        </div>
    </nav>
    `;

    // FOOTER HTML
    const footerHTML = `
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="footer-logo">I</div>
                    <div class="footer-info">
                        <h3>Invitrosoft</h3>
                        <p class="footer-description">
                            Plataforma moderna para la gestión integral de laboratorios de 
                            micropropagación. Optimiza tus procesos con tecnología de vanguardia.
                        </p>
                        <ul class="footer-contact">
                            <li>
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M20,8L12,13L4,8V6L12,11L20,6M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z"/></svg>
                                contacto@invitrosoft.com
                            </li>
                            <li>
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M20,15.5C18.83,15.5 17.67,15.33 16.58,15C16.21,14.89 15.81,15 15.54,15.27L13.21,17.6C10.07,15.93 8.07,13.93 6.4,10.79L8.73,8.46C9,8.19 9.11,7.79 9,7.42C8.67,6.33 8.5,5.17 8.5,4C8.5,3.45 8.05,3 7.5,3H4C3.45,3 3,3.45 3,4C3,16.39 7.61,21 20,21C20.55,21 21,20.55 21,20V16.5C21,15.95 20.55,15.5 20,15.5Z"/></svg>
                                +1 (555) 123-4567
                            </li>
                            <li>
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12,6A6,6 0 0,1 18,12C18,16.5 12,22 12,22C12,22 6,16.5 6,12A6,6 0 0,1 12,6M12,8A4,4 0 0,0 8,12C8,14.67 10.67,18.19 12,19.88C13.33,18.19 16,14.67 16,12A4,4 0 0,0 12,8Z"/></svg>
                                Ciudad de México, México
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Enlaces Rápidos</h4>
                    <ul class="footer-links">
                        <li><a href="index.html" class="active">Inicio</a></li>
                        <li><a href="proposito.html">Propósito</a></li>
                        <li><a href="panel.html">Panel de Control</a></li>
                        <li><a href="parametros.html">Parámetros</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Soporte</h4>
                    <ul class="footer-links">
                        <li><a href="contacto.html">Contacto</a></li>
                        <li><a href="acerca.html">Acerca de</a></li>
                        <li><a href="#">Documentación</a></li>
                        <li><a href="#">Soporte Técnico</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                © 2025 Invitrosoft. Todos los derechos reservados. | 
                <a href="#">Términos y condiciones</a> | 
                <a href="#">Política de Privacidad</a>
            </div>
        </div>
    </footer>
    `;

    // Inserta el header al principio y el footer al final del body
    document.body.insertAdjacentHTML("afterbegin", headerHTML);
    document.body.insertAdjacentHTML("beforeend", footerHTML);

    // Menú hamburguesa funcionalidad
    const menuToggle = document.getElementById('menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    const overlay = document.getElementById('mobile-menu-overlay');

    function toggleMenu() {
        menuToggle.classList.toggle('open');
        mobileMenu.classList.toggle('open');
        overlay.classList.toggle('open');
        document.body.style.overflow = mobileMenu.classList.contains('open') ? 'hidden' : '';
    }

    function closeMenu() {
        menuToggle.classList.remove('open');
        mobileMenu.classList.remove('open');
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    if (menuToggle) menuToggle.addEventListener('click', toggleMenu);
    if (overlay) overlay.addEventListener('click', closeMenu);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeMenu();
    });

    document.querySelectorAll('.mobile-menu-item').forEach(item => {
        item.addEventListener('click', closeMenu);
    });

    // Botón de acceso
    document.querySelectorAll('.access-btn, .mobile-access-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            closeMenu();
            window.location.href = 'src/index.html';
        });
    });
});