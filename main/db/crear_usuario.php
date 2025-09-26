<?php
header('Content-Type: application/json');
require_once 'conexion.php';
$input = json_decode(file_get_contents('php://input'), true);
$campos = ['identidad','nombre','genero','telefono','email','password'];
foreach ($campos as $campo) {
    if (empty($input[$campo])) {
        http_response_code(400);
        echo json_encode(['error' => "El campo $campo es obligatorio"]);
        exit;
    }
}

$stmt = $conn->prepare('SELECT id FROM usuarios WHERE email=?');
$stmt->bind_param('s', $input['email']);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['error' => 'El email ya está registrado']);
    exit;
}

$passwordHash = password_hash($input['password'], PASSWORD_DEFAULT);

$tipo = $input['tipo'] ?? 'admin';

$stmt = $conn->prepare('INSERT INTO usuarios (identidad, nombre, genero, telefono, email, password, tipo) VALUES (?, ?, ?, ?, ?, ?, ?)');
$stmt->bind_param(
    'sisssss',
    $input['identidad'],
    $input['nombre'],
    intval($input['genero']),
    $input['telefono'],
    $input['email'],
    $passwordHash,
    $tipo
);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'id' => $conn->insert_id]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo registrar el usuario']);
}
?>