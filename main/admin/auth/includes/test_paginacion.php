<?php
session_start();
require_once '../../db/conexion.php';
require_once 'Notificacion.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    die("No hay sesión activa");
}

$userId = $_SESSION['user_id'];
echo "<h1>Test de Paginación</h1>";
echo "<p><strong>Usuario ID:</strong> $userId</p>";

try {
    $notificacion = new Notificacion($conn);
    
    // Test 1: Contar total
    echo "<h2>Test 1: Contar total de notificaciones</h2>";
    $total = $notificacion->contarTodas($userId);
    echo "<p>✅ Total: <strong>$total</strong> notificaciones</p>";
    
    // Test 2: Obtener primeras 5
    echo "<h2>Test 2: Obtener primeras 5 notificaciones</h2>";
    $notif5 = $notificacion->obtenerPorUsuarioPaginado($userId, 5, 0, false);
    echo "<p>✅ Obtenidas: <strong>" . count($notif5) . "</strong> notificaciones</p>";
    if (!empty($notif5)) {
        echo "<ul>";
        foreach ($notif5 as $n) {
            echo "<li>ID: {$n['id']} - {$n['titulo']}</li>";
        }
        echo "</ul>";
    }
    
    // Test 3: Obtener primeras 20
    echo "<h2>Test 3: Obtener primeras 20 notificaciones</h2>";
    $notif20 = $notificacion->obtenerPorUsuarioPaginado($userId, 20, 0, false);
    echo "<p>✅ Obtenidas: <strong>" . count($notif20) . "</strong> notificaciones</p>";
    if (!empty($notif20)) {
        echo "<p>Primera: ID {$notif20[0]['id']} - {$notif20[0]['titulo']}</p>";
        echo "<p>Última: ID {$notif20[count($notif20)-1]['id']} - {$notif20[count($notif20)-1]['titulo']}</p>";
    }
    
    // Test 4: Obtener segunda página (offset 20)
    echo "<h2>Test 4: Obtener segunda página (offset 20)</h2>";
    $notif20_p2 = $notificacion->obtenerPorUsuarioPaginado($userId, 20, 20, false);
    echo "<p>✅ Obtenidas: <strong>" . count($notif20_p2) . "</strong> notificaciones</p>";
    if (!empty($notif20_p2)) {
        echo "<p>Primera: ID {$notif20_p2[0]['id']} - {$notif20_p2[0]['titulo']}</p>";
        echo "<p>Última: ID {$notif20_p2[count($notif20_p2)-1]['id']} - {$notif20_p2[count($notif20_p2)-1]['titulo']}</p>";
    }
    
    // Test 5: Query SQL directa
    echo "<h2>Test 5: Query SQL directa</h2>";
    $sql = "SELECT id, titulo FROM notificaciones WHERE usuario_id = ? ORDER BY fecha_creacion DESC LIMIT 20 OFFSET 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $directResults = $result->fetch_all(MYSQLI_ASSOC);
    echo "<p>✅ Resultados directos: <strong>" . count($directResults) . "</strong></p>";
    
    if (count($notif20) != count($directResults)) {
        echo "<p style='color: red;'>⚠️ PROBLEMA: El método obtenerPorUsuarioPaginado no devuelve la misma cantidad que la query directa</p>";
    } else {
        echo "<p style='color: green;'>✅ CORRECTO: Ambos métodos devuelven la misma cantidad</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>Logs de PHP</h2>";
echo "<pre>";
echo "Revisa el archivo de error log de PHP para ver los mensajes de debug.
";
echo "Ubicaciones comunes:
";
echo "- /var/log/apache2/error.log
";
echo "- /var/log/php/error.log
";
echo "- xampp/apache/logs/error.log (Windows)
";
echo "</pre>";
?>


