<?php
session_start();
require_once __DIR__ . '/../main/admin/db/conexion.php'; // Ruta relativa correcta
header('Content-Type: application/json; charset=utf-8');

// Log para debugging (eliminar en producción)
error_log("=== LOGIN ATTEMPT ===");

$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

error_log("Email recibido: " . $email);

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Faltan datos']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
    if (!$stmt) {
        error_log("Error preparando consulta: " . $conn->error);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error en la consulta']);
        exit;
    }
    
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    error_log("Usuario encontrado: " . ($user ? "SI" : "NO"));
    
    if ($user) {
        error_log("Verificando password...");
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_tipo'] = $user['tipo'];
            
            error_log("Login exitoso para: " . $user['email']);
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'nombre' => $user['nombre'],
                    'email' => $user['email'],
                    'tipo' => $user['tipo']
                ]
            ]);
        } else {
            error_log("Password incorrecta");
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Contraseña incorrecta']);
        }
    } else {
        error_log("Usuario no encontrado");
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
    }
} catch (Exception $e) {
    error_log("Excepción: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error del servidor: ' . $e->getMessage()]);
}
?>