// ✅ VERIFICACIÓN DE CARGA Y DEBUG
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Dashboard perfil JS cargado correctamente');
    
    // ✅ VERIFICAR QUE LOS BOTONES EXISTAN
    setTimeout(() => {
        const btnEditarPerfil = document.querySelector('button[onclick="abrirModalPerfil()"]');
        const btnAgregarDeportes = document.querySelector('button[onclick="abrirModalDeportes()"]');
        
        console.log('🔍 Verificando botones:');
        console.log('- Botón Editar Perfil:', btnEditarPerfil ? '✅ Encontrado' : '❌ No encontrado');
        console.log('- Botón Agregar Deportes:', btnAgregarDeportes ? '✅ Encontrado' : '❌ No encontrado');
        
        // ✅ VERIFICAR MODALES
        const modalPerfil = document.getElementById('modalPerfil');
        const modalDeportes = document.getElementById('modalDeportes');
        
        console.log('- Modal Perfil:', modalPerfil ? '✅ Encontrado' : '❌ No encontrado');
        console.log('- Modal Deportes:', modalDeportes ? '✅ Encontrado' : '❌ No encontrado');
        
        // ✅ VERIFICAR CSS
        const cssModales = document.querySelector('link[href*="dashboard_modales.css"]');
        console.log('- CSS Modales:', cssModales ? '✅ Cargado' : '❌ No cargado');
        
        // ✅ AGREGAR EVENT LISTENERS COMO BACKUP
        if (btnEditarPerfil && !btnEditarPerfil.onclick) {
            console.log('🔧 Agregando event listener de backup para Editar Perfil');
            btnEditarPerfil.addEventListener('click', abrirModalPerfil);
        }
        
        if (btnAgregarDeportes && !btnAgregarDeportes.onclick) {
            console.log('🔧 Agregando event listener de backup para Agregar Deportes');
            btnAgregarDeportes.addEventListener('click', abrirModalDeportes);
        }
        
    }, 1000);
    
    // Agregar animaciones CSS necesarias
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .modal-dashboard {
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .modal-dashboard.show {
            opacity: 1;
            visibility: visible;
        }
        
        .modal-container-dashboard {
            transform: scale(0.8);
            transition: transform 0.3s ease;
        }
        
        .modal-dashboard.show .modal-container-dashboard {
            transform: scale(1);
        }
    `;
    document.head.appendChild(style);
});

// ✅ Variables globales
let deportesDisponibles = [];
let deportesUsuario = [];

// ✅ FUNCIÓN RENOMBRADA: Abrir modal de perfil (evita conflicto con horarios_modal.js)
function abrirModalPerfil() {
    console.log('🔧 Intentando abrir modal de perfil...');
    cargarDatosPerfilActual();
    mostrarModalDashboard('modalPerfil');
}

// ✅ FUNCIÓN RENOMBRADA: Abrir modal de deportes (evita conflicto con horarios_modal.js)
function abrirModalDeportes() {
    console.log('🔧 Intentando abrir modal de deportes...');
    cargarDeportesDisponibles();
    mostrarModalDashboard('modalDeportes');
}

// ✅ FUNCIÓN RENOMBRADA: Mostrar modal con animación (nombre único para evitar conflictos)
function mostrarModalDashboard(modalId) {
    console.log('🎭 Mostrando modal:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        // Forzar reflow para que la animación funcione
        modal.offsetHeight;
        modal.classList.add('show');
        
        // Agregar event listener para cerrar con Escape
        document.addEventListener('keydown', cerrarConEscapeDashboard);
        
        console.log('✅ Modal mostrado:', modalId);
    } else {
        console.error('❌ Modal no encontrado:', modalId);
    }
}

// ✅ FUNCIÓN RENOMBRADA: Cerrar modal con animación (nombre único)
function cerrarModalDashboard(modalId) {
    console.log('🔒 Cerrando modal:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        
        // Esperar a que termine la animación antes de ocultar
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
        
        // Remover event listener de Escape
        document.removeEventListener('keydown', cerrarConEscapeDashboard);
        
        console.log('✅ Modal cerrado:', modalId);
    }
}

// ✅ FUNCIÓN RENOMBRADA: Cerrar modal con tecla Escape (nombre único)
function cerrarConEscapeDashboard(event) {
    if (event.key === 'Escape') {
        // Encontrar el modal abierto y cerrarlo
        const modalAbierto = document.querySelector('.modal-dashboard.show');
        if (modalAbierto) {
            const modalId = modalAbierto.getAttribute('id');
            cerrarModalDashboard(modalId);
        }
    }
}

// ✅ FUNCIÓN GLOBAL PARA CERRAR DESDE HTML (mantiene compatibilidad)
function cerrarModal(modalId) {
    cerrarModalDashboard(modalId);
}

// ✅ Cerrar modal al hacer clic fuera (mejorado)
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-backdrop')) {
        const modal = e.target.closest('.modal-dashboard');
        if (modal) {
            const modalId = modal.getAttribute('id');
            cerrarModalDashboard(modalId);
        }
    }
});

// ✅ FUNCIÓN ACTUALIZADA: Cargar datos del perfil actual
async function cargarDatosPerfilActual() {
    try {
        mostrarNotificacion('Cargando datos del perfil...', 'info');
        
        const response = await fetch('../../Controllers/PerfilController.php?action=obtener_perfil');
        const result = await response.json();
        
        if (result.success && result.data && result.data.perfil) {
            const perfil = result.data.perfil;
            
            // Llenar el formulario
            document.getElementById('nombre').value = perfil.nombre || '';
            document.getElementById('apellidos').value = perfil.apellidos || '';
            document.getElementById('email').value = perfil.email || '';
            document.getElementById('telefono').value = perfil.telefono || '';
            document.getElementById('fecha_nacimiento').value = perfil.fecha_nacimiento || '';
            document.getElementById('genero').value = perfil.genero || 'Masculino';
            
            mostrarNotificacion('Datos cargados correctamente', 'success');
        } else {
            throw new Error(result.message || 'Error al cargar perfil');
        }
    } catch (error) {
        console.error('Error cargando perfil:', error);
        mostrarNotificacion('Error al cargar datos del perfil: ' + error.message, 'error');
    }
}

// ✅ FUNCIÓN ACTUALIZADA: Cargar deportes disponibles
async function cargarDeportesDisponibles() {
    try {
        mostrarNotificacion('Cargando deportes disponibles...', 'info');
        
        const response = await fetch('../../Controllers/PerfilController.php?action=obtener_deportes');
        const result = await response.json();
        
        if (result.success && result.data) {
            deportesDisponibles = result.data.deportes || [];
            deportesUsuario = result.data.deportes_usuario || [];
            mostrarListaDeportes();
            mostrarNotificacion('Deportes cargados correctamente', 'success');
        } else {
            throw new Error(result.message || 'Error al cargar deportes');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('Error al cargar deportes: ' + error.message, 'error');
        document.getElementById('listaDeportes').innerHTML = `
            <div class="loading" style="text-align: center; padding: 40px;">
                <i class="fas fa-exclamation-triangle" style="font-size: 2em; color: #dc3545; margin-bottom: 10px;"></i>
                <p>Error al cargar deportes. Intenta de nuevo.</p>
                <button class="btn-primary" onclick="cargarDeportesDisponibles()" style="margin-top: 10px;">
                    <i class="fas fa-redo"></i> Reintentar
                </button>
            </div>
        `;
    }
}

// ✅ FUNCIÓN ACTUALIZADA: Mostrar lista de deportes
function mostrarListaDeportes() {
    const container = document.getElementById('listaDeportes');
    
    if (!deportesDisponibles || deportesDisponibles.length === 0) {
        container.innerHTML = `
            <div class="loading" style="text-align: center; padding: 40px;">
                <i class="fas fa-info-circle" style="font-size: 2em; color: #17a2b8; margin-bottom: 10px;"></i>
                <p>No hay deportes disponibles</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="deportes-grid">';
    deportesDisponibles.forEach(deporte => {
        const yaAgregado = deportesUsuario.some(d => d.id == deporte.id);
        const iconoDeporte = obtenerIconoDeporte(deporte.nombre);
        
        html += `
            <div class="deporte-item ${yaAgregado ? 'agregado' : ''}" data-deporte-id="${deporte.id}">
                <div class="deporte-icono">
                    <i class="fas fa-${iconoDeporte}"></i>
                </div>
                <div class="deporte-info">
                    <h4>${deporte.nombre.charAt(0).toUpperCase() + deporte.nombre.slice(1)}</h4>
                </div>
                <div class="deporte-accion">
                    ${yaAgregado ? 
                        `<button class="btn-deporte btn-eliminar" disabled>
                            <i class="fas fa-check"></i> Agregado
                        </button>` :
                        `<button class="btn-deporte btn-agregar" onclick="agregarDeporte(${deporte.id}, '${deporte.nombre}')">
                            <i class="fas fa-plus"></i> Agregar
                        </button>`
                    }
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    container.innerHTML = html;
}

// ✅ FUNCIÓN ACTUALIZADA: Obtener icono según el deporte
function obtenerIconoDeporte(nombreDeporte) {
    const iconos = {
        'futbol': 'futbol',
        'fútbol': 'futbol',
        'basketball': 'basketball-ball',
        'basquet': 'basketball-ball',
        'básquet': 'basketball-ball',
        'tenis': 'table-tennis',
        'voley': 'volleyball-ball',
        'vóley': 'volleyball-ball',
        'volleyball': 'volleyball-ball',
        'natacion': 'swimmer',
        'natación': 'swimmer',
        'running': 'running',
        'atletismo': 'running',
        'ciclismo': 'biking',
        'boxeo': 'fist-raised',
        'gimnasia': 'dumbbell',
        'default': 'running'
    };
    
    const nombre = nombreDeporte.toLowerCase();
    return iconos[nombre] || iconos['default'];
}

// ✅ FUNCIÓN ACTUALIZADA: Agregar deporte
async function agregarDeporte(deporteId, nombreDeporte) {
    try {
        mostrarNotificacion(`Agregando ${nombreDeporte}...`, 'info');
        
        const response = await fetch('../../Controllers/PerfilController.php?action=agregar_deporte', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ deporte_id: deporteId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Actualizar la lista local
            deportesUsuario.push({ id: deporteId, nombre: nombreDeporte });
            
            // Actualizar la vista del modal
            mostrarListaDeportes();
            
            // Actualizar la vista del dashboard
            actualizarDeportesFavoritosEnDashboard();
            
            mostrarNotificacion(`✅ ${nombreDeporte} agregado correctamente`, 'success');
        } else {
            throw new Error(result.message || 'Error al agregar deporte');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('❌ Error al agregar deporte: ' + error.message, 'error');
    }
}

// ✅ FUNCIÓN ACTUALIZADA: Eliminar deporte directamente desde el dashboard
async function eliminarDeporte(deporteId) {
    try {
        const deporte = deportesUsuario.find(d => d.id == deporteId);
        const nombreDeporte = deporte ? deporte.nombre : 'deporte';
        
        if (!confirm(`¿Estás seguro de que quieres eliminar ${nombreDeporte} de tus deportes favoritos?`)) {
            return;
        }
        
        mostrarNotificacion(`Eliminando ${nombreDeporte}...`, 'info');
        
        const response = await fetch('../../Controllers/PerfilController.php?action=eliminar_deporte', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ deporte_id: deporteId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Actualizar la lista local
            deportesUsuario = deportesUsuario.filter(d => d.id != deporteId);
            
            // Actualizar la vista del dashboard
            actualizarDeportesFavoritosEnDashboard();
            
            // Si hay modal abierto, actualizarlo también
            const modalDeportes = document.getElementById('modalDeportes');
            if (modalDeportes && modalDeportes.classList.contains('show')) {
                mostrarListaDeportes();
            }
            
            mostrarNotificacion(`✅ ${nombreDeporte} eliminado correctamente`, 'success');
        } else {
            throw new Error(result.message || 'Error al eliminar deporte');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('❌ Error al eliminar deporte: ' + error.message, 'error');
    }
}

// ✅ FUNCIÓN ACTUALIZADA: Actualizar deportes en el dashboard
function actualizarDeportesFavoritosEnDashboard() {
    const container = document.getElementById('deportesFavoritos');
    
    if (!container) {
        console.warn('Contenedor deportesFavoritos no encontrado');
        return;
    }
    
    if (deportesUsuario.length === 0) {
        container.innerHTML = '<p class="text-muted">No tienes deportes agregados</p>';
        return;
    }
    
    let html = '';
    deportesUsuario.forEach(deporte => {
        const icono = obtenerIconoDeporte(deporte.nombre);
        html += `
            <span class="sport-tag" data-deporte-id="${deporte.id}">
                <i class="fas fa-${icono}"></i>
                ${deporte.nombre.charAt(0).toUpperCase() + deporte.nombre.slice(1)}
                <i class="fas fa-times" onclick="eliminarDeporte(${deporte.id})" title="Eliminar deporte" style="cursor: pointer; margin-left: 8px;"></i>
            </span>
        `;
    });
    
    container.innerHTML = html;
}

// ✅ FUNCIÓN ACTUALIZADA: Mostrar notificaciones mejoradas
function mostrarNotificacion(mensaje, tipo = 'success') {
    // Crear contenedor de notificaciones si no existe
    let container = document.getElementById('notificaciones-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notificaciones-container';
        container.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1002;
            pointer-events: none;
        `;
        document.body.appendChild(container);
    }
    
    const colores = {
        'success': {
            bg: 'linear-gradient(135deg, #28a745, #20c997)',
            icon: 'check-circle'
        },
        'error': {
            bg: 'linear-gradient(135deg, #dc3545, #e74c3c)',
            icon: 'exclamation-circle'
        },
        'info': {
            bg: 'linear-gradient(135deg, #17a2b8, #00bcd4)',
            icon: 'info-circle'
        }
    };
    
    const config = colores[tipo] || colores['info'];
    
    const notificacion = document.createElement('div');
    notificacion.className = `notificacion-toast ${tipo}`;
    notificacion.style.cssText = `
        background: ${config.bg};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideInRight 0.3s ease;
        min-width: 280px;
        max-width: 400px;
        pointer-events: all;
        font-size: 14px;
        font-weight: 500;
    `;
    
    notificacion.innerHTML = `
        <i class="fas fa-${config.icon}" style="font-size: 16px;"></i>
        <span style="flex: 1;">${mensaje}</span>
        <i class="fas fa-times" onclick="this.parentElement.remove()" 
           style="cursor: pointer; opacity: 0.8; font-size: 12px;" 
           title="Cerrar"></i>
    `;
    
    container.appendChild(notificacion);
    
    // Remover automáticamente después de 4 segundos
    setTimeout(() => {
        if (notificacion.parentNode) {
            notificacion.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                if (notificacion.parentNode) {
                    notificacion.remove();
                }
            }, 300);
        }
    }, 4000);
}

