<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<?php
require_once __DIR__ . '/../Models/TorneosModel.php';
require_once __DIR__ . '/../Models/TorneosPartidosModel.php';
class TorneosController {
    private $torneosModel;
    private $partidosModel; // ✅ AÑADIR esta propiedad
    
    public function __construct() {
        $this->torneosModel = new TorneosModel();
        $this->partidosModel = new TorneosPartidosModel(); // ✅ Inicializar en el constructor
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
    
    // ✅ FUNCIÓN EXISTENTE - Actualizada para instituciones
    public function obtenerTorneos() {
        if (!$this->verificarAutenticacion()) return;
        
        $filtros = [
            'deporte_id' => $_GET['deporte_id'] ?? null,
            'estado' => $_GET['estado'] ?? null,
            'calificacion_min' => $_GET['calificacion_min'] ?? 0,
            'nombre' => $_GET['nombre'] ?? '',
            'organizador_tipo' => $_GET['organizador_tipo'] ?? null
        ];
        
        // ✅ NUEVO: Si es institución deportiva, filtrar por sus torneos
        if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'instalacion') {
            $filtros['usuario_instalacion_id'] = $_SESSION['user_id'];
        }
        
        try {
            $torneos = $this->torneosModel->obtenerTorneosConFiltros($filtros);
            $this->response(['success' => true, 'torneos' => $torneos]);
        } catch (Exception $e) {
            $this->response(['success' => false, 'message' => 'Error al obtener torneos: ' . $e->getMessage()]);
        }
    }

    // ✅ NUEVA FUNCIÓN: Crear torneo desde institución deportiva
    public function crearTorneo() {
        if (!$this->verificarAutenticacion()) return;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->response(['success' => false, 'message' => 'Método no permitido']);
        }
        
        // Solo instituciones pueden crear torneos por esta ruta
        if ($_SESSION['user_type'] !== 'instalacion') {
            $this->response(['success' => false, 'message' => 'Solo instituciones deportivas pueden crear torneos']);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Guardar la imagen remota en el servidor local si se proporciona una URL
        $imagenLocal = null;
        if (!empty($input['imagen_torneo'])) {
            $imagenLocal = $this->guardarImagenRemota($input['imagen_torneo']);
        }
        
        $datos = [
            'nombre' => trim($input['nombre'] ?? ''),
            'descripcion' => trim($input['descripcion'] ?? ''),
            'deporte_id' => $input['deporte_id'] ?? 0,
            'organizador_tipo' => 'institucion',
            'organizador_id' => $_SESSION['user_id'],
            'institucion_sede_id' => $input['institucion_sede_id'] ?? 0,
            'max_equipos' => $input['max_equipos'] ?? 16,
            'fecha_inicio' => $input['fecha_inicio'] ?? '',
            'fecha_fin' => $input['fecha_fin'] ?? '',
            'fecha_inscripcion_inicio' => $input['fecha_inscripcion_inicio'] ?? '',
            'fecha_inscripcion_fin' => $input['fecha_inscripcion_fin'] ?? '',
            'modalidad' => $input['modalidad'] ?? 'eliminacion_simple',
            'premio_1' => trim($input['premio_1'] ?? ''),
            'premio_2' => trim($input['premio_2'] ?? ''),
            'premio_3' => trim($input['premio_3'] ?? ''),
            'costo_inscripcion' => $input['costo_inscripcion'] ?? 0.00,
            'imagen_torneo' => $imagenLocal
        ];
        
        // Validaciones básicas
        if (empty($datos['nombre']) || empty($datos['deporte_id']) || empty($datos['institucion_sede_id'])) {
            $this->response(['success' => false, 'message' => 'Nombre, deporte y sede son requeridos']);
        }
        
        try {
            $resultado = $this->torneosModel->crearTorneo($datos);
            
            if ($resultado['success']) {
                $torneoId = $resultado['torneo_id'];
                
                // Crear partidos programados
                if (!empty($input['partidos_programados'])) {
                    $partidosCreados = 0;
                    foreach ($input['partidos_programados'] as $partido) {
                        // ✅ CORREGIR: Usar valores por defecto si no existen las claves
                        $partidoInfo = [
                            'fase' => $partido['fase'] ?? 'Primera Ronda',
                            'numeroPartido' => $partido['numeroPartido'] ?? 1,
                            'ronda' => $partido['ronda'] ?? 1,
                            'descripcion' => $partido['descripcion'] ?? ($partido['partidoNombre'] ?? 'Partido')
                        ];
                        
                        // Si no existe numeroPartido, extraerlo del partidoId
                        if (!isset($partido['numeroPartido']) && isset($partido['partidoId'])) {
                            $partidoIdParts = explode('-', $partido['partidoId']);
                            $partidoInfo['numeroPartido'] = intval(end($partidoIdParts));
                            
                            // Extraer ronda del partidoId también
                            if (preg_match('/ronda(\d+)/', $partido['partidoId'], $matches)) {
                                $partidoInfo['ronda'] = intval($matches[1]);
                            }
                        }
                        
                        $resultadoPartido = $this->partidosModel->crearPartidoTorneo(
                            $torneoId,
                            $partido['areaId'],
                            $partido['fecha'],
                            $partido['horaInicio'],
                            $partido['horaFin'],
                            $partidoInfo
                        );
                        
                        if ($resultadoPartido['success']) {
                            $partidosCreados++;
                        }
                    }
                    
                    $resultado['message'] .= " Se programaron $partidosCreados partidos.";
                }
            }
            
            $this->response($resultado);
        } catch (Exception $e) {
            $this->response(['success' => false, 'message' => 'Error al crear torneo: ' . $e->getMessage()]);
        }
    }

