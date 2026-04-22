<?php
// db/historial_api.php - API para gestión del historial de actividades
require_once '../../includes/auth_check.php';
header('Content-Type: application/json; charset=utf-8');

// Verificar sesión y permisos de administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false, 
        'message' => 'Acceso no autorizado',
        'session_data' => [
            'user_id' => $_SESSION['user_id'] ?? null,
            'user_tipo' => $_SESSION['user_tipo'] ?? null
        ]
    ]);
    exit;
}

require_once 'conexion.php';

// Obtener la acción solicitada
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'getActivities':
            getActivities($conn);
            break;
        case 'getUsers':
            getUsers($conn);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error del servidor',
        'error' => $e->getMessage()
    ]);
}

/**
 * Obtiene las actividades del sistema con paginación y filtros
 */
function getActivities($conn) {
    // Parámetros de paginación
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $pageSize = isset($_GET['pageSize']) ? min(max(1, intval($_GET['pageSize'])), 50) : 15;
    $offset = ($page - 1) * $pageSize;
    
    // Obtener el ID del usuario actual
    $current_user_id = $_SESSION['user_id'];
    
    // Construir consulta con filtros
    $where = [];
    $params = [];
    $types = '';
    
    // Excluir las actividades del usuario actual
    $where[] = 'n.usuario_id != ?';
    $params[] = $current_user_id;
    $types .= 'i';
    
    // Filtro por tipo de notificación
    if (!empty($_GET['type'])) {
        $where[] = 'n.tipo = ?';
        $params[] = $_GET['type'];
        $types .= 's';
    }
    
    // Filtro por acción
    if (!empty($_GET['accion'])) {
        $where[] = 'n.accion = ?';
        $params[] = $_GET['accion'];
        $types .= 's';
    }
    
    // Filtro por módulo
    if (!empty($_GET['modulo'])) {
        $where[] = 'n.modulo = ?';
        $params[] = $_GET['modulo'];
        $types .= 's';
    }
    
    // Filtro por usuario
    if (!empty($_GET['user'])) {
        $where[] = 'n.usuario_id = ?';
        $params[] = intval($_GET['user']);
        $types .= 'i';
    }
    
    // Filtro por fecha
    if (!empty($_GET['date'])) {
        $where[] = 'DATE(n.fecha_creacion) = ?';
        $params[] = $_GET['date'];
        $types .= 's';
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Consulta para contar el total de registros
    $countQuery = "SELECT COUNT(*) as total FROM notificaciones n $whereClause";
    
    if (!empty($params)) {
        $countStmt = $conn->prepare($countQuery);
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $total = $countStmt->get_result()->fetch_assoc()['total'];
        $countStmt->close();
    } else {
        $result = $conn->query($countQuery);
        $total = $result->fetch_assoc()['total'];
    }
    
    // Consulta para obtener los datos
    $query = "SELECT 
                n.id,
                n.usuario_id,
                n.planta_id,
                n.modulo,
                n.accion,
                n.entidad,
                n.entidad_id,
                n.tipo,
                n.titulo,
                u.tipo as rol_usuario,
                n.mensaje,
                n.leida,
                n.fecha_creacion,
                n.fecha_lectura,
                n.datos_adicionales,
                n.fase_tipo,
                n.fase_id,
                n.contaminacion_id,
                u.nombre as usuario_nombre,
                u.email as usuario_email
              FROM notificaciones n
              LEFT JOIN usuarios u ON n.usuario_id = u.id
              $whereClause
              ORDER BY n.fecha_creacion DESC
              LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($query);
    
    // Añadir los parámetros de paginación
    $types .= 'ii';
    $params[] = $pageSize;
    $params[] = $offset;
    
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $activities = [];
    
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'activities' => $activities,
        'total' => intval($total),
        'page' => $page,
        'pageSize' => $pageSize,
        'totalPages' => ceil($total / $pageSize)
    ]);
}

/**
 * Obtiene la lista de usuarios para el filtro
 */
function getUsers($conn) {
    $query = "SELECT 
                u.id, 
                CONCAT(u.nombre, ' (', u.email, ')') as nombre 
              FROM usuarios u
              WHERE u.id IN (SELECT DISTINCT usuario_id FROM notificaciones WHERE usuario_id IS NOT NULL)
              ORDER BY u.nombre ASC";
    
    $result = $conn->query($query);
    $users = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    
    echo json_encode([
        'success' => true,
        'users' => $users
    ]);
}

$conn->close();
?>