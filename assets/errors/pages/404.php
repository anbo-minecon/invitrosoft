<?php
require_once __DIR__ . '/../../../includes/ui_feedback.php';

$code = 404;
$title = 'Página no encontrada';
$description = 'La ruta solicitada no existe o fue movida.';
$imagePath = '/invitrosoft/assets/errors/404.png';

ui_render_error($code, $title, $description, $imagePath, 'Error 404 - Página no encontrada');