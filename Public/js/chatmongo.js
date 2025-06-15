class GameOnChatMongo {
    constructor() {
        this.currentConversationId = null;
        this.currentUserId = null;
        this.currentUserName = '';
        this.pollInterval = null;
        this.isPolling = false;
        this.isTeamChat = false;        // ← Para distinguir tipo de chat
        this.currentTeamId = null;      // ← ID del equipo actual
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.getCurrentUser();
    }
    
    getCurrentUser() {
        const userElement = document.querySelector('[data-user-id]');
        if (userElement) {
            this.currentUserId = parseInt(userElement.getAttribute('data-user-id'));
            console.log('Usuario actual ID:', this.currentUserId);
        }
    }
    
    async startConversation(targetUserId, targetUserName) {
        try {
            this.cleanupAllChats();
            this.currentUserName = targetUserName;
            this.isTeamChat = false;
            const response = await fetch('../../Controllers/MongoDBChatController.php?action=start_conversation', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    target_user_id: parseInt(targetUserId)
                })
            });
            const result = await response.json();            
            if (result.success) {
                this.currentConversationId = result.data.conversation_id;
                this.showChatInPanel();
                this.loadMessages();
                this.startPolling();
            } else {
                alert('Error iniciando conversación: ' + result.error);
            }
        } catch (error) {
            console.error('Error iniciando conversación MongoDB:', error);
            alert('Error de conexión al iniciar chat');
        }
    }
    
    async loadMessages() {
        if (!this.currentConversationId) return;
        
        try {
            const response = await fetch(`../../Controllers/MongoDBChatController.php?action=get_messages&conversation_id=${this.currentConversationId}`);
            const result = await response.json();
            
            if (result.success) {
                this.renderMessages(result.data);
            }
        } catch (error) {
            console.error('Error cargando mensajes MongoDB:', error);
        }
    }
    
    async sendMessage() {
        const input = document.getElementById('chatMessageInput');
        const message = input.value.trim();
        
        if (!message || !this.currentConversationId) return;
        
        try {
            const response = await fetch('../../Controllers/MongoDBChatController.php?action=send_message', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    conversation_id: this.currentConversationId,
                    message: message
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                input.value = '';
                this.loadMessages();
            } else {
                alert('Error enviando mensaje: ' + result.error);
            }
        } catch (error) {
            console.error('Error enviando mensaje MongoDB:', error);
            alert('Error de conexión al enviar mensaje');
        }
    }
    
    renderMessages(messages) {
        const container = document.getElementById('chatMessagesContainer');
        if (!container) return;
        
        container.innerHTML = '';
        
        messages.forEach(msg => {
            console.log('🔍 Mensaje:', msg);
            console.log('🔍 sender_id:', msg.sender_id, 'currentUserId:', this.currentUserId);
            
            // ⭐ VERIFICAR CORRECTAMENTE quién es el usuario actual
            const isCurrentUser = parseInt(msg.sender_id) === parseInt(this.currentUserId);
            const messageClass = isCurrentUser ? 'sent' : 'received';
            
            console.log('🔍 isCurrentUser:', isCurrentUser, 'class:', messageClass);
            
            const messageDiv = document.createElement('div');
            messageDiv.className = `chat-message ${messageClass}`;
            
            // ⭐ ESTILOS INLINE - MISMO DEGRADADO PARA AMBOS
            const messageStyles = `
                max-width: 70%;
                padding: 12px 16px;
                border-radius: 18px;
                word-wrap: break-word;
                box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                background: linear-gradient(135deg, #007f56 0%, #00bcd4 100%);
                color: white;
            `;
            
            // ⭐ POSICIÓN: Usuario derecha, Amigo izquierda
            const containerStyles = isCurrentUser ? 
                'display: flex; justify-content: flex-end; margin-bottom: 15px;' :
                'display: flex; justify-content: flex-start; margin-bottom: 15px;';
            
            messageDiv.style.cssText = containerStyles;
            
            messageDiv.innerHTML = `
                <div style="${messageStyles}">
                    <p style="margin: 0 0 4px 0; font-size: 0.9rem; line-height: 1.4;">${this.escapeHtml(msg.message)}</p>
                    <small style="font-size: 0.75rem; opacity: 0.9; display: block; text-align: right;">${this.formatTime(msg.timestamp)}</small>
                </div>
            `;
            
            container.appendChild(messageDiv);
        });
        
        container.scrollTo({
            top: container.scrollHeight,
            behavior: 'smooth'
        });
    }
    
    showChatInPanel() {
        const existingChats = document.querySelectorAll('[id^="chatMessagesContainer"]');
        existingChats.forEach(chat => {
            console.log('🗑️ Eliminando chat duplicado');
            chat.remove();
        });
        
        // ⭐ BUSCAR EL PANEL DE CHAT CORRECTO - UNO SOLO
        let chatContainer = null;
        
        // Buscar en el orden correcto
        const allCards = document.querySelectorAll('.dashboard-card');
        for (let card of allCards) {
            const h2 = card.querySelector('h2');
            if (h2 && (h2.textContent.trim() === 'CHAT' || h2.textContent.includes('Chat con'))) {
                chatContainer = card;
                console.log('✅ Panel encontrado:', h2.textContent);
                break; // ⭐ IMPORTANTE: Solo tomar el PRIMERO
            }
        }
        
        if (!chatContainer) {
            return;
        }
        
        chatContainer.innerHTML = '';
        
        chatContainer.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2 style="margin: 0; color: #007f56;">
                    <i class="fas fa-comment"></i> Chat con ${this.currentUserName}
                </h2>
                <button class="btn btn-sm btn-secondary" onclick="gameOnChatMongo.closeChatPanel()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="chatMessagesContainer" style="
                height: 400px !important;
                overflow-y: auto !important;
                border: 2px solid #dee2e6 !important;
                border-radius: 15px !important;
                padding: 20px !important;
                margin-bottom: 15px !important;
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 25%, #dee2e6 50%, #f1f3f4 75%, #ffffff 100%) !important;
                box-shadow: inset 0 2px 8px rgba(0,0,0,0.08), 0 4px 16px rgba(0,0,0,0.12) !important;
                background-image: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.3) 2px, transparent 2px), radial-gradient(circle at 80% 80%, rgba(0,0,0,0.05) 1px, transparent 1px) !important;
                background-size: 30px 30px, 20px 20px !important;
            ">
                <div class="chat-loading text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Cargando mensajes...</span>
                    </div>
                    <p style="margin-top: 10px;">Cargando conversación con ${this.currentUserName}...</p>
                </div>
            </div>
            
            <div class="input-group">
                <input type="text" id="chatMessageInput" class="form-control" placeholder="Escribe mensaje a ${this.currentUserName}..." maxlength="500">
                <button class="btn btn-primary" id="chatSendButton" type="button">
                    <i class="fas fa-paper-plane"></i> Enviar
                </button>
            </div>
            
            <div style="margin-top: 10px; font-size: 0.8rem; color: #666; text-align: center;">
                💬 Conversación con <strong>${this.currentUserName}</strong> • 🔐 MongoDB Seguro
            </div>
        `;
    }
    
    closeChatPanel() {
        const existingChats = document.querySelectorAll('[id^="chatMessagesContainer"]');
        existingChats.forEach(chat => chat.remove());
        
        // Buscar EL contenedor correcto
        let chatContainer = null;
        const allCards = document.querySelectorAll('.dashboard-card');
        
        for (let card of allCards) {
            const h2 = card.querySelector('h2');
            if (h2 && (h2.textContent.includes('Chat con') || h2.textContent.trim() === 'CHAT')) {
                chatContainer = card;
                break; // Solo el primero
            }
        }
        
        if (chatContainer) {
            chatContainer.innerHTML = `
                <h2>CHAT</h2>
                <div class="text-center text-muted">
                    <i class="fas fa-comments fa-3x mb-3"></i>
                    <p>Selecciona un amigo o equipo para iniciar una conversación</p>
                    <small>Sistema de chat MongoDB implementado</small>
                </div>
            `;
        }
        
        this.stopPolling();
        this.currentConversationId = null;
        this.currentUserName = '';
        console.log('✅ Chat cerrado SIN duplicados');
    }
    
    startPolling() {
        if (this.isPolling) return;
        
        this.isPolling = true;
        this.pollInterval = setInterval(() => {
            if (this.isTeamChat) {
                this.loadTeamMessages();
            } else {
                this.loadMessages();
            }
        }, 3000);
    }
    
    stopPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
        this.isPolling = false;
    }
    
    setupEventListeners() {
        document.addEventListener('keypress', (e) => {
            if (e.target.id === 'chatMessageInput' && e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
            // ⭐ AGREGAR evento para chat de equipos
            if (e.target.id === 'chatTeamMessageInput' && e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendTeamMessage();
            }
        });
        
        document.addEventListener('click', (e) => {
            if (e.target.id === 'chatSendButton' || e.target.parentElement.id === 'chatSendButton') {
                this.sendMessage();
            }
            // ⭐ AGREGAR evento para botón de equipos
            if (e.target.id === 'chatTeamSendButton' || e.target.parentElement.id === 'chatTeamSendButton') {
                this.sendTeamMessage();
            }
        });
    }
    
    formatTime(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // ========== FUNCIONES ESPECÍFICAS PARA CHAT DE EQUIPOS ==========
    
    async startTeamConversation(teamId, teamName) {
        try {
            console.log('🚀 Iniciando chat grupal MongoDB con:', teamId, teamName);
            
            this.cleanupAllChats();
            
            const response = await fetch('../../Controllers/MongoDBChatController.php?action=start_team_conversation', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    team_id: parseInt(teamId)
                })
            });
            
            const result = await response.json();
            console.log('Respuesta chat grupal MongoDB:', result);
            
            if (result.success) {
                this.currentConversationId = result.data.conversation_id;
                this.currentUserName = teamName;
                this.isTeamChat = true;
                this.currentTeamId = teamId;
                
                console.log('✅ Chat grupal iniciado:', {
                    conversationId: this.currentConversationId,
                    teamName: this.currentUserName
                });
                
                this.showTeamChatInPanel();  // ← FUNCIÓN ESPECÍFICA PARA EQUIPOS
                this.loadTeamMessages();     // ← FUNCIÓN ESPECÍFICA PARA EQUIPOS
                this.startPolling();
            } else {
                alert('Error iniciando chat grupal: ' + result.error);
            }
        } catch (error) {
            console.error('Error iniciando chat grupal MongoDB:', error);
            alert('Error de conexión al iniciar chat grupal');
        }
    }
    
    async loadTeamMessages() {
        if (!this.currentConversationId) {
            console.error('❌ No hay conversationId para cargar mensajes de equipo');
            return;
        }
        
        console.log('🔄 Cargando mensajes de equipo para conversación:', this.currentConversationId);
        
        try {
            const url = `../../Controllers/MongoDBChatController.php?action=get_team_messages&conversation_id=${this.currentConversationId}`;
            console.log('🌐 URL de solicitud:', url);
            
            const response = await fetch(url);
            const result = await response.json();
            
            console.log('📨 Respuesta completa de loadTeamMessages:', result);
            
            if (result.success) {
                console.log('✅ Mensajes obtenidos exitosamente:', result.data);
                this.renderTeamMessages(result.data);
            } else {
                console.error('❌ Error en respuesta:', result.error);
            }
        } catch (error) {
            console.error('💥 Error cargando mensajes de equipo MongoDB:', error);
        }
    }
    
    async sendTeamMessage() {
        const input = document.getElementById('chatTeamMessageInput');
        const message = input.value.trim();
        
        if (!message || !this.currentConversationId) return;
        
        try {
            const response = await fetch('../../Controllers/MongoDBChatController.php?action=send_team_message', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    conversation_id: this.currentConversationId,
                    message: message
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                input.value = '';
                this.loadTeamMessages();
            } else {
                alert('Error enviando mensaje al equipo: ' + result.error);
            }
        } catch (error) {
            console.error('Error enviando mensaje de equipo MongoDB:', error);
            alert('Error de conexión al enviar mensaje');
        }
    }
    
    renderTeamMessages(messages) {
        console.log('🐛 DEBUG renderTeamMessages recibido:', messages);
        console.log('🐛 DEBUG cantidad de mensajes:', messages.length);
        
        const container = document.getElementById('chatTeamMessagesContainer');
        if (!container) {
            console.error('❌ No se encontró chatTeamMessagesContainer');
            return;
        }
        
        container.innerHTML = '';
        
        if (!messages || messages.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted">
                    <i class="fas fa-comments fa-2x mb-3"></i>
                    <p>No hay mensajes en este equipo aún</p>
                    <small>¡Sé el primero en escribir!</small>
                </div>
            `;
            return;
        }
        
        messages.forEach((msg, index) => {
            console.log(`🐛 DEBUG mensaje ${index}:`, msg);
            console.log('🔍 sender_id:', msg.sender_id, 'currentUserId:', this.currentUserId);
            
            const isCurrentUser = parseInt(msg.sender_id) === parseInt(this.currentUserId);
            const messageClass = isCurrentUser ? 'sent' : 'received';
            
            console.log('🔍 isCurrentUser:', isCurrentUser, 'class:', messageClass);
            
            const messageDiv = document.createElement('div');
            messageDiv.className = `chat-message ${messageClass}`;
            
            // ⭐ ESTILOS INLINE - MISMO DEGRADADO PARA AMBOS
            const messageStyles = `
                max-width: 70%;
                padding: 12px 16px;
                border-radius: 18px;
                word-wrap: break-word;
                box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                background: linear-gradient(135deg, #007f56 0%, #00bcd4 100%);
                color: white;
            `;
            
            // ⭐ POSICIÓN: Usuario derecha, Miembro izquierda
            const containerStyles = isCurrentUser ? 
                'display: flex; justify-content: flex-end; margin-bottom: 15px;' :
                'display: flex; justify-content: flex-start; margin-bottom: 15px;';
            
            messageDiv.style.cssText = containerStyles;
            
            // ⭐ AGREGAR NOMBRE DEL MIEMBRO para mensajes recibidos
            const memberName = !isCurrentUser && msg.sender_name ? 
                `<div style="font-size: 0.7rem; opacity: 0.8; margin-bottom: 3px; font-weight: 600;">${msg.sender_name}</div>` : '';
            
            messageDiv.innerHTML = `
                <div style="${messageStyles}">
                    ${memberName}
                    <p style="margin: 0 0 4px 0; font-size: 0.9rem; line-height: 1.4;">${this.escapeHtml(msg.message)}</p>
                    <small style="font-size: 0.75rem; opacity: 0.9; display: block; text-align: right;">${this.formatTime(msg.timestamp)}</small>
                </div>
            `;
            
            container.appendChild(messageDiv);
        });
        
        console.log('✅ Mensajes del equipo renderizados:', messages.length);
        
        container.scrollTo({
            top: container.scrollHeight,
            behavior: 'smooth'
        });
    }
    
    showTeamChatInPanel() {
        console.log('🎨 Mostrando chat de EQUIPO en panel para:', this.currentUserName);
        
        // Eliminar chats duplicados
        const existingChats = document.querySelectorAll('[id^="chatTeamMessagesContainer"]');
        existingChats.forEach(chat => {
            console.log('🗑️ Eliminando chat de equipo duplicado');
            chat.remove();
        });
        
        // ⭐ BUSCAR EL PANEL DE CHAT CORRECTO
        let chatContainer = null;
        const allCards = document.querySelectorAll('.dashboard-card');
        for (let card of allCards) {
            const h2 = card.querySelector('h2');
            if (h2 && (h2.textContent.trim() === 'CHAT' || h2.textContent.includes('Chat con') || h2.textContent.includes('Grupo:'))) {
                chatContainer = card;
                console.log('✅ Panel de equipo encontrado:', h2.textContent);
                break;
            }
        }
        
        if (!chatContainer) {
            console.error('❌ No se encontró el panel de CHAT para equipos');
            return;
        }
        
        chatContainer.innerHTML = '';
        
        chatContainer.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2 style="margin: 0; color: #00bcd4;">
                    <i class="fas fa-users"></i> Grupo: ${this.currentUserName}
                </h2>
                <div>
                    <button class="btn btn-sm btn-outline-primary me-2" onclick="gameOnChatMongo.showTeamMembers()">
                        <i class="fas fa-users"></i> Miembros
                    </button>
                    <button class="btn btn-sm btn-secondary" onclick="gameOnChatMongo.closeTeamChatPanel()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <div id="chatTeamMessagesContainer" style="
                height: 400px !important;
                overflow-y: auto !important;
                border: 2px solid #00bcd4 !important;
                border-radius: 15px !important;
                padding: 20px !important;
                margin-bottom: 15px !important;
                background: linear-gradient(135deg, #f0f8ff 0%, #e6f3ff 25%, #d1e9ff 50%, #e8f4f8 75%, #ffffff 100%) !important;
                box-shadow: inset 0 2px 8px rgba(0,188,212,0.08), 0 4px 16px rgba(0,127,86,0.12) !important;
                background-image: radial-gradient(circle at 20% 20%, rgba(0,188,212,0.3) 2px, transparent 2px), radial-gradient(circle at 80% 80%, rgba(0,127,86,0.05) 1px, transparent 1px) !important;
                background-size: 30px 30px, 20px 20px !important;
            ">
                <div class="chat-loading text-center">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Cargando mensajes del equipo...</span>
                    </div>
                    <p style="margin-top: 10px; color: #00bcd4;">Cargando conversación del equipo ${this.currentUserName}...</p>
                </div>
            </div>
            
            <div class="input-group">
                <input type="text" id="chatTeamMessageInput" class="form-control" placeholder="Escribe tu mensaje al equipo ${this.currentUserName}..." maxlength="500">
                <button class="btn btn-info" id="chatTeamSendButton" type="button">
                    <i class="fas fa-paper-plane"></i> Enviar
                </button>
            </div>
            
            <div style="margin-top: 10px; font-size: 0.8rem; color: #666; text-align: center;">
                💬 Chat Grupal: <strong>${this.currentUserName}</strong> • 🔐 MongoDB Seguro • 👥 Equipo
            </div>
        `;
        
        console.log('✅ Panel de chat de EQUIPO actualizado para:', this.currentUserName);
    }
    
    closeTeamChatPanel() {
        console.log('🔴 Cerrando chat panel de EQUIPO');
        
        const existingChats = document.querySelectorAll('[id^="chatTeamMessagesContainer"]');
        existingChats.forEach(chat => chat.remove());
        
        let chatContainer = null;
        const allCards = document.querySelectorAll('.dashboard-card');
        
        for (let card of allCards) {
            const h2 = card.querySelector('h2');
            if (h2 && (h2.textContent.includes('Grupo:') || h2.textContent.includes('Chat con') || h2.textContent.trim() === 'CHAT')) {
                chatContainer = card;
                break;
            }
        }
        
        if (chatContainer) {
            chatContainer.innerHTML = `
                <h2>CHAT</h2>
                <div class="text-center text-muted">
                    <i class="fas fa-comments fa-3x mb-3"></i>
                    <p>Selecciona un amigo o equipo para iniciar una conversación</p>
                    <small>Sistema de chat MongoDB implementado</small>
                </div>
            `;
        }
        
        this.stopPolling();
        this.currentConversationId = null;
        this.currentUserName = '';
        this.isTeamChat = false;
        this.currentTeamId = null;
        console.log('✅ Chat de EQUIPO cerrado completamente');
    }
    
    showTeamMembers() {
        if (this.currentTeamId) {
            chatManager.verMiembrosEquipo(this.currentTeamId, this.currentUserName);
        }
    }
    
    cleanupAllChats() {
        console.log('🧹 Limpiando TODOS los chats (amigos y equipos)');
        
        // ⭐ DETENER polling
        this.stopPolling();
        
        // ⭐ LIMPIAR variables de estado
        this.currentConversationId = null;
        this.currentUserName = '';
        this.isTeamChat = false;
        this.currentTeamId = null;
        
        // ⭐ ELIMINAR TODOS los contenedores de chat
        const existingPrivateChats = document.querySelectorAll('[id^="chatMessagesContainer"]');
        const existingTeamChats = document.querySelectorAll('[id^="chatTeamMessagesContainer"]');
        
        existingPrivateChats.forEach(chat => {
            console.log('🗑️ Eliminando chat privado');
            chat.remove();
        });
        
        existingTeamChats.forEach(chat => {
            console.log('🗑️ Eliminando chat de equipo');
            chat.remove();
        });
        
        // ⭐ RESETEAR panel de chat a estado inicial
        this.resetChatPanel();
        
        console.log('✅ Limpieza completa realizada');
    }

    resetChatPanel() {
        let chatContainer = null;
        const allCards = document.querySelectorAll('.dashboard-card');
        
        for (let card of allCards) {
            const h2 = card.querySelector('h2');
            if (h2 && (h2.textContent.includes('Chat con') || 
                       h2.textContent.includes('Grupo:') || 
                       h2.textContent.trim() === 'CHAT')) {
                chatContainer = card;
                break;
            }
        }
        
        if (chatContainer) {
            chatContainer.innerHTML = `
                <h2>CHAT</h2>
                <div class="text-center text-muted">
                    <i class="fas fa-comments fa-3x mb-3"></i>
                    <p>Selecciona un amigo o equipo para iniciar una conversación</p>
                    <small>Sistema de chat MongoDB implementado</small>
                </div>
            `;
            console.log('✅ Panel de chat reseteado');
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.gameOnChatMongo = new GameOnChatMongo();
});

window.iniciarChatMongoDB = function(userId, userName) {
    console.log('Iniciando chat MongoDB con:', userId, userName);
    if (window.gameOnChatMongo) {
        window.gameOnChatMongo.startConversation(userId, userName);
    }
};