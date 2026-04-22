<?php
require_once __DIR__ . '/../../../includes/ui_feedback.php';

$code = 503;
$title = 'Servicio no disponible';
$description = 'Estamos en mantenimiento. Por favor, inténtalo de nuevo más tarde.';
$imagePath = '../503.png';

ui_render_error($code, $title, $description, $imagePath, 'Error 503 - Servicio no disponible');