<?php
session_start();

// Verificar si el usuario está autenticado como institución deportiva
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'instalacion') {
    header("Location: ../Auth/login.php");
    exit();
}

// Usar tu controlador existente
require_once '../../Controllers/InsDeporController.php';

$insDeporController = new InsDeporController();

// Obtener datos usando tu controlador existente
$todasLasInstalaciones = $insDeporController->getAllInstalaciones();

// Filtrar las instalaciones de esta institución específica
$misInstalaciones = $todasLasInstalaciones; // Por ahora todas

// Datos simulados para las métricas
$datosInstitucion = [
    'nombre' => $_SESSION['username'],
    'calificacion_promedio' => 4.5,
    'total_instalaciones' => count($misInstalaciones)
];

$reservasHoy = []; // Por ahora vacío
$estadisticasMes = [
    'ingresos' => 15500.00,
    'reservas_completadas' => 45,
    'nuevos_clientes' => 12
];

// Incluir cabecera
include_once 'header.php';
?>

<div class="dashboard-container-inst">
    <!-- Panel de Bienvenida -->
    <div class="welcome-banner-inst">
        <div class="welcome-content-inst">
            <h1>¡Bienvenido <?= $_SESSION['username'] ?>!</h1>
            <p>Administra tus instalaciones deportivas de manera eficiente y maximiza tu potencial.</p>
        </div>
        <div class="welcome-actions-inst">
            <button class="btn-primary-inst btn-nueva-instalacion">
                <i class="fas fa-plus"></i> Nueva Instalación
            </button>
            <button class="btn-secondary-inst btn-ver-promociones">
                <i class="fas fa-bullhorn"></i> Crear Promoción
            </button>
        </div>
    </div>

    <!-- Métricas Principales -->
    <div class="metrics-row-inst">
        <div class="metric-card-inst">
            <div class="metric-icon-inst bg-blue">
                <i class="fas fa-building"></i>
            </div>
            <div class="metric-content-inst">
                <h3><?= count($misInstalaciones) ?></h3>
                <p>Instalaciones Registradas</p>
                <span class="metric-change-inst positive">Sistema funcionando</span>
            </div>
        </div>

        <div class="metric-card-inst">
            <div class="metric-icon-inst bg-green">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="metric-content-inst">
                <h3><?= count($reservasHoy) ?></h3>
                <p>Reservas Hoy</p>
                <span class="metric-change-inst neutral">Sin conexión aún</span>
            </div>
        </div>

        <div class="metric-card-inst">
            <div class="metric-icon-inst bg-orange">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="metric-content-inst">
                <h3>S/. <?= number_format($estadisticasMes['ingresos'], 2) ?></h3>
                <p>Ingresos Proyectados</p>
                <span class="metric-change-inst positive">Datos simulados</span>
            </div>
        </div>

        <div class="metric-card-inst">
            <div class="metric-icon-inst bg-purple">
                <i class="fas fa-star"></i>
            </div>
            <div class="metric-content-inst">
                <h3><?= number_format($datosInstitucion['calificacion_promedio'], 1) ?></h3>
                <p>Calificación Promedio</p>
                <span class="metric-change-inst neutral">4.5/5.0 estrellas</span>
            </div>
        </div>
    </div>

    <!-- Contenido Principal -->
    <div class="dashboard-main-content-inst">
        <!-- Panel Izquierdo -->
        <div class="main-panel-inst">
            <!-- Mis Instalaciones -->
            <div class="content-card-inst">
                <div class="card-header-inst">
                    <h2><i class="fas fa-building"></i> Instalaciones del Sistema</h2>
                    <button class="btn-outline-inst">Ver Todas</button>
                </div>
                <div class="instalaciones-grid-inst">
                    <?php if (!empty($misInstalaciones)): ?>
                        <?php foreach (array_slice($misInstalaciones, 0, 3) as $instalacion): ?>
                        <div class="instalacion-card-inst">
                            <div class="instalacion-imagen-inst">
                                <img src="<?= $instalacion['imagen'] ?? '../../Resources/default_instalacion.jpg' ?>" alt="<?= htmlspecialchars($instalacion['nombre']) ?>">
                                <div class="instalacion-estado-inst activa">Activa</div>
                            </div>
                            <div class="instalacion-info-inst">
                                <h4><?= htmlspecialchars($instalacion['nombre']) ?></h4>
                                <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($instalacion['direccion']) ?></p>
                                <p><i class="fas fa-running"></i> Múltiples deportes</p>
                                <div class="instalacion-stats-inst">
                                    <span><i class="fas fa-calendar"></i> 0 reservas</span>
                                    <span><i class="fas fa-star"></i> 4.5</span>
                                </div>
                            </div>
                            <div class="instalacion-actions-inst">
                                <button class="btn-small-inst btn-edit">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                <button class="btn-small-inst btn-schedule">
                                    <i class="fas fa-clock"></i> Horarios
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state-inst">
                            <i class="fas fa-building"></i>
                            <h3>¡Sistema de instalaciones deportivas!</h3>
                            <p>Aquí se mostrarán las instalaciones cuando estén conectadas al sistema de reservas.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mensaje informativo -->
            <div class="content-card-inst">
                <div class="card-header-inst">
                    <h2><i class="fas fa-info-circle"></i> Información del Sistema</h2>
                </div>
                <div class="info-content">
                    <div class="alert alert-info">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <h4>¡Dashboard de Instalaciones Deportivas Funcionando!</h4>
                            <p>Has accedido correctamente al panel de gestión para instituciones deportivas privadas.</p>
                            <ul style="margin-top: 10px; padding-left: 20px;">
                                <li>✅ Autenticación funcionando</li>
                                <li>✅ Interfaz específica cargada</li>
                                <li>✅ Controlador InsDepor conectado</li>
                                <li>🔄 Próximo: Sistema de reservas</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Derecho - Sidebar -->
        <div class="sidebar-panel-inst">
            <!-- Usuario actual -->
            <div class="sidebar-card-inst">
                <h3><i class="fas fa-user"></i> Sesión Actual</h3>
                <div class="user-session-info">
                    <p><strong>Usuario:</strong> <?= $_SESSION['username'] ?></p>
                    <p><strong>Tipo:</strong> Instalación Deportiva</p>
                    <p><strong>ID:</strong> <?= $_SESSION['user_id'] ?></p>
                    <p><strong>Estado:</strong> <span style="color: green;">Conectado</span></p>
                </div>
            </div>

            <!-- Acciones disponibles -->
            <div class="sidebar-card-inst">
                <h3><i class="fas fa-cogs"></i> Funcionalidades</h3>
                <div class="features-list">
                    <div class="feature-item">
                        <i class="fas fa-building"></i>
                        <span>Gestión de instalaciones</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-calendar"></i>
                        <span>Control de horarios</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-users"></i>
                        <span>Gestión de reservas</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-trophy"></i>
                        <span>Organización de torneos</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../../Public/js/dashboard_instituciones.js"></script>

<?php include_once 'footer.php'; ?>