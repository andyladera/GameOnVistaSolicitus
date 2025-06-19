<?php
require_once __DIR__ . '/../Config/database.php';

class TorneosPartidosModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // ✅ CREAR PARTIDO DE TORNEO CON ÁREA
    public function crearPartidoTorneo($torneoId, $areaId, $fecha, $horaInicio, $horaFin, $partidoInfo) {
        // Mapear las fases del frontend a las permitidas en la BD
        $faseMap = [
            'Primera Ronda' => 'primera_ronda',
            'Ronda 2' => 'segunda_ronda',
            'Ronda 3' => 'tercera_ronda',
            'Cuartos de Final' => 'cuartos',
            'Semifinal' => 'semifinal',
            'Final' => 'final',
            'Tercer Lugar' => 'tercer_lugar'
        ];
        
        $faseBD = $faseMap[$partidoInfo['fase']] ?? 'primera_ronda';
        
        $sql = "INSERT INTO torneos_partidos 
                (torneo_id, area_deportiva_id, fase, numero_partido, ronda, 
                 descripcion_partido, fecha_partido, estado_partido) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'programado')";
        
        // Combinar fecha y hora
        $fechaPartido = $fecha . ' ' . $horaInicio;
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisisss", 
            $torneoId, 
            $areaId, 
            $faseBD, 
            $partidoInfo['numeroPartido'],
            $partidoInfo['ronda'],
            $partidoInfo['descripcion'],
            $fechaPartido
        );
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'partido_id' => $this->conn->insert_id,
                'message' => 'Partido creado exitosamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al crear partido: ' . $stmt->error
            ];
        }
    }

    // ✅ OBTENER PARTIDOS DE UN TORNEO
    public function getPartidosByTorneo($torneoId) {
        $sql = "SELECT tp.*, 
                       ad.nombre_area,
                       ad.tarifa_por_hora,
                       el.nombre as equipo_local_nombre,
                       ev.nombre as equipo_visitante_nombre,
                       DATE(tp.fecha_partido) as fecha,
                       TIME(tp.fecha_partido) as hora
                FROM torneos_partidos tp
                LEFT JOIN areas_deportivas ad ON tp.area_deportiva_id = ad.id
                LEFT JOIN equipos el ON tp.equipo_local_id = el.id
                LEFT JOIN equipos ev ON tp.equipo_visitante_id = ev.id
                WHERE tp.torneo_id = ?
                ORDER BY tp.fecha_partido ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $torneoId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ✅ VERIFICAR SI ÁREA ESTÁ LIBRE PARA PARTIDO
    public function verificarDisponibilidadArea($areaId, $fecha, $horaInicio, $horaFin) {
        // Verificar conflictos en reservas normales
        $sqlReservas = "SELECT COUNT(*) as conflictos 
                        FROM reservas 
                        WHERE area_deportiva_id = ? 
                        AND fecha = ? 
                        AND estado IN ('confirmada', 'pendiente')
                        AND (
                            (hora_inicio <= ? AND hora_fin > ?) OR 
                            (hora_inicio < ? AND hora_fin >= ?) OR
                            (hora_inicio >= ? AND hora_fin <= ?)
                        )";
        
        $stmt = $this->conn->prepare($sqlReservas);
        $stmt->bind_param("isssssss", $areaId, $fecha, $horaInicio, $horaInicio, $horaFin, $horaFin, $horaInicio, $horaFin);
        $stmt->execute();
        $result = $stmt->get_result();
        $conflictos = $result->fetch_assoc();
        
        if ($conflictos['conflictos'] > 0) {
            return false;
        }

        // Verificar conflictos en partidos de torneos
        $sqlPartidos = "SELECT COUNT(*) as conflictos 
                        FROM torneos_partidos 
                        WHERE area_deportiva_id = ? 
                        AND DATE(fecha_partido) = ? 
                        AND estado_partido IN ('programado', 'en_curso')
                        AND (
                            (TIME(fecha_partido) <= ? AND DATE_ADD(fecha_partido, INTERVAL 1 HOUR) > ?) OR
                            (TIME(fecha_partido) < ? AND DATE_ADD(fecha_partido, INTERVAL 1 HOUR) >= ?) OR
                            (TIME(fecha_partido) >= ? AND TIME(fecha_partido) <= ?)
                        )";
        
        $stmt = $this->conn->prepare($sqlPartidos);
        $stmt->bind_param("isssssss", $areaId, $fecha, $horaInicio, $horaInicio, $horaFin, $horaFin, $horaInicio, $horaFin);
        $stmt->execute();
        $result = $stmt->get_result();
        $conflictosPartidos = $result->fetch_assoc();
        
        return $conflictosPartidos['conflictos'] == 0;
    }

    // ✅ ELIMINAR PARTIDO
    public function eliminarPartido($partidoId) {
        $sql = "DELETE FROM torneos_partidos WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $partidoId);
        return $stmt->execute();
    }

    // Nueva función para obtener estructura del torneo
    public function obtenerEstructuraTorneo($torneoId) {
        $sql = "SELECT tp.*, 
                   ad.nombre_area,
                   ad.tarifa_por_hora,
                   el.nombre as equipo_local_nombre,
                   ev.nombre as equipo_visitante_nombre,
                   DATE(tp.fecha_partido) as fecha,
                   TIME(tp.fecha_partido) as hora
            FROM torneos_partidos tp
            LEFT JOIN areas_deportivas ad ON tp.area_deportiva_id = ad.id
            LEFT JOIN equipos el ON tp.equipo_local_id = el.id
            LEFT JOIN equipos ev ON tp.equipo_visitante_id = ev.id
            WHERE tp.torneo_id = ?
            ORDER BY tp.ronda ASC, tp.numero_partido ASC";
    
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $torneoId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function actualizarResultadoPartido($partidoId, $datos) {
        // Actualizar resultado básico
        $sql = "UPDATE torneos_partidos 
                SET resultado_local = ?, 
                    resultado_visitante = ?, 
                    estado_partido = 'finalizado',
                    observaciones = ?
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisi", 
            $datos['resultado_local'],
            $datos['resultado_visitante'],
            $datos['observaciones'],
            $partidoId
        );
        
        if (!$stmt->execute()) {
            return ['success' => false, 'message' => 'Error al actualizar resultado'];
        }
        
        // Determinar ganador
        if ($datos['resultado_local'] > $datos['resultado_visitante']) {
            $ganadorId = $datos['equipo_local_id'] ?? null;
        } elseif ($datos['resultado_visitante'] > $datos['resultado_local']) {
            $ganadorId = $datos['equipo_visitante_id'] ?? null;
        } else {
            $ganadorId = null; // Empate
        }
        
        if ($ganadorId) {
            $sql = "UPDATE torneos_partidos SET equipo_ganador_id = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $ganadorId, $partidoId);
            $stmt->execute();
        }
        
        // Guardar estadísticas detalladas si se proporcionan
        if (!empty($datos['estadisticas'])) {
            $this->guardarEstadisticasPartido($partidoId, $datos['estadisticas']);
        }
        
        // Guardar goleadores si se proporcionan
        if (!empty($datos['goleadores'])) {
            $this->guardarGoleadoresPartido($partidoId, $datos['goleadores']);
        }
        
        return ['success' => true, 'message' => 'Resultado actualizado correctamente'];
    }

    private function guardarEstadisticasPartido($partidoId, $estadisticas) {
        foreach ($estadisticas as $equipoId => $stats) {
            $sql = "INSERT INTO torneos_partidos_estadisticas 
                    (partido_id, equipo_id, goles, tarjetas_amarillas, tarjetas_rojas, mvp_jugador_id)
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    goles = VALUES(goles),
                    tarjetas_amarillas = VALUES(tarjetas_amarillas),
                    tarjetas_rojas = VALUES(tarjetas_rojas),
                    mvp_jugador_id = VALUES(mvp_jugador_id)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iiiiii", 
                $partidoId,
                $equipoId,
                $stats['goles'],
                $stats['tarjetas_amarillas'],
                $stats['tarjetas_rojas'],
                $stats['mvp_jugador_id']
            );
            $stmt->execute();
        }
    }

    private function guardarGoleadoresPartido($partidoId, $goleadores) {
        // Primero eliminar goleadores existentes
        $sql = "DELETE FROM torneos_partidos_goleadores WHERE partido_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $partidoId);
        $stmt->execute();
        
        // Insertar nuevos goleadores
        foreach ($goleadores as $gol) {
            $sql = "INSERT INTO torneos_partidos_goleadores 
                    (partido_id, jugador_id, equipo_id, minuto_gol, tipo_gol)
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iiiis", 
                $partidoId,
                $gol['jugador_id'],
                $gol['equipo_id'],
                $gol['minuto_gol'],
                $gol['tipo_gol']
            );
            $stmt->execute();
        }
    }
}
?>