<?php

// api/plantas.php - API para gestión de plantas con cambio de fase
session_start();
header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit;
}

// Conexión a base de datos
require_once 'conexion.php';
require_once 'notificaciones_emit.php';

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    case 'getAll':
        getPlantas($conn);
        break;
    case 'getOne':
        getPlanta($conn);
        break;
    case 'create':
        createPlanta($conn);
        break;
    case 'update':
        updatePlanta($conn);
        break;
    case 'delete':
        deletePlanta($conn);
        break;
    case 'getParametros':
        getParametros($conn);
        break;
    case 'getReactivos':
        getReactivos($conn);
        break;
    case 'cambiarFase':
        cambiarFase($conn);
        break;
    case 'guardarFase':
        guardarFase($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}

// Obtener todas las plantas
function getPlantas($conn) {
    $usuario_id = intval($_SESSION['user_id']);
    $query = "SELECT 
                p.*,
                po.nombre as origen_nombre,
                pr.nombre as protocolo_nombre,
                pe.nombre as estado_nombre,
                u.nombre as usuario_nombre
              FROM plantas p
              LEFT JOIN parametros po ON p.origen_id = po.id_parametro
              LEFT JOIN protocolos pr ON p.protocolo_id = pr.id
              LEFT JOIN parametros pe ON p.estado_id = pe.id_parametro
              LEFT JOIN usuarios u ON p.usuario_registro_id = u.id
              WHERE p.usuario_registro_id = $usuario_id
              ORDER BY p.fecha_creacion DESC";
    
    $result = $conn->query($query);
    $plantas = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $plantas[] = $row;
        }
        echo json_encode(['success' => true, 'plantas' => $plantas]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al cargar plantas']);
    }
}

// Obtener una planta específica
function getPlanta($conn) {
    $id = intval($_GET['id']);
    $usuario_id = intval($_SESSION['user_id']);
    
    $query = "SELECT 
                p.*,
                po.nombre as origen_nombre,
                pr.nombre as protocolo_nombre,
                pe.nombre as estado_nombre
              FROM plantas p
              LEFT JOIN parametros po ON p.origen_id = po.id_parametro
              LEFT JOIN protocolos pr ON p.protocolo_id = pr.id
              LEFT JOIN parametros pe ON p.estado_id = pe.id_parametro
              WHERE p.id = ? AND p.usuario_registro_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'planta' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Planta no encontrada']);
    }
}

