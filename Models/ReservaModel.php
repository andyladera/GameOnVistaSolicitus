<?php
require_once __DIR__ . '/../Config/database.php';

class ReservaModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Obtener reservas del usuario por rango de fechas
    public function obtenerReservasPorFecha($userId, $fechaInicio, $fechaFin) {
        $sql = "SELECT r.*, i.nombre as instalacion, d.nombre as deporte
                FROM reservas r
                INNER JOIN instituciones_deportivas i ON r.id_institucion = i.id
                INNER JOIN deportes d ON r.deporte_id = d.id
                WHERE r.id_usuario = ? 
                AND r.fecha BETWEEN ? AND ?
                AND r.estado IN ('confirmada', 'pendiente')
                ORDER BY r.fecha ASC, r.hora_inicio ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $userId, $fechaInicio, $fechaFin);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener torneos donde participan los equipos del usuario
    public function obtenerTorneosPorFecha($userId, $fechaInicio, $fechaFin) {
        $sql = "SELECT DISTINCT tp.fecha_partido as fecha, t.nombre as torneo_nombre,
                       CONCAT(el.nombre, ' vs ', ev.nombre) as partido_detalle,
                       tp.fase, tp.estado_partido,
                       i.nombre as sede_nombre,
                       d.nombre as deporte_nombre,
                       tp.id as partido_id
                FROM torneos_partidos tp
                INNER JOIN torneos t ON tp.torneo_id = t.id
                INNER JOIN deportes d ON t.deporte_id = d.id
                INNER JOIN equipos el ON tp.equipo_local_id = el.id
                INNER JOIN equipos ev ON tp.equipo_visitante_id = ev.id
                INNER JOIN instituciones_deportivas i ON t.institucion_sede_id = i.id
                INNER JOIN equipo_miembros em ON (em.equipo_id = el.id OR em.equipo_id = ev.id)
                WHERE em.usuario_id = ?
                AND DATE(tp.fecha_partido) BETWEEN ? AND ?
                AND tp.estado_partido IN ('programado', 'en_curso')
                GROUP BY tp.id
                ORDER BY tp.fecha_partido ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $userId, $fechaInicio, $fechaFin);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener próximas reservas del usuario (siguientes 7 días)
    public function obtenerProximasReservas($userId, $limite = 5) {
        $sql = "SELECT r.*, i.nombre as instalacion, d.nombre as deporte
                FROM reservas r
                INNER JOIN instituciones_deportivas i ON r.id_institucion = i.id
                INNER JOIN deportes d ON r.deporte_id = d.id
                WHERE r.id_usuario = ? 
                AND r.fecha >= CURDATE()
                AND r.estado IN ('confirmada', 'pendiente')
                ORDER BY r.fecha ASC, r.hora_inicio ASC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $limite);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener próximos torneos de los equipos del usuario
    public function obtenerProximosTorneos($userId, $limite = 5) {
        $sql = "SELECT DISTINCT tp.fecha_partido, t.nombre as torneo_nombre,
                       CONCAT(el.nombre, ' vs ', ev.nombre) as partido_detalle,
                       tp.fase, i.nombre as sede_nombre,
                       d.nombre as deporte_nombre,
                       tp.id as partido_id
                FROM torneos_partidos tp
                INNER JOIN torneos t ON tp.torneo_id = t.id
                INNER JOIN deportes d ON t.deporte_id = d.id
                INNER JOIN equipos el ON tp.equipo_local_id = el.id
                INNER JOIN equipos ev ON tp.equipo_visitante_id = ev.id
                INNER JOIN instituciones_deportivas i ON t.institucion_sede_id = i.id
                INNER JOIN equipo_miembros em ON (em.equipo_id = el.id OR em.equipo_id = ev.id)
                WHERE em.usuario_id = ?
                AND tp.fecha_partido >= NOW()
                AND tp.estado_partido IN ('programado', 'en_curso')
                GROUP BY tp.id
                ORDER BY tp.fecha_partido ASC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $limite);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener equipos del usuario
    public function obtenerEquiposUsuario($userId) {
        $sql = "SELECT e.id, e.nombre, d.nombre as deporte
                FROM equipos e
                INNER JOIN equipo_miembros em ON e.id = em.equipo_id
                INNER JOIN deportes d ON e.deporte_id = d.id
                WHERE em.usuario_id = ?
                AND e.estado = 1
                ORDER BY e.nombre ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ===== MÉTODOS EXISTENTES =====
    public function obtenerCronogramaDisponibilidad($idInstitucion, $fecha = null) {
        if (!$fecha) {
            $fecha = date('Y-m-d');
        }

        $sqlInstitucion = "SELECT nombre FROM instituciones_deportivas WHERE id = ?";
        $stmtInstitucion = $this->conn->prepare($sqlInstitucion);
        $stmtInstitucion->bind_param("i", $idInstitucion);
        $stmtInstitucion->execute();
        $resultadoInstitucion = $stmtInstitucion->get_result();
        $institucion = $resultadoInstitucion->fetch_assoc();

        if (!$institucion) {
            return null;
        }

        $cronogramaSemanal = [];
        for ($i = 0; $i < 7; $i++) {
            $fechaActual = date('Y-m-d', strtotime($fecha . " +$i days"));
            $nombreDia = $this->obtenerNombreDia($fechaActual);
            
            $sqlReservas = "SELECT hora_inicio, hora_fin, estado FROM reservas 
                           WHERE id_institucion = ? AND fecha = ? AND estado != 'cancelada'";
            $stmtReservas = $this->conn->prepare($sqlReservas);
            $stmtReservas->bind_param("is", $idInstitucion, $fechaActual);
            $stmtReservas->execute();
            $resultadoReservas = $stmtReservas->get_result();
            $reservasExistentes = $resultadoReservas->fetch_all(MYSQLI_ASSOC);

            $cronogramaDia = $this->generarCronogramaCompleto($reservasExistentes);
            
            $cronogramaSemanal[] = [
                'fecha' => $fechaActual,
                'nombre_dia' => $nombreDia,
                'cronograma' => $cronogramaDia
            ];
        }

        return [
            'institucion' => $institucion['nombre'],
            'fecha_inicio' => $fecha,
            'cronograma_semanal' => $cronogramaSemanal
        ];
    }

    private function obtenerNombreDia($fecha) {
        $dias = [
            'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo'
        ];
        
        $nombreIngles = date('l', strtotime($fecha));
        return $dias[$nombreIngles] ?? $nombreIngles;
    }

    private function generarCronogramaCompleto($reservasExistentes) {
        $cronograma = [];
        $horaInicio = new DateTime('05:00:00');
        $horaFin = new DateTime('24:00:00');
        $intervalo = new DateInterval('PT30M');

        while ($horaInicio->format('H:i:s') !== '00:00:00') {
            $horaActual = $horaInicio->format('H:i:s');
            $horaSiguiente = clone $horaInicio;
            $horaSiguiente->add($intervalo);
            
            if ($horaSiguiente->format('H:i:s') === '00:00:00') {
                $horaFinIntervalo = '00:00:00';
            } else {
                $horaFinIntervalo = $horaSiguiente->format('H:i:s');
            }

            $ocupado = $this->verificarIntervaloOcupado($horaActual, $horaFinIntervalo, $reservasExistentes);

            $cronograma[] = [
                'hora_inicio' => $horaInicio->format('H:i'),
                'hora_fin' => $horaSiguiente->format('H:i') === '00:00' ? '00:00' : $horaSiguiente->format('H:i'),
                'disponible' => !$ocupado,
                'estado' => $ocupado ? 'ocupado' : 'disponible'
            ];

            $horaInicio->add($intervalo);
            
            if ($horaInicio->format('H:i:s') === '00:00:00') {
                break;
            }
        }

        return $cronograma;
    }

    private function verificarIntervaloOcupado($horaInicio, $horaFin, $reservasExistentes) {
        foreach ($reservasExistentes as $reserva) {
            $reservaInicio = $reserva['hora_inicio'];
            $reservaFin = $reserva['hora_fin'];

            if (($horaInicio >= $reservaInicio && $horaInicio < $reservaFin) ||
                ($horaFin > $reservaInicio && $horaFin <= $reservaFin) ||
                ($horaInicio <= $reservaInicio && $horaFin >= $reservaFin)) {
                return true;
            }
        }
        return false;
    }

    public function obtenerReservasPorInstitucion($idInstitucion) {
        $sql = "SELECT * FROM reservas WHERE id_institucion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idInstitucion);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
}