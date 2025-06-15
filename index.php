<?php
// Iniciar la sesión
session_start();

// Incluir archivos de configuración
require_once 'Config/database.php';

// Definir la ruta base
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/gameon/');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameOn Network - Conectando deportistas e instalaciones deportivas</title>
    <link rel="stylesheet" href="Public/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Sección de video hero -->
    <div class="video-container">
        <video autoplay muted loop id="hero-video">
            <source src="Resources/video1.mp4" type="video/mp4">
            Tu navegador no soporta el tag de video.
        </video>
        <div class="overlay"></div>
        <div class="hero-content">
            <h1>Sistema de recomendaciones de Instalaciones Deportivas con Geolocalización e IA del IPD</h1>
            <p>Conectando deportistas con los mejores espacios deportivos</p>
            <div class="hero-buttons">
                <a href="Views/Auth/login.php" class="btn btn-primary">Iniciar Sesión</a>
                <a href="#about" class="btn btn-secondary">Conócenos</a>
            </div>
        </div>
    </div>

    <!-- Sección sobre nosotros -->
    <section id="about" class="about-section">
        <div class="container">
            <h2>¿Qué es el Sistema de recomendaciones de Instalaciones Deportivas con Geolocalización e IA del IPD-GameOn Network?</h2>
            <div class="about-content">
                <div class="about-text">
                    <p>GameOn Network es la plataforma revolucionaria diseñada para conectar a deportistas con las instalaciones deportivas perfectas para sus necesidades. Facilitamos el acceso al deporte y la actividad física en Tacna, creando una comunidad deportiva conectada y activa.</p>
                    <p>Nuestra misión es fomentar un estilo de vida saludable haciendo que la práctica deportiva sea más accesible, organizada y divertida para todos.</p>
                </div>
                <div class="about-image">
                    <img src="Resources/logo_ipd.png" alt="Logo IPD" style="width: 400px; height: auto;">
                </div>
            </div>
        </div>
    </section>

    <!-- Sección de beneficios -->
    <section class="benefits-section">
        <div class="container">
            <h2>¿Por qué elegir Sistema de recomendaciones de Instalaciones Deportivas con Geolocalización e IA del IPD-GameOn Network?</h2>
            <div class="benefits-grid">
                <div class="benefit-card">
                    <i class="fas fa-search-location"></i>
                    <h3>Encuentra tu espacio ideal</h3>
                    <p>Localiza fácilmente instalaciones deportivas cercanas con nuestro sistema de geolocalización integrado con Google Maps.</p>
                </div>
                <div class="benefit-card">
                    <i class="fas fa-calendar-check"></i>
                    <h3>Reserva en tiempo real</h3>
                    <p>Asegura tu espacio deportivo favorito con nuestro sistema de reservas instantáneas y olvídate de las llamadas telefónicas.</p>
                </div>
                <div class="benefit-card">
                    <i class="fas fa-users"></i>
                    <h3>Conecta con otros deportistas</h3>
                    <p>Encuentra compañeros de juego con tus mismos intereses y nivel, y disfruta más de tu deporte favorito.</p>
                </div>
                <div class="benefit-card">
                    <i class="fas fa-trophy"></i>
                    <h3>Participa en eventos</h3>
                    <p>Descubre y únete a competencias locales, ligas y torneos organizados por nuestra comunidad.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección para propietarios -->
    <section class="owners-section">
        <div class="container">
            <h2>¿Tienes una instalación deportiva?</h2>
            <div class="owners-content">
                <div class="owners-text">
                    <p>GameOn Network es la plataforma ideal para dar visibilidad a tu espacio deportivo. Optimiza la ocupación de tus instalaciones, gestiona reservas eficientemente y accede a análisis detallados del rendimiento de tu negocio.</p>
                    <a href="Views/UserInsD/registroinsd.php" class="btn btn-accent">Registra tu instalación</a>
                </div>
                <div class="owners-image">
                    <i class="fas fa-chart-line owners-icon"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección de call-to-action -->
    <section class="cta-section">
        <div class="container">
            <h2>¡Únete a la revolución deportiva!</h2>
            <p>Registrate ahora y comienza a disfrutar de todos los beneficios que GameOn Network tiene para ti.</p>
            <div class="cta-buttons">
                <a href="Views/UserDep/registrousuario.php" class="btn btn-large">Registrarse como deportista</a>
                <a href="Views/UserInsD/registroinsd.php" class="btn btn-large btn-outline">Registrar instalación deportiva</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h3>IPD - GameOn Network</h3>
                    <p>Conectando deportistas e instalaciones</p>
                </div>
                <div class="footer-links">
                    <h4>Enlaces rápidos</h4>
                    <ul>
                        <li><a href="#">Inicio</a></li>
                        <li><a href="#about">Sobre nosotros</a></li>
                        <li><a href="Views/Auth/login.php">Iniciar sesión</a></li>
                        <li><a href="Views/UserDep/registrousuario.php">Registrarse</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Contáctanos</h4>
                    <p><i class="fas fa-envelope"></i> info@gameonnetwork.com</p>
                    <p><i class="fas fa-phone"></i> +51 952 123 456</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> GameOn Network. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="Public/js/main.js"></script>
</body>
</html>