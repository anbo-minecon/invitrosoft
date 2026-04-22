<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/conexion.php';

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
if ($userId <= 0) { echo json_encode(['success'=>false,'message'=>'No autenticado']); exit; }

function getPerfil($conn, $userId) {
  $stmt = $conn->prepare('SELECT id, identidad, nombre, genero, telefono, email, tipo, ficha_formacion, created_at, foto FROM usuarios WHERE id=? LIMIT 1');
  $stmt->bind_param('i', $userId);
  $stmt->execute();
  $u = $stmt->get_result()->fetch_assoc();
  if (!$u) return ['success'=>false,'message'=>'Usuario no encontrado'];
  // normalize foto to public URL under /invitrosoft/img/user and provide fallback
  $fotoPublic = $u['foto'] ?? '';
  if (!$fotoPublic) {
    $fotoPublic = '/invitrosoft/img/user/default.png';
  }
  // If not starting with public base, keep as stored. Verify existence; if missing, fallback
  $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', DIRECTORY_SEPARATOR);
  $fsPath = $docRoot . $fotoPublic;
  if (!is_file($fsPath)) {
    $alt = $docRoot . '/invitrosoft/img/user/default.png';
    if (is_file($alt)) { $fotoPublic = '/invitrosoft/img/user/default.png'; }
  }
  return [
    'success'=>true,
    'perfil'=>[
      'id'=>$u['id'],
      'identidad'=>$u['identidad'],
      'nombre'=>$u['nombre'],
      'genero'=>$u['genero'],
      'telefono'=>$u['telefono'],
      'correo'=>$u['email'],
      'tipo'=>$u['tipo'],
      'ficha'=>$u['ficha_formacion'],
      'fecha_creacion'=>$u['created_at'],
      'foto'=>$fotoPublic,
      'instructor'=>null
    ]
  ];
}

function getEstadisticas($conn, $userId) {
  $stats = [
    'total_plantas'=>0,
    'fases_completadas'=>0,
    'proyectos_activos'=>0,
    'dias_activo'=>0,
  ];
  $q1 = $conn->prepare('SELECT COUNT(*) c FROM plantas WHERE usuario_registro_id=?');
  $q1->bind_param('i', $userId); $q1->execute(); $stats['total_plantas']=(int)$q1->get_result()->fetch_assoc()['c'];
  $q2 = $conn->prepare('SELECT COUNT(*) c FROM plantas WHERE usuario_registro_id=? AND (fecha_fin IS NULL OR fecha_fin="")');
  $q2->bind_param('i', $userId); $q2->execute(); $stats['proyectos_activos']=(int)$q2->get_result()->fetch_assoc()['c'];
  $q3sql = "SELECT (
    SELECT COUNT(*) FROM fase_enraizamiento fe INNER JOIN plantas p ON p.id=fe.planta_id WHERE p.usuario_registro_id=? AND fe.fecha_finalizacion IS NOT NULL
  ) + (
    SELECT COUNT(*) FROM fase_multiplicacion fm INNER JOIN plantas p ON p.id=fm.planta_id WHERE p.usuario_registro_id=? AND fm.fecha_finalizacion IS NOT NULL
  ) + (
    SELECT COUNT(*) FROM fase_adaptacion fa INNER JOIN plantas p ON p.id=fa.planta_id WHERE p.usuario_registro_id=? AND fa.fecha_finalizacion IS NOT NULL
  ) AS c";
  $q3 = $conn->prepare($q3sql); $q3->bind_param('iii',$userId,$userId,$userId); $q3->execute(); $stats['fases_completadas']=(int)$q3->get_result()->fetch_assoc()['c'];
  $u = $conn->prepare('SELECT created_at FROM usuarios WHERE id=?'); $u->bind_param('i',$userId); $u->execute(); $created = $u->get_result()->fetch_assoc()['created_at'] ?? null;
  if ($created) { $d1=new DateTime($created); $d2=new DateTime(); $stats['dias_activo']=$d1->diff($d2)->days; }
  return ['success'=>true,'estadisticas'=>$stats];
}

function getActividad($conn, $userId) {
  $sql = "(
    SELECT 'contaminacion' AS tipo, c.fecha_contaminacion AS fecha, CONCAT('Contaminación ', c.tipo, ' en ', p.nombre_comun) AS descripcion
    FROM contaminaciones c INNER JOIN plantas p ON p.id = c.planta_id
    WHERE p.usuario_registro_id = ?
  ) UNION ALL (
    SELECT 'planta' AS tipo, p.fecha_creacion AS fecha, CONCAT('Registró planta ', p.nombre_comun) AS descripcion
    FROM plantas p WHERE p.usuario_registro_id = ?
  )
  ORDER BY fecha DESC LIMIT 10";
  $st = $conn->prepare($sql); $st->bind_param('ii',$userId,$userId); $st->execute();
  $res = $st->get_result(); $acts=[]; while($row=$res->fetch_assoc()) $acts[]=$row;
  return ['success'=>true,'actividad'=>$acts];
}

function actualizarPerfil($conn, $userId) {
  $nombre = $_POST['nombre'] ?? '';
  $correo = $_POST['correo'] ?? '';
  $telefono = $_POST['telefono'] ?? '';
  $ficha = $_POST['ficha'] ?? '';
  if ($nombre==='' || $correo==='') return ['success'=>false,'message'=>'Nombre y correo son requeridos'];
  $chk = $conn->prepare('SELECT id FROM usuarios WHERE email=? AND id<>? LIMIT 1');
  $chk->bind_param('si',$correo,$userId); $chk->execute(); if($chk->get_result()->fetch_assoc()){return ['success'=>false,'message'=>'El correo ya está en uso'];}
  $st = $conn->prepare('UPDATE usuarios SET nombre=?, email=?, telefono=?, ficha_formacion=? WHERE id=?');
  $st->bind_param('ssssi',$nombre,$correo,$telefono,$ficha,$userId);
  if ($st->execute()) return ['success'=>true];
  return ['success'=>false,'message'=>'No se pudo actualizar'];
}

switch ($action) {
  case 'getPerfil': echo json_encode(getPerfil($conn,$userId), JSON_UNESCAPED_UNICODE); break;
  case 'getEstadisticas': echo json_encode(getEstadisticas($conn,$userId), JSON_UNESCAPED_UNICODE); break;
  case 'getActividad': echo json_encode(getActividad($conn,$userId), JSON_UNESCAPED_UNICODE); break;
  case 'actualizarPerfil': echo json_encode(actualizarPerfil($conn,$userId), JSON_UNESCAPED_UNICODE); break;
  default: echo json_encode(['success'=>false,'message'=>'Acción no válida']);
}
