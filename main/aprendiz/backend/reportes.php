<?php
// backend/reportes.php - API para generar reportes y estadísticas
session_start();
header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit;
}

require_once 'conexion.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'getReportes':
        getReportes($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}

function getReportes($conn) {
    $usuario_id = intval($_SESSION['user_id']);
    $fecha_inicio = isset($_GET['fecha_inicio']) ? $conn->real_escape_string($_GET['fecha_inicio']) : null;
    $fecha_fin = isset($_GET['fecha_fin']) ? $conn->real_escape_string($_GET['fecha_fin']) : null;
    
    $whereClause = "p.usuario_registro_id = $usuario_id";
    if ($fecha_inicio && $fecha_fin) {
        $whereClause .= " AND p.fecha_creacion BETWEEN '$fecha_inicio' AND '$fecha_fin 23:59:59'";
    } elseif ($fecha_inicio) {
        $whereClause .= " AND p.fecha_creacion >= '$fecha_inicio'";
    } elseif ($fecha_fin) {
        $whereClause .= " AND p.fecha_creacion <= '$fecha_fin 23:59:59'";
    }
    
    // 1. Distribución por fases
    // En la función getReportes(), modificar la consulta de fases:
    $queryFases = "SELECT 
                    p.fase_actual as fase, 
                    COUNT(*) as total 
                FROM plantas p
                WHERE p.usuario_registro_id = $usuario_id
                GROUP BY p.fase_actual
                ORDER BY total DESC";
    
    $resultFases = $conn->query($queryFases);
    $fases = [];
    if ($resultFases) {
        while ($row = $resultFases->fetch_assoc()) {
            $fases[] = $row;
        }
    }
    
    // 2. Fases por semana (últimas 8 semanas)
    $querySemanal = "SELECT 
                        WEEK(p.fecha_creacion) as semana,
                        fase_actual as fase,
                        COUNT(*) as total
                     FROM plantas p
                     WHERE $whereClause
                     AND p.fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK)
                     GROUP BY semana, fase_actual
                     ORDER BY semana ASC, fase_actual";
    
    $resultSemanal = $conn->query($querySemanal);
    $semanal = [];
    if ($resultSemanal) {
        while ($row = $resultSemanal->fetch_assoc()) {
            $semanal[] = $row;
        }
    }
    
    // 3. Proceso por tipo de planta (Top 5)
    $queryPlantas = "SELECT 
                        nombre_comun,
                        COUNT(*) as total
                     FROM plantas p
                     WHERE $whereClause
                     GROUP BY nombre_comun
                     ORDER BY total DESC
                     LIMIT 5";
    
    $resultPlantas = $conn->query($queryPlantas);
    $plantas = [];
    if ($resultPlantas) {
        while ($row = $resultPlantas->fetch_assoc()) {
            $plantas[] = $row;
        }
    }
    
   // 4. Reactivos más utilizados (Top 5)
$queryReactivos = "SELECT 
                    r.nombre_comun,
                    r.unidad_medida,
                    SUM(CAST(SUBSTRING_INDEX(elem.cantidad, ' ', 1) AS DECIMAL(10,2))) as total_usado
                   FROM reactivos r
                   INNER JOIN (
                       -- Eliminamos la referencia a fase_establecimiento_elementos
                       -- ya que esta tabla ya no existe
                       SELECT fme.reactivo_id, fme.cantidad, fm.planta_id 
                       FROM fase_multiplicacion_elementos fme
                       INNER JOIN fase_multiplicacion fm ON fm.id = fme.fase_multiplicacion_id
                       UNION ALL
                       SELECT fee2.reactivo_id, fee2.cantidad, fer.planta_id 
                       FROM fase_enraizamiento_elementos fee2
                       INNER JOIN fase_enraizamiento fer ON fer.id = fee2.fase_enraizamiento_id
                       UNION ALL
                       SELECT fae.reactivo_id, fae.cantidad, fa.planta_id 
                       FROM fase_adaptacion_elementos fae
                       INNER JOIN fase_adaptacion fa ON fa.id = fae.fase_adaptacion_id
                   ) elem ON r.id = elem.reactivo_id
                   INNER JOIN plantas p ON p.id = elem.planta_id
                   WHERE p.usuario_registro_id = $usuario_id
                   GROUP BY r.id, r.nombre_comun, r.unidad_medida
                   ORDER BY total_usado DESC
                   LIMIT 5";
    $resultReactivos = $conn->query($queryReactivos);
    $reactivos = [];
    if ($resultReactivos) {
        while ($row = $resultReactivos->fetch_assoc()) {
            $reactivos[] = $row;
        }
    }
    
    // 5. Plantas recientes (últimas 10)
    $queryRecientes = "SELECT 
                        codigo,
                        nombre_comun,
                        fase_actual,
                        fecha_creacion
                       FROM plantas p
                       WHERE $whereClause
                       ORDER BY fecha_creacion DESC
                       LIMIT 10";
    
    $resultRecientes = $conn->query($queryRecientes);
    $recientes = [];
    if ($resultRecientes) {
        while ($row = $resultRecientes->fetch_assoc()) {
            $recientes[] = $row;
        }
    }
    
    // Al final de la función getReportes, reemplaza todo lo que hay después de las consultas con:
        $response = [
            'success' => true,
            'fases' => $fases ?: [],
            'semanal' => $semanal ?: [],
            'plantas' => $plantas ?: [],
            'reactivos' => $reactivos ?: [],
            'recientes' => $recientes ?: [],
            'message' => 'Datos cargados correctamente'
        ];

// Enviar respuesta
header('Content-Type: application/json');
echo json_encode($response);
exit;

    // Agregar depuración
    error_log("Respuesta del servidor: " . json_encode($response));

    echo json_encode($response, JSON_PRETTY_PRINT);
    
    // Verificar si hay algún dato para mostrar
    $hasData = !empty($fases) || !empty($semanal) || !empty($plantas) || !empty($reactivos) || !empty($recientes);
    $response['hasData'] = $hasData;
    
    if (!$hasData) {
        $response['message'] = 'No se encontraron datos para mostrar con los filtros seleccionados';
    }
    
    echo json_encode($response);
}

$conn->close();
?>