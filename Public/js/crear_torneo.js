// Variables globales
let pasoActual = 1;
const totalPasos = 4;
let partidosHorarios = {}; // Almacenar horarios seleccionados por partido
let calendarioGenerado = false;

// Configuración de ImgBB API
const IMGBB_API_KEY = 'f94d58c09424ff225d85feee613de3a6';
const IMGBB_API_URL = 'https://api.imgbb.com/1/upload';

// ==================== FUNCIONES DE UTILIDAD PRIMERO ====================
function showNotification(message, type = 'success') {
    const colors = {
        success: 'linear-gradient(135deg, #28a745, #20c997)',
        error: 'linear-gradient(135deg, #dc3545, #e74c3c)',
        info: 'linear-gradient(135deg, #17a2b8, #6f42c1)',
        warning: 'linear-gradient(135deg, #ffc107, #fd7e14)'
    };
    
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        info: 'info-circle',
        warning: 'exclamation-triangle'
    };
    
    const notification = document.createElement('div');
    notification.className = `notification-toast ${type}`;
    notification.style.cssText = `
        position: fixed; top: 80px; right: 20px;
        background: ${colors[type] || colors.info};
        color: white; padding: 15px 20px; border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        z-index: 1000; animation: slideInRight 0.3s ease;
        max-width: 300px; font-weight: 600;
    `;
    notification.innerHTML = `
        <i class="fas fa-${icons[type] || icons.info}"></i>
        <span style="margin-left: 10px;">${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 4000);
}

function inicializarFormulario() {
    const hoy = new Date();
    const mañana = new Date(hoy);
    mañana.setDate(hoy.getDate() + 1);
    const fechaMinima = mañana.toISOString().split('T')[0];
    
    // Verificar que los elementos existan antes de asignar valores
    const fechaInicio = document.getElementById('fechaInicio');
    const fechaInscripcionInicio = document.getElementById('fechaInscripcionInicio');
    const fechaInscripcionFin = document.getElementById('fechaInscripcionFin');
    
    if (fechaInicio) fechaInicio.min = fechaMinima;
    if (fechaInscripcionInicio) fechaInscripcionInicio.min = hoy.toISOString().split('T')[0];
    if (fechaInscripcionFin) fechaInscripcionFin.min = hoy.toISOString().split('T')[0];
    
    console.log('✅ Formulario inicializado correctamente');
}

// Inicializar al cargar DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Sistema de creación de torneos inicializado');
    inicializarFormulario();
});

// ==================== NAVEGACIÓN ENTRE PASOS ====================
function siguientePaso(numeroPaso) {
    if (validarPasoActual()) {
        cambiarPaso(numeroPaso);
    }
}

function anteriorPaso(numeroPaso) {
    cambiarPaso(numeroPaso);
}

function cambiarPaso(numeroPaso) {
    // Ocultar paso actual
    const pasoActualElement = document.getElementById(`paso${pasoActual}`);
    const stepActualElement = document.querySelector(`.step[data-step="${pasoActual}"]`);
    
    if (pasoActualElement) pasoActualElement.classList.remove('active');
    if (stepActualElement) stepActualElement.classList.remove('active');
    
    // Marcar como completado si vamos hacia adelante
    if (numeroPaso > pasoActual && stepActualElement) {
        stepActualElement.classList.add('completed');
    }
    
    // Mostrar nuevo paso
    pasoActual = numeroPaso;
    const nuevoElement = document.getElementById(`paso${pasoActual}`);
    const nuevoStepElement = document.querySelector(`.step[data-step="${pasoActual}"]`);
    
    if (nuevoElement) nuevoElement.classList.add('active');
    if (nuevoStepElement) nuevoStepElement.classList.add('active');
    
    // Scroll al top
    const formContainer = document.querySelector('.form-container');
    if (formContainer) {
        formContainer.scrollIntoView({ behavior: 'smooth' });
    }
}

// ==================== VALIDACIÓN DE PASOS ====================
function validarPasoActual() {
    const validadores = {
        1: validarPaso1,
        2: validarPaso2,
        3: validarPaso3,
        4: validarPaso4
    };
    return validadores[pasoActual] ? validadores[pasoActual]() : true;
}

function validarPaso1() {
    const campos = [
        { id: 'nombreTorneo', mensaje: 'Por favor, ingresa el nombre del torneo' },
        { id: 'deporteTorneo', mensaje: 'Por favor, selecciona un deporte' },
        { id: 'sedeTorneo', mensaje: 'Por favor, selecciona una sede' }
    ];
    
    for (const campo of campos) {
        const elemento = document.getElementById(campo.id);
        if (!elemento || !elemento.value.trim()) {
            showNotification(campo.mensaje, 'error');
            if (elemento) elemento.focus();
            return false;
        }
    }
    return true;
}

function validarPaso2() {
    const maxEquipos = parseInt(document.getElementById('maxEquipos')?.value) || 0;
    const horario = document.getElementById('horarioTorneo')?.value;
    
    if (!maxEquipos || maxEquipos < 4) {
        showNotification('Mínimo 4 equipos para un torneo válido', 'error');
        const element = document.getElementById('maxEquipos');
        if (element) element.focus();
        return false;
    }
    
    if (!horario) {
        showNotification('Por favor, selecciona un horario para el torneo', 'error');
        const element = document.getElementById('horarioTorneo');
        if (element) element.focus();
        return false;
    }
    
    return true;
}

function validarPaso3() {
    const fechas = {
        inicio: document.getElementById('fechaInicio')?.value,
        inscripcionInicio: document.getElementById('fechaInscripcionInicio')?.value,
        inscripcionFin: document.getElementById('fechaInscripcionFin')?.value
    };
    
    // Validar campos requeridos
    if (!fechas.inicio) {
        showNotification('Por favor, selecciona la fecha de inicio del torneo', 'error');
        const element = document.getElementById('fechaInicio');
        if (element) element.focus();
        return false;
    }
    
    if (!fechas.inscripcionInicio) {
        showNotification('Por favor, selecciona la fecha de inicio de inscripciones', 'error');
        const element = document.getElementById('fechaInscripcionInicio');
        if (element) element.focus();
        return false;
    }
    
    if (!fechas.inscripcionFin) {
        showNotification('Por favor, selecciona la fecha de fin de inscripciones', 'error');
        const element = document.getElementById('fechaInscripcionFin');
        if (element) element.focus();
        return false;
    }
    
    // Validar lógica de fechas
    const hoy = new Date().toISOString().split('T')[0];
    
    if (fechas.inicio <= hoy) {
        showNotification('La fecha de inicio debe ser futura', 'error');
        return false;
    }
    
    if (fechas.inscripcionFin >= fechas.inicio) {
        showNotification('Las inscripciones deben cerrar antes del inicio del torneo', 'error');
        return false;
    }
    
    if (fechas.inscripcionInicio >= fechas.inscripcionFin) {
        showNotification('La fecha de inicio de inscripciones debe ser anterior al fin', 'error');
        return false;
    }
    
    // ✅ VALIDAR HORARIOS ASIGNADOS
    const maxEquipos = parseInt(document.getElementById('maxEquipos')?.value) || 0;
    const modalidad = document.getElementById('modalidadTorneo')?.value;
    
    if (maxEquipos && modalidad) {
        const estructuraPartidos = generarEstructuraPartidos(maxEquipos, modalidad);
        const partidosAsignados = Object.keys(partidosHorarios).length;
        const totalPartidos = estructuraPartidos.length;
        
        if (partidosAsignados < totalPartidos) {
            showNotification(`Faltan asignar horarios a ${totalPartidos - partidosAsignados} partido(s)`, 'error');
            return false;
        }
    }
    
    return true;
}

function validarPaso4() {
    const premio1 = document.getElementById('premio1')?.value;
    const premio2 = document.getElementById('premio2')?.value;
    const premio3 = document.getElementById('premio3')?.value;
    
    if (!premio1 || !premio2 || !premio3) {
        showNotification('Por favor, completa todos los premios (1er, 2do y 3er puesto)', 'error');
        return false;
    }
    
    return true;
}

// ==================== VALIDACIONES ESPECÍFICAS ====================
function validarEquipos() {
    const maxEquiposElement = document.getElementById('maxEquipos');
    const maxEquipos = parseInt(maxEquiposElement?.value) || 0;
    const btnSolicitud = document.getElementById('btnSolicitudIPD');
    
    if (!maxEquiposElement) return;
    
    if (maxEquipos > 15) {
        maxEquiposElement.value = 15;
        if (btnSolicitud) btnSolicitud.style.display = 'block';
        showNotification('Máximo 15 equipos para torneos privados. Para más equipos, solicita apoyo del IPD.', 'warning');
    } else if (maxEquipos < 4 && maxEquipos > 0) {
        maxEquiposElement.value = 4;
        showNotification('Mínimo 4 equipos para un torneo válido', 'warning');
    } else {
        if (btnSolicitud) btnSolicitud.style.display = 'none';
    }
    
    calcularHorarios();
}

function validarPremios(premiosTexto) {
    if (!premiosTexto) return false;
    
    // Verificar que cada línea de premio tenga contenido
    const lineasPremio = [
        /🥇.*1er\s*puesto:(.+)/i,
        /🥈.*2do\s*puesto:(.+)/i,
        /🥉.*3er\s*puesto:(.+)/i
    ];
    
    return lineasPremio.every(patron => {
        const match = premiosTexto.match(patron);
        return match && match[1].trim().length > 0;
    });
}

// ==================== CÁLCULO Y PREVISUALIZACIÓN ====================
function calcularHorarios() {
    const fechaInicio = document.getElementById('fechaInicio')?.value;
    const maxEquipos = parseInt(document.getElementById('maxEquipos')?.value) || 0;
    const modalidad = document.getElementById('modalidadTorneo')?.value;
    const horario = document.getElementById('horarioTorneo')?.value;
    
    const previsualizacion = document.getElementById('previsualizacionHorarios');
    
    if (!fechaInicio || !maxEquipos || !modalidad || !horario) {
        if (previsualizacion) previsualizacion.style.display = 'none';
        return;
    }
    
    const partidosTotales = calcularPartidos(maxEquipos, modalidad);
    const diasNecesarios = Math.ceil(partidosTotales / 4);
    
    // Calcular fecha de fin
    let fechaFin = new Date(fechaInicio);
    if (horario === 'fines_semana') {
        let finesDeSemanaNecesarios = Math.ceil(diasNecesarios / 2);
        let finesDeSemanasContados = 0;
        
        while (finesDeSemanasContados < finesDeSemanaNecesarios) {
            fechaFin.setDate(fechaFin.getDate() + 1);
            if (fechaFin.getDay() === 0) {
                finesDeSemanasContados++;
            }
        }
    } else {
        fechaFin.setDate(fechaFin.getDate() + diasNecesarios - 1);
    }
    
    const fechaFinElement = document.getElementById('fechaFin');
    if (fechaFinElement) {
        fechaFinElement.value = fechaFin.toISOString().split('T')[0];
    }
    
    // ✅ GENERAR PREVISUALIZACIÓN COMPLETA
    generarPrevisualizacion(maxEquipos, modalidad, fechaInicio, fechaFin.toISOString().split('T')[0], horario, partidosTotales, diasNecesarios);
    
    // ✅ GENERAR CALENDARIO DE SELECCIÓN DE HORARIOS
    setTimeout(() => {
        if (previsualizacion && previsualizacion.style.display !== 'none') {
            if (!calendarioGenerado) {
                generarCalendarioReservas();
            }
        }
    }, 500);
}

function calcularPartidos(equipos, modalidad) {
    const modalidades = {
        'eliminacion_simple': equipos - 1,
        'eliminacion_doble': (equipos - 1) * 2,
        'todos_contra_todos': (equipos * (equipos - 1)) / 2,
        'grupos_eliminatoria': Math.floor(equipos / 4) * 6 + 7
    };
    return modalidades[modalidad] || equipos - 1;
}

function generarPrevisualizacion(equipos, modalidad, fechaInicio, fechaFin, horario, partidos, dias) {
    const container = document.getElementById('previsualizacionHorarios');
    const infoContainer = document.getElementById('infoTorneo');
    const bracketsContainer = document.getElementById('bracketsPreview');
    
    if (!container || !infoContainer || !bracketsContainer) return;
    
    // Información básica
    infoContainer.innerHTML = `
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 15px;">
            <div style="text-align: center; padding: 15px; background: white; border-radius: 8px; border: 2px solid #28a745;">
                <div style="font-size: 28px; font-weight: bold; color: #28a745;">${equipos}</div>
                <div style="font-size: 12px; color: #666; font-weight: 600;">Equipos</div>
            </div>
            <div style="text-align: center; padding: 15px; background: white; border-radius: 8px; border: 2px solid #17a2b8;">
                <div style="font-size: 28px; font-weight: bold; color: #17a2b8;">${partidos}</div>
                <div style="font-size: 12px; color: #666; font-weight: 600;">Partidos</div>
            </div>
            <div style="text-align: center; padding: 15px; background: white; border-radius: 8px; border: 2px solid #ffc107;">
                <div style="font-size: 28px; font-weight: bold; color: #856404;">${dias}</div>
                <div style="font-size: 12px; color: #666; font-weight: 600;">Días</div>
            </div>
            <div style="text-align: center; padding: 15px; background: white; border-radius: 8px; border: 2px solid #dc3545;">
                <div style="font-size: 16px; font-weight: bold, color: #dc3545;">${fechaFin}</div>
                <div style="font-size: 12px; color: #666; font-weight: 600;">Fecha Fin</div>
            </div>
        </div>
    `;
    
    // ✅ GENERAR BRACKETS
    if (modalidad === 'eliminacion_simple') {
        bracketsContainer.innerHTML = generarBracketsEliminacion(equipos);
    }
    
    // Mostrar información de horarios
    mostrarHorariosRecomendados(fechaInicio, fechaFin, horario, partidos, equipos, modalidad);
    
    container.style.display = 'block';
}

// ==================== SELECCIÓN DE HORARIOS PARA PARTIDOS ====================
async function generarCalendarioReservas() {
    const fechaInicio = document.getElementById('fechaInicio')?.value;
    const fechaFin = document.getElementById('fechaFin')?.value;
    const sedeId = document.getElementById('sedeTorneo')?.value;
    const deporteId = document.getElementById('deporteTorneo')?.value;
    const horarioTorneo = document.getElementById('horarioTorneo')?.value;
    const maxEquipos = parseInt(document.getElementById('maxEquipos')?.value) || 0;
    const modalidad = document.getElementById('modalidadTorneo')?.value;

    if (!fechaInicio || !fechaFin || !sedeId || !deporteId || !horarioTorneo || !maxEquipos) {
        return;
    }

    const container = document.getElementById('calendarioHorarios');
    if (!container) {
        // Crear el contenedor si no existe
        const previsualizacion = document.getElementById('previsualizacionHorarios');
        if (!previsualizacion) return;
        
        const calendarioDiv = document.createElement('div');
        calendarioDiv.id = 'calendarioHorarios';
        calendarioDiv.innerHTML = `
            <div style="background: white; padding: 20px; border-radius: 8px; margin-top: 15px; border: 2px solid #17a2b8;">
                <h5 style="color: #17a2b8; margin: 0 0 20px 0;">
                    <i class="fas fa-calendar-alt"></i> Seleccionar Horarios para Partidos
                </h5>
                <div id="calendarioContent"></div>
            </div>
        `;
        previsualizacion.appendChild(calendarioDiv);
    }

    showNotification('Generando calendario de horarios...', 'info');

    try {
        await cargarHorariosDisponibles(fechaInicio, fechaFin, sedeId, deporteId, horarioTorneo, modalidad, maxEquipos);
        calendarioGenerado = true;
    } catch (error) {
        console.error('Error generando calendario:', error);
        showNotification('Error al generar calendario de horarios', 'error');
    }
}

async function cargarHorariosDisponibles(fechaInicio, fechaFin, sedeId, deporteId, horarioTorneo, modalidad, maxEquipos) {
    const content = document.getElementById('calendarioContent');
    if (!content) return;
    
    const fechaInicioObj = new Date(fechaInicio);
    const fechaFinObj = new Date(fechaFin);
    
    let html = '';
    let fechaActual = new Date(fechaInicioObj);
    
    // Filtrar solo los días según el horario del torneo
    const diasPermitidos = obtenerDiasPermitidos(horarioTorneo);
    
    while (fechaActual <= fechaFinObj) {
        const diaSemana = fechaActual.getDay();
        const nombreDia = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'][diaSemana];
        
        if (diasPermitidos.includes(nombreDia)) {
            const fechaStr = fechaActual.toISOString().split('T')[0];
            const fechaFormateada = fechaActual.toLocaleDateString('es-ES', {
                weekday: 'long',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            
            html += `
    <div class="calendario-dia" data-fecha="${fechaStr}">
        <div class="calendario-header">
            <h6>${fechaFormateada}</h6>
            <button type="button" onclick="cargarHorariosDia('${fechaStr}', ${sedeId}, ${deporteId}, '${horarioTorneo}')" 
                    class="btn-cargar-horarios">
                <i class="fas fa-clock"></i> Ver Horarios
            </button>
        </div>
        <div class="horarios-container" id="horarios-${fechaStr}" style="display: none;"></div>
    </div>
