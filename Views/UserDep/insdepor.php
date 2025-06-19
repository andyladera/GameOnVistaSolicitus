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

<link rel="stylesheet" href="../../Public/css/insdepor_dep.css">
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
                    <div class="card mb-3 instalacion-card card-with-image <?= ($highlightId == $instalacion['id']) ? 'highlight' : '' ?>" 
                         data-id="<?= $instalacion['id'] ?>" 
                         data-deportes="<?= implode(',', array_column($instalacion['deportes'], 'id')) ?>" 
                         data-calificacion="<?= $instalacion['calificacion'] ?>">
                        
                        <!-- ✅ CONTENEDOR DE IMAGEN -->
                        <div class="card-image-container">
                            <?php if (!empty($instalacion['imagen'])): ?>
                                <img src="<?= htmlspecialchars($instalacion['imagen']) ?>" 
                                     alt="<?= htmlspecialchars($instalacion['nombre']) ?>" 
                                     class="card-image"
                                     loading="lazy"
                                     onerror="this.parentElement.innerHTML='<div class=\'image-placeholder\'><i class=\'fas fa-building\'></i><span>Imagen no disponible</span></div>'">
                                <div class="card-image-overlay">
                                    <i class="fas fa-eye"></i> Ver instalación
                                </div>
                            <?php else: ?>
                                <div class="image-placeholder">
                                    <i class="fas fa-building"></i>
                                    <span>Sin imagen</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- ✅ CONTENIDO DE LA TARJETA -->
                        <div class="card-content-area">
                            <!-- Header -->
                            <div class="card-header-section">
                                <h2 class="card-title" style="border: none; padding: 0; margin-bottom: 8px;">
                                    <i class="fas fa-building"></i>
                                    <?= htmlspecialchars($instalacion['nombre']) ?>
                                </h2>
                                
                                <!-- Calificación -->
                                <div class="rating-badge">
                                    <i class="fas fa-star"></i>
                                    <?= number_format($instalacion['calificacion'], 1) ?>
                                </div>
                            </div>
                            
                            <!-- Información -->
                            <div class="card-info-section">
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><strong>Dirección:</strong> <?= htmlspecialchars($instalacion['direccion']) ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <span><strong>Tarifa:</strong> S/. <?= number_format($instalacion['tarifa'], 2) ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <i class="fas fa-phone"></i>
                                    <span><strong>Contacto:</strong> <?= htmlspecialchars($instalacion['telefono']) ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <i class="fas fa-envelope"></i>
                                    <span><?= htmlspecialchars($instalacion['email']) ?></span>
                                </div>
                                
                                <!-- Deportes disponibles -->
                                <div class="sports-tags">
                                    <?php foreach ($instalacion['deportes'] as $deporte): ?>
                                        <span class="sport-tag">
                                            <i class="fas fa-<?= obtenerIconoDeporte($deporte['nombre']) ?>"></i>
                                            <?= ucfirst($deporte['nombre']) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Acciones -->
                            <div class="card-actions-section">
                                <div class="action-buttons">
                                    <button class="action-btn btn-horarios" data-id="<?= $instalacion['id'] ?>">
                                        <i class="fas fa-clock"></i> Horarios
                                    </button>
                                    
                                    <button class="action-btn btn-cronograma" data-id="<?= $instalacion['id'] ?>">
                                        <i class="fas fa-calendar"></i> Cronograma
                                    </button>
                                    
                                    <button class="action-btn btn-mapa" 
                                            data-lat="<?= $instalacion['latitud'] ?>" 
                                            data-lng="<?= $instalacion['longitud'] ?>" 
                                            data-nombre="<?= htmlspecialchars($instalacion['nombre']) ?>">
                                        <i class="fas fa-map"></i> Mapa
                                    </button>
                                    
                                    <button class="action-btn btn-comentarios" data-id="<?= $instalacion['id'] ?>">
                                        <i class="fas fa-comments"></i> Comentarios
                                    </button>
                                    
                                    <button class="action-btn btn-imagenes" data-id="<?= $instalacion['id'] ?>">
                                        <i class="fas fa-images"></i> Galería
                                    </button>
                                    
                                    <button class="action-btn btn-reservar" data-id="<?= $instalacion['id'] ?>">
                                        <i class="fas fa-calendar-plus"></i> RESERVAR AHORA
                                    </button>
                                </div>
                                
                                <!-- Horarios expandibles -->
                                <div class="horarios-container" id="horarios-<?= $instalacion['id'] ?>" style="display: none;">
                                    <h6><i class="fas fa-clock"></i> Horarios de atención:</h6>
                                    <div class="row">
                                        <?php foreach ($instalacion['horarios'] as $dia => $horario): ?>
                                        <div class="col-md-6 mb-2">
                                            <strong><?= $dia ?>:</strong> <?= $horario ?>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
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


// ✅ FUNCIÓN PARA OBTENER ICONOS DE DEPORTES
function obtenerIconoDeporte($nombreDeporte) {
    $iconos = [
        'futbol' => 'futbol',
        'fútbol' => 'futbol',
        'basketball' => 'basketball-ball',
        'basquet' => 'basketball-ball',
        'básquet' => 'basketball-ball',
        'tenis' => 'table-tennis',
        'voley' => 'volleyball-ball',
        'vóley' => 'volleyball-ball',
        'volleyball' => 'volleyball-ball',
        'natacion' => 'swimmer',
        'natación' => 'swimmer',
        'running' => 'running',
        'atletismo' => 'running',
        'ciclismo' => 'biking',
        'boxeo' => 'fist-raised',
        'gimnasia' => 'dumbbell',
    ];
    
    $nombre = strtolower($nombreDeporte);
    return $iconos[$nombre] ?? 'running';
}
?>