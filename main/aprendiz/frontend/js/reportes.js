// reportes.js - Lógica para generar reportes y gráficos
const API_URL = '/invitrosoft-dos/main/aprendiz/backend/reportes.php';

let chartFases, chartSemanal, chartPlantas, chartReactivos;
let datosReportes = null;

document.addEventListener('DOMContentLoaded', async function() {
    await cargarReportes();
    
    document.getElementById('btnFiltrar').addEventListener('click', aplicarFiltros);
    document.getElementById('btnLimpiar').addEventListener('click', limpiarFiltros);
    document.getElementById('btnExportarPDF').addEventListener('click', exportarPDF);
    
    // Establecer fechas por defecto (últimos 30 días)
    const hoy = new Date();
    const hace30Dias = new Date(hoy);
    hace30Dias.setDate(hoy.getDate() - 30);
    
    document.getElementById('fechaFin').value = hoy.toISOString().split('T')[0];
    document.getElementById('fechaInicio').value = hace30Dias.toISOString().split('T')[0];
});

async function cargarReportes(fechaInicio = null, fechaFin = null) {
    try {
        let url = `${API_URL}?action=getReportes`;
        if (fechaInicio) url += `&fecha_inicio=${fechaInicio}`;
        if (fechaFin) url += `&fecha_fin=${fechaFin}`;
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            datosReportes = data;
            renderizarGraficoFases(data.fases);
            renderizarGraficoSemanal(data.semanal);
            renderizarGraficoPlantas(data.plantas);
            renderizarGraficoReactivos(data.reactivos);
            renderizarPlantasRecientes(data.recientes);
            renderizarUsuariosActivos(data.usuarios);
            actualizarEstadisticas(data);
        } else {
            mostrarNotificacion('Error al cargar reportes', 'error');
        }
    } catch (error) {
        console.error(error);
        mostrarNotificacion('Error al cargar reportes', 'error');
    }
}

function renderizarGraficoFases(datos) {
    const ctx = document.getElementById('chartFases').getContext('2d');
    
    if (chartFases) {
        chartFases.destroy();
    }
    
    const colores = {
        'seleccion': '#3b82f6',
        'establecimiento': '#f59e0b',
        'multiplicacion': '#8b5cf6',
        'enraizamiento': '#10b981',
        'adaptacion': '#06b6d4'
    };
    
    const labels = datos.map(d => d.fase.charAt(0).toUpperCase() + d.fase.slice(1));
    const values = datos.map(d => parseInt(d.total));
    const backgrounds = datos.map(d => colores[d.fase] || '#6b7280');
    
    chartFases = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: backgrounds,
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        font: {
                            size: 14,
                            weight: 'bold'
                        },
                        padding: 15,
                        generateLabels: function(chart) {
                            const data = chart.data;
                            return data.labels.map((label, i) => {
                                const value = data.datasets[0].data[i];
                                const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                const percent = ((value / total) * 100).toFixed(1);
                                return {
                                    text: `${label}: ${value} (${percent}%)`,
                                    fillStyle: data.datasets[0].backgroundColor[i],
                                    hidden: false,
                                    index: i
                                };
                            });
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percent = ((context.parsed / total) * 100).toFixed(1);
                            return `${context.label}: ${context.parsed} plantas (${percent}%)`;
                        }
                    }
                }
            }
        }
    });
}

function renderizarGraficoSemanal(datos) {
    const ctx = document.getElementById('chartSemanal').getContext('2d');
    
    if (chartSemanal) {
        chartSemanal.destroy();
    }
    
    const colores = {
        'seleccion': '#3b82f6',
        'establecimiento': '#f59e0b',
        'multiplicacion': '#8b5cf6',
        'enraizamiento': '#10b981',
        'adaptacion': '#06b6d4'
    };
    
    // Agrupar datos por semana y fase
    const semanas = {};
    datos.forEach(d => {
        if (!semanas[d.semana]) {
            semanas[d.semana] = {};
        }
        semanas[d.semana][d.fase] = parseInt(d.total);
    });
    
    const labels = Object.keys(semanas).sort();
    const fases = ['seleccion', 'establecimiento', 'multiplicacion', 'enraizamiento', 'adaptacion'];
    
    const datasets = fases.map(fase => ({
        label: fase.charAt(0).toUpperCase() + fase.slice(1),
        data: labels.map(sem => semanas[sem][fase] || 0),
        backgroundColor: colores[fase],
        borderRadius: 6
    }));
    
    chartSemanal = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels.map(sem => `Semana ${sem}`),
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                x: {
                    stacked: false,
                    grid: {
                        display: false
                    }
                },
                y: {
                    stacked: false,
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            size: 12,
                            weight: 'bold'
                        },
                        padding: 15
                    }
                }
            }
        }
    });
}

