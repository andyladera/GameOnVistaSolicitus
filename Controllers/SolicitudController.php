<?php
session_start();
require_once __DIR__ . '/../Models/Solicitud.php';

// Simulación de ID de administrador - En un sistema real, esto vendría de la sesión
if (!isset($_SESSION['admin_id'])) {
    // Para este ejemplo, asumiremos que el admin con ID 1 está logueado.
    // En tu implementación final, deberías tener un login de admin que establezca este valor.
    $_SESSION['admin_id'] = 1; 
}
$admin_id = $_SESSION['admin_id'];

$solicitudModel = new Solicitud();
$message = '';
$error = '';

// Procesar acciones de POST (aprobar/rechazar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $solicitud_id = $_POST['solicitud_id'] ?? 0;

    if ($action === 'aprobar') {
        $nuevo_usuario_id = $solicitudModel->aprobar($solicitud_id, $admin_id);
        if ($nuevo_usuario_id) {
            $message = "Solicitud aprobada con éxito. Se ha creado el nuevo usuario.";
            // TODO: Aquí irá la lógica para disparar el correo con EmailJS
        } else {
            // Usar el mensaje de error específico del modelo
            $error = $solicitudModel->error ?? "Error al aprobar la solicitud. Es posible que ya haya sido procesada o hubo un error en la base de datos.";
        }
    } elseif ($action === 'rechazar') {
        $motivo = $_POST['motivo_rechazo'] ?? 'No se especificó un motivo.';
        if ($solicitudModel->rechazar($solicitud_id, $admin_id, $motivo)) {
            $message = "Solicitud rechazada correctamente.";
            // TODO: Aquí irá la lógica para disparar el correo con EmailJS
        } else {
            $error = "Error al rechazar la solicitud.";
        }
    }
}

// Obtener todas las solicitudes para mostrarlas en la vista
$solicitudes_pendientes = $solicitudModel->obtenerTodas('pendiente');
$solicitudes_aprobadas = $solicitudModel->obtenerTodas('aprobada');
$solicitudes_rechazadas = $solicitudModel->obtenerTodas('rechazada');

?>
