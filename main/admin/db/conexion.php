<?php
// config/database.php - Configuración de conexión a base de datos
// ubicacion: /main/aprendiz/backend/conexion.php

/**
 * Función para cargar variables de entorno desde .env
 * Compatible con formato Laravel/Symfony
 */
function loadEnvFile($path) {
    if (!file_exists($path)) {
        error_log("❌ [APRENDIZ] Archivo .env no encontrado en: " . $path);
        return false;
    }
    
    if (!is_readable($path)) {
        error_log("❌ [APRENDIZ] Archivo .env no tiene permisos de lectura: " . $path);
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        error_log("❌ [APRENDIZ] Error al leer el archivo .env");
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
    
    error_log("✅ [APRENDIZ] Archivo .env cargado. Variables: " . count($env));
    return $env;
}

// Intentar cargar .env
$envPath = __DIR__ . '/../../../.env';
error_log("=== [APRENDIZ] CARGANDO CONFIGURACIÓN ===");
error_log("[APRENDIZ] Directorio actual: " . __DIR__);
error_log("[APRENDIZ] Ruta del .env: " . $envPath);

$dotenv = loadEnvFile($envPath);

if ($dotenv === false || empty($dotenv)) {
    error_log("⚠️ [APRENDIZ] No se pudo cargar .env, usando configuración de respaldo");
    
    // Configuración de respaldo para InfinityFree
    define('DB_HOST', '');
    define('DB_USER', '');
    define('DB_PASS', '');
    define('DB_NAME', '');
} else {
    // Configuración desde .env
    define('DB_HOST', $dotenv['DB_HOST'] ?? '');
    define('DB_USER', $dotenv['DB_USER'] ?? '');
    define('DB_PASS', $dotenv['DB_PASSWORD'] ?? '');
    define('DB_NAME', $dotenv['DB_NAME']);
    
    error_log("✅ [APRENDIZ] Usando configuración desde .env");
}

// Log de intento de conexión (sin mostrar password completo)
error_log("=== [APRENDIZ] INTENTO DE CONEXIÓN ===");
error_log("[APRENDIZ] Host: " . DB_HOST);
error_log("[APRENDIZ] User: " . DB_USER);
error_log("[APRENDIZ] DB: " . DB_NAME);

// Crear conexión
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar conexión
if ($conn->connect_error) {
    error_log("❌ [APRENDIZ] ERROR de conexión a la base de datos");
    error_log("[APRENDIZ] Error: " . $conn->connect_error);
    error_log("[APRENDIZ] Código: " . $conn->connect_errno);
    
    die(json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos',
        'error' => $conn->connect_error,
        'error_code' => $conn->connect_errno
    ]));
}

// Configurar charset
if (!$conn->set_charset("utf8mb4")) {
    error_log("⚠️ [APRENDIZ] Error al configurar charset: " . $conn->error);
} else {
    error_log("✅ [APRENDIZ] Charset configurado: utf8mb4");
}

// Configurar zona horaria
date_default_timezone_set('America/Bogota');

// Log de conexión exitosa
error_log("✅ [APRENDIZ] Conexión exitosa a la base de datos");
error_log("===================================");
?>