/**
 * Sistema de notificaciones optimizado con caché local
 * Ubicación: /main/aprendiz/frontend/js/notificaciones.js
 */

class NotificacionesManager {
    constructor() {
        this.currentFilter = 'todas';
        this.currentPage = 0;
        this.pageSize = 20;
        this.totalNotificaciones = 0;
        this.notificaciones = [];
        this.admins = [];
        this.selectedAdminId = 0;
        this.cacheKey = 'invitrosoft_notif_cache';
        this.lastFetchKey = 'invitrosoft_notif_last_fetch';
        this.viewedKey = 'invitrosoft_notif_viewed';
        
        this.init();
    }

    async init() {
        this.setupEventListeners();
        
        // Cargar desde caché primero para respuesta instantánea
        this.loadFromCache();
        
        // Cargar lista de administradores
        await this.loadAdmins();
        
        // Luego actualizar desde servidor
        await this.cargarNotificaciones();
        
        // Configurar actualización periódica (cada 60 segundos)
        this.intervalId = setInterval(() => this.verificarNuevasNotificaciones(), 60000);
    }

    setupEventListeners() {
        // Filtros de estado
        document.querySelectorAll('.filtro-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.currentFilter = e.target.dataset.filter;
                this.currentPage = 0;
                document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                this.renderNotificaciones();
                this.updatePaginacion();
            });
        });

        // Filtro por administrador
        const adminFilter = document.getElementById('filterAdmin');
        if (adminFilter) {
            adminFilter.addEventListener('change', (e) => {
                this.selectedAdminId = parseInt(e.target.value) || 0;
                this.currentPage = 0;
                this.renderNotificaciones();
                this.updatePaginacion();
            });
        }

        // Marcar todas como leídas
        document.getElementById('marcarTodo')?.addEventListener('click', () => {
            this.marcarTodasLeidas();
        });

        // Paginación
        document.getElementById('btnAnterior')?.addEventListener('click', () => {
            if (this.currentPage > 0) {
                this.currentPage--;
                this.renderNotificaciones();
                this.updatePaginacion();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });

        document.getElementById('btnSiguiente')?.addEventListener('click', () => {
            const maxPage = Math.ceil(this.getFilteredNotifications().length / this.pageSize);
            if (this.currentPage < maxPage - 1) {
                this.currentPage++;
                this.renderNotificaciones();
                this.updatePaginacion();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }

    // ==================== CACHÉ LOCAL ====================
    
    loadFromCache() {
        try {
            const cached = localStorage.getItem(this.cacheKey);
            if (cached) {
                const data = JSON.parse(cached);
                this.notificaciones = data.notificaciones || [];
                this.totalNotificaciones = data.total || 0;
                
                // Renderizar inmediatamente
                if (this.notificaciones.length > 0) {
                    this.renderNotificaciones();
                    this.updatePaginacion();
                }
            }
        } catch (error) {
            console.error('Error al cargar caché:', error);
        }
    }

    saveToCache() {
        try {
            const data = {
                notificaciones: this.notificaciones,
                total: this.totalNotificaciones,
                timestamp: Date.now()
            };
            localStorage.setItem(this.cacheKey, JSON.stringify(data));
            localStorage.setItem(this.lastFetchKey, Date.now().toString());
        } catch (error) {
            console.error('Error al guardar caché:', error);
        }
    }

    getViewedNotifications() {
        try {
            const viewed = localStorage.getItem(this.viewedKey);
            return viewed ? JSON.parse(viewed) : {};
        } catch (error) {
            return {};
        }
    }

    markAsViewed(notifId) {
        try {
            const viewed = this.getViewedNotifications();
            viewed[notifId] = Date.now();
            localStorage.setItem(this.viewedKey, JSON.stringify(viewed));
        } catch (error) {
            console.error('Error al marcar como vista:', error);
        }
    }

    // ==================== CARGAR ADMINISTRADORES ====================

    async loadAdmins() {
        try {
            const response = await fetch('../backend/notificaciones.php?action=get_admins', {
                credentials: 'same-origin'
            });

            if (!response.ok) return;

            const data = await response.json();

            if (data.success && data.admins) {
                this.admins = data.admins;
                this.renderAdminFilter();
            }
        } catch (error) {
            console.error('Error al cargar administradores:', error);
        }
    }

    renderAdminFilter() {
        const filterContainer = document.querySelector('.notificaciones-controls');
        if (!filterContainer) return;

        // Verificar si ya existe el filtro
        if (document.getElementById('filterAdmin')) return;

        // Crear el select de filtro
        const filterWrapper = document.createElement('div');
        filterWrapper.className = 'admin-filter-wrapper';
        filterWrapper.style.cssText = 'display: flex; align-items: center; gap: 8px;';

        const select = document.createElement('select');
        select.id = 'filterAdmin';
        select.className = 'filtro-select';
        select.style.cssText = `
            padding: 10px 16px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            background: var(--card-bg);
            color: var(--text-primary);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            min-width: 200px;
        `;

        // Opción por defecto
        const defaultOption = document.createElement('option');
        defaultOption.value = '0';
        defaultOption.textContent = '👥 Todos los admins';
        select.appendChild(defaultOption);

        // Agregar admins
        this.admins.forEach(admin => {
            const option = document.createElement('option');
            option.value = admin.id;
            option.textContent = `${admin.nombre} (${admin.total_notificaciones})`;
            select.appendChild(option);
        });

        filterWrapper.appendChild(select);
        
        // Insertar antes del botón "Marcar todas"
        const marcarBtn = document.getElementById('marcarTodo');
        if (marcarBtn) {
            marcarBtn.parentElement.insertBefore(filterWrapper, marcarBtn);
        }
    }

    // ==================== CARGAR NOTIFICACIONES ====================

    async cargarNotificaciones() {
        try {
            // Mostrar loading solo si no hay caché
            if (this.notificaciones.length === 0) {
                window.uiLoading?.show('Cargando notificaciones...');
            }

            const url = `../backend/notificaciones.php?filter=todas&limit=100&offset=0`;

            const response = await fetch(url, {
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error || 'Error al cargar notificaciones');
            }

            this.notificaciones = data.notificaciones || [];
            this.totalNotificaciones = data.total || 0;

            // Guardar en caché
            this.saveToCache();

            // Renderizar
            this.renderNotificaciones();
            this.updatePaginacion();

            window.uiLoading?.hide();
        } catch (error) {
            console.error('Error cargando notificaciones:', error);
            
            // Si hay caché, usarlo
            if (this.notificaciones.length > 0) {
                this.mostrarToast('Mostrando notificaciones del caché', 'warning');
            } else {
                this.mostrarError('No se pudieron cargar las notificaciones. Por favor, intenta de nuevo.');
            }
            
            window.uiLoading?.hide();
        }
    }

    async verificarNuevasNotificaciones() {
        try {
            const lastFetch = parseInt(localStorage.getItem(this.lastFetchKey) || '0');
            const now = Date.now();
            
            // Solo actualizar si han pasado más de 30 segundos
            if (now - lastFetch < 30000) {
                return;
            }

            const url = `../backend/notificaciones.php?filter=no_leidas&limit=1&offset=0`;
            const response = await fetch(url, { credentials: 'same-origin' });
            
            if (!response.ok) return;
            
            const data = await response.json();
            
            if (data.success && data.total > 0) {
                const cachedTotal = this.notificaciones.filter(n => !n.leida).length;
                
                if (data.total > cachedTotal) {
                    // Hay nuevas notificaciones
                    await this.cargarNotificaciones();
                    this.mostrarToast(`Tienes ${data.total - cachedTotal} nueva(s) notificación(es)`, 'info');
                }
            }
        } catch (error) {
            console.error('Error al verificar nuevas notificaciones:', error);
        }
    }

    // ==================== FILTRADO Y PAGINACIÓN ====================

    getFilteredNotifications() {
        let filtered = this.notificaciones;
        
        // Filtrar por estado (leída/no leída)
        if (this.currentFilter === 'no_leidas') {
            filtered = filtered.filter(n => !n.leida);
        } else if (this.currentFilter === 'leidas') {
            filtered = filtered.filter(n => n.leida);
        }
        
        // Filtrar por administrador
        if (this.selectedAdminId > 0) {
            filtered = filtered.filter(n => n.admin_id === this.selectedAdminId);
        }
        
        return filtered;
    }

    getPaginatedNotifications() {
        const filtered = this.getFilteredNotifications();
        const start = this.currentPage * this.pageSize;
        const end = start + this.pageSize;
        return filtered.slice(start, end);
    }

    // ==================== RENDERIZADO ====================

    renderNotificaciones() {
        const container = document.getElementById('notificacionesList');
        const paginated = this.getPaginatedNotifications();
        const viewed = this.getViewedNotifications();

        if (paginated.length === 0) {
            container.innerHTML = `
                <div class="notificaciones-empty">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <h3>Sin notificaciones</h3>
                    <p>No tienes notificaciones ${this.currentFilter === 'leidas' ? 'leídas' : this.currentFilter === 'no_leidas' ? 'sin leer' : ''} en este momento.</p>
                </div>
            `;
            return;
        }

        const html = paginated.map(notif => {
            const isViewed = viewed[notif.id] !== undefined;
            return this.renderNotificacion(notif, isViewed);
        }).join('');
        
        container.innerHTML = html;

        // Event listeners para marcar como leída
        document.querySelectorAll('.btn-notif[data-action="leer"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const notifId = parseInt(btn.dataset.id);
                this.marcarComoLeida(notifId);
            });
        });

        // Marcar como vista al hacer clic
        document.querySelectorAll('.notificacion-item').forEach(item => {
            item.addEventListener('click', () => {
                const notifId = parseInt(item.dataset.id);
                this.markAsViewed(notifId);
                
                // Actualizar visualmente
                const badge = item.querySelector('.notificacion-badge');
                if (badge) {
                    badge.style.opacity = '0.3';
                }
            });
        });
    }

    renderNotificacion(notif, isViewed) {
        const isUnread = !notif.leida;
        const tipoClass = (notif.tipo || 'info').toLowerCase();
        const tiempoRelativo = this.getRelativeTime(new Date(notif.fecha_creacion));

        return `
            <div class="notificacion-item ${isUnread ? 'no-leida' : 'leida'}" data-id="${notif.id}">
                <div class="notificacion-content">
                    <div class="notificacion-header">
                        ${isUnread && !isViewed ? '<span class="notificacion-badge"></span>' : ''}
                        <h3 class="notificacion-titulo">${this.escapeHtml(notif.titulo)}</h3>
                        <span class="notificacion-tipo ${tipoClass}">${this.getTipoLabel(tipoClass)}</span>
                    </div>
                    <p class="notificacion-mensaje">${this.escapeHtml(notif.mensaje)}</p>
                    <div class="notificacion-footer">
                        <div class="notificacion-fecha">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            <span>${tiempoRelativo}</span>
                        </div>
                        ${notif.admin_nombre ? `
                            <div class="notificacion-admin">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                                </svg>
                                <span>${this.escapeHtml(notif.admin_nombre)}</span>
                            </div>
                        ` : ''}
                    </div>
                </div>
                <div class="notificacion-acciones">
                    ${isUnread ? `
                        <button class="btn-notif" data-id="${notif.id}" data-action="leer" title="Marcar como leída">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    }

    getTipoLabel(tipo) {
        const labels = {
            'success': '✓ Éxito',
            'warning': '⚠ Aviso',
            'error': '✕ Error',
            'info': 'ℹ Info'
        };
        return labels[tipo] || labels['info'];
    }

    getRelativeTime(date) {
        const now = new Date();
        const diff = now - date;
        const seconds = Math.floor(diff / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);
        
        if (seconds < 60) return 'Hace un momento';
        if (minutes < 60) return `Hace ${minutes} min`;
        if (hours < 24) return `Hace ${hours}h`;
        if (days < 7) return `Hace ${days}d`;
        if (days < 30) {
            const weeks = Math.floor(days / 7);
            return `Hace ${weeks} semana${weeks > 1 ? 's' : ''}`;
        }
        if (days < 365) {
            const months = Math.floor(days / 30);
            return `Hace ${months} mes${months > 1 ? 'es' : ''}`;
        }
        const years = Math.floor(days / 365);
        return `Hace ${years} año${years > 1 ? 's' : ''}`;
    }

    updatePaginacion() {
        const filtered = this.getFilteredNotifications();
        const maxPage = Math.ceil(filtered.length / this.pageSize);
        const pagination = document.getElementById('paginationControls');
        const pageInfo = document.getElementById('pageInfo');
        const btnAnterior = document.getElementById('btnAnterior');
        const btnSiguiente = document.getElementById('btnSiguiente');

        if (!pagination || !pageInfo || !btnAnterior || !btnSiguiente) return;

        if (maxPage <= 1) {
            pagination.style.display = 'none';
        } else {
            pagination.style.display = 'flex';
            pageInfo.textContent = `Página ${this.currentPage + 1} de ${maxPage}`;
            btnAnterior.disabled = this.currentPage === 0;
            btnSiguiente.disabled = this.currentPage >= maxPage - 1;
        }
    }

    // ==================== ACCIONES ====================

    async marcarComoLeida(notifId) {
        try {
            const response = await fetch('../backend/notificaciones.php', {
                method: 'PUT',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: notifId })
            });

            const data = await response.json();

            if (data.success) {
                // Actualizar en caché local
                const notif = this.notificaciones.find(n => n.id === notifId);
                if (notif) {
                    notif.leida = true;
                    this.saveToCache();
                }
                
                this.mostrarToast('Notificación marcada como leída', 'success');
                this.renderNotificaciones();
                this.updatePaginacion();
            } else {
                this.mostrarToast(data.error || 'Error al marcar como leída', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarToast('Error al procesar la solicitud', 'error');
        }
    }

    async marcarTodasLeidas() {
        if (!confirm('¿Marcar todas las notificaciones como leídas?')) {
            return;
        }

        try {
            window.uiLoading?.show('Marcando notificaciones...');

            const formData = new FormData();
            formData.append('action', 'marcar_todas_leidas');

            const response = await fetch('../backend/notificaciones.php', {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Actualizar caché
                this.notificaciones.forEach(n => n.leida = true);
                this.saveToCache();
                
                this.mostrarToast('Todas las notificaciones han sido marcadas como leídas', 'success');
                this.currentPage = 0;
                this.renderNotificaciones();
                this.updatePaginacion();
            } else {
                this.mostrarToast(data.error || 'Error al marcar como leídas', 'error');
            }

            window.uiLoading?.hide();
        } catch (error) {
            console.error('Error:', error);
            this.mostrarToast('Error al procesar la solicitud', 'error');
            window.uiLoading?.hide();
        }
    }

    // ==================== UTILIDADES ====================

    mostrarError(mensaje) {
        const container = document.getElementById('notificacionesList');
        container.innerHTML = `
            <div class="notificaciones-empty">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3>Error</h3>
                <p>${mensaje}</p>
                <button onclick="location.reload()" class="btn-primary" style="margin-top: 20px;">
                    Reintentar
                </button>
            </div>
        `;
    }

    mostrarToast(mensaje, tipo = 'info') {
        const colores = {
            'success': '#10b981',
            'error': '#ef4444',
            'warning': '#f59e0b',
            'info': '#3b82f6'
        };

        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            top: 90px;
            right: 20px;
            padding: 16px 20px;
            background: var(--card-bg, #fff);
            color: var(--text-primary, #1a1f2e);
            border-left: 4px solid ${colores[tipo] || colores['info']};
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,.25);
            z-index: 10050;
            max-width: 360px;
            font-weight: 600;
            animation: slideInRight 0.3s ease-out;
        `;
        
        toast.textContent = mensaje;
        document.body.appendChild(toast);
        
        setTimeout(() => { 
            toast.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => toast.remove(), 300); 
        }, 3500);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Agregar animaciones CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
    
    .filtro-select {
        transition: all 0.2s ease;
    }
    
    .filtro-select:hover {
        border-color: var(--primary);
        box-shadow: 0 2px 8px rgba(0, 120, 50, 0.15);
    }
    
    .filtro-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(0, 120, 50, 0.1);
    }
    
    .admin-filter-wrapper {
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
`;
document.head.appendChild(style);

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.notificacionesManager = new NotificacionesManager();
});