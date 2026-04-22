document.addEventListener("DOMContentLoaded", () => {
    let currentCategory = "genero";
    let tipos = [];
    let parametros = [];
    let editingId = null;
    let currentSort = 'default'; // default, alpha-asc, alpha-desc, id-asc, id-desc

    // Cargar tipos de parámetros
    fetch('db/parametros.php?accion=tipos')
        .then(res => res.json())
        .then(data => { tipos = data; });

    // Cambiar categoría
    document.querySelectorAll('.category-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            currentCategory = tab.dataset.category;
            currentSort = 'default'; // Resetear ordenamiento al cambiar categoría
            renderCards();
        });
    });

    // Buscar
    document.getElementById('searchInput').addEventListener('input', renderCards);

    // Renderizar cards desde la base de datos
    async function renderCards() {
        const grid = document.getElementById('parametersGrid');
        grid.innerHTML = '<div class="empty-state">Cargando...</div>';
        const search = document.getElementById('searchInput').value.trim().toLowerCase();
        const res = await fetch(`db/parametros.php?accion=listar&tipo=${currentCategory}`);
        const data = await res.json();

        let filtered = data;
        if (search) {
            filtered = data.filter(p =>
                p.nombre.toLowerCase().includes(search) ||
                (p.descripcion && p.descripcion.toLowerCase().includes(search)) ||
                String(p.id_parametro).includes(search)
            );
        }

        // Aplicar ordenamiento
        filtered = applySortToParameters(filtered);

        if (!filtered.length) {
            grid.innerHTML = `
                <div class="empty-state">
                    <svg width="120" height="120" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 3H4C3.44772 3 3 3.44772 3 4V9C3 9.55228 3.44772 10 4 10H9C9.55228 10 10 9.55228 10 9V4C10 3.44772 9.55228 3 9 3Z" stroke="currentColor" stroke-width="2"/>
                        <path d="M20 3H15C14.4477 3 14 3.44772 14 4V9C14 9.55228 14.4477 10 15 10H20C20.5523 10 21 9.55228 21 9V4C21 3.44772 20.5523 3 20 3Z" stroke="currentColor" stroke-width="2"/>
                        <path d="M9 14H4C3.44772 14 3 14.4477 3 15V20C3 20.5523 3.44772 21 4 21H9C9.55228 21 10 20.5523 10 20V15C10 14.4477 9.55228 14 9 14Z" stroke="currentColor" stroke-width="2"/>
                        <path d="M20 14H15C14.4477 14 14 14.4477 14 15V20C14 20.5523 14.4477 21 15 21H20C20.5523 21 21 20.5523 21 20V15C21 14.4477 20.5523 14 20 14Z" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <h3>No hay parámetros registrados</h3>
                    <p>Comienza agregando tu primer parámetro</p>
                </div>
            `;
            return;
        }

        grid.innerHTML = '';
        filtered.forEach(item => {
            const card = document.createElement('div');
            card.className = 'parameter-card';

            card.innerHTML = `
                <div class="card-header">
                    <div class="card-icon">
                        <img src="parametros/icons/${currentCategory}.png" alt="">
                    </div>
                    <div class="card-info">
                        <div class="formulation-title">${item.nombre}</div>
                        <div class="formulation-subtitle">${currentCategory.charAt(0).toUpperCase() + currentCategory.slice(1)}</div>
                        <div class="card-id">#${String(item.id_parametro).padStart(3, '0')}</div>
                    </div>
                </div>
                <div class="card-details">
                    <div class="detail-item">
                        <span class="detail-label">Descripción</span>
                        <span class="detail-value">${item.descripcion || 'Sin descripción'}</span>
                    </div>
                </div>
                <div class="card-actions">
                    <button class="action-btn edit-btn" onclick="openModal(${item.id_parametro})" title="Editar">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11 4H4C3.46957 4 2.96086 4.21071 2.58579 4.58579C2.21071 4.96086 2 5.46957 2 6V20C2 20.5304 2.21071 21.0391 2.58579 21.4142C2.96086 21.7893 3.46957 22 4 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M18.5 2.50023C18.8978 2.1024 19.4374 1.87891 20 1.87891C20.5626 1.87891 21.1022 2.1024 21.5 2.50023C21.8978 2.89805 22.1213 3.43762 22.1213 4.00023C22.1213 4.56284 21.8978 5.1024 21.5 5.50023L12 15.0002L8 16.0002L9 12.0002L18.5 2.50023Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <button class="action-btn delete-btn" onclick="deleteParameter(${item.id_parametro})" title="Eliminar">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 6H5H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8 6V4C8 3.46957 8.21071 2.96086 8.58579 2.58579C8.96086 2.21071 9.46957 2 10 2H14C14.5304 2 15.0391 2.21071 15.4142 2.58579C15.7893 2.96086 16 3.46957 16 4V6M19 6V20C19 20.5304 18.7893 21.0391 18.4142 21.4142C18.0391 21.7893 17.5304 22 17 22H7C6.46957 22 5.96086 21.7893 5.58579 21.4142C5.21071 21.0391 5 20.5304 5 20V6H19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 11V17M14 11V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            `;
            grid.appendChild(card);
        });
    }

    // Función para aplicar ordenamiento a parámetros
    function applySortToParameters(params) {
        let sorted = [...params];
        
        switch(currentSort) {
            case 'alpha-asc':
                sorted.sort((a, b) => a.nombre.localeCompare(b.nombre));
                break;
            case 'alpha-desc':
                sorted.sort((a, b) => b.nombre.localeCompare(a.nombre));
                break;
            case 'id-asc':
                sorted.sort((a, b) => a.id_parametro - b.id_parametro);
                break;
            case 'id-desc':
                sorted.sort((a, b) => b.id_parametro - a.id_parametro);
                break;
            default: // 'default'
                sorted.sort((a, b) => a.id_parametro - b.id_parametro);
                break;
        }
        
        return sorted;
    }

    // Sistema de filtrado
    window.toggleFilter = function() {
        const filterMenu = document.getElementById('filterMenu');
        if (!filterMenu) {
            createFilterMenu();
        } else {
            filterMenu.classList.toggle('active');
        }
    }

    function createFilterMenu() {
        const filterMenu = document.createElement('div');
        filterMenu.id = 'filterMenu';
        filterMenu.className = 'filter-menu';
        filterMenu.innerHTML = `
            <div class="filter-menu-header">
                <h4>Ordenar por</h4>
                <button class="filter-close" onclick="toggleFilter()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <div class="filter-options">
                <button class="filter-option ${currentSort === 'default' ? 'active' : ''}" onclick="applySortFilter('default')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 4H21M3 8H21M3 12H21M3 16H21M3 20H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Por defecto (ID)</span>
                </button>
                <button class="filter-option ${currentSort === 'alpha-asc' ? 'active' : ''}" onclick="applySortFilter('alpha-asc')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11 7H7M11 12H9M11 17H7M19 7L15 17M19 17L15 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Alfabético (A-Z)</span>
                </button>
                <button class="filter-option ${currentSort === 'alpha-desc' ? 'active' : ''}" onclick="applySortFilter('alpha-desc')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11 7H7M11 12H9M11 17H7M15 7L19 17M15 17L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Alfabético (Z-A)</span>
                </button>
                <button class="filter-option ${currentSort === 'id-desc' ? 'active' : ''}" onclick="applySortFilter('id-desc')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 5V19M12 5L5 12M12 5L19 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>ID Mayor a Menor</span>
                </button>
                <button class="filter-option ${currentSort === 'id-asc' ? 'active' : ''}" onclick="applySortFilter('id-asc')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 19V5M12 19L5 12M12 19L19 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>ID Menor a Mayor</span>
                </button>
            </div>
        `;

        // Agregar estilos si no existen
        if (!document.getElementById('filter-menu-styles')) {
            const style = document.createElement('style');
            style.id = 'filter-menu-styles';
            style.textContent = `
                .filter-menu {
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%) scale(0.9);
                    background: var(--card-bg, #fff);
                    border-radius: 16px;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                    z-index: 11001;
                    width: 400px;
                    max-width: 90vw;
                    opacity: 0;
                    pointer-events: none;
                    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
                }
                .filter-menu.active {
                    opacity: 1;
                    pointer-events: all;
                    transform: translate(-50%, -50%) scale(1);
                }
                .filter-menu-header {
                    padding: 24px;
                    border-bottom: 2px solid var(--border);
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    background: linear-gradient(135deg, #008737 0%, #008737 100%);
                    border-radius: 16px 16px 0 0;
                }
                .filter-menu-header h4 {
                    margin: 0;
                    font-size: 18px;
                    font-weight: 700;
                    color: #fff;
                }
                .filter-close {
                    background: rgba(255, 255, 255, 0.2);
                    border: none;
                    width: 32px;
                    height: 32px;
                    border-radius: 8px;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #fff;
                    transition: all 0.2s;
                }
                .filter-close:hover {
                    background: rgba(255, 255, 255, 0.3);
                    transform: rotate(90deg);
                }
                .filter-options {
                    padding: 16px;
                }
                .filter-option {
                    width: 100%;
                    padding: 16px 20px;
                    background: var(--light-gray, #f8f9fa);
                    border: 2px solid transparent;
                    border-radius: 12px;
                    margin-bottom: 10px;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    gap: 14px;
                    font-size: 15px;
                    font-weight: 600;
                    color: var(--secondary, #374151);
                    transition: all 0.2s;
                    text-align: left;
                }
                .filter-option:hover {
                    background: #0087362f;
                    border-color: #008737;
                    transform: translateX(4px);
                }
                .filter-option.active {
                    background: linear-gradient(135deg, #008737 0%, #008737 100%);
                    color: #fff;
                    border-color: #008737;
                    box-shadow: 0 4px 12px rgba(0, 135, 55, 1);
                }
                .filter-option svg {
                    flex-shrink: 0;
                    opacity: 0.8;
                }
                .filter-option.active svg {
                    opacity: 1;
                }
                .filter-option:last-child {
                    margin-bottom: 0;
                }
                
                body.dark-mode .filter-menu {
                    background: var(--card-bg, #252d3d);
                }
                body.dark-mode .filter-option {
                    background: var(--muted, #1e2530);
                    color: var(--text-primary, #e8e8e8);
                }
            `;
            document.head.appendChild(style);
        }

        document.body.appendChild(filterMenu);
        setTimeout(() => filterMenu.classList.add('active'), 10);

        // Cerrar al hacer clic fuera
        setTimeout(() => {
            document.addEventListener('click', function closeOnOutside(e) {
                if (!filterMenu.contains(e.target) && !e.target.closest('[onclick*="toggleFilter"]')) {
                    filterMenu.classList.remove('active');
                    setTimeout(() => {
                        if (filterMenu.parentNode) {
                            filterMenu.remove();
                        }
                    }, 300);
                    document.removeEventListener('click', closeOnOutside);
                }
            });
        }, 100);
    }

    window.applySortFilter = function(sortType) {
        currentSort = sortType;
        renderCards();
        
        // Actualizar UI del menú de filtros
        const filterMenu = document.getElementById('filterMenu');
        if (filterMenu) {
            filterMenu.querySelectorAll('.filter-option').forEach(option => {
                option.classList.remove('active');
            });
            filterMenu.querySelector(`[onclick="applySortFilter('${sortType}')"]`)?.classList.add('active');
        }
        
        // Cerrar el menú
        toggleFilter();
    }

    // Modal para crear/editar
    window.openModal = function(id = null) {
        editingId = id;
        let nombre = '';
        let descripcion = '';
        let modalTitle = 'Nuevo Parámetro';

        if (id) {
            fetch(`db/parametros.php?accion=listar&tipo=${currentCategory}`)
                .then(res => res.json())
                .then(data => {
                    const found = data.find(p => p.id_parametro == id);
                    if (found) {
                        nombre = found.nombre;
                        descripcion = found.descripcion || '';
                        modalTitle = 'Editar Parámetro';
                    }
                    showModal(nombre, descripcion, modalTitle);
                });
        } else {
            showModal('', '', modalTitle);
        }
    }

    function showModal(nombre = '', descripcion = '', modalTitle = 'Nuevo Parámetro') {
        const modal = document.getElementById('parameterModal');
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modal-title">${modalTitle}</h2>
                    <button class="close-btn" onclick="closeModal()">×</button>
                </div>
                <div class="modal-body">
                    <form id="parameterForm">
                        <div class="form-group">
                            <label>Nombre del parámetro</label>
                            <input type="text" id="nombre" value="${nombre || ''}" required>
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea id="descripcion" rows="4" placeholder="Descripción del parámetro...">${descripcion || ''}</textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancelar</button>
                    <button type="button" class="btn-save" onclick="saveParameter()">Guardar</button>
                </div>
            </div>
        `;
        modal.classList.add('active');
    }

    // Guardar parámetro (crear/editar)
    window.saveParameter = async function() {
        const nombre = document.getElementById('nombre').value.trim();
        const descripcion = document.getElementById('descripcion').value.trim();

        if (!nombre) {
            mostrarNotificacion('Por favor complete el nombre', false);
            return;
        }

        // Buscar el id_tipo correspondiente al currentCategory
        const tipoObj = tipos.find(t => t.nombre === currentCategory);
        if (!tipoObj) {
            mostrarNotificacion('Tipo de parámetro no válido', false);
            return;
        }

        let body = { nombre, descripcion, id_tipo: tipoObj.id_tipo };
        let url = 'db/parametros.php?accion=crear';
        let method = 'POST';
        if (editingId) {
            url = 'db/parametros.php?accion=editar';
            body.id_parametro = editingId;
        }

        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        });
        const result = await res.json();

        if (result.success) {
            mostrarNotificacion(editingId ? 'Parámetro actualizado correctamente' : 'Parámetro creado correctamente', true);
            closeModal();
            renderCards();
        } else {
            mostrarNotificacion(result.error || 'Error al guardar el parámetro', false);
        }
    }

    // Eliminar parámetro
    window.deleteParameter = async function(id) {
        if (!confirm('¿Está seguro de que desea eliminar este parámetro?')) return;
        const res = await fetch('db/parametros.php?accion=eliminar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({id_parametro: id})
        });
        const result = await res.json();
        if (result.success) {
            mostrarNotificacion('Parámetro eliminado correctamente', true);
            renderCards();
        } else {
            mostrarNotificacion(result.error || 'Error al eliminar el parámetro', false);
        }
    }

    // Notificación tipo banner
    function mostrarNotificacion(msg, ok = true) {
        const n = document.getElementById('banner-notificacion');
        n.innerHTML = `
            <div style="
                display:flex;
                align-items:center;
                gap:10px;
                background:${ok ? '#eafaf1' : '#fdecea'};
                color:${ok ? '#218838' : '#c0392b'};
                border:1.5px solid ${ok ? '#2ecc71' : '#e74c3c'};
                border-radius:8px;
                padding:12px 24px;
                font-size:1.1rem;
                font-weight:600;
                box-shadow:0 2px 8px rgba(0,0,0,0.08);
            ">
                <span style="font-size:1.5em;">
                    ${ok
                        ? '<svg width="24" height="24" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="12" fill="#2ecc71"/><path d="M7 13l3 3 7-7" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
                        : '<svg width="24" height="24" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="12" fill="#e74c3c"/><path d="M15 9l-6 6M9 9l6 6" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>'
                    }
                </span>
                <span>${msg}</span>
            </div>
        `;
        n.style.display = 'block';
        setTimeout(() => {
            n.style.display = 'none';
            n.innerHTML = '';
        }, 2500);
    }

    window.closeModal = function() {
        document.getElementById('parameterModal').classList.remove('active');
        document.getElementById('parameterModal').innerHTML = '';
        editingId = null;
    }

    // Inicializar
    renderCards();
});