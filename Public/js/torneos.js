class TorneosManager {
    constructor() {
        this.baseUrl = '../../Controllers/TorneosController.php';
        this.filtroActual = {
            deporte_id: '',
            estado: '',
            calificacion_min: 0,
            nombre: '',
            organizador_tipo: ''
        };
        this.init();
    }

    init() {
        this.configurarEventos();
        this.cargarTorneosIniciales();
    }

    configurarEventos() {
        // Evento para filtros rápidos por estado
        document.querySelectorAll('[data-estado]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                // Remover clase active de todos los botones
                document.querySelectorAll('[data-estado]').forEach(b => b.classList.remove('active'));
                // Agregar active al clickeado
                e.target.classList.add('active');
                
                this.filtroActual.estado = e.target.dataset.estado;
                this.cargarTorneos();
            });
        });

        // Evento para botón de búsqueda
        document.getElementById('btnFiltrar').addEventListener('click', () => {
            this.aplicarFiltros();
        });

        // Eventos para filtros en tiempo real
        document.getElementById('busquedaNombre').addEventListener('input', (e) => {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.filtroActual.nombre = e.target.value;
                this.cargarTorneos();
            }, 500);
        });

        // Eventos para selects
        document.getElementById('filtroDeporte').addEventListener('change', (e) => {
            this.filtroActual.deporte_id = e.target.value;
            this.cargarTorneos();
        });

        document.getElementById('filtroCalificacion').addEventListener('change', (e) => {
            this.filtroActual.calificacion_min = e.target.value;
            this.cargarTorneos();
        });

        document.getElementById('filtroOrganizador').addEventListener('change', (e) => {
            this.filtroActual.organizador_tipo = e.target.value;
            this.cargarTorneos();
        });
    }

    aplicarFiltros() {
        this.filtroActual = {
            deporte_id: document.getElementById('filtroDeporte').value,
            calificacion_min: document.getElementById('filtroCalificacion').value,
            nombre: document.getElementById('busquedaNombre').value,
            organizador_tipo: document.getElementById('filtroOrganizador').value,
            estado: this.filtroActual.estado // Mantener estado de filtros rápidos
        };
        this.cargarTorneos();
    }

    async cargarTorneosIniciales() {
        this.cargarTorneos();
    }

    async cargarTorneos() {
        const container = document.getElementById('torneosContainer');
        
        // Mostrar loading
        container.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner"></div>
            </div>
        `;

        try {
            const params = new URLSearchParams({
                action: 'obtener_torneos',
                ...this.filtroActual
            });

            const response = await fetch(`${this.baseUrl}?${params}`);
            const data = await response.json();

            if (data.success) {
                this.mostrarTorneos(data.torneos);
            } else {
                this.mostrarError('Error cargando torneos: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarError('Error de conexión al cargar torneos');
        }
    }

    // ✅ FUNCIÓN ACTUALIZADA: Mostrar torneos con imágenes de imgbb
    mostrarTorneos(torneos) {
        const container = document.getElementById('torneosContainer');

        if (torneos.length === 0) {
            container.innerHTML = `
                <div class="sin-torneos">
                    <i class="fas fa-trophy"></i>
                    <h4>No se encontraron torneos</h4>
                    <p>Prueba ajustar los filtros de búsqueda</p>
                </div>
            `;
            return;
        }

        let html = '<div class="torneos-grid">';
        
        torneos.forEach(torneo => {
            const fechaInicio = new Date(torneo.fecha_inicio).toLocaleDateString('es-PE');
            const fechaFin = torneo.fecha_fin ? new Date(torneo.fecha_fin).toLocaleDateString('es-PE') : 'Por definir';
            const inscripcionFin = new Date(torneo.fecha_inscripcion_fin).toLocaleDateString('es-PE');
            
            const precio = parseFloat(torneo.costo_inscripcion) === 0 ? 
                '<span class="torneo-precio gratis"><i class="fas fa-gift"></i> GRATIS</span>' :
                `<span class="torneo-precio"><i class="fas fa-coins"></i> S/. ${parseFloat(torneo.costo_inscripcion).toFixed(2)}</span>`;

            const calificacion = this.generarEstrellas(torneo.sede_calificacion);
            
            const estadoClass = `estado-${torneo.estado.replace('_', '-')}`;
            
            // ✅ USAR IMAGEN DE IMGBB O PLACEHOLDER
            const imagenHtml = torneo.imagen_torneo ? 
                `<img src="${torneo.imagen_torneo}" alt="${torneo.nombre}" class="torneo-imagen" 
                     loading="lazy" onerror="this.parentElement.innerHTML=this.parentElement.querySelector('.torneo-imagen-placeholder').outerHTML;">
                 <div class="torneo-imagen-overlay">
                     <i class="fas fa-eye"></i> Ver torneo
                 </div>` :
                `<div class="torneo-imagen-placeholder">
                     <i class="fas fa-trophy"></i>
                     <span>Torneo ${torneo.deporte_nombre}</span>
                 </div>`;

            // Botones según el estado
            let botones = '';
            if (torneo.estado === 'inscripciones_abiertas') {
                botones = `
                    <button class="btn-torneo btn-ver-detalles" onclick="torneosManager.verDetalles(${torneo.id})">
                        <i class="fas fa-info-circle"></i> Ver Detalles
                    </button>
                    <button class="btn-torneo btn-inscribir" onclick="torneosManager.mostrarInscripcion(${torneo.id})">
                        <i class="fas fa-user-plus"></i> Inscribir Equipo
                    </button>
                `;
            } else {
                botones = `
                    <button class="btn-torneo btn-ver-detalles" onclick="torneosManager.verDetalles(${torneo.id})">
                        <i class="fas fa-info-circle"></i> Ver Detalles
                    </button>
                `;
            }

            html += `
                <div class="torneo-card">
                    <div class="torneo-imagen-container">
                        ${imagenHtml}
                    </div>
                    
                    <div class="torneo-content">
                        <div class="torneo-deporte">
                            <i class="fas fa-${this.obtenerIconoDeporte(torneo.deporte_nombre)}"></i> 
                            ${torneo.deporte_nombre}
                        </div>
                        
                        <h3 class="torneo-titulo">${torneo.nombre}</h3>
                        
                        <div class="torneo-info">
                            <i class="fas fa-calendar"></i> 
                            <strong>Inicio:</strong> ${fechaInicio}
                        </div>
                        
                        <div class="torneo-info">
                            <i class="fas fa-calendar-check"></i> 
                            <strong>Fin:</strong> ${fechaFin}
                        </div>
                        
                        <div class="torneo-info">
                            <i class="fas fa-clock"></i> 
                            <strong>Inscripciones hasta:</strong> ${inscripcionFin}
                        </div>
                        
                        <div class="torneo-info">
                            <i class="fas fa-users"></i> 
                            <strong>Equipos:</strong> ${torneo.equipos_inscritos}/${torneo.max_equipos}
                        </div>
                        
                        <div class="sede-info">
                            <div class="torneo-info">
                                <i class="fas fa-map-marker-alt"></i> 
                                <strong>Sede:</strong> ${torneo.sede_nombre}
                            </div>
                            <div class="calificacion-sede">
                                ${calificacion} (${torneo.sede_calificacion}/5)
                            </div>
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                                <i class="fas fa-${torneo.tipo_usuario === 'ipd' ? 'landmark' : 'building'}"></i>
                                ${torneo.tipo_usuario === 'ipd' ? 'IPD' : 'Privado'}
                            </div>
                        </div>
                        
                        ${precio}
                        
                        <div class="torneo-estado ${estadoClass}">
                            <i class="fas fa-info-circle"></i> ${torneo.estado_texto}
                        </div>
                        
                        <div class="torneo-acciones">
                            ${botones}
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;
    }

    // ✅ FUNCIÓN NUEVA: Obtener iconos de deportes
    obtenerIconoDeporte(nombreDeporte) {
        const iconos = {
            'futbol': 'futbol',
            'fútbol': 'futbol',
            'football': 'futbol',
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
        };
        
        const nombre = nombreDeporte.toLowerCase();
        return iconos[nombre] || 'trophy';
    }

    generarEstrellas(calificacion) {
        const estrellasLlenas = Math.floor(calificacion);
        const mediaEstrella = calificacion % 1 >= 0.5;
        let html = '';

        for (let i = 0; i < estrellasLlenas; i++) {
            html += '<i class="fas fa-star"></i>';
        }

        if (mediaEstrella) {
            html += '<i class="fas fa-star-half-alt"></i>';
        }

        const estrellasVacias = 5 - estrellasLlenas - (mediaEstrella ? 1 : 0);
        for (let i = 0; i < estrellasVacias; i++) {
            html += '<i class="far fa-star"></i>';
        }

        return html;
    }

    verDetalles(torneoId) {
        // TODO: Implementar modal de detalles
        console.log('Ver detalles del torneo:', torneoId);
        alert(`Función en desarrollo. Torneo ID: ${torneoId}`);
    }

    mostrarInscripcion(torneoId) {
        // TODO: Implementar modal de inscripción
        console.log('Inscribir equipo en torneo:', torneoId);
        alert(`Función en desarrollo. Inscripción para torneo ID: ${torneoId}`);
    }

    mostrarError(mensaje) {
        const container = document.getElementById('torneosContainer');
        container.innerHTML = `
            <div class="sin-torneos">
                <i class="fas fa-exclamation-triangle" style="color: #dc3545;"></i>
                <h4>Error</h4>
                <p>${mensaje}</p>
                <button class="btn btn-primary" onclick="torneosManager.cargarTorneos()">
                    <i class="fas fa-redo"></i> Reintentar
                </button>
            </div>
        `;
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Solo inicializar si no está deshabilitado el chat
    if (!window.chatDisabled) {
        window.torneosManager = new TorneosManager();
    } else {
        // Si chat está deshabilitado, solo inicializar torneos
        window.torneosManager = new TorneosManager();
    }
});