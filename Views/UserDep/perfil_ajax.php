<?php
// filepath: c:\xampp\htdocs\GameOn_Network\Views\UserDep\perfil_ajax.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'deportista') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

require_once '../../Controllers/PerfilController.php';
$perfilController = new PerfilController();
$userId = $_SESSION['user_id'];

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'getDeportes':
        $deportes = $perfilController->getDeportes();
        $deportesUsuario = $perfilController->getDeportesUsuario($userId);
        $deportesUsuarioIds = array_column($deportesUsuario, 'id');
        
        echo json_encode([
            'success' => true, 
            'deportes' => $deportes,
            'deportesUsuario' => $deportesUsuarioIds
        ]);
        break;
        
    case 'agregarDeporte':
        $deporteId = $_POST['deporte_id'] ?? '';
        if (empty($deporteId)) {
            echo json_encode(['success' => false, 'message' => 'ID de deporte requerido']);
            break;
        }
        
        $result = $perfilController->agregarDeporte($userId, $deporteId);
        echo json_encode($result);
        break;
        
    case 'eliminarDeporte':
        $deporteId = $_POST['deporte_id'] ?? '';
        if (empty($deporteId)) {
            echo json_encode(['success' => false, 'message' => 'ID de deporte requerido']);
            break;
        }
        
        $result = $perfilController->eliminarDeporte($userId, $deporteId);
        echo json_encode($result);
        break;
        
    case 'getPerfil':
        $perfil = $perfilController->getPerfilDeportista($userId);
        echo json_encode(['success' => true, 'perfil' => $perfil]);
        break;
        
    case 'actualizarPerfil':
        $datos = [
            'nombre' => $_POST['nombre'] ?? '',
            'apellidos' => $_POST['apellidos'] ?? '',
            'telefono' => $_POST['telefono'] ?? '',
            'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? '',
            'genero' => $_POST['genero'] ?? '',
            'email' => $_POST['email'] ?? ''
        ];
        
        // Validar datos requeridos
        if (empty($datos['nombre']) || empty($datos['apellidos'])) {
            echo json_encode(['success' => false, 'message' => 'Nombre y apellidos son requeridos']);
            break;
        }
        
        $result = $perfilController->actualizarPerfil($userId, $datos);
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}
?>