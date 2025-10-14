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
    <title>Mis plantas - Invitrosoft</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/plantaspanel.css">
    <link rel="stylesheet" href="styles/style-plantas.css">
    <style>
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        
        .btn-icon.fase {
            color: #8b5cf6;
        }
        .btn-icon.fase:hover:not(:disabled) {
            background: #8b5cf6;
            color: white;
            border-color: #8b5cf6;
            box-shadow: 0 4px 14px rgba(139, 92, 246, 0.3);
        }
        .btn-icon:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
        
        /* Estilos para el contenedor de reactivos */
        #reactivosContainer {
            max-height: 300px;
            overflow-y: auto;
        }
        
        #reactivosContainer:empty::before {
            content: 'No hay reactivos agregados. Haz clic en "Agregar Reactivo" para comenzar.';
            display: block;
            padding: 20px;
            text-align: center;
            color: #6b7280;
            background: #f9fafb;
            border-radius: 8px;
            border: 2px dashed #d1d5db;
        }
    </style>
</head>
<body>
    <main class="main-panel">
        <section class="panel-header">
            <h1>Mis plantas</h1>
            <button class="btn-primary" id="btnNuevaPlanta">
                <svg width="20" height="20" fill="none">
                    <circle cx="10" cy="10" r="10" fill="currentColor" opacity="0.2"/>
                    <path d="M10 5v10M5 10h10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Nueva planta
            </button>
        </section>

        <!-- Filtros y búsqueda -->
        <section class="filters-section">
            <div class="filters-row">
                <div class="search-box">
                    <svg width="20" height="20" fill="none" stroke="#6b7280" stroke-width="2">
                        <circle cx="9" cy="9" r="6"/>
                        <path d="M14 14l4 4"/>
                    </svg>
                    <input type="text" id="searchPlanta" placeholder="Buscar por nombre, código o especie...">
                </div>
                <select class="filter-select" id="filterFase">
                    <option value="">Todas las fases</option>
                </select>
                <select class="filter-select" id="filterEstado">
                    <option value="">Todos los estados</option>
                </select>
            </div>
        </section>

        <!-- Lista de plantas -->
        <section class="plantas-list">
            <div class="empty-state">
                <svg width="80" height="80" fill="none">
                    <circle cx="40" cy="40" r="40" fill="#e8e8e8"/>
                    <path d="M40 25v30M25 40h30" stroke="#007832" stroke-width="4" stroke-linecap="round"/>
                </svg>
                <p>Cargando plantas...</p>
            </div>
        </section>
    </main>

    <!-- Modal para crear/editar planta -->
    <div class="modal-overlay" id="modalPlantaOverlay">
        <div class="modal">
            <div class="modal-header">
                <h2 id="modalPlantaTitle">Nueva Planta</h2>
                <button type="button" class="modal-close" id="modalPlantaClose">&times;</button>
            </div>
            <form id="formPlanta">
                <div class="modal-body">
                    <input type="hidden" id="planta_id" name="id">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="codigo">Código *</label>
                            <input type="text" id="codigo" name="codigo" required 
                                   placeholder="Ej: P001">
                        </div>
                        <div class="form-group">
                            <label for="nombre_comun">Nombre común *</label>
                            <input type="text" id="nombre_comun" name="nombre_comun" required 
                                   placeholder="Ej: Piña">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre_cientifico">Nombre científico</label>
                            <input type="text" id="nombre_cientifico" name="nombre_cientifico" 
                                   placeholder="Ej: Ananas comosus">
                        </div>
                        <div class="form-group">
                            <label for="especie">Especie</label>
                            <input type="text" id="especie" name="especie" 
                                   placeholder="Ej: Ananas">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="origen_id">Origen</label>
                            <select id="origen_id" name="origen_id">
                                <option value="">Seleccionar origen</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="protocolo_id">Protocolo</label>
                            <select id="protocolo_id" name="protocolo_id">
                                <option value="">Seleccionar protocolo</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="metodo_propagacion">Método de propagación</label>
                            <input type="text" id="metodo_propagacion" name="metodo_propagacion" 
                                   placeholder="Ej: Murashige y Skoog">
                        </div>
                        <div class="form-group">
                            <label for="estado_id">Estado</label>
                            <select id="estado_id" name="estado_id">
                                <option value="">Seleccionar estado</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha_inicio">Fecha inicio</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio">
                        </div>
                        <div class="form-group">
                            <label for="fecha_fin">Fecha fin</label>
                            <input type="date" id="fecha_fin" name="fecha_fin">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="observaciones">Observaciones</label>
                        <textarea id="observaciones" name="observaciones" rows="4" 
                                  placeholder="Observaciones adicionales sobre la planta..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="btnCancelar">Cancelar</button>
                    <button type="submit" class="btn-primary">
                        <svg width="18" height="18" fill="currentColor">
                            <path d="M2 10 L7 15 L16 3" stroke="currentColor" stroke-width="2" fill="none"/>
                        </svg>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para cambiar fase -->
    <div class="modal-overlay" id="modalFaseOverlay">
        <div class="modal">
            <div class="modal-header">
                <h2 id="modalFaseTitle">Cambiar Fase</h2>
                <button type="button" class="modal-close" id="modalFaseClose">&times;</button>
            </div>
            <form id="formFase">
                <div class="modal-body">
                    <input type="hidden" id="planta_id_fase" name="planta_id">
                    <input type="hidden" id="fase" name="fase">
                    
                    <div id="camposFase">
                        <!-- Los campos se generan dinámicamente según la fase -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="btnCancelarFase">Cancelar</button>
                    <button type="submit" class="btn-primary">
                        <svg width="18" height="18" fill="currentColor">
                            <path d="M2 10 L7 15 L16 3" stroke="currentColor" stroke-width="2" fill="none"/>
                        </svg>
                        Guardar Cambio de Fase
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/header-footer.js"></script>
    <script src="js/plantas.js"></script>
</body>
</html>