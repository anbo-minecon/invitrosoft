<?php
require_once 'verificar_sesion.php';
require_once '../../db/conexion.php';
require_once 'Notificacion.php';

// Obtener notificaciones (todas en una sola página)
$notificacion = new Notificacion($conn);

// Obtener total de notificaciones para el usuario (incluyendo leídas y no leídas)
$totalNotificaciones = $notificacion->contarTodas($_SESSION['user_id']);

// Obtener todas las notificaciones del usuario (sin paginación) - Incluyendo leídas y no leídas
$soloNoLeidas = false; // Aseguramos que sea falso para obtener todas las notificaciones
$notificaciones = $notificacion->obtenerTodasPorUsuario($_SESSION['user_id'], $soloNoLeidas);

// Debug: Mostrar información de notificaciones
error_log("=== TODAS LAS NOTIFICACIONES ===");
error_log("Usuario ID: " . $_SESSION['user_id']);
error_log("Total notificaciones en BD: $totalNotificaciones");
error_log("Notificaciones obtenidas: " . count($notificaciones));

// Depuración: Verificar el conteo
error_log("=== index.php ===");
error_log("Total de notificaciones en BD: " . $totalNotificaciones);
error_log("Notificaciones obtenidas: " . count($notificaciones));

// Depuración: Mostrar las primeras 10 notificaciones
if (!empty($notificaciones)) {
    error_log("=== PRIMERAS 10 NOTIFICACIONES ===");
    $primeras10 = array_slice($notificaciones, 0, 10);
    foreach ($primeras10 as $i => $notif) {
        error_log(sprintf("%d. ID: %d - %s - %s", 
            $i + 1, 
            $notif['id'], 
            $notif['fecha_creacion'],
            $notif['titulo']
        ));
    }
}

// Debug: Log first 5 notification IDs for verification
if (!empty($notificaciones)) {
    $firstFive = array_slice($notificaciones, 0, 5);
    $ids = array_column($firstFive, 'id');
    error_log("Primeras 5 notificaciones (ID): " . implode(', ', $ids));
}

