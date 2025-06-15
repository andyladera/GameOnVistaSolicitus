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
                `<span class="torneo-precio">S/. ${parseFloat(torneo.costo_inscripcion).toFixed(2)}</span>`;

            const calificacion = this.generarEstrellas(torneo.sede_calificacion);
            
            const estadoClass = `estado-${torneo.estado.replace('_', '-')}`;
            
            const imagenUrl = torneo.imagen_torneo ? 
                `../../Resources/images_torneos/${torneo.imagen_torneo}` : 
                '../../Resources/torneo-default.png';

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
                    <img src="${imagenUrl}" alt="${torneo.nombre}" class="torneo-imagen" 
                         onerror="this.src='../../Resources/torneo-default.png'">
                    
                    <div class="torneo-content">
                        <div class="torneo-deporte">
                            <i class="fas fa-futbol"></i> ${torneo.deporte_nombre}
                        </div>
                        
                        <h3 class="torneo-titulo">${torneo.nombre}</h3>
                        
                        <div class="torneo-info">
                            <i class="fas fa-calendar"></i> ${fechaInicio} - ${fechaFin}
                        </div>
                        
                        <div class="torneo-info">
                            <i class="fas fa-clock"></i> Inscripciones hasta: ${inscripcionFin}
                        </div>
                        
                        <div class="torneo-info">
                            <i class="fas fa-users"></i> ${torneo.equipos_inscritos}/${torneo.max_equipos} equipos
                        </div>
                        
                        <div class="sede-info">
                            <div class="torneo-info">
                                <i class="fas fa-map-marker-alt"></i> ${torneo.sede_nombre}
                            </div>
                            <div class="calificacion-sede">
                                ${calificacion} (${torneo.sede_calificacion}/5)
                            </div>
                            <div style="font-size: 0.8rem; color: #6c757d;">
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