<?php
require_once '../Config/database.php';

class ChatModel {
    private $conn;
    private $db;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    // ========== GESTIÓN DE AMISTADES ==========
    
    public function enviarSolicitudAmistad($solicitante_id, $receptor_id) {
        // Verificar que no sean el mismo usuario
        if ($solicitante_id == $receptor_id) {
            return ['success' => false, 'message' => 'No puedes enviarte una solicitud a ti mismo'];
        }
        
        // Verificar si ya existe una amistad o solicitud pendiente
        $sql = "SELECT id, estado FROM amistades 
                WHERE (usuario_solicitante_id = ? AND usuario_receptor_id = ?) 
                OR (usuario_solicitante_id = ? AND usuario_receptor_id = ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $solicitante_id, $receptor_id, $receptor_id, $solicitante_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $amistad = $result->fetch_assoc();
            if ($amistad['estado'] == 'pendiente') {
                return ['success' => false, 'message' => 'Ya existe una solicitud pendiente'];
            } elseif ($amistad['estado'] == 'aceptada') {
                return ['success' => false, 'message' => 'Ya son amigos'];
            }
        }
        
        // Insertar nueva solicitud
        $sql = "INSERT INTO amistades (usuario_solicitante_id, usuario_receptor_id, estado) 
                VALUES (?, ?, 'pendiente')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $solicitante_id, $receptor_id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Solicitud enviada correctamente'];
        } else {
            return ['success' => false, 'message' => 'Error al enviar la solicitud'];
        }
    }
    
    public function obtenerSolicitudesPendientes($usuario_id) {
        $sql = "SELECT 
                    a.id,
                    a.fecha_solicitud,
                    u.id as solicitante_id,
                    u.nombre as nombre_solicitante,
                    u.apellidos as apellidos_solicitante,
                    u.username as username_solicitante,
                    u.email as email_solicitante
                FROM amistades a
                INNER JOIN usuarios_deportistas u ON a.usuario_solicitante_id = u.id
                WHERE a.usuario_receptor_id = ? 
                AND a.estado = 'pendiente'
                ORDER BY a.fecha_solicitud DESC";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function responderSolicitudAmistad($solicitud_id, $usuario_id, $respuesta) {
        // Verificar que la solicitud es para este usuario
        $sql = "SELECT usuario_receptor_id FROM amistades WHERE id = ? AND estado = 'pendiente'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $solicitud_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            return ['success' => false, 'message' => 'Solicitud no encontrada'];
        }
        
        $solicitud = $result->fetch_assoc();
        if ($solicitud['usuario_receptor_id'] != $usuario_id) {
            return ['success' => false, 'message' => 'No tienes permisos para responder esta solicitud'];
        }
        
        // Actualizar estado
        $sql = "UPDATE amistades SET estado = ?, fecha_respuesta = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $respuesta, $solicitud_id);
        
        if ($stmt->execute()) {
            $mensaje = $respuesta == 'aceptada' ? 'Solicitud aceptada' : 'Solicitud rechazada';
            return ['success' => true, 'message' => $mensaje];
        } else {
            return ['success' => false, 'message' => 'Error al procesar solicitud'];
        }
    }
    
    public function obtenerAmigos($usuario_id) {
        $sql = "SELECT 
                    CASE 
                        WHEN a.usuario_solicitante_id = ? THEN u2.id
                        ELSE u1.id
                    END as amigo_id,
                    CASE 
                        WHEN a.usuario_solicitante_id = ? THEN u2.nombre
                        ELSE u1.nombre
                    END as nombre,
                    CASE 
                        WHEN a.usuario_solicitante_id = ? THEN u2.apellidos
                        ELSE u1.apellidos
                    END as apellidos,
                    CASE 
                        WHEN a.usuario_solicitante_id = ? THEN u2.username
                        ELSE u1.username
                    END as username
                FROM amistades a
                INNER JOIN usuarios_deportistas u1 ON a.usuario_solicitante_id = u1.id
                INNER JOIN usuarios_deportistas u2 ON a.usuario_receptor_id = u2.id
                WHERE (a.usuario_solicitante_id = ? OR a.usuario_receptor_id = ?) 
                AND a.estado = 'aceptada'
                ORDER BY nombre, apellidos";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiiiii", $usuario_id, $usuario_id, $usuario_id, $usuario_id, $usuario_id, $usuario_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function buscarUsuarios($busqueda, $usuario_actual_id) {
        $sql = "SELECT id, nombre, apellidos, username 
                FROM usuarios_deportistas 
                WHERE (nombre LIKE ? OR apellidos LIKE ? OR username LIKE ?) 
                AND id != ? 
                AND estado = 1
                AND id NOT IN (
                    SELECT CASE 
                        WHEN usuario_solicitante_id = ? THEN usuario_receptor_id
                        ELSE usuario_solicitante_id
                    END
                    FROM amistades 
                    WHERE (usuario_solicitante_id = ? OR usuario_receptor_id = ?)
                    AND estado IN ('aceptada', 'pendiente')
                )
                ORDER BY nombre, apellidos
                LIMIT 20";
        
        $busqueda_param = "%{$busqueda}%";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssiiiii", 
            $busqueda_param, $busqueda_param, $busqueda_param, 
            $usuario_actual_id, $usuario_actual_id, $usuario_actual_id, $usuario_actual_id
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function buscarUsuarioPorId($usuario_id, $usuario_actual_id) {
        $sql = "SELECT id, nombre, apellidos, username, email, nivel_habilidad
                FROM usuarios_deportistas 
                WHERE id = ? 
                AND estado = 1
                AND id NOT IN (
                    SELECT CASE 
                        WHEN usuario_solicitante_id = ? THEN usuario_receptor_id
                        ELSE usuario_solicitante_id
                    END
                    FROM amistades 
                    WHERE (usuario_solicitante_id = ? OR usuario_receptor_id = ?)
                    AND estado IN ('aceptada', 'pendiente')
                )";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $usuario_id, $usuario_actual_id, $usuario_actual_id, $usuario_actual_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
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
            $sql = "INSERT INTO equipo_miembros (equipo_id, usuario_id, rol) VALUES (?, ?, 'creador')";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $equipo_id, $datos['creador_id']);
            $stmt->execute();
            
            $this->conn->commit();
            return ['success' => true, 'message' => 'Equipo creado correctamente', 'equipo_id' => $equipo_id];
            
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
    
        $sql .= " GROUP BY e.id, e.nombre, e.descripcion, e.limite_miembros, e.privado, e.creado_en, d.nombre, em.rol
                  ORDER BY e.nombre";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function obtenerEquiposPorDeporte($usuario_id, $deporte_id = null) {
        $sql = "SELECT e.id, e.nombre, e.descripcion, d.nombre as deporte, d.id as deporte_id,
                       em.rol, e.creado_en, 
                       (SELECT COUNT(*) FROM equipo_miembros WHERE equipo_id = e.id) as total_miembros
                FROM equipos e
                INNER JOIN equipo_miembros em ON e.id = em.equipo_id
                INNER JOIN deportes d ON e.deporte_id = d.id
                WHERE em.usuario_id = ? AND e.estado = 1";
        
        if ($deporte_id) {
            $sql .= " AND e.deporte_id = ?";
        }
        
        $sql .= " ORDER BY d.nombre, e.nombre";
        
        $stmt = $this->conn->prepare($sql);
        if ($deporte_id) {
            $stmt->bind_param("ii", $usuario_id, $deporte_id);
        } else {
            $stmt->bind_param("i", $usuario_id);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function obtenerMiembrosEquipo($equipo_id) {
        $sql = "SELECT u.id, u.nombre, u.apellidos, u.username, em.rol, em.fecha_union
                FROM equipo_miembros em
                INNER JOIN usuarios_deportistas u ON em.usuario_id = u.id
                WHERE em.equipo_id = ? AND u.estado = 1
                ORDER BY 
                    CASE em.rol 
                        WHEN 'creador' THEN 1 
                        WHEN 'administrador' THEN 2 
                        ELSE 3 
                    END, u.nombre";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $equipo_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function obtenerDeportes() {
        $sql = "SELECT id, nombre FROM deportes ORDER BY nombre";
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
}