// Crear nueva planta
function createPlanta($conn) {
    $codigo = $conn->real_escape_string($_POST['codigo']);
    $nombre_comun = $conn->real_escape_string($_POST['nombre_comun']);
    $nombre_cientifico = $conn->real_escape_string($_POST['nombre_cientifico'] ?? '');
    $especie = $conn->real_escape_string($_POST['especie'] ?? '');
    $origen_id = !empty($_POST['origen_id']) ? intval($_POST['origen_id']) : 'NULL';
    $protocolo_id = !empty($_POST['protocolo_id']) ? intval($_POST['protocolo_id']) : 'NULL';
    $metodo_propagacion = $conn->real_escape_string($_POST['metodo_propagacion'] ?? '');
    $fase_actual = 'seleccion'; // Siempre inicia en selección
    $fecha_inicio = !empty($_POST['fecha_inicio']) ? "'".$conn->real_escape_string($_POST['fecha_inicio'])."'" : 'NULL';
    $fecha_fin = !empty($_POST['fecha_fin']) ? "'".$conn->real_escape_string($_POST['fecha_fin'])."'" : 'NULL';
    $estado_id = !empty($_POST['estado_id']) ? intval($_POST['estado_id']) : 'NULL';
    $observaciones = $conn->real_escape_string($_POST['observaciones'] ?? '');
    $usuario_id = $_SESSION['user_id'];
    
    // Verificar que el código no exista
    $check = $conn->query("SELECT id FROM plantas WHERE codigo = '$codigo' AND usuario_registro_id = $usuario_id");
    if ($check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El código ya existe']);
        return;
    }
    
    $query = "INSERT INTO plantas 
              (codigo, nombre_comun, nombre_cientifico, especie, origen_id, protocolo_id, 
               metodo_propagacion, fase_actual, fecha_inicio, fecha_fin, estado_id, 
               usuario_registro_id, observaciones)
              VALUES 
              ('$codigo', '$nombre_comun', '$nombre_cientifico', '$especie', $origen_id, $protocolo_id,
               '$metodo_propagacion', '$fase_actual', $fecha_inicio, $fecha_fin, $estado_id,
               $usuario_id, '$observaciones')";
    
    if ($conn->query($query)) {
        $newId = $conn->insert_id;
        // Notificación: nueva planta
        notificar($conn, [
            'usuario_id' => intval($_SESSION['user_id']),
            'planta_id' => $newId,
            'tipo' => 'nueva_planta',
            'titulo' => 'Nueva planta creada',
            'mensaje' => 'Se creó la planta ' . $nombre_comun . ' (código ' . $codigo . ')',
            'usuario_id' => intval($_SESSION['user_id'])
        ]);
        echo json_encode(['success' => true, 'message' => 'Planta creada exitosamente', 'id' => $newId]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear planta: ' . $conn->error]);
    }
}

// Actualizar planta
function updatePlanta($conn) {
    $id = intval($_POST['id']);
    $usuario_id = intval($_SESSION['user_id']);
    $codigo = $conn->real_escape_string($_POST['codigo']);
    $nombre_comun = $conn->real_escape_string($_POST['nombre_comun']);
    $nombre_cientifico = $conn->real_escape_string($_POST['nombre_cientifico'] ?? '');
    $especie = $conn->real_escape_string($_POST['especie'] ?? '');
    $origen_id = !empty($_POST['origen_id']) ? intval($_POST['origen_id']) : 'NULL';
    $protocolo_id = !empty($_POST['protocolo_id']) ? intval($_POST['protocolo_id']) : 'NULL';
    $metodo_propagacion = $conn->real_escape_string($_POST['metodo_propagacion'] ?? '');
    $fecha_inicio = !empty($_POST['fecha_inicio']) ? "'".$conn->real_escape_string($_POST['fecha_inicio'])."'" : 'NULL';
    $fecha_fin = !empty($_POST['fecha_fin']) ? "'".$conn->real_escape_string($_POST['fecha_fin'])."'" : 'NULL';
    $estado_id = !empty($_POST['estado_id']) ? intval($_POST['estado_id']) : 'NULL';
    $observaciones = $conn->real_escape_string($_POST['observaciones'] ?? '');
    
    // Verificar que el código no esté duplicado (excepto para el mismo registro)
    $check = $conn->query("SELECT id FROM plantas WHERE codigo = '$codigo' AND id != $id AND usuario_registro_id = $usuario_id");
    if ($check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El código ya existe']);
        return;
    }
    
    $query = "UPDATE plantas SET
              codigo = '$codigo',
              nombre_comun = '$nombre_comun',
              nombre_cientifico = '$nombre_cientifico',
              especie = '$especie',
              origen_id = $origen_id,
              protocolo_id = $protocolo_id,
              metodo_propagacion = '$metodo_propagacion',
              fecha_inicio = $fecha_inicio,
              fecha_fin = $fecha_fin,
              estado_id = $estado_id,
              observaciones = '$observaciones'
              WHERE id = $id AND usuario_registro_id = $usuario_id";
    
    if ($conn->query($query)) {
        echo json_encode(['success' => true, 'message' => 'Planta actualizada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar planta: ' . $conn->error]);
    }
}

// Eliminar planta
function deletePlanta($conn) {
    $id = intval($_POST['id']);
    $usuario_id = intval($_SESSION['user_id']);
    
    // Verificar que no tenga registros en fases
    $checkFases = $conn->query("
        SELECT COUNT(*) as total FROM (
            SELECT planta_id FROM fase_establecimiento WHERE planta_id = $id
            UNION ALL
            SELECT planta_id FROM fase_multiplicacion WHERE planta_id = $id
            UNION ALL
            SELECT planta_id FROM fase_enraizamiento WHERE planta_id = $id
            UNION ALL
            SELECT planta_id FROM fase_adaptacion WHERE planta_id = $id
        ) AS fases
    ");
    
    $row = $checkFases->fetch_assoc();
    if ($row['total'] > 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'No se puede eliminar. La planta tiene registros en fases asociadas.'
        ]);
        return;
    }
    
    $query = "DELETE FROM plantas WHERE id = $id AND usuario_registro_id = $usuario_id";
    
    if ($conn->query($query) && $conn->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Planta eliminada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No autorizado o planta no encontrada']);
    }
}

