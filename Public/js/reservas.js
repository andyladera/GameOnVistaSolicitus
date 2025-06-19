// Public/js/reservas.js
class ReservasManager {
    constructor() {
        this.fechaActual = new Date();
        this.mesActual = this.fechaActual.getMonth();
        this.añoActual = this.fechaActual.getFullYear();
        this.meses = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];
        this.baseUrl = '../../Controllers/ReservaController.php';
        this.init();
    }

    init() {
        this.configurarEventos();
        this.generarCalendario();
        this.cargarProximasActividades();
        this.cargarEquiposUsuario();
    }

    configurarEventos() {
        document.getElementById('btnMesAnterior').addEventListener('click', () => {
            this.mesActual--;
            if (this.mesActual < 0) {
                this.mesActual = 11;
                this.añoActual--;
            }
            this.generarCalendario();
        });

        document.getElementById('btnMesSiguiente').addEventListener('click', () => {
            this.mesActual++;
            if (this.mesActual > 11) {
                this.mesActual = 0;
                this.añoActual++;
            }
            this.generarCalendario();
        });

        document.getElementById('btnCerrarModal').addEventListener('click', () => {
            document.getElementById('modalDia').style.display = 'none';
        });

        document.getElementById('modalDia').addEventListener('click', (e) => {
            if (e.target.id === 'modalDia') {
                document.getElementById('modalDia').style.display = 'none';
            }
        });

        document.getElementById('btnBuscarHorarios').addEventListener('click', () => {
            this.buscarHorarios();
        });
    }

    generarCalendario() {
        const grid = document.getElementById('calendarioGrid');
        const mesActualElement = document.getElementById('mesActual');
        
        mesActualElement.textContent = `${this.meses[this.mesActual]} ${this.añoActual}`;
        
        const cabeceras = grid.innerHTML.split('</div>').slice(0, 7).join('</div>') + '</div>';
        grid.innerHTML = cabeceras;
        
        const primerDia = new Date(this.añoActual, this.mesActual, 1);
        const ultimoDia = new Date(this.añoActual, this.mesActual + 1, 0);
        const diasEnMes = ultimoDia.getDate();
        const diaInicio = primerDia.getDay();
        
        const mesAnterior = new Date(this.añoActual, this.mesActual, 0);
        for (let i = diaInicio - 1; i >= 0; i--) {
            const dia = mesAnterior.getDate() - i;
            this.crearCeldaDia(dia, true, this.mesActual - 1, this.añoActual);
        }
        
        for (let dia = 1; dia <= diasEnMes; dia++) {
            this.crearCeldaDia(dia, false, this.mesActual, this.añoActual);
        }
        
        const totalCeldas = grid.children.length - 7;
        const celdasRestantes = (Math.ceil(totalCeldas / 7) * 7) - totalCeldas;
        for (let dia = 1; dia <= celdasRestantes; dia++) {
            this.crearCeldaDia(dia, true, this.mesActual + 1, this.añoActual);
        }
        
        this.cargarEventosMes();
    }

    crearCeldaDia(numeroDia, esOtroMes, mes, año) {
        const grid = document.getElementById('calendarioGrid');
        const celda = document.createElement('div');
        celda.className = 'dia-celda';
        
        if (esOtroMes) {
            celda.classList.add('dia-otro-mes');
        }
        
        const hoy = new Date();
        if (!esOtroMes && numeroDia === hoy.getDate() && 
            mes === hoy.getMonth() && año === hoy.getFullYear()) {
            celda.classList.add('dia-hoy');
        }
        
        const fechaCelda = new Date(año, mes, numeroDia);
        celda.dataset.fecha = fechaCelda.toISOString().split('T')[0];
        
        celda.innerHTML = `
            <div class="dia-numero">${numeroDia}</div>
            <div class="dia-eventos" id="eventos-${celda.dataset.fecha}">
                <!-- Los eventos se cargan dinámicamente -->
            </div>
        `;
        
        celda.addEventListener('click', () => {
            this.mostrarDetallesDia(celda.dataset.fecha);
        });
        
        grid.appendChild(celda);
    }

    async cargarEventosMes() {
        try {
            const fechaInicio = new Date(this.añoActual, this.mesActual, 1).toISOString().split('T')[0];
            const fechaFin = new Date(this.añoActual, this.mesActual + 1, 0).toISOString().split('T')[0];
            
            const response = await fetch(`${this.baseUrl}?action=obtener_eventos_mes&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`);
            const data = await response.json();
            
            // ✅ LIMPIAR EVENTOS ANTERIORES
            document.querySelectorAll('.dia-eventos').forEach(div => {
                div.innerHTML = '';
            });
            
            if (data.success) {
                data.data.forEach(evento => {
                    this.agregarEventoADia(evento);
                });
            }
            
        } catch (error) {
            console.error('Error cargando eventos del mes:', error);
        }
    }

    agregarEventoADia(evento) {
        const contenedorEventos = document.getElementById(`eventos-${evento.fecha}`);
        if (contenedorEventos) {
            const elementoEvento = document.createElement('div');
            elementoEvento.className = `evento-${evento.tipo}`;
            
            // ✅ MOSTRAR INFORMACIÓN SEGÚN EL TIPO
            if (evento.tipo === 'reserva') {
                elementoEvento.innerHTML = `
                    <div class="evento-texto">${evento.titulo}</div>
                    <div class="evento-subtexto">${evento.detalle}</div>
                `;
            } else if (evento.tipo === 'torneo') {
                elementoEvento.innerHTML = `
                    <div class="evento-texto">${evento.titulo}</div>
                    <div class="evento-subtexto">${evento.detalle}</div>
                `;
            }
            
            // ✅ TOOLTIP CON INFORMACIÓN COMPLETA
            elementoEvento.title = `${evento.titulo}\n${evento.detalle}`;
            
            contenedorEventos.appendChild(elementoEvento);
        }
    }

    mostrarDetallesDia(fecha) {
        const modal = document.getElementById('modalDia');
        const titulo = document.getElementById('modalDiaTitulo');
        const contenido = document.getElementById('modalDiaContenido');
        
        const fechaObj = new Date(fecha + 'T00:00:00');
        const fechaFormateada = fechaObj.toLocaleDateString('es-PE', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        titulo.textContent = fechaFormateada;
        
        contenido.innerHTML = `
            <div style="color: #b0b0b0; text-align: center; padding: 40px;">
                <i class="fas fa-calendar-day" style="font-size: 3rem; margin-bottom: 20px; color: #007bff;"></i>
                <h4 style="color: #ffffff;">Detalles del día</h4>
                <p>Funcionalidad en desarrollo...</p>
                <p>Fecha seleccionada: ${fecha}</p>
            </div>
        `;
        
        modal.style.display = 'block';
    }

    async cargarProximasActividades() {
        try {
            // ✅ CARGAR PRÓXIMAS RESERVAS CON INFORMACIÓN COMPLETA
            const responseReservas = await fetch(`${this.baseUrl}?action=obtener_proximas_reservas`);
            const dataReservas = await responseReservas.json();
            
            if (dataReservas.success) {
                let htmlReservas = '';
                if (dataReservas.data.length > 0) {
                    dataReservas.data.forEach(reserva => {
                        // ✅ FORMATEAR FECHA Y HORA
                        const fechaObj = new Date(reserva.fecha + 'T00:00:00');
                        const fechaFormateada = fechaObj.toLocaleDateString('es-PE', {
                            weekday: 'short',
                            month: 'short', 
                            day: 'numeric'
                        });
                        
                        const horaInicio = reserva.hora_inicio.substring(0, 5);
                        const horaFin = reserva.hora_fin.substring(0, 5);
                        
                        // ✅ DETERMINAR COLOR SEGÚN ESTADO
                        let estadoClass = '';
                        let estadoTexto = '';
                        switch(reserva.estado) {
                            case 'confirmada':
                                estadoClass = 'estado-confirmada';
                                estadoTexto = '✅ Confirmada';
                                break;
                            case 'pendiente':
                                estadoClass = 'estado-pendiente';
                                estadoTexto = '⏳ Pendiente';
                                break;
                            case 'cancelada':
                                estadoClass = 'estado-cancelada';
                                estadoTexto = '❌ Cancelada';
                                break;
                        }
                        
                        htmlReservas += `
                            <div class="reserva-item">
                                <div class="item-fecha">
                                    <i class="fas fa-calendar-day"></i>
                                    ${fechaFormateada} • ${horaInicio} - ${horaFin}
                                </div>
                                <div class="item-titulo">
                                    <i class="fas fa-futbol"></i>
                                    ${reserva.deporte} - ${reserva.nombre_area}
                                </div>
                                <div class="item-detalle">
                                    <i class="fas fa-map-marker-alt"></i>
                                    ${reserva.instalacion}
                                </div>
                                <div class="item-estado ${estadoClass}">
                                    ${estadoTexto}
                                </div>
                                <div class="item-precio">
                                    <i class="fas fa-dollar-sign"></i>
                                    S/ ${parseFloat(reserva.tarifa_por_hora).toFixed(2)}/hora
                                </div>
                            </div>
                        `;
                    });
                } else {
                    htmlReservas = `
                        <div class="estado-vacio">
                            <i class="fas fa-calendar-times"></i>
                            <p>No tienes reservas próximas</p>
                            <small>¡Reserva una cancha para empezar a jugar!</small>
                        </div>
                    `;
                }
                document.getElementById('proximasReservas').innerHTML = htmlReservas;
            }
            
            // ✅ CARGAR PRÓXIMOS TORNEOS (MANTENEMOS IGUAL POR AHORA)
            const responseTorneos = await fetch(`${this.baseUrl}?action=obtener_proximos_torneos`);
            const dataTorneos = await responseTorneos.json();
            
            if (dataTorneos.success) {
                let htmlTorneos = '';
                if (dataTorneos.data.length > 0) {
                    dataTorneos.data.forEach(torneo => {
                        const fechaObj = new Date(torneo.fecha_partido);
                        const fechaFormateada = fechaObj.toLocaleDateString('es-PE', {
                            weekday: 'short',
                            month: 'short',
                            day: 'numeric'
                        });
                        const horaFormateada = fechaObj.toLocaleTimeString('es-PE', {
                            hour: '2-digit', 
                            minute: '2-digit'
                        });
                        
                        htmlTorneos += `
                            <div class="torneo-item">
                                <div class="item-fecha">
                                    <i class="fas fa-clock"></i>
                                    ${fechaFormateada} • ${horaFormateada}
                                </div>
                                <div class="item-titulo">
                                    <i class="fas fa-trophy"></i>
                                    ${torneo.deporte_nombre} - ${torneo.torneo_nombre}
                                </div>
                                <div class="item-detalle">
                                    <i class="fas fa-users"></i>
                                    ${torneo.partido_detalle}
                                </div>
                                <div class="item-detalle">
                                    <i class="fas fa-map-marker-alt"></i>
                                    ${torneo.sede_nombre}
                                </div>
                            </div>
                        `;
                    });
                } else {
                    htmlTorneos = `
                        <div class="estado-vacio">
                            <i class="fas fa-trophy"></i>
                            <p>No tienes partidos de torneo próximos</p>
                            <small>¡Únete a un equipo e inscríbete en torneos!</small>
                        </div>
                    `;
                }
                document.getElementById('proximosTorneos').innerHTML = htmlTorneos;
            }
            
        } catch (error) {
            console.error('Error cargando próximas actividades:', error);
            document.getElementById('proximasReservas').innerHTML = `
                <div class="error-estado">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p style="color: #dc3545;">Error cargando reservas</p>
                </div>
            `;
            document.getElementById('proximosTorneos').innerHTML = `
                <div class="error-estado">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p style="color: #dc3545;">Error cargando torneos</p>
                </div>
            `;
        }
    }

    async cargarEquiposUsuario() {
        try {
            const response = await fetch(`${this.baseUrl}?action=obtener_equipos_usuario`);
            const data = await response.json();
            
            if (data.success) {
                const selectEquipo = document.getElementById('equipoReserva');
                selectEquipo.innerHTML = '<option value="">Reserva individual</option>';
                
                data.data.forEach(equipo => {
                    selectEquipo.innerHTML += `<option value="${equipo.id}">${equipo.nombre} (${equipo.deporte})</option>`;
                });
            }
        } catch (error) {
            console.error('Error cargando equipos del usuario:', error);
        }
    }

    buscarHorarios() {
        const fecha = document.getElementById('fechaReserva').value;
        const deporte = document.getElementById('deporteReserva').value;
        const equipo = document.getElementById('equipoReserva').value;
        
        if (!fecha || !deporte) {
            alert('Por favor selecciona fecha y deporte');
            return;
        }
        
        console.log('Buscando horarios:', { fecha, deporte, equipo });
        alert('Función en desarrollo. Redirigiendo a instalaciones...');
        window.location.href = 'insdepor.php';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    window.reservasManager = new ReservasManager();
});