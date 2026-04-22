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

// Validar que se haya enviado un archivo
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    $error = 'No se pudo cargar el archivo';
    if (isset($_FILES['foto']['error'])) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido',
            UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo del formulario',
            UPLOAD_ERR_PARTIAL => 'El archivo solo se subió parcialmente',
            UPLOAD_ERR_NO_FILE => 'No se seleccionó ningún archivo',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en el disco',
            UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la carga del archivo',
        ];
        $error = $uploadErrors[$_FILES['foto']['error']] ?? 'Error desconocido al cargar el archivo';
    }
    echo json_encode(['success' => false, 'error' => $error]);
    exit;
}

$img = $_FILES['foto'];

// Validar tipo de archivo
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($img['type'], $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Formato de imagen no permitido. Use JPG, PNG, GIF o WebP']);
    exit;
}

// Validar que sea una imagen real
if (!@getimagesize($img['tmp_name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'El archivo no es una imagen válida']);
    exit;
}

// Tamaño máximo 5MB
$maxFileSize = 5 * 1024 * 1024;
if ($img['size'] > $maxFileSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'La imagen es demasiado grande. Tamaño máximo: 5MB']);
    exit;
}

// Directorio de destino
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/invitrosoft/img/user/';

// Asegurarse de que el directorio existe
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'No se pudo crear el directorio de imágenes']);
        exit;
    }
}

// Verificar permisos de escritura
if (!is_writable($uploadDir)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'El directorio de imágenes no tiene permisos de escritura']);
    exit;
}

// Obtener la extensión del archivo
$ext = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));

// Generar nombre único para el archivo
$filename = 'user_' . $user_id . '_' . uniqid() . '.' . $ext;
$relativePath = 'img/user/' . $filename;
$absolutePath = $uploadDir . $filename;

// Mover el archivo subido
if (move_uploaded_file($img['tmp_name'], $absolutePath)) {
    // Establecer permisos adecuados
    chmod($absolutePath, 0644);
    
    // Obtener la foto anterior
    $stmt = $conn->prepare("SELECT foto FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $oldPhoto = $stmt->get_result()->fetch_assoc()['foto'];
    $stmt->close();

    // Construir la URL de la imagen
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $fotoUrl = $baseUrl . '/invitrosoft/' . ltrim($relativePath, '/');

    // Actualizar la base de datos
    $stmt = $conn->prepare("UPDATE usuarios SET foto = ?, foto_url = ?, fecha_actualizacion = NOW() WHERE id = ?");
    $stmt->bind_param("ssi", $relativePath, $fotoUrl, $user_id);
    
    // Establecer la variable de sesión para la foto
    $_SESSION['user_photo'] = $fotoUrl;
    
    if ($stmt->execute()) {
        // Eliminar la foto anterior si existe y no es la predeterminada
        if ($oldPhoto && $oldPhoto !== 'img/user/default.png' && file_exists($_SERVER['DOCUMENT_ROOT'] . '/invitrosoft/' . $oldPhoto)) {
            @unlink($_SERVER['DOCUMENT_ROOT'] . '/invitrosoft/' . $oldPhoto);
        }
        
        // Devolver la respuesta con éxito
        echo json_encode([
            'success' => true,
            'foto' => $relativePath,
            'foto_url' => $fotoUrl,
            'message' => 'Foto de perfil actualizada correctamente'
        ]);
    } else {
        // Si falla la actualización en la BD, eliminar la imagen subida
        @unlink($absolutePath);
        throw new Exception('Error al actualizar la base de datos: ' . $conn->error);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al guardar la imagen en el servidor']);
}
?>