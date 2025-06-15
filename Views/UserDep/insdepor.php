<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'deportista') {
    header("Location: ../Auth/login.php");
    exit();
}
require_once '../../Controllers/InsDeporController.php';
$insDeporController = new InsDeporController();
$instalaciones = $insDeporController->getInstalacionesCompletas();

// Obtener ID de instalación a resaltar si viene del dashboard
$highlightId = isset($_GET['highlight']) ? (int)$_GET['highlight'] : null;

include_once 'header.php';
?>

<!-- Agregar CSS específico para modales de instalaciones -->
<link rel="stylesheet" href="../../Public/css/modal_insdepor.css">

<div class="container mt-4">
    <div class="dashboard-row">
        <!-- Instalaciones Deportivas -->
        <div class="dashboard-card">
            <h2>MAPA DE INSTALACIONES DEPORTIVAS</h2>
            <div id="map" style="height: 400px; width:100%; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Cargando mapa...</span>
                    </div>
                    <p>Cargando mapa de instalaciones...</p>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="dashboard-card">
            <h2>FILTROS</h2>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="busquedaNombre">Buscar por nombre:</label>
                        <input type="text" class="form-control" id="busquedaNombre" placeholder="Nombre de la instalación">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filtroDeporte">Filtrar por deporte:</label>
                        <select class="form-control" id="filtroDeporte">
                            <option value="">Todos los deportes</option>
                            <option value="1">Fútbol</option>
                            <option value="2">Voley</option>
                            <option value="3">Básquet</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filtroCalificacion">Calificación mínima:</label>
                        <select class="form-control" id="filtroCalificacion">
                            <option value="0">Todas</option>
                            <option value="3">3 estrellas o más</option>
                            <option value="4">4 estrellas o más</option>
                            <option value="4.5">4.5 estrellas o más</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <button id="btnFiltrar" class="btn btn-primary">Aplicar filtros</button>
                <button id="btnCercanas" class="btn btn-primary">Instalaciones cercanas</button>
            </div>
        </div>
    </div>

    <!-- mapa de instalaciones -->
    <div class="dashboard-wide-card">
        <h2>LISTADOS DE INSTALACIONES DEPORTIVAS</h2>
            <div id="listaInstalaciones">
                <?php foreach ($instalaciones as $instalacion): ?>
                    <div class="card mb-3 instalacion-card <?= ($highlightId == $instalacion['id']) ? 'highlight' : '' ?>" data-id="<?= $instalacion['id'] ?>" data-deportes="<?= implode(',', array_column($instalacion['deportes'], 'id')) ?>" data-calificacion="<?= $instalacion['calificacion'] ?>">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h2 class="card-title"><?= $instalacion['nombre'] ?></h2>
                                    <p class="card-text">
                                        <strong>Dirección:</strong> <?= $instalacion['direccion'] ?><br>
                                        <strong>Tarifa:</strong> S/. <?= number_format($instalacion['tarifa'], 2) ?><br>
                                        <strong>Contacto:</strong> <?= $instalacion['telefono'] ?> | <?= $instalacion['email'] ?><br>
                                        <strong>Deportes:</strong> 
                                        <?php 
                                        $nombresDeportes = array_column($instalacion['deportes'], 'nombre');
                                        echo ucwords(implode(', ', $nombresDeportes)); 
                                        ?>
                                    </p>
                                </div>
                                <div class="col-md-4 text-right">
                                    <div class="calificacion-container">
                                        <span class="badge badge-warning p-2">
                                            <i class="fas fa-star"></i> <?= number_format($instalacion['calificacion'], 1) ?>
                                        </span>
                                    </div>
                                    <button class="btn btn-primary btn-sm mt-2 btn-ver-horarios" data-id="<?= $instalacion['id'] ?>">Ver horarios</button>
                                    <button class="btn btn-primary btn-sm mt-2 btn-ver-cronograma" data-id="<?= $instalacion['id'] ?>">Ver cronograma</button>
                                    <button class="btn btn-primary btn-sm mt-2 btn-ver-mapa" data-lat="<?= $instalacion['latitud'] ?>" data-lng="<?= $instalacion['longitud'] ?>" data-nombre="<?= $instalacion['nombre'] ?>">Ver en mapa</button>
                                    <button class="btn btn-primary btn-sm mt-2 btn-ver-comentarios" data-id="<?= $instalacion['id'] ?>">Ver Comentarios</button>
                                    <button class="btn btn-primary btn-sm mt-2 btn-ver-imagenes" data-id="<?= $instalacion['id'] ?>">Ver Imagenes</button>
                                </div>
                            </div>
                            <div class="horarios-container" id="horarios-<?= $instalacion['id'] ?>" style="display: none;">
                                <hr>
                                <h6>Horarios de atención:</h6>
                                <div class="row">
                                    <?php foreach ($instalacion['horarios'] as $dia => $horario): ?>
                                    <div class="col-md-3 mb-2">
                                        <strong><?= $dia ?>:</strong> <?= $horario ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <h2>🏆🥋🥊🏓🎾🏸🏈🏉🎱🎳⛳⛸️⚽</h2>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
    </div>
