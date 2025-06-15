<?php
// filepath: c:\xampp\htdocs\GameOn_Network\Controllers\PerfilController.php
require_once __DIR__ . '/../Config/Database.php';

class PerfilController {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Obtener información completa del deportista
    public function getPerfilDeportista($userId) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM usuarios_deportistas WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $perfil = $result->fetch_assoc();
            $stmt->close();
            return $perfil;
        } catch (Exception $e) {
            error_log("Error al obtener perfil: " . $e->getMessage());
            return false;
        }
    }
    
    // Obtener todos los deportes disponibles
    public function getDeportes() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM deportes ORDER BY nombre");
            $stmt->execute();
            $result = $stmt->get_result();
            $deportes = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $deportes;
        } catch (Exception $e) {
            error_log("Error al obtener deportes: " . $e->getMessage());
            return [];
        }
    }
    
    // Obtener deportes del usuario
    public function getDeportesUsuario($userId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT d.* FROM deportes d 
                INNER JOIN usuarios_deportes ud ON d.id = ud.deporte_id 
                WHERE ud.usuario_id = ? ORDER BY d.nombre
            ");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $deportes = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $deportes;
        } catch (Exception $e) {
            error_log("Error al obtener deportes del usuario: " . $e->getMessage());
            return [];
        }
    }
    
    // Agregar deporte al usuario
    public function agregarDeporte($userId, $deporteId) {
        try {
            // Verificar si ya existe
            $checkStmt = $this->conn->prepare("SELECT usuario_id FROM usuarios_deportes WHERE usuario_id = ? AND deporte_id = ?");
            $checkStmt->bind_param("ii", $userId, $deporteId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                $checkStmt->close();
                return ['success' => false, 'message' => 'Ya tienes este deporte agregado'];
            }
            $checkStmt->close();
            
            // Insertar nuevo deporte
            $stmt = $this->conn->prepare("INSERT INTO usuarios_deportes (usuario_id, deporte_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $userId, $deporteId);
            $result = $stmt->execute();
            $stmt->close();
            
            if ($result) {
                return ['success' => true, 'message' => 'Deporte agregado correctamente'];
            } else {
                return ['success' => false, 'message' => 'Error al agregar deporte'];
            }
        } catch (Exception $e) {
            error_log("Error al agregar deporte: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos'];
        }
    }
    
    // Eliminar deporte del usuario
    public function eliminarDeporte($userId, $deporteId) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM usuarios_deportes WHERE usuario_id = ? AND deporte_id = ?");
            $stmt->bind_param("ii", $userId, $deporteId);
            $result = $stmt->execute();
            $stmt->close();
            
            if ($result) {
                return ['success' => true, 'message' => 'Deporte eliminado correctamente'];
            } else {
                return ['success' => false, 'message' => 'Error al eliminar deporte'];
            }
        } catch (Exception $e) {
            error_log("Error al eliminar deporte: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos'];
        }
    }
    
    // Actualizar perfil del deportista
    public function actualizarPerfil($userId, $datos) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE usuarios_deportistas SET 
                nombre = ?, 
                apellidos = ?, 
                telefono = ?, 
                fecha_nacimiento = ?, 
                genero = ?, 
                email = ?
                WHERE id = ?
            ");
            
            $stmt->bind_param(
                "ssssssi",
                $datos['nombre'],
                $datos['apellidos'],
                $datos['telefono'],
                $datos['fecha_nacimiento'],
                $datos['genero'],
                $datos['email'],
                $userId
            );
            
            $result = $stmt->execute();
            $stmt->close();
            
            if ($result) {
                return ['success' => true, 'message' => 'Perfil actualizado correctamente'];
            } else {
                return ['success' => false, 'message' => 'Error al actualizar perfil'];
            }
        } catch (Exception $e) {
            error_log("Error al actualizar perfil: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos'];
        }
    }
}
?>