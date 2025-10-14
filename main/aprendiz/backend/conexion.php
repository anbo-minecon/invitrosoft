<?php
// config/database.php - Configuración de conexión a base de datos

// Configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'invitrosoft');

// Crear conexión
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar conexión
if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Error de conexión: ' . $conn->connect_error
    ]));
}

// Configurar charset
$conn->set_charset("utf8mb4");

// Configurar zona horaria
date_default_timezone_set('America/Bogota');

?>