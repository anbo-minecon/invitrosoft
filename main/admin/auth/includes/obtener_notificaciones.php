<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../../db/conexion.php';
require_once 'Notificacion.php';

header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode([
        'success' => false,
        'error' => 'No autorizado'
    ]));
}

try {
    if ($conn->connect_error) {
        throw new Exception("Error de conexión a la base de datos: " . $conn->connect_error);
    }

    $notificacion = new Notificacion($conn);
    
    error_log("=== obtener_notificaciones.php ===");
    error_log("Usuario ID: " . $_SESSION['user_id']);
    
    // CRÍTICO: Usar límite de 5 solo para el dropdown del header
    // Este archivo se usa SOLO para actualizar el dropdown
    $limite = 5; // Mantener en 5 para el dropdown
    
    error_log("Límite para dropdown: $limite");
    
    // Obtener últimas 5 notificaciones (para el dropdown)
    $notificaciones = $notificacion->obtenerPorUsuario(
        $_SESSION['user_id'],
        $limite,
        false  // Mostrar tanto leídas como no leídas
    );
    
    error_log("Notificaciones obtenidas para dropdown: " . count($notificaciones));
    
    // Contar total de no leídas para el contador
    $totalNoLeidas = $notificacion->contarNoLeidas($_SESSION['user_id']);
    
    // Formatear fechas
    $notificacionesFormateadas = [];
    foreach ($notificaciones as $notif) {
        try {
            $fecha = new DateTime($notif['fecha_creacion'] ?? 'now');
            $ahora = new DateTime();
            $diferencia = $ahora->diff($fecha);
            
            if ($diferencia->d > 7) {
                $tiempo = $fecha->format('d/m/Y H:i');
            } elseif ($diferencia->d > 0) {
                $tiempo = 'Hace ' . $diferencia->d . ' días';
            } elseif ($diferencia->h > 0) {
                $tiempo = 'Hace ' . $diferencia->h . ' horas';
            } elseif ($diferencia->i > 0) {
                $tiempo = 'Hace ' . $diferencia->i . ' minutos';
            } else {
                $tiempo = 'Hace unos segundos';
            }
            
            $notificacionesFormateadas[] = [
                'id' => $notif['id'] ?? 0,
                'titulo' => $notif['titulo'] ?? 'Sin título',
                'mensaje' => $notif['mensaje'] ?? '',
                'tipo' => $notif['tipo'] ?? 'info',
                'fecha_creacion' => $notif['fecha_creacion'] ?? '',
                'tiempo' => $tiempo,
                'leida' => (bool)($notif['leida'] ?? false)
            ];
        } catch (Exception $e) {
            error_log("Error procesando notificación: " . $e->getMessage());
            continue;
        }
    }
    
    echo json_encode([
        'success' => true,
        'notificaciones' => $notificacionesFormateadas,
        'total' => $totalNoLeidas
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error en obtener_notificaciones.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener notificaciones'
    ], JSON_UNESCAPED_UNICODE);
}
?>
