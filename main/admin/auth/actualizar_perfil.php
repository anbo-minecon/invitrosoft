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
    $nombre = trim($input['nombre'] ?? '');
    $apellido = trim($input['apellido'] ?? '');
    $email = trim($input['email'] ?? '');
    $telefono = trim($input['telefono'] ?? '');

    if (empty($nombre) || empty($apellido) || empty($email)) {
        throw new Exception('Nombre, apellido y email son obligatorios');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email inválido');
    }

    // Verificar si el email ya existe en otro usuario
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('El email ya está en uso');
    }

    // Actualizar usuario
    $stmt = $conn->prepare("
        UPDATE usuarios 
        SET nombre = ?, apellido = ?, email = ?, telefono = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ssssi", $nombre, $apellido, $email, $telefono, $user_id);

    if ($stmt->execute()) {
        $_SESSION['user_name'] = $nombre . ' ' . $apellido;
        $_SESSION['user_email'] = $email;
        echo json_encode([
            'success' => true,
            'message' => 'Perfil actualizado correctamente'
        ]);
    } else {
        throw new Exception('Error al actualizar el perfil');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
