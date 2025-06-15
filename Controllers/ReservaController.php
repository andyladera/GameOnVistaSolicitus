<?php
require_once __DIR__ . '/../Models/ReservaModel.php';

class ReservaController {
    private $reservaModel;

    public function __construct() {
        $this->reservaModel = new ReservaModel();
    }

    public function obtenerReservas() {
        // Manejar diferentes acciones
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $action = $_GET['action'] ?? '';
            
            switch($action) {
                case 'getCronograma':
                    $this->obtenerCronograma();
                    break;
                case 'getHorarios':
                    $this->obtenerCronograma();
                    break;
                case 'obtener_eventos_mes':
                    $this->obtenerEventosMes();
                    break;
                case 'obtener_proximas_reservas':
                    $this->obtenerProximasReservas();
                    break;
                case 'obtener_proximos_torneos':
                    $this->obtenerProximosTorneos();
                    break;
                case 'obtener_equipos_usuario':
                    $this->obtenerEquiposUsuario();
                    break;
                default:
                    $this->sendError('Acción no válida');
                    break;
            }
        }
    }

    private function obtenerEventosMes() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->sendError('Usuario no autenticado');
            return;
        }

        $fechaInicio = $_GET['fecha_inicio'] ?? '';
        $fechaFin = $_GET['fecha_fin'] ?? '';

        if (!$fechaInicio || !$fechaFin) {
            $this->sendError('Fechas requeridas');
            return;
        }

        try {
            $userId = $_SESSION['user_id'];
            
            $reservas = $this->reservaModel->obtenerReservasPorFecha($userId, $fechaInicio, $fechaFin);
            $torneos = $this->reservaModel->obtenerTorneosPorFecha($userId, $fechaInicio, $fechaFin);
            
            $eventos = [];
            
            // Procesar reservas (ahora sin duplicados)
            foreach ($reservas as $reserva) {
                $eventos[] = [
                    'fecha' => $reserva['fecha'],
                    'tipo' => 'reserva',
                    'titulo' => $reserva['deporte'] . ' ' . substr($reserva['hora_inicio'], 0, 5),
                    'detalle' => $reserva['instalacion'] . ' (' . $reserva['estado'] . ')'
                ];
            }
            
            // Procesar torneos (ahora sin duplicados y con deporte)
            foreach ($torneos as $torneo) {
                $eventos[] = [
                    'fecha' => substr($torneo['fecha'], 0, 10),
                    'tipo' => 'torneo',
                    'titulo' => $torneo['deporte_nombre'] . ' - ' . $torneo['torneo_nombre'],
                    'detalle' => $torneo['partido_detalle'] . ' - ' . $torneo['sede_nombre']
                ];
            }
            
            $this->sendSuccess($eventos);
            
        } catch (Exception $e) {
            $this->sendError('Error obteniendo eventos: ' . $e->getMessage());
        }
    }

    private function obtenerProximasReservas() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->sendError('Usuario no autenticado');
            return;
        }

        try {
            $userId = $_SESSION['user_id'];
            $reservas = $this->reservaModel->obtenerProximasReservas($userId, 5);
            $this->sendSuccess($reservas);
        } catch (Exception $e) {
            $this->sendError('Error obteniendo próximas reservas: ' . $e->getMessage());
        }
    }

    private function obtenerProximosTorneos() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->sendError('Usuario no autenticado');
            return;
        }

        try {
            $userId = $_SESSION['user_id'];
            $torneos = $this->reservaModel->obtenerProximosTorneos($userId, 5);
            $this->sendSuccess($torneos);
        } catch (Exception $e) {
            $this->sendError('Error obteniendo próximos torneos: ' . $e->getMessage());
        }
    }

    private function obtenerEquiposUsuario() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->sendError('Usuario no autenticado');
            return;
        }

        try {
            $userId = $_SESSION['user_id'];
            $equipos = $this->reservaModel->obtenerEquiposUsuario($userId);
            $this->sendSuccess($equipos);
        } catch (Exception $e) {
            $this->sendError('Error obteniendo equipos del usuario: ' . $e->getMessage());
        }
    }

    private function obtenerCronograma() {
        try {
            $idInstitucion = intval($_GET['id']);
            $fecha = $_GET['fecha'] ?? null;

            if ($idInstitucion <= 0) {
                throw new Exception('ID de institución inválido');
            }

            $cronograma = $this->reservaModel->obtenerCronogramaDisponibilidad($idInstitucion, $fecha);
            
            if (!$cronograma) {
                throw new Exception('Institución no encontrada');
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $cronograma
            ]);

        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    private function sendSuccess($data) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit;
    }

    private function sendError($message) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
}

$controller = new ReservaController();
$controller->obtenerReservas();