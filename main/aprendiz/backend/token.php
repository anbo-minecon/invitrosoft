<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
$userTipo = isset($_SESSION['user_tipo']) ? $_SESSION['user_tipo'] : '';
if ($userId <= 0) { echo json_encode(['success'=>false,'error'=>'No autenticado']); exit; }

// Cargar manualmente el archivo .env si no está cargado
$envFile = __DIR__ . '/../../../../.env';
$JWT_SECRET = 'grger5eger51erg1erv5erer45v1r5v55ver4v4vbe'; // Valor por defecto

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if ($name === 'JWT_SECRET') {
            $JWT_SECRET = $value;
            break;
        }
    }
}

// Asegurarse de que la clave secreta no esté vacía
if (empty($JWT_SECRET)) {
    $JWT_SECRET = 'grger5eger51erg1erv5erer45v1r5v55ver4v4vbe';
    error_log('ADVERTENCIA: Se está utilizando la clave secreta por defecto');
}
$ttl = 60 * 60 * 8; // 8h
$now = time();
$payload = [
  'id' => $userId,
  'role' => strtolower($userTipo ?: 'aprendiz'),
  'iat' => $now,
  'exp' => $now + $ttl,
];

function base64url_encode($data) { return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); }

$header = ['alg'=>'HS256','typ'=>'JWT'];
$segments = [];
$segments[] = base64url_encode(json_encode($header));
$segments[] = base64url_encode(json_encode($payload, JSON_UNESCAPED_UNICODE));
$signing_input = implode('.', $segments);
$signature = hash_hmac('sha256', $signing_input, $JWT_SECRET, true);
$segments[] = base64url_encode($signature);
$token = implode('.', $segments);

echo json_encode(['success'=>true,'token'=>$token]);
