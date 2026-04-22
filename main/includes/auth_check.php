<?php
// ubicacion: /invitrosoft/main/includes/auth_check.php

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_log("=== [AUTH_CHECK] VERIFICACIÓN ===");
error_log("[AUTH_CHECK] Session ID: " . session_id());
error_log("[AUTH_CHECK] User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NO DEFINIDO'));
error_log("[AUTH_CHECK] Request URI: " . $_SERVER['REQUEST_URI']);

if (!isset($_SESSION['user_id'])) {
    error_log("❌ [AUTH_CHECK] Usuario no autenticado, redirigiendo a login");
    
    // Guardar la URL actual para redirección después del login
    $current_url = $_SERVER['REQUEST_URI'];
    $_SESSION['redirect_url'] = $current_url;
    
    error_log("[AUTH_CHECK] URL de redirección guardada: " . $current_url);
    
    // Determinar si es una petición AJAX
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
               && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    if ($is_ajax) {
        // Para peticiones AJAX, devolver JSON
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'No autorizado',
            'message' => 'Sesión expirada o no iniciada',
            'redirect' => '/invitrosoft/src/index.html'
        ]);
        exit;
    } else {
        // Para peticiones normales, redirigir
        header('Location: /invitrosoft/src/index.html');
        exit;
    }
}

// Log de usuario autenticado
error_log("✅ [AUTH_CHECK] Usuario autenticado correctamente");
error_log("[AUTH_CHECK] Nombre: " . (isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'N/A'));
error_log("[AUTH_CHECK] Tipo: " . (isset($_SESSION['user_tipo']) ? $_SESSION['user_tipo'] : 'N/A'));

// Opcional: Verificar tiempo de inactividad (30 minutos)
$inactive_timeout = 1800; // 30 minutos en segundos

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive_timeout)) {
    error_log("⚠️ [AUTH_CHECK] Sesión expirada por inactividad");
    
    // Destruir sesión
    session_unset();
    session_destroy();
    
    // Iniciar nueva sesión para mensaje
    session_start();
    $_SESSION['login_message'] = 'Tu sesión ha expirado por inactividad. Por favor, inicia sesión nuevamente.';
    
    // Redirigir a login
    header('Location: /invitrosoft/src/index.html');
    exit;
}

// Actualizar tiempo de última actividad
$_SESSION['last_activity'] = time();

// Opcional: Verificar rol de usuario para acceso a módulos específicos
// Esta función puede ser llamada desde otros archivos
function checkUserRole($required_roles = []) {
    if (empty($required_roles)) {
        return true; // No hay restricción de roles
    }
    
    $user_role = isset($_SESSION['user_tipo']) ? strtolower($_SESSION['user_tipo']) : '';
    
    // Normalizar roles requeridos
    $required_roles = array_map('strtolower', $required_roles);
    
    if (!in_array($user_role, $required_roles)) {
        error_log("❌ [AUTH_CHECK] Acceso denegado. Rol requerido: " . implode(', ', $required_roles) . " | Rol actual: " . $user_role);
        return false;
    }
    
    error_log("✅ [AUTH_CHECK] Acceso permitido para rol: " . $user_role);
    return true;
}
?>