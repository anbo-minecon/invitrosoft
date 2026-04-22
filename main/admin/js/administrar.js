// Variables globales para Historial
let currentPage = 1;
let isLoading = false;
let hasMore = true;
const pageSize = 15;

// Variables globales para Usuarios
let allUsers = [];
let filteredUsers = [];

// Cambio de sección
document.querySelectorAll('.sidebar-item').forEach(item => {
    item.addEventListener('click', (e) => {
        e.preventDefault();
        const section = item.dataset.section;
        
        // Actualizar sidebar
        document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
        item.classList.add('active');
        
        // Cambiar sección
        document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
        document.getElementById(`${section}-section`).classList.add('active');
        
        // Cargar datos según la sección
        if (section === 'historial') {
            loadActivities(1, true);
        } else if (section === 'usuarios') {
            loadUsers();
        }
    });
});

// ==================== HISTORIAL ====================

async function loadActivities(page = 1, reset = false) {
    if (isLoading) return;
    isLoading = true;
    
    const container = document.getElementById('activitiesContainer');
    const loadMoreContainer = document.getElementById('loadMoreContainer');
    
    if (reset) {
        container.innerHTML = '<div class="loading"><div class="spinner"></div></div>';
        currentPage = 1;
        hasMore = true;
    } else if (page > 1) {
        container.insertAdjacentHTML('beforeend', '<div class="loading"><div class="spinner"></div></div>');
    }
    
    try {
        const filterType = document.getElementById('filterType').value;
        const filterUser = document.getElementById('filterUser').value;
        const filterDate = document.getElementById('filterDate').value;
        
        const params = new URLSearchParams({
            action: 'getActivities',
            page: page,
            pageSize: pageSize
        });
        
        if (filterType) params.append('type', filterType);
        if (filterUser) params.append('user', filterUser);
        if (filterDate) params.append('date', filterDate);
        
        const response = await fetch(`db/historial_api.php?${params.toString()}`);
        const data = await response.json();
        
        if (!data.success) throw new Error(data.message);
        
        if (reset || page === 1) {
            container.innerHTML = '';
        } else {
            container.querySelector('.loading')?.remove();
        }
        
        if (data.activities && data.activities.length > 0) {
            data.activities.forEach(activity => {
                container.appendChild(createActivityElement(activity));
            });
            hasMore = data.activities.length === pageSize;
            loadMoreContainer.classList.toggle('hidden', !hasMore);
        } else if (page === 1) {
            container.innerHTML = `
                <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                    <p>No hay actividades para mostrar</p>
                </div>`;
        }
        
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = `
            <div style="text-align: center; padding: 60px 20px; color: var(--danger);">
                <i class="fas fa-exclamation-circle" style="font-size: 48px; margin-bottom: 15px;"></i>
                <p>Error al cargar las actividades</p>
                <button onclick="loadActivities(1, true)" class="btn-primary" style="margin-top: 15px;">
                    Reintentar
                </button>
            </div>`;
    } finally {
        isLoading = false;
    }
}

