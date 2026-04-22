<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../conexion.php';

if (!isset($conn)) {
    echo json_encode(['success'=>false,'error'=>'Error de conexión a BD']);
    exit;
}

$userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
if ($userId <= 0) { 
    echo json_encode(['success'=>false,'error'=>'No autenticado']); 
    exit; 
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) { 
    echo json_encode(['success'=>false,'error'=>'Entrada inválida']); 
    exit; 
}

$nombre = trim($input['nombre'] ?? '');
$correo = trim($input['correo'] ?? '');
$telefono = trim($input['telefono'] ?? '');
$ficha = trim($input['ficha'] ?? '');

if ($nombre === '' || $correo === '') { 
    echo json_encode(['success'=>false,'error'=>'Nombre y correo son requeridos']); 
    exit; 
}

// validar email duplicado
$chk = $conn->prepare('SELECT id FROM usuarios WHERE email = ? AND id <> ? LIMIT 1');
if (!$chk) {
    echo json_encode(['success'=>false,'error'=>'Error en consulta: ' . $conn->error]);
    exit;
}
$chk->bind_param('si', $correo, $userId);
$chk->execute();
if ($chk->get_result()->fetch_assoc()) {
  echo json_encode(['success'=>false,'error'=>'El correo ya está en uso']);
  exit;
}

$sql = 'UPDATE usuarios SET nombre=?, email=?, telefono=?, ficha_formacion=? WHERE id=?';
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success'=>false,'error'=>'Error en preparación: ' . $conn->error]);
    exit;
}
$stmt->bind_param('ssssi', $nombre, $correo, $telefono, $ficha, $userId);
if ($stmt->execute()) {
  echo json_encode(['success'=>true]);
} else {
  echo json_encode(['success'=>false,'error'=>'No se pudo actualizar: ' . $stmt->error]);
}
$stmt->close();
