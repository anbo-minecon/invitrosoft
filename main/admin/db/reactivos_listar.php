<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Opcional: para AJAX
    header('Location: /invitrosoft/src/index.html');
    exit;
}
require_once "conexion.php";
header("Content-Type: application/json");
$res = $conn->query("SELECT id, nombre_comun FROM reactivos ORDER BY nombre_comun");
$data = [];
while ($row = $res->fetch_assoc()) $data[] = $row;
echo json_encode($data);
?>