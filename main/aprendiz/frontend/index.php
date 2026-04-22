<?php
require_once '../../includes/auth_check.php';
// Permitir solo roles aprendiz y pasante en este panel
$tipo = isset($_SESSION['user_tipo']) ? strtolower($_SESSION['user_tipo']) : '';
if ($tipo === 'admin') {
    header('Location: ../../admin/index.php');
    exit;
}
if ($tipo !== 'aprendiz' && $tipo !== 'pasante') {
    header('Location: ../../src/index.html');
    exit;
}
require_once __DIR__ . '/../../../includes/ui_feedback.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Principal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/plantaspanel.css">
    <link rel="stylesheet" href="../../welcome/welcome-message.css">
    <?php ui_loading_styles(); ?>
</head>
<body>
    <?php echo ui_loading_overlay('Cargando panel...'); ui_loading_script(); ?>
    <script>
      // Mostrar overlay inmediatamente (ya aparece si está en el DOM)
      uiLoading.show('Cargando panel...');
      window.addEventListener('load', () => {
        // Ocultar inmediatamente al terminar la carga de la página
        uiLoading.hide();
      });
    </script>
    <!-- After including welcome-message.js -->
    <script src="../../welcome/welcome-message.js"></script>
    <script src="../../welcome/init-welcome.js"></script>
    <script src="js/header-footer.js"></script>
</body>
</html>