// Obtener parámetros y protocolos para los selects
function getParametros($conn) {
    $parametros = [];
    
    // Obtener orígenes
    $queryOrigen = "SELECT id_parametro, nombre FROM parametros WHERE id_tipo = 3 ORDER BY nombre";
    $result = $conn->query($queryOrigen);
    $parametros['origen'] = [];
    while ($row = $result->fetch_assoc()) {
        $parametros['origen'][] = $row;
    }

    // Obtener estados
    $queryEstado = "SELECT id_parametro, nombre FROM parametros WHERE id_tipo = 2 ORDER BY nombre";
    $result = $conn->query($queryEstado);
    $parametros['estado'] = [];
    while ($row = $result->fetch_assoc()) {
        $parametros['estado'][] = $row;
    }
    
    // Obtener estados de raíces
    $queryEstadoRaices = "SELECT id_parametro, nombre FROM parametros WHERE id_tipo = 5 ORDER BY nombre";
    $result = $conn->query($queryEstadoRaices);
    $parametros['estadoRaices'] = [];
    while ($row = $result->fetch_assoc()) {
        $parametros['estadoRaices'][] = $row;
    }
    
    // Obtener protocolos
    $queryProtocolos = "SELECT id, nombre FROM protocolos ORDER BY nombre";
    $result = $conn->query($queryProtocolos);
    $protocolos = [];
    while ($row = $result->fetch_assoc()) {
        $protocolos[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'parametros' => $parametros,
        'protocolos' => $protocolos
    ]);
}

// Obtener reactivos disponibles
function getReactivos($conn) {
    $query = "SELECT id, nombre_comun, formula_quimica, unidad_medida, cantidad_total 
              FROM reactivos 
              WHERE cantidad_total > 0 AND estado = 'activo'
              ORDER BY nombre_comun";
    
    $result = $conn->query($query);
    $reactivos = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $reactivos[] = $row;
        }
        echo json_encode(['success' => true, 'reactivos' => $reactivos]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al cargar reactivos']);
    }
}

// Cambiar fase de la planta
function cambiarFase($conn) {
    $planta_id = intval($_POST['planta_id']);
    $usuario_id = intval($_SESSION['user_id']);
    
    // Obtener fase actual
    $query = "SELECT fase_actual FROM plantas WHERE id = $planta_id AND usuario_registro_id = $usuario_id";
    $result = $conn->query($query);
    
    if (!$result || $result->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Planta no encontrada o no autorizada']);
        return;
    }
    
    $planta = $result->fetch_assoc();
    $fase_actual = $planta['fase_actual'];
    
    // Determinar siguiente fase
    $fases = ['seleccion', 'establecimiento', 'multiplicacion', 'enraizamiento', 'adaptacion'];
    $index_actual = array_search($fase_actual, $fases);
    
    if ($index_actual === false || $index_actual >= count($fases) - 1) {
        echo json_encode(['success' => false, 'message' => 'La planta ya está en la última fase']);
        return;
    }
    
    $siguiente_fase = $fases[$index_actual + 1];
    
    echo json_encode([
        'success' => true,
        'fase_actual' => $fase_actual,
        'siguiente_fase' => $siguiente_fase,
        'planta_id' => $planta_id
    ]);
}

