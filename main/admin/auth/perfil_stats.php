<?php
session_start();
header('Content-Type: application/json');
require_once '../db/conexion.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

// DATOS PERSONALES
$sql = "SELECT nombre, apellido, email, telefono, '' AS bio, tipo AS rol, NOW() AS fecha_registro FROM usuarios WHERE id = $user_id";
$res = $conn->query($sql);
$usuario = $res && $res->num_rows ? $res->fetch_assoc() : null;

// STATS
$formulaciones = $conn->query("SELECT COUNT(*) AS total FROM formulaciones")->fetch_assoc()['total'] ?? 0;
$protocolos = $conn->query("SELECT COUNT(*) AS total FROM protocolos")->fetch_assoc()['total'] ?? 0;
$reactivos = $conn->query("SELECT COUNT(*) AS total FROM reactivos")->fetch_assoc()['total'] ?? 0;
// Horas de actividad: usa el campo tiempo_uso de usuarios si no tienes tabla de actividad
$horas = $conn->query("SELECT tiempo_uso FROM usuarios WHERE id = $user_id")->fetch_assoc()['tiempo_uso'] ?? 0;

echo json_encode([
    'success' => true,
    'usuario' => $usuario,
    'stats' => [
        'formulaciones' => $formulaciones,
        'protocolos' => $protocolos,
        'reactivos' => $reactivos,
        'horas' => $horas
    ]
]);
?>