<?php
session_start();

// Verificar si el usuario está autenticado como deportista
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'deportista') {
    header("Location: ../Auth/login.php");
    exit();
}

// Obtener las instalaciones deportivas
require_once '../../Controllers/InsDeporController.php';
require_once '../../Controllers/PerfilController.php';

$insDeporController = new InsDeporController();
$perfilController = new PerfilController();
$instalaciones = $insDeporController->getInstalacionesCompletas();

// Obtener datos del perfil y deportes del usuario
$perfilUsuario = $perfilController->getPerfilDeportista($_SESSION['user_id']);
$deportesUsuario = $perfilController->getDeportesUsuario($_SESSION['user_id']);

// Incluir cabecera
include_once 'header.php';
?>

<!-- CSS específico para modales del dashboard -->
<link rel="stylesheet" href="../../Public/css/dashboard_modales.css">

<div class="dashboard-container">
    <div class="dashboard-row">
        <!-- Información Personal -->
        <div class="dashboard-card">
            <h2>Información Personal</h2>
            <div class="user-profile">
                <div class="profile-image">
                    <img src="../../Resources/logo_user.jpg" alt="Foto de perfil">
                </div>
                <div class="profile-info">
                    <h3><?php echo $_SESSION['username']; ?></h3>
                    <p><?php echo $perfilUsuario['nombre'] ?? 'Nombre'; ?> <?php echo $perfilUsuario['apellidos'] ?? 'Apellido'; ?></p>
                    <p><?php echo $perfilUsuario['telefono'] ?? 'Sin teléfono'; ?></p>
                </div>
            </div>
            <button class="btn-outline" onclick="abrirModalPerfil()">Editar Perfil</button>
        </div>

        <!-- Deportes Favoritos -->
        <div class="dashboard-card">
            <h2>Deportes Favoritos</h2>
            <div class="sports-tags" id="deportesFavoritos">
                <?php if (!empty($deportesUsuario)): ?>
                    <?php foreach ($deportesUsuario as $deporte): ?>
                        <span class="sport-tag" data-deporte-id="<?= $deporte['id'] ?>">
                            <?= ucfirst($deporte['nombre']) ?>
                            <i class="fas fa-times ms-1" onclick="eliminarDeporte(<?= $deporte['id'] ?>)" style="cursor: pointer;" title="Eliminar deporte"></i>
                        </span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No tienes deportes agregados</p>
                <?php endif; ?>
            </div>
            <button class="btn-outline" onclick="abrirModalDeportes()">Agregar Deportes</button>
        </div>
    </div>

    <!-- Opciones de Reserva en Tiempo Real -->
    <div class="dashboard-wide-card">
        <h2>Opciones de Reserva en Tiempo Real</h2>
        <div class="reservation-options">
            <?php foreach (array_slice($instalaciones, 0, 3) as $instalacion): ?>
            <div class="reservation-card">
                <h3><?= $instalacion['nombre'] ?></h3>
                <p><strong>Deportes:</strong> <?= implode(', ', array_column($instalacion['deportes'], 'nombre')) ?></p>
                <p><strong>Tarifa:</strong> S/. <?= number_format($instalacion['tarifa'], 2) ?></p>
                <button class="btn-primary" onclick="verInstalacionCompleta(<?= $instalacion['id'] ?>)">Ver Detalles</button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Instalaciones Deportivas Cercanas (MANTENIENDO EL MAPA ORIGINAL) -->
    <div class="dashboard-wide-card">
        <h2>Instalaciones Deportivas Cercanas</h2>
        <div class="map-container">
            <div id="map" style="height: 400px; width:100%; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Cargando mapa...</span>
                    </div>
                    <p>Cargando mapa de instalaciones...</p>
                </div>
            </div>
        </div>
        <!-- Lista de instalaciones cercanas -->
        <div class="nearby-facilities">
            <?php foreach ($instalaciones as $instalacion): ?>
            <div class="facility-item">
                <h3><?= $instalacion['nombre'] ?></h3>
                <p><i class="fas fa-running"></i> <?= implode(', ', array_column($instalacion['deportes'], 'nombre')) ?></p>
                <p><i class="fas fa-map-marker-alt"></i> <?= $instalacion['direccion'] ?></p>
                <p><i class="fas fa-star"></i> <?= number_format($instalacion['calificacion'], 1) ?> estrellas</p>
                <button class="btn-primary btn-sm" onclick="verInstalacionCompleta(<?= $instalacion['id'] ?>)">Ver más</button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Historia de la Reserva -->
    <div class="dashboard-wide-card">
        <h2>Historia de la Reserva</h2>
        <div class="reservation-history">
            <div class="history-card">
                <h3>Cancha de Baloncesto - Tacna Arena</h3>
                <p>Fecha: 12 de mayo, 2023</p>
            </div>
            <div class="history-card">
                <h3>Cancha de Tenis - City Sports Club</h3>
                <p>Fecha: 5 de mayo, 2023</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar Deportes -->
<div id="modalDeportes" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Agregar Deportes</h3>
            <button class="modal-close" onclick="cerrarModal('modalDeportes')">&times;</button>
        </div>
        <div class="modal-body">
            <div id="listaDeportes">
                <div class="loading">Cargando deportes...</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Perfil -->
<div id="modalPerfil" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Editar Perfil</h3>
            <button class="modal-close" onclick="cerrarModal('modalPerfil')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="formPerfil">
                <div class="form-group">
                    <label>Nombre:</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Apellidos:</label>
                    <input type="text" id="apellidos" name="apellidos" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono" class="form-control">
                </div>
                <div class="form-group">
                    <label>Fecha de Nacimiento:</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control">
                </div>
                <div class="form-group">
                    <label>Género:</label>
                    <select id="genero" name="genero" class="form-control">
                        <option value="Masculino">Masculino</option>
                        <option value="Feminino">Femenino</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-outline" onclick="cerrarModal('modalPerfil')">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="../../Public/js/dashboard-perfil.js"></script>
<script src="../../Public/js/insdepor.js"></script>
<script>
    // Función para redirigir a la página de instalaciones con instalación específica
    function verInstalacionCompleta(instalacionId) {
        window.location.href = `insdepor.php?highlight=${instalacionId}`;
    }

    // Función global para inicializar el mapa (requerida por Google Maps API)
    function initMap() {
        console.log('initMap llamada desde Google Maps API - Dashboard');
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
        console.error('Error cargando Google Maps API - Dashboard');
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
        
        // Crear instancia del manager con configuración para dashboard
        window.insDeporManager = new InsDeporManager(instalacionesData);
        
        // Cargar el mapa después de un breve retraso
        setTimeout(loadGoogleMaps, 500);
    });
</script>

<?php
// Incluir pie de página
include_once 'footer.php';
?>