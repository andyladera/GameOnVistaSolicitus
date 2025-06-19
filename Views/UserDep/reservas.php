<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'deportista') {
    header("Location: ../Auth/login.php");
    exit();
}

include_once 'header.php';
?>

<link rel="stylesheet" href="../../Public/css/styles_reservas.css">

<div class="container mt-4">
    <h2 class="mb-4">
        <i class="fas fa-calendar-alt"></i> MIS RESERVAS Y TORNEOS
    </h2>
    
    <!-- CALENDARIO PRINCIPAL -->
    <div class="calendario-container">
        <div class="calendario-header">
            <h3 style="color: #ffffff; margin: 0;">
                <i class="fas fa-calendar"></i> Calendario de Actividades
            </h3>
            <div class="calendario-nav">
                <button class="btn-nav-mes" id="btnMesAnterior">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="mes-actual" id="mesActual">
                    <!-- Se llena dinámicamente -->
                </div>
                <button class="btn-nav-mes" id="btnMesSiguiente">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
        
        <!-- GRID DEL CALENDARIO -->
        <div class="calendario-grid" id="calendarioGrid">
            <!-- Cabeceras de días -->
            <div class="dia-cabecera">DOM</div>
            <div class="dia-cabecera">LUN</div>
            <div class="dia-cabecera">MAR</div>
            <div class="dia-cabecera">MIÉ</div>
            <div class="dia-cabecera">JUE</div>
            <div class="dia-cabecera">VIE</div>
            <div class="dia-cabecera">SÁB</div>
            
            <!-- Las celdas de días se generan dinámicamente -->
        </div>
        
        <!-- LEYENDA - ACTUALIZAR (quitar horarios disponibles) -->
        <div class="calendario-leyenda">
            <div class="leyenda-item">
                <div class="leyenda-color leyenda-reserva"></div>
                <span>Mis Reservas</span>
            </div>
            <div class="leyenda-item">
                <div class="leyenda-color leyenda-torneo"></div>
                <span>Torneos de mis Equipos</span>
            </div>
        </div>
    </div>

    <div class="dashboard-row">
        <!-- ZONA IZQUIERDA - PRÓXIMAS ACTIVIDADES -->
        <div class="dashboard-card">
            <h3 style="color: #ffffff; margin-bottom: 20px;">
                <i class="fas fa-clock"></i> Próximas Actividades
            </h3>
            
            <div class="reservas-sidebar">
                <div class="sidebar-section">
                    <h5><i class="fas fa-calendar-check"></i> Mis Reservas</h5>
                    <div id="proximasReservas">
                        <div class="loading-calendario">
                            <div class="spinner-calendario"></div>
                        </div>
                    </div>
                </div>
                
                <div class="sidebar-section">
                    <h5><i class="fas fa-trophy"></i> Torneos de mis Equipos</h5>
                    <div id="proximosTorneos">
                        <div class="loading-calendario">
                            <div class="spinner-calendario"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ZONA DERECHA - ACCIONES RÁPIDAS -->
        <div class="dashboard-card">
            <h3 style="color: #ffffff; margin-bottom: 20px;">
                <i class="fas fa-plus-circle"></i> Acciones Rápidas
            </h3>
            
            <div class="filtros-reservas">
                <h4><i class="fas fa-filter"></i> Nueva Reserva</h4>
                
                <div class="form-group">
                    <label for="fechaReserva">Fecha de Reserva:</label>
                    <input type="date" class="form-control" id="fechaReserva" 
                           min="<?php echo date('Y-m-d'); ?>" 
                           max="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                </div>
                
                <div class="form-group">
                    <label for="deporteReserva">Deporte:</label>
                    <select class="form-control" id="deporteReserva">
                        <option value="">Seleccionar deporte</option>
                        <option value="1">Fútbol</option>
                        <option value="2">Voley</option>
                        <option value="3">Básquet</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="equipoReserva">Equipo (opcional):</label>
                    <select class="form-control" id="equipoReserva">
                        <option value="">Reserva individual</option>
                        <!-- Se llena dinámicamente con equipos del usuario -->
                    </select>
                </div>
                
                <button class="btn-reservar" id="btnBuscarHorarios">
                    <i class="fas fa-search"></i> Buscar Horarios Disponibles
                </button>
                
                <hr style="border-color: #444; margin: 20px 0;">
                
                <h4><i class="fas fa-map-marker-alt"></i> Explorar Instalaciones</h4>
                
                <button class="btn-ver-horarios" onclick="window.location.href='insdepor.php'">
                    <i class="fas fa-map"></i> Ver Mapa de Instalaciones
                </button>
                
                <button class="btn-ver-horarios" onclick="window.location.href='torneos.php'">
                    <i class="fas fa-trophy"></i> Ver Torneos Disponibles
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PARA VER DETALLES DEL DÍA -->
<div class="modal-dia" id="modalDia">
    <div class="modal-dia-contenido">
        <div class="modal-dia-header">
            <h3 class="modal-dia-titulo" id="modalDiaTitulo">
                <!-- Se llena dinámicamente -->
            </h3>
            <button class="btn-cerrar-modal" id="btnCerrarModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="modalDiaContenido">
            <!-- Se llena dinámicamente -->
        </div>
    </div>
</div>

<!-- Deshabilitar chat en página de reservas -->
<script>
window.chatDisabled = true;
</script>

<!-- Script específico para reservas -->
<script src="../../Public/js/reservas.js"></script>

<?php
include_once 'footer.php';
?>