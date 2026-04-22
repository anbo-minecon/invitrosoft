// plantas.js - CRUD completo con cambio de fase y gestión de reactivos
const API_URL = '../backend/plantas.php';
let plantas = [];
let parametros = {};
let protocolos = [];
let reactivos = [];
let reactivosContador = 0;

window.editarPlanta = editarPlanta;
window.eliminarPlanta = eliminarPlanta;
window.cambiarFasePlanta = cambiarFasePlanta;
window.agregarReactivo = agregarReactivo;
window.eliminarReactivoFila = eliminarReactivoFila;

document.addEventListener('DOMContentLoaded', async function() {
    await cargarDatosIniciales();
    await cargarPlantas();

    document.getElementById('btnNuevaPlanta').addEventListener('click', abrirModalNueva);
    document.getElementById('searchPlanta').addEventListener('input', filtrarPlantas);
    document.getElementById('filterFase').addEventListener('change', filtrarPlantas);
    document.getElementById('filterEstado').addEventListener('change', filtrarPlantas);
    document.getElementById('modalPlantaClose').addEventListener('click', cerrarModal);
    document.getElementById('formPlanta').addEventListener('submit', guardarPlanta);
    document.getElementById('btnCancelar').addEventListener('click', cerrarModal);
    document.getElementById('modalPlantaOverlay').addEventListener('click', (e) => {
        if (e.target === e.currentTarget) cerrarModal();
    });
    
    document.getElementById('modalFaseOverlay')?.addEventListener('click', (e) => {
        if (e.target === e.currentTarget) cerrarModalFase();
    });
    document.getElementById('btnCancelarFase')?.addEventListener('click', cerrarModalFase);
    document.getElementById('modalFaseClose')?.addEventListener('click', cerrarModalFase);
    
    document.getElementById('formFase')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'guardarFase');
        
        try {
            const response = await fetch(API_URL, { method: 'POST', body: formData });
            const text = await response.text();
            const data = JSON.parse(text);
            if (data.success) {
                mostrarNotificacion(data.message || 'Fase actualizada', 'success');
                cerrarModalFase();
                await cargarPlantas();
            } else {
                mostrarNotificacion(data.message || 'Error', 'error');
            }
        } catch (error) {
            mostrarNotificacion('Error al actualizar fase', 'error');
        }
    });
});

async function cargarDatosIniciales() {
    try {
        const response = await fetch(`${API_URL}?action=getParametros`);
        const data = await response.json();
        if (data.success) {
            parametros = data.parametros;
            protocolos = data.protocolos;
            llenarSelects();
        }
        
        // Cargar reactivos
        const responseReactivos = await fetch(`${API_URL}?action=getReactivos`);
        const dataReactivos = await responseReactivos.json();
        if (dataReactivos.success) {
            reactivos = dataReactivos.reactivos;
        }
    } catch (error) {
        mostrarNotificacion('Error al cargar datos iniciales', 'error');
    }
}

function llenarSelects() {
    const selectOrigen = document.getElementById('origen_id');
    selectOrigen.innerHTML = '<option value="">Seleccionar origen</option>';
    if (parametros.origen) {
        parametros.origen.forEach(p => {
            selectOrigen.innerHTML += `<option value="${p.id_parametro}">${p.nombre}</option>`;
        });
    }

    const selectProtocolo = document.getElementById('protocolo_id');
    selectProtocolo.innerHTML = '<option value="">Seleccionar protocolo</option>';
    protocolos.forEach(p => {
        selectProtocolo.innerHTML += `<option value="${p.id}">${p.nombre}</option>`;
    });

    const selectEstado = document.getElementById('estado_id');
    selectEstado.innerHTML = '<option value="">Seleccionar estado</option>';
    if (parametros.estado) {
        parametros.estado.forEach(p => {
            selectEstado.innerHTML += `<option value="${p.id_parametro}">${p.nombre}</option>`;
        });
    }

    document.getElementById('filterFase').innerHTML = `
        <option value="">Todas las fases</option>
        <option value="seleccion">Selección</option>
        <option value="establecimiento">Establecimiento</option>
        <option value="multiplicacion">Multiplicación</option>
        <option value="enraizamiento">Enraizamiento</option>
        <option value="adaptacion">Adaptación</option>
    `;

    const filterEstado = document.getElementById('filterEstado');
    filterEstado.innerHTML = '<option value="">Todos los estados</option>';
    if (parametros.estado) {
        parametros.estado.forEach(p => {
            filterEstado.innerHTML += `<option value="${p.id_parametro}">${p.nombre}</option>`;
        });
    }
}

