<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../notificaciones_emit.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) { echo json_encode(['success'=>false,'error'=>'Entrada inválida']); exit; }

$planta_id = intval($input['planta_id'] ?? 0);
$fase_tipo = $input['fase_tipo'] ?? '';
$fase_id = intval($input['fase_id'] ?? 0);
$tipo = $input['tipo'] ?? '';
$cantidad = intval($input['cantidad'] ?? 0);
$motivo = trim($input['motivo'] ?? '');
$fecha = $input['fecha_contaminacion'] ?? '';

if ($planta_id<=0 || $fase_tipo==='' || $fase_id<=0 || ($tipo!=='endogena' && $tipo!=='exogena') || $fecha==='') {
    echo json_encode(['success'=>false,'error'=>'Datos incompletos']);
    exit;
}

$sql = "INSERT INTO contaminaciones (planta_id, fase_tipo, fase_id, tipo, cantidad, motivo, fecha_contaminacion) VALUES (?,?,?,?,?,?,?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('isissss', $planta_id, $fase_tipo, $fase_id, $tipo, $cantidad, $motivo, $fecha);
if ($stmt->execute()) {
    $contaId = $conn->insert_id;
    // Notificar al propietario de la planta
    notificar($conn, [
        'planta_id' => $planta_id,
        'tipo' => 'contaminacion',
        'titulo' => 'Contaminación registrada',
        'mensaje' => 'Se registró una contaminación (' . $tipo . ') en la fase ' . $fase_tipo,
        'contaminacion_id' => $contaId
    ]);
    // Notificar a rol admin
    notificar($conn, [
        'planta_id' => $planta_id,
        'tipo' => 'contaminacion',
        'titulo' => 'Contaminación registrada',
        'mensaje' => 'Se registró una contaminación (' . $tipo . ') en la fase ' . $fase_tipo,
        'contaminacion_id' => $contaId,
        'emit_role' => 'admin'
    ]);
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>'No se pudo registrar']);
}
