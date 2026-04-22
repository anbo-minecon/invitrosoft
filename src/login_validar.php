<?php
session_start();

// Configuración de errores para debugging
error_reporting(E_ALL);
ini_set('log_errors', 1);

error_log("=== INICIO LOGIN_VALIDAR.PHP ===");
error_log("Directorio actual: " . __DIR__);
error_log("Directorio padre: " . dirname(__DIR__));

// Ruta correcta desde /invitrosoft/src/ hacia /invitrosoft/main/admin/db/conexion.php
// __DIR__ = /home/vol10_4/infinityfree.com/if0_40400375/htdocs/invitrosoft/src
// Necesitamos subir un nivel (..) y luego ir a main/admin/db/conexion.php
$conexionPath = __DIR__ . '/../main/admin/db/conexion.php';

error_log("Ruta a conexion.php: " . $conexionPath);
error_log("¿Archivo existe?: " . (file_exists($conexionPath) ? 'SI' : 'NO'));

// Verificar si el archivo existe
if (!file_exists($conexionPath)) {
    error_log("❌ ERROR: No se encontró conexion.php en: " . $conexionPath);
    
    // Intentar ruta alternativa (por si acaso)
    $conexionPathAlt = dirname(__DIR__) . '/main/admin/db/conexion.php';
    error_log("Intentando ruta alternativa: " . $conexionPathAlt);
    
    if (file_exists($conexionPathAlt)) {
        $conexionPath = $conexionPathAlt;
        error_log("✅ Archivo encontrado en ruta alternativa");
    } else {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        die(json_encode([
            'success' => false,
            'error' => 'Error de configuración del servidor',
            'details' => 'No se pudo cargar la configuración de la base de datos',
            'debug' => [
                'directorio_actual' => __DIR__,
                'ruta_intentada' => $conexionPath,
                'ruta_alternativa' => $conexionPathAlt
            ]
        ]));
    }
}

require_once $conexionPath;
header('Content-Type: application/json; charset=utf-8');

error_log("=== PROCESANDO LOGIN ===");

// Leer input JSON
$input = json_decode(file_get_contents('php://input'), true);
$email = isset($input['email']) ? trim($input['email']) : '';
$password = isset($input['password']) ? $input['password'] : '';

error_log("Email recibido: " . $email);
error_log("Password recibido: " . (empty($password) ? 'VACÍO' : 'OK (' . strlen($password) . ' caracteres)'));

// Validar datos
if (empty($email) || empty($password)) {
    error_log("❌ Faltan datos en el request");
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Faltan datos de email o contraseña']);
    exit;
}

// Verificar que la conexión existe
if (!isset($conn) || !($conn instanceof mysqli)) {
    error_log("❌ ERROR: La variable \$conn no está definida o no es válida");
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Error en la conexión a la base de datos',
        'details' => 'La variable de conexión no está disponible'
    ]);
    exit;
}

try {
    // Preparar consulta
    $stmt = $conn->prepare("SELECT id, nombre, email, password, tipo FROM usuarios WHERE email = ?");
    if (!$stmt) {
        error_log("❌ Error preparando consulta: " . $conn->error);
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'error' => 'Error en la consulta a la base de datos',
            'details' => $conn->error
        ]);
        exit;
    }
    
    $stmt->bind_param('s', $email);
    
    if (!$stmt->execute()) {
        error_log("❌ Error ejecutando consulta: " . $stmt->error);
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'error' => 'Error al ejecutar la consulta'
        ]);
        exit;
    }
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    error_log("Usuario encontrado en BD: " . ($user ? "✅ SI (ID: " . $user['id'] . ")" : "❌ NO"));
    
    if ($user) {
        error_log("Verificando password...");
        error_log("Hash en BD: " . substr($user['password'], 0, 20) . "...");
        
        // Verificar contraseña
        if (password_verify($password, $user['password'])) {
            // Crear sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_tipo'] = $user['tipo'];
            
            error_log("✅ Login exitoso para: " . $user['email'] . " (Tipo: " . $user['tipo'] . ")");
            
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'nombre' => $user['nombre'],
                    'email' => $user['email'],
                    'tipo' => $user['tipo']
                ]
            ]);
        } else {
            error_log("❌ Password incorrecta para: " . $email);
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Contraseña incorrecta']);
        }
    } else {
        error_log("❌ Usuario no encontrado en BD: " . $email);
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("❌ Excepción: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Error del servidor',
        'details' => $e->getMessage()
    ]);
}

if (isset($conn)) {
    $conn->close();
}

error_log("=== FIN LOGIN_VALIDAR.PHP ===");
?>