// Guardar datos de la nueva fase
function guardarFase($conn) {
    $planta_id = intval($_POST['planta_id']);
    $fase = $conn->real_escape_string($_POST['fase']);
    $fecha_inicio = $conn->real_escape_string($_POST['fecha_inicio']);
    $fecha_finalizacion = !empty($_POST['fecha_finalizacion']) ? "'".$conn->real_escape_string($_POST['fecha_finalizacion'])."'" : 'NULL';
    $observaciones = $conn->real_escape_string($_POST['observaciones'] ?? '');
    $usuario_id = $_SESSION['user_id'];

    // Verificar propiedad de la planta
    $checkOwner = $conn->query("SELECT id FROM plantas WHERE id = $planta_id AND usuario_registro_id = $usuario_id");
    if (!$checkOwner || $checkOwner->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        return;
    }
    
    // Obtener reactivos (formato: reactivo_X y cantidad_X)
    $reactivos = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'reactivo_') === 0 && !empty($value)) {
            $index = str_replace('reactivo_', '', $key);
            $cantidad_key = 'cantidad_' . $index;
            if (!empty($_POST[$cantidad_key])) {
                $reactivos[] = [
                    'reactivo_id' => intval($value),
                    'cantidad' => $conn->real_escape_string($_POST[$cantidad_key])
                ];
            }
        }
    }
    
    $conn->begin_transaction();
    
    try {
        $fase_id = null;
        
        // Insertar en la tabla de la fase correspondiente
        switch ($fase) {
            case 'establecimiento':
                $metodo_propagacion = $conn->real_escape_string($_POST['metodo_propagacion'] ?? '');
                $contaminacion_id = !empty($_POST['contaminacion_id']) ? intval($_POST['contaminacion_id']) : 'NULL';
                
                $query = "INSERT INTO fase_establecimiento 
                          (planta_id, fecha_inicio, fecha_finalizacion, metodo_propagacion, 
                           contaminacion_id, usuario_registro_id, observaciones)
                          VALUES 
                          ($planta_id, '$fecha_inicio', $fecha_finalizacion, '$metodo_propagacion',
                           $contaminacion_id, $usuario_id, '$observaciones')";
                
                if ($conn->query($query)) {
                    $fase_id = $conn->insert_id;
                    
                    // Insertar reactivos y actualizar cantidades
                    foreach ($reactivos as $reactivo) {
                        // Verificar que el reactivo existe, está activo y hay suficiente cantidad disponible
                        $check = $conn->query("SELECT cantidad_total, estado FROM reactivos WHERE id = {$reactivo['reactivo_id']} FOR UPDATE");
                        if ($check && $row = $check->fetch_assoc()) {
                            if (!isset($row['estado']) || strtolower($row['estado']) !== 'activo') {
                                throw new Exception('El reactivo seleccionado no está activo');
                            }
                            $nueva_cantidad = $row['cantidad_total'] - floatval($reactivo['cantidad']);
                            if ($nueva_cantidad < 0) {
                                throw new Exception('No hay suficiente cantidad disponible del reactivo seleccionado');
                            }
                            
                            // Actualizar la cantidad disponible
                            $update = $conn->query("UPDATE reactivos SET cantidad_total = $nueva_cantidad WHERE id = {$reactivo['reactivo_id']}");
                            if (!$update) {
                                throw new Exception('Error al actualizar la cantidad del reactivo');
                            }
                            
                            // Insertar en la tabla de elementos de la fase
                            $query_reactivo = "INSERT INTO fase_establecimiento_elementos 
                                            (fase_establecimiento_id, reactivo_id, cantidad)
                                            VALUES ($fase_id, {$reactivo['reactivo_id']}, '{$reactivo['cantidad']}')";
                            $conn->query($query_reactivo);
                        } else {
                            throw new Exception('Reactivo no encontrado');
                        }
                    }
                }
                break;
                
            case 'multiplicacion':
                $num_explantes = intval($_POST['num_explantes_generados'] ?? 0);
                $tiempo_madurez = !empty($_POST['tiempo_estimacion_madurez']) ? intval($_POST['tiempo_estimacion_madurez']) : 'NULL';
                $contaminacion_id = !empty($_POST['contaminacion_id']) ? intval($_POST['contaminacion_id']) : 'NULL';
                
                $query = "INSERT INTO fase_multiplicacion 
                          (planta_id, fecha_inicio, fecha_finalizacion, num_explantes_generados,
                           tiempo_estimacion_madurez, contaminacion_id, usuario_registro_id, observaciones)
                          VALUES 
                          ($planta_id, '$fecha_inicio', $fecha_finalizacion, $num_explantes,
                           $tiempo_madurez, $contaminacion_id, $usuario_id, '$observaciones')";
                
                if ($conn->query($query)) {
                    $fase_id = $conn->insert_id;
                    
                    // Insertar reactivos y actualizar cantidades
                    foreach ($reactivos as $reactivo) {
                        // Verificar que el reactivo existe, está activo y hay suficiente cantidad disponible
                        $check = $conn->query("SELECT cantidad_total, estado FROM reactivos WHERE id = {$reactivo['reactivo_id']} FOR UPDATE");
                        if ($check && $row = $check->fetch_assoc()) {
                            if (!isset($row['estado']) || strtolower($row['estado']) !== 'activo') {
                                throw new Exception('El reactivo seleccionado no está activo');
                            }
                            $nueva_cantidad = $row['cantidad_total'] - floatval($reactivo['cantidad']);
                            if ($nueva_cantidad < 0) {
                                throw new Exception('No hay suficiente cantidad disponible del reactivo seleccionado');
                            }
                            
                            // Actualizar la cantidad disponible
                            $update = $conn->query("UPDATE reactivos SET cantidad_total = $nueva_cantidad WHERE id = {$reactivo['reactivo_id']}");
                            if (!$update) {
                                throw new Exception('Error al actualizar la cantidad del reactivo');
                            }
                            
                            // Insertar en la tabla de elementos de la fase
                            $query_reactivo = "INSERT INTO fase_multiplicacion_elementos 
                                            (fase_multiplicacion_id, reactivo_id, cantidad)
                                            VALUES ($fase_id, {$reactivo['reactivo_id']}, '{$reactivo['cantidad']}')";
                            $conn->query($query_reactivo);
                        } else {
                            throw new Exception('Reactivo no encontrado');
                        }
                    }
                }
                break;
                
            case 'enraizamiento':
                $medio_utilizado_id = !empty($_POST['medio_utilizado_id']) ? intval($_POST['medio_utilizado_id']) : 'NULL';
                $estado_raices_id = !empty($_POST['estado_raices_id']) ? intval($_POST['estado_raices_id']) : 'NULL';
                $contaminacion_id = !empty($_POST['contaminacion_id']) ? intval($_POST['contaminacion_id']) : 'NULL';
                $estado_id = !empty($_POST['estado_id']) ? intval($_POST['estado_id']) : 'NULL';
                
                $query = "INSERT INTO fase_enraizamiento 
                          (planta_id, fecha_inicio, fecha_finalizacion, medio_utilizado_id,
                           estado_raices_id, contaminacion_id, estado_id, usuario_registro_id, observaciones)
                          VALUES 
                          ($planta_id, '$fecha_inicio', $fecha_finalizacion, $medio_utilizado_id,
                           $estado_raices_id, $contaminacion_id, $estado_id, $usuario_id, '$observaciones')";
                
                if ($conn->query($query)) {
                    $fase_id = $conn->insert_id;
                    
                    // Insertar reactivos y actualizar cantidades
                    foreach ($reactivos as $reactivo) {
                        // Verificar que el reactivo existe, está activo y hay suficiente cantidad disponible
                        $check = $conn->query("SELECT cantidad_total, estado FROM reactivos WHERE id = {$reactivo['reactivo_id']} FOR UPDATE");
                        if ($check && $row = $check->fetch_assoc()) {
                            if (!isset($row['estado']) || strtolower($row['estado']) !== 'activo') {
                                throw new Exception('El reactivo seleccionado no está activo');
                            }
                            $nueva_cantidad = $row['cantidad_total'] - floatval($reactivo['cantidad']);
                            if ($nueva_cantidad < 0) {
                                throw new Exception('No hay suficiente cantidad disponible del reactivo seleccionado');
                            }
                            
                            // Actualizar la cantidad disponible
                            $update = $conn->query("UPDATE reactivos SET cantidad_total = $nueva_cantidad WHERE id = {$reactivo['reactivo_id']}");
                            if (!$update) {
                                throw new Exception('Error al actualizar la cantidad del reactivo');
                            }
                            
                            // Insertar en la tabla de elementos de la fase
                            $query_reactivo = "INSERT INTO fase_enraizamiento_elementos 
                                            (fase_enraizamiento_id, reactivo_id, cantidad)
                                            VALUES ($fase_id, {$reactivo['reactivo_id']}, '{$reactivo['cantidad']}')";
                            $conn->query($query_reactivo);
                        } else {
                            throw new Exception('Reactivo no encontrado');
                        }
                    }
                }
                break;
                
            case 'adaptacion':
                $condiciones = $conn->real_escape_string($_POST['condiciones_adaptacion'] ?? '');
                $medio_cultivo_id = !empty($_POST['medio_cultivo_id']) ? intval($_POST['medio_cultivo_id']) : 'NULL';
                $resultado = $conn->real_escape_string($_POST['resultado_adaptacion'] ?? '');
                $contaminacion_id = !empty($_POST['contaminacion_id']) ? intval($_POST['contaminacion_id']) : 'NULL';
                $estado_id = !empty($_POST['estado_id']) ? intval($_POST['estado_id']) : 'NULL';
                
                $query = "INSERT INTO fase_adaptacion 
                          (planta_id, fecha_inicio, fecha_finalizacion, condiciones_adaptacion,
                           medio_cultivo_id, resultado_adaptacion, contaminacion_id, estado_id,
                           usuario_registro_id, observaciones)
                          VALUES 
                          ($planta_id, '$fecha_inicio', $fecha_finalizacion, '$condiciones',
                           $medio_cultivo_id, '$resultado', $contaminacion_id, $estado_id,
                           $usuario_id, '$observaciones')";
                
                if ($conn->query($query)) {
                    $fase_id = $conn->insert_id;
                    
                    // Insertar reactivos y actualizar cantidades
                    foreach ($reactivos as $reactivo) {
                        // Verificar que el reactivo existe, está activo y hay suficiente cantidad disponible
                        $check = $conn->query("SELECT cantidad_total, estado FROM reactivos WHERE id = {$reactivo['reactivo_id']} FOR UPDATE");
                        if ($check && $row = $check->fetch_assoc()) {
                            if (!isset($row['estado']) || strtolower($row['estado']) !== 'activo') {
                                throw new Exception('El reactivo seleccionado no está activo');
                            }
                            $nueva_cantidad = $row['cantidad_total'] - floatval($reactivo['cantidad']);
                            if ($nueva_cantidad < 0) {
                                throw new Exception('No hay suficiente cantidad disponible del reactivo seleccionado');
                            }
                            
                            // Actualizar la cantidad disponible
                            $update = $conn->query("UPDATE reactivos SET cantidad_total = $nueva_cantidad WHERE id = {$reactivo['reactivo_id']}");
                            if (!$update) {
                                throw new Exception('Error al actualizar la cantidad del reactivo');
                            }
                            
                            // Insertar en la tabla de elementos de la fase
                            $query_reactivo = "INSERT INTO fase_adaptacion_elementos 
                                            (fase_adaptacion_id, reactivo_id, cantidad)
                                            VALUES ($fase_id, {$reactivo['reactivo_id']}, '{$reactivo['cantidad']}')";
                            $conn->query($query_reactivo);
                        } else {
                            throw new Exception('Reactivo no encontrado');
                        }
                    }
                }
                break;
                
            default:
                throw new Exception('Fase no válida');
        }
        
        if (!$fase_id) {
            throw new Exception('Error al insertar fase: ' . $conn->error);
        }
        
        // Actualizar fase_actual en la tabla plantas
        $updateQuery = "UPDATE plantas SET fase_actual = '$fase' WHERE id = $planta_id";
        if (!$conn->query($updateQuery)) {
            throw new Exception('Error al actualizar fase actual: ' . $conn->error);
        }
        
        $conn->commit();
        // Notificación: cambio de fase
        notificar($conn, [
            'planta_id' => $planta_id,
            'tipo' => 'cambio_fase',
            'titulo' => 'Cambio de fase',
            'mensaje' => 'La planta #' . $planta_id . ' cambió a la fase ' . $fase,
            'usuario_id' => intval($_SESSION['user_id']),
            'fase_tipo' => $fase,
            'fase_id' => $fase_id
        ]);

        // Si la fase es adaptacion y se marcó fecha_finalizacion, consideramos terminada
        if ($fase === 'adaptacion' && $fecha_finalizacion !== 'NULL') {
            notificar($conn, [
                'planta_id' => $planta_id,
                'tipo' => 'planta_terminada',
                'titulo' => 'Planta terminada',
                'mensaje' => 'La planta #' . $planta_id . ' ha finalizado su proceso',
                'usuario_id' => intval($_SESSION['user_id']),
                'fase_tipo' => $fase,
                'fase_id' => $fase_id
            ]);
        }

        echo json_encode(['success' => true, 'message' => 'Fase actualizada exitosamente']);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Include notificaciones_emit.php
require_once 'notificaciones_emit.php';

$conn->close();
?>