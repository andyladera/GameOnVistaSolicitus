<?php
// footer.php - Pie de página reutilizable para todas las páginas del dashboard
?>
    </main>
    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-info">
                <h3>Acerca de GameOn Network</h3>
                <p>GameOn Network conecta a los amantes de los deportes con instalaciones y eventos en tu zona, ofreciéndote una experiencia única.</p>
            </div>
            <div class="social-links">
                <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </footer>
    <!-- Incluir Font Awesome para los iconos sociales -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="../../Public/js/horarios_modal.js"></script>
    
    <!-- ✅ CARGAR CHATMONGO ESPECÍFICAMENTE PARA MISEQUIPOS -->
    <?php 
    $current_page = basename($_SERVER['PHP_SELF'], '.php');
    if ($current_page === 'misequipos'): 
    ?>
    <script src="../../Public/js/chatmongo.js"></script>
    <script>
        console.log('✅ ChatMongo.js cargado para misequipos');
    </script>
    <?php endif; ?>
    
    <!-- ✅ CARGAR CHAT.JS CON VERIFICACIÓN MEJORADA -->
    <script>
        // Solo cargar chat si está habilitado o es la página de misequipos
        const currentPath = window.location.pathname;
        const isMinsequiposPage = currentPath.includes('misequipos');
        
        if (window.chatEnabled || isMinsequiposPage) {
            console.log('🔄 Intentando cargar chat.js...');
            
            if (!window.ChatManager && !window.chatManager && !window.chatLoaded) {
                const chatScript = document.createElement('script');
                chatScript.src = '../../Public/js/chat.js';
                chatScript.onload = function() {
                    window.chatLoaded = true;
                    console.log('✅ Chat.js cargado exitosamente');
                    
                    // ✅ VERIFICAR QUE AMBOS SISTEMAS ESTÉN DISPONIBLES
                    setTimeout(() => {
                        if (window.gameOnChatMongo) {
                            console.log('✅ MongoDB Chat disponible');
                        } else {
                            console.error('❌ MongoDB Chat NO disponible');
                        }
                        
                        // ✅ VERIFICAR AMBAS VARIACIONES
                        if (window.chatManager || window.ChatManager) {
                            console.log('✅ ChatManager disponible');
                        } else {
                            console.error('❌ ChatManager NO disponible');
                            console.log('🔄 Intentando crear manualmente...');
                            // ✅ CREAR MANUALMENTE SI NO EXISTE
                            if (typeof ChatManager !== 'undefined') {
                                window.chatManager = new ChatManager();
                                console.log('✅ ChatManager creado manualmente');
                            }
                        }
                    }, 500);
                };
                chatScript.onerror = function() {
                    console.error('❌ Error cargando chat.js');
                };
                document.body.appendChild(chatScript);
            } else {
                console.log('✅ Chat ya está cargado o ChatManager existe');
            }
        } else {
            console.log('ℹ️ Chat no habilitado para esta página');
        }
    </script>
</body>
</html>