`;
        }
        
        fechaActual.setDate(fechaActual.getDate() + 1);
    }
    
    // Agregar sección de partidos programados
    html += `
        <div class="partidos-programados">
            <h6><i class="fas fa-list"></i> Partidos Programados</h6>
            <div id="partidosProgramados"></div>
        </div>
    `;
    
    content.innerHTML = html;
    
    // Mostrar estructura de partidos
    const estructuraPartidos = generarEstructuraPartidos(maxEquipos, modalidad);
    mostrarEstructuraPartidos(estructuraPartidos);
    
    // Inicializar lista de partidos programados
    actualizarPartidosProgramados();
}

// ✅ FUNCIÓN CORREGIDA: Cargar horarios de un día específico
async function cargarHorariosDia(fecha, sedeId, deporteId, horarioTorneo) {
    const container = document.getElementById(`horarios-${fecha}`);
    const btn = document.querySelector(`[onclick*="cargarHorariosDia('${fecha}'"]`);
    
    if (!container || !btn) return;
    
    if (container.style.display === 'block') {
        container.style.display = 'none';
        btn.innerHTML = '<i class="fas fa-clock"></i> Ver Horarios';
        return;
    }
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
    
    try {
        // ✅ OBTENER ÁREAS DE LA SEDE SELECCIONADA
        const response = await fetch(`../../Controllers/AreasDeportivasController.php?action=obtener_areas_por_sede&sede_id=${sedeId}&deporte_id=${deporteId}`);
        const result = await response.json();
        
        if (result.success && result.areas.length > 0) {
            let html = '<div class="areas-disponibles">';
            
            for (const area of result.areas) {
                // ✅ OBTENER HORARIOS DE CADA ÁREA
                const horariosArea = await obtenerHorariosArea(area.id, fecha);
                html += generarBloqueHorariosArea(area, horariosArea, fecha);
            }
            
            html += '</div>';
            container.innerHTML = html;
            container.style.display = 'block';
            btn.innerHTML = '<i class="fas fa-eye-slash"></i> Ocultar';
        } else {
            container.innerHTML = '<p style="text-align: center; color: #666; padding: 20px;">No hay áreas disponibles para este deporte en la sede seleccionada</p>';
            container.style.display = 'block';
            btn.innerHTML = '<i class="fas fa-eye-slash"></i> Ocultar';
        }
    } catch (error) {
        console.error('Error:', error);
        container.innerHTML = '<p style="text-align: center; color: #dc3545; padding: 20px;">Error al cargar horarios</p>';
        container.style.display = 'block';
        btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error';
    }
}

// ✅ FUNCIÓN CORREGIDA: Obtener horarios de un área específica
async function obtenerHorariosArea(areaId, fecha) {
    try {
        const response = await fetch(`../../Controllers/ReservaController.php?action=getCronograma&area_id=${areaId}&fecha=${fecha}`);
        const result = await response.json();
        
        if (result.success && result.data.cronograma) {
            return result.data.cronograma;
        }
        return [];
    } catch (error) {
        console.error('Error obteniendo horarios:', error);
        return [];
    }
}

// Modificar la función generarBloqueHorariosArea para crear bloques de 1 hora
function generarBloqueHorariosArea(area, cronograma, fecha) {
    let html = `
        <div class="area-horarios">
            <div class="area-info">
                <strong><i class="fas fa-map-marked-alt"></i> ${area.nombre_area}</strong>
                <span class="tarifa">S/. ${parseFloat(area.tarifa_por_hora).toFixed(2)}/hora</span>
            </div>
            <div class="horarios-grid">
    `;
    
    if (cronograma.length > 0) {
        // Agrupar cronograma en bloques de 1 hora
        const bloquesHora = agruparEnBloquesDe1Hora(cronograma);
        
        bloquesHora.forEach(bloque => {
            const partidoAsignado = partidosHorarios[`${fecha}-${area.id}-${bloque.hora_inicio}`];
            const claseExtra = partidoAsignado ? 'reservado-torneo' : '';
            const disponible = bloque.disponible && !partidoAsignado;
            
            html += `
                <div class="horario-bloque-torneo ${disponible ? 'disponible' : 'ocupado'} ${claseExtra}"
                     data-fecha="${fecha}"
                     data-hora-inicio="${bloque.hora_inicio}"
                     data-hora-fin="${bloque.hora_fin}"
                     data-area-id="${area.id}"
                     data-area-nombre="${area.nombre_area}"
                     data-tarifa="${area.tarifa_por_hora}"
                     onclick="${disponible ? 'seleccionarHorarioPartido(this)' : ''}"
                     title="${disponible ? 'Disponible - Bloque de 1 hora' : 'Ocupado o ya asignado'}">
                    <div class="hora-texto">${bloque.hora_inicio} - ${bloque.hora_fin}</div>
                    <div class="duracion-texto">1h completa</div>
                    ${!bloque.disponible ? '<div class="ocupado-texto">Ocupado</div>' : ''}
                    ${partidoAsignado ? `<div class="partido-asignado">${partidoAsignado.partidoNombre}</div>` : ''}
                </div>
            `;
        });
    } else {
        html += '<div style="grid-column: 1/-1; text-align: center; color: #666; padding: 20px;">Cerrado este día</div>';
    }
    
    html += '</div></div>';
    return html;
}

// Nueva función para agrupar cronograma en bloques de 1 hora
function agruparEnBloquesDe1Hora(cronograma) {
    const bloques = [];
    
    for (let i = 0; i < cronograma.length - 1; i += 2) {
        const bloque1 = cronograma[i];
        const bloque2 = cronograma[i + 1];
        
        // Verificar si son consecutivos y forman 1 hora
        if (bloque2 && esConsecutivo(bloque1.hora_inicio, bloque2.hora_inicio)) {
            bloques.push({
                hora_inicio: bloque1.hora_inicio,
                hora_fin: bloque2.hora_fin,
                disponible: bloque1.disponible && bloque2.disponible
            });
        } else {
            // Si no hay bloque consecutivo, crear uno de 30 min
            bloques.push({
                hora_inicio: bloque1.hora_inicio,
                hora_fin: bloque1.hora_fin,
                disponible: bloque1.disponible
            });
        }
    }
    
    return bloques;
}

// Función auxiliar para verificar si dos horarios son consecutivos
function esConsecutivo(hora1, hora2) {
    const [h1, m1] = hora1.split(':').map(Number);
    const [h2, m2] = hora2.split(':').map(Number);
    
    const tiempo1 = h1 * 60 + m1;
    const tiempo2 = h2 * 60 + m2;
    
    return (tiempo2 - tiempo1) === 30;
}

// ✅ FUNCIÓN CORREGIDA: Generar bloques de horarios para un área
function generarBloqueHorariosArea(area, cronograma, fecha) {
    let html = `
        <div class="area-horarios">
            <div class="area-info">
                <strong><i class="fas fa-map-marked-alt"></i> ${area.nombre_area}</strong>
                <span class="tarifa">S/. ${parseFloat(area.tarifa_por_hora).toFixed(2)}/hora</span>
            </div>
            <div class="horarios-grid">
    `;
    
    if (cronograma.length > 0) {
        // Agrupar cronograma en bloques de 1 hora
        const bloquesHora = agruparEnBloquesDe1Hora(cronograma);
        
        bloquesHora.forEach(bloque => {
            const partidoAsignado = partidosHorarios[`${fecha}-${area.id}-${bloque.hora_inicio}`];
            const claseExtra = partidoAsignado ? 'reservado-torneo' : '';
            const disponible = bloque.disponible && !partidoAsignado;
            
            html += `
                <div class="horario-bloque-torneo ${disponible ? 'disponible' : 'ocupado'} ${claseExtra}"
                     data-fecha="${fecha}"
                     data-hora-inicio="${bloque.hora_inicio}"
                     data-hora-fin="${bloque.hora_fin}"
                     data-area-id="${area.id}"
                     data-area-nombre="${area.nombre_area}"
                     data-tarifa="${area.tarifa_por_hora}"
                     onclick="${disponible ? 'seleccionarHorarioPartido(this)' : ''}"
                     title="${disponible ? 'Disponible - Bloque de 1 hora' : 'Ocupado o ya asignado'}">
                    <div class="hora-texto">${bloque.hora_inicio} - ${bloque.hora_fin}</div>
                    <div class="duracion-texto">1h completa</div>
                    ${!bloque.disponible ? '<div class="ocupado-texto">Ocupado</div>' : ''}
                    ${partidoAsignado ? `<div class="partido-asignado">${partidoAsignado.partidoNombre}</div>` : ''}
                </div>
            `;
        });
    } else {
        html += '<div style="grid-column: 1/-1; text-align: center; color: #666; padding: 20px;">Cerrado este día</div>';
    }
    
    html += '</div></div>';
    return html;
}

// ✅ RESTO DE FUNCIONES PARA HORARIOS
function seleccionarHorarioPartido(elemento) {
    const fecha = elemento.dataset.fecha;
    const horaInicio = elemento.dataset.horaInicio;
    const horaFin = elemento.dataset.horaFin;
    const areaId = elemento.dataset.areaId;
    const areaNombre = elemento.dataset.areaNombre;
    const tarifa = elemento.dataset.tarifa;
    
    mostrarModalAsignarPartido(fecha, horaInicio, horaFin, areaId, areaNombre, tarifa, elemento);
}

function mostrarModalAsignarPartido(fecha, horaInicio, horaFin, areaId, areaNombre, tarifa, elemento) {
    const maxEquipos = parseInt(document.getElementById('maxEquipos')?.value) || 0;
    const modalidad = document.getElementById('modalidadTorneo')?.value;
    const estructuraPartidos = generarEstructuraPartidos(maxEquipos, modalidad);
    
    // Obtener partidos disponibles (sin horario asignado)
    const partidosDisponibles = estructuraPartidos.filter(partido => {
        const clave = `${fecha}-${areaId}-${horaInicio}`;
        return !partidosHorarios[clave];
    });
    
    if (partidosDisponibles.length === 0) {
        showNotification('Todos los partidos ya tienen horario asignado', 'warning');
        return;
    }
    
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.style.cssText = `
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.7); display: flex; align-items: center;
        justify-content: center; z-index: 1001;
    `;
    
    const fechaFormateada = new Date(fecha).toLocaleDateString('es-ES', {
        weekday: 'long',
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
    
    modal.innerHTML = `
        <div style="background: white; border-radius: 12px; width: 90%; max-width: 600px; max-height: 80vh; overflow-y: auto;">
            <div style="padding: 20px; border-bottom: 2px solid #f1f3f5; background: #17a2b8; color: white; border-radius: 12px 12px 0 0;">
                <h3 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-clock"></i> Asignar Partido
                </h3>
                <p style="margin: 5px 0 0 0; opacity: 0.9;">
                    ${areaNombre} - ${fechaFormateada} ${horaInicio} - ${horaFin}
                </p>
            </div>
            
            <div style="padding: 20px;">
                <h4 style="margin: 0 0 15px 0; color: #2c3e50;">Selecciona el partido:</h4>
                <div style="display: grid; gap: 10px; max-height: 300px; overflow-y: auto;">
                    ${partidosDisponibles.map(partido => `
                        <div class="partido-opcion" onclick="asignarHorarioAPartido('${partido.id}', '${fecha}', '${horaInicio}', '${horaFin}', '${areaId}', '${areaNombre}', '${tarifa}', this.closest('.modal-overlay'))"
                             style="padding: 15px; border: 2px solid #e9ecef; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; background: white;">
                            <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">
                                ${partido.nombre}
                            </div>
                            <div style="font-size: 0.9em; color: #6c757d;">
                                ${partido.fase} - ${partido.descripcion}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
            
            <div style="padding: 15px 20px; border-top: 1px solid #f1f3f5; text-align: right;">
                <button onclick="this.closest('.modal-overlay').remove()" 
                        style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer;">
                    Cancelar
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// Modificar la función asignarHorarioAPartido para incluir fase
function asignarHorarioAPartido(partidoId, fecha, horaInicio, horaFin, areaId, areaNombre, tarifa, modal) {
    // Obtener información completa del partido de la estructura
    const maxEquipos = parseInt(document.getElementById('maxEquipos')?.value) || 0;
    const modalidad = document.getElementById('modalidadTorneo')?.value;
    const estructuraPartidos = generarEstructuraPartidos(maxEquipos, modalidad);
    
    const partidoCompleto = estructuraPartidos.find(p => p.id === partidoId);
    
    if (!partidoCompleto) {
        showNotification('Error: No se encontró información del partido', 'error');
        return;
    }
    
    // Guardar asignación con información completa
    const clavePartido = `${fecha}-${areaId}-${horaInicio}`;
    partidosHorarios[clavePartido] = {
        partidoId: partidoCompleto.id,
        fase: partidoCompleto.fase,
        numeroPartido: partidoCompleto.numeroPartido, // ✅ YA EXISTE
        ronda: partidoCompleto.ronda, // ✅ YA EXISTE
        descripcion: partidoCompleto.descripcion,
        fecha,
        horaInicio,
        horaFin,
        areaId,
        areaNombre,
        tarifa,
        partidoNombre: partidoCompleto.nombre,
        equipo1: partidoCompleto.equipo1,
        equipo2: partidoCompleto.equipo2
    };
    
    // Marcar el bloque completo como reservado
    const elemento = document.querySelector(`[data-fecha="${fecha}"][data-hora-inicio="${horaInicio}"][data-area-id="${areaId}"]`);
    if (elemento) {
        elemento.classList.remove('disponible');
        elemento.classList.add('reservado-torneo');
        elemento.innerHTML = `
            <div class="hora-texto">${horaInicio} - ${horaFin}</div>
            <div class="partido-asignado">${partidoCompleto.nombre}</div>
            <div class="fase-asignada">${partidoCompleto.fase}</div>
        `;
        elemento.onclick = () => eliminarAsignacionPartido(clavePartido, elemento);
    }
    
    // Actualizar lista de partidos programados
    actualizarPartidosProgramados();
    
    // Cerrar modal
    modal.remove();
    
    showNotification(`${partidoCompleto.nombre} (${partidoCompleto.fase}) asignado correctamente`, 'success');
}

function eliminarAsignacionPartido(clavePartido, elemento) {
    if (confirm('¿Deseas eliminar la asignación de este partido?')) {
        const horario = partidosHorarios[clavePartido];
        delete partidosHorarios[clavePartido];
        
        // Restaurar elemento visual
        elemento.classList.remove('reservado-torneo');
        elemento.classList.add('disponible');
        elemento.innerHTML = `
            <div class="hora-texto">${horario.horaInicio}</div>
            <div class="duracion-texto">1h</div>
        `;
        elemento.onclick = () => seleccionarHorarioPartido(elemento);
        
        actualizarPartidosProgramados();
        showNotification('Asignación eliminada', 'info');
    }
}

function actualizarPartidosProgramados() {
    const container = document.getElementById('partidosProgramados');
    if (!container) return;
    
    const partidosAsignados = Object.keys(partidosHorarios);
    
    if (partidosAsignados.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: #666; padding: 20px;">No hay partidos programados aún</p>';
        return;
    }
    
    let html = '<div style="display: grid; gap: 10px;">';
    
    partidosAsignados.forEach(partidoId => {
        const horario = partidosHorarios[partidoId];
        const fechaFormateada = new Date(horario.fecha).toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit'
        });
        
        // Obtener el número del partido correctamente
        const partidoInfo = horario.partidoId.split('-');
        const numeroPartido = partidoInfo[partidoInfo.length - 1];
        
        html += `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #e8f5e8; border-radius: 8px; border-left: 3px solid #28a745;">
                <div>
                    <div style="font-weight: 600; color: #2c3e50;">Partido ${numeroPartido}</div>
                    <div style="font-size: 0.9em; color: #666;">${fechaFormateada} • ${horario.horaInicio} • ${horario.areaNombre}</div>
                </div>
                <button onclick="eliminarAsignacionDirecta('${horario.partidoId}')" 
                        style="background: #dc3545; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer;">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function eliminarAsignacionDirecta(partidoId) {
    // Encontrar y eliminar la asignación
    const claveAEliminar = Object.keys(partidosHorarios).find(clave => 
        partidosHorarios[clave].partidoId === partidoId
    );
    
    if (claveAEliminar) {
        const partido = partidosHorarios[claveAEliminar];
        delete partidosHorarios[claveAEliminar];
        
        // Restaurar elemento visual
        const elemento = document.querySelector(
            `[data-fecha="${partido.fecha}"][data-hora-inicio="${partido.horaInicio}"][data-area-id="${partido.areaId}"]`
        );
        
        if (elemento) {
            elemento.classList.remove('reservado-torneo');
            elemento.classList.add('disponible');
            elemento.innerHTML = `
                <div class="hora-texto">${partido.horaInicio}</div>
                <div class="duracion-texto">1h</div>
            `;
            elemento.onclick = () => seleccionarHorarioPartido(elemento);
        }
        
        actualizarPartidosProgramados();
        showNotification('Asignación eliminada', 'info');
    }
}

function generarEstructuraPartidos(equipos, modalidad) {
    const partidos = [];
    let equiposRestantes = equipos;
    let ronda = 1;
    let partidoNumero = 1;
    
    const nombresFases = {
        16: 'Octavos de Final',
        8: 'Cuartos de Final',
        4: 'Semifinal',
        2: 'Final'
    };
    
    if (modalidad === 'eliminacion_simple') {
        while (equiposRestantes > 1) {
            const esPar = equiposRestantes % 2 === 0;
            const partidosRonda = Math.floor(equiposRestantes / 2);
            const nombreFase = ronda === 1 ? 'Primera Ronda' : 
                               (equiposRestantes === 2 ? 'Final' : 
                               nombresFases[equiposRestantes] || `Ronda ${ronda}`);
            
            // Registrar pases directos
            const paseDirecto = !esPar;
            
            for (let i = 0; i < partidosRonda; i++) {
                let equipo1, equipo2;
                
                if (ronda === 1) {
                    equipo1 = `Equipo ${(i * 2 + 1).toString().padStart(2, '0')}`;
                    equipo2 = `Equipo ${(i * 2 + 2).toString().padStart(2, '0')}`;
                } else {
                    // Usar referencias a ganadores de partidos anteriores
                    const offset = partidoNumero - partidosRonda;
                    equipo1 = `Ganador P${(i * 2 + 1 < partidoNumero) ? i * 2 + 1 : '?'}`;
                    equipo2 = `Ganador P${(i * 2 + 2 < partidoNumero) ? i * 2 + 2 : '?'}`;
                }
                
                partidos.push({
                    id: `ronda${ronda}-partido-${partidoNumero}`,
                    nombre: `Partido ${partidoNumero}`,
                    fase: nombreFase,
                    descripcion: `${nombreFase} - Partido ${partidoNumero}`,
                    ronda: ronda,
                    numeroPartido: partidoNumero,
                    equipo1: equipo1,
                    equipo2: equipo2,
                    tienePaseDirecto: false
                });
                
                partidoNumero++;
            }
            
            // Añadir información de pase directo si es necesario
            if (paseDirecto) {
                partidos.push({
                    id: `ronda${ronda}-pase-directo`,
                    nombre: `Pase Directo`,
                    fase: nombreFase,
                    descripcion: `${nombreFase} - Pase Directo`,
                    ronda: ronda,
                    numeroPartido: null,
                    equipo1: ronda === 1 ? `Equipo ${equiposRestantes}` : `Ganador P${partidoNumero-1}`,
                    equipo2: null,
                    tienePaseDirecto: true,
                    esPaseDirecto: true
                });
            }
            
            equiposRestantes = Math.ceil(equiposRestantes / 2);
            ronda++;
        }
    }
    
    return partidos;
}

function mostrarEstructuraPartidos(estructuraPartidos) {
    const container = document.getElementById('estructuraPartidos');
    if (!container) return;
    
    let html = '<div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px; border: 2px solid #ffc107;">';
    html += '<h6 style="color: #856404; margin: 0 0 15px 0;"><i class="fas fa-list-ol"></i> Estructura de Partidos</h6>';
    
    const partidosPorFase = {};
    let pasesPorFase = {};
    
    estructuraPartidos.forEach(partido => {
        if (!partidosPorFase[partido.fase]) {
            partidosPorFase[partido.fase] = [];
            pasesPorFase[partido.fase] = 0;
        }
        
        if (partido.esPaseDirecto) {
            pasesPorFase[partido.fase]++;
        } else {
            partidosPorFase[partido.fase].push(partido);
        }
    });
    
    Object.keys(partidosPorFase).forEach(fase => {
        html += `<div style="margin-bottom: 15px;">`;
        const numPartidos = partidosPorFase[fase].length;
        const numPases = pasesPorFase[fase];
        
        let subtitulo = `${numPartidos} partido${numPartidos !== 1 ? 's' : ''}`;
        if (numPases > 0) {
            subtitulo += ` + ${numPases} pase${numPases !== 1 ? 's' : ''} directo${numPases !== 1 ? 's' : ''}`;
        }
        
        html += `<div style="font-weight: 600; color: #2c3e50; margin-bottom: 8px;">${fase} <small>(${subtitulo})</small></div>`;
        html += `<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 8px;">`;
        
        partidosPorFase[fase].forEach(partido => {
            const asignado = partidosHorarios[partido.id];
            const claseEstado = asignado ? 'partido-asignado-estructura' : 'partido-pendiente-estructura';
            
            html += `
                <div class="${claseEstado}" style="padding: 8px; border-radius: 4px; font-size: 0.85em; text-align: center;">
                    <div style="font-weight: 600;">P${partido.numeroPartido}</div>
                    <div style="font-size: 0.8em;">${partido.equipo1} vs ${partido.equipo2}</div>
                    ${asignado ? `<div style="font-size: 0.75em; color: #28a745; margin-top: 3px;">✓ ${asignado.fecha} ${asignado.horaInicio}</div>` : ''}
                </div>
            `;
        });
        
        // Mostrar pases directos si existen
        if (numPases > 0) {
            html += `
                <div class="partido-pase-directo" style="padding: 8px; border-radius: 4px; font-size: 0.85em; text-align: center; background: #f8f9fa; border: 1px dashed #6c757d;">
                    <div style="font-weight: 600;">Pase Directo</div>
                    <div style="font-size: 0.8em;">Se determinará por sorteo</div>
                </div>
            `;
        }
        
        html += `</div></div>`;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function obtenerDiasPermitidos(horarioTorneo) {
    switch (horarioTorneo) {
        case 'fines_semana':
            return ['Sabado', 'Domingo'];
        case 'mananas':
        case 'tardes':
        default:
            return ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];
    }
}

// ==================== RESTO DE FUNCIONES EXISTENTES ====================
function generarBracketsEliminacion(equipos) {
    let html = '';
    let equiposRestantes = equipos;
    let ronda = 1;
    
    const nombresFases = {
        16: 'Octavos de Final',
        8: 'Cuartos de Final',
        4: 'Semifinal',
        2: 'Final'
    };
    
    html += '<div style="background: white; padding: 20px; border-radius: 8px; margin-top: 15px;">';
    html += '<h5 style="color: #2c5aa0; margin: 0 0 20px 0;"><i class="fas fa-sitemap"></i> Llaves del Torneo</h5>';
    
    // Seguir generando rondas hasta que quede 1 equipo (campeón)
    while (equiposRestantes > 1) {
        const esPar = equiposRestantes % 2 === 0;
        const partidos = Math.floor(equiposRestantes / 2);
        const hayImpar = !esPar;
        const nombreFase = ronda === 1 ? 'Primera Ronda' : 
                          (equiposRestantes === 2 ? 'Final' : 
                          nombresFases[equiposRestantes] || `Ronda ${ronda}`);
        
        html += `<div style="margin-bottom: 25px;">`;
        html += `<div style="margin-bottom: 15px; padding: 10px; background: linear-gradient(135deg, #2c5aa0, #4a7bc8); color: white; border-radius: 8px; text-align: center;">
                    <strong style="font-size: 1.1em;">${nombreFase}</strong>
                    <div style="font-size: 0.9em; opacity: 0.9; margin-top: 5px;">${partidos} partido${partidos !== 1 ? 's' : ''}${hayImpar ? ' + 1 pase directo' : ''}</div>
                 </div>`;
        html += `<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">`;
        
        // Generar partidos
        for (let i = 0; i < partidos; i++) {
            const equipo1 = ronda === 1 ? `Equipo ${(i * 2 + 1).toString().padStart(2, '0')}` : `Ganador P${i * 2 + 1}`;
            const equipo2 = ronda === 1 ? `Equipo ${(i * 2 + 2).toString().padStart(2, '0')}` : `Ganador P${i * 2 + 2}`;
            
            html += `
                <div style="background: #e3f2fd; padding: 15px; border-radius: 10px; text-align: center; border: 2px solid #2196f3; box-shadow: 0 2px 8px rgba(33, 150, 243, 0.1);">
                    <div style="font-weight: 700; color: #1976d2; margin-bottom: 10px; font-size: 1em;">
                        <i class="fas fa-trophy"></i> Partido ${i + 1}
                    </div>
                    <div style="background: white; padding: 8px; border-radius: 6px; margin-bottom: 8px; border-left: 3px solid #4caf50;">
                        <div style="font-weight: 600; color: #2e7d32; font-size: 0.9em;">${equipo1}</div>
                    </div>
                    <div style="margin: 8px 0; color: #2196f3; font-weight: bold; font-size: 1.2em;">⚔️ VS</div>
                    <div style="background: white; padding: 8px; border-radius: 6px; margin-bottom: 8px; border-left: 3px solid #ff9800;">
                        <div style="font-weight: 600; color: #ef6c00; font-size: 0.9em;">${equipo2}</div>
                    </div>
                    <div style="font-size: 0.8em; color: #666; margin-top: 10px;">
                        <i class="fas fa-clock"></i> Duración: 1 hora
                    </div>
                </div>
            `;
        }
        
        // Mostrar el pase directo si hay equipos impares
        if (hayImpar) {
            const equipoPase = ronda === 1 ? `Equipo ${equiposRestantes.toString().padStart(2, '0')}` : `Ganador P${partidos * 2 + (ronda > 2 ? 0 : 1)}`;
            
            html += `
                <div style="background: #e8f5e8; padding: 15px; border-radius: 10px; text-align: center; border: 2px solid #4caf50; box-shadow: 0 2px 8px rgba(76, 175, 80, 0.1);">
                    <div style="font-weight: 700; color: #2e7d32; margin-bottom: 10px; font-size: 1em;">
                        <i class="fas fa-medal"></i> Pase Directo
                    </div>
                    <div style="background: white; padding: 8px; border-radius: 6px; margin-bottom: 8px; border-left: 3px solid #4caf50;">
                        <div style="font-weight: 600; color: #2e7d32; font-size: 0.9em;">${equipoPase}</div>
                    </div>
                    <div style="margin: 8px 0; color: #4caf50; font-weight: bold; font-size: 0.9em;">Avanza automáticamente</div>
                    <div style="font-size: 0.8em; color: #666; margin-top: 10px;">
                        <i class="fas fa-info-circle"></i> Se determinará por sorteo
                    </div>
                </div>
            `;
        }
        
        html += '</div></div>';
        equiposRestantes = Math.ceil(equiposRestantes / 2);
        ronda++;
    }
    
    html += '</div>';
    return html;
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

// Agregar esta función a tu archivo crear_torneo.js (junto a las demás funciones de previsualización)
function mostrarHorariosRecomendados(fechaInicio, fechaFin, horarioTorneo, partidos, equipos, modalidad) {
    const container = document.getElementById('horariosRecomendados');
    if (!container) return;
    
    // Calcular días disponibles entre las fechas
    const inicio = new Date(fechaInicio);
    const fin = new Date(fechaFin);
    let diasTotales = Math.floor((fin - inicio) / (24 * 60 * 60 * 1000)) + 1;
    
    // Días efectivos según el horario del torneo
    let diasEfectivos = diasTotales;
    if (horarioTorneo === 'fines_semana') {
        diasEfectivos = Math.floor(diasTotales / 7) * 2 + Math.min(diasTotales % 7, 2);
    }
    
    // Calcular partidos por día (aproximado)
    const partidosPorDia = Math.ceil(partidos / diasEfectivos);
    const horasPorDia = horarioTorneo === 'mananas' ? 6 : horarioTorneo === 'tardes' ? 6 : 12;
    const partidosPosiblesPorDia = Math.floor(horasPorDia / 1); // Cada partido dura 1 hora
    
    let html = `
        <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px; border: 2px solid #17a2b8;">
            <h5 style="color: #17a2b8; margin: 0 0 15px 0;"><i class="fas fa-clock"></i> Horarios Recomendados</h5>
            
            <div style="margin-bottom: 15px; padding: 15px; background: #e3f2fd; border-radius: 8px; border-left: 4px solid #17a2b8;">
                <div style="margin-bottom: 10px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                    <div>
                        <strong>Días disponibles:</strong> ${diasEfectivos} días
                    </div>
                    <div>
                        <strong>Total partidos:</strong> ${partidos} partidos
                    </div>
                    <div>
                        <strong>Partidos por día:</strong> ~${partidosPorDia} partidos
                    </div>
                </div>
                
                <div style="font-size: 0.95em; color: #333;">
                    Para tu torneo de ${equipos} equipos en modalidad ${modalidadTexto(modalidad)}, 
                    recomendamos distribuir los ${partidos} partidos en ${diasEfectivos} días,
                    con aproximadamente ${partidosPorDia} partidos por día.
                </div>
            </div>
            
            <div style="padding: 15px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
                <div style="display: flex; align-items: center; gap: 10px; color: #856404;">
                    <i class="fas fa-lightbulb"></i>
                    <strong>Sugerencia:</strong> 
                    ${generarSugerenciaHorarios(horarioTorneo, partidosPorDia, partidosPosiblesPorDia)}
                </div>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
}

// Función auxiliar para mostrar texto de modalidad
function modalidadTexto(modalidad) {
    const modalidades = {
        'eliminacion_simple': 'Eliminación Simple',
        'eliminacion_doble': 'Eliminación Doble',
        'todos_contra_todos': 'Todos contra Todos',
        'grupos_eliminatoria': 'Grupos + Eliminatoria'
    };
    return modalidades[modalidad] || modalidad;
}

// Función auxiliar para generar sugerencias de horarios
function generarSugerenciaHorarios(horarioTorneo, partidosPorDia, partidosPosiblesPorDia) {
    if (partidosPorDia > partidosPosiblesPorDia) {
        return `Necesitarás más de un área deportiva para jugar ${partidosPorDia} partidos por día, 
                ya que en una sola área solo puedes jugar ${partidosPosiblesPorDia} partidos diarios 
                en horario de ${horarioTorneo === 'mananas' ? 'mañanas' : 
                               horarioTorneo === 'tardes' ? 'tardes' : 'fines de semana'}.`;
    } else {
        const horarioTexto = horarioTorneo === 'mananas' ? '6:00 AM a 12:00 PM' : 
                            horarioTorneo === 'tardes' ? '1:00 PM a 7:00 PM' : 
                            '9:00 AM a 9:00 PM (fines de semana)';
        
        return `Programar ${partidosPorDia} partidos diarios es factible en el horario seleccionado (${horarioTexto}). 
                Reserva las áreas con anticipación para asegurar disponibilidad.`;
    }
}

// Función para subir imagen a ImgBB
function subirImagen(input) {
    if (input.files && input.files[0]) {
        // Mostrar vista previa
        const previewImg = document.createElement('img');
        previewImg.style.maxWidth = '100%';
        previewImg.style.maxHeight = '200px';
        previewImg.style.borderRadius = '8px';
        
        const previewContainer = document.getElementById('previewImagen');
        previewContainer.innerHTML = '';
        previewContainer.appendChild(previewImg);
        
        const uploadStatus = document.getElementById('uploadStatus');
        const progressBar = document.getElementById('progressBar');
        const uploadText = document.getElementById('uploadText');
        
        // Mostrar estado de carga
        uploadStatus.style.display = 'block';
        progressBar.style.width = '0%';
        uploadText.innerText = 'Preparando imagen...';
        
        const file = input.files[0];
        
        // Verificar tamaño (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            showNotification('La imagen excede el tamaño máximo de 5MB', 'error');
            uploadStatus.style.display = 'none';
            previewContainer.innerHTML = `
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Arrastra una imagen o haz clic para seleccionar</p>
                <small>JPG, PNG, GIF (máx. 5MB)</small>
            `;
            return;
        }
        
        // Leer y mostrar vista previa
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            uploadText.innerText = 'Subiendo imagen...';
            progressBar.style.width = '30%';
            
            // Subir a ImgBB
            const base64Image = e.target.result.split(',')[1];
            
            const formData = new FormData();
            formData.append('key', IMGBB_API_KEY);
            formData.append('image', base64Image);
            
            fetch(IMGBB_API_URL, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                progressBar.style.width = '70%';
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    progressBar.style.width = '100%';
                    uploadText.innerHTML = `<span style="color: #28a745;">✓ Imagen subida correctamente</span>`;
                    
                    // Guardar URL en campo oculto
                    document.getElementById('imagenTorneoURL').value = data.data.url;
                    
                    setTimeout(() => {
                        progressBar.style.width = '100%';
                        progressBar.style.backgroundColor = '#28a745';
                    }, 300);
                    
                    showNotification('Imagen subida correctamente', 'success');
                } else {
                    uploadText.innerHTML = `<span style="color: #dc3545;">Error al subir imagen</span>`;
                    showNotification('Error al subir imagen: ' + (data.error?.message || 'Error desconocido'), 'error');
                }
            })
            .catch(error => {
                uploadText.innerHTML = `<span style="color: #dc3545;">Error de conexión</span>`;
                showNotification('Error de conexión: ' + error.message, 'error');
            });
        };
        
        reader.readAsDataURL(file);
    }
}

// Función para habilitar la edición de premios
function habilitarEdicionPremios() {
    const premiosTextarea = document.getElementById('premioDescripcion');
    
    if (premiosTextarea) {
        // Remover el atributo readonly
        premiosTextarea.removeAttribute('readonly');
        
        // Aplicar estilos para indicar que es editable
        premiosTextarea.style.border = '2px solid #17a2b8';
        premiosTextarea.style.backgroundColor = '#f8f9fa';
        
        // Mostrar notificación
        showNotification('Ya puedes editar la descripción de premios', 'info');
        
        // Poner foco en el textarea
        premiosTextarea.focus();
    }
}

// Función para guardar el torneo
async function guardarTorneo() {
    if (!validarPasoActual()) return;
    
    const form = document.getElementById('formCrearTorneo');
    const formData = new FormData(form);
    const imagenURL = document.getElementById('imagenTorneoURL').value;
    
    const torneoData = {
        nombre: formData.get('nombre'),
        descripcion: formData.get('descripcion'),
        deporte_id: parseInt(formData.get('deporte_id')),
        institucion_sede_id: parseInt(formData.get('institucion_sede_id')),
        max_equipos: parseInt(formData.get('max_equipos')),
        fecha_inicio: formData.get('fecha_inicio'),
        fecha_fin: formData.get('fecha_fin'),
        fecha_inscripcion_inicio: formData.get('fecha_inscripcion_inicio'),
        fecha_inscripcion_fin: formData.get('fecha_inscripcion_fin'),
        modalidad: formData.get('modalidad'),
        premio_1: formData.get('premio_1'),
        premio_2: formData.get('premio_2'),
        premio_3: formData.get('premio_3'),
        costo_inscripcion: parseFloat(formData.get('costo_inscripcion')) || 0,
        imagen_torneo: imagenURL || null,
        horario_torneo: formData.get('horario_torneo'),
        partidos_programados: Object.values(partidosHorarios)
    };
    torneoData.descripcion = torneoData.descripcion.replace(/[\uD800-\uDBFF][\uDC00-\uDFFF]/g, '');
    
    // Obtener fecha_fin directamente del campo (aunque esté disabled)
    const fechaFin = document.getElementById('fechaFin').value;
    if (!torneoData.fecha_fin && fechaFin) {
        torneoData.fecha_fin = fechaFin;
    }
    
    const btnGuardar = document.querySelector('.btn-finish');
    const textoOriginal = btnGuardar.innerHTML;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando torneo...';
    btnGuardar.disabled = true;
    
    try {
        const response = await fetch('../../Controllers/TorneosController.php?action=crear_torneo', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(torneoData)
        });
        
        // Capturar el texto completo de la respuesta
        const responseText = await response.text();
        console.log('Respuesta completa del servidor:', responseText);
        
        // Intentar parsear como JSON
        let result;
        try {
            result = JSON.parse(responseText);
            // Continuar con el procesamiento normal...
            if (result.success) {
                showNotification('¡Torneo creado exitosamente!', 'success');
                setTimeout(() => {
                    window.location.href = 'torneos.php';
                }, 2000);
            } else {
                showNotification('Error al crear torneo: ' + result.message, 'error');
                btnGuardar.innerHTML = textoOriginal;
                btnGuardar.disabled = false;
            }
        } catch (e) {
            console.error('No se pudo parsear como JSON:', e);
            showNotification('El servidor devolvió una respuesta no válida', 'error');
            btnGuardar.innerHTML = textoOriginal;
            btnGuardar.disabled = false;
        }
    } catch (error) {
        console.error('Error de conexión:', error);
        showNotification('Error de conexión al crear torneo', 'error');
        btnGuardar.innerHTML = textoOriginal;
        btnGuardar.disabled = false;
    }
}