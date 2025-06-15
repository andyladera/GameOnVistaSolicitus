<?php 
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'deportista') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
include_once 'header.php'; 
?> 
<link rel="stylesheet" href="../../Public/css/modales.css">

<div class="container mt-4">
    <!-- Panel de opciones de Chat --> 
    <div class="dashboard-wide-card"> 
        <h2>Opciones de Chat</h2>
        <div class="row">
            <div class="col-md-3">
                <button class="btn btn-primary w-100 mb-2" data-modal="modalCrearEquipo">
                    <i class="fas fa-plus"></i> Crear Equipo
                </button>
                <button class="btn btn-primary w-100 mb-2" data-modal="modalBuscarAmigos">
                    <i class="fas fa-user-plus"></i> Añadir Amigos
                </button>
                <button class="btn btn-primary w-100 mb-2" data-modal="modalSolicitudes">
                    <i class="fas fa-inbox"></i> Solicitudes
                    <span id="contadorSolicitudes" class="badge bg-danger ms-1" style="display: none;">0</span>
                </button>
            </div>
        </div>
    </div> 
    
    <div class="dashboard-row">
        <div class="dashboard-card"> 
            <h2>AMIGOS Y GRUPOS</h2>
            <div class="custom-tab-content">
                <!-- Tab Amigos -->
                <div class="custom-tab-pane active" id="tabAmigos">
                    <div id="listaAmigos">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tab Equipos -->
                <div class="custom-tab-pane active" id="tabEquipos">
                    <div id="listaEquipos">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
 
        <!-- Zona derecha: CHAT --> 
        <div class="dashboard-card"> 
            <h2>CHAT</h2>
            <div class="text-center text-muted">
                <i class="fas fa-comments fa-3x mb-3"></i>
                <p>Selecciona un amigo o equipo para iniciar una conversación</p>
                <small>Sistema de chat MongoDB implementado</small>
            </div>
        </div>
    </div> 
</div>

<!-- Modal Crear Equipo -->
<div class="custom-modal" id="modalCrearEquipo">
    <div class="custom-modal-content">
        <h3>Crear Nuevo Equipo</h3>
        <form id="formCrearEquipo">
            <div class="mb-3">
                <label>Nombre del equipo</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label>Deporte</label>
                <select name="deporte_id" class="form-control select-deportes" required>
                    <option value="">Seleccionar deporte</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Límite de miembros</label>
                <input type="number" name="limite_miembros" class="form-control" min="2" max="30" value="10">
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" name="privado" class="form-check-input" id="equipoPrivado">
                <label class="form-check-label" for="equipoPrivado">Equipo privado</label>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Crear Equipo</button>
                <button type="button" class="btn btn-secondary" data-close-modal="modalCrearEquipo">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Buscar Amigos -->
<div class="custom-modal" id="modalBuscarAmigos">
    <div class="custom-modal-content">
        <h3>Buscar Usuarios</h3>
        <form id="formBuscarAmigos">
            <div class="mb-3">
                <input type="text" id="busquedaAmigos" name="busqueda" class="form-control" placeholder="Buscar por nombre o username">
            </div>
            <div id="resultadosBusqueda"></div>
            <button type="button" class="btn btn-secondary" data-close-modal="modalBuscarAmigos">Cerrar</button>
        </form>
    </div>
</div>

<!-- Modal Solicitudes de Amistad -->
<div class="custom-modal" id="modalSolicitudes">
    <div class="custom-modal-content">
        <h3>Solicitudes de Amistad</h3>
        <div id="solicitudesPendientes">
            <div class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-secondary" data-close-modal="modalSolicitudes">Cerrar</button>
    </div>
</div>

<!-- Modal Miembros del Equipo -->  
<div class="custom-modal" id="modalMiembrosEquipo">
    <div class="custom-modal-content">
        <h3>Miembros del Equipo</h3>
        <div id="listaMiembrosEquipo"></div>
        <button type="button" class="btn btn-secondary" data-close-modal="modalMiembrosEquipo">Cerrar</button>
    </div>
</div>

<!-- HABILITAR chat específicamente para esta página -->
<script>
window.chatEnabled = true;
</script>

<!-- Scripts de Chat MongoDB -->
<script src="../../Public/js/chatmongo.js"></script>
<!-- Script de chat regular también -->
<script src="../../Public/js/chat.js"></script>
<div id="userDataContainer" data-user-id="<?php echo $_SESSION['user_id']; ?>" style="display: none;"></div>
<?php 
include_once 'footer.php'; 
?>