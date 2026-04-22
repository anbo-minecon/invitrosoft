<?php
session_start();
require_once '../../db/conexion.php';
require_once 'Notificacion.php';

header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'No autorizado']));
}

// Verificar que se proporcionó un ID de notificación
if (!isset($_POST['id'])) {
    http_response_code(400);
    die(json_encode(['error' => 'ID de notificación no proporcionado']));
}

try {
    $notificacion = new Notificacion($conn);
    $resultado = $notificacion->marcarComoLeida($_POST['id'], $_SESSION['user_id']);
    
    if ($resultado) {
        // Obtener el nuevo conteo de notificaciones no leídas
        $totalNoLeidas = $notificacion->contarNoLeidas($_SESSION['user_id']);
        
        echo json_encode([
            'success' => true,
            'total_no_leidas' => $totalNoLeidas
        ]);
    } else {
        throw new Exception('No se pudo marcar la notificación como leída');
    }
} catch (Exception $e) {
    error_log('Error en marcar_notificacion_leida.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al marcar la notificación como leída',
        'debug' => $e->getMessage() // Solo para desarrollo, quitar en producción
    ]);
}
?>