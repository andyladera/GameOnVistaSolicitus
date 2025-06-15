class InsDeporManager {
    constructor(instalacionesData) {
        this.instalaciones = instalacionesData;
        this.map = null;
        this.markers = [];
        this.infoWindow = null;
        this.userMarker = null;
        this.facilities = this.procesarInstalaciones();
        this.mapLoaded = false;
        
        this.init();
    }
    
    init() {
        // Esperar un poco para que el DOM esté completamente cargado
        setTimeout(() => {
            this.configurarEventos();
        }, 100);
    }
    
    procesarInstalaciones() {
        if (!this.instalaciones) return [];
        
        return this.instalaciones.map(instalacion => ({
            position: { 
                lat: parseFloat(instalacion.latitud), 
                lng: parseFloat(instalacion.longitud) 
            },
            name: instalacion.nombre,
            type: instalacion.deportes ? instalacion.deportes.map(d => d.nombre).join(', ') : 'Sin deportes',
            id: instalacion.id,
            tarifa: `S/. ${parseFloat(instalacion.tarifa).toFixed(2)}`,
            calificacion: parseFloat(instalacion.calificacion)
        }));
    }
    
    configurarEventos() {
        // Verificar que los elementos existan antes de agregar eventos
        this.configurarEventosHorarios();
        this.configurarEventosMapa();
        this.configurarEventosFiltros();
        this.configurarEventosModal();
    }
    
    configurarEventosHorarios() {
        const botonesHorarios = document.querySelectorAll('.btn-ver-horarios');
        botonesHorarios.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.getAttribute('data-id');
                this.toggleHorarios(id, e.target);
            });
        });
    }
    
    configurarEventosMapa() {
        const botonesMapa = document.querySelectorAll('.btn-ver-mapa');
        botonesMapa.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const lat = parseFloat(e.target.getAttribute('data-lat'));
                const lng = parseFloat(e.target.getAttribute('data-lng'));
                const nombre = e.target.getAttribute('data-nombre');
                this.centrarMapa(lat, lng, nombre);
            });
        });
    }
    
    configurarEventosFiltros() {
        const btnFiltrar = document.getElementById('btnFiltrar');
        const btnCercanas = document.getElementById('btnCercanas');
        
        if (btnFiltrar) {
            btnFiltrar.addEventListener('click', () => {
                this.aplicarFiltros();
            });
        }
        
        if (btnCercanas) {
            btnCercanas.addEventListener('click', () => {
                this.mostrarInstalacionesCercanas();
            });
        }
    }
    
    configurarEventosModal() {
        // Eventos para cronograma
        document.querySelectorAll('.btn-ver-cronograma').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.getAttribute('data-id');
                this.verCronograma(id);
            });
        });
        
        // Eventos para comentarios
        document.querySelectorAll('.btn-ver-comentarios').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.getAttribute('data-id');
                this.verComentarios(id);
            });
        });
        
        // Eventos para imágenes
        document.querySelectorAll('.btn-ver-imagenes').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.getAttribute('data-id');
                this.verImagenes(id);
            });
        });
        
        // Cerrar modal
        const modalClose = document.getElementById('modal-horarios-close');
        if (modalClose) {
            modalClose.addEventListener('click', () => {
                this.cerrarModal();
            });
        }
        
        // Cerrar modal al hacer clic en el backdrop
        const modalBackdrop = document.querySelector('.modal-horarios-backdrop');
        if (modalBackdrop) {
            modalBackdrop.addEventListener('click', () => {
                this.cerrarModal();
            });
        }
    }
    
    initMap() {
        console.log('Inicializando mapa...');
        
        // Verificar que el elemento del mapa exista
        const mapElement = document.getElementById("map");
        if (!mapElement) {
            console.error('Elemento del mapa no encontrado');
            return;
        }
        
        // Coordenadas predeterminadas (Tacna, Perú)
        const defaultLocation = { lat: -18.0066, lng: -70.2463 };
        
        try {
            // Crear el mapa
            this.map = new google.maps.Map(mapElement, {
                zoom: 14,
                center: defaultLocation,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                styles: [
                    { elementType: "geometry", stylers: [{ color: "#242f3e" }] },
                    { elementType: "labels.text.stroke", stylers: [{ color: "#242f3e" }] },
                    { elementType: "labels.text.fill", stylers: [{ color: "#746855" }] }
                    // ... resto de estilos
                ]
            });
            
            this.infoWindow = new google.maps.InfoWindow();
            this.mapLoaded = true;
            
            console.log('Mapa creado exitosamente');
            
            // Agregar marcadores
            this.addFacilityMarkers();
            
            // Intentar obtener la ubicación del usuario
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const userLocation = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        };
                        
                        this.map.setCenter(userLocation);
                        this.addUserMarker(userLocation);
                    },
                    (error) => {
                        console.log('Error de geolocalización:', error);
                        this.handleLocationError(true);
                    }
                );
            } else {
                this.handleLocationError(false);
            }
            
        } catch (error) {
            console.error('Error inicializando el mapa:', error);
        }
    }
    
    addUserMarker(location) {
        if (!this.map) return;
        
        this.userMarker = new google.maps.Marker({
            position: location,
            map: this.map,
            title: "Tu ubicación",
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 10,
                fillColor: "#00bcd4",
                fillOpacity: 1,
                strokeColor: "#ffffff",
                strokeWeight: 2,
            },
        });
    }
    
    handleLocationError(browserHasGeolocation) {
        if (!this.map || !this.infoWindow) return;
        
        const pos = this.map.getCenter();
        this.infoWindow.setPosition(pos);
        this.infoWindow.setContent(
            browserHasGeolocation
                ? "Error: El servicio de geolocalización falló."
                : "Error: Tu navegador no soporta geolocalización."
        );
        this.infoWindow.open(this.map);
    }
    
    addFacilityMarkers() {
        if (!this.map || !this.facilities) return;
        
        console.log('Agregando marcadores de instalaciones:', this.facilities.length);
        
        this.facilities.forEach((facility) => {
            const marker = new google.maps.Marker({
                position: facility.position,
                map: this.map,
                title: facility.name,
            });
            
            this.markers.push(marker);
            
            marker.addListener("click", () => {
                this.infoWindow.setContent(
                    `<div class="info-window">
                        <h3>${facility.name}</h3>
                        <p>${facility.type}</p>
                        <p>Tarifa: ${facility.tarifa}</p>
                        <p>Calificación: ${facility.calificacion.toFixed(1)} <i class="fas fa-star text-warning"></i></p>
                        <button onclick="window.insDeporManager.verInstalacion(${facility.id})" class="map-btn">Ver detalles</button>
                    </div>`
                );
                this.infoWindow.open(this.map, marker);
            });
        });
    }
    
    verInstalacion(id) {
        const instalacion = document.querySelector(`.instalacion-card[data-id="${id}"]`);
        if (instalacion) {
            instalacion.scrollIntoView({ behavior: 'smooth', block: 'center' });
            instalacion.classList.add('highlight');
            setTimeout(() => {
                instalacion.classList.remove('highlight');
            }, 2000);
        }
    }
    
    toggleHorarios(id, button) {
        const horariosContainer = document.getElementById(`horarios-${id}`);
        if (horariosContainer) {
            if (horariosContainer.style.display === 'none' || !horariosContainer.style.display) {
                horariosContainer.style.display = 'block';
                button.textContent = 'Ocultar horarios';
            } else {
                horariosContainer.style.display = 'none';
                button.textContent = 'Ver horarios';
            }
        }
    }
    
    centrarMapa(lat, lng, nombre) {
        if (!this.map) {
            console.log('Mapa no está inicializado');
            return;
        }
        
        const position = { lat, lng };
        this.map.setCenter(position);
        this.map.setZoom(16);
        
        // Abrir info window en el marcador correspondiente
        for (let i = 0; i < this.markers.length; i++) {
            if (this.markers[i].getTitle() === nombre) {
                google.maps.event.trigger(this.markers[i], 'click');
                break;
            }
        }
    }
    
    aplicarFiltros() {
        const nombreBusqueda = document.getElementById('busquedaNombre')?.value.toLowerCase() || '';
        const deporteSeleccionado = document.getElementById('filtroDeporte')?.value || '';
        const calificacionMinima = parseFloat(document.getElementById('filtroCalificacion')?.value) || 0;
        
        document.querySelectorAll('.instalacion-card').forEach(card => {
            const nombre = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
            const deportes = card.getAttribute('data-deportes')?.split(',') || [];
            const calificacion = parseFloat(card.getAttribute('data-calificacion')) || 0;
            
            let mostrar = true;
            
            // Filtrar por nombre
            if (nombreBusqueda && !nombre.includes(nombreBusqueda)) {
                mostrar = false;
            }
            
            // Filtrar por deporte
            if (deporteSeleccionado && !deportes.includes(deporteSeleccionado)) {
                mostrar = false;
            }
            
            // Filtrar por calificación
            if (calificacion < calificacionMinima) {
                mostrar = false;
            }
            
            card.style.display = mostrar ? 'block' : 'none';
        });
    }
    
    mostrarInstalacionesCercanas() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.ordenarPorDistancia(position.coords.latitude, position.coords.longitude);
                },
                () => {
                    alert('No se pudo acceder a tu ubicación. Permite el acceso a la ubicación para usar esta función.');
                }
            );
        } else {
            alert('Tu navegador no soporta geolocalización.');
        }
    }
    
    ordenarPorDistancia(userLat, userLng) {
        const instalacionesConDistancia = [];
        
        document.querySelectorAll('.instalacion-card').forEach(card => {
            const btn = card.querySelector('.btn-ver-mapa');
            if (btn) {
                const lat = parseFloat(btn.getAttribute('data-lat'));
                const lng = parseFloat(btn.getAttribute('data-lng'));
                
                const distance = this.calcularDistancia(userLat, userLng, lat, lng);
                
                instalacionesConDistancia.push({
                    element: card,
                    distance: distance
                });
            }
        });
        
        // Ordenar por distancia
        instalacionesConDistancia.sort((a, b) => a.distance - b.distance);
        
        // Reorganizar elementos en el DOM
        const container = document.getElementById('listaInstalaciones');
        if (container) {
            instalacionesConDistancia.forEach(item => {
                container.appendChild(item.element);
            });
        }
        
        alert('Instalaciones ordenadas por cercanía a tu ubicación actual.');
    }
    
    calcularDistancia(lat1, lng1, lat2, lng2) {
        const R = 6371; // Radio de la Tierra en km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
                Math.sin(dLng/2) * Math.sin(dLng/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }
    
    verCronograma(id) {
        console.log('Ver cronograma de instalación:', id);
        this.mostrarModal('Cronograma', 'Cargando cronograma...');
    }
    
    verComentarios(id) {
        console.log('Ver comentarios de instalación:', id);
        this.mostrarModal('Comentarios', 'Cargando comentarios...');
    }
    
    verImagenes(id) {
        console.log('Ver imágenes de instalación:', id);
        this.mostrarModal('Imágenes', 'Cargando imágenes...');
    }
    
    mostrarModal(titulo, contenido) {
        const modal = document.getElementById('modal-horarios');
        const modalTitulo = document.querySelector('.modal-horarios-title');
        const modalContenido = document.querySelector('.modal-horarios-content');
        
        if (modal && modalTitulo && modalContenido) {
            modalTitulo.textContent = titulo;
            modalContenido.innerHTML = contenido;
            modal.style.display = 'block';
        }
    }
    
    cerrarModal() {
        const modal = document.getElementById('modal-horarios');
        if (modal) {
            modal.style.display = 'none';
        }
    }
}

// Función global para inicializar el mapa (requerida por Google Maps API)
function initMap() {
    console.log('initMap llamada desde Google Maps API');
    if (window.insDeporManager) {
        window.insDeporManager.initMap();
    } else {
        console.log('insDeporManager no está disponible aún');
        // Intentar de nuevo en 100ms
        setTimeout(() => {
            if (window.insDeporManager) {
                window.insDeporManager.initMap();
            }
        }, 100);
    }
}