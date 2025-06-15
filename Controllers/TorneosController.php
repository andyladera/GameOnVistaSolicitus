<?php
// Controllers/TorneosController.php
require_once '../Models/TorneosModel.php';

class TorneosController {
    private $torneosModel;
    
    public function __construct() {
        $this->torneosModel = new TorneosModel();
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
    
    // ========== OBTENER TORNEOS CON FILTROS ==========
    
    public function obtenerTorneos() {
        if (!$this->verificarAutenticacion()) return;
        
        $filtros = [
            'deporte_id' => $_GET['deporte_id'] ?? null,
            'estado' => $_GET['estado'] ?? null,
            'calificacion_min' => $_GET['calificacion_min'] ?? 0,
            'nombre' => $_GET['nombre'] ?? '',
            'organizador_tipo' => $_GET['organizador_tipo'] ?? null
        ];
        
        try {
            $torneos = $this->torneosModel->obtenerTorneosConFiltros($filtros);
            $this->response(['success' => true, 'torneos' => $torneos]);
        } catch (Exception $e) {
            $this->response(['success' => false, 'message' => 'Error al obtener torneos: ' . $e->getMessage()]);
        }
    }
    
    public function obtenerDetallesTorneo() {
        if (!$this->verificarAutenticacion()) return;
        
        $torneo_id = $_GET['torneo_id'] ?? null;
        
        if (!$torneo_id) {
            $this->response(['success' => false, 'message' => 'ID de torneo requerido']);
        }
        
        try {
            $torneo = $this->torneosModel->obtenerDetallesTorneo($torneo_id);
            $equipos = $this->torneosModel->obtenerEquiposInscritos($torneo_id);
            
            $this->response([
                'success' => true, 
                'torneo' => $torneo,
                'equipos_inscritos' => $equipos
            ]);
        } catch (Exception $e) {
            $this->response(['success' => false, 'message' => 'Error obteniendo detalles: ' . $e->getMessage()]);
        }
    }
    
    public function inscribirEquipo() {
        if (!$this->verificarAutenticacion()) return;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->response(['success' => false, 'message' => 'Método no permitido']);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $torneo_id = $input['torneo_id'] ?? null;
        $equipo_id = $input['equipo_id'] ?? null;
        
        if (!$torneo_id || !$equipo_id) {
            $this->response(['success' => false, 'message' => 'Datos incompletos']);
        }
        
        try {
            $resultado = $this->torneosModel->inscribirEquipoEnTorneo($torneo_id, $equipo_id, $_SESSION['user_id']);
            $this->response($resultado);
        } catch (Exception $e) {
            $this->response(['success' => false, 'message' => 'Error en inscripción: ' . $e->getMessage()]);
        }
    }
    
    // ========== MANEJADOR DE RUTAS ==========
    
    public function handleRequest() {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'obtener_torneos':
                $this->obtenerTorneos();
                break;
            case 'obtener_detalles':
                $this->obtenerDetallesTorneo();
                break;
            case 'inscribir_equipo':
                $this->inscribirEquipo();
                break;
            default:
                $this->response(['success' => false, 'message' => 'Acción no válida']);
                break;
        }
    }
}

// Manejo directo si se llama al archivo
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new TorneosController();
    $controller->handleRequest();
}
?>