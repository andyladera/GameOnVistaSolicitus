<?php
// header.php - Cabecera reutilizable para todas las páginas del dashboard
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GameOn Network</title>
    <?php
    $current_page = basename($_SERVER['PHP_SELF'], '.php');
    if ($current_page !== 'torneos' && $current_page !== 'reservas') {
        echo '<link rel="stylesheet" href="../../Public/css/dashboard.css">';
    }
    ?>
    <link rel="stylesheet" href="../../Public/css/horarios_modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="main-header">
        <div class="logo">
            <img src="../../Resources/logo_ipd_2.png" alt="GameOn Red">
            <img src="../../Resources/logo_gameon.png" alt="GameOn Red">
        </div>
        <nav class="main-nav">
            <ul>
                <li><a href="dashboard.php">DASHBOARD</a></li>
                <li><a href="insdepor.php">INSTALACIONES DEPORTIVAS</a></li>
                <li><a href="torneos.php">TORNEOS</a></li>
                <li><a href="misequipos.php">AMIGOS Y EQUIPOS</a></li>
                <li><a href="reservas.php">RESERVAS</a></li>
            </ul>
        </nav>
        <div class="header-user">
            <div class="user-info">
                <span class="user-welcome">¡Hola, <?php echo $_SESSION['username']; ?>!</span>
            </div>
            <div class="logout-container">
                <a href="../Auth/logout.php" class="logout-btn" onclick="return confirm('¿Estás seguro que deseas cerrar sesión?')">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Cerrar Sesión</span>
                </a>
            </div>
        </div>
    </header>
    <main class="main-content">