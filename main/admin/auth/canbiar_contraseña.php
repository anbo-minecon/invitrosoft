<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '../db/conexion.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$input = json_decode(file_get_contents('php://input'), true);

try {
    $current = $input['current'] ?? '';
    $newPass = $input['new'] ?? '';
    $confirm = $input['confirm'] ?? '';
    
    // Validaciones
    if (empty($current) || empty($newPass) || empty($confirm)) {
        throw new Exception('Todos los campos son obligatorios');
    }
    
    if ($newPass !== $confirm) {
        throw new Exception('Las contraseñas no coinciden');
    }
    
    if (strlen($newPass) < 8) {
        throw new Exception('La contraseña debe tener al menos 8 caracteres');
    }
    
    // Verificar contraseña actual
    $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user || !password_verify($current, $user['password'])) {
        throw new Exception('La contraseña actual es incorrecta');
    }
    
    // Actualizar contraseña
    $hashedPassword = password_hash($newPass, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Contraseña actualizada correctamente'
        ]);
    } else {
        throw new Exception('Error al actualizar la contraseña');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>