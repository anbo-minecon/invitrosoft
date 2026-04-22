<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../conexion.php';

$userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
if ($userId <= 0) { echo json_encode(['success'=>false,'error'=>'No authenticated']); exit; }

// Datos de usuario
$sql = "SELECT id, identidad, nombre, genero, telefono, email, tipo, ficha_formacion, created_at, foto FROM usuarios WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
if (!$user) { echo json_encode(['success'=>false,'error'=>'User not found']); exit; }

// Normalize avatar path to public URL and ensure it exists
$docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', DIRECTORY_SEPARATOR);
$fotoPublic = $user['foto'] ?? '';
if (!$fotoPublic) {
  $fotoPublic = '/invitrosoft/img/user/default.png';
}
$fsPath = $docRoot . $fotoPublic;
if (!is_file($fsPath)) {
  $alt = $docRoot . '/invitrosoft/img/user/default.png';
  if (is_file($alt)) { $fotoPublic = '/invitrosoft/img/user/default.png'; }
}
$user['foto'] = $fotoPublic;

// Stats
$stats = [
  'totalPlantas' => 0,
  'fasesCompletadas' => 0,
  'proyectosActivos' => 0,
  'diasActivo' => 0
];

// total plantas
$q1 = $conn->prepare("SELECT COUNT(*) AS c FROM plantas WHERE usuario_registro_id = ?");
$q1->bind_param('i', $userId);
$q1->execute();
$stats['totalPlantas'] = (int)$q1->get_result()->fetch_assoc()['c'];

// proyectos activos: plantas sin fecha_fin
$q2 = $conn->prepare("SELECT COUNT(*) AS c FROM plantas WHERE usuario_registro_id = ? AND (fecha_fin IS NULL OR fecha_fin = '')");
$q2->bind_param('i', $userId);
$q2->execute();
$stats['proyectosActivos'] = (int)$q2->get_result()->fetch_assoc()['c'];

// fases completadas: contar filas con fecha_finalizacion no null en tablas de fases para plantas del usuario
$q3sql = "SELECT (
  SELECT COUNT(*) FROM fase_enraizamiento fe INNER JOIN plantas p ON p.id=fe.planta_id WHERE p.usuario_registro_id=? AND fe.fecha_finalizacion IS NOT NULL
) + (
  SELECT COUNT(*) FROM fase_multiplicacion fm INNER JOIN plantas p ON p.id=fm.planta_id WHERE p.usuario_registro_id=? AND fm.fecha_finalizacion IS NOT NULL
) + (
  SELECT COUNT(*) FROM fase_adaptacion fa INNER JOIN plantas p ON p.id=fa.planta_id WHERE p.usuario_registro_id=? AND fa.fecha_finalizacion IS NOT NULL
) AS c";
$q3 = $conn->prepare($q3sql);
$q3->bind_param('iii', $userId, $userId, $userId);
$q3->execute();
$stats['fasesCompletadas'] = (int)$q3->get_result()->fetch_assoc()['c'];

// días activo
if (!empty($user['created_at'])) {
  $created = new DateTime($user['created_at']);
  $now = new DateTime();
  $diff = $created->diff($now);
  $stats['diasActivo'] = (int)$diff->days;
}

// Actividad reciente: últimas 10 contaminaciones o plantas
$actividad = [];
$aSql = "(
  SELECT 'contaminacion' AS tipo, c.fecha_contaminacion AS fecha, CONCAT('Contaminación ', c.tipo, ' en ', p.nombre_comun) AS detalle
  FROM contaminaciones c INNER JOIN plantas p ON p.id = c.planta_id
  WHERE p.usuario_registro_id = ?
) UNION ALL (
  SELECT 'planta' AS tipo, p.fecha_creacion AS fecha, CONCAT('Registró planta ', p.nombre_comun) AS detalle
  FROM plantas p WHERE p.usuario_registro_id = ?
)
ORDER BY fecha DESC LIMIT 10";
$aq = $conn->prepare($aSql);
$aq->bind_param('ii', $userId, $userId);
$aq->execute();
$r = $aq->get_result();
while ($row = $r->fetch_assoc()) { $actividad[] = $row; }


echo json_encode([
  'success' => true,
  'user' => $user,
  'stats' => $stats,
  'actividad' => $actividad
], JSON_UNESCAPED_UNICODE);
