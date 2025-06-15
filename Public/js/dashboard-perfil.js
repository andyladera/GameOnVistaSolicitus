document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard perfil JS cargado');
});

// Función para abrir modal de perfil
function abrirModalPerfil() {
    cargarDatosPerfil();
    document.getElementById('modalPerfil').style.display = 'flex';
}

// Función para abrir modal de deportes
function abrirModalDeportes() {
    cargarDeportes();
    document.getElementById('modalDeportes').style.display = 'flex';
}

// Función para cerrar modal
function cerrarModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Cerrar modal al hacer clic fuera
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.style.display = 'none';
    }
});

// Función para cargar datos del perfil
async function cargarDatosPerfil() {
    try {
        const response = await fetch('perfil_ajax.php?action=getPerfil');
        const data = await response.json();
        
        if (data.success && data.perfil) {
            const perfil = data.perfil;
            document.getElementById('nombre').value = perfil.nombre || '';
            document.getElementById('apellidos').value = perfil.apellidos || '';
            document.getElementById('email').value = perfil.email || '';
            document.getElementById('telefono').value = perfil.telefono || '';
            document.getElementById('fecha_nacimiento').value = perfil.fecha_nacimiento || '';
            document.getElementById('genero').value = perfil.genero || '';
        }
    } catch (error) {
        console.error('Error al cargar datos del perfil:', error);
        mostrarNotificacion('Error al cargar datos del perfil', 'error');
    }
}

// Función para cargar deportes
async function cargarDeportes() {
    try {
        const response = await fetch('perfil_ajax.php?action=getDeportes');
        const data = await response.json();
        
        if (data.success) {
            mostrarListaDeportes(data.deportes, data.deportesUsuario);
        }
    } catch (error) {
        console.error('Error al cargar deportes:', error);
        mostrarNotificacion('Error al cargar deportes', 'error');
    }
}

