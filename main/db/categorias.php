<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "conexion.php";
header("Content-Type: application/json");

// Solo tabla categorias
$tabla = 'categorias';

// GET: obtener todas las categorías
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $conn->query("SELECT id, nombre, descripcion FROM $tabla ORDER BY id ASC");
    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    echo json_encode($data);
    exit;
}

// POST: crear nueva categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input['nombre']) || trim($input['nombre']) === '') {
        http_response_code(400);
        echo json_encode(["error" => "El nombre es obligatorio"]);
        exit;
    }
    $nombre = $input['nombre'];
    $descripcion = $input['descripcion'] ?? '';
    $stmt = $conn->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)");
    $stmt->bind_param("ss", $nombre, $descripcion);
    $stmt->execute();
    echo json_encode(["id" => $conn->insert_id]);
    exit;
}

// PUT: actualizar categoría
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "ID inválido"]);
        exit;
    }
    $nombre = $input['nombre'] ?? '';
    $descripcion = $input['descripcion'] ?? '';
    $stmt = $conn->prepare("UPDATE categorias SET nombre=?, descripcion=? WHERE id=?");
    $stmt->bind_param("ssi", $nombre, $descripcion, $id);
    $stmt->execute();
    echo json_encode(["success" => true]);
    exit;
}

// DELETE: eliminar categoría
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "ID inválido"]);
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM categorias WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode(["success" => true]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Método no permitido"]);
exit;