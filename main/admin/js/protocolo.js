// Función que maneja cualquier botón de menú de usuario
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

// Aplica tanto para escritorio como para móvil
setupUserMenu('mobile-user-btn', 'mobile-dropdown-menu');
setupUserMenu('mobile-user-btn-2', 'mobile-dropdown-menu-2');
setupUserMenu('desktop-user-btn', 'desktop-dropdown-menu');

let editingId = null;
let availableReactivos = [];
let availableFormulaciones = [];

// Cargar reactivos disponibles
fetch('db/reactivos_listar.php')
    .then(res => res.json())
    .then(data => { availableReactivos = data; })
    .catch(error => {
        console.error('Error cargando reactivos:', error);
    });

// Buscar
document.getElementById('searchInput').addEventListener('input', renderCards);

// Renderizar cards desde la base de datos
async function renderCards() {
    const grid = document.getElementById('protocolosGrid');
    grid.innerHTML = '<div class="empty-state">Cargando...</div>';
    
    try {
        const search = document.getElementById('searchInput').value.trim().toLowerCase();
        const res = await fetch('db/protocolos.php?accion=listar');
        
        if (!res.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        
        const data = await res.json();

        let filtered = data;
        if (search) {
            filtered = data.filter(p =>
                p.nombre.toLowerCase().includes(search) ||
                (p.nombre_planta && p.nombre_planta.toLowerCase().includes(search)) ||
                String(p.id).includes(search)
            );
        }

        if (!filtered.length) {
            grid.innerHTML = `
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                    </svg>
                    <h3>No hay protocolos registrados</h3>
                    <p>Comienza agregando tu primer protocolo</p>
                </div>
            `;
            return;
        }

        grid.innerHTML = '';
        filtered.forEach(item => {
            const card = document.createElement('div');
            card.className = 'formulation-card';
            
            const fasesCount = item.fases ? item.fases.length : 0;
            let totalReactivos = 0;
            if (item.fases && Array.isArray(item.fases)) {
                totalReactivos = item.fases.reduce((sum, fase) => {
                    return sum + (fase.reactivos ? fase.reactivos.length : 0);
                }, 0);
            }
            const fechaCreacion = item.fecha_creacion ? new Date(item.fecha_creacion).toLocaleDateString('es-ES') : 'N/A';

            card.innerHTML = `
                <div class="card-header">
                    <div class="card-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                        </svg>
                    </div>
                    <div class="card-info">
                        <div class="formulation-title">${item.nombre}</div>
                        <div class="formulation-subtitle">Protocolo de Micropropagación</div>
                        <div class="card-id">#${String(item.id).padStart(3, '0')}</div>
                    </div>
                </div>
                
                <div class="card-details">
                    <div class="detail-item">
                        <span class="detail-label">Técnica</span>
                        <span class="detail-value">${item.tecnica_utilizada || 'N/A'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Fases</span>
                        <span class="detail-value">${fasesCount}</span>
                    </div>
                </div>
                
                <div class="card-stats">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value">${totalReactivos}</div>
                            <div class="stat-label">Reactivos</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">${fasesCount}</div>
                            <div class="stat-label">Fases</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">${fechaCreacion}</div>
                            <div class="stat-label">Creado</div>
                        </div>
                    </div>
                </div>
                
                <div class="card-actions">
                    <button class="action-btn view-btn" onclick="viewProtocolo(${item.id})" title="Ver detalles">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z"/>
                        </svg>
                    </button>
                    <button class="action-btn edit-btn" onclick="openModal(${item.id})" title="Editar">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18,2.9 17.35,2.9 16.96,3.29L15.12,5.12L18.87,8.87M3,17.25V21H6.75L17.81,9.93L14.06,6.18L3,17.25Z"/>
                        </svg>
                    </button>
                    <button class="action-btn" style="background: #4CAF50; color: white;" onclick="duplicateProtocolo(${item.id})" title="Duplicar">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19,21H8V7H19M19,5H8A2,2 0 0,0 6,7V21A2,2 0 0,0 8,23H19A2,2 0 0,0 21,21V7A2,2 0 0,0 19,5M16,1H4A2,2 0 0,0 2,3V17H4V3H16V1Z"/>
                        </svg>
                    </button>
                    <button class="action-btn delete-btn" onclick="deleteProtocolo(${item.id})" title="Eliminar">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z"/>
                        </svg>
                    </button>
                </div>
            `;
            grid.appendChild(card);
        });
    } catch (error) {
        console.error('Error:', error);
        grid.innerHTML = `
            <div class="empty-state">
                <h3>Error al cargar protocolos</h3>
                <p>Por favor, intenta nuevamente</p>
            </div>
        `;
    }
}

// Modal para crear/editar
function openModal(id = null) {
    editingId = id;
    let nombre = '';
    let tecnica = '';
    let fases = [];
    let modalTitle = 'Nuevo Protocolo';

    if (id) {
        fetch('db/protocolos.php?accion=listar')
            .then(res => res.json())
            .then(data => {
                const found = data.find(p => p.id == id);
                if (found) {
                    nombre = found.nombre;
                    tecnica = found.tecnica_utilizada || '';
                    fases = found.fases || [];
                    modalTitle = 'Editar Protocolo';
                }
                showModal(nombre, tecnica, fases, modalTitle);
            });
    } else {
        showModal('', '', [], modalTitle);
    }
}

async function showModal(nombre = '', tecnica = '', fases = [], modalTitle = 'Nuevo Protocolo', reactivos = []) {
    await cargarFormulaciones(); // Espera a que se carguen las formulaciones

    const modal = document.getElementById('protocoloModal');
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h2>${modalTitle}</h2>
                <button class="close-btn" onclick="closeModal()">×</button>
            </div>
            <div class="modal-body">
                <form id="protocoloForm">
                    <div class="form-group">
                        <label>Nombre del Protocolo</label>
                        <input type="text" id="nombre" value="${nombre}" required>
                    </div>
                    <div class="form-group">
                        <label>Técnica Utilizada</label>
                        <textarea id="tecnica" rows="3" placeholder="Describe la técnica utilizada...">${tecnica}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Reactivos Generales</label>
                        <div id="reactivos-generales-list" class="reactivos-list">
                            ${renderReactivosGenerales(reactivos)}
                        </div>
                        <button type="button" class="add-reactivo-btn" onclick="addReactivoGeneral()">+ Agregar Reactivo</button>
                    </div>
                    <div class="form-group">
                        <label>Fases del Protocolo</label>
                        <div id="fases-container" class="fases-container">
                            ${renderFases(fases)}
                        </div>
                        <button type="button" class="add-fase-btn" onclick="addFase()">+ Agregar Fase</button>
                    </div>
                    <div class="form-group">
                        <label>Formulaciones asociadas *</label>
                        <div id="formulaciones-list" class="formulaciones-list"></div>
                        <div style="display: flex; gap: 10px; margin-top: 8px;">
                            <select id="formulacion-select">
                                <option value="">Seleccionar formulación...</option>
                                <optgroup label="Soluciones madre">
                                    ${availableFormulaciones.filter(f => f.tipo === 'soluciones-madre').map(f => `<option value="${f.id}">${f.nombre}</option>`).join('')}
                                </optgroup>
                                <optgroup label="Medios de cultivo">
                                    ${availableFormulaciones.filter(f => f.tipo === 'medios-cultivo').map(f => `<option value="${f.id}">${f.nombre}</option>`).join('')}
                                </optgroup>
                                <optgroup label="Soluciones desinfectantes">
                                    ${availableFormulaciones.filter(f => f.tipo === 'soluciones-desinfectantes').map(f => `<option value="${f.id}">${f.nombre}</option>`).join('')}
                                </optgroup>
                            </select>
                            <button type="button" class="add-formulacion-btn" onclick="addFormulacion()">+ Agregar Formulación</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancelar</button>
                <button type="button" class="btn-save" onclick="saveProtocolo()">Guardar</button>
            </div>
        </div>
    `;
    modal.classList.add('active');
    cargarFormulaciones(); // Cargar formulaciones al abrir el modal
}

// Funciones para manejar fases y reactivos
function renderFases(fases) {
    if (!fases || !fases.length) fases = [getFaseVacia()];
    return fases.map((f, i) => renderFase(f, i)).join('');
}

function getFaseVacia() {
    return {
        numero_fase: 1,
        nombre_fase: '',
        descripcion: '',
        imagen_url: '',
        reactivos: [getReactivoVacio()]
    };
}

function getReactivoVacio() {
    return { reactivo_id: '', cantidad: '' };
}

function renderFase(fase, idx) {
    return `
        <div class="fase-item" data-idx="${idx}" ${fase.id ? `data-fase-id="${fase.id}"` : ''}>
            <div class="fase-header">
                <span class="fase-title">Fase ${fase.numero_fase || idx + 1}</span>
                <button type="button" class="remove-fase-btn" onclick="removeFase(this)" title="Eliminar fase">×</button>
            </div>
            <div class="fase-fields">
                <div class="form-group">
                    <label>Número de Fase</label>
                    <input type="number" class="numero-fase" value="${fase.numero_fase || idx + 1}" min="1">
                </div>
                <div class="form-group">
                    <label>Nombre de la Fase *</label>
                    <input type="text" class="nombre-fase" value="${fase.nombre_fase || ''}" required>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea class="descripcion-fase" rows="2">${fase.descripcion || ''}</textarea>
                </div>
                <div class="form-group">
                    <label>Imagen de la Fase</label>
                    <input type="file" class="fase-imagen-input" accept="image/*" onchange="previewFaseImagen(this)">
                    <div class="fase-img-preview">
                        ${fase.imagen_url ? `<img src="img/protocolo/${fase.imagen_url}" style="max-width:100px;max-height:100px;border-radius:8px;">` : ''}
                    </div>
                    <button type="button" class="remove-img-btn" style="display:${fase.imagen_url ? 'inline-block' : 'none'}" onclick="removeFaseImagen(this)">Eliminar imagen</button>
                </div>
                <div class="form-group">
                    <label>Reactivos Utilizados</label>
                    <div class="reactivos-list">
                        ${renderReactivosFase(fase.reactivos, idx)}
                        <button type="button" class="add-reactivo-btn" onclick="addReactivo(this)">+ Agregar Reactivo</button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderReactivosFase(reactivos, faseIdx) {
    if (!reactivos || !reactivos.length) reactivos = [getReactivoVacio()];
    return reactivos.map((r, i) => `
        <div class="reactivo-item" data-idx="${i}">
            <select class="reactivo-select">
                <option value="">Seleccionar reactivo...</option>
                ${availableReactivos.map(ar => `<option value="${ar.id}" ${ar.id == r.reactivo_id ? 'selected' : ''}>${ar.nombre_comun}</option>`).join('')}
            </select>
            <input type="text" class="reactivo-cantidad" placeholder="Cantidad" value="${r.cantidad || ''}">
            <button type="button" class="remove-reactivo-btn" onclick="removeReactivo(this)" title="Eliminar reactivo">×</button>
        </div>
    `).join('');
}

function renderReactivosGenerales(reactivos) {
    if (!reactivos || !reactivos.length) reactivos = [getReactivoVacio()];
    return reactivos.map((r, i) => `
        <div class="reactivo-item" data-idx="${i}">
            <select class="reactivo-select">
                <option value="">Seleccionar reactivo...</option>
                ${availableReactivos.map(ar => `<option value="${ar.id}" ${ar.id == r.reactivo_id ? 'selected' : ''}>${ar.nombre_comun}</option>`).join('')}
            </select>
            <input type="text" class="reactivo-cantidad" placeholder="Cantidad" value="${r.cantidad || ''}">
            <button type="button" class="remove-reactivo-btn" onclick="removeReactivoGeneral(this)" title="Eliminar reactivo">×</button>
        </div>
    `).join('');
}

// Funciones globales para el modal
window.addFase = function() {
    const container = document.getElementById('fases-container');
    const fases = container.querySelectorAll('.fase-item');
    const idx = fases.length;
    const div = document.createElement('div');
    div.innerHTML = renderFase(getFaseVacia(), idx);
    container.appendChild(div.firstElementChild);
}

window.removeFase = function(btn) {
    const container = document.getElementById('fases-container');
    const fases = container.querySelectorAll('.fase-item');
    if (fases.length > 1) {
        btn.closest('.fase-item').remove();
    } else {
        mostrarNotificacion('Debe haber al menos una fase', false);
    }
}

window.addReactivo = function(btn) {
    const faseDiv = btn.closest('.fase-item');
    const reactivosDiv = faseDiv.querySelector('.reactivos-list');
    const div = document.createElement('div');
    div.innerHTML = renderReactivosFase([getReactivoVacio()], 0);
    reactivosDiv.insertBefore(div.firstElementChild, btn);
}

window.removeReactivo = function(btn) {
    btn.closest('.reactivo-item').remove();
}

window.addReactivoGeneral = function() {
    const reactivosDiv = document.getElementById('reactivos-generales-list');
    const div = document.createElement('div');
    div.innerHTML = renderReactivosGenerales([getReactivoVacio()]);
    reactivosDiv.appendChild(div.firstElementChild);
}

window.removeReactivoGeneral = function(btn) {
    btn.closest('.reactivo-item').remove();
}

// Nueva función para duplicar
window.duplicateProtocolo = function(id) {
    fetch('db/protocolos.php?accion=listar')
        .then(res => res.json())
        .then(data => {
            const found = data.find(p => p.id == id);
            if (found) {
                editingId = null; // Para crear uno nuevo
                showModal(
                    found.nombre + ' (Copia)',
                    found.tecnica_utilizada || '',
                    found.fases || [],
                    'Duplicar Protocolo'
                );
            }
        });
}

window.previewFaseImagen = function(input) {
    const file = input.files[0];
    const preview = input.parentElement.querySelector('.fase-img-preview');
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" style="max-width:100px;max-height:100px;border-radius:8px;">`;
        }
        reader.readAsDataURL(file);
        input.parentElement.querySelector('.remove-img-btn').style.display = 'inline-block';
    }
}

