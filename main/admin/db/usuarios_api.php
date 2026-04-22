<?php
// db/usuarios_api.php - API para gestión de usuarios
session_start();
header('Content-Type: application/json; charset=utf-8');

// Verificar sesión y permisos de administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false, 
        'message' => 'Acceso no autorizado'
    ]);
    exit;
}

require_once 'conexion.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'getAllUsers':
            getAllUsers($conn);
            break;
        case 'updateUser':
            updateUser($conn);
            break;
        case 'toggleStatus':
            toggleStatus($conn);
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
 * Obtiene todos los usuarios del sistema
 */
function getAllUsers($conn) {
    $query = "SELECT 
                u.id,
                u.identidad,
                u.nombre,
                u.email,
                u.telefono,
                u.tipo,
                u.foto,
                u.foto_url,
                u.fecha_creacion,
                'active' as estado
              FROM usuarios u
              ORDER BY u.nombre ASC";
    
    $result = $conn->query($query);
    $users = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Construir la URL de la foto correctamente
            if (!empty($row['foto'])) {
                // Si la foto no tiene la ruta completa
                if (strpos($row['foto'], 'http') !== 0) {
                    $row['foto_url'] = '../../' . $row['foto'];
                }
            } else {
                $row['foto_url'] = '../../../img/user/default.png';
            }
            $users[] = $row;
        }
    }
    
    echo json_encode([
        'success' => true,
        'users' => $users
    ]);
}

/**
 * Actualiza la información de un usuario
 */
function updateUser($conn) {
    $id = intval($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $tipo = $_POST['tipo'];
    
    // Validaciones
    if (empty($nombre) || empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'Nombre y email son obligatorios'
        ]);
        return;
    }
    
    if (!in_array($tipo, ['admin', 'aprendiz', 'pasante'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Tipo de usuario no válido'
        ]);
        return;
    }
    
    // Verificar si el email ya existe en otro usuario
    $checkQuery = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param('si', $email, $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'El email ya está en uso por otro usuario'
        ]);
        $checkStmt->close();
        return;
    }
    $checkStmt->close();
    
    // Actualizar usuario
    $query = "UPDATE usuarios 
              SET nombre = ?,
                  email = ?,
                  telefono = ?,
                  tipo = ?,
                  fecha_actualizacion = CURRENT_TIMESTAMP
              WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssi', $nombre, $email, $telefono, $tipo, $id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Usuario actualizado correctamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar usuario: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
}

/**
 * Activa o desactiva un usuario (sin eliminar)
 */
function toggleStatus($conn) {
    $id = intval($_POST['id']);
    $newStatus = $_POST['status'];
    
    // Verificar que no sea el usuario actual
    if ($id == $_SESSION['user_id']) {
        echo json_encode([
            'success' => false,
            'message' => 'No puedes desactivar tu propia cuenta'
        ]);
        return;
    }
    
    // En lugar de usar un campo "estado", vamos a cambiar el tipo a "inactivo"
    // o agregar un prefijo al email para marcarlo como inactivo
    if ($newStatus === 'inactive') {
        // Marcar como inactivo agregando [INACTIVO] al nombre
        $query = "UPDATE usuarios 
                  SET nombre = CONCAT('[INACTIVO] ', REPLACE(nombre, '[INACTIVO] ', ''))
                  WHERE id = ? AND nombre NOT LIKE '[INACTIVO]%'";
    } else {
        // Reactivar quitando [INACTIVO] del nombre
        $query = "UPDATE usuarios 
                  SET nombre = REPLACE(nombre, '[INACTIVO] ', '')
                  WHERE id = ?";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Estado actualizado correctamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar estado: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
}

$conn->close();
?>