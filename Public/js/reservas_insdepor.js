// Variables globales
let fechaSeleccionada = new Date().toISOString().split('T')[0];

// Inicializar al cargar DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Sistema de reservas inicializado');
    inicializarEventos();
});

// Inicializar eventos
function inicializarEventos() {
    // Event listener para cambio de fecha
    const fechaInput = document.getElementById('fechaReservas');
    if (fechaInput) {
        fechaInput.addEventListener('change', function() {
            fechaSeleccionada = this.value;
            cargarTodasLasAreas();
        });
    }
    
    // Event listeners para bloques de horarios
    document.addEventListener('click', function(e) {
        if (e.target.closest('.horario-bloque')) {
            const bloque = e.target.closest('.horario-bloque');
            if (bloque.classList.contains('ocupado')) {
                mostrarDetalleReserva(bloque);
            } else if (bloque.classList.contains('disponible')) {
                console.log('Bloque disponible clickeado:', bloque.dataset.hora);
            }
        }
    });
    
    // Cargar áreas iniciales
    setTimeout(cargarTodasLasAreas, 1000);
}

// ✅ NUEVA FUNCIÓN: Cargar todas las áreas
async function cargarTodasLasAreas() {
    const areasContainers = document.querySelectorAll('.horarios-grid[data-area-id]');
    
    for (const container of areasContainers) {
        const areaId = container.dataset.areaId;
        await cargarHorariosPorArea(areaId);
    }
    
    // Después cargar las reservas
    await cargarReservasReales();
    actualizarEstadisticas();
}