window.removeFaseImagen = function(btn) {
    const faseItem = btn.closest('.fase-item');
    const input = faseItem.querySelector('.fase-imagen-input');
    input.value = '';
    faseItem.querySelector('.fase-img-preview').innerHTML = '';
    btn.style.display = 'none';
    // Marca para eliminar la imagen si es edición
    faseItem.dataset.eliminarImagen = "1";
}

async function saveProtocolo() {
    const nombre = document.getElementById('nombre').value.trim();
    const tecnica = document.getElementById('tecnica').value.trim();
    const fases = [];
    const reactivos_generales = [];
    const formData = new FormData();

    // Recoger reactivos generales
    document.querySelectorAll('#reactivos-generales-list .reactivo-item').forEach(reactivoDiv => {
        const reactivo_id = reactivoDiv.querySelector('.reactivo-select').value;
        const cantidad = reactivoDiv.querySelector('.reactivo-cantidad').value.trim();
        if (reactivo_id && cantidad) {
            reactivos_generales.push({ reactivo_id, cantidad });
        }
    });

    // Recoger fases y archivos de imagen
    document.querySelectorAll('.fase-item').forEach((faseDiv, idx) => {
        const faseExistenteId = faseDiv.getAttribute('data-fase-id') || null;
        const numero_fase = parseInt(faseDiv.querySelector('.numero-fase').value);
        const nombre_fase = faseDiv.querySelector('.nombre-fase').value.trim();
        const descripcion = faseDiv.querySelector('.descripcion-fase').value.trim();
        const reactivos = [];
        faseDiv.querySelectorAll('.reactivo-item').forEach(reactivoDiv => {
            const reactivo_id = reactivoDiv.querySelector('.reactivo-select').value;
            const cantidad = reactivoDiv.querySelector('.reactivo-cantidad').value.trim();
            if (reactivo_id && cantidad) {
                reactivos.push({ reactivo_id, cantidad });
            }
        });

        // Imagen de la fase
        const imgInput = faseDiv.querySelector('.fase-imagen-input');
        if (imgInput && imgInput.files[0]) {
            formData.append(`fases[${idx}][imagen]`, imgInput.files[0]);
        }
        // Si se marcó para eliminar la imagen
        if (faseDiv.dataset.eliminarImagen === "1") {
            formData.append(`fases[${idx}][eliminar_imagen]`, "1");
        }

        const fase = {
            id: faseExistenteId,
            numero_fase,
            nombre_fase,
            descripcion,
            reactivos,
        };
        if (faseDiv.dataset.eliminarImagen === "1") {
            fase.eliminar_imagen = true;
        }

        fases.push(fase);
    });

    // Validación
    if (!nombre || !fases.length) {
        mostrarNotificacion('Por favor complete todos los campos obligatorios', false);
        return;
    }

    formData.append('nombre', nombre);
    formData.append('tecnica_utilizada', tecnica);
    formData.append('fases', JSON.stringify(fases));
    formData.append('reactivos_generales', JSON.stringify(reactivos_generales));
    
    const formulacionesSeleccionadas = Array.from(document.querySelectorAll('#formulaciones-list .formulacion-item')).map(div => div.dataset.id);
    if (!formulacionesSeleccionadas.length) {
        mostrarNotificacion('Debe seleccionar al menos una formulación', false);
        return;
    }
    formData.append('formulaciones', JSON.stringify(formulacionesSeleccionadas));
    
    if (editingId) formData.append('id', editingId);

    let url = 'db/protocolos.php?accion=' + (editingId ? 'editar' : 'crear');

    const res = await fetch(url, {
        method: 'POST',
        body: formData
    });
    const result = await res.json();
    mostrarNotificacion(result.msg, result.success);
    if (result.success) {
        closeModal();
        renderCards();
    }
}

