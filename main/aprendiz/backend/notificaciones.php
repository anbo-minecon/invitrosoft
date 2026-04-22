<?php
/**
 * API optimizada de Notificaciones para aprendices
 * Solo muestra notificaciones creadas por administradores
 * Ubicación: /main/aprendiz/backend/notificaciones.php
 */

session_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// GET: Listar notificaciones y administradores
if ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';
    
    // Acción: Obtener lista de administradores que han creado notificaciones
    if ($action === 'get_admins') {
        $sql = "
            SELECT DISTINCT 
                u.id,
                u.nombre,
                u.email,
                COUNT(n.id) as total_notificaciones
            FROM notificaciones n
            INNER JOIN usuarios u ON u.id = (
                SELECT id FROM usuarios WHERE tipo = 'admin' ORDER BY id LIMIT 1
            )
            WHERE n.usuario_id = $user_id
            GROUP BY u.id, u.nombre, u.email
            ORDER BY u.nombre ASC
        ";
        
        $result = $conn->query($sql);
        $admins = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $admins[] = [
                    'id' => (int)$row['id'],
                    'nombre' => $row['nombre'],
                    'email' => $row['email'],
                    'total_notificaciones' => (int)$row['total_notificaciones']
                ];
            }
        }
        
        echo json_encode([
            'success' => true,
            'admins' => $admins
        ]);
        exit;
    }
    
    // Acción: Listar notificaciones
    $filter = $_GET['filter'] ?? 'todas';
    $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 50;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $admin_id = isset($_GET['admin_id']) ? (int)$_GET['admin_id'] : 0;

    // Query base - SOLO notificaciones donde existe un admin en la tabla usuarios
    // Y que pertenecen al usuario actual
    $where = "WHERE n.usuario_id = $user_id 
              AND EXISTS (
                  SELECT 1 FROM usuarios u 
                  WHERE u.tipo = 'admin' 
                  LIMIT 1
              )";
    
    // Aplicar filtro de leídas/no leídas
    if ($filter === 'leidas') {
        $where .= " AND n.leida = 1";
    } elseif ($filter === 'no_leidas') {
        $where .= " AND n.leida = 0";
    }
    
    // Filtrar por administrador específico (futuro: cuando se guarde quién creó la notificación)
    // Por ahora, todas las notificaciones se asumen creadas por un admin genérico
    
    // Contar total
    $countSql = "SELECT COUNT(*) as total FROM notificaciones n $where";
    $countResult = $conn->query($countSql);
    $total = $countResult ? (int)$countResult->fetch_assoc()['total'] : 0;

    // Obtener notificaciones con información del admin
    $sql = "
        SELECT 
            n.id,
            n.usuario_id,
            n.planta_id,
            n.modulo,
            n.accion,
            n.tipo,
            n.titulo,
            n.mensaje,
            n.leida,
            n.fecha_creacion,
            n.fecha_lectura,
            (SELECT u.id FROM usuarios u WHERE u.tipo = 'admin' ORDER BY u.id LIMIT 1) as admin_id,
            (SELECT u.nombre FROM usuarios u WHERE u.tipo = 'admin' ORDER BY u.id LIMIT 1) as admin_nombre,
            (SELECT u.email FROM usuarios u WHERE u.tipo = 'admin' ORDER BY u.id LIMIT 1) as admin_email
        FROM notificaciones n
        $where
        ORDER BY n.fecha_creacion DESC
        LIMIT $limit OFFSET $offset
    ";

    $result = $conn->query($sql);
    if (!$result) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $conn->error]);
        exit;
    }

    $notificaciones = [];
    while ($row = $result->fetch_assoc()) {
        $notificaciones[] = [
            'id' => (int)$row['id'],
            'planta_id' => $row['planta_id'] ? (int)$row['planta_id'] : null,
            'modulo' => $row['modulo'],
            'accion' => $row['accion'],
            'tipo' => $row['tipo'] ?: 'info',
            'titulo' => $row['titulo'],
            'mensaje' => $row['mensaje'],
            'leida' => (bool)$row['leida'],
            'fecha_creacion' => $row['fecha_creacion'],
            'fecha_lectura' => $row['fecha_lectura'],
            'admin_id' => $row['admin_id'] ? (int)$row['admin_id'] : null,
            'admin_nombre' => $row['admin_nombre'] ?: 'Administrador',
            'admin_email' => $row['admin_email']
        ];
    }

    echo json_encode([
        'success' => true,
        'notificaciones' => $notificaciones,
        'total' => $total,
        'limit' => $limit,
        'offset' => $offset
    ]);
    exit;
}

// PUT: Marcar notificación como leída
if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $notif_id = isset($input['id']) ? (int)$input['id'] : 0;

    if (!$notif_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID de notificación requerido']);
        exit;
    }

    // Verificar que la notificación pertenezca al usuario
    $check = $conn->query("SELECT usuario_id FROM notificaciones WHERE id = $notif_id");
    if (!$check || $check->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Notificación no encontrada']);
        exit;
    }

    $row = $check->fetch_assoc();
    if ((int)$row['usuario_id'] !== $user_id) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'No tienes permiso']);
        exit;
    }

    $update = $conn->query("
        UPDATE notificaciones 
        SET leida = 1, fecha_lectura = NOW() 
        WHERE id = $notif_id
    ");

    if ($update) {
        echo json_encode(['success' => true, 'message' => 'Notificación marcada como leída']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit;
}

// POST: Marcar todas como leídas
if ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'marcar_todas_leidas') {
        $update = $conn->query("
            UPDATE notificaciones 
            SET leida = 1, fecha_lectura = NOW() 
            WHERE usuario_id = $user_id 
            AND leida = 0
            AND EXISTS (
                SELECT 1 FROM usuarios u 
                WHERE u.tipo = 'admin' 
                LIMIT 1
            )
        ");

        if ($update) {
            $affected = $conn->affected_rows;
            echo json_encode([
                'success' => true, 
                'message' => 'Todas las notificaciones marcadas como leídas',
                'affected' => $affected
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Acción no válida']);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Método no permitido']);
?>