// ✅ FUNCIÓN ACTUALIZADA: Event listener para el formulario de perfil
document.addEventListener('DOMContentLoaded', function() {
    const formPerfil = document.getElementById('formPerfil');
    if (formPerfil) {
        formPerfil.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                mostrarNotificacion('Guardando cambios en el perfil...', 'info');
                
                const formData = new FormData(this);
                const datos = Object.fromEntries(formData);
                
                const response = await fetch('../../Controllers/PerfilController.php?action=actualizar_perfil', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(datos)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    mostrarNotificacion('✅ Perfil actualizado correctamente', 'success');
                    cerrarModalDashboard('modalPerfil');
                    
                    // Recargar la página para mostrar los cambios
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    throw new Error(result.message || 'Error al actualizar perfil');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarNotificacion('❌ Error al actualizar perfil: ' + error.message, 'error');
            }
        });
    }
    
    // Cargar deportes del usuario al iniciar
    setTimeout(() => {
        cargarDeportesUsuarioInicial();
    }, 1000);
});

// ✅ FUNCIÓN ACTUALIZADA: Cargar deportes del usuario al inicializar
async function cargarDeportesUsuarioInicial() {
    try {
        const response = await fetch('../../Controllers/PerfilController.php?action=obtener_deportes');
        const result = await response.json();
        
        if (result.success && result.data) {
            deportesDisponibles = result.data.deportes || [];
            deportesUsuario = result.data.deportes_usuario || [];
            console.log('✅ Deportes del usuario cargados:', deportesUsuario.length);
        }
    } catch (error) {
        console.error('Error cargando deportes iniciales:', error);
    }
}

// ✅ Agregar animación slideOutRight
const additionalStyles = document.createElement('style');
additionalStyles.textContent = `
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(additionalStyles);

// ✅ HACER FUNCIONES GLOBALES (con nombres únicos para evitar conflictos)
window.abrirModalPerfil = abrirModalPerfil;
window.abrirModalDeportes = abrirModalDeportes;
window.cerrarModal = cerrarModal;
window.agregarDeporte = agregarDeporte;
window.eliminarDeporte = eliminarDeporte;

console.log('✅ Dashboard-perfil.js completamente actualizado y cargado');