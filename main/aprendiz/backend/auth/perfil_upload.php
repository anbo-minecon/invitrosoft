<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../conexion.php';

$userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
if ($userId <= 0) { echo json_encode(['success'=>false,'error'=>'No authenticated']); exit; }

// Obtener la foto anterior antes de actualizar
$oldFotoPublic = '';
$getOld = $conn->prepare('SELECT foto FROM usuarios WHERE id = ? LIMIT 1');
$getOld->bind_param('i', $userId);
$getOld->execute();
$resOld = $getOld->get_result();
if ($rowOld = $resOld->fetch_assoc()) {
  $oldFotoPublic = $rowOld['foto'] ?? '';
}

if (!isset($_FILES['avatar'])) {
  echo json_encode(['success'=>false,'error'=>'Archivo no recibido']);
  exit;
}

$file = $_FILES['avatar'];
if ($file['error'] !== UPLOAD_ERR_OK) {
  echo json_encode(['success'=>false,'error'=>'Error al subir archivo']);
  exit;
}

$allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
if (!isset($allowed[$mime])) {
  echo json_encode(['success'=>false,'error'=>'Formato no permitido']);
  exit;
}
$ext = $allowed[$mime];
// Build absolute filesystem path using the web server document root
$docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', DIRECTORY_SEPARATOR);
$baseDir = $docRoot . '/invitrosoft/img/user';
if (!is_dir($baseDir)) {
  @mkdir($baseDir, 0777, true);
}
$filename = 'user_' . $userId . '_' . time() . '.' . $ext;
$destPath = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
if (!move_uploaded_file($file['tmp_name'], $destPath)) {
  echo json_encode(['success'=>false,'error'=>'No se pudo guardar la imagen']);
  exit;
}
// ruta pública
$publicPath = '/invitrosoft/img/user/' . $filename;

// Actualizar BD; si falla, eliminar la nueva imagen para evitar archivos huérfanos
$stmt = $conn->prepare('UPDATE usuarios SET foto=? WHERE id=?');
$stmt->bind_param('si', $publicPath, $userId);
if ($stmt->execute()) {
  // Borrar la foto anterior si aplica
  $defaultPublic = '/invitrosoft/img/user/default.png';
  if (!empty($oldFotoPublic) && $oldFotoPublic !== $defaultPublic && $oldFotoPublic !== $publicPath) {
    $oldFs = $docRoot . $oldFotoPublic;
    // Seguridad: solo eliminar si está dentro del directorio de usuarios
    $realBase = realpath($baseDir) ?: $baseDir;
    $realOld = realpath($oldFs);
    if ($realOld && strpos($realOld, $realBase) === 0 && is_file($realOld)) {
      @unlink($realOld);
    }
  }
  echo json_encode(['success'=>true,'foto'=>$publicPath]);
} else {
  // Revertir archivo guardado si falla la actualización
  if (is_file($destPath)) { @unlink($destPath); }
  echo json_encode(['success'=>false,'error'=>'No se pudo actualizar la foto']);
}

