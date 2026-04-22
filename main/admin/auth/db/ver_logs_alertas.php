<?php
/**
 * Visor de logs de alertas enviadas
 * Ubicación: /invitrosoft/main/admin/auth/db/ver_logs_alertas.php
 */

require_once '../../../includes/auth_check.php';

$logFile = __DIR__ . '/alertas_enviadas.log';

// Parámetros de filtrado
$filtroTipo = $_GET['tipo'] ?? '';
$filtroReactivo = $_GET['reactivo'] ?? '';
$mostrarSolo = $_GET['mostrar'] ?? 'todos'; // todos, exitosos, fallidos

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs de Alertas - InvitroSoft</title>
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
            padding: 20px;
            color: var(--text-primary);
            min-height: 100vh;
        }
        
        .container {
            margin: 0 auto;
            background: var(--bg-card);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }
        
        .header {
            background: var(--gradient-primary);
            color: var(--text-inverse);
            padding: 28px 30px;
        }
        
        .header h1 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .header p {
            opacity: 0.95;
            font-size: 14px;
            font-weight: 400;
        }
        
        .filters {
            padding: 24px 30px;
            background: var(--bg-secondary);
            border-bottom: 2px solid var(--border-color);
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filter-group label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .filter-group select,
        .filter-group input {
            padding: 10px 14px;
            border: 2px solid var(--input-border);
            border-radius: var(--radius-md);
            font-size: 13px;
            background: var(--input-bg);
            color: var(--input-text);
            font-weight: 500;
            transition: var(--transition-fast);
            min-width: 140px;
        }
        
        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 120, 50, 0.1);
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-normal);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn:active {
            transform: scale(0.97);
        }
        
        .btn-primary {
            background: var(--btn-primary-bg);
            color: var(--btn-primary-text);
        }
        
        .btn-primary:hover {
            background: var(--btn-primary-hover);
            box-shadow: var(--shadow-md);
        }
        
        .btn-secondary {
            background: var(--btn-secondary-bg);
            color: var(--btn-secondary-text);
        }
        
        .btn-secondary:hover {
            background: var(--btn-secondary-hover);
        }
        
        .stats {
            padding: 24px 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            border-bottom: 2px solid var(--border-color);
        }
        
        .stat-card {
            padding: 20px;
            border-radius: var(--radius-lg);
            border-left: 4px solid;
            transition: var(--transition-normal);
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }
        
        .stat-card.success {
            background: var(--badge-success-bg);
            border-color: var(--success);
        }
        
        .stat-card.error {
            background: var(--badge-error-bg);
            border-color: var(--danger);
        }
        
        .stat-card.info {
            background: var(--badge-info-bg);
            border-color: var(--info);
        }
        
        .stat-card .label {
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 6px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-card .value {
            font-size: 28px;
            font-weight: 800;
            color: var(--text-primary);
        }
        
        .logs-table {
            padding: 0 30px 30px;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        
        thead {
            background: var(--table-header-bg);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        th {
            padding: 14px 12px;
            text-align: left;
            font-weight: 700;
            color: var(--table-header-text);
            border-bottom: 2px solid var(--table-border);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        
        td {
            padding: 14px 12px;
            border-bottom: 1px solid var(--table-border);
            color: var(--table-text);
        }
        
        tbody tr {
            transition: var(--transition-fast);
        }
        
        tbody tr:hover {
            background: var(--table-hover-bg);
        }
        
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 10px;
            border-radius: var(--radius-md);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .badge.success {
            background: var(--badge-success-bg);
            color: var(--badge-success-text);
        }
        
        .badge.error {
            background: var(--badge-error-bg);
            color: var(--badge-error-text);
        }
        
        .badge.stock {
            background: var(--badge-warning-bg);
            color: var(--badge-warning-text);
        }
        
        .badge.vencimiento {
            background: var(--badge-info-bg);
            color: var(--badge-info-text);
        }
        
        .empty {
            text-align: center;
            padding: 80px 20px;
            color: var(--text-tertiary);
        }
        
        .empty .icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.4;
        }
        
        .empty h3 {
            font-size: 20px;
            margin-bottom: 8px;
            color: var(--text-primary);
            font-weight: 600;
        }
        
        .empty p {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .reactivo-id {
            font-weight: 700;
            color: var(--primary);
            background: var(--badge-default-bg);
            padding: 4px 8px;
            border-radius: var(--radius-sm);
            display: inline-block;
        }
        
        .email-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .mensaje-cell {
            max-width: 300px;
            color: var(--text-secondary);
            font-size: 12px;
        }
        
        .fecha-cell {
            font-weight: 500;
            white-space: nowrap;
        }
        
        @media (max-width: 768px) {
            
            body{
                padding:0px;
            }
            .stats {
                grid-template-columns: 1fr;
            }
            
            .filters {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group select,
            .filter-group input {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="material-icons-round" style="font-size: 1.2em; vertical-align: middle;">email</i> Registro de Alertas por Correo</h1>
            <p>Historial completo de notificaciones enviadas del sistema InvitroSoft</p>
        </div>

        <div class="filters">
            <form method="GET" style="display: flex; gap: 16px; flex-wrap: wrap; width: 100%; align-items: center;">
                <div class="filter-group">
                    <label><i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle; margin-right: 4px;">sell</i> Tipo:</label>
                    <select name="tipo">
                        <option value="">Todos</option>
                        <option value="stock" <?= $filtroTipo === 'stock' ? 'selected' : '' ?>>Stock</option>
                        <option value="vencimiento" <?= $filtroTipo === 'vencimiento' ? 'selected' : '' ?>>Vencimiento</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle; margin-right: 4px;">bar_chart</i> Estado:</label>
                    <select name="mostrar">
                        <option value="todos" <?= $mostrarSolo === 'todos' ? 'selected' : '' ?>>Todos</option>
                        <option value="exitosos" <?= $mostrarSolo === 'exitosos' ? 'selected' : '' ?>>Exitosos</option>
                        <option value="fallidos" <?= $mostrarSolo === 'fallidos' ? 'selected' : '' ?>>Fallidos</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle; margin-right: 4px;">science</i> ID Reactivo:</label>
                    <input type="number" name="reactivo" placeholder="Ej: 5" value="<?= htmlspecialchars($filtroReactivo) ?>">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle;">search</i> Filtrar
                </button>
                <a href="?" class="btn btn-secondary">
                    <i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle;">clear</i> Limpiar
                </a>
            </form>
        </div>

        <?php
        if (!file_exists($logFile)) {
            echo '<div class="empty">
                <i class="material-icons-round" style="font-size: 64px; opacity: 0.4; margin-bottom: 20px;">email</i>
                <h3>No hay logs disponibles</h3>
                <p>Aún no se han enviado alertas por correo</p>
            </div>';
        } else {
            $lineas = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            // Estadísticas
            $total = 0;
            $exitosos = 0;
            $fallidos = 0;
            $porTipo = ['stock' => 0, 'vencimiento' => 0];
            
            $logs = [];
            
            foreach (array_reverse($lineas) as $linea) {
                $partes = explode('|', $linea);
                if (count($partes) < 7) continue;
                
                list($timestamp, $reactivo_id, $tipo, $hash, $exito, $email, $fecha_legible, $mensaje) = $partes;
                
                // Aplicar filtros
                if ($filtroTipo && $tipo !== $filtroTipo) continue;
                if ($filtroReactivo && (int)$reactivo_id !== (int)$filtroReactivo) continue;
                if ($mostrarSolo === 'exitosos' && $exito !== '1') continue;
                if ($mostrarSolo === 'fallidos' && $exito === '1') continue;
                
                $total++;
                if ($exito === '1') $exitosos++;
                else $fallidos++;
                
                $porTipo[$tipo] = ($porTipo[$tipo] ?? 0) + 1;
                
                $logs[] = [
                    'timestamp' => (int)$timestamp,
                    'reactivo_id' => $reactivo_id,
                    'tipo' => $tipo,
                    'exito' => $exito === '1',
                    'email' => $email,
                    'fecha' => $fecha_legible,
                    'mensaje' => $mensaje ?? '',
                    'estilo' => $exito ? 'success' : 'error',
                    'icono' => $exito ? '<i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle; color: var(--success);">check_circle</i>' : '<i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle; color: var(--danger);">error</i>',
                    'tipoIcono' => $tipo === 'stock' ? '<i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle;">inventory_2</i>' : '<i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle;">event</i>'
                ];
            }
            
            // Mostrar estadísticas
            echo '<div class="stats">';
            echo '<div class="stat-card info"><div class="label"><i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle; margin-right: 4px;">assessment</i> Total Alertas</div><div class="value">' . $total . '</div></div>';
            echo '<div class="stat-card success"><div class="label"><i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle; margin-right: 4px; color: var(--success);">check_circle</i> Exitosas</div><div class="value">' . $exitosos . '</div></div>';
            echo '<div class="stat-card error"><div class="label"><i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle; margin-right: 4px; color: var(--danger);">error</i> Fallidas</div><div class="value">' . $fallidos . '</div></div>';
            echo '</div>';
            
            // Mostrar tabla
            if (empty($logs)) {
                echo '<div class="empty">
                    <i class="material-icons-round" style="font-size: 64px; opacity: 0.4; margin-bottom: 20px;">search</i>
                    <h3>No hay resultados</h3>
                    <p>Intenta ajustar los filtros de búsqueda</p>
                </div>';
            } else {
                echo '<div class="logs-table">';
                echo '<table>';
                echo '<thead><tr>';
                echo '<th><i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle; margin-right: 4px;">event</i> Fecha</th>';
                echo '<th><i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle; margin-right: 4px;">science</i> Reactivo</th>';
                echo '<th><i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle; margin-right: 4px;">sell</i> Tipo</th>';
                echo '<th><i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle; margin-right: 4px;">assessment</i> Estado</th>';
                echo '<th><i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle; margin-right: 4px;">email</i> Destinatario</th>';
                echo '<th><i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle; margin-right: 4px;">chat_bubble</i> Mensaje</th>';
                echo '</tr></thead>';
                echo '<tbody>';
                
                foreach ($logs as $log) {
                    $estadoBadge = $log['exito'] 
                        ? '<span class="badge success"><i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle;">check_circle</i> Enviado</span>' 
                        : '<span class="badge error"><i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle;">error</i> Fallido</span>';
                    
                    $tipoBadge = $log['tipo'] === 'stock'
                        ? '<span class="badge stock"><i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle;">inventory_2</i> Stock</span>'
                        : '<span class="badge vencimiento"><i class="material-icons-round" style="font-size: 1.1em; vertical-align: middle;">event</i> Vencimiento</span>';
                    
                    echo '<tr>';
                    echo '<td class="fecha-cell">' . htmlspecialchars($log['fecha']) . '</td>';
                    echo '<td><span class="reactivo-id">#' . htmlspecialchars($log['reactivo_id']) . '</span></td>';
                    echo '<td>' . $tipoBadge . '</td>';
                    echo '<td>' . $estadoBadge . '</td>';
                    echo '<td class="email-cell" title="' . htmlspecialchars($log['email']) . '">' . htmlspecialchars($log['email']) . '</td>';
                    echo '<td class="mensaje-cell">' . htmlspecialchars($log['mensaje']) . '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody>';
                echo '</table>';
                echo '</div>';
            }
        }
        ?>
    </div>
</body>
</html>