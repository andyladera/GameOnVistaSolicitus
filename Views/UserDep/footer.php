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
    
    <!-- Cargar chat según la configuración -->
    <script>
        // Solo cargar chat si está habilitado o no está específicamente deshabilitado
        if (window.chatEnabled || !window.chatDisabled) {
            // Evitar cargar chat.js si ya está cargado
            if (!window.chatLoaded) {
                const chatScript = document.createElement('script');
                chatScript.src = '../../Public/js/chat.js';
                chatScript.onload = function() {
                    window.chatLoaded = true;
                };
                document.body.appendChild(chatScript);
            }
        }
    </script>
</body>
</html>