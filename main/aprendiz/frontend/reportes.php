<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /invitrosoft/src/index.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes - Invitrosoft</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/plantaspanel.css">
    <link rel="stylesheet" href="styles/reportes.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <main class="main-panel">
        <section class="panel-header">
            <h1>Reportes y Estadísticas</h1>
            <div class="header-controls">
                <button class="btn-primary" id="btnExportarPDF">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2v6h6M6 18h12M6 14h12M6 10h4"/>
                        <path d="M4 2h10l6 6v12a2 2 0 01-2 2H4a2 2 0 01-2-2V4a2 2 0 012-2z"/>
                    </svg>
                    Exportar PDF
                </button>
            </div>
        </section>

        <!-- Filtros de fecha -->
        <section class="filters-section">
            <div class="filters-row">
                <div class="form-group">
                    <label>Fecha inicio</label>
                    <input type="date" id="fechaInicio" class="filter-input">
                </div>
                <div class="form-group">
                    <label>Fecha fin</label>
                    <input type="date" id="fechaFin" class="filter-input">
                </div>
                <button class="btn-primary" id="btnFiltrar">Aplicar filtros</button>
                <button class="btn-secondary" id="btnLimpiar">Limpiar</button>
            </div>
        </section>

        <!-- Grid de reportes -->
        <div class="reportes-grid">
            <!-- Diagrama circular de fases -->
            <div class="reporte-card card-large">
                <div class="card-header">
                    <h3>Distribución por Fases</h3>
                    <span class="card-subtitle">Estado actual de plantas</span>
                </div>
                <div class="card-body">
                    <canvas id="chartFases"></canvas>
                </div>
                <div class="card-footer">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="stat-label">Total plantas</span>
                            <span class="stat-value" id="totalPlantas">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Fase más común</span>
                            <span class="stat-value" id="faseComun">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico semanal -->
            <div class="reporte-card card-large">
                <div class="card-header">
                    <h3>Fases por Semana</h3>
                    <span class="card-subtitle">Inicio de fases en el tiempo</span>
                </div>
                <div class="card-body">
                    <canvas id="chartSemanal"></canvas>
                </div>
            </div>

            <!-- Proceso por tipo de planta -->
            <div class="reporte-card card-medium">
                <div class="card-header">
                    <h3>Proceso por Tipo de Planta</h3>
                    <span class="card-subtitle">Distribución por especie</span>
                </div>
                <div class="card-body">
                    <canvas id="chartPlantas"></canvas>
                </div>
            </div>

            <!-- Tabla de plantas generadas -->
            <div class="reporte-card card-medium">
                <div class="card-header">
                    <h3>Plantas Generadas</h3>
                    <span class="card-subtitle">Últimos 30 días</span>
                </div>
                <div class="card-body">
                    <div class="plantas-list" id="plantasRecientes">
                        <div class="empty-state">Cargando...</div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de reactivos generados -->
            <div class="reporte-card card-medium">
                <div class="card-header">
                    <h3>Reactivos Más Utilizados</h3>
                    <span class="card-subtitle">Top 5 reactivos</span>
                </div>
                <div class="card-body">
                    <canvas id="chartReactivos"></canvas>
                </div>
            </div>

            <!-- Tarjeta de usuarios activos -->
            <div class="reporte-card card-medium">
                <div class="card-header">
                    <h3>Usuarios Activos</h3>
                    <span class="card-subtitle">Registro de plantas por usuario</span>
                </div>
                <div class="card-body">
                    <div class="usuarios-list" id="usuariosActivos">
                        <div class="empty-state">Cargando...</div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="js/header-footer.js"></script>
    <script src="js/reportes.js"></script>
</body>
</html>