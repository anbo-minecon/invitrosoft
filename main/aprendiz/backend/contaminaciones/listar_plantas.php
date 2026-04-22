<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../conexion.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

$rows = [];

$ordenar = isset($_GET['ordenar']) ? trim($_GET['ordenar']) : '';
$fase = isset($_GET['fase']) ? trim($_GET['fase']) : '';

// base con conteo de contaminaciones
$base = "SELECT p.id, p.codigo, p.nombre_comun, p.fase_actual, IFNULL(c.cnt,0) AS conta_count
         FROM plantas p
         LEFT JOIN (
           SELECT planta_id, COUNT(*) AS cnt
           FROM contaminaciones
           GROUP BY planta_id
         ) c ON c.planta_id = p.id";
$where = [];
$params = [];
$types = '';

if ($q !== '') {
    $where[] = "(p.codigo LIKE ? OR p.nombre_comun LIKE ?)";
    $like = "%$q%";
    $params[] = $like; $params[] = $like; $types .= 'ss';
}

// Siempre limitar por usuario logueado
if ($userId > 0) {
    $where[] = "p.usuario_registro_id = ?";
    $params[] = $userId; $types .= 'i';
} else {
    // Si no hay usuario, devolver vacío por seguridad
    echo json_encode([]);
    exit;
}

// Filtro por fase si aplica
$allowedFases = ['seleccion','establecimiento','multiplicacion','enraizamiento','adaptacion'];
if ($fase !== '' && in_array($fase, $allowedFases, true)) {
    $where[] = "LOWER(p.fase_actual) = ?";
    $params[] = $fase; $types .= 's';
}

$sql = $base;
if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
// Ordenamiento
switch ($ordenar) {
    case 'az':
        $order = 'p.nombre_comun ASC';
        break;
    case 'za':
        $order = 'p.nombre_comun DESC';
        break;
    case 'mas_conta':
        $order = 'conta_count DESC, p.id DESC';
        break;
    case 'menos_conta':
        $order = 'conta_count ASC, p.id DESC';
        break;
    case 'fase':
        $order = 'p.fase_actual ASC, p.id DESC';
        break;
    default:
        $order = 'p.id DESC';
}

$sql .= ' ORDER BY ' . $order . ' LIMIT 100';

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

echo json_encode($rows, JSON_UNESCAPED_UNICODE);
