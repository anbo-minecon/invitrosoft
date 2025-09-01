<?php
require_once "conexion.php";
header("Content-Type: application/json");

// Solo tabla reactivos
$tabla = 'reactivos';

// GET: obtener todos los reactivos con sus categorías
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $conn->query("
        SELECT r.id, r.nombre_comun, r.formula_quimica, r.categoria_id, 
               r.unidad_medida, r.cantidad_total, r.fecha_vencimiento,
               c.nombre as categoria_nombre
        FROM $tabla r 
        LEFT JOIN categorias c ON r.categoria_id = c.id 
        ORDER BY r.id ASC
    ");
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

// POST: crear nuevo reactivo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($input['nombre_comun']) || trim($input['nombre_comun']) === '') {
        http_response_code(400);
        echo json_encode(["error" => "El nombre común es obligatorio"]);
        exit;
    }
    
    $nombre_comun = $input['nombre_comun'];
    $formula_quimica = $input['formula_quimica'] ?? '';
    $categoria_id = $input['categoria_id'] ?? null;
    $unidad_medida = $input['unidad_medida'] ?? '';
    $cantidad_total = $input['cantidad_total'] ?? 0;
    $fecha_vencimiento = $input['fecha_vencimiento'] ?? null;
    
    $stmt = $conn->prepare("INSERT INTO reactivos (nombre_comun, formula_quimica, categoria_id, unidad_medida, cantidad_total, fecha_vencimiento) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisss", $nombre_comun, $formula_quimica, $categoria_id, $unidad_medida, $cantidad_total, $fecha_vencimiento);
    $stmt->execute();
    
    echo json_encode(["id" => $conn->insert_id]);
    exit;
}

// PUT: actualizar reactivo
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "ID inválido"]);
        exit;
    }
    
    $nombre_comun = $input['nombre_comun'] ?? '';
    $formula_quimica = $input['formula_quimica'] ?? '';
    $categoria_id = $input['categoria_id'] ?? null;
    $unidad_medida = $input['unidad_medida'] ?? '';
    $cantidad_total = $input['cantidad_total'] ?? 0;
    $fecha_vencimiento = $input['fecha_vencimiento'] ?? null;
    
    $stmt = $conn->prepare("UPDATE reactivos SET nombre_comun=?, formula_quimica=?, categoria_id=?, unidad_medida=?, cantidad_total=?, fecha_vencimiento=? WHERE id=?");
    $stmt->bind_param("ssisssi", $nombre_comun, $formula_quimica, $categoria_id, $unidad_medida, $cantidad_total, $fecha_vencimiento, $id);
    $stmt->execute();
    
    echo json_encode(["success" => true]);
    exit;
}

// DELETE: eliminar reactivo
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "ID inválido"]);
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM reactivos WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    echo json_encode(["success" => true]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Método no permitido"]);
exit;
?>