async function deleteProtocolo(id) {
    if (!confirm('¿Está seguro de que desea eliminar este protocolo?')) return;
    
    const res = await fetch('db/protocolos.php?accion=eliminar', {
        method: 'POST',
        body: new URLSearchParams({id})
    });
    const result = await res.json();
    mostrarNotificacion(result.msg, result.success);
    if(result.success) renderCards();
}

function viewProtocolo(id) {
    fetch('db/protocolos.php?accion=listar')
        .then(res => res.json())
        .then(data => {
            const found = data.find(p => p.id == id);
            if (found) {
                showViewModal(found);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarNotificacion('Error al cargar el protocolo', false);
        });
}

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

// Inicializar
renderCards();

window.closeModal = function() {
    document.getElementById('protocoloModal').classList.remove('active');
    document.getElementById('protocoloModal').innerHTML = '';
    editingId = null;
}


function showViewModal(protocolo) {
    const modal = document.createElement('div');
    modal.id = 'viewProtocolModal';
    modal.className = 'view-protocol-modal';

    let fasesHTML = '';
    if (protocolo.fases && protocolo.fases.length > 0) {
        protocolo.fases.forEach((fase, idx) => {
            let pieImagen = fase.pie_imagen || `Imagen ${idx + 1}. Laboratorio de Biotecnología Vegetal SENA.`;
            fasesHTML += `
                <div class="fase-card-vista">
                    <div class="fase-card-row">
                        <div class="fase-num-circle">${fase.numero_fase}</div>
                        <div class="fase-nombre-box">
                            <div class="fase-nombre">${fase.nombre_fase}</div>
                        </div>
                        <div class="fase-card-imgbox">
                            ${fase.imagen_url
                                ? `<img src="img/protocolo/${fase.imagen_url}" alt="${fase.nombre_fase}">`
                                : `<svg viewBox="0 0 24 24" fill="#bdbdbd" width="80" height="80">
                                    <path d="M21,17H7V3H21M21,1H7A2,2 0 0,0 5,3V17A2,2 0 0,0 7,19H21A2,2 0 0,0 23,17V3A2,2 0 0,0 21,1M3,5H1V21A2,2 0 0,0 3,23H19V21H3M15.96,10.29L13.21,13.83L11.25,11.47L8.5,15H19.5L15.96,10.29Z"/>
                                </svg>`
                            }
                        </div>
                    </div>
                    <div class="fase-pie-img">${pieImagen}</div>
                    <div class="fase-card-desc">
                        ${fase.descripcion ? `<div>${fase.descripcion}</div>` : ''}
                    </div>
                </div>
            `;
        });
    }

    let formulacionesHTML = '';
    if (protocolo.formulaciones && protocolo.formulaciones.length > 0) {
        formulacionesHTML = `
            <div class="protocolo-formulaciones-vista">
                <div class="formulaciones-label">Formulaciones asociadas:</div>
                <div class="formulaciones-chips">
                    ${protocolo.formulaciones.map(f =>
                        `<span class="formulacion-chip">
                            <span class="formulacion-chip-nombre" title="Doble clic para ver" ondblclick="viewFormulacion(${f.id})">${f.nombre}</span>
                            <button type="button" class="chip-ver-btn" onclick="viewFormulacion(${f.id})" title="Ver formulación">
                                <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M12 5c-7 0-9 7-9 7s2 7 9 7 9-7 9-7-2-7-9-7zm0 12c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8a3 3 0 100 6 3 3 0 000-6z" fill="#007832"/></svg>
                            </button>
                        </span>`
                    ).join('')}
                </div>
            </div>
        `;
    }

    modal.innerHTML = `
        <div class="view-modal-overlay" onclick="closeViewProtocolModal()"></div>
        <div class="view-modal-content">
            <div class="view-modal-header">
                <h2>${protocolo.nombre}</h2>
                <p class="view-protocol-id">#${String(protocolo.id).padStart(3, '0')}</p>
                ${protocolo.tecnica_utilizada ? `<p class="view-protocol-technique">${protocolo.tecnica_utilizada}</p>` : ''}
                <button class="view-modal-close" onclick="closeViewProtocolModal()">×</button>
            </div>
            <div class="view-modal-body">
                <div class="protocol-steps-container">
                    ${fasesHTML}
                </div>
                ${formulacionesHTML}
            </div>
            <div class="view-modal-footer">
                <button class="btn-secondary" onclick="closeViewProtocolModal()">Cerrar</button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    setTimeout(() => modal.classList.add('active'), 10);
}

function closeViewProtocolModal() {
    const modal = document.getElementById('viewProtocolModal');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => modal.remove(), 300);
    }
}


async function cargarFormulaciones() {
    const res = await fetch('db/formulaciones.php?accion=listar');
    availableFormulaciones = await res.json();
}

function renderFormulacionesSeleccionadas(formulacionesIds = []) {
    const list = document.getElementById('formulaciones-list');
    if (!list) return;
    list.innerHTML = '';
    formulacionesIds.forEach(fid => {
        const f = availableFormulaciones.find(ff => ff.id == fid);
        if (f) {
            const div = document.createElement('div');
            div.className = 'formulacion-item';
            div.dataset.id = f.id;
            div.innerHTML = `
                <span ondblclick="viewFormulacion(${f.id})" title="Doble clic para ver">${f.nombre}</span>
                <button type="button" class="remove-formulacion-btn" onclick="removeFormulacion(${f.id})">×</button>
            `;
            list.appendChild(div);
        }
    });
}

window.addFormulacion = function() {
    const select = document.getElementById('formulacion-select');
    const fid = select.value;
    if (!fid) return;
    const list = document.getElementById('formulaciones-list');
    if ([...list.children].some(div => div.dataset.id == fid)) return; // No duplicados
    renderFormulacionesSeleccionadas([
        ...[...list.children].map(div => div.dataset.id),
        fid
    ]);
    select.value = '';
};

window.removeFormulacion = function(fid) {
    const list = document.getElementById('formulaciones-list');
    const item = list.querySelector(`.formulacion-item[data-id="${fid}"]`);
    if (item) item.remove();
};

// Al cargar el modal de edición, renderizar las formulaciones asociadas
document.getElementById('protocoloModal').addEventListener('DOMNodeInserted', function(e) {
    if (e.target.id === 'protocoloModal' && editingId) {
        fetch('db/protocolos.php?accion=listar')
            .then(res => res.json())
            .then(data => {
                const found = data.find(p => p.id == editingId);
                if (found && found.formulaciones) {
                    renderFormulacionesSeleccionadas(found.formulaciones.map(f => f.id));
                }
            });
    }
});

window.viewFormulacion = function(id) {
    fetch('db/formulaciones.php?accion=listar')
        .then(res => res.json())
        .then(data => {
            const found = data.find(f => f.id == id);
            if (found) {
                showFormulacionModal(found);
            }
        })
        .catch(error => {
            alert('Error al cargar la formulación');
        });
};

function showFormulacionModal(formulacion) {
    const modal = document.createElement('div');
    modal.id = 'viewFormulacionModal';
    modal.className = 'view-protocol-modal';

    let gruposHTML = '';
    if (formulacion.grupos && formulacion.grupos.length > 0) {
        formulacion.grupos.forEach((grupo, idx) => {
            let reactivosHTML = '';
            if (grupo.reactivos && grupo.reactivos.length > 0) {
                reactivosHTML = grupo.reactivos.map(r => `
                    <tr>
                        <td>${r.nombre_comun || ''}</td>
                        <td>${r.cantidad || ''}</td>
                        <td>${grupo.solucion_madre || formulacion.solucion_madre || ''}</td>
                    </tr>
                `).join('');
            }
            gruposHTML += `
                <tr class="grupo-row">
                    <td colspan="3" class="grupo-nombre">${grupo.nombre_grupo}</td>
                </tr>
                ${reactivosHTML}
            `;
        });
    }

    modal.innerHTML = `
        <div class="view-modal-overlay" onclick="closeViewFormulacionModal()"></div>
        <div class="view-modal-content" style="max-width: 700px;">
            <div class="view-modal-header">
                <h2>${formulacion.nombre}</h2>
                <p class="view-protocol-id">#${String(formulacion.id).padStart(3, '0')}</p>
                <button class="view-modal-close" onclick="closeViewFormulacionModal()">×</button>
            </div>
            <div class="view-modal-body">
                <table class="tabla-formulacion">
                    <thead>
                        <tr>
                            <th>REACTIVOS</th>
                            <th>CANTIDAD</th>
                            <th>S.MADRE</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${gruposHTML}
                    </tbody>
                </table>
            </div>
            <div class="view-modal-footer">
                <button class="btn-secondary" onclick="closeViewFormulacionModal()">Cerrar</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    setTimeout(() => modal.classList.add('active'), 10);
}

function closeViewFormulacionModal() {
    const modal = document.getElementById('viewFormulacionModal');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => modal.remove(), 300);
    }
}