// Incluir el header
$titulo_pagina = "Historial de Actividades";
include 'header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Historial de Actividades</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../../index.php">Inicio</a></li>
        <li class="breadcrumb-item active">Actividades</li>
    </ol>

    <!-- Estadísticas rápidas -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total de Actividades</h5>
                    <h2><?= $totalNotificaciones ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Página Actual</h5>
                    <h2><?= $pagina ?> de <?= $totalPaginas ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>En esta página</h5>
                    <h2><?= count($notificaciones) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Registro de Actividades
            <span class="badge bg-secondary float-end">
                Mostrando <?= count($notificaciones) ?> de <?= $totalNotificaciones ?>
            </span>
        </div>
        <div class="card-body">
            <?php if (empty($notificaciones)): ?>
                <div class="alert alert-info text-center py-5">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <h5>No hay actividades registradas</h5>
                    <p class="text-muted">Las actividades aparecerán aquí cuando se registren cambios en el sistema.</p>
                </div>
            <?php else: 
                // Depuración: Mostrar información sobre las notificaciones
                $numNotificaciones = count($notificaciones);
                $primeras5 = array_slice($notificaciones, 0, 5);
                $ids = array_column($primeras5, 'id');
                error_log("Mostrando $numNotificaciones notificaciones en la tabla");
                error_log("Primeras 5 IDs: " . implode(', ', $ids));
            ?>
                <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                    <table class="table table-hover table-striped" id="tablaNotificaciones" style="margin-bottom: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 15%">Fecha</th>
                                <th style="width: 15%">Acción</th>
                                <th style="width: 60%">Descripción</th>
                                <th style="width: 10%">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            error_log("Iniciando bucle para mostrar " . count($notificaciones) . " notificaciones");
                            $contador = 0;
                            foreach ($notificaciones as $notif): 
                                $contador++;
                                $claseFila = $notif['leida'] ? '' : 'table-primary';
                                
                                // Depuración: Verificar los datos de cada notificación
                                error_log(sprintf("Notificación %d: ID=%d, Título=%s, Leída=%s", 
                                    $contador, 
                                    $notif['id'], 
                                    $notif['titulo'],
                                    $notif['leida'] ? 'Sí' : 'No'
                                ));
                                
                                // Calcular tiempo transcurrido
                                $fecha = new DateTime($notif['fecha_creacion']);
                                $ahora = new DateTime();
                                $diferencia = $ahora->diff($fecha);
                                
                                if ($diferencia->d > 7) {
                                    $tiempoTranscurrido = $fecha->format('d/m/Y');
                                } elseif ($diferencia->d > 0) {
                                    $tiempoTranscurrido = 'Hace ' . $diferencia->d . ' días';
                                } elseif ($diferencia->h > 0) {
                                    $tiempoTranscurrido = 'Hace ' . $diferencia->h . ' horas';
                                } else {
                                    $tiempoTranscurrido = 'Hace unos minutos';
                                }
                            ?>
                            <tr class="<?= $claseFila ?>" 
                                onclick="marcarComoLeida(<?= $notif['id'] ?>, this)"
                                style="cursor: pointer;"
                                title="Click para marcar como leída">
                                <td>
                                    <div class="fw-bold">
                                        <?= date('d/m/Y', strtotime($notif['fecha_creacion'])) ?>
                                    </div>
                                    <small class="text-muted">
                                        <?= date('H:i', strtotime($notif['fecha_creacion'])) ?> hs
                                    </small>
                                    <div>
                                        <small class="text-muted fst-italic">
                                            <?= $tiempoTranscurrido ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    // Determinar el color del badge según el tipo
                                    $badgeClass = 'info';
                                    $iconClass = 'fa-info-circle';
                                    
                                    switch($notif['tipo']) {
                                        case 'error':
                                        case 'danger':
                                            $badgeClass = 'danger';
                                            $iconClass = 'fa-times-circle';
                                            break;
                                        case 'warning':
                                            $badgeClass = 'warning';
                                            $iconClass = 'fa-exclamation-triangle';
                                            break;
                                        case 'success':
                                            $badgeClass = 'success';
                                            $iconClass = 'fa-check-circle';
                                            break;
                                    }
                                    ?>
                                    <span class="badge bg-<?= $badgeClass ?>">
                                        <i class="fas <?= $iconClass ?>"></i>
                                        <?= ucfirst($notif['accion'] ?? 'notificación') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold mb-1"><?= htmlspecialchars($notif['titulo']) ?></div>
                                    <div class="text-muted small"><?= nl2br(htmlspecialchars($notif['mensaje'])) ?></div>
                                    <?php if ($notif['modulo']): ?>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-cube"></i> 
                                                <strong>Módulo:</strong> <?= htmlspecialchars($notif['modulo']) ?>
                                                <?php if ($notif['entidad']): ?>
                                                    | <strong><?= ucfirst($notif['entidad']) ?></strong>
                                                    <?php if ($notif['entidad_id']): ?>
                                                        <span class="badge bg-light text-dark">#<?= $notif['entidad_id'] ?></span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($notif['leida']): ?>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-check"></i> Leída
                                        </span>
                                        <?php if ($notif['fecha_lectura']): ?>
                                            <small class="d-block text-muted mt-1">
                                                <?= date('d/m H:i', strtotime($notif['fecha_lectura'])) ?>
                                            </small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-primary">
                                            <i class="fas fa-bell"></i> Nueva
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mostrar total de notificaciones -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Mostrando <strong><?= count($notificaciones) ?></strong> de <?= $totalNotificaciones ?> notificaciones en total
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Función para marcar una notificación como leída
function marcarComoLeida(id, elemento) {
    // Si se hizo clic en un enlace dentro de la fila, no hacer nada
    if (event && event.target && event.target.tagName === 'A') {
        return true;
    }

    // Marcar visualmente como leída inmediatamente
    if (elemento) {
        elemento.classList.remove('table-primary');
        const estadoBadge = elemento.querySelector('td:last-child .badge');
        if (estadoBadge) {
            estadoBadge.className = 'badge bg-secondary';
            estadoBadge.innerHTML = '<i class="fas fa-check"></i> Leída';
        }
    }

    // Enviar petición al servidor
    fetch('marcar_notificacion_leida.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Notificación marcada como leída:', id);
            
            // Actualizar el contador de notificaciones no leídas en el header
            const contador = document.getElementById('contadorNotificaciones');
            if (contador && data.total_no_leidas !== undefined) {
                if (data.total_no_leidas > 0) {
                    contador.textContent = data.total_no_leidas > 9 ? '9+' : data.total_no_leidas;
                    contador.style.display = 'block';
                } else {
                    contador.style.display = 'none';
                }
            }
        } else {
            console.error('Error al marcar notificación:', data.error);
        }
    })
    .catch(error => {
        console.error('Error en la petición:', error);
        // Revertir cambio visual si hay error
        if (elemento) {
            elemento.classList.add('table-primary');
        }
    });
}

// Agregar efecto hover mejorado
document.addEventListener('DOMContentLoaded', function() {
    const filas = document.querySelectorAll('#tablaNotificaciones tbody tr');
    filas.forEach(fila => {
        fila.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.01)';
            this.style.transition = 'transform 0.2s';
        });
        fila.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    console.log('Página cargada - Total de notificaciones en tabla:', filas.length);
});
</script>

<?php
// Incluir el footer
include 'footer.php';
?>