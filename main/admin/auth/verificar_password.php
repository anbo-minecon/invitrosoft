<?php
session_start();
require_once '../db/conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$password = $data['password'] ?? '';

if (empty($password)) {
    echo json_encode(['success' => false, 'error' => 'La contraseña es requerida']);
    exit;
}

try {
    // Intentar primero con 'password' (campo usado en cambiar_contraseña.php)
    $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Contraseña incorrecta']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    error_log("Error al verificar contraseña: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error al procesar la solicitud']);
}
?>