<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db/conexion.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

$user_id = intval($_SESSION['user_id']);

// Función para obtener la URL base
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    return $protocol . $_SERVER['HTTP_HOST'];
}

try {
    // Obtener datos del usuario
    $stmt = $conn->prepare("
        SELECT 
            u.id, u.nombre, u.email, u.telefono, u.tipo as rol, u.created_at, u.foto, u.foto_url,
            u.identidad, u.tiempo_uso, u.ficha_formacion, u.bibliografia,
            p.nombre as genero_nombre, u.genero
        FROM usuarios u
        LEFT JOIN parametros p ON u.genero = p.id_parametro
        WHERE u.id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    
    if (!$usuario) {
        throw new Exception('Usuario no encontrado');
    }
    
    // Construir URL de la foto
    $baseUrl = getBaseUrl();
    $foto = $usuario['foto'] ?? '';
    $fotoUrl = '';
    
    if (!empty($foto)) {
        // Si la foto no es una URL completa, construir la URL completa
        if (!filter_var($foto, FILTER_VALIDATE_URL)) {
            // Eliminar barras iniciales si existen
            $foto = ltrim($foto, '/');
            $fotoUrl = $baseUrl . '/invitrosoft/' . $foto;
            
            // Verificar si el archivo existe físicamente
            $rutaFisica = $_SERVER['DOCUMENT_ROOT'] . '/invitrosoft/' . $foto;
            if (!file_exists($rutaFisica)) {
                // Si el archivo no existe, usar la imagen por defecto
                $foto = 'img/user/default.png';
                $fotoUrl = $baseUrl . '/invitrosoft/' . $foto;
            }
        } else {
            $fotoUrl = $foto;
        }
    } else {
        // Si no hay foto, usar la imagen por defecto
        $foto = 'img/user/default.png';
        $fotoUrl = $baseUrl . '/invitrosoft/' . $foto;
    }
    
    // Formatear la respuesta
    $response = [
        'success' => true,
        'usuario' => [
            'id' => $usuario['id'],
            'nombre' => $usuario['nombre'] ?? '',
            'nombre_completo' => $usuario['nombre'] ?? '',
            'email' => $usuario['email'] ?? '',
            'telefono' => $usuario['telefono'] ?? '',
            'genero' => $usuario['genero_nombre'] ?? 'No especificado',
            'rol' => ucfirst($usuario['rol'] ?? 'Usuario'),
            'fecha_registro' => date('d/m/Y', strtotime($usuario['created_at'] ?? 'now')),
            'foto' => $foto,
            'foto_url' => $fotoUrl,
            'fecha_creacion' => $usuario['created_at'],
            'rol' => $usuario['rol'],
            'bibliografia' => $usuario['bibliografia'] ?? '',
            'identidad' => $usuario['identidad'] ?? '',
            'tiempo_uso' => $usuario['tiempo_uso'] ?? 'No especificado',
            'ficha_formacion' => $usuario['ficha_formacion'] ?? 'No especificada',
            'genero_id' => $usuario['genero'] ?? null
        ]
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Error al cargar el perfil: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_UNESCAPED_UNICODE);
}
?>