<?php
header('Content-Type: application/json');
require_once 'conexion.php';

$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

switch ($accion) {
    case 'listar':
        listar_parametros();
        break;
    case 'crear':
        crear_parametro();
        break;
    case 'editar':
        editar_parametro();
        break;
    case 'eliminar':
        eliminar_parametro();
        break;
    case 'tipos':
        listar_tipos();
        break;
    default:
        echo json_encode(['error' => 'Acción no válida']);
}

function listar_parametros() {
    global $conn;
    $tipo = $_GET['tipo'] ?? '';
    $sql = "SELECT p.id_parametro, p.nombre, p.descripcion, p.usuarios, t.nombre AS tipo 
            FROM parametros p 
            INNER JOIN tipo_parametro t ON p.id_tipo = t.id_tipo";
    if ($tipo) {
        $sql .= " WHERE t.nombre = '" . $conn->real_escape_string($tipo) . "'";
    }
    $sql .= " ORDER BY p.id_parametro DESC";
    $res = $conn->query($sql);
    $data = [];
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}

function crear_parametro() {
    global $conn;
    $input = json_decode(file_get_contents('php://input'), true);
    $id_tipo = $input['id_tipo'] ?? null;
    $nombre = $conn->real_escape_string($input['nombre'] ?? '');
    $descripcion = $conn->real_escape_string($input['descripcion'] ?? '');
    if (!$id_tipo || !$nombre) {
        echo json_encode(['error' => 'Faltan datos obligatorios']);
        return;
    }
    $sql = "INSERT INTO parametros (id_tipo, nombre, descripcion) VALUES ($id_tipo, '$nombre', '$descripcion')";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Error al crear']);
    }
}

function editar_parametro() {
    global $conn;
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id_parametro'] ?? null;
    $nombre = $conn->real_escape_string($input['nombre'] ?? '');
    $descripcion = $conn->real_escape_string($input['descripcion'] ?? '');
    if (!$id || !$nombre) {
        echo json_encode(['error' => 'Faltan datos obligatorios']);
        return;
    }
    $sql = "UPDATE parametros SET nombre='$nombre', descripcion='$descripcion' WHERE id_parametro=$id";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Error al editar']);
    }
}

function eliminar_parametro() {
    global $conn;
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id_parametro'] ?? null;
    if (!$id) {
        echo json_encode(['error' => 'ID no proporcionado']);
        return;
    }
    $sql = "DELETE FROM parametros WHERE id_parametro=$id";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Error al eliminar']);
    }
}

function listar_tipos() {
    global $conn;
    $res = $conn->query("SELECT * FROM tipo_parametro");
    $data = [];
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}
