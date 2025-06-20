<?php 
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'deportista') { 
    header("Location: ../Auth/login.php"); 
    exit(); 
}
include_once 'header.php';
?>

<link rel="stylesheet" href="../../Public/css/misequipos_dep.css">

<div class="container mt-4">
    <!-- Panel de opciones de Chat --> 
    <div class="dashboard-wide-card"> 
        <h2><i class="fas fa-cogs"></i> Opciones de Chat</h2>
        <div class="row">
            <div class="col-md-12"> <!-- ✅ CAMBIAR A col-md-12 para usar todo el ancho -->
                <div class="opciones-chat-container"> <!-- ✅ NUEVO CONTENEDOR -->
                    <button class="btn-opcion-chat btn-crear-equipo" data-modal="modalCrearEquipo">
                        <i class="fas fa-plus"></i> Crear Equipo
                    </button>
                    <button class="btn-opcion-chat btn-anadir-amigos" data-modal="modalBuscarAmigos">
                        <i class="fas fa-user-plus"></i> Añadir Amigos
                    </button>
                    <button class="btn-opcion-chat btn-solicitudes" data-modal="modalSolicitudes">
                        <i class="fas fa-inbox"></i> Solicitudes
                        <span id="contadorSolicitudes" class="badge bg-danger" style="display: none;">0</span>
                    </button>
                </div>
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
        <button class="custom-modal-close" data-close-modal="modalCrearEquipo">&times;</button>
        <h3><i class="fas fa-users"></i> Crear Nuevo Equipo</h3>
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
        <button class="custom-modal-close" data-close-modal="modalBuscarAmigos">&times;</button>
        <h3><i class="fas fa-search"></i> Buscar Usuarios</h3>
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
        <button class="custom-modal-close" data-close-modal="modalSolicitudes">&times;</button>
        <h3><i class="fas fa-envelope"></i> Solicitudes de Amistad</h3>
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

<!-- ANTES DEL userDataContainer, agregar verificación -->
<script>
// ✅ ASEGURAR QUE ESTA PÁGINA CARGUE CHAT
window.chatEnabled = true;
window.chatDisabled = false;

// ✅ VERIFICAR CARGA DESPUÉS DE UN MOMENTO
setTimeout(() => {
    console.log('🔍 Verificando sistemas de chat...');
    console.log('gameOnChatMongo:', window.gameOnChatMongo ? '✅ Disponible' : '❌ NO disponible');
    console.log('chatManager:', window.chatManager ? '✅ Disponible' : '❌ NO disponible');
    console.log('ChatManager (clase):', window.ChatManager ? '✅ Disponible' : '❌ NO disponible');
    console.log('iniciarChatMongoDB función:', window.iniciarChatMongoDB ? '✅ Disponible' : '❌ NO disponible');
    
    // ✅ VERIFICAR SI EXISTE LA CLASE ChatManager
    if (typeof ChatManager !== 'undefined' && !window.chatManager) {
        console.log('🔧 Creando instancia de ChatManager...');
        window.chatManager = new ChatManager();
        console.log('✅ ChatManager instanciado manualmente');
    }
    
    if (!window.gameOnChatMongo) {
        console.error('🚨 PROBLEMA: MongoDB Chat no se cargó correctamente');
        console.log('📝 Intentando cargar manualmente...');
        
        // ✅ CARGAR MANUALMENTE SI NO EXISTE
        const mongoScript = document.createElement('script');
        mongoScript.src = '../../Public/js/chatmongo.js';
        mongoScript.onload = function() {
            console.log('✅ MongoDB Chat cargado manualmente');
        };
        document.head.appendChild(mongoScript);
    }
}, 2000);
</script>
<div id="userDataContainer" data-user-id="<?php echo $_SESSION['user_id']; ?>" style="display: none;"></div>
<?php 
include_once 'footer.php'; 
?>