async function cargarPlantas() {
    const contenedor = document.querySelector('.plantas-list');
    contenedor.innerHTML = '<div class="empty-state"><p>Cargando plantas...</p></div>';
    
    try {
        const res = await fetch(`${API_URL}?action=getAll`);
        const data = await res.json();
        
        if (!data.success || !data.plantas.length) {
            contenedor.innerHTML = '<div class="empty-state"><p>No hay plantas registradas.</p></div>';
            return;
        }
        
        plantas = data.plantas;
        renderizarPlantas(plantas);
    } catch (err) {
        contenedor.innerHTML = '<div class="empty-state"><p>Error al cargar plantas.</p></div>';
        mostrarNotificacion('Error al cargar plantas', 'error');
    }
}

function renderizarPlantas(plantasArray) {
    const contenedor = document.querySelector('.plantas-list');
    
    if (plantasArray.length === 0) {
        contenedor.innerHTML = '<div class="empty-state"><p>No se encontraron plantas.</p></div>';
        return;
    }

    contenedor.innerHTML = '';
    plantasArray.forEach(planta => {
        const deshabilitarFase = planta.fase_actual === 'adaptacion';
        const card = `
            <div class="planta-card">
                <div class="planta-header">
                    <div class="planta-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white">
                            <path d="m10.622,20.995c-1.652,0-3.284-.516-4.633-1.57l4.425-4.425h8.867c-.814,1.24-1.726,2.389-2.732,3.439-1.617,1.688-3.787,2.556-5.927,2.556Zm1.792-7.995h8.055c.512-.957.973-1.958,1.38-3h-6.435l-3,3ZM22.811.083c-2.731.299-11.044,1.209-17.545,7.729-1.539,1.543-2.362,3.68-2.257,5.861.077,1.602.625,3.093,1.573,4.331l10.004-10.004h7.964c.666-2.136,1.125-4.419,1.365-6.823l.123-1.228-1.227.134ZM1.483,23.932l4.506-4.507c-.053-.041-.105-.083-.157-.126-.472-.39-.89-.824-1.25-1.295L.069,22.518l1.414,1.414Z"/>
                        </svg>
                    </div>
                    <div class="planta-title-section">
                        <div class="planta-nombre">${planta.nombre_comun}</div>
                        <div class="planta-cientifico">${planta.nombre_cientifico || ''}</div>
                        <div class="planta-codigo">Código: ${planta.codigo}</div>
                    </div>
                    <span class="fase-badge ${planta.fase_actual}">
                        ${planta.fase_actual.charAt(0).toUpperCase() + planta.fase_actual.slice(1)}
                    </span>
                </div>
                <div class="planta-body">
                    <div class="planta-info-grid">
                        <div class="info-item">
                            <span class="info-label">Especie</span>
                            <span class="info-value">${planta.especie || '-'}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Método de propagación</span>
                            <span class="info-value">${planta.metodo_propagacion || '-'}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Origen</span>
                            <span class="info-value">${planta.origen_nombre || '-'}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Protocolo</span>
                            <span class="info-value">${planta.protocolo_nombre || '-'}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Fecha inicio</span>
                            <span class="info-value">${planta.fecha_inicio || '-'}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Fecha fin</span>
                            <span class="info-value">${planta.fecha_fin || '-'}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Estado</span>
                            <span class="estado-badge">${planta.estado_nombre || '-'}</span>
                        </div>
                    </div>
                    ${planta.observaciones ? `<div class="planta-observaciones">${planta.observaciones}</div>` : ''}
                </div>
                <div class="planta-footer">
                    <span class="info-label">Por: ${planta.usuario_nombre || '-'}</span>
                    <div class="action-buttons">
                        <button class="btn-icon fase" onclick="cambiarFasePlanta(${planta.id})" ${deshabilitarFase ? 'disabled' : ''}>
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 12l5 5 9-9M12 5v14"/>
                            </svg>
                        </button>
                        <button class="btn-icon edit" onclick="editarPlanta(${planta.id})">
                            <svg width="20" height="20" fill="currentColor"><path d="M4 13.5V16h2.5l7.06-7.06-2.5-2.5L4 13.5zM17.71 7.04a1 1 0 000-1.41l-2.34-2.34a1 1 0 00-1.41 0l-1.13 1.13 3.75 3.75 1.13-1.13z"/></svg>
                        </button>
                        <button class="btn-icon delete" onclick="eliminarPlanta(${planta.id})">
                            <svg width="20" height="20" fill="none"><path d="M6 7v9a2 2 0 002 2h4a2 2 0 002-2V7M9 7V5a1 1 0 011-1h2a1 1 0 011 1v2m-7 0h10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
        contenedor.innerHTML += card;
    });
}

function filtrarPlantas() {
    const searchTerm = document.getElementById('searchPlanta').value.toLowerCase();
    const faseFilter = document.getElementById('filterFase').value;
    const estadoFilter = document.getElementById('filterEstado').value;

    const plantasFiltradas = plantas.filter(planta => {
        const matchSearch = 
            planta.nombre_comun.toLowerCase().includes(searchTerm) ||
            (planta.nombre_cientifico && planta.nombre_cientifico.toLowerCase().includes(searchTerm)) ||
            planta.codigo.toLowerCase().includes(searchTerm);
        const matchFase = !faseFilter || planta.fase_actual === faseFilter;
        const matchEstado = !estadoFilter || planta.estado_id == estadoFilter;
        return matchSearch && matchFase && matchEstado;
    });

    renderizarPlantas(plantasFiltradas);
}

function abrirModalNueva() {
    document.getElementById('modalPlantaTitle').textContent = 'Nueva Planta';
    document.getElementById('formPlanta').reset();
    document.getElementById('planta_id').value = '';
    document.getElementById('modalPlantaOverlay').classList.add('active');
}

async function editarPlanta(id) {
    try {
        const response = await fetch(`${API_URL}?action=getOne&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const planta = data.planta;
            document.getElementById('modalPlantaTitle').textContent = 'Editar Planta';
            document.getElementById('planta_id').value = planta.id;
            document.getElementById('codigo').value = planta.codigo;
            document.getElementById('nombre_comun').value = planta.nombre_comun;
            document.getElementById('nombre_cientifico').value = planta.nombre_cientifico || '';
            document.getElementById('especie').value = planta.especie || '';
            document.getElementById('origen_id').value = planta.origen_id || '';
            document.getElementById('protocolo_id').value = planta.protocolo_id || '';
            document.getElementById('metodo_propagacion').value = planta.metodo_propagacion || '';
            document.getElementById('fecha_inicio').value = planta.fecha_inicio || '';
            document.getElementById('fecha_fin').value = planta.fecha_fin || '';
            document.getElementById('estado_id').value = planta.estado_id || '';
            document.getElementById('observaciones').value = planta.observaciones || '';
            document.getElementById('modalPlantaOverlay').classList.add('active');
        } else {
            mostrarNotificacion('Error al cargar la planta', 'error');
        }
    } catch (error) {
        mostrarNotificacion('Error al cargar la planta', 'error');
    }
}

