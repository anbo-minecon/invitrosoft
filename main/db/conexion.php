<?php
// Archivo de conexión reutilizable
$conn = new mysqli("localhost", "root", "", "invitrosoft");
if ($conn->connect_errno) {
    http_response_code(500);
    die("Error de conexión a la base de datos");
}
?>
