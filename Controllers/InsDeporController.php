<?php
require_once __DIR__ . '/../Models/InsDeporModel.php';

class InsDeporController {
    private $insDeporModel;
    
    public function __construct() {
        $this->insDeporModel = new InsDeporModel();
    }
    
    // Obtener todas las instalaciones deportivas
    public function getAllInstalaciones() {
        return $this->insDeporModel->getAllInstalaciones();
    }
    
    // Obtener una instalación deportiva por ID
    public function getInstalacionById($id) {
        return $this->insDeporModel->getInstalacionById($id);
    }
    
    // Obtener los horarios de una instalación deportiva
    public function getHorariosInstalacion($instalacionId) {
        return $this->insDeporModel->getHorariosInstalacion($instalacionId);
    }
    
    // Obtener los deportes que ofrece una instalación
    public function getDeportesInstalacion($instalacionId) {
        return $this->insDeporModel->getDeportesInstalacion($instalacionId);
    }
    
    // Obtener instalaciones deportivas cercanas
    public function getInstalacionesCercanas($latitud, $longitud, $distanciaKm = 5) {
        return $this->insDeporModel->getInstalacionesCercanas($latitud, $longitud, $distanciaKm);
    }
    
    // Obtener instalaciones por deporte
    public function getInstalacionesPorDeporte($deporteId) {
        return $this->insDeporModel->getInstalacionesPorDeporte($deporteId);
    }
    
    // Buscar instalaciones por nombre
    public function buscarInstalaciones($termino) {
        return $this->insDeporModel->buscarInstalaciones($termino);
    }
    
    // Formatear horarios para mostrar de forma amigable
    public function formatearHorarios($horarios) {
        $horarioFormateado = [];
        foreach ($horarios as $horario) {
            $horaApertura = date('H:i', strtotime($horario['hora_apertura']));
            $horaCierre = date('H:i', strtotime($horario['hora_cierre']));
            $horarioFormateado[$horario['dia']] = "$horaApertura - $horaCierre";
        }
        return $horarioFormateado;
    }
    
    // Obtener instalaciones con información completa para mostrar
    public function getInstalacionesCompletas() {
        $instalaciones = $this->getAllInstalaciones();
        $instalacionesCompletas = [];
        
        foreach ($instalaciones as $instalacion) {
            $horarios = $this->getHorariosInstalacion($instalacion['id']);
            $deportes = $this->getDeportesInstalacion($instalacion['id']);
            
            $instalacion['horarios'] = $this->formatearHorarios($horarios);
            $instalacion['deportes'] = $deportes;
            
            $instalacionesCompletas[] = $instalacion;
        }
        
        return $instalacionesCompletas;
    }
}
?>