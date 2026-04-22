<?php
// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Habilitar CORS si es necesario
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

try {
    // Registrar la solicitud entrante
    error_log("=== Inicio de solicitud de notificación ===");
    error_log("Método: " . $_SERVER['REQUEST_METHOD']);
    
    // Obtener los datos del cuerpo de la petición
    $input = file_get_contents('php://input');
    error_log("Datos recibidos: " . $input);
    
    $datos = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
    }

    // Incluir la clase Notificacion y la conexión
    require_once __DIR__ . '/Notificacion.php';
    require_once __DIR__ . '/../../db/conexion.php'; // Ruta relativa desde registrar_notificacion.php

    // Verificar conexión
    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new Exception("Error: No se pudo establecer la conexión a la base de datos");
    }

    // Crear instancia y registrar notificación
    $notificacion = new Notificacion($conn);
    $idNotificacion = $notificacion->registrar($datos);

    // Registrar éxito
    error_log("Notificación registrada con ID: " . $idNotificacion);
    
    // Devolver respuesta exitosa
    echo json_encode([
        'success' => true,
        'id' => $idNotificacion,
        'mensaje' => 'Notificación registrada correctamente'
    ]);

} catch (Exception $e) {
    // Registrar error
    error_log("Error en registrar_notificacion.php: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());
    
    // Devolver error
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString() // Solo en desarrollo
    ]);
}
?>