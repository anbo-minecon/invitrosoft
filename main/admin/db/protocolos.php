<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Opcional: para AJAX
    header('Location: /invitrosoft/src/index.html');
    exit;
}
require_once "conexion.php";
header("Content-Type: application/json");

$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

switch ($accion) {
    case 'listar':
        $sql = "SELECT p.*, 
                (SELECT COUNT(*) FROM fases_protocolo fp WHERE fp.protocolo_id = p.id) as total_fases,
                (SELECT COUNT(*) FROM fase_reactivos fr 
                 INNER JOIN fases_protocolo fp2 ON fr.fase_id = fp2.id 
                 WHERE fp2.protocolo_id = p.id) as total_reactivos
                FROM protocolos p ORDER BY p.id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = [];
        
        while ($row = $res->fetch_assoc()) {
            // Cargar fases
            $stmtf = $conn->prepare("SELECT * FROM fases_protocolo WHERE protocolo_id=? ORDER BY numero_fase");
            $stmtf->bind_param("i", $row['id']);
            $stmtf->execute();
            $resf = $stmtf->get_result();
            $fases = [];
            
            while ($f = $resf->fetch_assoc()) {
                // Cargar reactivos de la fase
                $stmtr = $conn->prepare("SELECT fr.*, r.nombre_comun 
                    FROM fase_reactivos fr 
                    JOIN reactivos r ON fr.reactivo_id = r.id 
                    WHERE fr.fase_id=?");
                $stmtr->bind_param("i", $f['id']);
                $stmtr->execute();
                $resr = $stmtr->get_result();
                $reactivos = [];
                
                while ($r = $resr->fetch_assoc()) {
                    $reactivos[] = [
                        "reactivo_id" => $r['reactivo_id'],
                        "nombre_comun" => $r['nombre_comun'],
                        "cantidad" => $r['cantidad']
                    ];
                }
                
                $f['reactivos'] = $reactivos;
                $fases[] = $f;
            }
            
            $row['fases'] = $fases;

            // Cargar formulaciones
            $resForm = $conn->query("SELECT f.* FROM formulaciones f
                INNER JOIN protocolo_formulacion pf ON pf.formulacion_id = f.id
                WHERE pf.protocolo_id = {$row['id']}");
            $formulaciones = [];
            while ($f = $resForm->fetch_assoc()) {
                $formulaciones[] = $f;
            }
            $row['formulaciones'] = $formulaciones;

            $data[] = $row;
        }
        echo json_encode($data);
        exit;

    case 'crear':
        $nombre = $_POST['nombre'] ?? '';
        $tecnica = $_POST['tecnica_utilizada'] ?? '';
        $fases = json_decode($_POST['fases'], true);
        $formulaciones = json_decode($_POST['formulaciones'] ?? '[]', true);

        if (!$nombre || !count($fases)) {
            http_response_code(400);
            echo json_encode(['success'=>false, 'msg'=>'Datos incompletos']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO protocolos (nombre, tecnica_utilizada) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre, $tecnica);
        
        if ($stmt->execute()) {
            $protocolo_id = $conn->insert_id;
            
            foreach ($fases as $idx => $f) {
                $numero_fase = $f['numero_fase'];
                $nombre_fase = $f['nombre_fase'];
                $descripcion = $f['descripcion'] ?? '';
                $imagen_url = '';
                if (isset($_FILES["fases"]["name"][$idx]["imagen"]) && $_FILES["fases"]["error"][$idx]["imagen"] == 0) {
                    $ext = pathinfo($_FILES["fases"]["name"][$idx]["imagen"], PATHINFO_EXTENSION);
                    $nombreArchivo = 'fase_' . uniqid() . '.' . $ext;
                    $rutaDestino = __DIR__ . '/../img/protocolo/' . $nombreArchivo;
                    move_uploaded_file($_FILES["fases"]["tmp_name"][$idx]["imagen"], $rutaDestino);
                    $imagen_url = $nombreArchivo;
                }
                
                $stmtf = $conn->prepare("INSERT INTO fases_protocolo (protocolo_id, numero_fase, nombre_fase, descripcion, imagen_url) VALUES (?, ?, ?, ?, ?)");
                $stmtf->bind_param("iisss", $protocolo_id, $numero_fase, $nombre_fase, $descripcion, $imagen_url);
                $stmtf->execute();
                $fase_id = $conn->insert_id;
                
                foreach ($f['reactivos'] as $r) {
                    $reactivo_id = intval($r['reactivo_id']);
                    $cantidad = $r['cantidad'] ?? '';
                    $stmtr = $conn->prepare("INSERT INTO fase_reactivos (fase_id, reactivo_id, cantidad) VALUES (?, ?, ?)");
                    $stmtr->bind_param("iis", $fase_id, $reactivo_id, $cantidad);
                    $stmtr->execute();
                }
            }
            // Elimina relaciones viejas si es editar
            $conn->query("DELETE FROM protocolo_formulacion WHERE protocolo_id=$protocolo_id");
            foreach ($formulaciones as $fid) {
                $fid = intval($fid);
                $conn->query("INSERT INTO protocolo_formulacion (protocolo_id, formulacion_id) VALUES ($protocolo_id, $fid)");
            }
            echo json_encode(['success'=>true, 'msg'=>'Protocolo creado correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['success'=>false, 'msg'=>'Error al crear el protocolo']);
        }
        exit;

    case 'editar':
        $id = intval($_POST['id']);
        $nombre = $_POST['nombre'] ?? '';
        $tecnica = $_POST['tecnica_utilizada'] ?? '';
        $fases = json_decode($_POST['fases'], true);
        $formulaciones = json_decode($_POST['formulaciones'] ?? '[]', true);

        if (!$id || !$nombre || !count($fases)) {
            http_response_code(400);
            echo json_encode(['success'=>false, 'msg'=>'Datos incompletos']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE protocolos SET nombre=?, tecnica_utilizada=? WHERE id=?");
        $stmt->bind_param("ssi", $nombre, $tecnica, $id);

        if ($stmt->execute()) {
            // 1. Obtener ids y datos de fases actuales
            $fases_actuales = [];
            $resFases = $conn->query("SELECT id, imagen_url FROM fases_protocolo WHERE protocolo_id=$id");
            while ($row = $resFases->fetch_assoc()) {
                $fases_actuales[$row['id']] = $row['imagen_url'];
            }

            // 2. Recorrer las fases recibidas
            $fases_recibidas_ids = [];
            foreach ($fases as $idx => $f) {
                $fase_id = isset($f['id']) ? intval($f['id']) : 0;
                $numero_fase = $f['numero_fase'];
                $nombre_fase = $f['nombre_fase'];
                $descripcion = $f['descripcion'] ?? '';
                $imagen_url = '';

                // Si es fase existente
                if ($fase_id && isset($fases_actuales[$fase_id])) {
                    $imagen_anterior = $fases_actuales[$fase_id];
                    // Si se sube nueva imagen
                    if ((isset($_FILES["fases"]["name"][$idx]["imagen"]) && $_FILES["fases"]["error"][$idx]["imagen"] == 0)) {
                        if ($imagen_anterior) {
                            $ruta = __DIR__ . '/../img/protocolo/' . $imagen_anterior;
                            if (file_exists($ruta)) unlink($ruta);
                        }
                        $ext = pathinfo($_FILES["fases"]["name"][$idx]["imagen"], PATHINFO_EXTENSION);
                        $nombreArchivo = 'fase_' . uniqid() . '.' . $ext;
                        $rutaDestino = __DIR__ . '/../img/protocolo/' . $nombreArchivo;
                        move_uploaded_file($_FILES["fases"]["tmp_name"][$idx]["imagen"], $rutaDestino);
                        $imagen_url = $nombreArchivo;
                    } elseif (isset($f['eliminar_imagen']) && $f['eliminar_imagen']) {
                        // Solo eliminar si se marca explícitamente
                        if ($imagen_anterior) {
                            $ruta = __DIR__ . '/../img/protocolo/' . $imagen_anterior;
                            if (file_exists($ruta)) unlink($ruta);
                        }
                        $imagen_url = '';
                    } else {
                        // No se cambia la imagen
                        $imagen_url = $imagen_anterior;
                    }
                    // Actualizar la fase
                    $stmtf = $conn->prepare("UPDATE fases_protocolo SET numero_fase=?, nombre_fase=?, descripcion=?, imagen_url=? WHERE id=?");
                    $stmtf->bind_param("isssi", $numero_fase, $nombre_fase, $descripcion, $imagen_url, $fase_id);
                    $stmtf->execute();
                } else {
                    // Fase nueva
                    if (isset($_FILES["fases"]["name"][$idx]["imagen"]) && $_FILES["fases"]["error"][$idx]["imagen"] == 0) {
                        $ext = pathinfo($_FILES["fases"]["name"][$idx]["imagen"], PATHINFO_EXTENSION);
                        $nombreArchivo = 'fase_' . uniqid() . '.' . $ext;
                        $rutaDestino = __DIR__ . '/../img/protocolo/' . $nombreArchivo;
                        move_uploaded_file($_FILES["fases"]["tmp_name"][$idx]["imagen"], $rutaDestino);
                        $imagen_url = $nombreArchivo;
                    }
                    $stmtf = $conn->prepare("INSERT INTO fases_protocolo (protocolo_id, numero_fase, nombre_fase, descripcion, imagen_url) VALUES (?, ?, ?, ?, ?)");
                    $stmtf->bind_param("iisss", $id, $numero_fase, $nombre_fase, $descripcion, $imagen_url);
                    $stmtf->execute();
                    $fase_id = $conn->insert_id;
                }
                $fases_recibidas_ids[] = $fase_id;

                // Elimina reactivos antiguos y agrega los nuevos para esta fase
                $conn->query("DELETE FROM fase_reactivos WHERE fase_id=$fase_id");
                foreach ($f['reactivos'] as $r) {
                    $reactivo_id = intval($r['reactivo_id']);
                    $cantidad = $r['cantidad'] ?? '';
                    $stmtr = $conn->prepare("INSERT INTO fase_reactivos (fase_id, reactivo_id, cantidad) VALUES (?, ?, ?)");
                    $stmtr->bind_param("iis", $fase_id, $reactivo_id, $cantidad);
                    $stmtr->execute();
                }
            }

            // 3. Eliminar fases que ya no están y sus imágenes
            foreach ($fases_actuales as $fid => $img) {
                if (!in_array($fid, $fases_recibidas_ids)) {
                    if ($img) {
                        $ruta = __DIR__ . '/../img/protocolo/' . $img;
                        if (file_exists($ruta)) unlink($ruta);
                    }
                    $conn->query("DELETE FROM fases_protocolo WHERE id=$fid");
                    $conn->query("DELETE FROM fase_reactivos WHERE fase_id=$fid");
                }
            }
            // Elimina relaciones viejas si es editar
            $conn->query("DELETE FROM protocolo_formulacion WHERE protocolo_id=$id");
            foreach ($formulaciones as $fid) {
                $fid = intval($fid);
                $conn->query("INSERT INTO protocolo_formulacion (protocolo_id, formulacion_id) VALUES ($id, $fid)");
            }
            echo json_encode(['success'=>true, 'msg'=>'Protocolo actualizado correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['success'=>false, 'msg'=>'Error al actualizar el protocolo']);
        }
        exit;

    case 'eliminar':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success'=>false, 'msg'=>'ID inválido']);
            exit;
        }
        // Eliminar imágenes de las fases
        $resFases = $conn->query("SELECT imagen_url FROM fases_protocolo WHERE protocolo_id=$id");
        while ($row = $resFases->fetch_assoc()) {
            if ($row['imagen_url']) {
                $ruta = __DIR__ . '/../img/protocolo/' . $row['imagen_url'];
                if (file_exists($ruta)) unlink($ruta);
            }
        }
        // Eliminar reactivos, fases y protocolo
        $conn->query("DELETE FROM fase_reactivos WHERE fase_id IN (SELECT id FROM fases_protocolo WHERE protocolo_id=$id)");
        $conn->query("DELETE FROM fases_protocolo WHERE protocolo_id=$id");
        if ($conn->query("DELETE FROM protocolos WHERE id=$id")) {
            echo json_encode(['success'=>true, 'msg'=>'Protocolo eliminado correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['success'=>false, 'msg'=>'Error al eliminar el protocolo']);
        }
        exit;

    default:
        http_response_code(405);
        echo json_encode(["success"=>false, "msg"=>"Método no permitido"]);
        exit;
}
?>