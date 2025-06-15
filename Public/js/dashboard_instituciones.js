// dashboard_instituciones.js
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard Instituciones cargado - usando InsDeporController existente');
    
    // Inicializar componentes
    initQuickActions();
    initNotifications();
});

// Funciones para las acciones del dashboard
function editarInstalacion(instalacionId) {
    console.log('Editando instalación:', instalacionId);
    // Redirigir a página de edición o abrir modal
    window.location.href = `instalaciones.php?edit=${instalacionId}`;
}

function gestionarHorarios(instalacionId) {
    console.log('Gestionando horarios de instalación:', instalacionId);
    // Redirigir a página de horarios o abrir modal
    window.location.href = `horarios.php?instalacion=${instalacionId}`;
}

function contactarCliente(telefono) {
    console.log('Contactando cliente:', telefono);
    // Abrir aplicación de teléfono o WhatsApp
    window.open(`tel:${telefono}`, '_self');
}

function nuevaInstalacion() {
    console.log('Creando nueva instalación');
    window.location.href = 'instalaciones.php?action=new';
}

function gestionarTodosLosHorarios() {
    console.log('Gestionando todos los horarios');
    window.location.href = 'horarios.php';
}

function bloquearHorario() {
    console.log('Bloqueando horario');
    // Abrir modal de bloqueo de horario
    alert('Función de bloqueo de horario - próximamente');
}

function crearTorneo() {
    console.log('Creando torneo');
    window.location.href = 'torneos.php?action=new';
}

function nuevaPromocion() {
    console.log('Creando promoción');
    window.location.href = 'promociones.php?action=new';
}

// Acciones rápidas del sidebar
function initQuickActions() {
    const quickActionBtns = document.querySelectorAll('.quick-action-btn-inst');
    
    quickActionBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.querySelector('span').textContent.trim();
            handleQuickAction(action);
        });
    });
}

function handleQuickAction(action) {
    switch(action) {
        case 'Nueva Instalación':
            nuevaInstalacion();
            break;
        case 'Bloquear Horario':
            bloquearHorario();
            break;
        case 'Crear Torneo':
            crearTorneo();
            break;
        case 'Nueva Promoción':
            nuevaPromocion();
            break;
        default:
            console.log('Acción no reconocida:', action);
    }
}

// Gestión de notificaciones
function initNotifications() {
    const notificationItems = document.querySelectorAll('.notification-item-inst');
    
    notificationItems.forEach(item => {
        item.addEventListener('click', function() {
            this.classList.remove('nueva');
            markAsRead(this);
        });
    });
}

function markAsRead(notificationElement) {
    console.log('Notificación marcada como leída');
    // Aquí iría la lógica para marcar como leída en el backend
}

// Funciones de utilidad
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification-toast ${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${type === 'success' ? 'linear-gradient(135deg, #28a745, #20c997)' : 'linear-gradient(135deg, #dc3545, #e74c3c)'};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        z-index: 1000;
        animation: slideInRight 0.3s ease;
    `;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span style="margin-left: 10px;">${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Agregar estilos para la animación
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);