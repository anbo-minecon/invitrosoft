<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso denegado');
}

require_once __DIR__ . '/../db/conexion.php';

$response = [
    'success' => false,
    'data' => []
];

// Verificar si hay error de conexión
if ($conn->connect_error) {
    $response['error'] = 'Error de conexión: ' . $conn->connect_error;
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Configurar el conjunto de caracteres
try {
    $conn->set_charset('utf8');
    
    // Obtener estadísticas
    $stats = [];
    
    // Función auxiliar para obtener un conteo
    function getCount($conn, $table, $where = '') {
        $sql = "SELECT COUNT(*) as total FROM $table";
        if (!empty($where)) {
            $sql .= " WHERE $where";
        }
        $result = $conn->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            return (int)$row['total'];
        }
        return 0;
    }
    
    // Obtener totales
    $stats['total_usuarios'] = getCount($conn, 'usuarios');
    $stats['total_reactivos'] = getCount($conn, 'reactivos');
    $stats['total_plantas'] = getCount($conn, 'plantas');
    $stats['total_formulaciones'] = getCount($conn, 'formulaciones');
    $stats['reactivos_bajos'] = getCount($conn, 'reactivos', 'cantidad_total < 50');
    $stats['plantas_multiplicacion'] = getCount($conn, 'plantas', "estado = 'en_multiplicacion'");
    
    $response = [
        'success' => true,
        'data' => $stats
    ];
    
} catch (Exception $e) {
    $response['error'] = 'Error: ' . $e->getMessage();
}

// Cerrar conexión
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
