<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once __DIR__ . '/../conexion.php';

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
        $current_password = $input['currentPassword'] ?? '';
        $new_password = $input['newPassword'] ?? '';
        $confirm_password = $input['confirmPassword'] ?? '';

        // Validar campos requeridos
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            throw new Exception('Todos los campos son obligatorios');
        }

        // Verificar que las contraseñas coincidan
        if ($new_password !== $confirm_password) {
            throw new Exception('Las contraseñas no coinciden');
        }

        // Verificar longitud mínima de la contraseña
        if (strlen($new_password) < 8) {
            throw new Exception('La contraseña debe tener al menos 8 caracteres');
        }

        // Obtener la contraseña actual del usuario
        $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = ?");
        if ($stmt === false) {
            throw new Exception('Error en la preparación de la consulta: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            throw new Exception('Error al verificar la contraseña actual: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $stmt->close();
            throw new Exception('Usuario no encontrado');
        }
        
        $user = $result->fetch_assoc();
        $stored_password = $user['password'];
        
        // Verificar la contraseña actual
        if (!password_verify($current_password, $stored_password)) {
            $stmt->close();
            throw new Exception('La contraseña actual es incorrecta');
        }
        
        $stmt->close();

        // Actualizar la contraseña
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $query = "UPDATE usuarios SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        
        if ($stmt === false) {
            throw new Exception('Error en la preparación de la consulta: ' . $conn->error);
        }
        
        $stmt->bind_param('si', $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            echo json_encode([
                'success' => true,
                'message' => 'Contraseña actualizada correctamente'
            ]);
            exit;
        } else {
            $stmt->close();
            throw new Exception('Error al actualizar la contraseña: ' . $stmt->error);
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'error' => $e->getMessage()
        ]);
        exit;
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}
?>
