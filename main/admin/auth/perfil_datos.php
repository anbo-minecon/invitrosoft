<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db/conexion.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$user_id = intval($_SESSION['user_id']);

try {
    // 1. DATOS DEL USUARIO
    $stmt = $conn->prepare("
        SELECT 
            u.id, 
            u.nombre, 
            u.email, 
            u.telefono, 
            u.tipo as rol, 
            u.created_at as fecha_registro, 
            u.foto,
            u.identidad,
            u.tiempo_uso,
            u.ficha_formacion,
            p.nombre as genero_nombre
        FROM usuarios u
        LEFT JOIN parametros p ON u.genero = p.id_parametro
        WHERE u.id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    if (!$usuario) {
        throw new Exception('Usuario no encontrado');
    }

    // 2. ESTADÍSTICAS REALES
    $formulaciones = $conn->query("SELECT COUNT(*) as total FROM formulaciones")->fetch_assoc()['total'];
    $protocolos = $conn->query("SELECT COUNT(*) as total FROM protocolos")->fetch_assoc()['total'];
    $reactivos = $conn->query("SELECT COUNT(*) as total FROM reactivos")->fetch_assoc()['total'];
    
    // Plantas registradas por este usuario
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM plantas WHERE usuario_registro_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $plantas = $stmt->get_result()->fetch_assoc()['total'];

    // 3. ACTIVIDAD RECIENTE (últimas 5 acciones)
    $actividades = [];
    
    // Últimas formulaciones
    $res = $conn->query("
        SELECT 'Formulación creada' as accion, nombre as detalle, fecha_creacion as fecha 
        FROM formulaciones 
        ORDER BY id DESC LIMIT 2
    ");
    while ($row = $res->fetch_assoc()) {
        $actividades[] = $row;
    }
    
    // Últimos protocolos
    $res = $conn->query("
        SELECT 'Protocolo creado' as accion, nombre as detalle, fecha_creacion as fecha 
        FROM protocolos 
        ORDER BY id DESC LIMIT 2
    ");
    while ($row = $res->fetch_assoc()) {
        $actividades[] = $row;
    }
    
    // Últimos reactivos
    $res = $conn->query("
        SELECT 'Reactivo agregado' as accion, nombre_comun as detalle, NULL as fecha 
        FROM reactivos 
        ORDER BY id DESC LIMIT 1
    ");
    while ($row = $res->fetch_assoc()) {
        $actividades[] = $row;
    }

    // Ordenar por fecha
    usort($actividades, function($a, $b) {
        return strtotime($b['fecha'] ?? 'now') - strtotime($a['fecha'] ?? 'now');
    });
    $actividades = array_slice($actividades, 0, 5);

    // 4. RESPUESTA JSON
    echo json_encode([
        'success' => true,
        'usuario' => [
            'id' => $usuario['id'],
            'nombre' => $usuario['nombre'] ?? '',
            'email' => $usuario['email'] ?? '',
            'telefono' => $usuario['telefono'] ?? '',
            'identidad' => $usuario['identidad'] ?? '',
            'genero' => $usuario['genero_nombre'] ?? '',
            'rol' => ucfirst($usuario['rol'] ?? 'Usuario'),
            'fecha_registro' => $usuario['fecha_registro'] ?? '',
            'foto' => $usuario['foto'] ?? '',
            'tiempo_uso' => $usuario['tiempo_uso'] ?? '',
            'ficha_formacion' => $usuario['ficha_formacion'] ?? ''
        ],
        'stats' => [
            'formulaciones' => $formulaciones,
            'protocolos' => $protocolos,
            'reactivos' => $reactivos,
            'plantas' => $plantas
        ],
        'actividades' => $actividades
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}