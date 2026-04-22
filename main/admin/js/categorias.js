let categories = [];
let editingId = null;
let reactivos = [];
let currentSort = 'default'; // default, alpha-asc, alpha-desc, quantity-asc, quantity-desc

// Configuración de la API
const API_BASE = 'db/categorias.php';


// Inicializar la aplicación
document.addEventListener('DOMContentLoaded', async function() {
    await loadReactivos();
    await loadCategories();
    setupEventListeners();
});



function setupEventListeners() {
    // Búsqueda en tiempo real
    document.getElementById('searchInput').addEventListener('input', function() {
        filterCategories(this.value);
    });

    // Formulario de categoría
    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveCategory();
    });

    // Cerrar modal al hacer clic fuera
    document.getElementById('categoryModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
}


// Cargar categorías desde la API
async function loadCategories() {
    try {
        const response = await fetch(API_BASE + '?accion=listar');
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`Error ${response.status}: ${errorText}`);
        }
        
        const data = await response.json();
        
        if (!Array.isArray(data)) {
            throw new Error('Formato de respuesta inválido');
        }
        
        categories = data.map(category => ({
            id: category.id,
            nombre: category.nombre || 'Sin nombre',
            descripcion: category.descripcion || 'Sin descripción',
            cantidad_reactivos: parseInt(category.cantidad_reactivos) || 0
        }));
        
        console.log('Categorías cargadas:', categories);
        
        // Aplicar el ordenamiento actual al cargar
        if (currentSort !== 'default') {
            const searchTerm = document.getElementById('searchInput')?.value || '';
            let filteredCategories = categories.filter(category => 
                category.nombre.toLowerCase().includes(searchTerm.toLowerCase()) ||
                (category.descripcion && category.descripcion.toLowerCase().includes(searchTerm.toLowerCase()))
            );
            applySortToCategories(filteredCategories);
        } else {
            renderCategories(categories);
        }
        
        updateCategoryCount();
    } catch (error) {
        console.error('Error al cargar categorías:', error);
        const errorMessage = error.message || 'Error al cargar las categorías';
        console.error('Detalles del error:', errorMessage);
        
        // Mostrar mensaje de error en la interfaz
        const grid = document.getElementById('categoriesGrid');
        if (grid) {
            grid.innerHTML = `
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M13 13h-2V7h2m0 10h-2v-2h2M12 2A10 10 0 002 12a10 10 0 0010 10 10 10 0 0010-10A10 10 0 0012 2z"/>
                    </svg>
                    <h3>Error al cargar las categorías</h3>
                    <p>${errorMessage}</p>
                </div>
            `;
        }
        
        alert(`Error: ${errorMessage}`);
    }
}


// Cargar reactivos desde la API
async function loadReactivos() {
    try {
        const response = await fetch('db/reactivos_listar.php');
        if (!response.ok) throw new Error('Error al cargar reactivos');
        const data = await response.json();
        // Cargar reactivos completos con categoria_id
        const responseComplete = await fetch('db/reactivos.php');
        if (responseComplete.ok) {
            reactivos = await responseComplete.json();
        } else {
            reactivos = data;
        }
    } catch (error) {
        console.error('Error:', error);
        reactivos = [];
    }
}