async function eliminarPlanta(id) {
    if (!confirm('¿Está seguro de eliminar esta planta?')) return;

    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        const response = await fetch(API_URL, { method: 'POST', body: formData });
        const data = await response.json();
        
        if (data.success) {
            mostrarNotificacion('Planta eliminada', 'success');
            await cargarPlantas();
        } else {
            mostrarNotificacion(data.message || 'Error', 'error');
        }
    } catch (error) {
        mostrarNotificacion('Error al eliminar', 'error');
    }
}

async function cambiarFasePlanta(id) {
    try {
        const formData = new FormData();
        formData.append('action', 'cambiarFase');
        formData.append('planta_id', id);

        const response = await fetch(API_URL, { method: 'POST', body: formData });
        const data = await response.json();
        
        if (data.success) {
            abrirModalFase(data.planta_id, data.fase_actual, data.siguiente_fase);
        } else {
            mostrarNotificacion(data.message || 'Error', 'error');
        }
    } catch (error) {
        mostrarNotificacion('Error al cambiar fase', 'error');
    }
}

function abrirModalFase(plantaId, faseActual, siguienteFase) {
    document.getElementById('modalFaseTitle').textContent = `Cambiar a: ${siguienteFase.charAt(0).toUpperCase() + siguienteFase.slice(1)}`;
    document.getElementById('planta_id_fase').value = plantaId;
    document.getElementById('fase').value = siguienteFase;
    document.getElementById('camposFase').innerHTML = generarCamposFase(siguienteFase);
    reactivosContador = 0;
    document.getElementById('modalFaseOverlay').classList.add('active');
}

