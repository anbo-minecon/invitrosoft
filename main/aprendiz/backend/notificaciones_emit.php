<?php
// Helper para insertar notificación en BD
if (!function_exists('notificar')) {
  function notificar($conn, $opts = []) {
    // $opts: usuario_id (destinatario), planta_id, tipo, titulo, mensaje, usuario_id, fase_tipo, fase_id, contaminacion_id
    $usuario_id = isset($opts['usuario_id']) ? intval($opts['usuario_id']) : 0;
    $planta_id = isset($opts['planta_id']) ? intval($opts['planta_id']) : null;
    $tipo = isset($opts['tipo']) ? $conn->real_escape_string($opts['tipo']) : 'otro';
    $titulo = isset($opts['titulo']) ? $conn->real_escape_string($opts['titulo']) : '';
    $mensaje = isset($opts['mensaje']) ? $conn->real_escape_string($opts['mensaje']) : '';
    $fase_tipo = isset($opts['fase_tipo']) ? $conn->real_escape_string($opts['fase_tipo']) : null;
    $fase_id = isset($opts['fase_id']) ? intval($opts['fase_id']) : null;
    $contaminacion_id = isset($opts['contaminacion_id']) ? intval($opts['contaminacion_id']) : null;

    // Asegurar que 'tipo' esté dentro del ENUM actual de la tabla
    $allowedTipos = ['nueva_planta','cambio_fase','contaminacion','otro'];
    if (!in_array($tipo, $allowedTipos, true)) { $tipo = 'otro'; }

    // Si no hay usuario destinatario, intentar inferir desde la planta
    if ($usuario_id <= 0 && $planta_id) {
      $res = $conn->query("SELECT usuario_registro_id FROM plantas WHERE id=".(int)$planta_id." LIMIT 1");
      if ($res && $row = $res->fetch_assoc()) { $usuario_id = (int)$row['usuario_registro_id']; }
    }
    if ($usuario_id <= 0) { $usuario_id = 1; } // fallback mínimo para cumplir NOT NULL

    // Insertar en BD
    $cols = ['usuario_id','tipo','titulo','mensaje'];
    $vals = ["$usuario_id","'$tipo'","'$titulo'","'$mensaje'"];
    if (!is_null($planta_id)) { $cols[]='planta_id'; $vals[]=(string)(int)$planta_id; }
    if (!is_null($fase_tipo)) { $cols[]='fase_tipo'; $vals[]="'".$fase_tipo."'"; }
    if (!is_null($fase_id)) { $cols[]='fase_id'; $vals[]=(string)(int)$fase_id; }
    if (!is_null($contaminacion_id)) { $cols[]='contaminacion_id'; $vals[]=(string)(int)$contaminacion_id; }

    $sql = "INSERT INTO notificaciones (".implode(',', $cols).") VALUES (".implode(',', $vals).")";
    @$conn->query($sql);
    
    return true;
  }
}