// Función para mostrar la lista de deportes
function mostrarListaDeportes(deportes, deportesUsuario) {
    const container = document.getElementById('listaDeportes');
    let html = '<div class="deportes-grid">';
    
    deportes.forEach(deporte => {
        const yaAgregado = deportesUsuario.includes(deporte.id);
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
                    <button class="btn-deporte ${yaAgregado ? 'btn-eliminar' : 'btn-agregar'}" 
                            onclick="${yaAgregado ? 'eliminarDeporteModal' : 'agregarDeporteModal'}(${deporte.id})"
                            ${yaAgregado ? 'disabled' : ''}>
                        <i class="fas fa-${yaAgregado ? 'check' : 'plus'}"></i>
                        ${yaAgregado ? 'Agregado' : 'Agregar'}
                    </button>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

// Función para obtener icono según el deporte
function obtenerIconoDeporte(nombreDeporte) {
    const iconos = {
        'futbol': 'futbol',
        'basketball': 'basketball-ball',
        'basquet': 'basketball-ball',
        'tenis': 'table-tennis',
        'voley': 'volleyball-ball',
        'natacion': 'swimmer',
        'running': 'running',
        'ciclismo': 'biking',
        'default': 'running'
    };
    
    const nombre = nombreDeporte.toLowerCase();
    return iconos[nombre] || iconos['default'];
}

// Función para agregar deporte desde modal
async function agregarDeporteModal(deporteId) {
    try {
        const formData = new FormData();
        formData.append('action', 'agregarDeporte');
        formData.append('deporte_id', deporteId);
        
        const response = await fetch('perfil_ajax.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarNotificacion(data.message, 'success');
            // Actualizar la tarjeta del deporte en el modal
            const deporteItem = document.querySelector(`[data-deporte-id="${deporteId}"]`);
            if (deporteItem) {
                deporteItem.classList.add('agregado');
                const btn = deporteItem.querySelector('.btn-deporte');
                btn.className = 'btn-deporte btn-eliminar';
                btn.innerHTML = '<i class="fas fa-check"></i> Agregado';
                btn.disabled = true;
                btn.onclick = null;
            }
            // Actualizar la lista en el dashboard
            setTimeout(() => {
                actualizarDeportesDashboard();
            }, 500);
        } else {
            mostrarNotificacion(data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('Error de conexión', 'error');
    }
}

// Función para eliminar deporte desde modal
async function eliminarDeporteModal(deporteId) {
    if (!confirm('¿Estás seguro de que quieres eliminar este deporte?')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'eliminarDeporte');
        formData.append('deporte_id', deporteId);
        
        const response = await fetch('perfil_ajax.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarNotificacion(data.message, 'success');
            // Actualizar la tarjeta del deporte en el modal
            const deporteItem = document.querySelector(`[data-deporte-id="${deporteId}"]`);
            if (deporteItem) {
                deporteItem.classList.remove('agregado');
                const btn = deporteItem.querySelector('.btn-deporte');
                btn.className = 'btn-deporte btn-agregar';
                btn.innerHTML = '<i class="fas fa-plus"></i> Agregar';
                btn.disabled = false;
                btn.onclick = () => agregarDeporteModal(deporteId);
            }
            // Actualizar la lista en el dashboard
            setTimeout(() => {
                actualizarDeportesDashboard();
            }, 500);
        } else {
            mostrarNotificacion(data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('Error de conexión', 'error');
    }
}

// Función para eliminar deporte directamente desde el dashboard
async function eliminarDeporte(deporteId) {
    if (!confirm('¿Estás seguro de que quieres eliminar este deporte?')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'eliminarDeporte');
        formData.append('deporte_id', deporteId);
        
        const response = await fetch('perfil_ajax.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarNotificacion(data.message, 'success');
            actualizarDeportesDashboard();
        } else {
            mostrarNotificacion(data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('Error de conexión', 'error');
    }
}

// Función para actualizar deportes en el dashboard
async function actualizarDeportesDashboard() {
    try {
        const response = await fetch('perfil_ajax.php?action=getDeportes');
        const data = await response.json();
        
        if (data.success) {
            const deportesContainer = document.getElementById('deportesFavoritos');
            const deportes = data.deportes.filter(d => data.deportesUsuario.includes(d.id));
            
            if (deportes.length > 0) {
                let html = '';
                deportes.forEach(deporte => {
                    html += `
                        <span class="sport-tag" data-deporte-id="${deporte.id}">
                            ${deporte.nombre.charAt(0).toUpperCase() + deporte.nombre.slice(1)}
                            <i class="fas fa-times ms-1" onclick="eliminarDeporte(${deporte.id})" style="cursor: pointer;" title="Eliminar deporte"></i>
                        </span>
                    `;
                });
                deportesContainer.innerHTML = html;
            } else {
                deportesContainer.innerHTML = '<p class="text-muted">No tienes deportes agregados</p>';
            }
        }
    } catch (error) {
        console.error('Error al actualizar deportes:', error);
    }
}

// Función para mostrar notificaciones
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
        `;
        document.body.appendChild(container);
    }
    
    const notificacion = document.createElement('div');
    notificacion.className = `notificacion-toast ${tipo}`;
    notificacion.style.cssText = `
        background: ${tipo === 'success' ? 'linear-gradient(135deg, #28a745, #20c997)' : 'linear-gradient(135deg, #dc3545, #e74c3c)'};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 10px;
        box-shadow: 0 5px 15px ${tipo === 'success' ? 'rgba(40, 167, 69, 0.3)' : 'rgba(220, 53, 69, 0.3)'};
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideInRight 0.3s ease;
        min-width: 280px;
    `;
    notificacion.innerHTML = `
        <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${mensaje}</span>
    `;
    
    container.appendChild(notificacion);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        notificacion.remove();
    }, 3000);
}

// Event listener para el formulario de perfil
document.addEventListener('DOMContentLoaded', function() {
    const formPerfil = document.getElementById('formPerfil');
    if (formPerfil) {
        formPerfil.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'actualizarPerfil');
            
            try {
                const response = await fetch('perfil_ajax.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    mostrarNotificacion(data.message, 'success');
                    cerrarModal('modalPerfil');
                    // Actualizar la información en el dashboard
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    mostrarNotificacion(data.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarNotificacion('Error de conexión', 'error');
            }
        });
    }
});