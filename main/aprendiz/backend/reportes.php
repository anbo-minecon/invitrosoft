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
    $fecha_inicio = isset($_GET['fecha_inicio']) ? $conn->real_escape_string($_GET['fecha_inicio']) : null;
    $fecha_fin = isset($_GET['fecha_fin']) ? $conn->real_escape_string($_GET['fecha_fin']) : null;
    
    $whereClause = "1=1";
    if ($fecha_inicio && $fecha_fin) {
        $whereClause = "p.fecha_creacion BETWEEN '$fecha_inicio' AND '$fecha_fin 23:59:59'";
    } elseif ($fecha_inicio) {
        $whereClause = "p.fecha_creacion >= '$fecha_inicio'";
    } elseif ($fecha_fin) {
        $whereClause = "p.fecha_creacion <= '$fecha_fin 23:59:59'";
    }
    
    // 1. Distribución por fases
    $queryFases = "SELECT 
                    fase_actual as fase, 
                    COUNT(*) as total 
                   FROM plantas p
                   WHERE $whereClause
                   GROUP BY fase_actual
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
                           SELECT reactivo_id, cantidad FROM fase_establecimiento_elementos
                           UNION ALL
                           SELECT reactivo_id, cantidad FROM fase_multiplicacion_elementos
                           UNION ALL
                           SELECT reactivo_id, cantidad FROM fase_enraizamiento_elementos
                           UNION ALL
                           SELECT reactivo_id, cantidad FROM fase_adaptacion_elementos
                       ) elem ON r.id = elem.reactivo_id
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
    
    // 6. Usuarios activos (Top 5)
    $queryUsuarios = "SELECT 
                        u.nombre,
                        u.rol,
                        COUNT(p.id) as total_plantas
                      FROM usuarios u
                      INNER JOIN plantas p ON u.id = p.usuario_registro_id
                      WHERE $whereClause
                      GROUP BY u.id, u.nombre, u.rol
                      ORDER BY total_plantas DESC
                      LIMIT 5";
    
    $resultUsuarios = $conn->query($queryUsuarios);
    $usuarios = [];
    if ($resultUsuarios) {
        while ($row = $resultUsuarios->fetch_assoc()) {
            $usuarios[] = $row;
        }
    }
    
    echo json_encode([
        'success' => true,
        'fases' => $fases,
        'semanal' => $semanal,
        'plantas' => $plantas,
        'reactivos' => $reactivos,
        'recientes' => $recientes,
        'usuarios' => $usuarios
    ]);
}

$conn->close();
?>