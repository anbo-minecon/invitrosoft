<?php
// config/database.php - Configuración de conexión a base de datos
// ubicacion: /main/welcome/api/conexion.php

/**
 * Función para cargar variables de entorno desde .env
 * Compatible con formato Laravel/Symfony
 */
function loadEnvFile($path) {
    if (!file_exists($path)) {
        error_log("❌ [WELCOME] Archivo .env no encontrado en: " . $path);
        return false;
    }
    
    if (!is_readable($path)) {
        error_log("❌ [WELCOME] Archivo .env no tiene permisos de lectura: " . $path);
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        error_log("❌ [WELCOME] Error al leer el archivo .env");
        return false;
    }
    
    $env = [];
    
    foreach ($lines as $line) {
        // Ignorar comentarios
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        // Parsear línea KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remover comillas si existen
            if (preg_match('/^(["\'])(.*)\1$/', $value, $matches)) {
                $value = $matches[2];
            }
            
            $env[$key] = $value;
        }
    }
    
    error_log("✅ [WELCOME] Archivo .env cargado. Variables: " . count($env));
    return $env;
}

// Intentar cargar .env
$envPath = __DIR__ . '/../../../../.env';
error_log("=== [WELCOME] CARGANDO CONFIGURACIÓN ===");
error_log("[WELCOME] Directorio actual: " . __DIR__);
error_log("[WELCOME] Ruta del .env: " . $envPath);

$dotenv = loadEnvFile($envPath);

if ($dotenv === false || empty($dotenv)) {
    error_log("⚠️ [WELCOME] No se pudo cargar .env, usando configuración de respaldo");
    
    // Configuración de respaldo para InfinityFree
    define('DB_HOST', 'sql109.infinityfree.com');
    define('DB_USER', 'if0_40400375');
    define('DB_PASS', '3154945917');
    define('DB_NAME', 'if0_40400375_invitrosoft');
} else {
    // Configuración desde .env
    define('DB_HOST', $dotenv['DB_HOST'] ?? 'sql109.infinityfree.com');
    define('DB_USER', $dotenv['DB_USER'] ?? 'if0_40400375');
    define('DB_PASS', $dotenv['DB_PASSWORD'] ?? '3154945917');
    define('DB_NAME', $dotenv['DB_NAME'] ?? 'if0_40400375_invitrosoft');
    
    error_log("✅ [WELCOME] Usando configuración desde .env");
}

// Log de intento de conexión (sin mostrar password completo)
error_log("=== [WELCOME] INTENTO DE CONEXIÓN ===");
error_log("[WELCOME] Host: " . DB_HOST);
error_log("[WELCOME] User: " . DB_USER);
error_log("[WELCOME] DB: " . DB_NAME);

// Crear conexión
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar conexión
if ($conn->connect_error) {
    error_log("❌ [WELCOME] ERROR de conexión a la base de datos");
    error_log("[WELCOME] Error: " . $conn->connect_error);
    error_log("[WELCOME] Código: " . $conn->connect_errno);
    
    header('Content-Type: application/json');
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Error al conectar con la base de datos',
        'error' => $conn->connect_error,
        'error_code' => $conn->connect_errno
    ]));
}

// Configurar charset
if (!$conn->set_charset('utf8mb4')) {
    error_log("⚠️ [WELCOME] Error al configurar charset: " . $conn->error);
} else {
    error_log("✅ [WELCOME] Charset configurado: utf8mb4");
}

// Configurar zona horaria
date_default_timezone_set('America/Bogota');

// Log de conexión exitosa
error_log("✅ [WELCOME] Conexión exitosa a la base de datos");
error_log("===================================");
?>