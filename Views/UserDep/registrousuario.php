<?php
require_once __DIR__ . '/../../Controllers/UserController.php';

$controller = new UserController();
$data = $controller->registroDeportista();

$form_data = $data['form_data'];
$form_data['deportes_favoritos'] = $_POST['deportes_favoritos'] ?? [];
$form_data['disponibilidad'] = $_POST['disponibilidad'] ?? [];
$error_message = $data['error_message'];
$success_message = $data['success_message'];
$deportes = $data['deportes'];
$niveles = $data['niveles'];
$dias_semana = $data['dias_semana'];
$franjas_horarias = $data['franjas_horarias'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Deportista - GameOn</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../Public/css/styles_registrousu.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-page">
        <!-- Lado izquierdo con imagen y mensaje -->
        <div class="auth-image">
            <div class="welcome-text">
                <h1>🏆🤺⚽🥎🏀🏐🎳⛸️🏸🎾🏓🥊🥋🏆</h1>
                <h1>¡Estamos felices que te unas al equipo GameOn!</h1>
                <h1>🏆🤺⚽🥎🏀🏐🎳⛸️🏸🎾🏓🥊🥋🏆</h1>
                <p>
                Nos alegra mucho que hayas decidido unirte al equipo GameOn. Aquí no solo encontrarás una plataforma, sino una comunidad apasionada por el deporte y la vida saludable.
                </p>
                <p>
                GameOn Network es mucho más que un simple espacio digital; es un lugar donde deportistas como tú pueden conectar con las instalaciones deportivas ideales en Tacna, facilitando el acceso a la actividad física y fomentando la práctica constante y divertida.
                </p>
                <p>
                Nuestra misión es acompañarte en cada paso hacia un estilo de vida activo y saludable, ofreciéndote herramientas para que el deporte sea accesible, organizado y, sobre todo, una fuente de alegría y bienestar.
                </p>
                <p>
                Estamos comprometidos en construir una comunidad deportiva vibrante y conectada, donde cada usuario pueda encontrar el espacio perfecto para crecer, entrenar y disfrutar. Gracias por confiar en GameOn y ser parte de este movimiento que impulsa la salud, la motivación y la pasión por el deporte.
                </p>
                <p>
                ¡Vamos juntos a alcanzar nuevas metas y hacer del deporte una experiencia única y gratificante para todos!
                </p>
                <h2>Patrocinadores:</h2>
            </div>
            <div class="image-container">
                <img src="../../Resources/logo_ipd.png" alt="Bienvenida a GameOn">
            </div>
        </div>
        
        <!-- Lado derecho con formulario -->
        <div class="auth-container">
            <div class="form-container">
                <h2>Registro de Deportista</h2>
                <p>Únete a GameOn Network y accede a las mejores experiencias deportivas</p>

                <?php if (!empty($error_message)): ?>
                    <div class="alert error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                    <div class="alert success"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nombre *</label>
                            <input type="text" name="nombre" value="<?php echo htmlspecialchars($form_data['nombre']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Apellidos *</label>
                            <input type="text" name="apellidos" value="<?php echo htmlspecialchars($form_data['apellidos']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($form_data['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" value="<?php echo htmlspecialchars($form_data['telefono']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Fecha de nacimiento</label>
                            <input type="date" name="fecha_nacimiento" value="<?php echo htmlspecialchars($form_data['fecha_nacimiento']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Género</label>
                            <select name="genero">
                                <option value="">Seleccionar</option>
                                <option value="masculino" <?php echo ($form_data['genero'] == 'masculino') ? 'selected' : ''; ?>>Masculino</option>
                                <option value="femenino" <?php echo ($form_data['genero'] == 'femenino') ? 'selected' : ''; ?>>Femenino</option>
                                <option value="otro" <?php echo ($form_data['genero'] == 'otro') ? 'selected' : ''; ?>>Otro</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Nombre de usuario *</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($form_data['username']); ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Contraseña *</label>
                            <input type="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label>Confirmar contraseña *</label>
                            <input type="password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-submit">Registrarme</button>
                    </div>
                    <p class="login-link">¿Ya tienes cuenta? <a href="../Auth/login.php">Inicia sesión</a></p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>