// ✅ NUEVA FUNCIÓN: Cargar horarios por área
async function cargarHorariosPorArea(areaId) {
    try {
        const response = await fetch(`../../Controllers/ReservaController.php?action=getCronograma&area_id=${areaId}&fecha=${fechaSeleccionada}`);
        const result = await response.json();
        
        if (result.success) {
            generarBloquesPorArea(areaId, result.data);
        } else {
            console.error('Error cargando horarios del área:', result.message);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// ✅ NUEVA FUNCIÓN: Generar bloques de horarios
function generarBloquesPorArea(areaId, datos) {
    const container = document.querySelector(`[data-area-id="${areaId}"] .horarios-grid`);
    if (!container || datos.cerrado) return;
    
    const { cronograma } = datos;
    let html = '';
    
    cronograma.forEach(bloque => {
        const claseEstado = bloque.disponible ? 'disponible' : 'ocupado';
        const horaTexto = `${bloque.hora_inicio}-${bloque.hora_fin}`;
        
        html += `
            <div class="horario-bloque ${claseEstado}" data-hora="${horaTexto}" data-area-id="${areaId}">
                <span class="hora">${bloque.hora_inicio}</span>
                <span class="duracion">30min</span>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// ✅ ACTUALIZAR FUNCIÓN: Cargar reservas reales desde la base de datos
async function cargarReservasReales() {
    try {
        const response = await fetch(`../../Controllers/ReservaController.php?action=reservas_institucion&fecha=${fechaSeleccionada}`);
        const result = await response.json();
        
        console.log('Respuesta completa del servidor:', result); // ✅ Debug mejorado
        
        if (result.success) {
            // ✅ SOLUCIÓN: Manejar ambas estructuras posibles
            let reservas = [];
            let partidos = [];
            
            if (result.data) {
                // Si viene envuelto en 'data'
                reservas = result.data.reservas || [];
                partidos = result.data.partidos || [];
            } else if (result.reservas !== undefined) {
                // Si viene directo
                reservas = result.reservas || [];
                partidos = result.partidos || [];
            }
            
            console.log('Reservas procesadas:', reservas);
            console.log('Partidos procesados:', partidos);
            
            actualizarVistaReservas({
                reservas: reservas,
                partidos: partidos
            });
        } else {
            console.error('Error del servidor:', result.message);
            showNotification('Error cargando reservas: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error de conexión:', error);
        showNotification('Error de conexión al cargar reservas', 'error');
    }
}

// ✅ ACTUALIZAR FUNCIÓN: Actualizar vista con reservas reales
function actualizarVistaReservas(data) {
    console.log('Datos recibidos en actualizarVistaReservas:', data);
    
    // ✅ VALIDACIÓN MEJORADA
    if (!data || typeof data !== 'object') {
        console.error('Datos inválidos recibidos:', data);
        showNotification('Error: Datos de reservas inválidos', 'error');
        return;
    }
    
    // ✅ VERIFICACIÓN ESPECÍFICA PARA ARRAYS
    let reservas = [];
    let partidos = [];
    
    if (Array.isArray(data.reservas)) {
        reservas = data.reservas;
    } else if (data.reservas) {
        console.error('reservas no es un array:', data.reservas);
        reservas = [];
    }
    
    if (Array.isArray(data.partidos)) {
        partidos = data.partidos;
    } else if (data.partidos) {
        console.error('partidos no es un array:', data.partidos);
        partidos = [];
    }
    
    console.log(`Procesando ${reservas.length} reservas y ${partidos.length} partidos`);
    
    // Limpiar reservas anteriores
    const todosLosBloques = document.querySelectorAll('.horario-bloque');
    todosLosBloques.forEach(bloque => {
        bloque.classList.remove('ocupado', 'partido-torneo');
        bloque.classList.add('disponible');
        const reservaInfo = bloque.querySelector('.reserva-info, .partido-info');
        if (reservaInfo) {
            reservaInfo.remove();
        }
    });
    
    // ✅ PROCESAR RESERVAS
    reservas.forEach((reserva, index) => {
        console.log(`Procesando reserva ${index + 1}:`, reserva);
        
        const areaId = reserva.area_deportiva_id;
        const horaInicio = reserva.hora_inicio ? reserva.hora_inicio.substring(0, 5) : '';
        const horaFin = reserva.hora_fin ? reserva.hora_fin.substring(0, 5) : '';
        
        if (!areaId || !horaInicio || !horaFin) {
            console.warn('Reserva con datos incompletos:', reserva);
            return;
        }
        
        // Buscar bloques que coincidan con la reserva
        const bloquesArea = document.querySelectorAll(`[data-area-id="${areaId}"] .horario-bloque`);
        console.log(`Encontrados ${bloquesArea.length} bloques para área ${areaId}`);
        
        bloquesArea.forEach(bloque => {
            const horaBloque = bloque.dataset.hora;
            const [inicioBloque] = horaBloque.split('-');
            
            // Si la hora del bloque está dentro del rango de la reserva
            if (inicioBloque >= horaInicio && inicioBloque < horaFin) {
                bloque.classList.remove('disponible');
                bloque.classList.add('ocupado');
                
                // Agregar información de la reserva
                const reservaInfo = document.createElement('div');
                reservaInfo.className = 'reserva-info';
                reservaInfo.innerHTML = `<small>${reserva.cliente_nombre || 'Cliente'}</small>`;
                bloque.appendChild(reservaInfo);
                
                // Actualizar datos para el modal
                bloque.dataset.tipoOcupacion = 'reserva';
                bloque.dataset.reservaId = reserva.id;
                bloque.dataset.clienteNombre = reserva.cliente_nombre || 'Cliente';
                bloque.dataset.clienteTelefono = reserva.cliente_telefono || 'No disponible';
                bloque.dataset.areaNombre = reserva.nombre_area || 'Área';
                bloque.dataset.tarifaHora = reserva.tarifa_por_hora || '0';
                bloque.dataset.estado = reserva.estado || 'confirmada';
            }
        });
    });
    
    // ✅ PROCESAR PARTIDOS
    partidos.forEach((partido, index) => {
        console.log(`Procesando partido ${index + 1}:`, partido);
        
        const areaId = partido.area_deportiva_id;
        const horaInicio = partido.hora_inicio ? partido.hora_inicio.substring(0, 5) : '';
        const horaFin = partido.hora_fin ? partido.hora_fin.substring(0, 5) : '';
        
        if (!areaId || !horaInicio || !horaFin) {
            console.warn('Partido con datos incompletos:', partido);
            return;
        }
        
        // Buscar bloques que coincidan con el partido (1 hora completa)
        const bloquesArea = document.querySelectorAll(`[data-area-id="${areaId}"] .horario-bloque`);
        
        bloquesArea.forEach(bloque => {
            const horaBloque = bloque.dataset.hora;
            const [inicioBloque] = horaBloque.split('-');
            
            // Si la hora del bloque está dentro del rango del partido
            if (inicioBloque >= horaInicio && inicioBloque < horaFin) {
                bloque.classList.remove('disponible');
                bloque.classList.add('partido-torneo');
                
                // Agregar información del partido
                const partidoInfo = document.createElement('div');
                partidoInfo.className = 'partido-info';
                const equipos = `${partido.equipo_local_nombre || 'TBD'} vs ${partido.equipo_visitante_nombre || 'TBD'}`;
                partidoInfo.innerHTML = `<small>${equipos}</small>`;
                bloque.appendChild(partidoInfo);
                
                // Actualizar datos para el modal
                bloque.dataset.tipoOcupacion = 'partido';
                bloque.dataset.partidoId = partido.id;
                bloque.dataset.torneoNombre = partido.torneo_nombre || 'Torneo';
                bloque.dataset.equipoLocal = partido.equipo_local_nombre || 'Por definir';
                bloque.dataset.equipoVisitante = partido.equipo_visitante_nombre || 'Por definir';
                bloque.dataset.fase = partido.fase || 'primera_ronda';
                bloque.dataset.numeroPartido = partido.numero_partido || 'X';
                bloque.dataset.estadoPartido = partido.estado_partido || 'programado';
                bloque.dataset.estadoTexto = partido.estado_texto || 'Programado';
                bloque.dataset.areaNombre = partido.nombre_area || 'Área';
                bloque.dataset.deporteNombre = partido.deporte_nombre || 'Deporte';
                bloque.dataset.modalidad = partido.torneo_modalidad || 'eliminacion_simple';
            }
        });
    });
    
    showNotification(`Cargadas ${reservas.length} reservas y ${partidos.length} partidos`, 'success');
}

// ✅ FUNCIÓN MEJORADA: Actualizar estadísticas
function actualizarEstadisticas() {
    const reservasHoyElement = document.getElementById('reservasHoy');
    const horasOcupadasElement = document.getElementById('horasOcupadas');
    const ingresosDiaElement = document.getElementById('ingresosDia');
    
    // Agregar efecto de carga
    [reservasHoyElement, horasOcupadasElement, ingresosDiaElement].forEach(el => {
        el.parentElement.parentElement.classList.add('stat-loading');
    });
    
    setTimeout(() => {
        const bloquesOcupados = document.querySelectorAll('.horario-bloque.ocupado');
        const bloquesPartidos = document.querySelectorAll('.horario-bloque.partido-torneo');
        const reservasHoy = new Set();
        const partidosHoy = new Set();
        
        // Contar reservas únicas
        bloquesOcupados.forEach(bloque => {
            if (bloque.dataset.reservaId) {
                reservasHoy.add(bloque.dataset.reservaId);
            }
        });
        
        // Contar partidos únicos
        bloquesPartidos.forEach(bloque => {
            if (bloque.dataset.partidoId) {
                partidosHoy.add(bloque.dataset.partidoId);
            }
        });
        
        // Actualizar reservas (incluye partidos)
        const totalReservas = reservasHoy.size + partidosHoy.size;
        reservasHoyElement.textContent = totalReservas;
        
        // Actualizar horas ocupadas
        const totalBloques = bloquesOcupados.length + bloquesPartidos.length;
        const horasOcupadas = Math.round(totalBloques * 0.5); // 30min = 0.5 horas
        horasOcupadasElement.textContent = horasOcupadas;
        
        // Calcular ingresos aproximados
        let ingresos = 0;
        bloquesOcupados.forEach(bloque => {
            const tarifa = parseFloat(bloque.dataset.tarifaHora || 0);
            ingresos += tarifa * 0.5; // 30 minutos
        });
        
        // Los partidos de torneo no generan ingresos directos, pero podrías agregar lógica aquí
        
        ingresosDiaElement.textContent = `S/. ${ingresos.toFixed(2)}`;
        
        // Remover efecto de carga
        [reservasHoyElement, horasOcupadasElement, ingresosDiaElement].forEach(el => {
            el.parentElement.parentElement.classList.remove('stat-loading');
        });
        
        // Agregar animación de conteo
        animateCountUp(reservasHoyElement, totalReservas);
        animateCountUp(horasOcupadasElement, horasOcupadas);
        
    }, 800);
}

// ✅ NUEVA FUNCIÓN: Animación de conteo
function animateCountUp(element, targetValue) {
    const startValue = 0;
    const duration = 1000;
    const stepTime = 50;
    const steps = duration / stepTime;
    const increment = targetValue / steps;
    let currentValue = startValue;
    
    const timer = setInterval(() => {
        currentValue += increment;
        if (currentValue >= targetValue) {
            currentValue = targetValue;
            clearInterval(timer);
        }
        
        if (element.id === 'ingresosDia') {
            element.textContent = `S/. ${currentValue.toFixed(2)}`;
        } else {
            element.textContent = Math.round(currentValue);
        }
    }, stepTime);
}

// Actualizar reservas
function actualizarReservas() {
    console.log('Actualizando reservas para fecha:', fechaSeleccionada);
    cargarTodasLasAreas();
}

// Filtrar por instalación
function filtrarPorInstalacion() {
    const filtro = document.getElementById('filtroInstalacion').value;
    const instalaciones = document.querySelectorAll('.instalacion-reservas');
    
    instalaciones.forEach(instalacion => {
        if (filtro === '' || instalacion.dataset.instalacionId === filtro) {
            instalacion.style.display = 'block';
        } else {
            instalacion.style.display = 'none';
        }
    });
}

// Filtrar por deporte
function filtrarPorDeporte() {
    const filtro = document.getElementById('filtroDeporte').value;
    const areas = document.querySelectorAll('.area-deportiva');
    
    areas.forEach(area => {
        if (filtro === '' || area.dataset.deporte === getDeporteNombre(filtro)) {
            area.style.display = 'block';
        } else {
            area.style.display = 'none';
        }
    });
}

// Filtrar por estado
function filtrarPorEstado() {
    const filtro = document.getElementById('filtroEstado').value;
    const bloques = document.querySelectorAll('.horario-bloque');
    
    bloques.forEach(bloque => {
        const esVisible = filtro === '' || bloque.classList.contains(filtro);
        bloque.style.display = esVisible ? 'flex' : 'none';
    });
}

// Obtener nombre del deporte
function getDeporteNombre(id) {
    const deportes = {
        '1': 'futbol',
        '2': 'voley',
        '3': 'basquet'
    };
    return deportes[id] || '';
}

// ✅ ACTUALIZAR FUNCIÓN: Mostrar detalle de reserva/partido
function mostrarDetalleReserva(bloque) {
    const modalBody = document.getElementById('modalBody');
    const hora = bloque.dataset.hora;
    const tipoOcupacion = bloque.dataset.tipoOcupacion || 'reserva';
    
    if (tipoOcupacion === 'partido') {
        // ✅ MOSTRAR INFORMACIÓN DEL PARTIDO
        modalBody.innerHTML = `
            <div class="detalle-partido">
                <h4><i class="fas fa-trophy"></i> Partido de Torneo</h4>
                <div class="partido-header">
                    <div class="torneo-nombre">${bloque.dataset.torneoNombre}</div>
                    <div class="partido-numero">Partido #${bloque.dataset.numeroPartido}</div>
                </div>
                
                <div class="equipos-enfrentamiento">
                    <div class="equipo">
                        <i class="fas fa-home"></i>
                        <span>${bloque.dataset.equipoLocal}</span>
                    </div>
                    <div class="vs">VS</div>
                    <div class="equipo">
                        <i class="fas fa-plane"></i>
                        <span>${bloque.dataset.equipoVisitante}</span>
                    </div>
                </div>
                
                <div class="info-item">
                    <strong>Área:</strong> ${bloque.dataset.areaNombre}
                </div>
                <div class="info-item">
                    <strong>Deporte:</strong> ${bloque.dataset.deporteNombre}
                </div>
                <div class="info-item">
                    <strong>Fecha:</strong> ${fechaSeleccionada}
                </div>
                <div class="info-item">
                    <strong>Horario:</strong> ${hora}
                </div>
                <div class="info-item">
                    <strong>Fase:</strong> ${formatearFase(bloque.dataset.fase)}
                </div>
                <div class="info-item">
                    <strong>Modalidad:</strong> ${formatearModalidad(bloque.dataset.modalidad)}
                </div>
                <div class="info-item">
                    <strong>Estado:</strong> <span class="badge ${bloque.dataset.estadoPartido}">${bloque.dataset.estadoTexto}</span>
                </div>
            </div>
        `;
        
        // Cambiar botones del modal
        const modalActions = document.querySelector('#modalReserva .modal-actions');
        modalActions.innerHTML = `
            <button class="btn-secondary" onclick="cerrarModal()">Cerrar</button>
            <button class="btn-primary" onclick="gestionarPartido('${bloque.dataset.partidoId}')">
                <i class="fas fa-cogs"></i> Gestionar Partido
            </button>
        `;
        
    } else {
        // ✅ MOSTRAR INFORMACIÓN DE RESERVA NORMAL
        const clienteNombre = bloque.dataset.clienteNombre || 'Cliente';
        const clienteTelefono = bloque.dataset.clienteTelefono || 'No disponible';
        const areaNombre = bloque.dataset.areaNombre || 'Área deportiva';
        const tarifaHora = bloque.dataset.tarifaHora || '0.00';
        const estado = bloque.dataset.estado || 'confirmada';
        
        modalBody.innerHTML = `
            <div class="detalle-reserva">
                <h4><i class="fas fa-calendar-check"></i> Reserva Individual</h4>
                <div class="info-item">
                    <strong>Cliente:</strong> ${clienteNombre}
                </div>
                <div class="info-item">
                    <strong>Área:</strong> ${areaNombre}
                </div>
                <div class="info-item">
                    <strong>Fecha:</strong> ${fechaSeleccionada}
                </div>
                <div class="info-item">
                    <strong>Horario:</strong> ${hora}
                </div>
                <div class="info-item">
                    <strong>Teléfono:</strong> ${clienteTelefono}
                </div>
                <div class="info-item">
                    <strong>Tarifa:</strong> S/. ${parseFloat(tarifaHora).toFixed(2)} por hora
                </div>
                <div class="info-item">
                    <strong>Estado:</strong> <span class="badge ${estado}">${estado.charAt(0).toUpperCase() + estado.slice(1)}</span>
                </div>
            </div>
        `;
        
        // Restaurar botones del modal
        const modalActions = document.querySelector('#modalReserva .modal-actions');
        modalActions.innerHTML = `
            <button class="btn-secondary" onclick="cerrarModal()">Cerrar</button>
            <button class="btn-primary" onclick="contactarCliente()">
                <i class="fas fa-phone"></i> Contactar Cliente
            </button>
        `;
    }
    
    document.getElementById('modalReserva').style.display = 'flex';
}

// ✅ NUEVAS FUNCIONES AUXILIARES
function formatearFase(fase) {
    const fases = {
        'primera_ronda': 'Primera Ronda',
        'segunda_ronda': 'Segunda Ronda',
        'tercera_ronda': 'Tercera Ronda',
        'cuartos': 'Cuartos de Final',
        'semifinal': 'Semifinal',
        'final': 'Final',
        'tercer_lugar': 'Tercer Lugar'
    };
    return fases[fase] || fase;
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

function gestionarPartido(partidoId) {
    // Redirigir a la gestión del partido
    window.open(`gestionar_torneo.php?torneo_id=${partidoId}`, '_blank');
}

// Cerrar modal
function cerrarModal() {
    document.getElementById('modalReserva').style.display = 'none';
}

// Contactar cliente
function contactarCliente() {
    const telefono = '+51946143071'; // Ejemplo
    window.open(`tel:${telefono}`, '_self');
}

// Mostrar notificaciones
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification-toast ${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${type === 'success' ? 'linear-gradient(135deg, #28a745, #20c997)' : 
                     type === 'error' ? 'linear-gradient(135deg, #dc3545, #e74c3c)' :
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
                          type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span style="margin-left: 10px;">${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 4000);
}

// Estilos adicionales
const additionalStyles = document.createElement('style');
additionalStyles.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    .detalle-reserva {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .detalle-reserva h4 {
        color: var(--primary-color);
        margin: 0;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--border-light);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid var(--border-light);
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge.confirmada {
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
        border: 1px solid rgba(40, 167, 69, 0.3);
    }
    
    .badge.pendiente {
        background: rgba(255, 193, 7, 0.1);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.3);
    }
    
    .loading-horarios {
        text-align: center;
        padding: 40px;
        color: var(--text-secondary);
    }
    
    .no-areas {
        text-align: center;
        padding: 40px;
        color: var(--text-secondary);
    }
`;
document.head.appendChild(additionalStyles);