// Variables globales
let pasoActual = 1;
const totalPasos = 4;

// Configuración de ImgBB API
const IMGBB_API_KEY = 'f94d58c09424ff225d85feee613de3a6';
const IMGBB_API_URL = 'https://api.imgbb.com/1/upload';

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
    document.getElementById(`paso${pasoActual}`).classList.remove('active');
    document.querySelector(`.step[data-step="${pasoActual}"]`).classList.remove('active');
    
    // Marcar como completado si vamos hacia adelante
    if (numeroPaso > pasoActual) {
        document.querySelector(`.step[data-step="${pasoActual}"]`).classList.add('completed');
    }
    
    // Mostrar nuevo paso
    pasoActual = numeroPaso;
    document.getElementById(`paso${pasoActual}`).classList.add('active');
    document.querySelector(`.step[data-step="${pasoActual}"]`).classList.add('active');
    
    // Scroll al top
    document.querySelector('.form-container').scrollIntoView({ behavior: 'smooth' });
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
        const valor = document.getElementById(campo.id).value.trim();
        if (!valor) {
            showNotification(campo.mensaje, 'error');
            document.getElementById(campo.id).focus();
            return false;
        }
    }
    return true;
}

function validarPaso2() {
    const maxEquipos = parseInt(document.getElementById('maxEquipos').value);
    const horario = document.getElementById('horarioTorneo').value;
    
    if (!maxEquipos || maxEquipos < 4) {
        showNotification('Mínimo 4 equipos para un torneo válido', 'error');
        document.getElementById('maxEquipos').focus();
        return false;
    }
    
    if (!horario) {
        showNotification('Por favor, selecciona un horario para el torneo', 'error');
        document.getElementById('horarioTorneo').focus();
        return false;
    }
    
    return true;
}

function validarPaso3() {
    const fechas = {
        inicio: document.getElementById('fechaInicio').value,
        inscripcionInicio: document.getElementById('fechaInscripcionInicio').value,
        inscripcionFin: document.getElementById('fechaInscripcionFin').value
    };
    
    // Validar campos requeridos
    if (!fechas.inicio) {
        showNotification('Por favor, selecciona la fecha de inicio del torneo', 'error');
        document.getElementById('fechaInicio').focus();
        return false;
    }
    
    if (!fechas.inscripcionInicio) {
        showNotification('Por favor, selecciona la fecha de inicio de inscripciones', 'error');
        document.getElementById('fechaInscripcionInicio').focus();
        return false;
    }
    
    if (!fechas.inscripcionFin) {
        showNotification('Por favor, selecciona la fecha de fin de inscripciones', 'error');
        document.getElementById('fechaInscripcionFin').focus();
        return false;
    }
    
    // Validar lógica de fechas
    const hoy = new Date().toISOString().split('T')[0];
    const inicio = new Date(fechas.inicio);
    const inscripcionInicio = new Date(fechas.inscripcionInicio);
    const inscripcionFin = new Date(fechas.inscripcionFin);
    
    if (fechas.inicio <= hoy) {
        showNotification('La fecha de inicio debe ser futura', 'error');
        return false;
    }
    
    if (inscripcionFin >= inicio) {
        showNotification('Las inscripciones deben cerrar antes del inicio del torneo', 'error');
        return false;
    }
    
    if (inscripcionInicio >= inscripcionFin) {
        showNotification('La fecha de inicio de inscripciones debe ser anterior al fin', 'error');
        return false;
    }
    
    return true;
}

function validarPaso4() {
    const premios = document.getElementById('premioDescripcion').value;
    
    if (!validarPremios(premios)) {
        showNotification('Por favor, completa la descripción de todos los premios (1er, 2do y 3er puesto)', 'error');
        document.getElementById('premioDescripcion').focus();
        return false;
    }
    
    return true;
}

// ==================== VALIDACIONES ESPECÍFICAS ====================
function validarEquipos() {
    const maxEquipos = parseInt(document.getElementById('maxEquipos').value) || 0;
    const btnSolicitud = document.getElementById('btnSolicitudIPD');
    const input = document.getElementById('maxEquipos');
    
    if (maxEquipos > 15) {
        input.value = 15;
        btnSolicitud.style.display = 'block';
        showNotification('Máximo 15 equipos para torneos privados. Para más equipos, solicita apoyo del IPD.', 'warning');
    } else if (maxEquipos < 4 && maxEquipos > 0) {
        input.value = 4;
        showNotification('Mínimo 4 equipos para un torneo válido', 'warning');
    } else {
        btnSolicitud.style.display = 'none';
    }
    
    calcularHorarios();
}

function validarPremios(premiosTexto) {
    if (!premiosTexto) return false;
    
    const patrones = [
        /🥇 1er puesto:\s*(.+)/,
        /🥈 2do puesto:\s*(.+)/,
        /🥉 3er puesto:\s*(.+)/
    ];
    
    return patrones.every(patron => {
        const match = premiosTexto.match(patron);
        return match && match[1].trim().length > 0;
    });
}

