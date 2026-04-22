<?php
/**
 * Script para limpiar logs antiguos de alertas
 * Ubicación: /invitrosoft/main/admin/auth/db/limpiar_logs_alertas.php
 */

require_once '../../../includes/auth_check.php';

// Solo administradores pueden limpiar logs
if (!isset($_SESSION['user_tipo']) || $_SESSION['user_tipo'] !== 'admin') {
    die(json_encode(['success' => false, 'error' => 'Acceso denegado']));
}

$logFile = __DIR__ . '/alertas_enviadas.log';

// Procesar acción
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'limpiar_antiguos') {
        // Eliminar logs de más de X días
        $dias = (int)($_POST['dias'] ?? 7);
        
        if (!file_exists($logFile)) {
            echo json_encode(['success' => false, 'error' => 'Archivo de logs no existe']);
            exit;
        }
        
        try {
            $lineas = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $timestamp_limite = time() - ($dias * 24 * 60 * 60);
            $lineas_nuevas = [];
            $eliminadas = 0;
            
            foreach ($lineas as $linea) {
                $partes = explode('|', $linea);
                if (count($partes) > 0 && (int)$partes[0] >= $timestamp_limite) {
                    $lineas_nuevas[] = $linea;
                } else {
                    $eliminadas++;
                }
            }
            
            file_put_contents($logFile, implode("\n", $lineas_nuevas) . "\n", LOCK_EX);
            
            echo json_encode([
                'success' => true,
                'mensaje' => "Se eliminaron $eliminadas registros antiguos",
                'restantes' => count($lineas_nuevas)
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        
    } elseif ($accion === 'limpiar_todo') {
        // Eliminar todos los logs
        try {
            if (file_exists($logFile)) {
                $count = count(file($logFile));
                // Borrar el archivo y recrearlo vacío para que el sistema siga encontrando el archivo
                unlink($logFile);
                // Recrear archivo vacío
                file_put_contents($logFile, "", LOCK_EX);
                echo json_encode([
                    'success' => true,
                    'mensaje' => "Se eliminaron $count registros",
                    'restantes' => 0
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No hay logs para eliminar']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        
    } elseif ($accion === 'exportar') {
        // Exportar logs a CSV
        if (!file_exists($logFile)) {
            echo json_encode(['success' => false, 'error' => 'No hay logs para exportar']);
            exit;
        }
        
        $lineas = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="logs_alertas_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');

        // Escribir BOM UTF-8 para que Excel detecte correctamente la codificación
        fwrite($output, "\xEF\xBB\xBF");
        // Forzar a Excel a usar ';' como separador
        fwrite($output, "sep=;\r\n");

        // Encabezados CSV (usando ';' como separador para locales que usan coma decimal)
        fputcsv($output, ['Timestamp', 'Reactivo ID', 'Tipo', 'Hash', 'Exito', 'Email', 'Fecha', 'Mensaje'], ';');

        foreach ($lineas as $linea) {
            // Dividir en un máximo de 8 partes para que el campo 'mensaje' pueda contener '|' sin romper columnas
            $partes = explode('|', $linea, 8);
            // Asegurar exactamente 8 columnas rellenando con cadenas vacías si faltan
            if (count($partes) < 8) {
                $partes = array_pad($partes, 8, '');
            }
            // Escribir fila CSV con separador ';' (fputcsv se encarga de escapado y comillas)
            fputcsv($output, $partes, ';');
        }
        
        fclose($output);
        exit;
        
    } else {
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
    }
    
    exit;
}

// Obtener estadísticas
$stats = [
    'total' => 0,
    'exitosos' => 0,
    'fallidos' => 0,
    'tamano' => 0,
    'mas_antiguo' => null,
    'mas_reciente' => null
];

if (file_exists($logFile)) {
    $lineas = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $stats['total'] = count($lineas);
    $stats['tamano'] = round(filesize($logFile) / 1024, 2); // KB
    
    $timestamps = [];
    
    foreach ($lineas as $linea) {
        $partes = explode('|', $linea);
        if (count($partes) < 5) continue;
        
        if ($partes[4] === '1') $stats['exitosos']++;
        else $stats['fallidos']++;
        
        $timestamps[] = (int)$partes[0];
    }
    
    if (!empty($timestamps)) {
        $stats['mas_antiguo'] = date('d/m/Y H:i', min($timestamps));
        $stats['mas_reciente'] = date('d/m/Y H:i', max($timestamps));
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Limpieza de Logs - InvitroSoft</title>
    <link rel="stylesheet" href="../../css/color_variable.css">
    <link rel="icon" href="logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: var(--bg-secondary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-primary);
        }
        
        .container {
            width: 100%;
            background: var(--bg-card);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }
        
        .header {
            background: var(--gradient-primary);
            color: var(--text-inverse);
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .header p {
            opacity: 0.95;
            font-size: 14px;
        }
        
        .content {
            padding: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            padding: 18px;
            border-radius: var(--radius-lg);
            background: var(--bg-secondary);
            border-left: 4px solid var(--primary);
            transition: var(--transition-normal);
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .stat-card .label {
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 6px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-card .value {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
        }
        
        .action-section {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .action-section:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }
        
        .action-section h3 {
            font-size: 16px;
            margin-bottom: 8px;
            color: var(--text-primary);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .action-section p {
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .form-group {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .form-group label {
            font-size: 13px;
            color: var(--text-primary);
            font-weight: 500;
        }
        
        .form-group input {
            padding: 10px 14px;
            border: 2px solid var(--input-border);
            border-radius: var(--radius-md);
            font-size: 14px;
            width: 80px;
            background: var(--input-bg);
            color: var(--input-text);
            font-weight: 600;
            transition: var(--transition-fast);
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 120, 50, 0.1);
        }
        
        .form-group span {
            font-size: 13px;
            color: var(--text-secondary);
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-normal);
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn:active {
            transform: scale(0.98);
        }
        
        .btn-primary {
            background: var(--btn-primary-bg);
            color: var(--btn-primary-text);
        }
        
        .btn-primary:hover {
            background: var(--btn-primary-hover);
            box-shadow: var(--shadow-md);
        }
        
        .btn-danger {
            background: var(--btn-danger-bg);
            color: var(--btn-danger-text);
        }
        
        .btn-danger:hover {
            background: var(--btn-danger-hover);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        
        .btn-success {
            background: var(--btn-success-bg);
            color: var(--btn-success-text);
        }
        
        .btn-success:hover {
            background: var(--btn-success-hover);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .alert {
            padding: 14px 18px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
            font-weight: 500;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert.success {
            background: var(--badge-success-bg);
            color: var(--badge-success-text);
            border-left: 4px solid var(--success);
        }
        
        .alert.error {
            background: var(--badge-error-bg);
            color: var(--badge-error-text);
            border-left: 4px solid var(--danger);
        }
        
        .date-range {
            font-size: 12px;
            color: var(--text-tertiary);
            text-align: center;
            margin-bottom: 30px;
            padding: 12px;
            background: var(--bg-secondary);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
        }
        
        /* Iconos con emoji */
        .icon {
            font-size: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="material-icons-round" style="font-size: 1.2em; vertical-align: middle;">delete_sweep</i> Limpieza de Logs</h1>
            <p>Gestión de registros de alertas del sistema</p>
        </div>
        
        <div class="content">
            <div id="alertBox" class="alert"></div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="label">Total Registros</div>
                    <div class="value"><?= $stats['total'] ?></div>
                </div>
                <div class="stat-card">
                    <i class="material-icons-round" style="font-size: 2em; color: var(--primary);">assessment</i>
                    <div class="value"><?= $stats['tamano'] ?> KB</div>
                </div>
                <div class="stat-card">
                    <div class="label">Exitosos</div>
                    <div class="value"><?= $stats['exitosos'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="label">Fallidos</div>
                    <div class="value"><?= $stats['fallidos'] ?></div>
                </div>
            </div>
            
            <?php if ($stats['mas_antiguo']): ?>
                <div class="date-range">
                    <i class="material-icons-round" style="font-size: 1.2em; vertical-align: middle;">calendar_today</i> Del <?= $stats['mas_antiguo'] ?> al <?= $stats['mas_reciente'] ?>
                </div>
            <?php endif; ?>
            
            <div class="action-section">
                <h3><i class="material-icons-round" style="font-size: 1.2em; vertical-align: middle;">calendar_today</i> Limpiar Logs Antiguos</h3>
                <p>Eliminar registros anteriores a cierta cantidad de días</p>
                <div class="form-group">
                    <label>Eliminar logs de más de:</label>
                    <input type="number" id="diasInput" value="7" min="1" max="365">
                    <span>días</span>
                </div>
                <button type="button" class="btn btn-primary" onclick="limpiarLogs('limpiar_antiguos')">
                    <i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle;">cleaning_services</i> Limpiar Antiguos
                </button>
            </div>
            
            <div class="action-section">
                <h3><i class="material-icons-round" style="font-size: 1.2em; vertical-align: middle;">get_app</i> Exportar Logs</h3>
                <p>Descargar todos los registros en formato CSV</p>
                <form method="POST" style="margin: 0;">
                    <input type="hidden" name="accion" value="exportar">
                    <button type="submit" class="btn btn-success">
                        <i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle;">get_app</i> Exportar a CSV
                    </button>
                </form>
            </div>
            
            <div class="action-section">
                <h3><i class="material-icons-round" style="font-size: 1.2em; vertical-align: middle;">warning</i> Eliminar Todos los Logs</h3>
                <p>Esta acción eliminará permanentemente todos los registros</p>
                <button type="button" class="btn btn-danger" onclick="limpiarLogs('limpiar_todo')">
                    <i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle;">delete_forever</i> Eliminar Todo
                </button>
            </div>
        </div>
    </div>
    
    <script>
        /* ... */
        function mostrarAlerta(mensaje, tipo) {
            const alertBox = document.getElementById('alertBox');
            alertBox.textContent = mensaje;
            alertBox.className = 'alert ' + tipo;
            alertBox.style.display = 'block';
            
            setTimeout(() => {
                alertBox.style.display = 'none';
            }, 5000);
        }
        
        async function limpiarAntiguos() {
            const dias = document.getElementById('diasInput').value;
            
            if (!dias || dias < 1) {
                mostrarAlerta('⚠️ Ingresa un número válido de días', 'error');
                return;
            }
            
            if (!confirm(`¿Eliminar registros de más de ${dias} días?`)) return;
            
            try {
                const formData = new FormData();
                formData.append('accion', 'limpiar_antiguos');
                formData.append('dias', dias);

                const response = await fetch('', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await response.json();
                
                if (data.success) {
                    mostrarAlerta('✅ ' + data.mensaje, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    mostrarAlerta('❌ Error: ' + data.error, 'error');
                }
            } catch (error) {
                mostrarAlerta('❌ Error al procesar solicitud', 'error');
            }
        }
        
        async function confirmarLimpiezaTotal() {
            if (!confirm('⚠️ ¿Estás seguro de eliminar TODOS los logs?\n\nEsta acción no se puede deshacer.')) {
                return;
            }
            
            if (!confirm('⚠️ Esta es tu última oportunidad. ¿Continuar?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('accion', 'limpiar_todo');
                
                const response = await fetch('', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await response.json();
                
                if (data.success) {
                    mostrarAlerta('✅ ' + data.mensaje, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    mostrarAlerta('❌ Error: ' + data.error, 'error');
                }
            } catch (error) {
                mostrarAlerta('❌ Error al procesar solicitud', 'error');
            }
        }

        // Manejador genérico para los botones del UI
        function limpiarLogs(action) {
            if (action === 'limpiar_antiguos') {
                limpiarAntiguos();
                return;
            }
            if (action === 'limpiar_todo') {
                confirmarLimpiezaTotal();
                return;
            }
            mostrarAlerta('Acción no válida', 'error');
        }
    </script>
</body>
</html>