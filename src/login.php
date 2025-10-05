<?php
session_start();
require_once '../main/db/conexion.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

//#############
file_put_contents('debug_login.txt', print_r($input, true));

if (!$email || !$password) {
    echo json_encode(['success' => false, 'error' => 'Faltan datos']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();


//####################
if ($user) {
    file_put_contents('debug_login.txt', "Hash en BD: {$user['password']}\nPassword recibido: $password\nVerifica: " . (password_verify($password, $user['password']) ? 'OK' : 'FAIL'));
}

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Credenciales incorrectas']);
}
?>
