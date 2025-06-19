<?php
session_start();

// Verificar si el usuario está autenticado como institución deportiva
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'instalacion') {
    header("Location: ../Auth/login.php");
    exit();
}

$torneoId = $_GET['torneo_id'] ?? null;
if (!$torneoId) {
    header("Location: torneos.php");
    exit();
}

include_once 'header.php';
?>

<link rel="stylesheet" href="../../Public/cssInsDepor/gestionar_torneo.css">

<div class="gestionar-container">
    <div class="gestionar-header">
        <div class="header-info">
            <h2><i class="fas fa-cogs"></i> Gestionar Torneo</h2>
            <div id="torneoInfo" class="torneo-info-header">
                <!-- Info del torneo se carga dinámicamente -->
            </div>
        </div>
        <div class="header-actions">
            <button class="btn-secondary" onclick="window.location.href='torneos.php'">
                <i class="fas fa-arrow-left"></i> Volver
            </button>
            <button class="btn-primary" onclick="actualizarDatos()">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
        </div>
    </div>

    <!-- Tabs de gestión -->
    <div class="gestionar-tabs">
        <button class="tab-btn active" data-tab="llaves">
            <i class="fas fa-sitemap"></i> Llaves del Torneo
        </button>
        <button class="tab-btn" data-tab="equipos">
            <i class="fas fa-users"></i> Equipos Inscritos
        </button>
        <button class="tab-btn" data-tab="estadisticas">
            <i class="fas fa-chart-bar"></i> Estadísticas
        </button>
    </div>

    <!-- Contenido de llaves -->
    <div class="tab-content active" id="llaves">
        <div class="llaves-container" id="llavesContainer">
            <div class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Cargando llaves del torneo...</p>
            </div>
        </div>
    </div>

    <!-- Contenido de equipos -->
    <div class="tab-content" id="equipos">
        <div class="equipos-container" id="equiposContainer">
            <div class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Cargando equipos inscritos...</p>
            </div>
        </div>
    </div>

    <!-- Contenido de estadísticas -->
    <div class="tab-content" id="estadisticas">
        <div class="estadisticas-container" id="estadisticasContainer">
            <div class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Cargando estadísticas...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar resultado de partido -->
<div class="modal-overlay" id="modalEditarPartido" style="display: none;">
    <div class="modal-partido">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Gestionar Partido</h3>
            <button class="btn-close" onclick="cerrarModalPartido()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="editarPartidoContent">
            <!-- Contenido se carga dinámicamente -->
        </div>
    </div>
</div>

<script>
// Pasar el ID del torneo al JavaScript
window.torneoId = <?php echo json_encode($torneoId); ?>;
</script>
<script src="../../Public/js/gestionar_torneo.js"></script>

<?php include_once 'footer.php'; ?>