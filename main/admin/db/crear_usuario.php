<?php

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /invitrosoft/src/index.html');
    exit;
}

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Conexión PDO (ajusta estos datos)
$dsn = 'mysql:host=localhost;dbname=invitrosoft;charset=utf8mb4'; // Cambia 'invitrosoft' por el nombre real de tu BD
$dbUser = 'root';
$dbPass = '';

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
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Verificar duplicados (email o identidad)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email OR identidad = :identidad");
    $stmt->execute([':email'=>$email, ':identidad'=>$identidad]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success'=>false,'error'=>'Email o identidad ya registrados']);
        exit;
    }

    // INSERT según tipo
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

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success'=>true, 'id'=> $pdo->lastInsertId()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success'=>false, 'error'=>'Error BD: '.$e->getMessage()]);
}
?>