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
        <!-- ✅ LADO IZQUIERDO CON DISEÑO MEJORADO -->
        <div class="auth-image">
            <div class="welcome-text">
                <h1>⚽🏀🏐🏸🥋🥇🥈🥉🏆⚽</h1>
                <h1>Únete a la Comunidad Deportiva Líder.</h1>
                <h1>⚽🏀🏐🏸🥋🥇🥈🥉🏆⚽</h1>
                <p>
                    En GameOn Network, conectamos tu pasión por el deporte con las mejores instalaciones de Tacna. Encuentra, reserva y juega como nunca antes.
                </p>
                <p>
                    Forma parte de un movimiento que impulsa la salud, la motivación y la competencia sana.
                </p>
            </div>
            
            <!-- ✅ SECCIÓN DE PATROCINADORES REUBICADA -->
            <div class="sponsors-section">
                <h2>Patrocinador Oficial:</h2>
                <img src="../../Resources/logo_ipd_2.png" alt="Logo IPD">
            </div>
        </div>
        
        <!-- Lado derecho con formulario (sin cambios en la estructura) -->
        <div class="auth-container">
            <div class="form-container">
                <h2>Registro de Deportista</h2>
                <p>Completa tus datos para empezar a jugar.</p>

                <?php if (!empty($error_message)): ?>
                    <div class="alert error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                    <div class="alert success"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre *</label>
                            <input id="nombre" type="text" name="nombre" value="<?php echo htmlspecialchars($form_data['nombre']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="apellidos">Apellidos *</label>
                            <input id="apellidos" type="text" name="apellidos" value="<?php echo htmlspecialchars($form_data['apellidos']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input id="email" type="email" name="email" value="<?php echo htmlspecialchars($form_data['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input id="telefono" type="text" name="telefono" value="<?php echo htmlspecialchars($form_data['telefono']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha_nacimiento">Fecha de nacimiento</label>
                            <input id="fecha_nacimiento" type="date" name="fecha_nacimiento" value="<?php echo htmlspecialchars($form_data['fecha_nacimiento']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="genero">Género</label>
                            <select id="genero" name="genero">
                                <option value="">Seleccionar</option>
                                <option value="masculino" <?php echo ($form_data['genero'] == 'masculino') ? 'selected' : ''; ?>>Masculino</option>
                                <option value="femenino" <?php echo ($form_data['genero'] == 'femenino') ? 'selected' : ''; ?>>Femenino</option>
                                <option value="otro" <?php echo ($form_data['genero'] == 'otro') ? 'selected' : ''; ?>>Otro</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Nombre de usuario *</label>
                        <input id="username" type="text" name="username" value="<?php echo htmlspecialchars($form_data['username']); ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Contraseña *</label>
                            <input id="password" type="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirmar contraseña *</label>
                            <input id="confirm_password" type="password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-submit">Crear mi Cuenta</button>
                    </div>
                    <p class="login-link">¿Ya tienes cuenta? <a href="../Auth/login.php">Inicia sesión</a></p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>