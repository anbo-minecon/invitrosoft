<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

require_once "../db/conexion.php";
header("Content-Type: application/json");

try {
    $userId = $_SESSION['user_id'];
    $stats = [];
    
    // 1. Contar formulaciones creadas
    $stmtForm = $conn->prepare("SELECT COUNT(*) as total FROM formulaciones");
    $stmtForm->execute();
    $resultForm = $stmtForm->get_result();
    $stats['formulaciones'] = $resultForm->fetch_assoc()['total'];
    
    // 2. Contar protocolos activos
    $stmtProt = $conn->prepare("SELECT COUNT(*) as total FROM protocolos");
    $stmtProt->execute();
    $resultProt = $stmtProt->get_result();
    $stats['protocolos'] = $resultProt->fetch_assoc()['total'];
    
    // 3. Contar reactivos registrados
    $stmtReact = $conn->prepare("SELECT COUNT(*) as total FROM reactivos");
    $stmtReact->execute();
    $resultReact = $stmtReact->get_result();
    $stats['reactivos'] = $resultReact->fetch_assoc()['total'];
    
    // 4. Calcular horas de actividad (basado en notificaciones o sesiones)
    // Opción 1: Si tienes una tabla de sesiones o logs
    // $stmtHoras = $conn->prepare("SELECT SUM(TIMESTAMPDIFF(HOUR, fecha_inicio, fecha_fin)) as total_horas FROM sesiones WHERE usuario_id = ?");
    
    // Opción 2: Estimar basado en número de acciones (cada acción = 0.1 horas)
    $stmtActividad = $conn->prepare("SELECT COUNT(*) as total_acciones FROM notificaciones WHERE usuario_id = ?");
    $stmtActividad->bind_param("i", $userId);
    $stmtActividad->execute();
    $resultActividad = $stmtActividad->get_result();
    $totalAcciones = $resultActividad->fetch_assoc()['total_acciones'];
    $stats['horas'] = round($totalAcciones * 0.1, 1); // Cada acción ≈ 6 minutos
    
    // Opción 3: Si no tienes registros, usar un valor por defecto
    if ($stats['horas'] == 0) {
        // Calcular días desde registro
        $stmtDias = $conn->prepare("SELECT DATEDIFF(NOW(), fecha_registro) as dias FROM usuarios WHERE id = ?");
        $stmtDias->bind_param("i", $userId);
        $stmtDias->execute();
        $resultDias = $stmtDias->get_result();
        $dias = $resultDias->fetch_assoc()['dias'];
        $stats['horas'] = max(1, $dias * 0.5); // Media hora por día
    }
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener estadísticas: ' . $e->getMessage()
    ]);
}
?>