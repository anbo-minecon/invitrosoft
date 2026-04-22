<?php
// includes/ui_feedback.php
// Utilidades para páginas de error y overlay de carga
// Uso:
//   require_once __DIR__.'/ui_feedback.php';
//   ui_render_error(404, 'Página no encontrada');
//   // o helpers: ui_error_404(); ui_error_500(); ui_error_503('Mantenimiento programado');
//   // Overlay de carga: echo ui_loading_overlay(); ui_loading_script();

if (!function_exists('ui_base_head')) {
    function ui_base_head($title = 'Invitrosoft') {
        $css = <<<CSS
        <style>
          :root{
            --bg:#0f1419; --card-bg:#1b2432; --text:#e5e7eb; --text-secondary:#a0aec0; --primary:#007832; --primary-light:#00a844; --border:#2d3748;
          }
          *{box-sizing:border-box}
          html,body{height:100%}
          body{margin:0; background:var(--bg); color:var(--text); font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,'Helvetica Neue',Arial,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';}
          .ui-wrap{min-height:100%; display:flex; align-items:center; justify-content:center; padding:24px}
          .ui-card{width:100%; max-width:980px; background:var(--card-bg); border:1px solid var(--border); border-radius:16px; box-shadow:0 24px 72px rgba(0,0,0,.35); overflow:hidden}
          .ui-head{display:flex; align-items:center; gap:12px; padding:18px 22px; border-bottom:1px solid var(--border)}
          .ui-head .badge{display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:999px; background:linear-gradient(135deg,var(--primary) 0%, var(--primary-light) 100%); color:#fff; font-weight:900}
          .ui-title{margin:0; font-size:18px; font-weight:900}
          .ui-body{padding:22px}
          .ui-layout{display:grid; grid-template-columns: 1fr; gap:28px; align-items:center}
          .ui-content{display:flex; flex-direction:column; gap:10px}
          .ui-brand{font-size:28px; font-weight:900; letter-spacing:.2px; background:linear-gradient(90deg,#4ade80 0%, #22c55e 50%, #16a34a 100%); -webkit-background-clip:text; background-clip:text; color:transparent}
          .ui-headline{margin:0; font-weight:800; font-size:26px; line-height:1.2}
          .ui-headline .ui-code-text{font-weight:900; margin-right:6px}
          .ui-desc{margin:2px 0 0 0; color:var(--text-secondary); font-weight:700}
          .ui-media{display:flex; align-items:center; justify-content:center}
          .ui-media img{max-width:360px; width:100%; height:auto; display:block; filter:drop-shadow(0 16px 30px rgba(0,0,0,.3));}
          @media(min-width: 860px){ .ui-layout{grid-template-columns: 1fr 380px;} }
          .ui-actions{display:flex; gap:10px; padding:18px 22px; border-top:1px solid var(--border); justify-content:flex-end; background:rgba(0,0,0,.06)}
          .btn{display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:999px; border:1px solid var(--border); background:transparent; color:var(--text); font-weight:900; cursor:pointer}
          .btn-primary{border-color:var(--primary); background:linear-gradient(135deg,var(--primary) 0%, var(--primary-light) 100%); color:#fff}
          .btn:hover{filter:saturate(1.05)}

          /* Loading overlay */
          .ui-loading{position:fixed; inset:0; display:none; align-items:center; justify-content:center; z-index:4000}
          .ui-loading.active{display:flex}
          .ui-loading .backdrop{position:absolute; inset:0; background:rgba(0,0,0,.5)}
          .ui-loading .pane{position:relative; z-index:1; background:var(--card-bg); border:1px solid var(--border); border-radius:14px; padding:18px 20px; display:flex; align-items:center; gap:12px; box-shadow:0 24px 72px rgba(0,0,0,.35)}
          .spinner{width:22px; height:22px; border:3px solid #2e3a49; border-top-color:var(--primary-light); border-radius:50%; animation:spin .9s linear infinite}
          .spinner-img{width:28px; height:28px; display:block; object-fit:contain; animation:spin 1s linear infinite}
          @keyframes spin{to{transform:rotate(360deg)}}
        </style>
        CSS;
        echo "<!DOCTYPE html><html lang=\"es\"><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"><title>".htmlspecialchars($title)."</title>$css</head><body>";
    }
}

if (!function_exists('ui_base_foot')) {
    function ui_base_foot() { echo "</body></html>"; }
}

// Estilos del overlay para páginas normales (cuando no usamos ui_base_head)
if (!function_exists('ui_loading_styles')) {
    function ui_loading_styles(): void {
        echo '<style>'
           . '.ui-loading{position:fixed; inset:0; display:none; align-items:center; justify-content:center; z-index:4000}'
           . '.ui-loading.active{display:flex}'
           . '.ui-loading .backdrop{position:absolute; inset:0; background:rgba(0,0,0,.5)}'
           . '.ui-loading .pane{position:relative; z-index:1; background:#1b2432; border:1px solid #2d3748; border-radius:14px; padding:18px 20px; display:flex; align-items:center; gap:12px; box-shadow:0 24px 72px rgba(0,0,0,.35)}'
           . '.spinner{width:22px; height:22px; border:3px solid #2e3a49; border-top-color:#00a844; border-radius:50%; animation:spin .9s linear infinite}'
           . '.spinner-img{width:28px; height:28px; display:block; object-fit:contain; animation:spin 1s linear infinite}'
           . '@keyframes spin{to{transform:rotate(360deg)}}'
           . '</style>';
    }
}

if (!function_exists('ui_render_error')) {
    // $imageUrl es opcional. Si se provee, se muestra a la izquierda del contenido en desktop.
    function ui_render_error(int $code = 500, string $message = 'Ha ocurrido un error', string $description = '', string $imageUrl = '', string $imageAlt = '') {
        http_response_code($code);
        ui_base_head("$code - $message");
        echo '<div class="ui-wrap"><article class="ui-card">';
        echo '<header class="ui-head"><span class="badge">!</span><h1 class="ui-title">'.htmlspecialchars($message).'</h1></header>';
        echo '<div class="ui-body">';
        if ($imageUrl !== '') {
            echo '<div class="ui-layout">';
            echo '<div class="ui-media"><img src="'.htmlspecialchars($imageUrl).'" alt="'.htmlspecialchars($imageAlt ?: $message).'"></div>';
            echo '<div class="ui-content">';
        }
        echo '<div><span class="ui-code">'.htmlspecialchars((string)$code).'</span><strong>'.htmlspecialchars(ui_error_label($code)).'</strong></div>';
        if ($description !== '') {
            echo '<p class="ui-desc">'.htmlspecialchars($description).'</p>';
        }
        if ($imageUrl !== '') {
            echo '</div></div>'; // close .ui-content and .ui-layout
        }
        echo '</div>';
        echo '<div class="ui-actions">'
           .'<button class="btn" onclick="history.back()">⟵ Volver</button>'
           .'<button class="btn" onclick="location.href=\'/invitrosoft/main/aprendiz/frontend/index.php\'">Ir al panel</button>'
           .'<button class="btn-primary" onclick="location.href=\'/\'">Inicio</button>'
           .'</div>';
        echo '</article></div>';
        ui_base_foot();
        exit;
    }
}

// Helper para error con imagen explícita
if (!function_exists('ui_error_with_image')) {
    function ui_error_with_image(int $code, string $message, string $description, string $imageUrl, string $imageAlt = ''): void {
        ui_render_error($code, $message, $description, $imageUrl, $imageAlt);
    }
}

if (!function_exists('ui_error_label')) {
    function ui_error_label(int $code): string {
        return match ($code) {
            400 => 'Solicitud inválida',
            401 => 'No autorizado',
            403 => 'Acceso denegado',
            404 => 'No encontrado',
            408 => 'Tiempo de espera agotado',
            429 => 'Demasiadas solicitudes',
            500 => 'Error interno del servidor',
            502 => 'Puerta de enlace no válida',
            503 => 'Servicio no disponible',
            504 => 'Tiempo de espera de la puerta de enlace',
            default => 'Error',
        };
    }
}

// Helpers rápidos específicos
if (!function_exists('ui_error_404')) { function ui_error_404(string $desc = 'La ruta solicitada no existe.'){ ui_render_error(404,'Página no encontrada',$desc);} }
if (!function_exists('ui_error_500')) { function ui_error_500(string $desc = 'Lo sentimos, algo salió mal.'){ ui_render_error(500,'Error interno',$desc);} }
if (!function_exists('ui_error_503')) { function ui_error_503(string $desc = 'Mantenimiento. Intenta más tarde.'){ ui_render_error(503,'Servicio no disponible',$desc);} }

// Loading overlay HTML y script
if (!function_exists('ui_loading_overlay')) {
    function ui_loading_overlay(string $text = 'Cargando...', string $imageUrl = ''): string {
        $icon = $imageUrl !== ''
          ? '<img id="uiLoadingImg" class="spinner-img" src="'.htmlspecialchars($imageUrl).'" alt="Cargando">'
          : '<div id="uiLoadingSpinner" class="spinner"></div>';
        return '<div id="uiLoading" class="ui-loading" role="status" aria-live="polite">'
              .'<div class="backdrop" aria-hidden="true"></div>'
              .'<div class="pane">'.$icon.'<strong id="uiLoadingText">'.$text.'</strong></div>'
              .'</div>';
    }
}

if (!function_exists('ui_loading_script')) {
    function ui_loading_script(): void {
        echo '<script>'
           . 'window.uiLoading = {'
           . 'show:(t,img)=>{'
           . ' const el=document.getElementById("uiLoading"); if(!el) return;'
           . ' if(t){ const tx=document.getElementById("uiLoadingText"); if(tx) tx.textContent=t; }'
           . ' if(img){ let tag=document.getElementById("uiLoadingImg"); let sp=document.getElementById("uiLoadingSpinner");'
           . '   if(tag){ tag.src=img; }'
           . '   else if(sp){ sp.outerHTML = `<img id="uiLoadingImg" class="spinner-img" src="${img}" alt="Cargando">`; }'
           . ' }'
           . ' el.classList.add("active");'
           . '},'
           . 'hide:()=>{const el=document.getElementById("uiLoading"); if(!el) return; el.classList.remove("active");}'
           . '};'
           . '</script>';
    }
}

?>
