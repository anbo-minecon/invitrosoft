<?php
/**
 * Página de Notificaciones para aprendices
 * Ubicación: /main/aprendiz/frontend/notificaciones.php
 */

require_once '../../includes/auth_check.php';

$tipo = isset($_SESSION['user_tipo']) ? strtolower($_SESSION['user_tipo']) : '';
if ($tipo === 'admin') {
    header('Location: ../../admin/index.php');
    exit;
}
if ($tipo !== 'aprendiz' && $tipo !== 'pasante') {
    header('Location: ../../src/index.html');
    exit;
}

require_once __DIR__ . '/../../../includes/ui_feedback.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificaciones - Invitrosoft</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/notificaciones.css">
    <link rel="stylesheet" href="styles/plantaspanel.css">
    <?php ui_loading_styles(); ?>
</head>
<body>
    <?php echo ui_loading_overlay('Cargando notificaciones...'); ui_loading_script(); ?>
    
    <script src="js/header-footer.js"></script>

    <main class="notificaciones-container">
        <div class="notificaciones-header">
            <h1>Notificaciones</h1>
            <div class="notificaciones-controls">
                <button id="filtroTodas" class="filtro-btn active" data-filter="todas">Todas</button>
                <button id="filtroNoLeidas" class="filtro-btn" data-filter="no_leidas">No leídas</button>
                <button id="filtroLeidas" class="filtro-btn" data-filter="leidas">Leídas</button>
                <button id="marcarTodo" class="btn-action" title="Marcar todas como leídas">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Marcar todas
                </button>
            </div>
        </div>

        <div id="notificacionesList" class="notificaciones-list">
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Cargando notificaciones...</p>
            </div>
        </div>

        <div id="paginationControls" class="pagination-controls" style="display: none;">
            <button id="btnAnterior" class="btn-pagination">← Anterior</button>
            <span id="pageInfo"></span>
            <button id="btnSiguiente" class="btn-pagination">Siguiente →</button>
        </div>
    </main>

    <script src="js/notificaciones.js"></script>
</body>
</html>
