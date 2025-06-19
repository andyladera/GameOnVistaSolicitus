<?php
session_start();

// Verificar si el usuario está autenticado como institución deportiva
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'instalacion') {
    header("Location: ../Auth/login.php");
    exit();
}

// Usar controladores
require_once '../../Controllers/InsDeporController.php';

$insDeporController = new InsDeporController();
$usuarioInstalacionId = $_SESSION['user_id'];

// Obtener información del usuario para mostrar torneos relevantes
$misInstalaciones = $insDeporController->getInstalacionesPorUsuario($usuarioInstalacionId);

include_once 'header.php';
?>

<link rel="stylesheet" href="../../Public/cssInsDepor/crear_torneo.css">

<div class="crear-torneo-container">
    <!-- Header -->
    <div class="crear-torneo-header">
        <div class="header-content">
            <div class="breadcrumb">
                <a href="torneos.php" class="breadcrumb-link">
                    <i class="fas fa-trophy"></i> Torneos
                </a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">Crear Nuevo Torneo</span>
            </div>
            <h1><i class="fas fa-plus-circle"></i> Crear Nuevo Torneo</h1>
            <p>Complete la información para organizar un nuevo torneo deportivo</p>
        </div>
        <div class="header-actions">
            <a href="torneos.php" class="btn-secondary-crear">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Formulario de creación -->
    <div class="form-container">
        <form id="formCrearTorneo" class="torneo-form">
            <!-- Paso 1: Información Básica -->
            <div class="form-section active" id="paso1">
                <div class="section-header">
                    <p>...</p>
                    <p>...</p>
                    <p>...</p>
                    <h3><i class="fas fa-info-circle"></i> Información Básica</h3>
                    <p>Datos principales del torneo</p>
                </div>
                
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="nombreTorneo">
                            <i class="fas fa-trophy"></i> Nombre del Torneo
                        </label>
                        <input type="text" id="nombreTorneo" name="nombre" required 
                               placeholder="Ej: Copa de Fútbol Verano 2025">
                    </div>
                    
                    <div class="form-group">
                        <label for="deporteTorneo">
                            <i class="fas fa-futbol"></i> Deporte
                        </label>
                        <select id="deporteTorneo" name="deporte_id" required>
                            <option value="">Seleccionar deporte</option>
                            <option value="1">Fútbol</option>
                            <option value="2">Vóley</option>
                            <option value="3">Básquet</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="sedeTorneo">
                            <i class="fas fa-map-marker-alt"></i> Sede del Torneo
                        </label>
                        <select id="sedeTorneo" name="institucion_sede_id" required>
                            <option value="">Seleccionar sede</option>
                            <?php foreach ($misInstalaciones as $instalacion): ?>
                                <option value="<?= $instalacion['id'] ?>"><?= htmlspecialchars($instalacion['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="descripcionTorneo">
                            <i class="fas fa-align-left"></i> Descripción
                        </label>
                        <textarea id="descripcionTorneo" name="descripcion" rows="3" 
                                  placeholder="Describe el torneo, reglas especiales, etc."></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-next" onclick="siguientePaso(2)">
                        Siguiente <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Paso 2: Configuración -->
            <div class="form-section" id="paso2">
                <div class="section-header">
                    <p>...</p>
                    <p>...</p>
                    <p>...</p>
                    <h3><i class="fas fa-cogs"></i> Configuración del Torneo</h3>
                    <p>Modalidad, equipos y horarios</p>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="modalidadTorneo">
                            <i class="fas fa-sitemap"></i> Modalidad
                        </label>
                        <select id="modalidadTorneo" name="modalidad" onchange="calcularHorarios()">
                            <option value="eliminacion_simple">Eliminación Simple</option>
                            <option value="eliminacion_doble" disabled>Eliminación Doble (Próximamente)</option>
                            <option value="todos_contra_todos" disabled>Todos vs Todos (Próximamente)</option>
                            <option value="grupos_eliminatoria" disabled>Grupos + Eliminatoria (Próximamente)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="maxEquipos">
                            <i class="fas fa-users"></i> Máximo de Equipos
                        </label>
                        <input type="number" id="maxEquipos" name="max_equipos" value="10" min="4" max="15" 
                               onchange="validarEquipos(); calcularHorarios()">
                        <small class="form-hint">Recomendado: 10 equipos</small>
                        <button type="button" id="btnSolicitudIPD" onclick="solicitarIPD()" 
                                style="display: none;" class="btn-warning-small">
                            + de 15 equipos (Solicitar IPD)
                        </button>
                    </div>
                    
                    <div class="form-group">
                        <label for="costoInscripcion">
                            <i class="fas fa-dollar-sign"></i> Costo de Inscripción
                        </label>
                        <input type="number" id="costoInscripcion" name="costo_inscripcion" value="0" min="0" step="0.01">
                        <small class="form-hint">En soles (S/.)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="horarioTorneo">
                            <i class="fas fa-clock"></i> Horarios del Torneo
                        </label>
                        <select id="horarioTorneo" name="horario_torneo" required onchange="calcularHorarios()">
                            <option value="">Seleccionar horario</option>
                            <option value="mananas">Mañanas (Lunes a Domingo)</option>
                            <option value="tardes">Tardes (Lunes a Domingo)</option>
                            <option value="fines_semana">Solo Fines de Semana</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-prev" onclick="anteriorPaso(1)">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                    <button type="button" class="btn-next" onclick="siguientePaso(3)">
                        Siguiente <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Paso 3: Fechas -->
            <div class="form-section" id="paso3">
                <div class="section-header">
                    <p>...</p>
                    <p>...</p>
                    <p>...</p>
                    <h3><i class="fas fa-calendar-alt"></i> Fechas del Torneo</h3>
                    <p>Programación temporal del evento</p>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="fechaInicio">
                            <i class="fas fa-play-circle"></i> Fecha de Inicio
                        </label>
                        <input type="date" id="fechaInicio" name="fecha_inicio" required onchange="calcularHorarios()">
                    </div>
                    
                    <div class="form-group">
                        <label for="fechaFin">
                            <i class="fas fa-stop-circle"></i> Fecha de Fin
                        </label>
                        <input type="date" id="fechaFin" name="fecha_fin" disabled>
                        <small class="form-hint">Se calcula automáticamente</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="fechaInscripcionInicio">
                            <i class="fas fa-calendar-plus"></i> Inicio de Inscripciones
                        </label>
                        <input type="date" id="fechaInscripcionInicio" name="fecha_inscripcion_inicio" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="fechaInscripcionFin">
                            <i class="fas fa-calendar-times"></i> Fin de Inscripciones
                        </label>
                        <input type="date" id="fechaInscripcionFin" name="fecha_inscripcion_fin" required>
                    </div>
                </div>
                <div id="previsualizacionHorarios" class="preview-section" style="display: none;">
                    <h4><i class="fas fa-calendar-check"></i> Previsualización del Torneo</h4>
                    <div id="infoTorneo"></div>
                    <div id="bracketsPreview"></div>
                    <div id="horariosRecomendados"></div>
                    <!-- ✅ NUEVO: Contenedor para estructura de partidos -->
                    <div id="estructuraPartidos"></div>
                    <!-- El calendario se creará dinámicamente aquí -->
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-prev" onclick="anteriorPaso(2)">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                    <button type="button" class="btn-next" onclick="siguientePaso(4)">
                        Siguiente <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Paso 4: Imagen y Premios -->
            <div class="form-section" id="paso4">
                <div class="section-header">
                    <p>...</p>
                    <p>...</p>
                    <p>...</p>
                    <h3><i class="fas fa-image"></i> Imagen y Premios</h3>
                    <p>Personalización visual y recompensas</p>
                </div>
                
                <div class="form-grid">
                    <!-- Subida de imagen -->
                    <div class="form-group">
                        <label for="imagenTorneo">
                            <i class="fas fa-camera"></i> Imagen del Torneo
                        </label>
                        <div class="upload-area">
                            <input type="file" id="imagenTorneo" name="imagen_torneo" accept="image/*" onchange="subirImagen(this)">
                            <div class="upload-preview" id="previewImagen">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Arrastra una imagen o haz clic para seleccionar</p>
                                <small>JPG, PNG, GIF (máx. 5MB)</small>
                            </div>
                        </div>
                        <div id="uploadStatus" style="display: none;">
                            <div class="upload-progress">
                                <div id="progressBar"></div>
                            </div>
                            <small id="uploadText"></small>
                        </div>
                        <input type="hidden" id="imagenTorneoURL" name="imagen_torneo_url">
                    </div>
                    
                    <!-- Premios -->
                    <div class="form-group">
                        <label>
                            <i class="fas fa-medal"></i> Descripción de Premios
                        </label>
                        <div class="premios-container">
                            <div class="premio-item">
                                <label for="premio1">
                                    <i class="fas fa-trophy" style="color: gold;"></i> 1er Puesto
                                </label>
                                <input type="text" id="premio1" name="premio_1" required placeholder="Ej: Trofeo, medallas y S/. 1000">
                            </div>
                            <div class="premio-item">
                                <label for="premio2">
                                    <i class="fas fa-trophy" style="color: silver;"></i> 2do Puesto
                                </label>
                                <input type="text" id="premio2" name="premio_2" required placeholder="Ej: Medallas y S/. 500">
                            </div>
                            <div class="premio-item">
                                <label for="premio3">
                                    <i class="fas fa-trophy" style="color: #cd7f32;"></i> 3er Puesto
                                </label>
                                <input type="text" id="premio3" name="premio_3" required placeholder="Ej: Medallas y S/. 250">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-prev" onclick="anteriorPaso(3)">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                    <button type="button" class="btn-finish" onclick="guardarTorneo()">
                        <i class="fas fa-save"></i> Crear Torneo
                    </button>
                </div>
            </div>
        </form>

        <!-- Indicador de pasos -->
        <div class="steps-indicator">
            <div class="step active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-label">Información</div>
            </div>
            <div class="step" data-step="2">
                <div class="step-number">2</div>
                <div class="step-label">Configuración</div>
            </div>
            <div class="step" data-step="3">
                <div class="step-number">3</div>
                <div class="step-label">Fechas</div>
            </div>
            <div class="step" data-step="4">
                <div class="step-number">4</div>
                <div class="step-label">Finalizar</div>
            </div>
        </div>
    </div>
</div>

<script src="../../Public/js/crear_torneo.js"></script>

<?php include_once 'footer.php'; ?>