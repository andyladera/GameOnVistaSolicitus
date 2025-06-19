// Variables globales
let torneoId = window.torneoId || null;
let torneoData = null;
let partidosData = [];
let equiposData = [];

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Sistema de gestión de torneo inicializado');
    if (torneoId) {
        inicializarEventos();
        cargarDatosTorneo();
    } else {
        showNotification('Error: ID de torneo no encontrado', 'error');
        setTimeout(() => window.location.href = 'torneos.php', 2000);
    }
});

function inicializarEventos() {
    // Tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            cambiarTab(this.dataset.tab);
        });
    });
    
    // Cerrar modal con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModalPartido();
        }
    });
}

async function cargarDatosTorneo() {
    try {
        showNotification('Cargando datos del torneo...', 'info');
        
        // Cargar información básica del torneo
        const responseDetalle = await fetch(`../../Controllers/TorneosController.php?action=obtener_detalles&torneo_id=${torneoId}`);
        const resultDetalle = await responseDetalle.json();
        
        if (resultDetalle.success) {
            torneoData = resultDetalle.torneo;
            equiposData = resultDetalle.equipos_inscritos;
            
            mostrarInfoTorneo();
            mostrarEquipos();
            
            // Cargar partidos
            await cargarPartidos();
            
            showNotification('Datos cargados correctamente', 'success');
        } else {
            throw new Error(resultDetalle.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al cargar datos del torneo: ' + error.message, 'error');
    }
}

async function cargarPartidos() {
    try {
        const response = await fetch(`../../Controllers/TorneosController.php?action=obtener_partidos&torneo_id=${torneoId}`);
        const result = await response.json();
        
        if (result.success) {
            partidosData = result.partidos;
            mostrarLlaves();
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Error cargando partidos:', error);
        mostrarLlavesVacias();
    }
}

function mostrarInfoTorneo() {
    const container = document.getElementById('torneoInfo');
    container.innerHTML = `
        <div class="torneo-info-content">
            <h3>${torneoData.nombre}</h3>
            <div class="torneo-info-details">
                <span class="info-badge">
                    <i class="fas fa-calendar"></i> 
                    ${formatearFecha(torneoData.fecha_inicio)}
                </span>
                <span class="info-badge">
                    <i class="fas fa-users"></i> 
                    ${torneoData.equipos_inscritos}/${torneoData.max_equipos} equipos
                </span>
                <span class="info-badge">
                    <i class="fas fa-map-marker-alt"></i> 
                    ${torneoData.sede_nombre}
                </span>
                <span class="info-badge estado-${torneoData.estado.replace(/_/g, '-')}">
                    <i class="fas fa-flag"></i> 
                    ${formatearEstado(torneoData.estado)}
                </span>
            </div>
        </div>
    `;
}

function mostrarLlaves() {
    const container = document.getElementById('llavesContainer');
    
    if (partidosData.length === 0) {
        mostrarLlavesVacias();
        return;
    }
    
    // Agrupar partidos por fase
    const partidosPorFase = agruparPartidosPorFase(partidosData);
    
    let html = '<div class="llaves-grid">';
    
    // Ordenar fases lógicamente
    const ordenFases = ['primera_ronda', 'segunda_ronda', 'tercera_ronda', 'cuartos', 'semifinal', 'final', 'tercer_lugar'];
    
    ordenFases.forEach(fase => {
        if (partidosPorFase[fase]) {
            html += `
                <div class="fase-container">
                    <div class="fase-header">
                        <h4 class="fase-titulo">
                            <i class="fas fa-layer-group"></i>
                            ${formatearFase(fase)}
                        </h4>
                        <span class="fase-info">${partidosPorFase[fase].length} partidos</span>
                    </div>
                    <div class="partidos-fase">
            `;
            
            partidosPorFase[fase].forEach(partido => {
                html += generarTarjetaPartido(partido);
            });
            
            html += '</div></div>';
        }
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function mostrarLlavesVacias() {
    const container = document.getElementById('llavesContainer');
    container.innerHTML = `
        <div class="empty-state">
            <i class="fas fa-sitemap"></i>
            <h3>No hay partidos programados</h3>
            <p>Los partidos aparecerán aquí cuando se complete la programación del torneo</p>
            <button class="btn-primary" onclick="cargarPartidos()">
                <i class="fas fa-sync-alt"></i> Verificar partidos
            </button>
        </div>
    `;
}

function generarTarjetaPartido(partido) {
    const fechaPartido = new Date(partido.fecha_partido);
    const equipo1 = partido.equipo_local_nombre || 'Equipo por definir';
    const equipo2 = partido.equipo_visitante_nombre || 'Equipo por definir';
    const resultado = partido.estado_partido === 'finalizado' ? 
        `${partido.resultado_local || 0} - ${partido.resultado_visitante || 0}` : 
        'vs';
    
    const estadoClass = partido.estado_partido === 'finalizado' ? 'finalizado' : 
                       partido.estado_partido === 'en_curso' ? 'en-curso' : 'programado';
    
    return `
        <div class="partido-card ${estadoClass}" data-partido-id="${partido.id}">
            <div class="partido-header">
                <div class="partido-numero">
                    <span class="numero">P${partido.numero_partido || 'X'}</span>
                    <span class="ronda">R${partido.ronda || '1'}</span>
                </div>
                <div class="partido-estado">
                    <span class="estado-badge ${estadoClass}">
                        ${formatearEstadoPartido(partido.estado_partido)}
                    </span>
                </div>
            </div>
            
            <div class="partido-equipos">
                <div class="equipo equipo-local">
                    <div class="equipo-nombre">${equipo1}</div>
                    ${partido.estado_partido === 'finalizado' ? 
                        `<div class="equipo-resultado ${(partido.resultado_local > partido.resultado_visitante) ? 'ganador' : ''}">${partido.resultado_local || 0}</div>` : 
                        '<div class="equipo-placeholder">-</div>'
                    }
                </div>
                
                <div class="versus">
                    <div class="vs-texto">${resultado}</div>
                </div>
                
                <div class="equipo equipo-visitante">
                    <div class="equipo-nombre">${equipo2}</div>
                    ${partido.estado_partido === 'finalizado' ? 
                        `<div class="equipo-resultado ${(partido.resultado_visitante > partido.resultado_local) ? 'ganador' : ''}">${partido.resultado_visitante || 0}</div>` : 
                        '<div class="equipo-placeholder">-</div>'
                    }
                </div>
            </div>
            
            <div class="partido-info">
                <div class="info-item">
                    <i class="fas fa-calendar"></i>
                    <span>${fechaPartido.toLocaleDateString('es-ES')}</span>
                </div>
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <span>${fechaPartido.toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'})}</span>
                </div>
                ${partido.nombre_area ? `
                    <div class="info-item">
                        <i class="fas fa-map-marked-alt"></i>
                        <span>${partido.nombre_area}</span>
                    </div>
                ` : ''}
            </div>
            
            <div class="partido-actions">
                ${partido.estado_partido === 'programado' ? 
                    `<button class="btn-small btn-primary" onclick="gestionarPartido(${partido.id})">
                        <i class="fas fa-play"></i> Iniciar
                    </button>` : 
                    `<button class="btn-small btn-secondary" onclick="verDetallesPartido(${partido.id})">
                        <i class="fas fa-eye"></i> Ver Detalles
                    </button>`
                }
                ${partido.estado_partido !== 'cancelado' ? 
                    `<button class="btn-small btn-outline" onclick="editarPartido(${partido.id})">
                        <i class="fas fa-edit"></i> Editar
                    </button>` : ''
                }
            </div>
        </div>
    `;
}

function mostrarEquipos() {
    const container = document.getElementById('equiposContainer');
    
    if (equiposData.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>No hay equipos inscritos</h3>
                <p>Los equipos aparecerán aquí cuando se inscriban al torneo</p>
                <div class="inscripcion-info">
                    <p><strong>Fecha límite de inscripción:</strong> ${formatearFecha(torneoData.fecha_inscripcion_fin)}</p>
                </div>
            </div>
        `;
        return;
    }
    
    let html = '<div class="equipos-grid">';
    
    equiposData.forEach((equipo, index) => {
        html += `
            <div class="equipo-card" style="animation-delay: ${index * 0.1}s">
                <div class="equipo-header">
                    <div class="equipo-nombre">
                        <i class="fas fa-shield-alt"></i>
                        <h4>${equipo.equipo_nombre}</h4>
                    </div>
                    <div class="equipo-estado">
                        <span class="estado-badge ${equipo.estado_inscripcion}">
                            ${formatearEstadoInscripcion(equipo.estado_inscripcion)}
                        </span>
                    </div>
                </div>
                
                <div class="equipo-info">
                    <div class="info-row">
                        <span class="info-label">Líder:</span>
                        <span class="info-value">${equipo.lider_nombre} ${equipo.lider_apellidos}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Miembros:</span>
                        <span class="info-value">${equipo.total_miembros} jugadores</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Inscrito:</span>
                        <span class="info-value">${formatearFecha(equipo.fecha_inscripcion.split(' ')[0])}</span>
                    </div>
                </div>
                
                <div class="equipo-actions">
                    <button class="btn-small btn-outline" onclick="verEquipo(${equipo.id})">
                        <i class="fas fa-eye"></i> Ver Detalles
                    </button>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

// Funciones de gestión de partidos
function gestionarPartido(partidoId) {
    const partido = partidosData.find(p => p.id === partidoId);
    if (!partido) return;
    
    editarPartido(partidoId);
}

function editarPartido(partidoId) {
    const partido = partidosData.find(p => p.id === partidoId);
    if (!partido) return;
    
    const modalContent = document.getElementById('editarPartidoContent');
    modalContent.innerHTML = `
        <form id="formEditarPartido" data-partido-id="${partidoId}">
            <div class="partido-form">
                <div class="form-header">
                    <h4>Partido ${partido.numero_partido || 'X'} - ${formatearFase(partido.fase)}</h4>
                    <p>Fecha: ${new Date(partido.fecha_partido).toLocaleDateString('es-ES')} a las ${new Date(partido.fecha_partido).toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'})}</p>
                </div>

                <div class="equipos-resultado">
                    <div class="equipo-resultado">
                        <label class="equipo-label">
                            <i class="fas fa-home"></i>
                            ${partido.equipo_local_nombre || 'Equipo Local'}
                        </label>
                        <input type="number" id="resultadoLocal" name="resultado_local" min="0" max="50" value="${partido.resultado_local || 0}">
                    </div>
                    
                    <div class="vs-resultado">
                        <span class="vs-text">VS</span>
                        <div class="estado-partido">
                            <select id="estadoPartido" name="estado_partido">
                                <option value="programado" ${partido.estado_partido === 'programado' ? 'selected' : ''}>Programado</option>
                                <option value="en_curso" ${partido.estado_partido === 'en_curso' ? 'selected' : ''}>En Curso</option>
                                <option value="finalizado" ${partido.estado_partido === 'finalizado' ? 'selected' : ''}>Finalizado</option>
                                <option value="suspendido" ${partido.estado_partido === 'suspendido' ? 'selected' : ''}>Suspendido</option>
                                <option value="cancelado" ${partido.estado_partido === 'cancelado' ? 'selected' : ''}>Cancelado</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="equipo-resultado">
                        <label class="equipo-label">
                            <i class="fas fa-plane"></i>
                            ${partido.equipo_visitante_nombre || 'Equipo Visitante'}
                        </label>
                        <input type="number" id="resultadoVisitante" name="resultado_visitante" min="0" max="50" value="${partido.resultado_visitante || 0}">
                    </div>
                </div>
                
                <div class="observaciones-section">
                    <label for="observaciones">Observaciones del partido:</label>
                    <textarea id="observaciones" name="observaciones" rows="4" placeholder="Comentarios, incidencias, notas del partido...">${partido.observaciones || ''}</textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="cerrarModalPartido()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Guardar Resultado
                    </button>
                </div>
            </div>
        </form>
    `;
    
    document.getElementById('modalEditarPartido').style.display = 'flex';
    
    // Event listener para el formulario
    document.getElementById('formEditarPartido').addEventListener('submit', async function(e) {
        e.preventDefault();
        await guardarResultadoPartido(e.target);
    });
}

async function guardarResultadoPartido(form) {
    try {
        const partidoId = form.dataset.partidoId;
        const formData = new FormData(form);
        
        const datos = {
            partido_id: partidoId,
            resultado_local: parseInt(formData.get('resultado_local')),
            resultado_visitante: parseInt(formData.get('resultado_visitante')),
            estado_partido: formData.get('estado_partido'),
            observaciones: formData.get('observaciones')
        };
        
        const response = await fetch('../../Controllers/TorneosController.php?action=actualizar_resultado', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(datos)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Resultado guardado correctamente', 'success');
            cerrarModalPartido();
            await cargarPartidos(); // Recargar partidos
        } else {
            showNotification('Error al guardar resultado: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error de conexión al guardar resultado', 'error');
    }
}

function verDetallesPartido(partidoId) {
    const partido = partidosData.find(p => p.id === partidoId);
    if (!partido) return;
    
    showNotification(`Detalles del partido ${partido.numero_partido || 'X'} - Funcionalidad en desarrollo`, 'info');
}

function verEquipo(equipoId) {
    const equipo = equiposData.find(e => e.id === equipoId);
    if (!equipo) return;
    
    showNotification(`Ver detalles del equipo ${equipo.equipo_nombre} - Funcionalidad en desarrollo`, 'info');
}

// Funciones auxiliares
function cambiarTab(tabName) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
    document.getElementById(tabName).classList.add('active');
    
    // Cargar datos específicos del tab si es necesario
    if (tabName === 'estadisticas') {
        cargarEstadisticas();
    }
}

function agruparPartidosPorFase(partidos) {
    return partidos.reduce((acc, partido) => {
        const fase = partido.fase;
        if (!acc[fase]) acc[fase] = [];
        acc[fase].push(partido);
        return acc;
    }, {});
}

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

function formatearEstadoPartido(estado) {
    const estados = {
        'programado': 'Programado',
        'en_curso': 'En Curso',
        'finalizado': 'Finalizado',
        'suspendido': 'Suspendido',
        'cancelado': 'Cancelado'
    };
    return estados[estado] || estado;
}

function formatearEstadoInscripcion(estado) {
    const estados = {
        'pendiente': 'Pendiente',
        'confirmada': 'Confirmado',
        'rechazada': 'Rechazado',
        'retirado': 'Retirado'
    };
    return estados[estado] || estado;
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

function formatearFecha(fecha) {
    if (!fecha) return 'No definida';
    return new Date(fecha).toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

function cerrarModalPartido() {
    document.getElementById('modalEditarPartido').style.display = 'none';
}

function actualizarDatos() {
    cargarDatosTorneo();
}

function cargarEstadisticas() {
    const container = document.getElementById('estadisticasContainer');
    container.innerHTML = `
        <div class="estadisticas-demo">
            <h3>Estadísticas del Torneo</h3>
            <p>Funcionalidad en desarrollo - Aquí se mostrarán:</p>
            <ul>
                <li>Tabla de posiciones</li>
                <li>Goleadores</li>
                <li>Estadísticas por equipo</li>
                <li>Jugadores destacados</li>
            </ul>
        </div>
    `;
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

// Agregar estilos CSS
const styles = document.createElement('style');
styles.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
`;
document.head.appendChild(styles);