    // Método para guardar la imagen desde URL remota
    private function guardarImagenRemota($url) {
        return $url;
    }

    public function actualizarTorneo() {
        if (!$this->verificarAutenticacion()) return;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->response(['success' => false, 'message' => 'Método no permitido']);
        }
        
        if ($_SESSION['user_type'] !== 'instalacion') {
            $this->response(['success' => false, 'message' => 'Solo instituciones deportivas pueden editar torneos']);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $torneoId = $input['torneo_id'] ?? null;
        
        if (!$torneoId) {
            $this->response(['success' => false, 'message' => 'ID de torneo requerido']);
        }
        
        // Verificar permisos
        if (!$this->torneosModel->verificarPermisosEdicion($torneoId, $_SESSION['user_id'])) {
            $this->response(['success' => false, 'message' => 'No tienes permisos para editar este torneo']);
        }
        
        $datos = [
            'nombre' => trim($input['nombre'] ?? ''),
            'descripcion' => trim($input['descripcion'] ?? ''),
            'deporte_id' => $input['deporte_id'] ?? 0,
            'organizador_id' => $_SESSION['user_id'],
            'max_equipos' => $input['max_equipos'] ?? 16,
            'fecha_inicio' => $input['fecha_inicio'] ?? '',
            'fecha_fin' => $input['fecha_fin'] ?? '',
            'fecha_inscripcion_inicio' => $input['fecha_inscripcion_inicio'] ?? '',
            'fecha_inscripcion_fin' => $input['fecha_inscripcion_fin'] ?? '',
            'modalidad' => $input['modalidad'] ?? 'eliminacion_simple',
            'premio_1' => trim($input['premio_1'] ?? ''),
            'premio_2' => trim($input['premio_2'] ?? ''),
            'premio_3' => trim($input['premio_3'] ?? ''),
            'costo_inscripcion' => $input['costo_inscripcion'] ?? 0.00,
            'imagen_torneo' => $input['imagen_torneo'] ?? null
        ];
        
        try {
            $resultado = $this->torneosModel->actualizarTorneo($torneoId, $datos);
            $this->response($resultado);
        } catch (Exception $e) {
            $this->response(['success' => false, 'message' => 'Error al actualizar torneo: ' . $e->getMessage()]);
        }
    }

    // ✅ FUNCIONES EXISTENTES - Mantener como están
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
    
    public function obtenerPartidosTorneo() {
        if (!$this->verificarAutenticacion()) return;
        
        $torneoId = $_GET['torneo_id'] ?? null;
        
        if (!$torneoId) {
            $this->response(['success' => false, 'message' => 'ID de torneo requerido']);
        }
        
        try {
            $partidos = $this->partidosModel->obtenerEstructuraTorneo($torneoId);
            $this->response([
                'success' => true,
                'partidos' => $partidos
            ]);
        } catch (Exception $e) {
            $this->response(['success' => false, 'message' => 'Error al obtener partidos: ' . $e->getMessage()]);
        }
    }

    public function actualizarResultadoPartido() {
        if (!$this->verificarAutenticacion()) return;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->response(['success' => false, 'message' => 'Método no permitido']);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $partidoId = $input['partido_id'] ?? null;
        
        if (!$partidoId) {
            $this->response(['success' => false, 'message' => 'ID de partido requerido']);
        }
        
        try {
            // Actualizar resultado del partido
            $resultado = $this->partidosModel->actualizarResultadoPartido($partidoId, $input);
            $this->response($resultado);
        } catch (Exception $e) {
            $this->response(['success' => false, 'message' => 'Error al actualizar partido: ' . $e->getMessage()]);
        }
    }

    // ✅ MANEJADOR DE RUTAS ACTUALIZADO
    public function handleRequest() {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'obtener_torneos':
                $this->obtenerTorneos();
                break;
            case 'crear_torneo':
                $this->crearTorneo();
                break;
            case 'actualizar_torneo':
                $this->actualizarTorneo();
                break;
            case 'obtener_detalles':
                $this->obtenerDetallesTorneo();
                break;
            case 'obtener_partidos': // ✅ NUEVO
                $this->obtenerPartidosTorneo();
                break;
            case 'actualizar_resultado': // ✅ NUEVO
                $this->actualizarResultadoPartido();
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