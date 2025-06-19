<?php
// filepath: c:\xampp\htdocs\GameOn_Network\Controllers\PerfilController.php
require_once __DIR__ . '/../Config/database.php';

class PerfilController {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        
        // ✅ MANEJAR REQUESTS AJAX
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
            $this->handleGetRequest();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
            $this->handlePostRequest();
        }
    }
    
    // ✅ NUEVA FUNCIÓN: Manejar requests GET
    private function handleGetRequest() {
        $action = $_GET['action'];
        
        switch ($action) {
            case 'obtener_perfil':
                $this->obtenerPerfilAjax();
                break;
            case 'obtener_deportes':
                $this->obtenerDeportesAjax();
                break;
            default:
                $this->sendError('Acción no válida');
        }
    }
    
    // ✅ NUEVA FUNCIÓN: Manejar requests POST
    private function handlePostRequest() {
        $action = $_GET['action'];
        
        switch ($action) {
            case 'actualizar_perfil':
                $this->actualizarPerfilAjax();
                break;
            case 'agregar_deporte':
                $this->agregarDeporteAjax();
                break;
            case 'eliminar_deporte':
                $this->eliminarDeporteAjax();
                break;
            default:
                $this->sendError('Acción no válida');
        }
    }
    
    // ✅ NUEVA FUNCIÓN: Obtener perfil vía AJAX
    private function obtenerPerfilAjax() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->sendError('Usuario no autenticado');
            return;
        }
        
        $perfil = $this->getPerfilDeportista($_SESSION['user_id']);
        if ($perfil) {
            $this->sendSuccess(['perfil' => $perfil]);
        } else {
            $this->sendError('Error al obtener perfil');
        }
    }
    
    // ✅ NUEVA FUNCIÓN: Obtener deportes vía AJAX
    private function obtenerDeportesAjax() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->sendError('Usuario no autenticado');
            return;
        }
        
        $deportes = $this->getDeportes();
        $deportesUsuario = $this->getDeportesUsuario($_SESSION['user_id']);
        
        $this->sendSuccess([
            'deportes' => $deportes,
            'deportes_usuario' => $deportesUsuario
        ]);
    }
    
    // ✅ NUEVA FUNCIÓN: Actualizar perfil vía AJAX
    private function actualizarPerfilAjax() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->sendError('Usuario no autenticado');
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $this->sendError('Datos no recibidos');
            return;
        }
        
        $resultado = $this->actualizarPerfil($_SESSION['user_id'], $input);
        
        if ($resultado['success']) {
            $this->sendSuccess(['message' => $resultado['message']]);
        } else {
            $this->sendError($resultado['message']);
        }
    }
    
    // ✅ NUEVA FUNCIÓN: Agregar deporte vía AJAX
    private function agregarDeporteAjax() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->sendError('Usuario no autenticado');
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['deporte_id'])) {
            $this->sendError('ID de deporte requerido');
            return;
        }
        
        $resultado = $this->agregarDeporte($_SESSION['user_id'], $input['deporte_id']);
        
        if ($resultado['success']) {
            $this->sendSuccess(['message' => $resultado['message']]);
        } else {
            $this->sendError($resultado['message']);
        }
    }
    
    // ✅ NUEVA FUNCIÓN: Eliminar deporte vía AJAX
    private function eliminarDeporteAjax() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->sendError('Usuario no autenticado');
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['deporte_id'])) {
            $this->sendError('ID de deporte requerido');
            return;
        }
        
        $resultado = $this->eliminarDeporte($_SESSION['user_id'], $input['deporte_id']);
        
        if ($resultado['success']) {
            $this->sendSuccess(['message' => $resultado['message']]);
        } else {
            $this->sendError($resultado['message']);
        }
    }
    
    // ✅ FUNCIÓN NUEVA: Enviar respuesta exitosa
    private function sendSuccess($data) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit;
    }
    
    // ✅ FUNCIÓN NUEVA: Enviar error
    private function sendError($message) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
    
    // ✅ FUNCIONES EXISTENTES (mantener como están)
    
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

// ✅ INSTANCIAR LA CLASE PARA MANEJAR REQUESTS
new PerfilController();
?>