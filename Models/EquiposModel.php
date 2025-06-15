<?php
// Models/EquiposModel.php
require_once '../Config/database.php';

class EquiposModel {
    private $conn;
    private $db;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    // ========== GESTIÓN DE EQUIPOS ==========
    
    public function crearEquipo($datos) {
        // Verificar límite de equipos por deporte (máximo 5)
        $sql = "SELECT COUNT(*) as total FROM equipo_miembros em
                INNER JOIN equipos e ON em.equipo_id = e.id
                WHERE em.usuario_id = ? AND e.deporte_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $datos['creador_id'], $datos['deporte_id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['total'] >= 5) {
            return ['success' => false, 'message' => 'Límite máximo de 5 equipos por deporte alcanzado'];
        }
        
        // Verificar que el nombre no esté duplicado en el mismo deporte
        $sql = "SELECT COUNT(*) as total FROM equipos 
                WHERE nombre = ? AND deporte_id = ? AND estado = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $datos['nombre'], $datos['deporte_id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['total'] > 0) {
            return ['success' => false, 'message' => 'Ya existe un equipo con ese nombre en este deporte'];
        }
        
        $this->conn->begin_transaction();
        
        try {
            // Crear equipo
            $sql = "INSERT INTO equipos (nombre, descripcion, deporte_id, creador_id, limite_miembros, privado) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssiiii", 
                $datos['nombre'], 
                $datos['descripcion'], 
                $datos['deporte_id'], 
                $datos['creador_id'], 
                $datos['limite_miembros'], 
                $datos['privado']
            );
            $stmt->execute();
            $equipo_id = $this->conn->insert_id;
            
            // Agregar creador como miembro con rol de creador
            $sql = "INSERT INTO equipo_miembros (equipo_id, usuario_id, rol, fecha_union) 
                    VALUES (?, ?, 'creador', NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $equipo_id, $datos['creador_id']);
            $stmt->execute();
            
            $this->conn->commit();
            return [
                'success' => true, 
                'message' => 'Equipo creado correctamente', 
                'equipo_id' => $equipo_id
            ];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'Error al crear equipo: ' . $e->getMessage()];
        }
    }
    
    public function obtenerEquiposUsuario($usuario_id, $deporte_id = null) {
        $sql = "SELECT 
                    e.id,
                    e.nombre,
                    e.descripcion,
                    e.limite_miembros,
                    e.privado,
                    e.creado_en,
                    d.nombre as deporte_nombre,
                    d.id as deporte_id,
                    em.rol,
                    COUNT(em2.id) as total_miembros
                FROM equipos e
                INNER JOIN equipo_miembros em ON e.id = em.equipo_id
                INNER JOIN deportes d ON e.deporte_id = d.id
                LEFT JOIN equipo_miembros em2 ON e.id = em2.equipo_id
                WHERE em.usuario_id = ? 
                AND e.estado = 1";
        
        $params = [$usuario_id];
        $types = "i";
        
        if ($deporte_id) {
            $sql .= " AND e.deporte_id = ?";
            $params[] = $deporte_id;
            $types .= "i";
        }
        
        $sql .= " GROUP BY e.id, e.nombre, e.descripcion, e.limite_miembros, e.privado, e.creado_en, d.nombre, d.id, em.rol
                  ORDER BY d.nombre, e.nombre";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function obtenerMiembrosEquipo($equipo_id) {
        $sql = "SELECT 
                    u.id, 
                    u.nombre, 
                    u.apellidos, 
                    u.username, 
                    u.email,
                    em.rol, 
                    em.fecha_union,
                    CASE em.rol 
                        WHEN 'creador' THEN 1 
                        WHEN 'administrador' THEN 2 
                        ELSE 3 
                    END as orden_rol
                FROM equipo_miembros em
                INNER JOIN usuarios_deportistas u ON em.usuario_id = u.id
                WHERE em.equipo_id = ? AND u.estado = 1
                ORDER BY orden_rol, u.nombre, u.apellidos";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $equipo_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function obtenerDeportes() {
        $sql = "SELECT id, nombre, descripcion FROM deportes WHERE estado = 1 ORDER BY nombre";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function verificarMiembroEquipo($equipo_id, $usuario_id) {
        $sql = "SELECT rol FROM equipo_miembros WHERE equipo_id = ? AND usuario_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $equipo_id, $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : false;
    }
    public function verificarPermisosCreador($equipo_id, $usuario_id) {
        $sql = "SELECT rol FROM equipo_miembros 
                WHERE equipo_id = ? AND usuario_id = ? AND rol = 'creador'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $equipo_id, $usuario_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
    
    public function obtenerAmigosDisponiblesParaEquipo($equipo_id, $usuario_id) {
        $sql = "SELECT DISTINCT 
                    u.id,
                    u.nombre,
                    u.apellidos,
                    u.username,
                    u.email
                FROM usuarios_deportistas u
                INNER JOIN amistades a ON (
                    (a.usuario_solicitante_id = ? AND a.usuario_receptor_id = u.id) OR
                    (a.usuario_receptor_id = ? AND a.usuario_solicitante_id = u.id)
                )
                WHERE a.estado = 'aceptada'
                AND u.estado = 1
                AND u.id NOT IN (
                    SELECT usuario_id FROM equipo_miembros WHERE equipo_id = ?
                )
                ORDER BY u.nombre, u.apellidos";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $usuario_id, $usuario_id, $equipo_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function agregarMiembroAEquipo($equipo_id, $amigo_id, $creador_id) {
        // Verificar que el usuario sea creador del equipo
        if (!$this->verificarPermisosCreador($equipo_id, $creador_id)) {
            return ['success' => false, 'message' => 'Solo el creador puede añadir miembros'];
        }
        
        // Verificar que son amigos
        $sql = "SELECT COUNT(*) as total FROM amistades 
                WHERE ((usuario_solicitante_id = ? AND usuario_receptor_id = ?) OR 
                       (usuario_receptor_id = ? AND usuario_solicitante_id = ?))
                AND estado = 'aceptada'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $creador_id, $amigo_id, $creador_id, $amigo_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['total'] == 0) {
            return ['success' => false, 'message' => 'Solo puedes añadir amigos al equipo'];
        }
        
        // Verificar límite de miembros
        $sql = "SELECT 
                    e.limite_miembros,
                    COUNT(em.usuario_id) as total_miembros
                FROM equipos e
                LEFT JOIN equipo_miembros em ON e.id = em.equipo_id
                WHERE e.id = ?
                GROUP BY e.id, e.limite_miembros";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $equipo_id);
        $stmt->execute();
        $equipo = $stmt->get_result()->fetch_assoc();
        
        if ($equipo['total_miembros'] >= $equipo['limite_miembros']) {
            return ['success' => false, 'message' => 'El equipo ha alcanzado el límite de miembros'];
        }
        
        // Verificar que el amigo no esté ya en el equipo
        $sql = "SELECT COUNT(*) as total FROM equipo_miembros 
                WHERE equipo_id = ? AND usuario_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $equipo_id, $amigo_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['total'] > 0) {
            return ['success' => false, 'message' => 'Este usuario ya pertenece al equipo'];
        }
        
        // Añadir al equipo
        $sql = "INSERT INTO equipo_miembros (equipo_id, usuario_id, rol, fecha_union) 
                VALUES (?, ?, 'miembro', NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $equipo_id, $amigo_id);
        
        if ($stmt->execute()) {
            // Obtener datos del amigo añadido
            $sql = "SELECT nombre, apellidos, username FROM usuarios_deportistas WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $amigo_id);
            $stmt->execute();
            $amigo = $stmt->get_result()->fetch_assoc();
            
            return [
                'success' => true,
                'message' => "{$amigo['nombre']} {$amigo['apellidos']} ha sido añadido al equipo",
                'miembro_añadido' => $amigo
            ];
        } else {
            return ['success' => false, 'message' => 'Error al añadir miembro al equipo'];
        }
    }
    
    public function eliminarMiembroEquipo($equipo_id, $miembro_id, $creador_id) {
        // Verificar que el usuario sea creador del equipo
        if (!$this->verificarPermisosCreador($equipo_id, $creador_id)) {
            return ['success' => false, 'message' => 'Solo el creador puede eliminar miembros'];
        }
        
        // No puede eliminarse a sí mismo (creador)
        if ($miembro_id == $creador_id) {
            return ['success' => false, 'message' => 'El creador no puede eliminarse del equipo'];
        }
        
        // Eliminar miembro
        $sql = "DELETE FROM equipo_miembros 
                WHERE equipo_id = ? AND usuario_id = ? AND rol != 'creador'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $equipo_id, $miembro_id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            return ['success' => true, 'message' => 'Miembro eliminado del equipo'];
        } else {
            return ['success' => false, 'message' => 'Error al eliminar miembro o miembro no encontrado'];
        }
    }
    
    public function __destruct() {
        if ($this->db) {
            $this->db->closeConnection();
        }
    }
}
?>