function generarCamposFase(fase) {
    let campos = `
        <div class="form-row">
            <div class="form-group">
                <label for="fecha_inicio_fase">Fecha de inicio *</label>
                <input type="date" id="fecha_inicio_fase" name="fecha_inicio" required>
            </div>
            <div class="form-group">
                <label for="fecha_finalizacion">Fecha de finalización</label>
                <input type="date" id="fecha_finalizacion" name="fecha_finalizacion">
            </div>
        </div>
    `;

    if (fase === 'establecimiento') {
        campos += `<div class="form-group">
            <label>Método de propagación</label>
            <input type="text" name="metodo_propagacion" placeholder="Ej: Cultivo in vitro">
        </div>`;
    } else if (fase === 'multiplicacion') {
        campos += `<div class="form-row">
            <div class="form-group">
                <label>Número de explantes</label>
                <input type="number" name="num_explantes_generados" min="0">
            </div>
            <div class="form-group">
                <label>Tiempo madurez (meses)</label>
                <input type="number" name="tiempo_estimacion_madurez" min="1">
            </div>
        </div>`;
    } else if (fase === 'enraizamiento') {
        campos += `<div class="form-row">
            <div class="form-group">
                <label>Medio utilizado</label>
                <select name="medio_utilizado_id">
                    <option value="">Seleccionar</option>
                    ${parametros.origen ? parametros.origen.map(p => `<option value="${p.id_parametro}">${p.nombre}</option>`).join('') : ''}
                </select>
            </div>
            <div class="form-group">
                <label>Estado de raíces</label>
                <select name="estado_raices_id">
                    <option value="">Seleccionar</option>
                    ${parametros.estadoRaices ? parametros.estadoRaices.map(p => `<option value="${p.id_parametro}">${p.nombre}</option>`).join('') : ''}
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>Estado general</label>
            <select name="estado_id">
                <option value="">Seleccionar</option>
                ${parametros.estado ? parametros.estado.map(p => `<option value="${p.id_parametro}">${p.nombre}</option>`).join('') : ''}
            </select>
        </div>`;
    } else if (fase === 'adaptacion') {
        campos += `<div class="form-group">
            <label>Condiciones de adaptación</label>
            <textarea name="condiciones_adaptacion" rows="3"></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Medio de cultivo</label>
                <select name="medio_cultivo_id">
                    <option value="">Seleccionar</option>
                    ${parametros.origen ? parametros.origen.map(p => `<option value="${p.id_parametro}">${p.nombre}</option>`).join('') : ''}
                </select>
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select name="estado_id">
                    <option value="">Seleccionar</option>
                    ${parametros.estado ? parametros.estado.map(p => `<option value="${p.id_parametro}">${p.nombre}</option>`).join('') : ''}
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>Resultado de adaptación</label>
            <input type="text" name="resultado_adaptacion" placeholder="Ej: Exitosa">
        </div>`;
    }

    // Sección de reactivos (común para todas las fases)
    campos += `
        <div class="form-group" style="margin-top: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <label style="margin: 0;">Reactivos utilizados</label>
                <button type="button" onclick="agregarReactivo()" 
                        style="padding: 6px 12px; background: #007832; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px;">
                    + Agregar Reactivo
                </button>
            </div>
            <div id="reactivosContainer" style="display: flex; flex-direction: column; gap: 10px;">
                <!-- Los reactivos se agregarán aquí dinámicamente -->
            </div>
        </div>
    `;

    campos += `<div class="form-group">
        <label>Observaciones</label>
        <textarea name="observaciones" rows="4"></textarea>
    </div>`;

    return campos;
}

