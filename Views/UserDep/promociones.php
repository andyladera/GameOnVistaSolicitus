<?php
session_start();

// Verificar si el usuario está autenticado como deportista
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'deportista') {
    header("Location: ../Auth/login.php");
    exit();
}

// Incluir el controlador
// require_once '../../Controllers/EquiposModel.php';

// Incluir cabecera
include_once 'header.php';
?>

<div class="container mt-4">
    <h2 class="mb-4">Promociones Deportivas</h2>
    
    <!-- UN PANEL -->
    <div class="dashboard-wide-card">
        <h2>MAPA DE INSTALACIONES DEPORTIVAS</h2>
    </div>

    <div class="dashboard-row">
        <!-- ZONA IZQUIERDA DEL PANEL -->
        <div class="dashboard-card">
            <h2>LISTADOS DE INSTALACIONES DEPORTIVAS</h2>
            
        </div>

        <!-- ZONA DERECHA DEL PANEL -->
        <div class="dashboard-card">
            <h2>FILTROS</h2>
            
        </div>
    </div>
</div>

<?php
// Incluir pie de página (corregida la ruta)
include_once 'footer.php';
?>