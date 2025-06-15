class ChatManager {
    constructor() {
        this.baseUrl = '../../Controllers/ChatController.php';
        this.equiposUrl = '../../Controllers/EquiposController.php';
        this.init();
    }

    init() {
        this.cargarEventos();
        this.cargarDatosIniciales();
    }

    cargarEventos() {
        // Eventos para modales
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-modal]')) {
                this.abrirModal(e.target.dataset.modal);
            }
            if (e.target.matches('[data-close-modal]')) {
                this.cerrarModal(e.target.dataset.closeModal);
            }
        });

        // Eventos para formularios
        document.addEventListener('submit', (e) => {
            if (e.target.matches('#formCrearEquipo')) {
                e.preventDefault();
                this.crearEquipo();
            }
            if (e.target.matches('#formBuscarAmigos')) {
                e.preventDefault();
                this.buscarUsuarios();
            }
        });

        // Eventos para botones de solicitudes
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-enviar-solicitud]')) {
                this.enviarSolicitudAmistad(e.target.dataset.enviarSolicitud);
            }
            if (e.target.matches('[data-aceptar-solicitud]')) {
                this.responderSolicitud(e.target.dataset.aceptarSolicitud, 'aceptada');
            }
            if (e.target.matches('[data-rechazar-solicitud]')) {
                this.responderSolicitud(e.target.dataset.rechazarSolicitud, 'rechazada');
            }
        });

        // Evento para filtrar equipos por deporte
        document.addEventListener('change', (e) => {
            if (e.target.matches('#filtroDeporte')) {
                this.filtrarEquiposPorDeporte(e.target.value);
            }
        });

        // Búsqueda en tiempo real
        document.addEventListener('input', (e) => {
            if (e.target.matches('#busquedaAmigos')) {
                this.buscarUsuariosEnTiempoReal(e.target.value);
            }
        });

        // Cerrar modal al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('custom-modal')) {
                e.target.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });
    }

    // ========== MÉTODOS PARA CARGAR DATOS ==========
    
    async cargarDatosIniciales() {
        try {
            await this.cargarDeportes();
            await this.cargarAmigos();
            await this.cargarEquipos();
            await this.cargarSolicitudesPendientes();
        } catch (error) {
            console.error('Error cargando datos iniciales:', error);
        }
    }

    async cargarDeportes() {
        try {
            const response = await fetch(`${this.baseUrl}?action=obtener_deportes`);
            const data = await response.json();
            
            if (data.success) {
                this.llenarSelectDeportes(data.deportes);
            } else {
                console.error('Error:', data.message);
            }
        } catch (error) {
            console.error('Error cargando deportes:', error);
        }
    }

    llenarSelectDeportes(deportes) {
        const selects = document.querySelectorAll('.select-deportes');
        selects.forEach(select => {
            select.innerHTML = '<option value="">Seleccionar deporte</option>';
            deportes.forEach(deporte => {
                select.innerHTML += `<option value="${deporte.id}">${deporte.nombre}</option>`;
            });
        });
    }

    async cargarAmigos() {
        try {
            const response = await fetch(`${this.baseUrl}?action=obtener_amigos`);
            const data = await response.json();
            
            if (data.success) {
                this.mostrarAmigos(data.amigos);
            } else {
                document.getElementById('listaAmigos').innerHTML = 
                    '<p class="text-center text-muted">Error cargando amigos</p>';
            }
        } catch (error) {
            console.error('Error cargando amigos:', error);
            document.getElementById('listaAmigos').innerHTML = 
                '<p class="text-center text-muted">Error cargando amigos</p>';
        }
    }

    mostrarAmigos(amigos) {
        const container = document.getElementById('listaAmigos');
        
        if (amigos.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted">
                    <i class="fas fa-user-friends fa-2x mb-3"></i>
                    <p>No tienes amigos agregados aún</p>
                    <button class="btn btn-primary" data-modal="modalBuscarAmigos">
                        Buscar Amigos
                    </button>
                </div>
            `;
            return;
        }

        let html = '';
        amigos.forEach(amigo => {
            html += `
                <div class="lista-item" onclick="chatManager.iniciarChatPrivado(${amigo.amigo_id}, '${amigo.nombre} ${amigo.apellidos}')">
                    <img src="../../Resources/default-avatar.png" alt="${amigo.nombre}">
                    <div class="lista-item-info">
                        <div class="lista-item-nombre">${amigo.nombre} ${amigo.apellidos}</div>
                        <div class="lista-item-detalle">@${amigo.username}</div>
                    </div>
                    <button class="lista-item-accion" onclick="event.stopPropagation(); chatManager.iniciarChatPrivado(${amigo.amigo_id}, '${amigo.nombre} ${amigo.apellidos}')">
                        Chat MongoDB
                    </button>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }

    async cargarEquipos() {
        try {
            const response = await fetch(`${this.equiposUrl}?action=obtener`);
            const data = await response.json();
            
            if (data.success) {
                this.mostrarEquipos(data.equipos);
            } else {
                document.getElementById('listaEquipos').innerHTML = 
                    '<p class="text-center text-muted">Error cargando equipos</p>';
            }
        } catch (error) {
            console.error('Error cargando equipos:', error);
            document.getElementById('listaEquipos').innerHTML = 
                '<p class="text-center text-muted">Error cargando equipos</p>';
        }
    }

    mostrarEquipos(equipos) {
        const container = document.getElementById('listaEquipos');
        
        if (equipos.length === 0) {
            container.innerHTML = `
                <div class="estado-vacio">
                    <i class="fas fa-users"></i>
                    <p>No tienes equipos aún</p>
                    <button class="btn btn-primary btn-sm" data-modal="modalCrearEquipo">
                        <i class="fas fa-plus"></i> Crear Equipo
                    </button>
                </div>
            `;
            return;
        }

        let html = '';
        equipos.forEach(equipo => {
            const rolBadge = equipo.rol === 'creador' ? '<span class="badge bg-warning ms-1">Líder</span>' : 
                        equipo.rol === 'administrador' ? '<span class="badge bg-info ms-1">Admin</span>' : '';
            
            // ⭐ BOTÓN ESPECIAL para creadores
            const botonesCreador = equipo.rol === 'creador' ? `
                <button class="btn btn-success btn-sm me-1" onclick="chatManager.mostrarAñadirAmigos(${equipo.id}, '${equipo.nombre.replace(/'/g, "\\'")}')">
                    <i class="fas fa-user-plus"></i> Añadir
                </button>
            ` : '';
            
            html += `
                <div class="lista-item">
                    <img src="../../Resources/team-default.png" alt="${equipo.nombre}" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiMwMDdmNTYiLz4KPHN2ZyB4PSIxMiIgeT0iMTIiIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgZmlsbD0id2hpdGUiPgo8cGF0aCBkPSJNOCAwdjhIOHY4aDh2LThIOHYtOEg4eiIvPgo8L3N2Zz4KPC9zdmc+'">
                    <div class="lista-item-info">
                        <div class="lista-item-nombre">
                            ${equipo.nombre} ${rolBadge}
                        </div>
                        <div class="lista-item-detalle">
                            ${equipo.deporte_nombre} • ${equipo.total_miembros}/${equipo.limite_miembros} miembros
                        </div>
                    </div>
                    <div style="display: flex; gap: 5px; align-items: center;">
                        ${botonesCreador}
                        <button class="lista-item-accion" onclick="chatManager.iniciarChatEquipo(${equipo.id}, '${equipo.nombre.replace(/'/g, "\\'")}')">
                            <i class="fas fa-comments"></i> Chat Grupo
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="chatManager.verMiembrosEquipo(${equipo.id}, '${equipo.nombre.replace(/'/g, "\\'")}')">
                            <i class="fas fa-users"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }

    // ========== GESTIÓN DE MODALES ==========
    
    abrirModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');  // ✅ USAR CLASE ACTIVE
            document.body.style.overflow = 'hidden';
        }
    }

    cerrarModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');  // ✅ USAR CLASE ACTIVE
            document.body.style.overflow = 'auto';
        }
    }

    // ========== GESTIÓN DE AMIGOS ==========
    
    async enviarSolicitudAmistad(receptorId) {
        try {
            const response = await fetch(`${this.baseUrl}?action=enviar_solicitud`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ receptor_id: receptorId })
            });

            const result = await response.json();
            
            if (result.success) {
                this.mostrarExito('Solicitud enviada exitosamente');
            } else {
                this.mostrarError(result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarError('Error al enviar solicitud');
        }
    }

    async cargarSolicitudesPendientes() {
        try {
            const response = await fetch(`${this.baseUrl}?action=solicitudes_pendientes`);
            const data = await response.json();
            
            if (data.success) {
                this.mostrarSolicitudesPendientes(data.solicitudes);
                this.actualizarContadorSolicitudes(data.solicitudes.length);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    mostrarSolicitudesPendientes(solicitudes) {
        const container = document.getElementById('solicitudesPendientes');
        
        if (solicitudes.length === 0) {
            container.innerHTML = '<p class="text-center text-muted">No tienes solicitudes pendientes</p>';
            return;
        }

        let html = '';
        solicitudes.forEach(solicitud => {
            html += `
                <div class="lista-item">
                    <img src="../../Resources/default-avatar.png" alt="${solicitud.nombre_solicitante}">
                    <div class="lista-item-info">
                        <div class="lista-item-nombre">${solicitud.nombre_solicitante} ${solicitud.apellidos_solicitante}</div>
                        <div class="lista-item-detalle">@${solicitud.username_solicitante}</div>
                    </div>
                    <div style="display: flex; gap: 5px;">
                        <button class="btn btn-success btn-sm" onclick="chatManager.responderSolicitud(${solicitud.id}, 'aceptada')">
                            ✓
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="chatManager.responderSolicitud(${solicitud.id}, 'rechazada')">
                            ✗
                        </button>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }

    async responderSolicitud(solicitudId, respuesta) {
        try {
            const response = await fetch(`${this.baseUrl}?action=responder_solicitud`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    solicitud_id: solicitudId, 
                    respuesta: respuesta 
                })
            });

            const result = await response.json();
            
            if (result.success) {
                this.mostrarExito(respuesta === 'aceptada' ? 'Solicitud aceptada' : 'Solicitud rechazada');
                await this.cargarSolicitudesPendientes();
                if (respuesta === 'aceptada') {
                    await this.cargarAmigos(); // Recargar amigos si aceptó
                }
            } else {
                this.mostrarError(result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarError('Error al responder solicitud');
        }
    }

    actualizarContadorSolicitudes(cantidad) {
        const contador = document.getElementById('contadorSolicitudes');
        if (contador) {
            if (cantidad > 0) {
                contador.textContent = cantidad;
                contador.style.display = 'inline-block';
            } else {
                contador.style.display = 'none';
            }
        }
    }

    // ========== MÉTODOS DE CHAT ==========
    
    iniciarChatPrivado(amigoId, nombreAmigo) {
        console.log('🚀 Redirigiendo a MongoDB:', amigoId, nombreAmigo);
        
        // Llamar a MongoDB en lugar de mostrar "próximamente"
        if (window.iniciarChatMongoDB) {
            window.iniciarChatMongoDB(amigoId, nombreAmigo);
        } else if (window.gameOnChatMongo) {
            window.gameOnChatMongo.startConversation(amigoId, nombreAmigo);
        } else {
            console.error('❌ MongoDB Chat no disponible');
            this.mostrarError('Sistema de chat no disponible. Recarga la página.');
        }
    }

    iniciarChatEquipo(equipoId, nombreEquipo) {
        console.log('🚀 Iniciando chat grupal MongoDB:', equipoId, nombreEquipo);
        
        if (window.gameOnChatMongo) {
            window.gameOnChatMongo.startTeamConversation(equipoId, nombreEquipo);  // ← Función específica
        } else {
            console.error('❌ MongoDB Chat no disponible');
            this.mostrarError('Sistema de chat grupal no disponible. Recarga la página.');
        }
    }

    verMiembrosEquipo(equipoId, nombreEquipo) {
        console.log('👥 Mostrando miembros del equipo:', equipoId, nombreEquipo);
        
        fetch(`../../Controllers/ChatController.php?action=obtener_miembros&equipo_id=${equipoId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.mostrarModalMiembros(nombreEquipo, data.miembros);
                } else {
                    this.mostrarError(data.message || 'Error al obtener miembros');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.mostrarError('Error de conexión');
            });
    }

    mostrarModalMiembros(nombreEquipo, miembros) {
        let html = `
            <div class="custom-modal active" id="modalMiembros">
                <div class="custom-modal-content">
                    <button class="custom-modal-close" onclick="chatManager.cerrarModal('modalMiembros')">&times;</button>
                    <h3><i class="fas fa-users"></i> Miembros de ${nombreEquipo}</h3>
                    <div style="max-height: 400px; overflow-y: auto;">
        `;
        
        miembros.forEach(miembro => {
            const rolBadge = miembro.rol === 'creador' ? '<span class="badge bg-warning ms-2">Líder</span>' : 
                            miembro.rol === 'administrador' ? '<span class="badge bg-info ms-2">Admin</span>' : 
                            '<span class="badge bg-secondary ms-2">Miembro</span>';
            
            html += `
                <div class="lista-item" style="margin-bottom: 10px;">
                    <img src="../../Resources/default-avatar.png" alt="${miembro.nombre}" style="width: 35px; height: 35px;">
                    <div class="lista-item-info">
                        <div class="lista-item-nombre">
                            ${miembro.nombre} ${miembro.apellidos} ${rolBadge}
                        </div>
                        <div class="lista-item-detalle">
                            @${miembro.username} • Desde ${new Date(miembro.fecha_union).toLocaleDateString()}
                        </div>
                    </div>
                    <button class="btn btn-outline-primary btn-sm" onclick="chatManager.iniciarChatPrivado(${miembro.id}, '${miembro.nombre} ${miembro.apellidos}')">
                        <i class="fas fa-comment"></i> Chat
                    </button>
                </div>
            `;
        });
        
        html += `
                    </div>
                    <div style="text-align: center; margin-top: 20px;">
                        <button class="btn btn-secondary" onclick="chatManager.cerrarModal('modalMiembros')">Cerrar</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', html);
    }

    cerrarModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.remove();
        }
    }

    // ========== UTILIDADES ==========
    
    mostrarError(mensaje) {
        this.mostrarNotificacion(mensaje, 'error');
    }

    mostrarExito(mensaje) {
        this.mostrarNotificacion(mensaje, 'success');
    }

    mostrarInfo(mensaje) {
        this.mostrarNotificacion(mensaje, 'info');
    }

    mostrarNotificacion(mensaje, tipo) {
        // Crear notificación toast
        const toast = document.createElement('div');
        toast.className = `alert alert-${tipo === 'success' ? 'success' : tipo === 'error' ? 'danger' : 'info'} position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            ${mensaje}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        `;
        
        document.body.appendChild(toast);
        
        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 5000);
    }

    async mostrarAñadirAmigos(equipoId, nombreEquipo) {
        try {
            const response = await fetch(`${this.equiposUrl}?action=obtener_amigos_para_equipo&equipo_id=${equipoId}`);
            const data = await response.json();
            
            if (data.success) {
                this.mostrarModalAñadirAmigos(equipoId, nombreEquipo, data.amigos);
            } else {
                this.mostrarError(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarError('Error obteniendo amigos disponibles');
        }
    }

    mostrarModalAñadirAmigos(equipoId, nombreEquipo, amigos) {
        // Verificar si ya hay un modal abierto
        const modalExistente = document.getElementById('modalAñadirAmigos');
        if (modalExistente) {
            modalExistente.remove();
        }
        
        let html = `
            <div class="custom-modal active" id="modalAñadirAmigos">
                <div class="custom-modal-content">
                    <button class="custom-modal-close" onclick="chatManager.cerrarModal('modalAñadirAmigos')">&times;</button>
                    <h3><i class="fas fa-user-plus"></i> Añadir Amigos a ${nombreEquipo}</h3>
        `;
        
        if (amigos.length === 0) {
            html += `
                <div class="text-center text-muted p-4">
                    <i class="fas fa-user-friends fa-3x mb-3"></i>
                    <p>No tienes amigos disponibles para añadir</p>
                    <small>Todos tus amigos ya están en el equipo o no tienes amigos agregados</small>
                </div>
            `;
        } else {
            html += `<div style="max-height: 400px; overflow-y: auto;">`;
            
            amigos.forEach(amigo => {
                html += `
                    <div class="lista-item" style="margin-bottom: 10px;">
                        <img src="../../Resources/default-avatar.png" alt="${amigo.nombre}" style="width: 35px; height: 35px;">
                        <div class="lista-item-info">
                            <div class="lista-item-nombre">
                                ${amigo.nombre} ${amigo.apellidos}
                            </div>
                            <div class="lista-item-detalle">
                                @${amigo.username}
                            </div>
                        </div>
                        <button class="btn btn-success btn-sm" onclick="chatManager.añadirAmigoAEquipo(${equipoId}, ${amigo.id}, '${amigo.nombre} ${amigo.apellidos}', '${nombreEquipo}')">
                            <i class="fas fa-plus"></i> Añadir
                        </button>
                    </div>
                `;
            });
            
            html += `</div>`;
        }
        
        html += `
                    <div style="text-align: center; margin-top: 20px;">
                        <button class="btn btn-secondary" onclick="chatManager.cerrarModal('modalAñadirAmigos')">Cerrar</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', html);
    }

    async añadirAmigoAEquipo(equipoId, amigoId, nombreAmigo, nombreEquipo) {
        try {
            const response = await fetch(`${this.equiposUrl}?action=agregar_amigo`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    equipo_id: equipoId,
                    amigo_id: amigoId
                })
            });

            const result = await response.json();
            
            if (result.success) {
                this.mostrarExito(result.message);
                // Cerrar modal y recargar datos
                this.cerrarModal('modalAñadirAmigos');
                await this.cargarEquipos(); // Recargar equipos para actualizar contador
            } else {
                this.mostrarError(result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarError('Error añadiendo amigo al equipo');
        }
    }

    async crearEquipo() {
        const form = document.getElementById('formCrearEquipo');
        const formData = new FormData(form);
        
        const datos = {
            nombre: formData.get('nombre'),
            descripcion: formData.get('descripcion'),
            deporte_id: parseInt(formData.get('deporte_id')),
            limite_miembros: parseInt(formData.get('limite_miembros')),
            privado: formData.get('privado') ? 1 : 0
        };
        
        try {
            const response = await fetch(`${this.equiposUrl}?action=crear`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(datos)
            });

            const result = await response.json();
            
            if (result.success) {
                this.mostrarExito('Equipo creado exitosamente');
                this.cerrarModal('modalCrearEquipo');
                form.reset();
                await this.cargarEquipos();
            } else {
                this.mostrarError(result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarError('Error al crear equipo');
        }
    }

    // ========== BÚSQUEDA DE USUARIOS POR ID ==========

    async buscarUsuarios() {
        const busqueda = document.getElementById('busquedaAmigos').value.trim();
        
        // ⭐ SOLO PERMITIR NÚMEROS (ID)
        if (!/^\d+$/.test(busqueda)) {
            document.getElementById('resultadosBusqueda').innerHTML = 
                '<p class="text-warning"><i class="fas fa-info-circle"></i> Solo se puede buscar por ID numérico</p>';
            return;
        }
        
        if (busqueda.length < 1) {
            document.getElementById('resultadosBusqueda').innerHTML = 
                '<p class="text-muted">Ingresa un ID de usuario</p>';
            return;
        }
        
        try {
            const response = await fetch(`${this.baseUrl}?action=buscar_por_id&id=${busqueda}`);
            const data = await response.json();
            
            if (data.success) {
                this.mostrarResultadosBusqueda([data.usuario]);
            } else {
                document.getElementById('resultadosBusqueda').innerHTML = 
                    `<p class="text-muted"><i class="fas fa-search"></i> ${data.message}</p>`;
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('resultadosBusqueda').innerHTML = 
                '<p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Error en la búsqueda</p>';
        }
    }

    mostrarResultadosBusqueda(usuarios) {
        const container = document.getElementById('resultadosBusqueda');
        
        if (usuarios.length === 0) {
            container.innerHTML = '<p class="text-muted">No se encontraron usuarios</p>';
            return;
        }
        
        let html = '';
        usuarios.forEach(usuario => {
            const estadoAmistad = usuario.estado_amistad || 'sin_relacion';
            let botonAmistad = '';
            
            switch (estadoAmistad) {
                case 'sin_relacion':
                    botonAmistad = `<button class="btn btn-primary btn-sm" onclick="chatManager.enviarSolicitudAmistad(${usuario.id})">
                        <i class="fas fa-user-plus"></i> Agregar
                    </button>`;
                    break;
                case 'pendiente_enviada':
                    botonAmistad = '<span class="badge bg-warning"><i class="fas fa-clock"></i> Solicitud enviada</span>';
                    break;
                case 'pendiente_recibida':
                    botonAmistad = `<div style="display: flex; gap: 5px;">
                        <button class="btn btn-success btn-sm" onclick="chatManager.responderSolicitud(${usuario.id}, 'aceptar')">
                            <i class="fas fa-check"></i> Aceptar
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="chatManager.responderSolicitud(${usuario.id}, 'rechazar')">
                            <i class="fas fa-times"></i> Rechazar
                        </button>
                    </div>`;
                    break;
                case 'aceptada':
                    botonAmistad = '<span class="badge bg-success"><i class="fas fa-check-circle"></i> Ya son amigos</span>';
                    break;
            }
            
            html += `
                <div class="lista-item" style="margin-bottom: 15px; padding: 12px; border: 1px solid #eee; border-radius: 8px;">
                    <img src="../../Resources/default-avatar.png" alt="${usuario.nombre}" style="width: 40px; height: 40px; border-radius: 50%;">
                    <div class="lista-item-info">
                        <div class="lista-item-nombre" style="font-weight: 600;">
                            ${usuario.nombre} ${usuario.apellidos}
                        </div>
                        <div class="lista-item-detalle" style="color: #666;">
                            @${usuario.username} • ID: ${usuario.id}
                        </div>
                    </div>
                    <div style="margin-left: auto;">
                        ${botonAmistad}
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }

    async buscarUsuariosEnTiempoReal(valor) {
        clearTimeout(this.busquedaTimeout);
        this.busquedaTimeout = setTimeout(() => {
            if (valor.length >= 1) {
                this.buscarUsuarios();
            } else {
                document.getElementById('resultadosBusqueda').innerHTML = '';
            }
        }, 500); // Esperar 500ms para no sobrecargar
    }

    // ========== GESTIÓN DE SOLICITUDES DE AMISTAD ==========

    async enviarSolicitudAmistad(usuarioId) {
        try {
            const response = await fetch(`${this.baseUrl}?action=enviar_solicitud`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    usuario_receptor_id: usuarioId
                })
            });

            const result = await response.json();
            
            if (result.success) {
                this.mostrarExito('Solicitud de amistad enviada');
                // Actualizar estado del usuario en la búsqueda
                this.buscarUsuarios();
            } else {
                this.mostrarError(result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarError('Error enviando solicitud');
        }
    }

    async responderSolicitud(usuarioId, accion) {
        try {
            const response = await fetch(`${this.baseUrl}?action=responder_solicitud`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    usuario_solicitante_id: usuarioId,
                    accion: accion
                })
            });

            const result = await response.json();
            
            if (result.success) {
                const mensaje = accion === 'aceptar' ? 'Solicitud aceptada' : 'Solicitud rechazada';
                this.mostrarExito(mensaje);
                
                // Recargar solicitudes y amigos
                this.cargarSolicitudesPendientes();
                this.cargarAmigos();
                this.buscarUsuarios(); // Actualizar búsqueda
            } else {
                this.mostrarError(result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarError('Error procesando solicitud');
        }
    }

    // ========== GESTIÓN DE MODALES ==========

    cerrarModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.remove();
        }
    }

    // ========== MENSAJES DE ÉXITO Y ERROR ==========

    mostrarExito(mensaje) {
        // Crear toast de éxito
        const toast = document.createElement('div');
        toast.className = 'alert alert-success alert-dismissible fade show position-fixed';
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>${mensaje}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        `;
        
        document.body.appendChild(toast);
        
        // Auto-remover después de 3 segundos
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 3000);
    }

    mostrarError(mensaje) {
        // Crear toast de error
        const toast = document.createElement('div');
        toast.className = 'alert alert-danger alert-dismissible fade show position-fixed';
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            <i class="fas fa-exclamation-circle me-2"></i>${mensaje}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        `;
        
        document.body.appendChild(toast);
        
        // Auto-remover después de 4 segundos
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 4000);
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.chatManager = new ChatManager();
});