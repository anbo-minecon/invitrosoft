<?php
require_once '../../includes/auth_check.php';
require_once "conexion.php";
$res = null;
header("Content-Type: application/json");
// Por defecto devolver solo reactivos activos para consumidores.
$onlyActive = !(isset($_GET['all']) && $_GET['all'] == '1');
if ($onlyActive) {
	$res = $conn->query("SELECT id, nombre_comun, estado FROM reactivos WHERE estado = 'activo' ORDER BY nombre_comun");
} else {
	$res = $conn->query("SELECT id, nombre_comun, estado FROM reactivos ORDER BY nombre_comun");
}
$data = [];
while ($row = $res->fetch_assoc()) $data[] = $row;
echo json_encode($data);
?>