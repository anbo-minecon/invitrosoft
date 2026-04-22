<?php
// includes/error_router.php
// Router global de errores para Apache ErrorDocument
require_once __DIR__ . '/ui_feedback.php';

// Detectar código desde Apache o querystring
$code = 500;
if (isset($_SERVER['REDIRECT_STATUS'])) {
    $code = (int)$_SERVER['REDIRECT_STATUS'];
} elseif (isset($_GET['code'])) {
    $code = (int)$_GET['code'];
}

// Lista de códigos de error manejados
$errorPages = [401, 403, 404, 500, 503];

// Si el código de error tiene una página personalizada, redirigir a ella
if (in_array($code, $errorPages, true)) {
    $errorPage = "/invitrosoft/assets/errors/pages/{$code}.php";
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $errorPage)) {
        include $_SERVER['DOCUMENT_ROOT'] . $errorPage;
        exit;
    }
}

// Si no hay una página personalizada, mostrar el error genérico
$defaults = [
    401 => ['Sesión no válida', 'Debes iniciar sesión para continuar.'],
    403 => ['Acceso denegado', 'No tienes permisos para ver este recurso.'],
    404 => ['Página no encontrada', 'La ruta solicitada no existe o fue movida.'],
    500 => ['Error interno', 'Ocurrió un error inesperado.'],
    503 => ['Servicio no disponible', 'Estamos en mantenimiento. Intenta más tarde.'],
];
[$title, $desc] = $defaults[$code] ?? ['Error', 'Ocurrió un error.'];

// Buscar imagen de error
$imgDir = '/invitrosoft/assets/errors/';
$imgPath = '';
foreach ([$code.'.png', $code.'.jpg', $code.'.jpeg', $code.'.webp', 'generic.png'] as $fn) {
    $abs = $_SERVER['DOCUMENT_ROOT'] . $imgDir . $fn;
    if (is_file($abs)) { 
        $imgPath = $imgDir . $fn; 
        break; 
    }
}

// Renderizar error genérico
ui_render_error($code, $title, $desc, $imgPath, (string)$code);