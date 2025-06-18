<?php
session_start();

// Verificar si el usuario está autenticado como institución deportiva
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'instalacion') {
    header("Location: ../Auth/login.php");
    exit();
}

// Usar controladores
require_once '../../Controllers/InsDeporController.php';

$insDeporController = new InsDeporController();
$usuarioInstalacionId = $_SESSION['user_id'];

// Obtener información del usuario para mostrar torneos relevantes
$misInstalaciones = $insDeporController->getInstalacionesPorUsuario($usuarioInstalacionId);

include_once 'header.php';
?>

<link rel="stylesheet" href="../../Public/cssInsDepor/torneos.css">

<div class="torneos-container">
    <!-- Header de torneos -->
    <div class="torneos-header">
        <div class="header-content">
            <h2><i class="fas fa-trophy"></i> Gestión de Torneos</h2>
            <p>Administra y organiza torneos deportivos en tus instalaciones</p>
        </div>
        <div class="header-actions">
            <a href="crear_torneo.php" class="btn-primary-torneos">
                <i class="fas fa-plus"></i> Crear Torneo
            </a>
            <button class="btn-primary-torneos" onclick="window.location.reload()">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="torneos-stats">
        <div class="stat-card-torneo">
            <div class="stat-icon-torneo green">
                <i class="fas fa-trophy"></i>
            </div>
            <div class="stat-content-torneo">
                <h3 id="totalTorneos">-</h3>
                <p>Total Torneos</p>
            </div>
        </div>
        <div class="stat-card-torneo">
            <div class="stat-icon-torneo blue">
                <i class="fas fa-play-circle"></i>
            </div>
            <div class="stat-content-torneo">
                <h3 id="torneosActivos">-</h3>
                <p>En Curso</p>
            </div>
        </div>
        <div class="stat-card-torneo">
            <div class="stat-icon-torneo orange">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content-torneo">
                <h3 id="torneosProximos">-</h3>
                <p>Próximos</p>
            </div>
        </div>
        <div class="stat-card-torneo">
            <div class="stat-icon-torneo purple">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content-torneo">
                <h3 id="torneosFinalizados">-</h3>
                <p>Finalizados</p>
            </div>
        </div>
    </div>

    <!-- Filtros y controles -->
    <div class="torneos-controls">
        <div class="filtros-torneos">
            <div class="filtro-grupo">
                <label for="filtroEstado">Estado:</label>
                <select id="filtroEstado">
                    <option value="">Todos los estados</option>
                    <option value="proximo">Próximo</option>
                    <option value="inscripciones_abiertas">Inscripciones Abiertas</option>
                    <option value="inscripciones_cerradas">Inscripciones Cerradas</option>
                    <option value="activo">En Curso</option>
                    <option value="finalizado">Finalizado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
            <div class="filtro-grupo">
                <label for="filtroDeporte">Deporte:</label>
                <select id="filtroDeporte">
                    <option value="">Todos los deportes</option>
                    <option value="1">Fútbol</option>
                    <option value="2">Vóley</option>
                    <option value="3">Básquet</option>
                </select>
            </div>
        </div>
        
        <div class="vista-controles">
            <button class="btn-vista active" data-vista="grid" title="Vista en tarjetas">
                <i class="fas fa-th-large"></i>
            </button>
            <button class="btn-vista" data-vista="lista" title="Vista en lista">
                <i class="fas fa-list"></i>
            </button>
        </div>
    </div>

    <!-- Grid de torneos (Vista por defecto) -->
    <div class="torneos-grid" id="torneosGrid">
        <!-- Los torneos se cargarán dinámicamente aquí -->
    </div>

    <!-- Lista de torneos (Vista alternativa) -->
    <div class="torneos-lista" id="torneosLista" style="display: none;">
        <!-- La lista se cargará dinámicamente aquí -->
    </div>
</div>

<!-- Modal para ver detalles del torneo -->
<div class="modal-overlay" id="modalDetalles" style="display: none;">
    <div class="modal-torneo">
        <div class="modal-header">
            <h3><i class="fas fa-info-circle"></i> Detalles del Torneo</h3>
            <button class="btn-close" onclick="cerrarModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="detallesContent">
            <!-- Contenido de detalles se carga dinámicamente -->
        </div>
        <div class="modal-actions">
            <button class="btn-secondary" onclick="cerrarModal()">Cerrar</button>
        </div>
    </div>
</div>

<script src="../../Public/js/torneos_insdepor.js"></script>

<?php include_once 'footer.php'; ?>