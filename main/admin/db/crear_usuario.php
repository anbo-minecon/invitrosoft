<?php
// Ubicación: /main/admin/db/crear_usuario.php

// Configuración de encabezados
header('Content-Type: application/json; charset=utf-8');

// Incluir archivos necesarios
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/conexion.php';

// Verificar si la petición es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Método no permitido
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido. Se esperaba una petición POST.'
    ]);
    exit;
}

// Restringir a rol administrador
if (!isset($_SESSION['user_tipo']) || strtolower($_SESSION['user_tipo']) !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// Configurar manejo de errores
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

// Leer datos JSON del POST
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'No se recibieron datos JSON válidos']);
    exit;
}

// Validaciones básicas
$required = ['nombre','email','password','password2','identidad','genero','telefono','tipo'];
foreach ($required as $r) {
    if (empty($input[$r])) {
        http_response_code(400);
        echo json_encode(['success'=>false,'error'=>"Falta campo: $r"]);
        exit;
    }
}

if ($input['password'] !== $input['password2']) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Las contraseñas no coinciden']);
    exit;
}

// Sanitizar y validar datos
$nombre = trim($input['nombre']);
$email = filter_var(trim($input['email']), FILTER_VALIDATE_EMAIL);
if (!$email) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Email inválido']);
    exit;
}
$identidad = trim($input['identidad']);
$genero = intval($input['genero']);
$telefono = trim($input['telefono']);
$tipo = trim($input['tipo']);
$tiempo_uso = isset($input['tiempo_uso']) ? trim($input['tiempo_uso']) : null;
$ficha_formacion = isset($input['ficha_formacion']) ? trim($input['ficha_formacion']) : null;
$passHash = password_hash($input['password'], PASSWORD_DEFAULT);

try {
    // Crear conexión PDO usando las constantes de conexion.php
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4, time_zone = '-05:00'"
    ]);

    // Verificar duplicados (email o identidad)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email OR identidad = :identidad");
    $stmt->execute([':email'=>$email, ':identidad'=>$identidad]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success'=>false,'error'=>'Email o identidad ya registrados']);
        exit;
    }

    // Preparar INSERT según tipo de usuario
    if ($tipo === 'aprendiz') {
        $sql = "INSERT INTO usuarios (nombre,email,password,identidad,genero,telefono,tipo,tiempo_uso,ficha_formacion,created_at)
                VALUES (:nombre,:email,:password,:identidad,:genero,:telefono,:tipo,:tiempo_uso,:ficha_formacion,NOW())";
        $params = [
            ':nombre'=>$nombre,
            ':email'=>$email,
            ':password'=>$passHash,
            ':identidad'=>$identidad,
            ':genero'=>$genero,
            ':telefono'=>$telefono,
            ':tipo'=>$tipo,
            ':tiempo_uso'=>$tiempo_uso,
            ':ficha_formacion'=>$ficha_formacion
        ];
    } elseif ($tipo === 'pasante') {
        $sql = "INSERT INTO usuarios (nombre,email,password,identidad,genero,telefono,tipo,tiempo_uso,created_at)
                VALUES (:nombre,:email,:password,:identidad,:genero,:telefono,:tipo,:tiempo_uso,NOW())";
        $params = [
            ':nombre'=>$nombre,
            ':email'=>$email,
            ':password'=>$passHash,
            ':identidad'=>$identidad,
            ':genero'=>$genero,
            ':telefono'=>$telefono,
            ':tipo'=>$tipo,
            ':tiempo_uso'=>$tiempo_uso
        ];
    } else { // admin u otros
        $sql = "INSERT INTO usuarios (nombre,email,password,identidad,genero,telefono,tipo,created_at)
                VALUES (:nombre,:email,:password,:identidad,:genero,:telefono,:tipo,NOW())";
        $params = [
            ':nombre'=>$nombre,
            ':email'=>$email,
            ':password'=>$passHash,
            ':identidad'=>$identidad,
            ':genero'=>$genero,
            ':telefono'=>$telefono,
            ':tipo'=>$tipo
        ];
    }

    // Iniciar transacción
    $pdo->beginTransaction();
    try {
        // Insertar usuario
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $newUserId = $pdo->lastInsertId();
        
        // Crear notificación de nuevo usuario
        $titulo = "Nuevo usuario registrado";
        $mensaje = "Se ha creado un nuevo usuario: " . $nombre . " (" . $tipo . ")";
        
        // Notificar a los administradores
        $adminStmt = $pdo->prepare("SELECT id FROM usuarios WHERE tipo = 'admin' AND id != :current_user_id");
        $adminStmt->execute([':current_user_id' => $_SESSION['user_id']]);
        $admins = $adminStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Insertar notificaciones para cada administrador
        $notifStmt = $pdo->prepare("INSERT INTO notificaciones 
            (usuario_id, tipo, titulo, mensaje, modulo, accion, fecha_creacion) 
            VALUES (:usuario_id, 'info', :titulo, :mensaje, 'usuarios', 'crear', NOW())");
        
        foreach ($admins as $admin) {
            $notifStmt->execute([
                ':usuario_id' => $admin['id'],
                ':titulo' => $titulo,
                ':mensaje' => $mensaje
            ]);
        }
        
        // Confirmar transacción
        $pdo->commit();
        
        error_log("✅ [ADMIN] Usuario creado exitosamente: " . $nombre . " (ID: " . $newUserId . ")");
        
        echo json_encode([
            'success'=>true, 
            'id'=> $newUserId, 
            'message' => 'Usuario creado exitosamente'
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("❌ [ADMIN] Error en transacción: " . $e->getMessage());
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("❌ [ADMIN] Error de base de datos: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success'=>false, 
        'error'=>'Error al procesar la solicitud. Por favor, intente nuevamente.'
    ]);
}
?>