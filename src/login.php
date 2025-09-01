<?php
session_start();
require_once '../main/db/conexion.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';
$password = $input['contraseña'] ?? '';

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Email y contraseña son obligatorios']);
    exit;
}

$stmt = $conn->prepare('SELECT id, nombre, email, password, tipo FROM usuarios WHERE email=?');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['usuario'] = [
        'id' => $user['id'],
        'nombre' => $user['nombre'],
        'email' => $user['email'],
        'tipo' => $user['tipo']
    ];
    echo json_encode(['success' => true]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Credenciales incorrectas']);
}
