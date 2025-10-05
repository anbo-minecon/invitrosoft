document.addEventListener("DOMContentLoaded", () => {
    let currentCategory = "genero";
    let tipos = [];
    let parametros = [];
    let editingId = null;

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

        if (!filtered.length) {
            grid.innerHTML = `
                <div class="empty-state">
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
                    <button class="action-btn edit-btn" onclick="openModal(${item.id_parametro})" title="Editar">Editar</button>
                    <button class="action-btn delete-btn" onclick="deleteParameter(${item.id_parametro})" title="Eliminar">Eliminar</button>
                </div>
            `;
            grid.appendChild(card);
        });
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