function renderizarGraficoPlantas(datos) {
    const ctx = document.getElementById('chartPlantas').getContext('2d');
    
    if (chartPlantas) {
        chartPlantas.destroy();
    }
    
    const labels = datos.map(d => d.nombre_comun);
    const values = datos.map(d => parseInt(d.total));
    
    // Generar colores aleatorios para cada planta
    const backgrounds = labels.map(() => {
        const r = Math.floor(Math.random() * 200);
        const g = Math.floor(Math.random() * 200);
        const b = Math.floor(Math.random() * 200);
        return `rgba(${r}, ${g}, ${b}, 0.7)`;
    });
    
    chartPlantas = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Cantidad de plantas',
                data: values,
                backgroundColor: backgrounds,
                borderRadius: 8,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

function renderizarGraficoReactivos(datos) {
    const ctx = document.getElementById('chartReactivos').getContext('2d');
    
    if (chartReactivos) {
        chartReactivos.destroy();
    }
    
    const labels = datos.map(d => d.nombre_comun);
    const values = datos.map(d => parseFloat(d.total_usado));
    
    chartReactivos = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Cantidad utilizada',
                data: values,
                backgroundColor: '#10b981',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

function renderizarPlantasRecientes(plantas) {
    const contenedor = document.getElementById('plantasRecientes');
    
    if (!plantas || plantas.length === 0) {
        contenedor.innerHTML = '<div class="empty-state">No hay plantas recientes</div>';
        return;
    }
    
    contenedor.innerHTML = plantas.map(p => `
        <div class="planta-item">
            <div class="planta-info">
                <h4>${p.nombre_comun}</h4>
                <p>Código: ${p.codigo} | ${new Date(p.fecha_creacion).toLocaleDateString()}</p>
            </div>
            <span class="planta-badge ${p.fase_actual}">${p.fase_actual}</span>
        </div>
    `).join('');
}

function renderizarUsuariosActivos(usuarios) {
    const contenedor = document.getElementById('usuariosActivos');
    
    if (!usuarios || usuarios.length === 0) {
        contenedor.innerHTML = '<div class="empty-state">No hay usuarios activos</div>';
        return;
    }
    
    contenedor.innerHTML = usuarios.map(u => {
        const iniciales = u.nombre.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
        return `
            <div class="usuario-item">
                <div class="usuario-info">
                    <div class="usuario-avatar">${iniciales}</div>
                    <div class="usuario-details">
                        <h4>${u.nombre}</h4>
                        <p>${u.rol || 'Usuario'}</p>
                    </div>
                </div>
                <span class="usuario-count">${u.total_plantas}</span>
            </div>
        `;
    }).join('');
}

function actualizarEstadisticas(data) {
    const total = data.fases.reduce((sum, f) => sum + parseInt(f.total), 0);
    document.getElementById('totalPlantas').textContent = total;
    
    if (data.fases.length > 0) {
        const faseMax = data.fases.reduce((max, f) => 
            parseInt(f.total) > parseInt(max.total) ? f : max
        );
        const faseNombre = faseMax.fase.charAt(0).toUpperCase() + faseMax.fase.slice(1);
        document.getElementById('faseComun').textContent = faseNombre;
    }
}

async function aplicarFiltros() {
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;
    
    if (fechaInicio && fechaFin && fechaInicio > fechaFin) {
        mostrarNotificacion('La fecha de inicio no puede ser mayor a la fecha fin', 'error');
        return;
    }
    
    await cargarReportes(fechaInicio, fechaFin);
}

function limpiarFiltros() {
    document.getElementById('fechaInicio').value = '';
    document.getElementById('fechaFin').value = '';
    cargarReportes();
}

function exportarPDF() {
    mostrarNotificacion('Función de exportación en desarrollo', 'info');
}

function mostrarNotificacion(mensaje, tipo) {
    const colores = {
        'success': '#10b981',
        'error': '#ef4444',
        'info': '#3b82f6'
    };
    
    const toast = document.createElement('div');
    toast.textContent = mensaje;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 24px;
        background: ${colores[tipo] || '#6b7280'};
        color: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        animation: slideIn 0.3s;
        font-weight: 600;
        min-width: 250px;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}