function agregarReactivo() {
    const container = document.getElementById('reactivosContainer');
    const index = reactivosContador++;
    
    const opcionesReactivos = reactivos.map(r => 
        `<option value="${r.id}">${r.nombre_comun} (${r.unidad_medida})</option>`
    ).join('');
    
    const filaReactivo = document.createElement('div');
    filaReactivo.id = `reactivo-fila-${index}`;
    filaReactivo.style.cssText = 'display: flex; gap: 10px; align-items: center; background: #f9fafb; padding: 10px; border-radius: 8px;';
    filaReactivo.innerHTML = `
        <select name="reactivo_${index}" style="flex: 2; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
            <option value="">Seleccionar reactivo</option>
            ${opcionesReactivos}
        </select>
        <input type="text" name="cantidad_${index}" placeholder="Cantidad" 
               style="flex: 1; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
        <button type="button" onclick="eliminarReactivoFila(${index})" 
                style="padding: 8px 12px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 7v9a2 2 0 002 2h4a2 2 0 002-2V7M9 7V5a1 1 0 011-1h2a1 1 0 011 1v2m-7 0h10" stroke-linecap="round"/>
            </svg>
        </button>
    `;
    
    container.appendChild(filaReactivo);
}

function eliminarReactivoFila(index) {
    const fila = document.getElementById(`reactivo-fila-${index}`);
    if (fila) {
        fila.remove();
    }
}

async function guardarPlanta(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const id = document.getElementById('planta_id').value;
    formData.append('action', id ? 'update' : 'create');
    if (id) formData.append('id', id);

    try {
        const response = await fetch(API_URL, { method: 'POST', body: formData });
        const data = await response.json();
        if (data.success) {
            mostrarNotificacion(data.message || 'Planta guardada', 'success');
            cerrarModal();
            await cargarPlantas();
        } else {
            mostrarNotificacion(data.message || 'Error', 'error');
        }
    } catch (error) {
        mostrarNotificacion('Error al guardar', 'error');
    }
}

function cerrarModal() {
    document.getElementById('modalPlantaOverlay').classList.remove('active');
}

function cerrarModalFase() {
    document.getElementById('modalFaseOverlay').classList.remove('active');
}

function mostrarNotificacion(mensaje, tipo) {
    const toast = document.createElement('div');
    toast.textContent = mensaje;
    toast.style.cssText = `position:fixed;top:20px;right:20px;padding:16px 24px;background:${tipo==='success'?'#10b981':'#ef4444'};color:white;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:10000;animation:slideIn 0.3s;font-weight:600;min-width:250px`;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}