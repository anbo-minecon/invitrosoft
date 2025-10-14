<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /invitrosoft/src/index.html');
    exit;
}
require_once "conexion.php";
header("Content-Type: application/json");

$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

switch ($accion) {
    case 'listar':
        $tipo = $_GET['tipo'] ?? '';
        $sql = "SELECT * FROM formulaciones";
        if ($tipo) $sql .= " WHERE tipo=?";
        $stmt = $conn->prepare($sql);
        if ($tipo) $stmt->bind_param("s", $tipo);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = [];
        while ($row = $res->fetch_assoc()) {
            // Cargar grupos y reactivos solo para soluciones madre y medios de cultivo
            $grupos = [];
            if ($row['tipo'] !== 'soluciones-desinfectantes') {
                $stmtg = $conn->prepare("SELECT * FROM grupos_formulacion WHERE formulacion_id=?");
                $stmtg->bind_param("i", $row['id']);
                $stmtg->execute();
                $resg = $stmtg->get_result();
                while ($g = $resg->fetch_assoc()) {
                    $reactivos = [];
                    $stmtr = $conn->prepare("SELECT gr.*, r.nombre_comun FROM grupo_reactivos gr JOIN reactivos r ON gr.reactivo_id = r.id WHERE gr.grupo_id=?");
                    $stmtr->bind_param("i", $g['id']);
                    $stmtr->execute();
                    $resr = $stmtr->get_result();
                    while ($r = $resr->fetch_assoc()) {
                        $reactivos[] = [
                            "reactivo_id" => $r['reactivo_id'],
                            "nombre_comun" => $r['nombre_comun'],
                            "cantidad" => $r['cantidad']
                        ];
                    }
                    $g['reactivos'] = $reactivos;
                    $grupos[] = $g;
                }
            }
            $row['grupos'] = $grupos;
            $data[] = $row;
        }
        echo json_encode($data);
        exit;

    case 'crear':
        $input = json_decode(file_get_contents('php://input'), true);
        $nombre = $input['nombre'] ?? '';
        $tipo = $input['tipo'] ?? '';
        $solucion_madre = $input['solucion_madre'] ?? null;
        $volumen = $input['volumen'] ?? null;
        $desinfectante = $input['desinfectante'] ?? '';
        $concentracion = $input['concentracion'] ?? '';
        $grupos = $input['grupos'] ?? [];

        // Validación según tipo
        if (!$nombre || !$tipo ||
            ($tipo === 'soluciones-desinfectantes' && (!$desinfectante || !$concentracion))
        ) {
            http_response_code(400);
            echo json_encode(['success'=>false, 'msg'=>'Datos incompletos']);
            exit;
        }
        // Para soluciones madre y medios de cultivo, grupos es obligatorio
        if (($tipo === 'soluciones-madre' || $tipo === 'medios-cultivo') && !count($grupos)) {
            http_response_code(400);
            echo json_encode(['success'=>false, 'msg'=>'Debe agregar al menos un grupo con reactivos']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO formulaciones (nombre, tipo, solucion_madre, volumen, desinfectante, concentracion) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nombre, $tipo, $solucion_madre, $volumen, $desinfectante, $concentracion);
        if ($stmt->execute()) {
            $formulacion_id = $conn->insert_id;
            if ($tipo !== 'soluciones-desinfectantes') {
                foreach ($grupos as $g) {
                    $nombre_grupo = $g['nombre_grupo'];
                    $stmtg = $conn->prepare("INSERT INTO grupos_formulacion (formulacion_id, nombre_grupo) VALUES (?, ?)");
                    $stmtg->bind_param("is", $formulacion_id, $nombre_grupo);
                    $stmtg->execute();
                    $grupo_id = $conn->insert_id;
                    foreach ($g['reactivos'] as $r) {
                        $reactivo_id = intval($r['reactivo_id']);
                        $cantidad = $r['cantidad'] ?? '';
                        $stmtr = $conn->prepare("INSERT INTO grupo_reactivos (grupo_id, reactivo_id, cantidad) VALUES (?, ?, ?)");
                        $stmtr->bind_param("iis", $grupo_id, $reactivo_id, $cantidad);
                        $stmtr->execute();
                    }
                }
            }
            echo json_encode(['success'=>true, 'msg'=>'Formulación creada correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['success'=>false, 'msg'=>'Error al crear la formulación']);
        }
        exit;

    case 'editar':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id']);
        $nombre = $input['nombre'] ?? '';
        $tipo = $input['tipo'] ?? '';
        $solucion_madre = $input['solucion_madre'] ?? null;
        $volumen = $input['volumen'] ?? null;
        $desinfectante = $input['desinfectante'] ?? '';
        $concentracion = $input['concentracion'] ?? '';
        $grupos = $input['grupos'] ?? [];

        if (!$id || !$nombre || !$tipo ||
            ($tipo === 'soluciones-desinfectantes' && (!$desinfectante || !$concentracion))
        ) {
            http_response_code(400);
            echo json_encode(['success'=>false, 'msg'=>'Datos incompletos']);
            exit;
        }
        if (($tipo === 'soluciones-madre' || $tipo === 'medios-cultivo') && !count($grupos)) {
            http_response_code(400);
            echo json_encode(['success'=>false, 'msg'=>'Debe agregar al menos un grupo con reactivos']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE formulaciones SET nombre=?, tipo=?, solucion_madre=?, volumen=?, desinfectante=?, concentracion=? WHERE id=?");
        $stmt->bind_param("ssssssi", $nombre, $tipo, $solucion_madre, $volumen, $desinfectante, $concentracion, $id);
        if ($stmt->execute()) {
            // Eliminar grupos y reactivos antiguos solo si no es desinfectante
            if ($tipo !== 'soluciones-desinfectantes') {
                $conn->query("DELETE FROM grupo_reactivos WHERE grupo_id IN (SELECT id FROM grupos_formulacion WHERE formulacion_id=$id)");
                $conn->query("DELETE FROM grupos_formulacion WHERE formulacion_id=$id");
                foreach ($grupos as $g) {
                    $nombre_grupo = $g['nombre_grupo'];
                    $stmtg = $conn->prepare("INSERT INTO grupos_formulacion (formulacion_id, nombre_grupo) VALUES (?, ?)");
                    $stmtg->bind_param("is", $id, $nombre_grupo);
                    $stmtg->execute();
                    $grupo_id = $conn->insert_id;
                    foreach ($g['reactivos'] as $r) {
                        $reactivo_id = intval($r['reactivo_id']);
                        $cantidad = $r['cantidad'] ?? '';
                        $stmtr = $conn->prepare("INSERT INTO grupo_reactivos (grupo_id, reactivo_id, cantidad) VALUES (?, ?, ?)");
                        $stmtr->bind_param("iis", $grupo_id, $reactivo_id, $cantidad);
                        $stmtr->execute();
                    }
                }
            } else {
                // Si es desinfectante, elimina cualquier grupo/relación previa
                $conn->query("DELETE FROM grupo_reactivos WHERE grupo_id IN (SELECT id FROM grupos_formulacion WHERE formulacion_id=$id)");
                $conn->query("DELETE FROM grupos_formulacion WHERE formulacion_id=$id");
            }
            echo json_encode(['success'=>true, 'msg'=>'Formulación actualizada correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['success'=>false, 'msg'=>'Error al actualizar la formulación']);
        }
        exit;

    case 'eliminar':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success'=>false, 'msg'=>'ID inválido']);
            exit;
        }
        if ($conn->query("DELETE FROM formulaciones WHERE id=$id")) {
            echo json_encode(['success'=>true, 'msg'=>'Formulación eliminada correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['success'=>false, 'msg'=>'Error al eliminar la formulación']);
        }
        exit;

    default:
        http_response_code(405);
        echo json_encode(["success"=>false, "msg"=>"Método no permitido"]);
        exit;
}
?>
