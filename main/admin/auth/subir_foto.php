<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db/conexion.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$user_id = intval($_SESSION['user_id']);

if (!isset($_FILES['foto'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No se envió ninguna imagen']);
    exit;
}

$img = $_FILES['foto'];
$ext = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if (!in_array($ext, $allowed)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Formato de imagen no permitido']);
    exit;
}

// Ruta absoluta al directorio de imágenes
$dir = $_SERVER['DOCUMENT_ROOT'] . '/invitrosoft/img/user/';
if (!is_dir($dir)) mkdir($dir, 0777, true);

// 1. Buscar la imagen anterior
$stmt = $conn->prepare("SELECT foto FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($foto_anterior);
$stmt->fetch();
$stmt->close();

// 2. Eliminar la imagen anterior si existe
if ($foto_anterior) {
    $ruta_fisica = $_SERVER['DOCUMENT_ROOT'] . $foto_anterior;
    if (file_exists($ruta_fisica)) {
        @unlink($ruta_fisica);
    }
}

$filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
$destino = $dir . $filename;

if (move_uploaded_file($img['tmp_name'], $destino)) {
    // Guarda la ruta relativa en la BD
    $ruta = "/invitrosoft/img/user/$filename";
    $stmt = $conn->prepare("UPDATE usuarios SET foto = ? WHERE id = ?");
    $stmt->bind_param("si", $ruta, $user_id);
    $stmt->execute();
    echo json_encode(['success' => true, 'foto' => $ruta]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al guardar la imagen']);
}
?>