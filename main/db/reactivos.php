<?php
require_once "conexion.php";
header("Content-Type: application/json");

$tabla = 'reactivos';
$imgDir = __DIR__ . '/../img/reactivos/';
if (!file_exists($imgDir)) mkdir($imgDir, 0777, true);

// GET: obtener todos los reactivos con sus categorías
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $conn->query("
        SELECT r.id, r.nombre_comun, r.formula_quimica, r.categoria_id, 
               r.unidad_medida, r.cantidad_total, r.fecha_vencimiento,
               r.imagen,
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

// POST: crear o actualizar reactivo (con imagen)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nombre_comun = $_POST['nombre_comun'] ?? '';
    $formula_quimica = $_POST['formula_quimica'] ?? '';
    $categoria_id = $_POST['categoria_id'] ?? null;
    $unidad_medida = $_POST['unidad_medida'] ?? '';
    $cantidad_total = $_POST['cantidad_total'] ?? 0;
    $fecha_vencimiento = $_POST['fecha_vencimiento'] ?? null;
    $imagen = null;

    // Manejo de imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $nombreArchivo = uniqid('reactivo_') . '.' . $ext;
        move_uploaded_file($_FILES['imagen']['tmp_name'], $imgDir . $nombreArchivo);
        $imagen = $nombreArchivo;
    }

    if ($id) {
        // Actualizar
        if ($imagen) {
            $stmt = $conn->prepare("UPDATE reactivos SET nombre_comun=?, formula_quimica=?, categoria_id=?, unidad_medida=?, cantidad_total=?, fecha_vencimiento=?, imagen=? WHERE id=?");
            $stmt->bind_param("ssissssi", $nombre_comun, $formula_quimica, $categoria_id, $unidad_medida, $cantidad_total, $fecha_vencimiento, $imagen, $id);
        } else {
            $stmt = $conn->prepare("UPDATE reactivos SET nombre_comun=?, formula_quimica=?, categoria_id=?, unidad_medida=?, cantidad_total=?, fecha_vencimiento=? WHERE id=?");
            $stmt->bind_param("ssisssi", $nombre_comun, $formula_quimica, $categoria_id, $unidad_medida, $cantidad_total, $fecha_vencimiento, $id);
        }
        $stmt->execute();
        echo json_encode(["success" => true]);
        exit;
    } else {
        // Crear
        $stmt = $conn->prepare("INSERT INTO reactivos (nombre_comun, formula_quimica, categoria_id, unidad_medida, cantidad_total, fecha_vencimiento, imagen) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssissss", $nombre_comun, $formula_quimica, $categoria_id, $unidad_medida, $cantidad_total, $fecha_vencimiento, $imagen);
        $stmt->execute();
        echo json_encode(["id" => $conn->insert_id]);
        exit;
    }
}

// DELETE: eliminar reactivo
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "ID inválido"]);
        exit;
    }
    // Eliminar imagen asociada
    $res = $conn->query("SELECT imagen FROM reactivos WHERE id=$id");
    if ($row = $res->fetch_assoc()) {
        if ($row['imagen'] && file_exists($imgDir . $row['imagen'])) {
            unlink($imgDir . $row['imagen']);
        }
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