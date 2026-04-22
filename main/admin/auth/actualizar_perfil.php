<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once '../db/conexion.php';


session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar conexión a la base de datos
if (!isset($conn) || $conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Obtener datos del cuerpo de la petición
$input = json_decode(file_get_contents('php://input'), true);

// Verificar si se recibieron datos JSON válidos
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Datos JSON inválidos',
        'error' => json_last_error_msg()
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nombre = trim($input['nombre'] ?? '');
        $email = trim($input['email'] ?? '');
        $telefono = trim($input['telefono'] ?? '');
        $bibliografia = trim($input['bibliografia'] ?? '');

        if (empty($nombre) || empty($email)) {
            throw new Exception('Nombre y email son obligatorios');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email inválido');
        }

        // Verificar si el email ya existe en otro usuario
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        if ($stmt === false) {
            throw new Exception('Error en la preparación de la consulta: ' . $conn->error);
        }
        
        $stmt->bind_param("si", $email, $user_id);
        if (!$stmt->execute()) {
            throw new Exception('Error al verificar el email: ' . $stmt->error);
        }
        
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception('El email ya está en uso');
        }
        $stmt->close();

        // Actualizar usuario
        $query = "UPDATE usuarios SET nombre = ?, email = ?, telefono = ?, bibliografia = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        
        if ($stmt === false) {
            throw new Exception('Error en la preparación de la consulta: ' . $conn->error);
        }
        
        // Usar variables con nombres diferentes para evitar conflictos
        $param_nombre = $nombre;
        $param_email = $email;
        $param_telefono = $telefono;
        $param_bibliografia = $bibliografia;
        $param_id = $user_id;
        
        $stmt->bind_param('ssssi', $param_nombre, $param_email, $param_telefono, $param_bibliografia, $param_id);
        
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $nombre;
            $_SESSION['user_email'] = $email;
            echo json_encode([
                'success' => true,
                'message' => 'Perfil actualizado correctamente',
                'data' => [
                    'nombre' => $nombre,
                    'email' => $email,
                    'telefono' => $telefono,
                    'bibliografia' => $bibliografia
                ]
            ]);
        } else {
            throw new Exception('Error al actualizar el perfil: ' . $stmt->error);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar el perfil',
            'error' => $e->getMessage()
        ]);
    }
} // Cierre del if ($_SERVER['REQUEST_METHOD'] === 'POST')
?>