</div>

<!-- Modal para cronograma/comentarios/imágenes -->
<div id="modal-horarios" class="modal-horarios">
    <div class="modal-horarios-backdrop"></div>
    <div class="modal-horarios-container">
        <div class="modal-horarios-header">
            <h3 class="modal-horarios-title">Información</h3>
            <button id="modal-horarios-close" class="modal-horarios-close">&times;</button>
        </div>
        <div class="modal-horarios-body">
            <div class="modal-horarios-content">
                <!-- El contenido se llenará dinámicamente -->
            </div>
        </div>
    </div>
</div>

<!-- Deshabilitar chat en página de instalaciones deportivas -->
<script>
window.chatDisabled = true;
</script>

<!-- Scripts -->
<script src="../../Public/js/insdepor.js"></script>
<script>
    // Función global para inicializar el mapa (requerida por Google Maps API)
    function initMap() {
        console.log('initMap llamada desde Google Maps API');
        if (window.insDeporManager) {
            window.insDeporManager.initMap();
        } else {
            console.log('insDeporManager no está disponible aún, esperando...');
            setTimeout(() => {
                if (window.insDeporManager) {
                    window.insDeporManager.initMap();
                } else {
                    console.error('insDeporManager no se pudo cargar');
                    document.getElementById('map').innerHTML = '<div class="text-center text-danger"><i class="fas fa-exclamation-triangle"></i><br>Error cargando el mapa</div>';
                }
            }, 1000);
        }
    }

    // Función para manejar errores del mapa
    function handleMapError() {
        console.error('Error cargando Google Maps API');
        document.getElementById('map').innerHTML = '<div class="text-center text-warning"><i class="fas fa-map-marked-alt"></i><br>Mapa no disponible temporalmente</div>';
    }

    // Cargar Google Maps API de forma asíncrona
    function loadGoogleMaps() {
        // Verificar si ya está cargado
        if (window.google && window.google.maps) {
            initMap();
            return;
        }

        // Crear script para cargar Google Maps
        const script = document.createElement('script');
        script.src = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyBjRa0PWfLEyt1Ba02c-3M6zWnyEM7lU2A&loading=async&callback=initMap';
        script.async = true;
        script.defer = true;
        script.onerror = handleMapError;
        document.head.appendChild(script);
    }

    // Inicializar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        const instalacionesData = <?= json_encode($instalaciones) ?>;
        const highlightId = <?= json_encode($highlightId) ?>;
        
        window.insDeporManager = new InsDeporManager(instalacionesData);
        
        // Si hay una instalación para resaltar, hacerlo después de cargar
        if (highlightId) {
            setTimeout(() => {
                const instalacion = document.querySelector(`.instalacion-card[data-id="${highlightId}"]`);
                if (instalacion) {
                    instalacion.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // Mantener el resaltado por más tiempo cuando viene del dashboard
                    setTimeout(() => {
                        instalacion.classList.remove('highlight');
                    }, 5000);
                }
            }, 1000);
        }
        
        // Cargar el mapa después de un breve retraso
        setTimeout(loadGoogleMaps, 500);
    });
</script>

<?php
include_once 'footer.php';
?>