<?php
require_once __DIR__ . '/../../../includes/ui_feedback.php';

$code = 500;
$title = 'Error interno del servidor';
$description = 'Ocurrió un error inesperado. Por favor, inténtalo de nuevo más tarde.';
$imagePath = '../500.png';

ui_render_error($code, $title, $description, $imagePath, 'Error 500 - Error interno');