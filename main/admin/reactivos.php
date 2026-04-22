<?php
require_once '../includes/auth_check.php';
?>
<!-- rest of your HTML content -->

<!DOCTYPE html>
<html lang="es">
<head>
    <script>
        // Verificar y aplicar preferencia de modo oscuro al cargar
        (function() {
            const darkMode = localStorage.getItem('darkMode');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            // Aplicar modo oscuro SOLO si está explícitamente habilitado
            // O si no hay preferencia guardada Y el sistema prefiere oscuro
            if (darkMode === 'enabled') {
                document.documentElement.classList.add('dark-mode');
            } else if (darkMode === 'disabled') {
                // Asegurar que NO esté en modo oscuro
                document.documentElement.classList.remove('dark-mode');
            } else if (prefersDark) {
                // Solo si no hay preferencia guardada y el sistema prefiere oscuro
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reactivos</title>
    <link rel="stylesheet" href="css/formul.css">
    <link rel="stylesheet" href="css/header-footer.css">
    <link rel="stylesheet" href="css/reactivos.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <style>
        .edit-btn, .delete-btn {
            width: 36px !important;
            height: 36px !important;
        }
        
        .edit-btn svg, .delete-btn svg {
            width: 18px !important;
            height: 18px !important;
            stroke: white !important;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
            box-shadow: 0 1px 0 rgba(0,0,0,0.05);
        }
        .status-badge.activo { background:#E6F6EA; color:#14794D; }
        .status-badge.inactivo { background:#FDECEA; color:#A12A2A; }
        .status-badge.agotado { background:#FFF4E6; color:#B35A00; }
    </style>
</head>
<body>
    <div id="banner-notificacion" class="notification"></div>

    <div class="container">
        <div class="controls">
            <div class="search-box">
                <span class="search-icon">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z"/>
                    </svg>
                </span>
                <input type="text" class="search-input" placeholder="Buscar reactivo..." id="searchInput" autocomplete="off">
            </div>
            
            <div class="filter-section">
                <div class="filter-group">
                    <label for="sortSelect" class="filter-label">Ordenar por:</label>
                    <select id="sortSelect" class="form-select" onchange="applyFilters()">
                        <option value="">Seleccionar...</option>
                        <option value="name_asc">Nombre (A-Z)</option>
                        <option value="name_desc">Nombre (Z-A)</option>
                        <option value="date_desc">Más recientes</option>
                        <option value="date_asc">Más antiguos</option>
                        <option value="quantity_asc">Menor cantidad</option>
                        <option value="quantity_desc">Mayor cantidad</option>
                        <option value="expiry_asc">Próximos a vencer</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="categoryFilter" class="filter-label">Categoría:</label>
                    <select id="categoryFilter" class="form-select" onchange="applyFilters()">
                        <option value="">Todas</option>
                    </select>
                </div>
            </div>
            
            <button class="add-btn" onclick="openModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="#ffffff">
                    <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                </svg>
                <span class="btn-text">Nuevo Reactivo</span>
            </button>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>IMAGEN</th>
                        <th>REACTIVO</th>
                        <th>CATEGORÍA</th>
                        <th>CANTIDAD</th>
                        <th>VENCIMIENTO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody id="reactivesTable">
                    <!-- Contenido dinámico -->
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal" id="reactiveModal">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg, var(--primary), var(--primary-light));color:#fff;">
                <h3 class="modal-title" id="modalTitle" style="font-size:1.5em;font-weight:700;">Nuevo Reactivo</h3>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <form id="reactiveForm">
                <div class="modal-body">
                    <div class="form-row" style="display:flex;gap:24px;">
                        <input type="hidden" id="userId" value="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '1'; ?>">
                        <div class="form-group" style="flex:1;">
                            <label class="form-label" for="commonName">Nombre común</label>
                            <input type="text" class="form-input" id="commonName" placeholder="Ej: Ácido Sulfúrico" required>
                            <div class="error" id="nameError"></div>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label class="form-label" for="chemicalFormula">Fórmula química</label>
                            <input type="text" class="form-input" id="chemicalFormula" placeholder="Ej: H₂SO₄">
                        </div>
                    </div>
                    <div class="form-row" style="display:flex;gap:24px;">
                        <div class="form-group" style="flex:1;">
                            <label class="form-label" for="category">Categoría</label>
                            <select class="form-select" id="category" required>
                                <option value="">Seleccionar categoría</option>
                            </select>
                            <div class="error" id="categoryError"></div>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label class="form-label" for="unitMeasure">Unidad de medida</label>
                            <input type="text" class="form-input" id="unitMeasure" placeholder="Ej: ml, g, kg">
                        </div>
                    </div>
                    <div class="form-row" style="display:flex;gap:24px;">
                        <div class="form-group" style="flex:1;">
                            <label class="form-label" for="totalQuantity">Cantidad total</label>
                            <input type="number" class="form-input" id="totalQuantity" placeholder="0.00" step="0.01" min="0">
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label class="form-label" for="expiryDate">Fecha de vencimiento</label>
                            <input type="date" class="form-input" id="expiryDate">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label class="form-label" for="reactiveImage">Imagen</label>
                            <input type="file" class="form-input" id="reactiveImage" accept="image/*">
                            <div style="margin-top:8px;">
                                <img id="imagePreview" src="" alt="Vista previa" style="max-width:120px; max-height:120px; display:none; border-radius:8px; border:1px solid #eee;">
                            </div>
                        </div>
                    </div>
                    <div class="form-row" style="margin-top:12px;">
                        <div class="form-group" style="flex:1;">
                            <label class="form-label" for="estado">Estado</label>
                            <select id="estado" class="form-select">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                                <option value="agotado">Agotado</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn-cancel" onclick="closeModal()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M18 6L6 18M6 6L18 18"/>
                        </svg>
                        Cancelar
                    </button>
                    <button type="submit" class="btn-save" id="saveBtn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5C3.89543 21 3 20.1046 3 19V5C3 3.89543 3.89543 3 5 3H16L21 8V19C21 20.1046 20.1046 21 19 21Z"/>
                            <path d="M7 3V8H15"/>
                            <path d="M17 21V13H7V21"/>
                        </svg>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let reactives = [];
        let categories = [];
        let editingId = null;

        const REACTIVE_API = 'db/reactivos.php';
        const CATEGORY_API = 'db/categorias.php';

        document.addEventListener('DOMContentLoaded', function() {
            // Aplicar el modo oscuro correctamente al cargar la página
            applyDarkModeOnLoad();
            
            loadCategories();
            loadReactives();
            setupEventListeners();
        });

        // Función para aplicar el modo oscuro al cargar
        function applyDarkModeOnLoad() {
            const darkMode = localStorage.getItem('darkMode');
            const html = document.documentElement;
            const body = document.body;
            
            if (darkMode === 'enabled') {
                html.classList.add('dark-mode');
                body.classList.add('dark-mode');
                body.style.backgroundColor = 'var(--bg-primary)';
                body.style.color = 'var(--text-primary)';
            } else if (darkMode === 'disabled') {
                html.classList.remove('dark-mode');
                body.classList.remove('dark-mode');
                body.style.backgroundColor = '';
                body.style.color = '';
            } else {
                // Sin preferencia guardada, usar preferencia del sistema
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (prefersDark) {
                    html.classList.add('dark-mode');
                    body.classList.add('dark-mode');
                    body.style.backgroundColor = 'var(--bg-primary)';
                    body.style.color = 'var(--text-primary)';
                } else {
                    html.classList.remove('dark-mode');
                    body.classList.remove('dark-mode');
                    body.style.backgroundColor = '';
                    body.style.color = '';
                }
            }
        }

        function showNotification(msg, type = 'success') {
            const banner = document.getElementById('banner-notificacion');
            banner.textContent = msg;
            banner.style.display = 'block';
            banner.className = 'notification ' + (type === 'error' ? 'error' : 'success');
            setTimeout(() => { banner.style.display = 'none'; }, 3500);
        }

        function setupEventListeners() {
            document.getElementById('searchInput').addEventListener('input', function() {
                filterReactives(this.value);
            });
            
            // Inicializar el filtro
            applyFilters();
            document.getElementById('reactiveForm').addEventListener('submit', function(e) {
                e.preventDefault();
                saveReactive();
            });
            document.getElementById('reactiveModal').addEventListener('click', function(e) {
                if (e.target === this) closeModal();
            });
            document.getElementById('reactiveImage').addEventListener('change', function() {
                const file = this.files[0];
                const preview = document.getElementById('imagePreview');
                if (file) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.src = '';
                    preview.style.display = 'none';
                }
            });
        }

        async function loadCategories() {
            try {
                const response = await fetch(CATEGORY_API);
                if (!response.ok) throw new Error('Error al cargar categorías');
                categories = await response.json();
                populateCategorySelect();
                populateCategoryFilter();
            } catch (error) {
                showNotification('No se pudieron cargar las categorías', 'error');
            }
        }
        
        function populateCategoryFilter() {
            const select = document.getElementById('categoryFilter');
            // Mantener la opción por defecto
            select.innerHTML = '<option value="">Todas las categorías</option>';
            
            // Ordenar categorías alfabéticamente
            const sortedCategories = [...categories].sort((a, b) => 
                a.nombre.localeCompare(b.nombre)
            );
            
            // Agregar categorías al filtro
            sortedCategories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.nombre;
                select.appendChild(option);
            });
        }

        function populateCategorySelect() {
            const select = document.getElementById('category');
            select.innerHTML = '<option value="">Seleccionar categoría</option>';
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.nombre;
                select.appendChild(option);
            });
        }

        async function loadReactives() {
            try {
                const response = await fetch(REACTIVE_API);
                if (!response.ok) throw new Error('Error al cargar reactivos');
                reactives = await response.json();
                renderReactives(reactives);
            } catch (error) {
                showNotification('No se pudieron cargar los reactivos', 'error');
            }
        }

        function applyFilters() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const sortBy = document.getElementById('sortSelect').value;
            const categoryId = document.getElementById('categoryFilter').value;
            
            let filtered = [...reactives];
            
            // Filtrar por término de búsqueda
            if (searchTerm) {
                filtered = filtered.filter(reactive => 
                    reactive.nombre_comun.toLowerCase().includes(searchTerm) ||
                    (reactive.descripcion && reactive.descripcion.toLowerCase().includes(searchTerm))
                );
            }
            
            // Filtrar por categoría
            if (categoryId) {
                filtered = filtered.filter(reactive => 
                    String(reactive.categoria_id) === String(categoryId)
                );
            }
            
            // Ordenar
            filtered.sort((a, b) => {
                switch(sortBy) {
                    case 'name_asc':
                        return a.nombre_comun.localeCompare(b.nombre_comun);
                    case 'name_desc':
                        return b.nombre_comun.localeCompare(a.nombre_comun);
                    case 'date_asc':
                        return new Date(a.fecha_creacion) - new Date(b.fecha_creacion);
                    case 'date_desc':
                        return new Date(b.fecha_creacion) - new Date(a.fecha_creacion);
                    case 'quantity_asc':
                        return (parseFloat(a.cantidad_total) || 0) - (parseFloat(b.cantidad_total) || 0);
                    case 'quantity_desc':
                        return (parseFloat(b.cantidad_total) || 0) - (parseFloat(a.cantidad_total) || 0);
                    case 'expiry_asc':
                        return calcularDiasParaVencer(a.fecha_vencimiento) - calcularDiasParaVencer(b.fecha_vencimiento);
                    default:
                        return 0;
                }
            });
            
            renderReactives(filtered);
        }
        
        function filterReactives(searchTerm) {
            applyFilters();
        }
        
        function renderReactives(data) {
            const tbody = document.getElementById('reactivesTable');
            tbody.innerHTML = '';
            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="7" style="padding:60px;text-align:center;color:#999;"><div><h3>No hay reactivos registrados</h3><p>Agrega tu primer reactivo usando el botón Nuevo Reactivo.</p></div></td></tr>`;
                return;
            }
            data.forEach(reactive => {
                const row = document.createElement('tr');
                const quantity = parseFloat(reactive.cantidad_total) || 0;
                const maxQuantity = 2000;

                const minimo = 200;
                const diasParaVencer = calcularDiasParaVencer(reactive.fecha_vencimiento);

                const percentage = Math.min((quantity / maxQuantity) * 100, 100);
                const expiryDays = calculateDaysToExpiry(reactive.fecha_vencimiento);
                const expiryDisplay = formatExpiryDate(reactive.fecha_vencimiento);

                // ALERTAS AUTOMÁTICAS:
                if (quantity <= minimo && !reactive.alerta_enviada_minimo) {
                    enviarAlertaCorreo(reactive, minimo, 'cantidad');
                    reactive.alerta_enviada_minimo = true;
                }

                if (diasParaVencer <= 15 && diasParaVencer >= 0 && !reactive.alerta_enviada_vencimiento) {
                    enviarAlertaCorreo(reactive, diasParaVencer, 'vencimiento');
                    reactive.alerta_enviada_vencimiento = true;
                }

                row.innerHTML = `
                    <td>${String(reactive.id).padStart(3, '0')}</td>
                    <td>
                        ${reactive.imagen ? `<img src="img/reactivos/${reactive.imagen}" alt="Imagen" style="max-width:40px;max-height:40px;border-radius:6px;">` : ''}
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
                            <div style="flex:1;min-width:0;">
                                <div class="reactive-name" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${reactive.nombre_comun}</div>
                                <div class="chemical-formula">${formatChemicalFormula(reactive.formula_quimica)}</div>
                            </div>
                            <div style="flex:0 0 auto;margin-left:8px;">
                                <span class="status-badge ${reactive.estado ? reactive.estado.toLowerCase() : 'activo'}">${reactive.estado ? reactive.estado : 'activo'}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        ${reactive.categoria_nombre ? `<span class="category-tag ${reactive.categoria_nombre.toLowerCase()}">${reactive.categoria_nombre}</span>` : ''}
                    </td>
                    <td>
                        <div class="quantity-container">
                            <div class="quantity-text">${quantity} / ${maxQuantity} ${reactive.unidad_medida || 'g'}</div>
                            <div class="quantity-bar-container">
                                <div class="quantity-bar" style="width: ${percentage}%"></div>
                            </div>
                            <div class="quantity-status">${quantity < 200 ? 'Bajo' : quantity < 800 ? 'Normal' : 'Alto'}</div>
                        </div>
                    </td>
                    <td>
                        <div class="expiry-container">
                            <div class="expiry-days" style="color:${expiryDays <= 7 ? '#e74c3c' : '#4CAF50'};">${expiryDays} días</div>
                            <div class="expiry-date">${expiryDisplay}</div>
                        </div>
                    </td>
                    <td>
                        <div class="actions">
                            <button class="action-btn edit-btn" onclick="editReactive(${reactive.id})" title="Editar">
                                 <svg width="16" height="16" viewBox="0 0 24 24" fill="#ff5e00ff" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M11 4H4C3.46957 4 2.96086 4.21071 2.58579 4.58579C2.21071 4.96086 2 5.46957 2 6V20C2 20.5304 2.21071 21.0391 2.58579 21.4142C2.96086 21.7893 3.46957 22 4 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V13" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M18.5 2.50023C18.8978 2.1024 19.4374 1.87891 20 1.87891C20.5626 1.87891 21.1022 2.1024 21.5 2.50023C21.8978 2.89805 22.1213 3.43762 22.1213 4.00023C22.1213 4.56284 21.8978 5.1024 21.5 5.50023L12 15.0002L8 16.0002L9 12.0002L18.5 2.50023Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteReactive(${reactive.id})" title="Eliminar">
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

        // === ENVÍO DE ALERTAS POR CORREO (CORREGIDO) ===
        async function enviarAlertaCorreo(reactivo, valor, tipo) {
            // ✅ CORRECCIÓN: Mapear tipos correctamente
            let tipoCorreo = tipo;
            if (tipo === 'cantidad') {
                tipoCorreo = 'stock'; // Cambiar 'cantidad' a 'stock'
            }
            
            const payload = {
                id: reactivo.id,
                nombre: reactivo.nombre_comun,
                categoria: reactivo.categoria_nombre || 'Sin categoría',
                cantidad: reactivo.cantidad_total || 0,
                um: reactivo.unidad_medida || 'g',
                tipo: tipoCorreo, // 'stock' o 'vencimiento'
                valor: valor,
                fecha_vencimiento: reactivo.fecha_vencimiento || ''
            };

            console.log(`📦 Enviando alerta de tipo: ${tipoCorreo}`, payload);

            try {
                const res = await fetch('db/send_alert.php', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });

                const text = await res.text();
                console.log("📩 Respuesta del servidor (raw):", text);

                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('⚠️ Error al parsear JSON:', e);
                    console.error('Respuesta recibida:', text);
                    return;
                }

                if (data.success) {
                    if (data.duplicada) {
                        console.log(`ℹ️ Alerta ya enviada previamente (sin cambios): ${reactivo.nombre_comun}`);
                    } else {
                        console.log(`✅ Correo enviado (${tipoCorreo}): ${reactivo.nombre_comun}`);
                    }
                } else {
                    console.warn(`⚠️ Fallo al enviar correo (${tipoCorreo}):`, data.error || data.message);
                    console.warn('Detalles:', data.details || 'Sin detalles');
                }

            } catch (err) {
                console.error("🚨 Error de red al enviar alerta:", err);
                // No mostrar error al usuario para no interrumpir la experiencia
            }
        }

        // === FUNCIONES AUXILIARES ===
        function calcularDiasParaVencer(fechaVencimiento) {
            if (!fechaVencimiento) return 9999;
            const hoy = new Date();
            const fecha = new Date(fechaVencimiento);
            const diff = fecha - hoy;
            return Math.ceil(diff / (1000 * 60 * 60 * 24));
        }

        function formatChemicalFormula(formula) {
            if (!formula) return '';
            return formula.replace(/(\d+)/g, '<sub>$1</sub>');
        }

        function calculateDaysToExpiry(expiryDate) {
            if (!expiryDate) return 0;
            const today = new Date();
            const expiry = new Date(expiryDate);
            const diffTime = expiry - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            return Math.max(0, diffDays);
        }

        function formatExpiryDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit', 
                year: 'numeric'
            });
        }

        function filterReactives(searchTerm) {
            const filtered = reactives.filter(reactive => 
                reactive.nombre_comun.toLowerCase().includes(searchTerm.toLowerCase()) ||
                (reactive.formula_quimica && reactive.formula_quimica.toLowerCase().includes(searchTerm.toLowerCase())) ||
                (reactive.categoria_nombre && reactive.categoria_nombre.toLowerCase().includes(searchTerm.toLowerCase()))
            );
            renderReactives(filtered);
        }

        function openModal(isEdit = false, reactiveData = null) {
            const modal = document.getElementById('reactiveModal');
            const modalTitle = document.getElementById('modalTitle');

                if (isEdit && reactiveData) {
                modalTitle.textContent = 'Editar Reactivo';
                document.getElementById('commonName').value = reactiveData.nombre_comun;
                document.getElementById('chemicalFormula').value = reactiveData.formula_quimica || '';
                document.getElementById('category').value = reactiveData.categoria_id || '';
                document.getElementById('unitMeasure').value = reactiveData.unidad_medida || '';
                document.getElementById('totalQuantity').value = reactiveData.cantidad_total || '';
                document.getElementById('expiryDate').value = reactiveData.fecha_vencimiento || '';
                    document.getElementById('estado').value = reactiveData.estado || 'activo';
                if (reactiveData.imagen) {
                    document.getElementById('imagePreview').src = 'img/reactivos/' + reactiveData.imagen;
                    document.getElementById('imagePreview').style.display = 'block';
                }
                editingId = reactiveData.id;
            } else {
                modalTitle.textContent = 'Nuevo Reactivo';
                document.getElementById('reactiveForm').reset();
                document.getElementById('imagePreview').style.display = 'none';
                    // valor por defecto
                    document.getElementById('estado').value = 'activo';
                editingId = null;
            }

            clearErrors();
            document.body.classList.add('modal-open');
            modal.classList.add('active');
        }

        function closeModal() {
            document.body.classList.remove('modal-open');
            document.getElementById('reactiveModal').classList.remove('active');
            editingId = null;
            clearErrors();
        }

        function clearErrors() {
            document.getElementById('nameError').textContent = '';
            document.getElementById('categoryError').textContent = '';
        }
        //guardar
        async function saveReactive() {
            const saveBtn = document.getElementById('saveBtn');
            clearErrors();

            const commonName = document.getElementById('commonName').value.trim();
            if (!commonName) {
                document.getElementById('nameError').textContent = 'El nombre común es obligatorio';
                return;
            }

            const formData = new FormData();
            formData.append('nombre_comun', commonName);
            formData.append('formula_quimica', document.getElementById('chemicalFormula').value.trim());
            formData.append('categoria_id', document.getElementById('category').value || '');
            formData.append('unidad_medida', document.getElementById('unitMeasure').value.trim());
            formData.append('cantidad_total', document.getElementById('totalQuantity').value || 0);
            formData.append('fecha_vencimiento', document.getElementById('expiryDate').value || '');
            // Estado
            formData.append('estado', document.getElementById('estado').value || 'activo');
            
            // Añadir la imagen si existe
            const imageInput = document.getElementById('reactiveImage');
            if (imageInput.files.length > 0) {
                formData.append('imagen', imageInput.files[0]);
            }

            try {
                let url = REACTIVE_API;
                
                // Si es edición, agregar parámetros
                if (editingId) {
                    url = `${REACTIVE_API}?id=${editingId}`;
                    formData.append('id', editingId);
                    formData.append('_method', 'PUT'); // Simular PUT
                }

                const response = await fetch(url, {
                    method: 'POST', // Siempre POST
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Error al guardar el reactivo');
                }

                const notificationMessage = editingId ? 'Reactivo actualizado exitosamente' : 'Reactivo guardado exitosamente';
                showNotification(notificationMessage);
                closeModal();
                await loadReactives();
                
                // Notificar DESPUÉS de guardar exitosamente
                const userId = document.getElementById('userId')?.value || 1;
                if (editingId) {
                    await notificarEdicionReactivo(editingId, commonName, userId);
                } else {
                    await notificarNuevoReactivo(data.id, commonName, userId);
                }

            } catch (error) {
                console.error('Error al guardar el reactivo:', error);
                showNotification('Error al guardar el reactivo: ' + error.message, 'error');
            }
        }
        
        // Notificar creación de reactivo
       function notificarNuevoReactivo(id, nombreComun, usuarioId) {
            if (!usuarioId) {
                console.error('No se pudo obtener el ID del usuario');
                return;
            }
            
            const url = '/invitrosoft/main/admin/auth/includes/registrar_notificacion.php'
            const datos = {
                usuario_id: usuarioId,
                titulo: 'Nuevo Reactivo',
                mensaje: `Se ha creado el reactivo: ${nombreComun}`,
                tipo: 'success',
                modulo: 'reactivos',
                accion: 'crear',
                entidad: 'reactivo',
                entidad_id: id
            };

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(datos)
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Error en la respuesta:', text);
                        throw new Error(`HTTP error! status: ${response.status}, body: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {      
                if (!data.success) {
                    console.error('Error en la respuesta:', data.error || 'Error desconocido');
                }
            })
            .catch(error => {                
                showNotification('Error al crear notificación: ' + error.message, 'error');
            });
        }

        function editReactive(id) {
            const reactive = reactives.find(r => r.id == id);
            if (reactive) {
                openModal(true, reactive);
            }
        }

        //notificaciones a historial de edicion
        async function notificarEdicionReactivo(id, nombreComun, usuarioId) {
            if (!usuarioId) {
                console.error('Error: No se pudo obtener el ID del usuario');
                return Promise.reject('ID de usuario no proporcionado');
            }

            const url = '/invitrosoft/main/admin/auth/includes/registrar_notificacion.php';
            const datos = {
                usuario_id: parseInt(usuarioId),
                titulo: 'Reactivo Modificado',
                mensaje: `Se ha modificado el reactivo: ${nombreComun}`,
                tipo: 'warning',
                modulo: 'reactivos',
                accion: 'editar',
                entidad: 'reactivo',
                entidad_id: id
            };

            console.log('Enviando notificación de edición a:', url, datos);

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(datos)
                });

                const text = await response.text();
                console.log('Respuesta del servidor (edición):', response.status, text);

                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Error al parsear JSON:', e, text);
                    throw new Error('Respuesta del servidor no es un JSON válido');
                }

                if (!response.ok) {
                    throw new Error(data.error || `Error HTTP ${response.status}`);
                }

                console.log('Notificación de edición exitosa:', data);
                return data;

            } catch (error) {
                console.error('Error en la petición de notificación (edición):', error);
                throw error;
            }
        }

        async function deleteReactive(id) {
            const reactive = reactives.find(r => r.id == id);
            if (!reactive) return;

            if (!confirm(`¿Estás seguro de eliminar "${reactive.nombre_comun}"?`)) return;

            try {
                await notificarEliminacionReactivo(id, reactive.nombre_comun, document.getElementById('userId')?.value || 1);
                const response = await fetch(`${REACTIVE_API}?id=${id}`, { method: 'DELETE' });
                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.error || 'Error al eliminar');
                }
                await loadReactives();
                showNotification('Reactivo eliminado correctamente', 'success');
            } catch (error) {
                showNotification('Error al eliminar: ' + error.message, 'error');
            }
        }

        async function notificarEliminacionReactivo(id, nombreComun, usuarioId) {
            if (!usuarioId) {
                console.error('Error: No se pudo obtener el ID del usuario');
                return Promise.reject('ID de usuario no proporcionado');
            }

            const url = '/invitrosoft/main/admin/auth/includes/registrar_notificacion.php';
            const datos = {
                usuario_id: parseInt(usuarioId),
                titulo: 'Reactivo Eliminado',
                mensaje: `Se ha eliminado el reactivo: ${nombreComun}`,
                tipo: 'danger',
                modulo: 'reactivos',
                accion: 'eliminar',
                entidad: 'reactivo',
                entidad_id: id
            };

            console.log('Enviando notificación de eliminación a:', url, datos);

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(datos)
                });

                const text = await response.text();
                console.log('Respuesta del servidor (eliminación):', response.status, text);

                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Error al parsear JSON:', e, text);
                    throw new Error('Respuesta del servidor no es un JSON válido');
                }

                if (!response.ok) {
                    throw new Error(data.error || `Error HTTP ${response.status}`);
                }

                console.log('Notificación de eliminación exitosa:', data);
                return data;

            } catch (error) {
                console.error('Error en la petición de notificación (eliminación):', error);
                throw error;
            }
        }
        
        function toggleFilter() {
            showNotification('Función de filtro en desarrollo', 'error');
        }
    </script>
    <script src="js/header-footer.js"></script>
    <script>
        // Función para alternar el modo oscuro (debe ser llamada desde el botón del header)
        function toggleDarkMode() {
            const html = document.documentElement;
            const body = document.body;
            const isDark = html.classList.contains('dark-mode');
            
            if (isDark) {
                // Desactivar modo oscuro
                html.classList.remove('dark-mode');
                body.classList.remove('dark-mode');
                localStorage.setItem('darkMode', 'disabled');
                body.style.backgroundColor = '';
                body.style.color = '';
            } else {
                // Activar modo oscuro
                html.classList.add('dark-mode');
                body.classList.add('dark-mode');
                localStorage.setItem('darkMode', 'enabled');
                body.style.backgroundColor = 'var(--bg-primary)';
                body.style.color = 'var(--text-primary)';
            }
            
            // Actualizar el ícono del botón si existe
            const icon = document.getElementById('darkModeIcon');
            if (icon) {
                icon.className = isDark ? 'fas fa-moon' : 'fas fa-sun';
            }
        }
        
        // Escuchar cambios en localStorage desde otras pestañas
        window.addEventListener('storage', function(e) {
            if (e.key === 'darkMode') {
                applyDarkModeOnLoad();
            }
        });
    </script>
</body>
</html>