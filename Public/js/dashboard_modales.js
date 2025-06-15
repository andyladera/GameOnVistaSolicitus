document.addEventListener('DOMContentLoaded', function() {
    // Event listeners para abrir modales
    const btnEditarPerfil = document.querySelector('.btn-editar-perfil');
    const btnAgregarDeportes = document.querySelector('.btn-agregar-deportes');
    
    if (btnEditarPerfil) {
        btnEditarPerfil.addEventListener('click', function() {
            cargarDatosPerfil();
            mostrarModalPerfil();
        });
    }
    
    if (btnAgregarDeportes) {
        btnAgregarDeportes.addEventListener('click', function() {
            cargarDeportes();
            mostrarModalDeportes();
        });
    }
    
    // Event listeners para cerrar modales
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('modal-close-btn')) {
            cerrarModal(e.target.closest('.modal-dashboard'));
        }
        
        if (e.target && e.target.classList.contains('modal-backdrop')) {
            cerrarModal(e.target.closest('.modal-dashboard'));
        }
    });
    
    // Event listener para formulario de perfil
    const formPerfil = document.getElementById('formEditarPerfil');
    if (formPerfil) {
        formPerfil.addEventListener('submit', function(e) {
            e.preventDefault();
            actualizarPerfil();
        });
    }
    
    // Cerrar modal con tecla Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modalAbierto = document.querySelector('.modal-dashboard.show');
            if (modalAbierto) {
                cerrarModal(modalAbierto);
            }
        }
    });
});

// Función para mostrar modal de perfil
function mostrarModalPerfil() {
    const modal = document.getElementById('modalEditarPerfil');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

// Función para mostrar modal de deportes
function mostrarModalDeportes() {
    const modal = document.getElementById('modalAgregarDeportes');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

// Función para cerrar modal
function cerrarModal(modal) {
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// Función para cargar datos del perfil
async function cargarDatosPerfil() {
    try {
        const response = await fetch('perfil_ajax.php?action=getPerfil');
        const data = await response.json();
        
        if (data.success && data.perfil) {
            const perfil = data.perfil;
            document.getElementById('inputNombre').value = perfil.nombre || '';
            document.getElementById('inputApellidos').value = perfil.apellidos || '';
            document.getElementById('inputEmail').value = perfil.email || '';
            document.getElementById('inputTelefono').value = perfil.telefono || '';
            document.getElementById('inputFechaNacimiento').value = perfil.fecha_nacimiento || '';
            document.getElementById('selectGenero').value = perfil.genero || '';
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
    const container = document.getElementById('deportesContainer');
    let html = '';
    
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
                            onclick="${yaAgregado ? 'eliminarDeporte' : 'agregarDeporte'}(${deporte.id})"
                            ${yaAgregado ? 'disabled' : ''}>
                        <i class="fas fa-${yaAgregado ? 'check' : 'plus'}"></i>
                        ${yaAgregado ? 'Agregado' : 'Agregar'}
                    </button>
                </div>
            </div>
        `;
    });
    
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

// Función para agregar deporte
async function agregarDeporte(deporteId) {
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
            // Actualizar la tarjeta del deporte
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

// Función para eliminar deporte
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
            // Actualizar la tarjeta del deporte
            const deporteItem = document.querySelector(`[data-deporte-id="${deporteId}"]`);
            if (deporteItem) {
                deporteItem.classList.remove('agregado');
                const btn = deporteItem.querySelector('.btn-deporte');
                btn.className = 'btn-deporte btn-agregar';
                btn.innerHTML = '<i class="fas fa-plus"></i> Agregar';
                btn.disabled = false;
                btn.onclick = () => agregarDeporte(deporteId);
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

// Función para actualizar perfil
async function actualizarPerfil() {
    const formData = new FormData(document.getElementById('formEditarPerfil'));
    formData.append('action', 'actualizarPerfil');
    
    try {
        const response = await fetch('perfil_ajax.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarNotificacion(data.message, 'success');
            cerrarModal(document.getElementById('modalEditarPerfil'));
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
                            <i class="fas fa-times" onclick="eliminarDeporteDirecto(${deporte.id})" title="Eliminar deporte"></i>
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

// Función para eliminar deporte directamente desde el dashboard
async function eliminarDeporteDirecto(deporteId) {
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

// Función para mostrar notificaciones
function mostrarNotificacion(mensaje, tipo = 'success') {
    // Crear contenedor de notificaciones si no existe
    let container = document.getElementById('notificaciones-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notificaciones-container';
        document.body.appendChild(container);
    }
    
    const notificacion = document.createElement('div');
    notificacion.className = `notificacion-toast ${tipo}`;
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