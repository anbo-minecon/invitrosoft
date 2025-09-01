<?php
header('Content-Type: application/json');
require_once 'conexion.php';

// Recibir datos JSON
$input = json_decode(file_get_contents('php://input'), true);

// Validar campos obligatorios
$campos = ['identidad','nombre','genero','telefono','email','password'];
foreach ($campos as $campo) {
    if (empty($input[$campo])) {
        http_response_code(400);
        echo json_encode(['error' => "El campo $campo es obligatorio"]);
        exit;
    }
}

// Validar email único
$stmt = $mysqli->prepare('SELECT id FROM usuarios WHERE email=?');
$stmt->bind_param('s', $input['email']);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['error' => 'El email ya está registrado']);
    exit;
}

// Hash de contraseña
$passwordHash = password_hash($input['password'], PASSWORD_DEFAULT);

// Campos adicionales
$tipo = $input['tipo'] ?? 'admin';
$tiempo_uso = $input['tiempo_uso'] ?? null;
$ficha_formacion = $input['ficha_formacion'] ?? null;

// Insertar usuario
$stmt = $mysqli->prepare('INSERT INTO usuarios (identidad, nombre, genero, telefono, email, password, tipo, tiempo_uso, ficha_formacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->bind_param('sssssssss',
    $input['identidad'],
    $input['nombre'],
    $input['genero'],
    $input['telefono'],
    $input['email'],
    $passwordHash,
    $tipo,
    $tiempo_uso,
    $ficha_formacion
);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'id' => $mysqli->insert_id]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo registrar el usuario']);
}