function createActivityElement(activity) {
    const div = document.createElement('div');
    div.className = 'activity-item';
    
    const icons = {
        nueva_planta: '<path d="M17,8C8,10 5.9,16.17 3.82,21.34L5.71,22L6.66,19.7C7.14,19.87 7.64,20 8,20C19,20 22,3 22,3C21,5 14,5.25 9,6.25C4,7.25 2,11.5 2,13.5C2,15.5 3.75,17.25 3.75,17.25C7,8 17,8 17,8Z"/>',
        contaminacion: '<path d="M12,2L1,21H23M12,6L19.5,19.5H4.5M11,10V14H13V10M11,16V18H13V16"/>',
        cambio_fase: '<path d="M13,20V7.83L16.88,11.71L18.29,10.29L12,4L5.71,10.29L7.12,11.71L11,7.83V20H13Z"/>',
        nuevo_usuario: '<path d="M12,19.2C9.5,19.2 7.29,17.92 6,16C6.03,14 10,12.9 12,12.9C14,12.9 17.97,14 18,16C16.71,17.92 14.5,19.2 12,19.2M12,5A3,3 0 0,1 15,8A3,3 0 0,1 12,11A3,3 0 0,1 9,8A3,3 0 0,1 12,5M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12C22,6.47 17.5,2 12,2Z"/>',
        default: '<path d="M13,13H11V7H13M13,17H11V15H13M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z"/>'
    };
    
    const icon = icons[activity.tipo] || icons.default;
    const date = new Date(activity.fecha_creacion);
    const relativeTime = getRelativeTime(date);
    
    div.innerHTML = `
        <div class="activity-dot">
            <svg viewBox="0 0 24 24">${icon}</svg>
        </div>
        <div class="activity-content">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                    <strong style="color: var(--text-primary);">${escapeHtml(activity.titulo)}</strong>
                    ${activity.tipo ? `<span class="badge badge-${activity.tipo}">${activity.tipo}</span>` : ''}
                </div>
                <span style="font-size: 13px; color: var(--text-secondary);">${relativeTime}</span>
            </div>
            <p style="color: var(--text-secondary); font-size: 14px; margin: 0;">${escapeHtml(activity.mensaje)}</p>
            ${activity.usuario_nombre ? `
                <p style="margin-top: 8px; font-size: 12px; color: var(--text-muted);">
                    <i class="fas fa-user"></i> ${escapeHtml(activity.usuario_nombre)}
                </p>
            ` : ''}
        </div>
    `;
    
    return div;
}

