<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../conexion.php';

$plantaId = isset($_GET['planta_id']) ? intval($_GET['planta_id']) : 0;
if ($plantaId <= 0) { echo json_encode([]); exit; }

$fases = [];

// Establecimiento
$sql = "SELECT id, 'establecimiento' AS fase_tipo, NULL AS nombre FROM fase_establecimiento WHERE planta_id = ? ORDER BY id DESC";
if ($stmt = $conn->prepare($sql)) {
  $stmt->bind_param('i', $plantaId);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $fases[] = ['fase_id' => (int)$row['id'], 'fase_tipo' => $row['fase_tipo'], 'nombre' => $row['nombre']];
  }
  $stmt->close();
}

// Multiplicacion
$sql = "SELECT id, 'multiplicacion' AS fase_tipo, NULL AS nombre FROM fase_multiplicacion WHERE planta_id = ? ORDER BY id DESC";
if ($stmt = $conn->prepare($sql)) {
  $stmt->bind_param('i', $plantaId);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $fases[] = ['fase_id' => (int)$row['id'], 'fase_tipo' => $row['fase_tipo'], 'nombre' => $row['nombre']];
  }
  $stmt->close();
}

// Enraizamiento
$sql = "SELECT id, 'enraizamiento' AS fase_tipo, NULL AS nombre FROM fase_enraizamiento WHERE planta_id = ? ORDER BY id DESC";
if ($stmt = $conn->prepare($sql)) {
  $stmt->bind_param('i', $plantaId);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $fases[] = ['fase_id' => (int)$row['id'], 'fase_tipo' => $row['fase_tipo'], 'nombre' => $row['nombre']];
  }
  $stmt->close();
}

// Adaptacion
$sql = "SELECT id, 'adaptacion' AS fase_tipo, NULL AS nombre FROM fase_adaptacion WHERE planta_id = ? ORDER BY id DESC";
if ($stmt = $conn->prepare($sql)) {
  $stmt->bind_param('i', $plantaId);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) {
    $fases[] = ['fase_id' => (int)$row['id'], 'fase_tipo' => $row['fase_tipo'], 'nombre' => $row['nombre']];
  }
  $stmt->close();
}

echo json_encode($fases, JSON_UNESCAPED_UNICODE);