// ==================== CÁLCULO Y PREVISUALIZACIÓN ====================
function calcularHorarios() {
    const fechaInicio = document.getElementById('fechaInicio').value;
    const maxEquipos = parseInt(document.getElementById('maxEquipos').value) || 0;
    const modalidad = document.getElementById('modalidadTorneo').value;
    const horario = document.getElementById('horarioTorneo').value;
    
    if (!fechaInicio || !maxEquipos || !modalidad || !horario) {
        document.getElementById('previsualizacionHorarios').style.display = 'none';
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
    
    document.getElementById('fechaFin').value = fechaFin.toISOString().split('T')[0];
    
    // ✅ GENERAR PREVISUALIZACIÓN COMPLETA
    generarPrevisualizacion(maxEquipos, modalidad, fechaInicio, fechaFin.toISOString().split('T')[0], horario, partidosTotales, diasNecesarios);
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
    
    if (!container) return;
    
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
                <div style="font-size: 16px; font-weight: bold; color: #dc3545;">${fechaFin}</div>
                <div style="font-size: 12px; color: #666; font-weight: 600;">Fecha Fin</div>
            </div>
        </div>
    `;
    
    // ✅ GENERAR BRACKETS (esto estaba faltando)
    if (modalidad === 'eliminacion_simple') {
        bracketsContainer.innerHTML = generarBracketsEliminacion(equipos);
    }
    
    // Generar cronograma e información
    const cronograma = generarCronogramaConSorteos(fechaInicio, equipos, modalidad, horario);
    mostrarHorariosRecomendados(fechaInicio, fechaFin, horario, partidos, equipos, modalidad);
    
    container.style.display = 'block';
}

// ==================== GESTIÓN DE IMAGEN ====================
async function subirImagen(input) {
    const file = input.files[0];
    if (!file) return;
    
    if (!file.type.startsWith('image/')) {
        showNotification('Por favor selecciona un archivo de imagen válido', 'error');
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) {
        showNotification('La imagen es muy grande. Máximo 5MB', 'error');
        return;
    }
    
    const uploadStatus = document.getElementById('uploadStatus');
    const progressBar = document.getElementById('progressBar');
    const uploadText = document.getElementById('uploadText');
    const preview = document.getElementById('previewImagen');
    
    uploadStatus.style.display = 'block';
    uploadText.textContent = 'Subiendo imagen...';
    uploadText.style.color = '#2c5aa0';
    progressBar.style.width = '0%';
    
    try {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('key', IMGBB_API_KEY);
        
        // Progreso simulado
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += Math.random() * 30;
            if (progress > 90) progress = 90;
            progressBar.style.width = progress + '%';
        }, 200);
        
        const response = await fetch(IMGBB_API_URL, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        clearInterval(progressInterval);
        progressBar.style.width = '100%';
        
        if (result.success) {
            const imageUrl = result.data.url;
            document.getElementById('imagenTorneoURL').value = imageUrl;
            
            preview.innerHTML = `
                <img src="${imageUrl}" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                <p style="margin-top: 10px; font-weight: 600; color: #28a745;">Imagen cargada exitosamente</p>
            `;
            
            uploadText.textContent = '¡Imagen subida exitosamente!';
            uploadText.style.color = '#28a745';
            showNotification('Imagen subida correctamente', 'success');
            
            setTimeout(() => {
                uploadStatus.style.display = 'none';
            }, 2000);
        } else {
            throw new Error(result.error?.message || 'Error al subir imagen');
        }
    } catch (error) {
        console.error('Error subiendo imagen:', error);
        progressBar.style.width = '0%';
        uploadText.textContent = 'Error al subir imagen';
        uploadText.style.color = '#dc3545';
        document.getElementById('imagenTorneoURL').value = '';
        preview.innerHTML = `
            <i class="fas fa-cloud-upload-alt"></i>
            <p>Arrastra una imagen o haz clic para seleccionar</p>
            <small>JPG, PNG, GIF (máx. 5MB)</small>
        `;
        showNotification('Error al subir imagen: ' + error.message, 'error');
        
        setTimeout(() => {
            uploadStatus.style.display = 'none';
        }, 3000);
    }
}

// ==================== GESTIÓN DE PREMIOS ====================
function habilitarEdicionPremios() {
    const premiosField = document.getElementById('premioDescripcion');
    if (premiosField.readOnly) {
        premiosField.readOnly = false;
        premiosField.style.background = 'white';
        premiosField.style.border = '2px solid #28a745';
        premiosField.focus();
        
        const texto = premiosField.value;
        const posicionPrimerPremio = texto.indexOf('🥇 1er puesto: ') + 15;
        premiosField.setSelectionRange(posicionPrimerPremio, posicionPrimerPremio);
        
        showNotification('Ahora puedes editar la descripción de los premios', 'info');
    }
}

// ==================== GUARDAR TORNEO ====================
async function guardarTorneo() {
    if (!validarPasoActual()) {
        return;
    }
    
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
        premio_descripcion: formData.get('premio_descripcion'),
        costo_inscripcion: parseFloat(formData.get('costo_inscripcion')) || 0,
        imagen_torneo: imagenURL || null,
        horario_torneo: formData.get('horario_torneo')
    };
    
    const btnGuardar = document.querySelector('.btn-finish');
    const textoOriginal = btnGuardar.innerHTML;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando torneo...';
    btnGuardar.disabled = true;
    
    try {
        showNotification('Creando torneo...', 'info');
        
        const response = await fetch('../../Controllers/TorneosController.php?action=crear_torneo', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(torneoData)
        });
        
        const result = await response.json();
        
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
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error de conexión al crear torneo', 'error');
        btnGuardar.innerHTML = textoOriginal;
        btnGuardar.disabled = false;
    }
}

// ==================== BRACKETS Y CRONOGRAMA ====================
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
    
    while (equiposRestantes > 1) {
        const partidos = Math.floor(equiposRestantes / 2);
        const hayImpar = equiposRestantes % 2 !== 0;
        const nombreFase = ronda === 1 ? 'Primera Ronda' : (nombresFases[equiposRestantes] || `Ronda ${ronda}`);
        
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
        
        // Equipo con pase directo si hay número impar
        if (hayImpar) {
            const equipoDirecto = ronda === 1 ? 
                `Equipo ${equipos.toString().padStart(2, '0')}` : 
                'Ganador sorteo anterior';
            
            html += `
                <div style="background: #fff3cd; padding: 15px; border-radius: 10px; text-align: center; border: 2px solid #ffc107; box-shadow: 0 2px 8px rgba(255, 193, 7, 0.1);">
                    <div style="font-weight: 700; color: #856404; margin-bottom: 10px; font-size: 1em;">
                        <i class="fas fa-dice"></i> Pase Directo
                    </div>
                    <div style="background: white; padding: 12px; border-radius: 6px; margin-bottom: 10px; border-left: 3px solid #ffc107;">
                        <div style="font-weight: 600; color: #856404; font-size: 0.9em;">${equipoDirecto}</div>
                    </div>
                    <div style="margin: 8px 0; color: #ffc107; font-weight: bold; font-size: 1.2em;">🎯</div>
                    <div style="font-size: 0.8em; color: #666; margin-top: 10px;">
                        <i class="fas fa-random"></i> Avanza automáticamente
                    </div>
                </div>
            `;
        }
        
        html += '</div></div>';
        
        equiposRestantes = Math.ceil(equiposRestantes / 2);
        ronda++;
    }
    
    // Campeón
    html += `
        <div style="margin-top: 20px; padding: 15px; background: linear-gradient(135deg, #28a745, #20c997); color: white; border-radius: 8px; text-align: center;">
            <h6 style="margin: 0 0 10px 0; font-weight: 700;"><i class="fas fa-crown"></i> ¡Campeón del Torneo!</h6>
            <div style="font-size: 0.9em; opacity: 0.9;">El ganador de la final será coronado campeón</div>
        </div>
    `;
    
    html += '</div>';
    return html;
}

function generarCronogramaConSorteos(fechaInicio, equipos, modalidad, horario) {
    let html = '';
    let fechaActual = new Date(fechaInicio);
    let equiposRestantes = equipos;
    let fase = 1;
    
    const nombresFases = {
        16: 'Octavos de Final',
        8: 'Cuartos de Final', 
        4: 'Semifinal',
        2: 'Final'
    };
    
    // Buscar próxima fecha disponible
    function buscarProximaFechaDisponible(fecha, horario, esPrimeraFecha = false) {
        let nuevaFecha = new Date(fecha);
        
        if (horario === 'fines_semana') {
            if (!esPrimeraFecha) {
                do {
                    nuevaFecha.setDate(nuevaFecha.getDate() + 1);
                } while (nuevaFecha.getDay() !== 6 && nuevaFecha.getDay() !== 0);
            } else {
                while (nuevaFecha.getDay() !== 6 && nuevaFecha.getDay() !== 0) {
                    nuevaFecha.setDate(nuevaFecha.getDate() + 1);
                }
            }
        } else {
            if (!esPrimeraFecha) {
                nuevaFecha.setDate(nuevaFecha.getDate() + 1);
            }
        }
        return nuevaFecha;
    }
    
    fechaActual = buscarProximaFechaDisponible(fechaActual, horario, true);
    
    while (equiposRestantes > 1) {
        const partidos = Math.floor(equiposRestantes / 2);
        const hayImpar = equiposRestantes % 2 !== 0;
        const nombreFase = fase === 1 ? 'Primera Ronda' : (nombresFases[equiposRestantes] || `Ronda ${fase}`);
        
        if (fase > 1) {
            fechaActual = buscarProximaFechaDisponible(fechaActual, horario, false);
        }
        
        const fechaFormateada = fechaActual.toLocaleDateString('es-ES', {
            weekday: 'long',
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
        
        const fechaISO = fechaActual.toISOString().split('T')[0];
        const tiempoTotal = partidos * 1.0; // 1 hora por partido
        
        html += `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; margin-bottom: 10px; background: white; border-radius: 8px; border-left: 3px solid #2c5aa0; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 5px;">
                        <strong style="color: #2c3e50; font-size: 1.1em;">${nombreFase}</strong>
                        <button onclick="verificarAreasDisponibles('${nombreFase}', '${fechaISO}', ${partidos * 2})" 
                                style="background: #17a2b8; color: white; border: none; padding: 6px 12px; border-radius: 20px; font-size: 0.8em; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 5px;"
                                onmouseover="this.style.background='#138496'"
                                onmouseout="this.style.background='#17a2b8'">
                            <i class="fas fa-map-marked-alt"></i> Verificar Áreas
                        </button>
                    </div>
                    <div style="font-size: 0.85em; color: #666;">
                        ${partidos} partido${partidos !== 1 ? 's' : ''} × 1h
                        ${hayImpar ? ' + 🎲 Sorteo pase directo' : ''}
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-weight: 600; color: #495057; margin-bottom: 3px;">${fechaFormateada}</div>
                    <div style="font-size: 0.8em; color: #2c5aa0;">⏰ ${tiempoTotal}h total${hayImpar ? ' + sorteo' : ''}</div>
                </div>
            </div>
        `;
        
        equiposRestantes = Math.ceil(equiposRestantes / 2);
        fase++;
    }
    
    return html;
}

function mostrarHorariosRecomendados(fechaInicio, fechaFin, horario, partidos, equipos, modalidad) {
    const container = document.getElementById('horariosRecomendados');
    if (!container) return;
    
    const horarios = {
        'mananas': { rango: '8:00 AM - 12:00 PM', texto: 'Lunes a Domingo en horario matutino' },
        'tardes': { rango: '2:00 PM - 8:00 PM', texto: 'Lunes a Domingo en horario vespertino' },
        'fines_semana': { rango: '9:00 AM - 6:00 PM', texto: 'Solo Sábados y Domingos' }
    };
    
    const info = horarios[horario] || horarios['mananas'];
    
    container.innerHTML = `
        <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
            <h5 style="margin: 0 0 15px 0; color: #2c5aa0;"><i class="fas fa-clock"></i> Información del Torneo</h5>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div><strong>Período:</strong><br><span style="color: #666;">${fechaInicio} al ${fechaFin}</span></div>
                <div><strong>Horario:</strong><br><span style="color: #666;">${info.rango}</span></div>
                <div><strong>Días:</strong><br><span style="color: #666;">${info.texto}</span></div>
            </div>
        </div>
    `;
}

// ==================== VERIFICACIÓN DE ÁREAS ====================
async function verificarAreasDisponibles(fase, fecha, equipos) {
    try {
        const sedeId = document.getElementById('sedeTorneo').value;
        const deporteId = document.getElementById('deporteTorneo').value;
        
        if (!sedeId || !deporteId) {
            showNotification('Selecciona sede y deporte primero', 'warning');
            return;
        }
        
        const btnVerificar = document.querySelector(`[onclick*="verificarAreasDisponibles('${fase}'"]`);
        if (btnVerificar) {
            btnVerificar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
            btnVerificar.style.background = '#6c757d';
            btnVerificar.disabled = true;
        }
        
        const partidosNecesarios = Math.floor(equipos / 2);
        
        const requestData = {
            action: 'verificar_y_reservar_automatico',
            sede_id: parseInt(sedeId),
            deporte_id: parseInt(deporteId),
            fecha: fecha,
            partidos_necesarios: partidosNecesarios,
            fase: fase,
            horario_torneo: document.getElementById('horarioTorneo').value || 'mananas'
        };
        
        const response = await fetch('../../Models/AreasDeportivasModel.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        });
        
        const responseText = await response.text();
        let result;
        
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Error parseando JSON:', parseError);
            
            if (btnVerificar) {
                btnVerificar.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error servidor';
                btnVerificar.style.background = '#dc3545';
                btnVerificar.disabled = false;
            }
            
            showNotification('Error en el servidor. Revisa la consola.', 'error');
            return;
        }
        
        if (result.success && result.reservas_realizadas > 0) {
            btnVerificar.innerHTML = `<i class="fas fa-check-circle"></i> ${result.reservas_realizadas} área(s) disponible(s)`;
            btnVerificar.style.background = '#28a745';
            btnVerificar.style.color = 'white';
            btnVerificar.disabled = false;
            
            showNotification(`✅ ${result.reservas_realizadas} área(s) disponible(s) para ${fase}`, 'success');
            
            setTimeout(() => {
                mostrarAreasDisponibles(fase, result.reservas_detalle, result.simulado || false);
            }, 1000);
        } else {
            btnVerificar.innerHTML = '<i class="fas fa-times-circle"></i> Sin disponibilidad';
            btnVerificar.style.background = '#dc3545';
            btnVerificar.style.color = 'white';
            btnVerificar.disabled = false;
            
            showNotification(`❌ ${result.message || 'Sin disponibilidad'}`, 'error');
        }
        
    } catch (error) {
        console.error('Error:', error);
        
        const btnVerificar = document.querySelector(`[onclick*="verificarAreasDisponibles('${fase}'"]`);
        if (btnVerificar) {
            btnVerificar.innerHTML = '<i class="fas fa-wifi"></i> Error conexión';
            btnVerificar.style.background = '#6c757d';
            btnVerificar.disabled = false;
        }
        
        showNotification('Error de conexión: ' + error.message, 'error');
    }
}

function mostrarAreasDisponibles(fase, areasDetalle, esSimulacion = true) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.style.cssText = `
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.7); display: flex; align-items: center;
        justify-content: center; z-index: 1001; backdrop-filter: blur(5px);
    `;
    
    const tituloModal = esSimulacion ? 'Áreas Disponibles' : 'Reservas Confirmadas';
    const colorTema = esSimulacion ? '#17a2b8' : '#28a745';
    const iconoTema = esSimulacion ? 'fa-search' : 'fa-check-circle';
    
    modal.innerHTML = `
        <div style="background: white; border-radius: 12px; width: 90%; max-width: 700px; max-height: 80vh; overflow-y: auto;">
            <div style="padding: 25px; border-bottom: 2px solid #f1f3f5; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, ${colorTema}, #20c997); color: white; border-radius: 12px 12px 0 0;">
                <h3 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                    <i class="fas ${iconoTema}"></i> ${tituloModal} - ${fase}
                </h3>
                <button onclick="this.closest('.modal-overlay').remove()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: white;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div style="padding: 25px;">
                <div style="margin-bottom: 20px; padding: 15px; background: #e3f2fd; border-radius: 8px; border-left: 4px solid ${colorTema};">
                    <h4 style="margin: 0 0 10px 0; color: ${colorTema};">
                        <i class="fas fa-info-circle"></i> ${esSimulacion ? 'Horarios Disponibles' : 'Horarios Reservados'}
                    </h4>
                    <p style="margin: 0; color: ${colorTema};">
                        Se ${esSimulacion ? 'encontraron' : 'reservaron'} <strong>${areasDetalle.length} área(s)</strong> para los partidos de <strong>${fase}</strong>
                        <br><small>Duración por partido: <strong>1h</strong></small>
                    </p>
                </div>
                
                <div style="display: grid; gap: 15px;">
                    ${areasDetalle.map((area, index) => `
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 3px solid ${colorTema};">
                            <div>
                                <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">
                                    <i class="fas fa-map-marked-alt"></i> ${area.nombre_area}
                                </div>
                                <div style="font-size: 0.9em; color: #666; display: flex; align-items: center; gap: 15px;">
                                    <span><i class="fas fa-clock"></i> ${area.hora_inicio} - ${area.hora_fin}</span>
                                    <span><i class="fas fa-stopwatch"></i> 1h</span>
                                    <span><i class="fas fa-users"></i> Partido ${index + 1}</span>
                                    <span><i class="fas fa-dollar-sign"></i> S/. ${area.tarifa_por_hora}/hora</span>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="background: ${colorTema}; color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.8em; font-weight: 600;">
                                    ${esSimulacion ? 'DISPONIBLE' : 'RESERVADO'}
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
                
                <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
                    <div style="display: flex; align-items: center; gap: 10px; color: #856404;">
                        <i class="fas fa-lightbulb"></i>
                        <strong>Nota:</strong> ${esSimulacion ? 
                            'Estos horarios están disponibles. Duración: 1 hora por partido (40 min de juego + tiempo adicional).' : 
                            'Las reservas se han confirmado automáticamente para el torneo.'}
                    </div>
                </div>
            </div>
            
            <div style="padding: 20px 25px; border-top: 1px solid #f1f3f5; display: flex; justify-content: center; background: #f8f9fa; border-radius: 0 0 12px 12px;">
                <button onclick="this.closest('.modal-overlay').remove()" 
                        style="background: ${colorTema}; color: white; padding: 12px 30px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-check"></i> Entendido
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// ==================== SELECCIÓN DE HORARIOS PARA PARTIDOS ====================
let partidosHorarios = {}; // Almacenar horarios seleccionados por partido
let calendarioGenerado = false;

async function generarCalendarioReservas() {
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;
    const sedeId = document.getElementById('sedeTorneo').value;
    const deporteId = document.getElementById('deporteTorneo').value;
    const horarioTorneo = document.getElementById('horarioTorneo').value;
    const maxEquipos = parseInt(document.getElementById('maxEquipos').value) || 0;
    const modalidad = document.getElementById('modalidadTorneo').value;

    if (!fechaInicio || !fechaFin || !sedeId || !deporteId || !horarioTorneo || !maxEquipos) {
        return;
    }

    const partidosTotales = calcularPartidos(maxEquipos, modalidad);
    
    // Generar estructura de partidos para reserva
    const estructuraPartidos = generarEstructuraPartidos(maxEquipos, modalidad);
    
    const container = document.getElementById('calendarioHorarios');
    if (!container) {
        // Crear el contenedor si no existe
        const previsualizacion = document.getElementById('previsualizacionHorarios');
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
        await cargarHorariosDisponibles(fechaInicio, fechaFin, sedeId, deporteId, horarioTorneo, estructuraPartidos);
        calendarioGenerado = true;
    } catch (error) {
        console.error('Error generando calendario:', error);
        showNotification('Error al generar calendario de horarios', 'error');
    }
}

async function cargarHorariosDisponibles(fechaInicio, fechaFin, sedeId, deporteId, horarioTorneo, estructuraPartidos) {
    const content = document.getElementById('calendarioContent');
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
                        <button onclick="cargarHorariosDia('${fechaStr}', ${sedeId}, ${deporteId}, '${horarioTorneo}')" 
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
    mostrarEstructuraPartidos(estructuraPartidos);
}

async function cargarHorariosDia(fecha, sedeId, deporteId, horarioTorneo) {
    const container = document.getElementById(`horarios-${fecha}`);
    const btn = document.querySelector(`[onclick*="cargarHorariosDia('${fecha}'"]`);
    
    if (container.style.display === 'block') {
        container.style.display = 'none';
        btn.innerHTML = '<i class="fas fa-clock"></i> Ver Horarios';
        return;
    }
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
    
    try {
        // Obtener áreas deportivas de la sede
        const response = await fetch(`../../Controllers/AreasDeportivasController.php?action=obtener_areas_por_sede&sede_id=${sedeId}&deporte_id=${deporteId}`);
        const result = await response.json();
        
        if (result.success && result.areas.length > 0) {
            let html = '';
            
            for (const area of result.areas) {
                const horariosArea = await obtenerHorariosArea(area.id, fecha);
                html += generarBloqueHorariosArea(area, horariosArea, fecha);
            }
            
            container.innerHTML = html;
            container.style.display = 'block';
            btn.innerHTML = '<i class="fas fa-eye-slash"></i> Ocultar';
        } else {
            container.innerHTML = '<p style="text-align: center; color: #666; padding: 20px;">No hay áreas disponibles para este deporte</p>';
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

function generarBloqueHorariosArea(area, cronograma, fecha) {
    let html = `
        <div class="area-horarios">
            <div class="area-info">
                <strong>${area.nombre_area}</strong>
                <span class="tarifa">S/. ${area.tarifa_por_hora}/hora</span>
            </div>
            <div class="horarios-grid">
    `;
    
    cronograma.forEach(bloque => {
        const disponible = bloque.disponible;
        const claseEstado = disponible ? 'disponible' : 'ocupado';
        const horaCompleta = `${fecha} ${bloque.hora_inicio}-${bloque.hora_fin}`;
        
        html += `
            <div class="horario-bloque-torneo ${claseEstado}" 
                 data-fecha="${fecha}" 
                 data-hora-inicio="${bloque.hora_inicio}" 
                 data-hora-fin="${bloque.hora_fin}"
                 data-area-id="${area.id}"
                 data-area-nombre="${area.nombre_area}"
                 data-tarifa="${area.tarifa_por_hora}"
                 onclick="${disponible ? `seleccionarHorarioPartido(this)` : ''}">
                <div class="hora-texto">${bloque.hora_inicio}</div>
                <div class="duracion-texto">1h</div>
                ${!disponible ? '<div class="ocupado-texto">Ocupado</div>' : ''}
            </div>
        `;
    });
    
    html += `
            </div>
        </div>
    `;
    
    return html;
}

function seleccionarHorarioPartido(elemento) {
    const fecha = elemento.dataset.fecha;
    const horaInicio = elemento.dataset.horaInicio;
    const horaFin = elemento.dataset.horaFin;
    const areaId = elemento.dataset.areaId;
    const areaNombre = elemento.dataset.areaNombre;
    const tarifa = elemento.dataset.tarifa;
    
    // Mostrar modal para asignar partido
    mostrarModalAsignarPartido(fecha, horaInicio, horaFin, areaId, areaNombre, tarifa, elemento);
}

function mostrarModalAsignarPartido(fecha, horaInicio, horaFin, areaId, areaNombre, tarifa, elemento) {
    const maxEquipos = parseInt(document.getElementById('maxEquipos').value) || 0;
    const modalidad = document.getElementById('modalidadTorneo').value;
    const estructuraPartidos = generarEstructuraPartidos(maxEquipos, modalidad);
    
    // Obtener partidos disponibles (sin horario asignado)
    const partidosDisponibles = estructuraPartidos.filter(partido => !partidosHorarios[partido.id]);
    
    if (partidosDisponibles.length === 0) {
        showNotification('Todos los partidos ya tienen horario asignado', 'warning');
        return;
    }
    
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.style.cssText = `
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.7); display: flex; align-items: center;
        justify-content: center; z-index: 1001; backdrop-filter: blur(5px);
    `;
    
    const fechaFormateada = new Date(fecha).toLocaleDateString('es-ES', {
        weekday: 'long',
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
    
    modal.innerHTML = `
        <div style="background: white; border-radius: 12px; width: 90%; max-width: 600px; max-height: 80vh; overflow-y: auto;">
            <div style="padding: 25px; border-bottom: 2px solid #f1f3f5; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #17a2b8, #20c997); color: white; border-radius: 12px 12px 0 0;">
                <h3 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-calendar-plus"></i> Asignar Partido
                </h3>
                <button onclick="this.closest('.modal-overlay').remove()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: white;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div style="padding: 25px;">
                <div style="margin-bottom: 20px; padding: 15px; background: #e3f2fd; border-radius: 8px; border-left: 4px solid #17a2b8;">
                    <h4 style="margin: 0 0 10px 0; color: #17a2b8;">
                        <i class="fas fa-info-circle"></i> Horario Seleccionado
                    </h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; color: #17a2b8;">
                        <div><strong>Fecha:</strong> ${fechaFormateada}</div>
                        <div><strong>Hora:</strong> ${horaInicio} - ${horaFin}</div>
                        <div><strong>Área:</strong> ${areaNombre}</div>
                        <div><strong>Costo:</strong> S/. ${tarifa}/hora</div>
                    </div>
                </div>
                
                <div>
                    <h4 style="margin: 0 0 15px 0; color: #2c3e50;">Seleccionar Partido:</h4>
                    <div style="display: grid; gap: 10px; max-height: 300px; overflow-y: auto;">
                        ${partidosDisponibles.map(partido => `
                            <div class="partido-opcion" onclick="asignarHorarioAPartido('${partido.id}', '${fecha}', '${horaInicio}', '${horaFin}', '${areaId}', '${areaNombre}', '${tarifa}', this.closest('.modal-overlay'))"
                                 style="padding: 15px; border: 2px solid #e9ecef; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; background: white;">
                                <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">
                                    ${partido.fase} - Partido ${partido.numero}
                                </div>
                                <div style="color: #666; font-size: 0.9em;">
                                    ${partido.equipo1} vs ${partido.equipo2}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Agregar estilos hover a las opciones
    modal.querySelectorAll('.partido-opcion').forEach(opcion => {
        opcion.addEventListener('mouseover', function() {
            this.style.borderColor = '#17a2b8';
            this.style.background = '#f8f9fa';
        });
        opcion.addEventListener('mouseout', function() {
            this.style.borderColor = '#e9ecef';
            this.style.background = 'white';
        });
    });
}

function asignarHorarioAPartido(partidoId, fecha, horaInicio, horaFin, areaId, areaNombre, tarifa, modal) {
    // Guardar asignación
    partidosHorarios[partidoId] = {
        fecha,
        horaInicio,
        horaFin,
        areaId,
        areaNombre,
        tarifa
    };
    
    // Marcar el bloque como reservado
    const elemento = document.querySelector(`[data-fecha="${fecha}"][data-hora-inicio="${horaInicio}"][data-area-id="${areaId}"]`);
    if (elemento) {
        elemento.classList.remove('disponible');
        elemento.classList.add('reservado-torneo');
        elemento.innerHTML = `
            <div class="hora-texto">${horaInicio}</div>
            <div class="partido-asignado">Partido ${partidoId.split('-')[1]}</div>
        `;
        elemento.onclick = () => eliminarAsignacionPartido(partidoId, elemento);
    }
    
    // Actualizar lista de partidos programados
    actualizarPartidosProgramados();
    
    // Cerrar modal
    modal.remove();
    
    showNotification(`Partido asignado para ${fecha} a las ${horaInicio}`, 'success');
}

function eliminarAsignacionPartido(partidoId, elemento) {
    if (confirm('¿Deseas eliminar la asignación de este partido?')) {
        delete partidosHorarios[partidoId];
        
        // Restaurar bloque como disponible
        elemento.classList.remove('reservado-torneo');
        elemento.classList.add('disponible');
        
        const horaInicio = elemento.dataset.horaInicio;
        elemento.innerHTML = `
            <div class="hora-texto">${horaInicio}</div>
            <div class="duracion-texto">1h</div>
        `;
        elemento.onclick = () => seleccionarHorarioPartido(elemento);
        
        actualizarPartidosProgramados();
        showNotification('Asignación de partido eliminada', 'info');
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
        
        html += `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #e8f5e8; border-radius: 8px; border-left: 3px solid #28a745;">
                <div>
                    <div style="font-weight: 600; color: #2c3e50;">Partido ${partidoId.split('-')[1]} - ${partidoId.split('-')[0]}</div>
                    <div style="font-size: 0.9em; color: #666;">${fechaFormateada} • ${horario.horaInicio} • ${horario.areaNombre}</div>
                </div>
                <button onclick="eliminarAsignacionDirecta('${partidoId}')" 
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
    if (confirm('¿Deseas eliminar la asignación de este partido?')) {
        const horario = partidosHorarios[partidoId];
        delete partidosHorarios[partidoId];
        
        // Restaurar bloque visual
        const elemento = document.querySelector(`[data-fecha="${horario.fecha}"][data-hora-inicio="${horario.horaInicio}"][data-area-id="${horario.areaId}"]`);
        if (elemento) {
            elemento.classList.remove('reservado-torneo');
            elemento.classList.add('disponible');
            elemento.innerHTML = `
                <div class="hora-texto">${horario.horaInicio}</div>
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
            const partidosRonda = Math.floor(equiposRestantes / 2);
            const nombreFase = ronda === 1 ? 'Primera Ronda' : (nombresFases[equiposRestantes] || `Ronda ${ronda}`);
            
            for (let i = 0; i < partidosRonda; i++) {
                const equipo1 = ronda === 1 ? `Equipo ${(i * 2 + 1).toString().padStart(2, '0')}` : `Ganador P${i * 2 + 1}`;
                const equipo2 = ronda === 1 ? `Equipo ${(i * 2 + 2).toString().padStart(2, '0')}` : `Ganador P${i * 2 + 2}`;
                
                partidos.push({
                    id: `${nombreFase.replace(/\s+/g, '')}-${partidoNumero}`,
                    fase: nombreFase,
                    numero: partidoNumero,
                    equipo1,
                    equipo2,
                    ronda
                });
                partidoNumero++;
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
    estructuraPartidos.forEach(partido => {
        if (!partidosPorFase[partido.fase]) {
            partidosPorFase[partido.fase] = [];
        }
        partidosPorFase[partido.fase].push(partido);
    });
    
    Object.keys(partidosPorFase).forEach(fase => {
        html += `<div style="margin-bottom: 15px;">`;
        html += `<div style="font-weight: 600; color: #2c3e50; margin-bottom: 8px;">${fase}</div>`;
        html += `<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 8px;">`;
        
        partidosPorFase[fase].forEach(partido => {
            const asignado = partidosHorarios[partido.id];
            const claseEstado = asignado ? 'partido-asignado-estructura' : 'partido-pendiente-estructura';
            
            html += `
                <div class="${claseEstado}" style="padding: 8px; border-radius: 4px; font-size: 0.85em; text-align: center;">
                    <div style="font-weight: 600;">P${partido.numero}</div>
                    <div style="font-size: 0.8em;">${partido.equipo1} vs ${partido.equipo2}</div>
                    ${asignado ? `<div style="font-size: 0.75em; color: #28a745; margin-top: 3px;">✓ ${asignado.fecha} ${asignado.horaInicio}</div>` : ''}
                </div>
            `;
        });
        
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

// Modificar la función calcularHorarios existente para incluir el calendario
const calcularHorariosOriginal = calcularHorarios;
calcularHorarios = function() {
    calcularHorariosOriginal();
    
    // Agregar calendario después de la previsualización
    setTimeout(() => {
        if (document.getElementById('previsualizacionHorarios') && document.getElementById('previsualizacionHorarios').style.display !== 'none') {
            if (!calendarioGenerado) {
                generarCalendarioReservas();
            }
        }
    }, 500);
};

// Modificar la función de validación del paso 3 para incluir horarios
const validarPaso3Original = validarPaso3;
validarPaso3 = function() {
    const validacionBasica = validarPaso3Original();
    if (!validacionBasica) return false;
    
    // Validar que se hayan asignado horarios a todos los partidos
    const maxEquipos = parseInt(document.getElementById('maxEquipos').value) || 0;
    const modalidad = document.getElementById('modalidadTorneo').value;
    const estructuraPartidos = generarEstructuraPartidos(maxEquipos, modalidad);
    
    const partidosAsignados = Object.keys(partidosHorarios).length;
    const totalPartidos = estructuraPartidos.length;
    
    if (partidosAsignados < totalPartidos) {
        showNotification(`Faltan asignar horarios a ${totalPartidos - partidosAsignados} partido(s)`, 'error');
        return false;
    }
    
    return true;
};