<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /invitrosoft/src/index.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Principal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/plantaspanel.css">
</head>
<body>
    <script src="js/header-footer.js"></script>
</body>
</html>