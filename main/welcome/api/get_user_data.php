<?php
// ubicacion: /main/welcome/api/get_user_data.php

// Configuración de encabezados para API
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar solicitudes OPTIONS para CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_log("=== [GET_USER_DATA] INICIO ===");
error_log("[GET_USER_DATA] Session ID: " . session_id());
error_log("[GET_USER_DATA] User ID en sesión: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NO DEFINIDO'));

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    error_log("❌ [GET_USER_DATA] Usuario no autenticado");
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'No autorizado',
        'message' => 'Sesión no iniciada o expirada',
        'session_status' => session_status(),
        'session_id' => session_id()
    ]);
    exit;
}

// Incluir la conexión a la base de datos
try {
    $conexionPath = __DIR__ . '/conexion.php';
    
    if (!file_exists($conexionPath)) {
        throw new Exception('No se encontró el archivo de conexión en: ' . $conexionPath);
    }
    
    require_once $conexionPath;
    
    // Verificar si la conexión fue exitosa
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception('Error de conexión a la base de datos: ' . (isset($conn) ? $conn->connect_error : 'Conexión no establecida'));
    }
    
    error_log("✅ [GET_USER_DATA] Conexión a BD exitosa");
    
    // Obtener datos del usuario
    $query = "SELECT id, nombre, email, tipo as rol, foto, foto_url 
              FROM usuarios 
              WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Error al preparar la consulta: ' . $conn->error);
    }
    
    $userId = $_SESSION['user_id'];
    $stmt->bind_param('i', $userId);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    error_log("[GET_USER_DATA] Usuario encontrado: " . ($user ? "SI (ID: {$user['id']})" : "NO"));
    
    if ($user) {
        // Procesar la URL de la foto
        if (!empty($user['foto_url'])) {
            $user['foto'] = $user['foto_url'];
        } elseif (!empty($user['foto'])) {
            // Normalizar la ruta de la foto
            $fotoPath = $user['foto'];
            // Remover ../ del inicio
            $fotoPath = preg_replace('#^(\.\./)+#', '/', $fotoPath);
            // Asegurar que empiece con /
            if (strpos($fotoPath, '/') !== 0) {
                $fotoPath = '/' . $fotoPath;
            }
            $user['foto'] = $fotoPath;
        } else {
            // Foto por defecto
            $user['foto'] = '/invitrosoft/img/avatar/default.png';
        }
        
        error_log("✅ [GET_USER_DATA] Datos del usuario enviados correctamente");
        error_log("[GET_USER_DATA] Usuario: {$user['nombre']}, Rol: {$user['rol']}");
        
        echo json_encode([
            'success' => true,
            'id' => $user['id'],
            'nombre' => $user['nombre'],
            'email' => $user['email'],
            'tipo' => $user['rol'],
            'foto' => $user['foto']
        ]);
    } else {
        error_log("❌ [GET_USER_DATA] Usuario no encontrado en BD");
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Usuario no encontrado',
            'user_id' => $userId
        ]);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log('❌ [GET_USER_DATA] Error: ' . $e->getMessage());
    error_log('[GET_USER_DATA] Stack trace: ' . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error del sistema',
        'message' => $e->getMessage()
    ]);
}

error_log("=== [GET_USER_DATA] FIN ===");
?>