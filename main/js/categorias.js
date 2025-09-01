
let categories = [];
let editingId = null;


// Configuración de la API
const API_BASE = 'db/categorias.php';


// Inicializar la aplicación
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
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
        const response = await fetch(API_BASE);
        if (!response.ok) throw new Error('Error al cargar categorías');
        categories = await response.json();
        renderCategories(categories);
        updateCategoryCount();
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar las categorías');
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
            <td>${getRandomReactiveCount()}</td>
            <td>
                <div class="actions">
                    <button class="action-btn edit-btn" onclick="editCategory(${category.id})" title="Editar">
                        ✏️
                    </button>
                    <button class="action-btn view-btn" onclick="viewCategory(${category.id})" title="Ver">
                        👁️
                    </button>
                    <button class="action-btn delete-btn" onclick="deleteCategory(${category.id})" title="Eliminar">
                        🗑️
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

        // Función auxiliar para generar cantidad aleatoria (ya que no está en la BD)
        function getRandomReactiveCount() {
            return Math.floor(Math.random() * 20) + 1;
        }

        // Filtrar categorías
        function filterCategories(searchTerm) {
            const filtered = categories.filter(category => 
                category.nombre.toLowerCase().includes(searchTerm.toLowerCase()) ||
                (category.descripcion && category.descripcion.toLowerCase().includes(searchTerm.toLowerCase()))
            );
            renderCategories(filtered);
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

        // Ver categoría
        function viewCategory(id) {
            const category = categories.find(cat => cat.id == id);
            if (category) {
                alert(`Categoría: ${category.nombre}\nDescripción: ${category.descripcion || 'Sin descripción'}`);
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

        // Función placeholder para filtro
        function toggleFilter() {
            alert('Función de filtro en desarrollo');
        }