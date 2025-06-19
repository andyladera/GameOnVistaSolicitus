<?php
require_once __DIR__ . '/../Models/AreasDeportivasModel.php';

class AreasDeportivasController {
    private $areasModel;

    public function __construct() {
        $this->areasModel = new AreasDeportivasModel();
    }

    // Obtener áreas por institución
    public function getAreasByInstitucion($institucionId) {
        return $this->areasModel->getAreasByInstitucion($institucionId);
    }
    
    // Obtener áreas por usuario instalación
    public function getAreasByUsuarioInstalacion($usuarioInstalacionId) {
        return $this->areasModel->getAreasByUsuarioInstalacion($usuarioInstalacionId);
    }
    
    // Crear nueva área
    public function crearArea($data) {
        return $this->areasModel->crearArea(
            $data['institucion_id'],
            $data['deporte_id'],
            $data['nombre_area'],
            $data['descripcion'],
            $data['capacidad'],
            $data['tarifa'],
            $data['imagen'] ?? null
        );
    }
    
    // Obtener horarios de área con formato
    public function getHorariosAreaFormateados($areaId) {
        $horarios = $this->areasModel->getHorariosArea($areaId);
        $horarioFormateado = [];
        
        foreach ($horarios as $horario) {
            if ($horario['disponible']) {
                $horaApertura = date('H:i', strtotime($horario['hora_apertura']));
                $horaCierre = date('H:i', strtotime($horario['hora_cierre']));
                $horarioFormateado[$horario['dia']] = "$horaApertura - $horaCierre";
            } else {
                $horarioFormateado[$horario['dia']] = "Cerrado";
            }
        }
        
        return $horarioFormateado;
    }
    
    // Obtener deportes
    public function getDeportes() {
        return $this->areasModel->getDeportes();
    }
    
    // ✅ NUEVA FUNCIÓN: Obtener áreas por sede y deporte
    public function getAreasBySedeAndDeporte($sedeId, $deporteId) {
        return $this->areasModel->getAreasBySedeAndDeporte($sedeId, $deporteId);
    }
    
    // ✅ NUEVA FUNCIÓN: Obtener cronograma de área para fecha específica
    public function getCronogramaArea($areaId, $fecha) {
        require_once __DIR__ . '/../Models/ReservaModel.php';
        $reservaModel = new ReservaModel();
        return $reservaModel->obtenerCronogramaAreaDeportiva($areaId, $fecha);
    }
}

// ✅ MANEJO AJAX para obtener áreas por sede
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] === 'obtener_areas_por_sede') {
        $sedeId = intval($_GET['sede_id'] ?? 0);
        $deporteId = intval($_GET['deporte_id'] ?? 0);
        
        if ($sedeId > 0 && $deporteId > 0) {
            $controller = new AreasDeportivasController();
            $areas = $controller->getAreasBySedeAndDeporte($sedeId, $deporteId);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'areas' => $areas
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Parámetros inválidos'
            ]);
        }
        exit;
    }
}
?>