// Renderizar categorías en la tabla
function renderCategories(data) {
    const tbody = document.getElementById('categoriesTable');
    tbody.innerHTML = '';
    data.forEach(category => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${String(category.id).padStart(3, '0')}</td>
            <td>
                <span class="category-tag ${category.nombre ? category.nombre.toLowerCase() : ''}">
                    ${category.nombre}
                </span>
            </td>
            <td>${category.descripcion || ''}</td>
            <td>${getReactiveCountByCategory(category.id)}</td>
            <td>
                <div class="actions">
                    <button class="action-btn edit-btn" onclick="editCategory(${category.id})" title="Editar">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="#ff5e00ff" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11 4H4C3.46957 4 2.96086 4.21071 2.58579 4.58579C2.21071 4.96086 2 5.46957 2 6V20C2 20.5304 2.21071 21.0391 2.58579 21.4142C2.96086 21.7893 3.46957 22 4 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V13" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M18.5 2.50023C18.8978 2.1024 19.4374 1.87891 20 1.87891C20.5626 1.87891 21.1022 2.1024 21.5 2.50023C21.8978 2.89805 22.1213 3.43762 22.1213 4.00023C22.1213 4.56284 21.8978 5.1024 21.5 5.50023L12 15.0002L8 16.0002L9 12.0002L18.5 2.50023Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <button class="action-btn view-btn" onclick="viewCategory(${category.id})" title="Ver">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="#00aeffff" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <button class="action-btn delete-btn" onclick="deleteCategory(${category.id})" title="Eliminar">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="#ff0000ff" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 6H5H21" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8 6V4C8 3.46957 8.21071 2.96086 8.58579 2.58579C8.96086 2.21071 9.46957 2 10 2H14C14.5304 2 15.0391 2.21071 15.4142 2.58579C15.7893 2.96086 16 3.46957 16 4V6M19 6V20C19 20.5304 18.7893 21.0391 18.4142 21.4142C18.0391 21.7893 17.5304 22 17 22H7C6.46957 22 5.96086 21.7893 5.58579 21.4142C5.21071 21.0391 5 20.5304 5 20V6H19Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 11V17M14 11V17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Filtrar categorías
function filterCategories(searchTerm) {
    const filtered = categories.filter(category => 
        category.nombre.toLowerCase().includes(searchTerm.toLowerCase()) ||
        (category.descripcion && category.descripcion.toLowerCase().includes(searchTerm.toLowerCase()))
    );
    
    // Aplicar ordenamiento actual si existe
    applySortToCategories(filtered);
}

// Función auxiliar para aplicar ordenamiento sin cerrar menú
function applySortToCategories(categoriesToSort) {
    let sortedCategories = [...categoriesToSort];
    
    switch(currentSort) {
        case 'alpha-asc':
            sortedCategories.sort((a, b) => a.nombre.localeCompare(b.nombre));
            break;
        case 'alpha-desc':
            sortedCategories.sort((a, b) => b.nombre.localeCompare(a.nombre));
            break;
        case 'quantity-asc':
            sortedCategories.sort((a, b) => 
                getReactiveCountByCategory(a.id) - getReactiveCountByCategory(b.id)
            );
            break;
        case 'quantity-desc':
            sortedCategories.sort((a, b) => 
                getReactiveCountByCategory(b.id) - getReactiveCountByCategory(a.id)
            );
            break;
        default:
            sortedCategories.sort((a, b) => a.id - b.id);
            break;
    }
    
    renderCategories(sortedCategories);
}

// Actualizar contador
function updateCategoryCount() {
    document.getElementById('categoryCount').textContent = String(categories.length).padStart(2, '0');
}

// Abrir modal
function openModal(isEdit = false, categoryData = null) {
    const modal = document.getElementById('categoryModal');
    const modalTitle = document.getElementById('modalTitle');
    const form = document.getElementById('categoryForm');

    if (isEdit && categoryData) {
        modalTitle.textContent = 'Editar categoría';
        document.getElementById('categoryName').value = categoryData.nombre;
        document.getElementById('categoryDescription').value = categoryData.descripcion || '';
        editingId = categoryData.id;
    } else {
        modalTitle.textContent = 'Nueva categoría';
        form.reset();
        editingId = null;
    }

    clearErrors();
    modal.style.display = 'block';
}

// Cerrar modal
function closeModal() {
    document.getElementById('categoryModal').style.display = 'none';
    document.getElementById('categoryForm').reset();
    editingId = null;
    clearErrors();
}

// Limpiar errores
function clearErrors() {
    document.getElementById('nameError').textContent = '';
}

// Guardar categoría
async function saveCategory() {
    const nameInput = document.getElementById('categoryName');
    const descriptionInput = document.getElementById('categoryDescription');
    const saveBtn = document.getElementById('saveBtn');

    clearErrors();

    // Validación
    if (!nameInput.value.trim()) {
        document.getElementById('nameError').textContent = 'El nombre es obligatorio';
        return;
    }

    const categoryData = {
        nombre: nameInput.value.trim(),
        descripcion: descriptionInput.value.trim()
    };

    try {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span>⏳</span> Guardando...';

        let response;
        if (editingId) {
            // Actualizar
            response = await fetch(`${API_BASE}?id=${editingId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(categoryData)
            });
        } else {
            // Crear
            response = await fetch(API_BASE, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(categoryData)
            });
        }

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Error al guardar la categoría');
        }

        await loadCategories();
        closeModal();
        
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('nameError').textContent = error.message;
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<span>💾</span> Guardar';
    }
}

// Editar categoría
function editCategory(id) {
    const category = categories.find(cat => cat.id == id);
    if (category) {
        openModal(true, category);
    }
}

// Eliminar categoría
async function deleteCategory(id) {
    const category = categories.find(cat => cat.id == id);
    if (!category) return;

    if (!confirm(`¿Estás seguro de que deseas eliminar la categoría "${category.nombre}"?`)) {
        return;
    }

    try {
        const response = await fetch(`${API_BASE}?id=${id}`, {
            method: 'DELETE'
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Error al eliminar la categoría');
        }

        await loadCategories();
    } catch (error) {
        console.error('Error:', error);
        alert('Error al eliminar la categoría: ' + error.message);
    }
}

// Sistema de filtrado mejorado
function toggleFilter() {
    const filterMenu = document.getElementById('filterMenu');
    if (!filterMenu) {
        createFilterMenu();
    } else {
        filterMenu.classList.toggle('active');
    }
}

function createFilterMenu() {
    // Crear menú de filtros si no existe
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
            <button class="filter-option ${currentSort === 'quantity-desc' ? 'active' : ''}" onclick="applySortFilter('quantity-desc')">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 5V19M12 5L5 12M12 5L19 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Mayor cantidad</span>
            </button>
            <button class="filter-option ${currentSort === 'quantity-asc' ? 'active' : ''}" onclick="applySortFilter('quantity-asc')">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 19V5M12 19L5 12M12 19L19 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Menor cantidad</span>
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
                z-index: 10000;
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
                background: var(--muted, #f8f9fa);
                border: 2px solid transparent;
                border-radius: 12px;
                margin-bottom: 10px;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 14px;
                font-size: 15px;
                font-weight: 600;
                color: var(--text-primary, #008737);
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
    // Activar después de un pequeño delay para la animación
    setTimeout(() => filterMenu.classList.add('active'), 10);

    // Cerrar al hacer clic fuera
    setTimeout(() => {
        document.addEventListener('click', function closeOnOutside(e) {
            if (!filterMenu.contains(e.target) && !e.target.closest('.add-btn')) {
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

function applySortFilter(sortType) {
    currentSort = sortType;
    
    // Obtener categorías actuales (puede estar filtradas por búsqueda)
    const searchTerm = document.getElementById('searchInput').value;
    let filteredCategories = categories.filter(category => 
        category.nombre.toLowerCase().includes(searchTerm.toLowerCase()) ||
        (category.descripcion && category.descripcion.toLowerCase().includes(searchTerm.toLowerCase()))
    );

    // Aplicar ordenamiento
    let sortedCategories = [...filteredCategories];
    
    switch(sortType) {
        case 'alpha-asc':
            sortedCategories.sort((a, b) => a.nombre.localeCompare(b.nombre));
            break;
        case 'alpha-desc':
            sortedCategories.sort((a, b) => b.nombre.localeCompare(a.nombre));
            break;
        case 'quantity-asc':
            sortedCategories.sort((a, b) => 
                getReactiveCountByCategory(a.id) - getReactiveCountByCategory(b.id)
            );
            break;
        case 'quantity-desc':
            sortedCategories.sort((a, b) => 
                getReactiveCountByCategory(b.id) - getReactiveCountByCategory(a.id)
            );
            break;
        default: // 'default'
            sortedCategories.sort((a, b) => a.id - b.id);
            break;
    }

    renderCategories(sortedCategories);
    
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

function getReactiveCountByCategory(categoryId) {
    return reactivos.filter(r => String(r.categoria_id) === String(categoryId)).length;
}

// Ver categoría - MODAL MEJORADO
function viewCategory(id) {
    const category = categories.find(cat => cat.id == id);
    if (!category) return;
    
    const count = getReactiveCountByCategory(category.id);
    const maxCount = Math.max(...categories.map(c => getReactiveCountByCategory(c.id)), 1);
    const percentage = (count / maxCount) * 100;
    
    // Determinar nivel y color
    let level = 'Bajo';
    let levelColor = '#fbbf24';
    let barColor = '#fbbf24';
    
    if (percentage >= 70) {
        level = 'Alto';
        levelColor = '#10b981';
        barColor = '#10b981';
    } else if (percentage >= 40) {
        level = 'Medio';
        levelColor = '#3b82f6';
        barColor = '#3b82f6';
    }
    
    const modal = document.createElement('div');
    modal.className = 'view-modal-bg';
    modal.innerHTML = `
        <div class="view-modal-enhanced">
            <div class="modal-view-header">
                <div class="category-icon-large">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 3H4C3.44772 3 3 3.44772 3 4V9C3 9.55228 3.44772 10 4 10H9C9.55228 10 10 9.55228 10 9V4C10 3.44772 9.55228 3 9 3Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M20 3H15C14.4477 3 14 3.44772 14 4V9C14 9.55228 14.4477 10 15 10H20C20.5523 10 21 9.55228 21 9V4C21 3.44772 20.5523 3 20 3Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 14H4C3.44772 14 3 14.4477 3 15V20C3 20.5523 3.44772 21 4 21H9C9.55228 21 10 20.5523 10 20V15C10 14.4477 9.55228 14 9 14Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M20 14H15C14.4477 14 14 14.4477 14 15V20C14 20.5523 14.4477 21 15 21H20C20.5523 21 21 20.5523 21 20V15C21 14.4477 20.5523 14 20 14Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="modal-view-title">
                    <h2>Categoría #${String(category.id).padStart(3, '0')}</h2>
                    <p>Detalles completos de la categoría</p>
                </div>
                <button class="modal-close-enhanced" onclick="this.closest('.view-modal-bg').remove()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

            <div class="modal-view-body">
                <div class="info-card">
                    <div class="info-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 7H20M10 11V17M14 11V17M5 7L6 19C6 19.5304 6.21071 20.0391 6.58579 20.4142C6.96086 20.7893 7.46957 21 8 21H16C16.5304 21 17.0391 20.7893 17.4142 20.4142C17.7893 20.0391 18 19.5304 18 19L19 7M9 7V4C9 3.73478 9.10536 3.48043 9.29289 3.29289C9.48043 3.10536 9.73478 3 10 3H14C14.2652 3 14.5196 3.10536 14.7071 3.29289C14.8946 3.48043 15 3.73478 15 4V7" stroke="#007832" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="info-content">
                        <label>Nombre de categoría</label>
                        <p class="info-value">${category.nombre}</p>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="#007832" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14 2V8H20M16 13H8M16 17H8M10 9H8" stroke="#007832" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="info-content">
                        <label>Descripción</label>
                        <p class="info-value">${category.descripcion || 'Sin descripción proporcionada'}</p>
                    </div>
                </div>

                <div class="info-card highlight">
                    <div class="info-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 11L12 14L22 4M21 12V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H16" stroke="#007832" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="info-content full-width">
                        <div class="quantity-header">
                            <label>Reactivos registrados</label>
                            <div class="quantity-badge" style="background: ${levelColor}20; color: ${levelColor};">
                                <span class="quantity-number">${count}</span>
                                <span class="quantity-level">Nivel ${level}</span>
                            </div>
                        </div>
                        
                        <div class="progress-container">
                            <div class="progress-bar" style="--progress: ${percentage}%; --bar-color: ${barColor};">
                                <div class="progress-fill"></div>
                            </div>
                            <div class="progress-labels">
                                <span>0</span>
                                <span>${maxCount}</span>
                            </div>
                        </div>

                        <div class="level-indicators">
                            <div class="level-item ${percentage < 40 ? 'active' : ''}">
                                <div class="level-dot" style="background: #fbbf24;"></div>
                                <span>Bajo (0-39%)</span>
                            </div>
                            <div class="level-item ${percentage >= 40 && percentage < 70 ? 'active' : ''}">
                                <div class="level-dot" style="background: #3b82f6;"></div>
                                <span>Medio (40-69%)</span>
                            </div>
                            <div class="level-item ${percentage >= 70 ? 'active' : ''}">
                                <div class="level-dot" style="background: #10b981;"></div>
                                <span>Alto (70-100%)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-view-footer">
                <button class="btn-close-modal" onclick="this.closest('.view-modal-bg').remove()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Cerrar
                </button>
            </div>
        </div>
    `;
    
    // Añadir estilos si no existen
    if (!document.getElementById('modal-view-styles')) {
        const style = document.createElement('style');
        style.id = 'modal-view-styles';
        style.textContent = `
            .view-modal-bg { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(4px); z-index: 11001; display: flex; align-items: center; justify-content: center; padding: 20px; animation: fadeIn 0.3s ease; }
            @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
            .view-modal-enhanced { background: var(--card-bg, #fff); border-radius: 20px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); max-width: 600px; width: 100%; max-height: 90vh; overflow: hidden; animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); display: flex; flex-direction: column; }
            @keyframes slideUp { from { opacity: 0; transform: translateY(40px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
            .modal-view-header {background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);color: white;padding: 32px;border-radius: var(--radius-lg) var(--radius-lg) 0 0;display: flex;justify-content: space-between;align-items: center; position: relative}
            .category-icon-large { width: 64px; height: 64px; background: rgba(255, 255, 255, 0.2); border-radius: 16px; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(10px); }
            .modal-view-title h2 { margin: 0; color: #fff; font-size: 24px; font-weight: 700; letter-spacing: -0.5px; }
            .modal-view-title p { margin: 4px 0 0 0; color: rgba(255, 255, 255, 0.9); font-size: 14px; }
            .modal-close-enhanced { position: absolute; top: 20px; right: 20px; width: 36px; height: 36px; border: none; background: rgba(255, 255, 255, 0.2); border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #fff; transition: all 0.2s; }
            .modal-close-enhanced:hover { background: rgba(255, 255, 255, 0.3); transform: rotate(90deg); }
            .modal-view-body { padding: 32px; overflow-y: auto; flex: 1; }
            .info-card { display: flex; gap: 16px; padding: 20px; background: var(--muted, #f8faf9); border-radius: 12px; margin-bottom: 16px; border: 1px solid var(--border, #e5e7eb); transition: all 0.2s; }
            .info-card:hover { background: var(--primary-bg, #f0f4f2); border-color: var(--primary, #007832); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 120, 50, 0.1); }
            .info-card.highlight { background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #00783220; }
            .info-icon { width: 44px; height: 44px; background: var(--card-bg, #fff); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0, 120, 50, 0.08); }
            .info-content { flex: 1; }
            .info-content.full-width { width: 100%; }
            .info-content label { display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary, #6b7280); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
            .info-value { margin: 0; font-size: 16px; font-weight: 500; color: var(--text-primary, #1f2937); line-height: 1.5; }
            .quantity-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
            .quantity-badge { display: flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 20px; font-weight: 600; }
            .quantity-number { font-size: 24px; font-weight: 700; }
            .quantity-level { font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
            .progress-container { margin: 20px 0; }
            .progress-bar { height: 12px; background: var(--border, #e5e7eb); border-radius: 10px; overflow: hidden; position: relative; }
            .progress-fill { height: 100%; width: var(--progress); background: var(--bar-color); border-radius: 10px; transition: width 1s cubic-bezier(0.16, 1, 0.3, 1); position: relative; overflow: hidden; }
            .progress-fill::after { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent); animation: shimmer 2s infinite; }
            @keyframes shimmer { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
            .progress-labels { display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px; color: var(--text-secondary, #6b7280); font-weight: 500; }
            .level-indicators { display: flex; gap: 16px; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border, #e5e7eb); }
            .level-item { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-secondary, #6b7280); opacity: 0.5; transition: all 0.3s; }
            .level-item.active { opacity: 1; font-weight: 600; }
            .level-dot { width: 10px; height: 10px; border-radius: 50%; transition: all 0.3s; }
            .level-item.active .level-dot { width: 12px; height: 12px; box-shadow: 0 0 0 3px currentColor; opacity: 0.3; }
            .modal-view-footer { padding: 20px 32px; background: var(--muted, #f9fafb); border-top: 1px solid var(--border, #e5e7eb); display: flex; justify-content: flex-end; }
            .btn-close-modal { background: var(--primary, #007832); color: #fff; border: none; border-radius: 10px; padding: 12px 28px; font-size: 15px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s; }
            .btn-close-modal:hover { background: var(--primary-light, #00a844); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0, 120, 50, 0.3); }
            .btn-close-modal:active { transform: translateY(0); }
            
            body.dark-mode .view-modal-enhanced { background: var(--card-bg, #252d3d); }
            body.dark-mode .info-card { background: var(--muted, #1e2530); border-color: var(--border, #2d3748); }
            body.dark-mode .info-card:hover { background: #2a3142; border-color: var(--primary, #007832); }
            body.dark-mode .info-icon { background: var(--card-bg, #252d3d); }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(modal);
}