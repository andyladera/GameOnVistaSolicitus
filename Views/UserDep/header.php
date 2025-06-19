<?php
// Views/UserDep/header.php - COPIADO DE LA ESTRUCTURA QUE FUNCIONA
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Deportista - GameOn Network</title>
    
    <!-- ✅ USAR LOS CSS QUE YA EXISTEN -->
    <link rel="stylesheet" href="../../Public/css/header_dep.css">
    <link rel="stylesheet" href="../../Public/css/dashboard_dep.css">
    <link rel="stylesheet" href="../../Public/css/footer_dep.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="main-header">
        <div class="logo">
            <img src="../../Resources/logo_ipd_2.png" alt="GameOn Network">
            <span class="logo-text">Panel Deportista</span>
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
                <span class="user-type">Deportista</span>
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