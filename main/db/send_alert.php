<?php
require __DIR__ . '/../../includes/send_alert_email.php';
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['nombre']) || !isset($data['tipo'])) {
    echo json_encode(['success' => false, 'msg' => 'Datos incompletos']);
    exit;
}

// Config SMTP (usa tus credenciales correctas)
$smtp = [
    'host' => 'smtp.gmail.com',
    'username' => 'voceroadso2025@gmail.com',
    'password' => 'kkey pewd xjlj dgxc',
    'port' => 587,
    'from' => 'voceroadso2025@gmail.com',
    'to' => 'adaniesbasilio@gmail.com' // Cambia esto al correo del administrador
];

$product = [
    'id' => $data['id'] ?? 'N/A',
    'nombre' => $data['nombre'] ?? 'Sin nombre',
    'categoria' => $data['categoria'] ?? 'Sin categoría',
    'cantidad' => $data['cantidad'] ?? 0,
    'um' => $data['um'] ?? '',
    'fecha_vencimiento' => $data['fecha_vencimiento'] ?? ''
];

$tipo = $data['tipo'];
$valor = $data['valor'] ?? 0;

// 📧 Decide qué tipo de mensaje enviar
file_put_contents(__DIR__ . '/debug_log.txt', print_r($data, true));

if ($tipo === 'stock') {
    $threshold = $valor;
    $result = sendStockAlertEmail($smtp, $product, $threshold);
} elseif ($tipo === 'vencimiento') {
    $dias = (int)$valor;
    $result = sendVencimientoAlertEmail($smtp, $product, $dias);
} else {
    echo json_encode(['success' => false, 'msg' => 'Tipo de alerta no reconocido']);
    exit;
}

echo json_encode($result);