function getRelativeTime(date) {
    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    
    if (minutes < 1) return 'Hace un momento';
    if (minutes < 60) return `Hace ${minutes} min`;
    if (hours < 24) return `Hace ${hours}h`;
    if (days < 7) return `Hace ${days}d`;
    return date.toLocaleDateString('es-ES');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Event Listeners Historial
document.getElementById('refreshBtn')?.addEventListener('click', () => {
    loadActivities(1, true);
});

document.getElementById('applyFilters')?.addEventListener('click', () => {
    loadActivities(1, true);
});

document.getElementById('resetFilters')?.addEventListener('click', () => {
    document.getElementById('filterType').value = '';
    document.getElementById('filterUser').value = '';
    document.getElementById('filterDate').value = '';
    loadActivities(1, true);
});

document.getElementById('loadMoreBtn')?.addEventListener('click', () => {
    if (!isLoading && hasMore) {
        currentPage++;
        loadActivities(currentPage);
    }
});

// ==================== USUARIOS ====================

async function loadUsers() {
    const container = document.getElementById('usersContainer');
    container.innerHTML = '<div class="loading"><div class="spinner"></div></div>';
    
    try {
        const response = await fetch('db/usuarios_api.php?action=getAllUsers');
        const data = await response.json();
        
        if (!data.success) throw new Error(data.message);
        
        allUsers = data.users || [];
        filteredUsers = allUsers;
        renderUsers();
        
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = `
            <div style="text-align: center; padding: 60px 20px; color: var(--danger);">
                <i class="fas fa-exclamation-circle" style="font-size: 48px; margin-bottom: 15px;"></i>
                <p>Error al cargar usuarios</p>
            </div>`;
    }
}

function renderUsers() {
    const container = document.getElementById('usersContainer');
    
    if (filteredUsers.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
                <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                <p>No se encontraron usuarios</p>
            </div>`;
        return;
    }
    
    const table = `
        <div class="users-container">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    ${filteredUsers.map(user => {
                        const isInactive = user.nombre.includes('[INACTIVO]');
                        const displayName = user.nombre.replace('[INACTIVO] ', '');
                        const status = isInactive ? 'inactive' : 'active';
                        
                        return `
                        <tr>
                            <td>
                                <div class="user-info">
                                    <img src="${user.foto_url || '../../img/user/default.png'}" 
                                         onerror="this.src='../../img/user/default.png'"
                                         alt="${displayName}" 
                                         class="user-avatar">
                                    <div class="user-details">
                                        <h4>${escapeHtml(displayName)}</h4>
                                        <p>${user.identidad}</p>
                                    </div>
                                </div>
                            </td>
                            <td>${escapeHtml(user.email)}</td>
                            <td><span class="badge badge-info">${user.tipo}</span></td>
                            <td>
                                <span class="status-badge status-${status}">
                                    ${isInactive ? 'Inactivo' : 'Activo'}
                                </span>
                            </td>
                            <td>
                                <div class="user-actions">
                                    <button class="btn-action btn-edit" onclick="editUser(${user.id})" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-toggle" onclick="toggleUserStatus(${user.id}, '${status}')" title="${isInactive ? 'Activar' : 'Desactivar'}">
                                        <i class="fas fa-${isInactive ? 'check' : 'ban'}"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `}).join('')}
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = table;
}

// Buscar usuarios
document.getElementById('searchUsers')?.addEventListener('input', (e) => {
    const search = e.target.value.toLowerCase();
    filteredUsers = allUsers.filter(user => 
        user.nombre.toLowerCase().includes(search) ||
        user.email.toLowerCase().includes(search) ||
        user.identidad.includes(search)
    );
    renderUsers();
});

// Editar usuario
window.editUser = function(userId) {
    const user = allUsers.find(u => u.id === userId);
    if (!user) return;
    
    // Limpiar el nombre de [INACTIVO] si lo tiene
    const cleanName = user.nombre.replace('[INACTIVO] ', '');
    
    document.getElementById('editUserId').value = user.id;
    document.getElementById('editUserName').value = cleanName;
    document.getElementById('editUserEmail').value = user.email;
    document.getElementById('editUserPhone').value = user.telefono || '';
    document.getElementById('editUserType').value = user.tipo;
    
    document.getElementById('editUserModal').classList.add('active');
};

// Cerrar modal
document.getElementById('closeEditModal')?.addEventListener('click', () => {
    document.getElementById('editUserModal').classList.remove('active');
});

document.getElementById('cancelEdit')?.addEventListener('click', () => {
    document.getElementById('editUserModal').classList.remove('active');
});

// Guardar cambios
document.getElementById('editUserForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'updateUser');
    formData.append('id', document.getElementById('editUserId').value);
    formData.append('nombre', document.getElementById('editUserName').value);
    formData.append('email', document.getElementById('editUserEmail').value);
    formData.append('telefono', document.getElementById('editUserPhone').value);
    formData.append('tipo', document.getElementById('editUserType').value);
    
    try {
        const response = await fetch('db/usuarios_api.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            alert('Usuario actualizado correctamente');
            document.getElementById('editUserModal').classList.remove('active');
            loadUsers();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error al actualizar usuario');
        console.error(error);
    }
});

// Toggle estado usuario
window.toggleUserStatus = async function(userId, currentStatus) {
    const newStatus = currentStatus === 'inactive' ? 'active' : 'inactive';
    const action = newStatus === 'inactive' ? 'desactivar' : 'activar';
    
    if (!confirm(`¿Está seguro de ${action} este usuario?`)) return;
    
    const formData = new FormData();
    formData.append('action', 'toggleStatus');
    formData.append('id', userId);
    formData.append('status', newStatus);
    
    try {
        const response = await fetch('db/usuarios_api.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            loadUsers();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error al cambiar estado del usuario');
        console.error(error);
    }
};

// Cargar usuarios al inicio
async function loadUsersForFilter() {
    try {
        const response = await fetch('db/historial_api.php?action=getUsers');
        const data = await response.json();
        
        if (data.success && data.users) {
            const select = document.getElementById('filterUser');
            data.users.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = user.nombre;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Refresh usuarios
document.getElementById('refreshUsersBtn')?.addEventListener('click', () => {
    loadUsers();
});

// Inicialización
document.addEventListener('DOMContentLoaded', () => {
    loadActivities(1, true);
    loadUsersForFilter();
});