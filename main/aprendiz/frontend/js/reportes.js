// reportes.js - Lógica para generar reportes y gráficos
const API_URL = '../backend/reportes.php';
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
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (!data || data.success === false) {
            const errorMsg = data?.message || 'No se pudo cargar la información de los reportes';
            throw new Error(errorMsg);
        }

        // Asegurarse de que todos los arrays existan
        data.fases = Array.isArray(data.fases) ? data.fases : [];
        data.semanal = Array.isArray(data.semanal) ? data.semanal : [];
        data.plantas = Array.isArray(data.plantas) ? data.plantas : [];
        data.reactivos = Array.isArray(data.reactivos) ? data.reactivos : [];
        data.recientes = Array.isArray(data.recientes) ? data.recientes : [];

        datosReportes = data;
        
        // Renderizar todos los gráficos
        if (data.fases && data.fases.length > 0) {
            renderizarGraficoFases(data.fases);
        } else {
            console.warn("No hay datos de fases para mostrar");
            const chartContainer = document.getElementById('chartFases')?.closest('.reporte-card');
            if (chartContainer) {
                chartContainer.querySelector('.card-body').innerHTML = '<div style="padding: 20px; text-align: center; color: #6b7280;">No hay datos disponibles</div>';
            }
        }
        
        if (data.semanal && data.semanal.length > 0) {
            renderizarGraficoSemanal(data.semanal);
        } else {
            console.warn("No hay datos semanales para mostrar");
            const chartContainer = document.getElementById('chartSemanal')?.closest('.reporte-card');
            if (chartContainer) {
                chartContainer.querySelector('.card-body').innerHTML = '<div style="padding: 20px; text-align: center; color: #6b7280;">No hay datos disponibles</div>';
            }
        }
        
        if (data.plantas && data.plantas.length > 0) {
            renderizarGraficoPlantas(data.plantas);
        } else {
            console.warn("No hay datos de plantas para mostrar");
            const chartContainer = document.getElementById('chartPlantas')?.closest('.reporte-card');
            if (chartContainer) {
                chartContainer.querySelector('.card-body').innerHTML = '<div style="padding: 20px; text-align: center; color: #6b7280;">No hay datos disponibles</div>';
            }
        }
        
        if (data.reactivos && data.reactivos.length > 0) {
            renderizarGraficoReactivos(data.reactivos);
        } else {
            console.warn("No hay datos de reactivos para mostrar");
            const chartContainer = document.getElementById('chartReactivos')?.closest('.reporte-card');
            if (chartContainer) {
                chartContainer.querySelector('.card-body').innerHTML = '<div style="padding: 20px; text-align: center; color: #6b7280;">No hay datos disponibles</div>';
            }
        }
        
        if (data.recientes && data.recientes.length > 0) {
            renderizarPlantasRecientes(data.recientes);
        } else {
            document.getElementById('plantasRecientes').innerHTML = '<div class="empty-state">No hay plantas recientes para mostrar</div>';
        }
        
        actualizarEstadisticas(data);
        
    } catch (error) {
        console.error('Error en cargarReportes:', error);
        mostrarNotificacion(error.message || 'Ocurrió un error al cargar los reportes', 'error');
    }
}

function renderizarGraficoFases(datos) {
    const chartElement = document.getElementById('chartFases');
    
    if (!chartElement) {
        console.error('No se encontró el elemento con ID "chartFases"');
        return;
    }
    
    // Verificar si hay datos
    if (!Array.isArray(datos) || datos.length === 0) {
        console.warn('No hay datos para mostrar en el gráfico de fases');
        const container = chartElement.closest('.reporte-card');
        if (container) {
            container.querySelector('.card-body').innerHTML = '<div style="padding: 20px; text-align: center; color: #6b7280;">No hay datos disponibles</div>';
        }
        return;
    }

    // Validar que los datos tengan la estructura esperada
    const datosValidos = datos.every(item => item && typeof item === 'object' && 'fase' in item && 'total' in item);
    
    if (!datosValidos) {
        console.error('Los datos del gráfico de fases no tienen el formato esperado:', datos);
        const container = chartElement.closest('.reporte-card');
        if (container) {
            container.querySelector('.card-body').innerHTML = '<div style="padding: 20px; text-align: center; color: #6b7280;">Formato de datos incorrecto</div>';
        }
        return;
    }

    // Destruir instancia anterior si existe
    if (chartFases && typeof chartFases.destroy === 'function') {
        chartFases.destroy();
    }

    // Configuración del gráfico
    const ctx = chartElement.getContext('2d');
    const colores = {
        'seleccion': '#3b82f6',
        'establecimiento': '#f59e0b',
        'multiplicacion': '#8b5cf6',
        'enraizamiento': '#10b981',
        'adaptacion': '#06b6d4'
    };

    const labels = datos.map(d => d.fase ? d.fase.charAt(0).toUpperCase() + d.fase.slice(1) : '');
    const values = datos.map(d => parseInt(d.total) || 0);
    const backgrounds = datos.map(d => colores[d.fase] || '#6b7280');

    chartFases = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: backgrounds,
                borderWidth: 1
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
                            size: 12,
                            weight: 'bold'
                        },
                        padding: 15
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '70%',
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    });
}

function renderizarGraficoSemanal(datos) {
    const chartElement = document.getElementById('chartSemanal');
    
    if (!chartElement) {
        console.error('No se encontró el elemento chartSemanal');
        return;
    }
    
    const ctx = chartElement.getContext('2d');
    
    if (chartSemanal && typeof chartSemanal.destroy === 'function') {
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
    const chartElement = document.getElementById('chartPlantas');
    
    if (!chartElement) {
        console.error('No se encontró el elemento chartPlantas');
        return;
    }
    
    const ctx = chartElement.getContext('2d');
    
    if (chartPlantas && typeof chartPlantas.destroy === 'function') {
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
    const chartElement = document.getElementById('chartReactivos');
    
    if (!chartElement) {
        console.error('No se encontró el elemento chartReactivos');
        return;
    }
    
    const ctx = chartElement.getContext('2d');
    
    if (chartReactivos && typeof chartReactivos.destroy === 'function') {
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

function actualizarEstadisticas(data) {
    const totalElement = document.getElementById('totalPlantas');
    const faseComunElement = document.getElementById('faseComun');
    
    if (!totalElement || !faseComunElement) {
        console.warn('No se encontraron elementos de estadísticas');
        return;
    }
    
    const total = data.fases.reduce((sum, f) => sum + parseInt(f.total), 0);
    totalElement.textContent = total;
    
    if (data.fases.length > 0) {
        const faseMax = data.fases.reduce((max, f) => 
            parseInt(f.total) > parseInt(max.total) ? f : max
        );
        const faseNombre = faseMax.fase.charAt(0).toUpperCase() + faseMax.fase.slice(1);
        faseComunElement.textContent = faseNombre;
    } else {
        faseComunElement.textContent = '-';
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
    const hoy = new Date();
    const hace30Dias = new Date(hoy);
    hace30Dias.setDate(hoy.getDate() - 30);
    
    document.getElementById('fechaFin').value = hoy.toISOString().split('T')[0];
    document.getElementById('fechaInicio').value = hace30Dias.toISOString().split('T')[0];
    
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