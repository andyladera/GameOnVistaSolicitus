document.addEventListener('DOMContentLoaded', function () {
    // Event listener para los botones de cronograma/horarios
    document.querySelectorAll('.btn-ver-cronograma').forEach(button => {
        button.addEventListener('click', function () {
            const institucionId = this.getAttribute('data-id');
            
            // Mostrar indicador de carga
            button.textContent = 'Cargando...';
            button.disabled = true;
            
            fetch('../../Controllers/ReservaController.php?action=getCronograma&id=' + institucionId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        mostrarModal(data.data);
                    } else {
                        throw new Error(data.message || 'Error al cargar cronograma');
                    }
                })
                .catch(error => {
                    console.error('Error al cargar cronograma:', error);
                    alert('Error al cargar el cronograma. Por favor, intenta de nuevo.');
                })
                .finally(() => {
                    // Restaurar el botón
                    button.textContent = 'Ver cronograma';
                    button.disabled = false;
                });
        });
    });

    // Event listener para cerrar el modal con el botón X
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'modal-horarios-close') {
            cerrarModal();
        }
    });

    // Event listener para cerrar el modal haciendo clic en el backdrop
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('modal-horarios-backdrop')) {
            cerrarModal();
        }
    });

    // Cerrar modal con tecla Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModal();
        }
    });
});

function mostrarModal(data) {
    const modal = document.getElementById('modal-horarios');
    const titulo = modal.querySelector('.modal-horarios-title');
    const contenido = modal.querySelector('.modal-horarios-content');

    if (!modal) {
        console.error('Modal no encontrado');
        return;
    }

    titulo.textContent = `Cronograma - ${data.institucion}`;

    // Verificar si hay cronograma
    if (!data.cronograma_semanal || data.cronograma_semanal.length === 0) {
        contenido.innerHTML = '<p style="text-align: center; color: #666;">No hay cronograma disponible para esta instalación.</p>';
    } else {
        contenido.innerHTML = '';
        
        // Crear el calendario
        const calendario = document.createElement('div');
        calendario.classList.add('cronograma-calendario');
        
        data.cronograma_semanal.forEach((dia, index) => {
            const diaDiv = document.createElement('div');
            diaDiv.classList.add('cronograma-dia');
            if (index === 0) diaDiv.classList.add('dia-actual');
            
            // Encabezado del día
            const encabezadoDia = document.createElement('div');
            encabezadoDia.classList.add('cronograma-dia-header');
            encabezadoDia.innerHTML = `
                <h4>${dia.nombre_dia}</h4>
                <span class="fecha">${formatearFechaCorta(dia.fecha)}</span>
            `;
            
            // Grid de horarios
            const gridHorarios = document.createElement('div');
            gridHorarios.classList.add('cronograma-grid');
            
            dia.cronograma.forEach(intervalo => {
                const slot = document.createElement('div');
                slot.classList.add('cronograma-slot');
                slot.classList.add(intervalo.disponible ? 'disponible' : 'ocupado');
                slot.title = `${intervalo.hora_inicio} - ${intervalo.hora_fin} (${intervalo.disponible ? 'Disponible' : 'Ocupado'})`;
                slot.setAttribute('data-hora', `${intervalo.hora_inicio}-${intervalo.hora_fin}`);
                slot.setAttribute('data-fecha', dia.fecha);
                slot.setAttribute('data-disponible', intervalo.disponible);
                
                // Solo mostrar la hora de inicio para ahorrar espacio
                slot.textContent = intervalo.hora_inicio;
                
                gridHorarios.appendChild(slot);
            });
            
            diaDiv.appendChild(encabezadoDia);
            diaDiv.appendChild(gridHorarios);
            calendario.appendChild(diaDiv);
        });
        
        contenido.appendChild(calendario);
        
        // Agregar leyenda
        const leyenda = document.createElement('div');
        leyenda.classList.add('cronograma-leyenda');
        leyenda.innerHTML = `
            <div class="leyenda-item">
                <div class="leyenda-color disponible"></div>
                <span>Disponible</span>
            </div>
            <div class="leyenda-item">
                <div class="leyenda-color ocupado"></div>
                <span>Ocupado</span>
            </div>
        `;
        contenido.appendChild(leyenda);
        
        // Agregar botón de reservar
        const botonReservar = document.createElement('div');
        botonReservar.classList.add('cronograma-acciones');
        botonReservar.innerHTML = `
            <button class="btn-reservar-general" onclick="abrirFormularioReserva('${data.institucion}')">
                <i class="fas fa-calendar-plus"></i> Hacer Reserva
            </button>
        `;
        contenido.appendChild(botonReservar);
    }

    // Mostrar el modal
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function formatearFechaCorta(fecha) {
    const fechaObj = new Date(fecha + 'T00:00:00');
    const dia = fechaObj.getDate();
    const mes = fechaObj.getMonth() + 1;
    return `${dia}/${mes}`;
}

function abrirFormularioReserva(nombreInstitucion) {
    // Aquí puedes agregar la lógica para abrir el formulario de reserva
    alert(`Abrir formulario de reserva para: ${nombreInstitucion}`);
    // Ejemplo: window.location.href = 'reservas.php?institucion=' + encodeURIComponent(nombreInstitucion);
}

function formatearFecha(fecha) {
    const fechaObj = new Date(fecha + 'T00:00:00');
    const opciones = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    return fechaObj.toLocaleDateString('es-ES', opciones);
}

function cerrarModal() {
    const modal = document.getElementById('modal-horarios');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = ''; // Restaurar scroll del body
    }
}