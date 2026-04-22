<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../conexion.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$ordenar = isset($_GET['ordenar']) ? trim($_GET['ordenar']) : '';
$fase = isset($_GET['fase']) ? trim($_GET['fase']) : '';
$userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

if ($userId <= 0) { echo json_encode([]); exit; }

$allowedFases = ['seleccion','establecimiento','multiplicacion','enraizamiento','adaptacion'];

$sql = "SELECT c.id,
               c.planta_id,
               p.codigo,
               p.nombre_comun,
               p.fase_actual,
               c.fase_tipo,
               c.fase_id,
               c.tipo,
               c.cantidad,
               c.motivo,
               c.fecha_contaminacion
        FROM contaminaciones c
        INNER JOIN plantas p ON p.id = c.planta_id
        WHERE p.usuario_registro_id = ?";
$params = [$userId];
$types = 'i';

if ($q !== '') {
    $sql .= " AND (p.codigo LIKE ? OR p.nombre_comun LIKE ? OR c.motivo LIKE ?)";
    $like = "%$q%";
    $params[] = $like; $params[] = $like; $params[] = $like; $types .= 'sss';
}

if ($fase !== '' && in_array(strtolower($fase), $allowedFases, true)) {
    $sql .= " AND LOWER(c.fase_tipo) = ?";
    $params[] = strtolower($fase); $types .= 's';
}

switch ($ordenar) {
    case 'az':
        $order = 'p.nombre_comun ASC, c.fecha_contaminacion DESC';
        break;
    case 'za':
        $order = 'p.nombre_comun DESC, c.fecha_contaminacion DESC';
        break;
    case 'fase':
        $order = 'c.fase_tipo ASC, c.fecha_contaminacion DESC';
        break;
    default:
        $order = 'c.fecha_contaminacion DESC, c.id DESC';
}
$sql .= " ORDER BY $order LIMIT 200";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while ($row = $res->fetch_assoc()) { $out[] = $row; }

echo json_encode($out, JSON_UNESCAPED_UNICODE);
