<?php
// Controllers/EquiposController.php
require_once '../Models/EquiposModel.php';

class EquiposController {
    private $equiposModel;
    
    public function __construct() {
        $this->equiposModel = new EquiposModel();
    }
    
    private function verificarAutenticacion() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            $this->response(['success' => false, 'message' => 'No autenticado']);
            return false;
        }
        return true;
    }
    
    private function response($data) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // ========== GESTIÓN DE EQUIPOS ==========
    
    public function crearEquipo() {
        if (!$this->verificarAutenticacion()) return;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->response(['success' => false, 'message' => 'Método no permitido']);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $datos = [
            'nombre' => trim($input['nombre'] ?? ''),
            'descripcion' => trim($input['descripcion'] ?? ''),
            'deporte_id' => $input['deporte_id'] ?? 0,
            'creador_id' => $_SESSION['user_id'],
            'limite_miembros' => $input['limite_miembros'] ?? 10,
            'privado' => $input['privado'] ?? 0
        ];
        
        if (empty($datos['nombre']) || empty($datos['deporte_id'])) {
            $this->response(['success' => false, 'message' => 'Nombre y deporte son requeridos']);
        }
        
        try {
            $resultado = $this->equiposModel->crearEquipo($datos);
            $this->response($resultado);
        } catch (Exception $e) {
            $this->response(['success' => false, 'message' => 'Error al crear equipo: ' . $e->getMessage()]);
        }
    }
    
    public function obtenerEquipos() {
        if (!$this->verificarAutenticacion()) return;
        
        try {
            $deporte_id = $_GET['deporte_id'] ?? null;
            $equipos = $this->equiposModel->obtenerEquiposUsuario($_SESSION['user_id'], $deporte_id);
            $this->response(['success' => true, 'equipos' => $equipos]);
        } catch (Exception $e) {
            $this->response(['success' => false, 'message' => 'Error al obtener equipos: ' . $e->getMessage()]);
        }
    }
    
    public function obtenerMiembros() {
        if (!$this->verificarAutenticacion()) return;
        
        $equipo_id = $_GET['equipo_id'] ?? null;
        
        if (!$equipo_id) {
            $this->response(['success' => false, 'message' => 'ID de equipo requerido']);
        }
        
        // Verificar que el usuario es miembro del equipo
        $es_miembro = $this->equiposModel->verificarMiembroEquipo($equipo_id, $_SESSION['user_id']);
        if (!$es_miembro) {
            $this->response(['success' => false, 'message' => 'No tienes acceso a este equipo']);
        }
        
        try {
            $miembros = $this->equiposModel->obtenerMiembrosEquipo($equipo_id);
            $this->response(['success' => true, 'miembros' => $miembros]);
        } catch (Exception $e) {
            $this->response(['success' => false, 'message' => 'Error al obtener miembros: ' . $e->getMessage()]);
        }
    }
    
    public function obtenerDeportes() {
        if (!$this->verificarAutenticacion()) return;
        
        try {
            $deportes = $this->equiposModel->obtenerDeportes();
            $this->response(['success' => true, 'deportes' => $deportes]);
        } catch (Exception $e) {
            $this->response(['success' => false, 'message' => 'Error al obtener deportes: ' . $e->getMessage()]);
        }
    }
    
    // ⭐ NUEVAS FUNCIONES PARA AÑADIR AMIGOS
    
    public function obtenerAmigosParaEquipo() {
        if (!$this->verificarAutenticacion()) return;
        
        $equipoId = $_GET['equipo_id'] ?? null;
        
        if (!$equipoId) {
            $this->response(['success' => false, 'message' => 'ID de equipo requerido']);
        }
        
        try {
            // Verificar que el usuario sea creador del equipo
            $permisos = $this->equiposModel->verificarPermisosCreador($equipoId, $_SESSION['user_id']);
            if (!$permisos) {
                $this->response(['success' => false, 'message' => 'Solo el creador puede añadir miembros']);
            }
            
            $amigos = $this->equiposModel->obtenerAmigosDisponiblesParaEquipo($equipoId, $_SESSION['user_id']);
            $this->response(['success' => true, 'amigos' => $amigos]);
            
        } catch (Exception $e) {
            $this->response(['success' => false, 'message' => 'Error obteniendo amigos: ' . $e->getMessage()]);
        }
    }
    
    public function agregarAmigoAEquipo() {
        if (!$this->verificarAutenticacion()) return;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->response(['success' => false, 'message' => 'Método no permitido']);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $equipoId = $input['equipo_id'] ?? null;
        $amigoId = $input['amigo_id'] ?? null;
        
        if (!$equipoId || !$amigoId) {
            $this->response(['success' => false, 'message' => 'Datos incompletos']);
        }
        
        try {
            $resultado = $this->equiposModel->agregarMiembroAEquipo($equipoId, $amigoId, $_SESSION['user_id']);
            $this->response($resultado);
            
        } catch (Exception $e) {
            $this->response(['success' => false, 'message' => 'Error añadiendo al equipo: ' . $e->getMessage()]);
        }
    }
    
    public function eliminarMiembroEquipo() {
        if (!$this->verificarAutenticacion()) return;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->response(['success' => false, 'message' => 'Método no permitido']);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $equipoId = $input['equipo_id'] ?? null;
        $miembroId = $input['miembro_id'] ?? null;
        
        if (!$equipoId || !$miembroId) {
            $this->response(['success' => false, 'message' => 'Datos incompletos']);
        }
        
        try {
            $resultado = $this->equiposModel->eliminarMiembroEquipo($equipoId, $miembroId, $_SESSION['user_id']);
            $this->response($resultado);
            
        } catch (Exception $e) {
            $this->response(['success' => false, 'message' => 'Error eliminando miembro: ' . $e->getMessage()]);
        }
    }
    public function handleRequest() {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'crear':
                $this->crearEquipo();
                break;
            case 'obtener':
                $this->obtenerEquipos();
                break;
            case 'obtener_miembros':
                $this->obtenerMiembros();
                break;
            case 'obtener_deportes':
                $this->obtenerDeportes();
                break;
            case 'obtener_amigos_para_equipo':
                $this->obtenerAmigosParaEquipo();
                break;
            case 'agregar_amigo':
                $this->agregarAmigoAEquipo();
                break;
            case 'eliminar_miembro':
                $this->eliminarMiembroEquipo();
                break;
            default:
                $this->response(['success' => false, 'message' => 'Acción no válida']);
                break;
        }
    }
}

// Manejo directo si se llama al archivo
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new EquiposController();
    $controller->handleRequest();
}
?>