<?php
require_once '../../includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contaminaciones - Invitrosoft</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/plantaspanel.css">
    <link rel="stylesheet" href="styles/style-plantas.css">
    <link rel="stylesheet" href="styles/contaminaciones.css">
</head>
<body>
    <main class="main-panel">
        <section class="panel-header">
            <h1>Contaminaciones</h1>
            <div class="header-controls">
                <button class="btn-primary" id="btnDecision">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2v6m0 8v6"/>
                        <path d="M12 12l6-6M12 12l-6 6"/>
                    </svg>
                    Modo decisión
                </button>
            </div>
        </section>

        <section class="filters-section">
            <div class="filters-row">
                <div class="form-group">
                    <label>Búsqueda</label>
                    <input type="text" id="buscar" class="filter-input" placeholder="Código o nombre de planta">
                </div>
                <div class="form-group">
                    <label>Ordenar</label>
                    <select id="ordenar" class="filter-input">
                        <option value="">Recientes</option>
                        <option value="az">A → Z</option>
                        <option value="za">Z → A</option>
                        <option value="mas_conta">Más contaminaciones</option>
                        <option value="menos_conta">Menos contaminaciones</option>
                        <option value="fase">Por fase</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Fase</label>
                    <select id="fase" class="filter-input">
                        <option value="">Todas</option>
                        <option value="seleccion">Selección</option>
                        <option value="establecimiento">Establecimiento</option>
                        <option value="multiplicacion">Multiplicación</option>
                        <option value="enraizamiento">Enraizamiento</option>
                        <option value="adaptacion">Adaptación</option>
                    </select>
                </div>
                <button class="btn-primary" id="btnBuscar">Buscar</button>
                <button class="btn-primary" id="btnLimpiar">Limpiar</button>
            </div>
        </section>

        <section>
            <div class="plantas-grid" id="gridPlantas">
                <div class="empty-state">Cargando plantas...</div>
            </div>
        </section>
    </main>

    <!-- Modal Selección de Fase y Formulario -->
    <div class="modal" id="modalContaminacion" aria-hidden="true" style="background: transparent; max-width: none; width: 100%;">
        <div class="modal-backdrop" data-close></div>
        <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modalTitle" aria-describedby="modalDesc">
            <div class="modal-header">
                <h3 id="modalTitle">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z"/></svg>
                    Reportar contaminación
                </h3>
                <button class="modal-close" data-close aria-label="Cerrar">&times;</button>
            </div>
            <div class="modal-body">
                <p id="modalDesc" class="text-muted" style="margin-top:0;margin-bottom:14px;color:#6b7280;">Selecciona la fase de la planta y completa los detalles.</p>

                <div id="stepFases" class="step">
                    <div class="form-group">
                        <label>Seleccione la fase</label>
                        <div id="listaFases" class="chips"></div>
                    </div>
                </div>

                <div id="stepForm" class="step" style="display:none;">
                    <form id="formContaminacion">
                        <input type="hidden" id="plantaId">
                        <input type="hidden" id="faseTipo">
                        <input type="hidden" id="faseId">

                        <div class="form-group">
                            <label>Detalles de la contaminación</label>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Tipo</label>
                                <select id="tipo" required>
                                    <option value="">Seleccione</option>
                                    <option value="endogena">Endógena</option>
                                    <option value="exogena">Exógena</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Cantidad</label>
                                <input type="number" id="cantidad" min="0" value="0" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Motivo</label>
                            <input type="text" id="motivo" placeholder="Ej. contaminación fúngica" required>
                        </div>
                        <div class="form-group">
                            <label>Fecha de contaminación</label>
                            <input type="date" id="fecha" required>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn-secondary" id="btnAtras">Atrás</button>
                            <button type="submit" class="btn-primary">Registrar</button>
                        </div>
                        <div id="formMsg" class="form-msg" role="alert" style="display:none;"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="js/header-footer.js"></script>
    <script src="js/contaminaciones.js"></script>
</body>
</html>
