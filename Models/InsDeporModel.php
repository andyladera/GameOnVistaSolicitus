<?php
require_once __DIR__ . '/../Config/database.php';

class InsDeporModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Obtener todas las instalaciones deportivas
    public function getAllInstalaciones() {
        $query = "SELECT * FROM instituciones_deportivas WHERE estado = 1";
        $result = $this->conn->query($query);
        return $this->fetchAllAssoc($result);
    }

    // Obtener una instalación deportiva por ID
    public function getInstalacionById($id) {
        $query = "SELECT * FROM instituciones_deportivas WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Obtener los horarios de una instalación deportiva
    public function getHorariosInstalacion($instalacionId) {
        $query = "SELECT * FROM horarios_atencion WHERE institucion_deportiva_id = ? 
                  ORDER BY FIELD(dia, 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo')";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $instalacionId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $this->fetchAllAssoc($result);
    }

    // Obtener los deportes que ofrece una instalación
    public function getDeportesInstalacion($instalacionId) {
        $query = "SELECT d.id, d.nombre FROM deportes d 
                  INNER JOIN instituciones_deportes id ON d.id = id.deporte_id 
                  WHERE id.institucion_deportiva_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $instalacionId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $this->fetchAllAssoc($result);
    }

    // Obtener instalaciones deportivas cercanas a una ubicación
    public function getInstalacionesCercanas($latitud, $longitud, $distanciaKm = 5) {
        $query = "SELECT *, 
                  (6371 * acos(cos(radians(?)) * cos(radians(latitud)) * 
                  cos(radians(longitud) - radians(?)) + 
                  sin(radians(?)) * sin(radians(latitud)))) AS distancia 
                  FROM instituciones_deportivas
                  WHERE estado = 1
                  HAVING distancia < ? 
                  ORDER BY distancia";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("dddi", $latitud, $longitud, $latitud, $distanciaKm);
        $stmt->execute();
        $result = $stmt->get_result();
        return $this->fetchAllAssoc($result);
    }

    // Obtener instalaciones deportivas por deporte
    public function getInstalacionesPorDeporte($deporteId) {
        $query = "SELECT i.* FROM instituciones_deportivas i 
                  INNER JOIN instituciones_deportes id ON i.id = id.institucion_deportiva_id 
                  WHERE id.deporte_id = ? AND i.estado = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $deporteId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $this->fetchAllAssoc($result);
    }

    // Buscar instalaciones deportivas por nombre
    public function buscarInstalaciones($termino) {
        $termino = "%$termino%";
        $query = "SELECT * FROM instituciones_deportivas 
                  WHERE nombre LIKE ? AND estado = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $termino);
        $stmt->execute();
        $result = $stmt->get_result();
        return $this->fetchAllAssoc($result);
    }

    // Función auxiliar para obtener todos los resultados como array asociativo
    private function fetchAllAssoc($result) {
        $rows = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }
}
?>