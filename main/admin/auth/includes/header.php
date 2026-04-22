<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../../db/conexion.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /invitrosoft/src/index.html');
    exit();
}

// Initialize notification variables
$notificaciones = [];
$totalNoLeidas = 0;

try {
    // Include and initialize Notificacion class
    require_once 'Notificacion.php';
    $notificacion = new Notificacion($conn);
    
    // Get all notifications for the current user (both read and unread)
    $notificaciones = $notificacion->obtenerPorUsuario($_SESSION['user_id'], 5, false);
    // Still count unread notifications for the badge
    $totalNoLeidas = $notificacion->contarNoLeidas($_SESSION['user_id']);
} catch (Exception $e) {
    // Log the error but don't show it to the user
    error_log('Error al cargar notificaciones: ' . $e->getMessage());
}

// Set page title if not already set
if (!isset($titulo_pagina)) {
    $titulo_pagina = 'Panel de Control';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo_pagina) ?> - Invitrosoft</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link href="style.css" rel="stylesheet">
    <link href="notifications.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../">Invitrosoft</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="notificaciones/">Notificaciones</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <li class="nav-item dropdown">
                            <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                <?php if ($totalNoLeidas > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="contadorNotificaciones">
                                    <?= $totalNoLeidas > 9 ? '9+' : $totalNoLeidas ?>
                                    <span class="visually-hidden">notificaciones sin leer</span>
                                </span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
                                <li>
                                    <div class="dropdown-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Notificaciones</h6>
                                        <span class="badge bg-success"><?= $totalNoLeidas ?> sin leer</span>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <div id="listaNotificaciones" style="max-height: 300px; overflow-y: auto;">
                                    <?php if (empty($notificaciones)): ?>
                                        <li class="text-center py-3">
                                            <i class="fas fa-bell-slash text-muted mb-2 d-block" style="font-size: 1.5rem;"></i>
                                            <p class="mb-0 text-muted">No hay notificaciones nuevas</p>
                                        </li>
                                    <?php else: ?>
                                        <?php foreach ($notificaciones as $notif): 
                                            $fecha = new DateTime($notif['fecha_creacion']);
                                            $ahora = new DateTime();
                                            $diferencia = $ahora->diff($fecha);
                                            
                                            if ($diferencia->d > 7) {
                                                $tiempo = $fecha->format('d/m/Y');
                                            } elseif ($diferencia->d > 0) {
                                                $tiempo = 'Hace ' . $diferencia->d . ' días';
                                            } elseif ($diferencia->h > 0) {
                                                $tiempo = 'Hace ' . $diferencia->h . ' horas';
                                            } else {
                                                $tiempo = 'Hace unos minutos';
                                            }
                                            
                                            $tipoClase = 'notification-' . ($notif['tipo'] ?? 'info');
                                            $noLeidaClase = $notif['leida'] == 0 ? 'unread' : '';
                                            $icon = 'info-circle';
                                            switch ($notif['tipo']) {
                                                case 'success':
                                                    $icon = 'check-circle';
                                                    break;
                                                case 'warning':
                                                    $icon = 'exclamation-triangle';
                                                    break;
                                                case 'error':
                                                    $icon = 'times-circle';
                                                    break;
                                            }
                                        ?>
                                        <li>
                                            <a class="dropdown-item notification-item <?= $noLeidaClase ?>" href="#" onclick="marcarNotificacionLeida(<?= $notif['id'] ?>, this); return false;">
                                                <div class="d-flex align-items-start">
                                                    <div class="flex-shrink-0 me-3">
                                                        <i class="fas fa-<?= $icon ?> <?= $tipoClase ?> fa-lg"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <h6 class="mb-0 notification-title"><?= htmlspecialchars($notif['titulo']) ?></h6>
                                                            <small class="text-muted">
                                                                <i class="far fa-clock"></i> <?= $tiempo ?>
                                                            </small>
                                                        </div>
                                                        <p class="mb-0 notification-message"><?= htmlspecialchars($notif['mensaje']) ?></p>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <li>
                                    <a class="dropdown-item text-center text-success fw-bold" href="index.php">
                                        Ver todas las notificaciones
                                        <i class="fas fa-arrow-right ms-2"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.html">Mi Perfil</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Page content will be inserted here -->