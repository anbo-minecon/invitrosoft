<?php
class Notificacion {
    private $conn;

    public function __construct($conn = null) {
        if ($conn === null) {
            require_once '../../db/conexion.php';
            $this->conn = $GLOBALS['conn'] ?? null;
        } else {
            $this->conn = $conn;
        }
        
        if (!$this->conn) {
            throw new Exception("No se pudo establecer la conexión a la base de datos");
        }
    }

    public function registrar($datos) {
        if (!is_array($datos)) {
            throw new Exception("Los datos deben ser un array");
        }
        
        // Valores por defecto
        $datos = array_merge([
            'planta_id' => null,
            'modulo' => 'sistema',
            'accion' => 'notificacion',
            'entidad' => null,
            'entidad_id' => null,
            'tipo' => 'info',
            'leida' => 0,
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'fecha_lectura' => null,
            'datos_adicionales' => null
        ], $datos);

        // Validar campos requeridos
        $camposRequeridos = ['usuario_id', 'titulo', 'mensaje', 'tipo'];
        foreach ($camposRequeridos as $campo) {
            if (!isset($datos[$campo]) || $datos[$campo] === '') {
                throw new Exception("El campo '$campo' es requerido");
            }
        }

        try {
            $sql = "INSERT INTO notificaciones 
                   (usuario_id, titulo, mensaje, tipo, modulo, accion, entidad, entidad_id, fecha_creacion, leida) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 0)";

            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
            }

            $stmt->bind_param(
                "issssssi", 
                $datos['usuario_id'],
                $datos['titulo'],
                $datos['mensaje'],
                $datos['tipo'],
                $datos['modulo'],
                $datos['accion'],
                $datos['entidad'],
                $datos['entidad_id']
            );

            $resultado = $stmt->execute();
            
            if (!$resultado) {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }

            return $this->conn->insert_id;

        } catch (Exception $e) {
            error_log("Error en Notificacion::registrar: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener notificaciones para el header/dropdown (limitadas a 5)
     */
    public function obtenerPorUsuario($usuarioId, $limite = 5, $soloNoLeidas = false) {
        $sql = "SELECT n.*, 
                CONCAT(u.nombre) as nombre_usuario
                FROM notificaciones n
                LEFT JOIN usuarios u ON n.usuario_id = u.id
                WHERE n.usuario_id = ? ";
        
        if ($soloNoLeidas) {
            $sql .= " AND n.leida = 0";
        }
        
        $sql .= " ORDER BY n.fecha_creacion DESC LIMIT ?";
        
        error_log("=== obtenerPorUsuario (HEADER) ===");
        error_log("Usuario: $usuarioId, Límite: $limite, Solo no leídas: " . ($soloNoLeidas ? 'SI' : 'NO'));
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando consulta: " . $this->conn->error);
            return [];
        }
        
        $stmt->bind_param("ii", $usuarioId, $limite);
        
        if (!$stmt->execute()) {
            error_log("Error ejecutando consulta: " . $stmt->error);
            return [];
        }
        
        $result = $stmt->get_result();
        $notificaciones = $result->fetch_all(MYSQLI_ASSOC);
        
        error_log("Notificaciones obtenidas: " . count($notificaciones));
        
        return $notificaciones;
    }

    /**
     * Obtener TODAS las notificaciones de un usuario (sin paginación)
     */
    public function obtenerTodasPorUsuario($usuarioId, $soloNoLeidas = false) {
        $usuarioId = (int)$usuarioId;
        
        // Consulta principal sin límite
        $sql = "SELECT n.*,
                DATE_FORMAT(n.fecha_creacion, '%Y-%m-%d %H:%i:%s') as fecha_creacion,
                DATE_FORMAT(n.fecha_lectura, '%Y-%m-%d %H:%i:%s') as fecha_lectura
                FROM notificaciones n
                WHERE n.usuario_id = ?";
        
        if ($soloNoLeidas) {
            $sql .= " AND n.leida = 0";
        }
        
        $sql .= " ORDER BY n.fecha_creacion DESC";
        
        error_log("=== obtenerTodasPorUsuario ===");
        error_log("SQL: $sql");
        error_log("Usuario ID: $usuarioId");
        error_log("Solo no leídas: " . ($soloNoLeidas ? 'SI' : 'NO'));
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("❌ Error preparando statement: " . $this->conn->error);
            return [];
        }
        
        if (!$stmt->bind_param("i", $usuarioId)) {
            error_log("❌ Error en bind_param: " . $stmt->error);
            return [];
        }
        
        if (!$stmt->execute()) {
            error_log("❌ Error ejecutando query: " . $stmt->error);
            return [];
        }
        
        $result = $stmt->get_result();
        if (!$result) {
            error_log("❌ Error obteniendo resultado: " . $this->conn->error);
            return [];
        }
        
        $notificaciones = $result->fetch_all(MYSQLI_ASSOC);
        error_log("✅ Total de notificaciones obtenidas: " . count($notificaciones));
        
        return $notificaciones;
    }

    /**
     * Obtener notificaciones paginadas para el historial completo
     * MÉTODO CORREGIDO - PROBLEMA RESUELTO
     */
    public function obtenerPorUsuarioPaginado($usuarioId, $porPagina = 20, $offset = 0, $soloNoLeidas = false) {
    // Limpiar y convertir a enteros
    $usuarioId = (int)$usuarioId;
    $porPagina = (int)$porPagina;
    $offset = (int)$offset;
    
    error_log("========================================");
    error_log("MÉTODO: obtenerPorUsuarioPaginado");
    error_log("Usuario: $usuarioId");
    error_log("Por página: $porPagina");
    error_log("Offset: $offset");
    error_log("Solo no leídas: " . ($soloNoLeidas ? 'SI' : 'NO'));
    
    // Construir query base
    $sql = "SELECT 
                n.*,
                DATE_FORMAT(n.fecha_creacion, '%Y-%m-%d %H:%i:%s') as fecha_creacion,
                DATE_FORMAT(n.fecha_lectura, '%Y-%m-%d %H:%i:%s') as fecha_lectura
            FROM notificaciones n
            WHERE n.usuario_id = ?";
    
    if ($soloNoLeidas) {
        $sql .= " AND n.leida = 0";
    }
    
    $sql .= " ORDER BY n.fecha_creacion DESC";
    $sql .= " LIMIT ? OFFSET ?";
    
    error_log("SQL: $sql");
    
    // Preparar statement
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        error_log("❌ ERROR prepare: " . $this->conn->error);
        return [];
    }
    
    // Bind parameters
    if (!$stmt->bind_param("iii", $usuarioId, $porPagina, $offset)) {
        error_log("❌ ERROR bind_param: " . $stmt->error);
        $stmt->close();
        return [];
    }
    
    // Execute
    if (!$stmt->execute()) {
        error_log("❌ ERROR execute: " . $stmt->error);
        $stmt->close();
        return [];
    }
    
    // Get result
    $result = $stmt->get_result();
    if (!$result) {
        error_log("❌ ERROR get_result: " . $this->conn->error);
        $stmt->close();
        return [];
    }
    
    // Fetch all
    $notificaciones = [];
    while ($row = $result->fetch_assoc()) {
        $notificaciones[] = $row;
    }
    
    $stmt->close();
    
    error_log("✅ Notificaciones obtenidas: " . count($notificaciones));
    
    if (!empty($notificaciones)) {
        $ids = array_column($notificaciones, 'id');
        error_log("✅ IDs: " . implode(', ', array_slice($ids, 0, 10)) . (count($ids) > 10 ? '...' : ''));
    } else {
        // Si no hay resultados, verificar por qué
        $checkSql = "SELECT COUNT(*) as total FROM notificaciones WHERE usuario_id = ?";
        $checkStmt = $this->conn->prepare($checkSql);
        $checkStmt->bind_param("i", $usuarioId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $checkRow = $checkResult->fetch_assoc();
        error_log("⚠️ Total en BD: " . $checkRow['total']);
        $checkStmt->close();
    }
    
    error_log("========================================");
    
    return $notificaciones;
}

    /**
     * Marcar notificación como leída
     */
    public function marcarComoLeida($notificacionId, $usuarioId) {
        $notificacionId = (int)$notificacionId;
        $usuarioId = (int)$usuarioId;
        
        $sql = "UPDATE notificaciones 
                SET leida = 1, 
                    fecha_lectura = NOW() 
                WHERE id = ? AND usuario_id = ?";
        
        error_log("Marcando notificación $notificacionId como leída para usuario $usuarioId");
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando consulta: " . $this->conn->error);
            return false;
        }
        
        $stmt->bind_param("ii", $notificacionId, $usuarioId);
        $resultado = $stmt->execute();
        
        if ($resultado) {
            error_log("✅ Notificación marcada como leída correctamente");
        } else {
            error_log("❌ Error al marcar notificación: " . $stmt->error);
        }
        
        return $resultado;
    }

    /**
     * Contar notificaciones no leídas
     */
    public function contarNoLeidas($usuarioId) {
        $usuarioId = (int)$usuarioId;
        
        $sql = "SELECT COUNT(*) as total 
                FROM notificaciones 
                WHERE usuario_id = ? AND leida = 0";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando consulta contarNoLeidas: " . $this->conn->error);
            return 0;
        }
        
        $stmt->bind_param("i", $usuarioId);
        
        if (!$stmt->execute()) {
            error_log("Error ejecutando contarNoLeidas: " . $stmt->error);
            return 0;
        }
        
        $result = $stmt->get_result();
        if (!$result) {
            error_log("Error obteniendo resultado contarNoLeidas: " . $this->conn->error);
            return 0;
        }
        
        $row = $result->fetch_assoc();
        $total = $row ? (int)$row['total'] : 0;
        
        error_log("📊 Total no leídas para usuario $usuarioId: $total");
        
        return $total;
    }
    
    /**
     * Contar TODAS las notificaciones (leídas y no leídas)
     */
    public function contarTodas($usuarioId) {
        $usuarioId = (int)$usuarioId;
        
        $sql = "SELECT COUNT(*) as total 
                FROM notificaciones 
                WHERE usuario_id = ?";
        
        error_log("=== contarTodas ===");
        error_log("Contando TODAS las notificaciones para usuario: $usuarioId");
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("❌ Error preparando consulta: " . $this->conn->error);
            return 0;
        }
        
        $stmt->bind_param("i", $usuarioId);
        
        if (!$stmt->execute()) {
            error_log("❌ Error ejecutando consulta: " . $stmt->error);
            return 0;
        }
        
        $result = $stmt->get_result();
        if (!$result) {
            error_log("❌ Error obteniendo resultado: " . $this->conn->error);
            return 0;
        }
        
        $row = $result->fetch_assoc();
        $total = $row ? (int)$row['total'] : 0;
        
        error_log("✅ Total de TODAS las notificaciones: $total");
        
        return $total;
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function marcarTodasComoLeidas($usuarioId) {
        $usuarioId = (int)$usuarioId;
        
        $sql = "UPDATE notificaciones 
                SET leida = 1, 
                    fecha_lectura = NOW() 
                WHERE usuario_id = ? AND leida = 0";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("i", $usuarioId);
        return $stmt->execute();
    }

    /**
     * Eliminar notificaciones antiguas (opcional, para mantenimiento)
     */
    public function eliminarAntiguas($usuarioId, $diasAntiguedad = 90) {
        $usuarioId = (int)$usuarioId;
        $diasAntiguedad = (int)$diasAntiguedad;
        
        $sql = "DELETE FROM notificaciones 
                WHERE usuario_id = ? 
                AND leida = 1 
                AND fecha_creacion < DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("ii", $usuarioId, $diasAntiguedad);
        return $stmt->execute();
    }
}
?>