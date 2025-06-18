// Variables globales
let torneosData = [];
let vistaActual = 'grid';
let filtroEstado = '';
let filtroDeporte = '';

// Inicializar al cargar DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Sistema de gestión de torneos inicializado');
    inicializarEventos();
    cargarTorneosReales();
});

// ✅ FUNCIÓN: Cargar torneos reales desde la base de datos
async function cargarTorneosReales() {
    try {
        showNotification('Cargando torneos...', 'info');
        
        const response = await fetch('../../Controllers/TorneosController.php?action=obtener_torneos');
        const result = await response.json();
        
        if (result.success) {
            torneosData = result.torneos;
            actualizarEstadisticas();
            mostrarTorneos(torneosData);
            
            if (torneosData.length === 0) {
                showNotification('No tienes torneos creados aún', 'info');
            } else {
                showNotification(`${torneosData.length} torneos cargados exitosamente`, 'success');
            }
        } else {
            console.error('Error cargando torneos:', result.message);
            showNotification('Error al cargar torneos: ' + result.message, 'error');
            cargarTorneosFicticios();
        }
    } catch (error) {
        console.error('Error de conexión:', error);
        showNotification('Error de conexión al cargar torneos', 'error');
        cargarTorneosFicticios();
    }
}

// ✅ FUNCIÓN: Ver detalles con datos reales
async function verDetalles(torneoId) {
    try {
        const response = await fetch(`../../Controllers/TorneosController.php?action=obtener_detalles&torneo_id=${torneoId}`);
        const result = await response.json();
        
        if (result.success) {
            const torneo = result.torneo;
            const equipos = result.equipos_inscritos;
            
            const modalContent = document.getElementById('detallesContent');
            modalContent.innerHTML = `
                <div class="torneo-detalles">
                    <div class="detalle-header">
                        <h3>${torneo.nombre}</h3>
                        <span class="badge estado-${torneo.estado.replace(/_/g, '-')}">${formatearEstado(torneo.estado)}</span>
                    </div>
                    
                    <div class="detalle-info">
                        <div class="info-grid">
                            <div class="info-item">
                                <strong>Deporte:</strong> ${torneo.deporte_nombre}
                            </div>
                            <div class="info-item">
                                <strong>Sede:</strong> ${torneo.sede_nombre}
                            </div>
                            <div class="info-item">
                                <strong>Modalidad:</strong> ${formatearModalidad(torneo.modalidad)}
                            </div>
                            <div class="info-item">
                                <strong>Equipos:</strong> ${torneo.equipos_inscritos}/${torneo.max_equipos}
                            </div>
                            <div class="info-item">
                                <strong>Inicio:</strong> ${formatearFecha(torneo.fecha_inicio)}
                            </div>
                            <div class="info-item">
                                <strong>Inscripciones:</strong> ${formatearFecha(torneo.fecha_inscripcion_fin)}
                            </div>
                        </div>
                        
                        <div class="descripcion">
                            <strong>Descripción:</strong>
                            <p>${torneo.descripcion}</p>
                        </div>
                        
                        <div class="premios">
                            <strong>Premios:</strong>
                            <p style="white-space: pre-line;">${torneo.premio_descripcion}</p>
                        </div>
                        
                        ${equipos.length > 0 ? `
                            <div class="equipos-inscritos">
                                <strong>Equipos Inscritos:</strong>
                                <div class="equipos-lista">
                                    ${equipos.map(equipo => `
                                        <div class="equipo-item">
                                            <span class="equipo-nombre">${equipo.equipo_nombre}</span>
                                            <span class="equipo-lider">${equipo.lider_nombre} ${equipo.lider_apellidos}</span>
                                            <span class="equipo-miembros">${equipo.total_miembros} miembros</span>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        ` : '<p>No hay equipos inscritos aún.</p>'}
                    </div>
                </div>
            `;
            
            document.getElementById('modalDetalles').style.display = 'flex';
        } else {
            showNotification('Error al cargar detalles: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error de conexión al cargar detalles', 'error');
    }
}

// Inicializar eventos
function inicializarEventos() {
    const filtroEstadoSelect = document.getElementById('filtroEstado');
    const filtroDeporteSelect = document.getElementById('filtroDeporte');
    
    if (filtroEstadoSelect) {
        filtroEstadoSelect.addEventListener('change', function() {
            filtroEstado = this.value;
            aplicarFiltros();
        });
    }
    
    if (filtroDeporteSelect) {
        filtroDeporteSelect.addEventListener('change', function() {
            filtroDeporte = this.value;
            aplicarFiltros();
        });
    }
    
    const btnsVista = document.querySelectorAll('.btn-vista');
    btnsVista.forEach(btn => {
        btn.addEventListener('click', function() {
            const vista = this.dataset.vista;
            cambiarVista(vista);
        });
    });
    
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            cerrarModal();
        }
        
        if (e.target.closest('.btn-close')) {
            cerrarModal();
        }
    });
}

// ✅ FUNCIÓN: Mostrar torneos en la vista actual
function mostrarTorneos(torneos) {
    if (vistaActual === 'grid') {
        mostrarTorneosGrid(torneos);
    } else {
        mostrarTorneosLista(torneos);
    }
}

// ✅ FUNCIÓN: Mostrar torneos en vista grid
function mostrarTorneosGrid(torneos) {
    const container = document.getElementById('torneosGrid');
    const listaContainer = document.getElementById('torneosLista');
    
    if (!container) return;
    
    container.style.display = 'grid';
    if (listaContainer) listaContainer.style.display = 'none';
    
    if (torneos.length === 0) {
        container.innerHTML = `
            <div class="empty-torneos" style="grid-column: 1/-1;">
                <i class="fas fa-trophy"></i>
                <h3>No hay torneos disponibles</h3>
                <p>Aún no se han creado torneos. ¡Crea el primer torneo!</p>
                <a href="crear_torneo.php" class="btn-primary-torneos">
                    <i class="fas fa-plus"></i> Crear Primer Torneo
                </a>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    torneos.forEach((torneo, index) => {
        const iconoDeporte = obtenerIconoDeporte(torneo.deporte_id);
        const estadoClass = `estado-${torneo.estado.replace(/_/g, '-')}`;
        const estadoTexto = formatearEstado(torneo.estado);
        const progreso = calcularProgreso(torneo);
        
        html += `
            <div class="torneo-card" style="animation-delay: ${index * 0.1}s">
                <div class="torneo-imagen">
                    ${torneo.imagen_torneo ? 
                        `<img src="${torneo.imagen_torneo}" alt="${torneo.nombre}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                         <div class="icono-deporte" style="display: none;"><i class="${iconoDeporte}"></i></div>` :
                        `<div class="icono-deporte"><i class="${iconoDeporte}"></i></div>`
                    }
                    <div class="torneo-estado ${estadoClass}">${estadoTexto}</div>
                </div>
                
                <div class="torneo-content">
                    <div class="torneo-header">
                        <h3 class="torneo-titulo">${torneo.nombre}</h3>
                        <div class="torneo-deporte">
                            <i class="${iconoDeporte}"></i>
                            <span>${obtenerNombreDeporte(torneo.deporte_id)}</span>
                        </div>
                    </div>
                    
                    <div class="torneo-info">
                        <div class="info-item">
                            <span class="info-label">Inicio</span>
                            <span class="info-value">${formatearFecha(torneo.fecha_inicio)}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Modalidad</span>
                            <span class="info-value">${formatearModalidad(torneo.modalidad)}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Inscripción</span>
                            <span class="info-value">S/. ${parseFloat(torneo.costo_inscripcion || 0).toFixed(2)}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Organizador</span>
                            <span class="info-value">${torneo.organizador_tipo === 'ipd' ? 'IPD' : 'Privado'}</span>
                        </div>
                    </div>
                    
                    <div class="torneo-sede">
                        <div class="sede-nombre">
                            <i class="fas fa-map-marker-alt"></i>
                            ${torneo.sede_nombre}
                        </div>
                        <div class="sede-info">Sede del torneo</div>
                    </div>
                    
                    <div class="torneo-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${progreso.porcentaje}%"></div>
                        </div>
                        <div class="progress-text">
                            <span>Equipos: ${torneo.equipos_inscritos}/${torneo.max_equipos}</span>
                            <span>${progreso.porcentaje}%</span>
                        </div>
                    </div>
                    
                    <div class="torneo-actions">
                        <button class="btn-action btn-ver" onclick="verDetalles(${torneo.id})">
                            <i class="fas fa-eye"></i> Ver
                        </button>
                        <button class="btn-action btn-editar" onclick="editarTorneo(${torneo.id})">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn-action btn-gestionar" onclick="gestionarTorneo(${torneo.id})">
                            <i class="fas fa-cogs"></i> Gestionar
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// ✅ FUNCIÓN: Mostrar torneos en vista lista
function mostrarTorneosLista(torneos) {
    const container = document.getElementById('torneosLista');
    const gridContainer = document.getElementById('torneosGrid');
    
    if (!container) return;
    
    container.style.display = 'block';
    if (gridContainer) gridContainer.style.display = 'none';
    
    if (torneos.length === 0) {
        container.innerHTML = `
            <div class="empty-torneos">
                <i class="fas fa-trophy"></i>
                <h3>No hay torneos disponibles</h3>
                <p>Aún no se han creado torneos. ¡Crea el primer torneo!</p>
                <a href="crear_torneo.php" class="btn-primary-torneos">
                    <i class="fas fa-plus"></i> Crear Primer Torneo
                </a>
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="lista-header">
            <div>Torneo</div>
            <div>Deporte</div>
            <div>Fechas</div>
            <div>Equipos</div>
            <div>Estado</div>
            <div>Acciones</div>
        </div>
    `;
    
    torneos.forEach(torneo => {
        const iconoDeporte = obtenerIconoDeporte(torneo.deporte_id);
        const estadoClass = `estado-${torneo.estado.replace(/_/g, '-')}`;
        const estadoTexto = formatearEstado(torneo.estado);
        
        html += `
            <div class="torneo-item">
                <div class="item-torneo">
                    <div class="item-titulo">${torneo.nombre}</div>
                    <div class="item-sede">
                        <i class="fas fa-map-marker-alt"></i>
                        ${torneo.sede_nombre}
                    </div>
                </div>
                
                <div class="item-deporte">
                    <i class="${iconoDeporte}"></i>
                    <span>${obtenerNombreDeporte(torneo.deporte_id)}</span>
                </div>
                
                <div class="item-fechas">
                    <div><strong>Inicio:</strong> ${formatearFecha(torneo.fecha_inicio)}</div>
                    <div><strong>Inscr:</strong> ${formatearFecha(torneo.fecha_inscripcion_fin)}</div>
                </div>
                
                <div class="item-equipos">
                    <div class="equipos-numero">${torneo.equipos_inscritos}</div>
                    <div class="equipos-max">de ${torneo.max_equipos}</div>
                </div>
                
                <div class="item-estado">
                    <span class="badge-estado ${estadoClass}">${estadoTexto}</span>
                </div>
                
                <div class="item-acciones">
                    <button class="btn-mini btn-ver" onclick="verDetalles(${torneo.id})" title="Ver detalles">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-mini btn-editar" onclick="editarTorneo(${torneo.id})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-mini btn-gestionar" onclick="gestionarTorneo(${torneo.id})" title="Gestionar">
                        <i class="fas fa-cogs"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// ✅ FUNCIÓN: Actualizar estadísticas
function actualizarEstadisticas() {
    const stats = {
        total: torneosData.length,
        activos: torneosData.filter(t => t.estado === 'activo').length,
        proximos: torneosData.filter(t => t.estado === 'inscripciones_abiertas' || t.estado === 'proximo').length,
        finalizados: torneosData.filter(t => t.estado === 'finalizado').length
    };
    
    const elements = {
        total: document.getElementById('totalTorneos'),
        activos: document.getElementById('torneosActivos'),
        proximos: document.getElementById('torneosProximos'),
        finalizados: document.getElementById('torneosFinalizados')
    };
    
    if (elements.total) elements.total.textContent = stats.total;
    if (elements.activos) elements.activos.textContent = stats.activos;
    if (elements.proximos) elements.proximos.textContent = stats.proximos;
    if (elements.finalizados) elements.finalizados.textContent = stats.finalizados;
}

// ✅ FUNCIONES DE UTILIDAD
function obtenerIconoDeporte(deporteId) {
    const iconos = {
        1: 'fas fa-futbol',
        2: 'fas fa-volleyball-ball', 
        3: 'fas fa-basketball-ball'
    };
    return iconos[deporteId] || 'fas fa-trophy';
}

function obtenerNombreDeporte(deporteId) {
    const nombres = {
        1: 'Fútbol',
        2: 'Vóley',
        3: 'Básquet'
    };
    return nombres[deporteId] || 'Deporte';
}

function formatearEstado(estado) {
    const estados = {
        'proximo': 'Próximo',
        'inscripciones_abiertas': 'Inscripciones Abiertas',
        'inscripciones_cerradas': 'Inscripciones Cerradas',
        'activo': 'En Curso',
        'finalizado': 'Finalizado',
        'cancelado': 'Cancelado'
    };
    return estados[estado] || estado;
}

function formatearModalidad(modalidad) {
    const modalidades = {
        'eliminacion_simple': 'Eliminación Simple',
        'eliminacion_doble': 'Eliminación Doble',
        'todos_contra_todos': 'Todos vs Todos',
        'grupos_eliminatoria': 'Grupos + Eliminatoria'
    };
    return modalidades[modalidad] || modalidad;
}

function formatearFecha(fecha) {
    if (!fecha) return 'No definida';
    return new Date(fecha).toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

function calcularProgreso(torneo) {
    const porcentaje = Math.round((torneo.equipos_inscritos / torneo.max_equipos) * 100);
    return { porcentaje };
}

// ✅ FUNCIONES DE INTERACCIÓN
function cambiarVista(vista) {
    vistaActual = vista;
    
    document.querySelectorAll('.btn-vista').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-vista="${vista}"]`).classList.add('active');
    
    mostrarTorneos(torneosData);
}

function aplicarFiltros() {
    let torneosFiltrados = torneosData;
    
    if (filtroEstado) {
        torneosFiltrados = torneosFiltrados.filter(t => t.estado === filtroEstado);
    }
    
    if (filtroDeporte) {
        torneosFiltrados = torneosFiltrados.filter(t => t.deporte_id == filtroDeporte);
    }
    
    mostrarTorneos(torneosFiltrados);
}

function editarTorneo(torneoId) {
    const torneo = torneosData.find(t => t.id === torneoId);
    if (!torneo) return;
    
    console.log('Editar torneo:', torneo);
    showNotification(`Editando: ${torneo.nombre}`, 'info');
}

function gestionarTorneo(torneoId) {
    const torneo = torneosData.find(t => t.id === torneoId);
    if (!torneo) return;
    
    console.log('Gestionar torneo:', torneo);
    showNotification(`Gestionando: ${torneo.nombre}`, 'info');
}

function cerrarModal() {
    const modales = document.querySelectorAll('.modal-overlay');
    modales.forEach(modal => {
        modal.style.display = 'none';
    });
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification-toast ${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${type === 'success' ? 'linear-gradient(135deg, #28a745, #20c997)' : 
                     type === 'error' ? 'linear-gradient(135deg, #dc3545, #e74c3c)' :
                     type === 'info' ? 'linear-gradient(135deg, #17a2b8, #6f42c1)' :
                     'linear-gradient(135deg, #17a2b8, #20c997)'};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        z-index: 1000;
        animation: slideInRight 0.3s ease;
        max-width: 300px;
        font-weight: 600;
    `;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 
                          type === 'error' ? 'exclamation-circle' : 
                          type === 'info' ? 'info-circle' : 'bell'}"></i>
        <span style="margin-left: 10px;">${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 4000);
}

// ✅ FUNCIÓN: Cargar torneos ficticios (fallback)
function cargarTorneosFicticios() {
    torneosData = [
        {
            id: 1,
            nombre: "Copa Top Gol 2025",
            descripcion: "Torneo de fútbol amateur",
            deporte_id: 1,
            estado: "inscripciones_abiertas",
            modalidad: "eliminacion_simple",
            max_equipos: 16,
            equipos_inscritos: 8,
            fecha_inicio: "2025-07-15",
            fecha_fin: "2025-07-28",
            fecha_inscripcion_fin: "2025-07-10",
            costo_inscripcion: 150.00,
            organizador_tipo: "institucion",
            sede_nombre: "Top Gol Tacna",
            imagen_torneo: null
        }
    ];
    
    actualizarEstadisticas();
    mostrarTorneos(torneosData);
    showNotification('Datos de demostración cargados', 'info');
}

// Agregar estilos de animación
const styles = document.createElement('style');
styles.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
`;
document.head.appendChild(styles);