<?php
require_once '../../Config/database.php';

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Recoger y sanear los datos del formulario
    $nombre_institucion = trim($_POST['nombre']);
    $ruc = trim($_POST['ruc']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 2. Validar los datos
    if (empty($nombre_institucion) || empty($ruc) || empty($email) || empty($password) || empty($_FILES['documento']['name'])) {
        $error = "Por favor, complete todos los campos obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El formato del correo electrónico no es válido.";
    } elseif (!preg_match('/^\d{11}$/', $ruc)) {
        $error = "El RUC debe contener exactamente 11 dígitos.";
    } else {
        $db = new Database();
        $conn = $db->getConnection();

        // Verificar si el email o RUC ya existen en solicitudes pendientes o usuarios aprobados
        $stmt_check = $conn->prepare("SELECT id FROM solicitudes_registro WHERE email = ? OR ruc = ?");
        $stmt_check->bind_param("ss", $email, $ruc);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error = "Ya existe una solicitud con este correo electrónico o RUC.";
        } else {
            // 3. Hashear la contraseña
            $password_hashed = password_hash($password, PASSWORD_BCRYPT);

            // 4. Gestionar la subida del archivo
            $target_dir = "../../documentos_solicitudes/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            $file_extension = pathinfo($_FILES["documento"]["name"], PATHINFO_EXTENSION);
            $new_filename = 'doc_' . $ruc . '_' . time() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;

            if ($file_extension != 'pdf') {
                $error = "Solo se permiten archivos en formato PDF.";
            } elseif (move_uploaded_file($_FILES["documento"]["tmp_name"], $target_file)) {
                // 5. Insertar en la base de datos
                $stmt = $conn->prepare("INSERT INTO solicitudes_registro (nombre_institucion, ruc, email, password, documento_path) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $nombre_institucion, $ruc, $email, $password_hashed, $target_file);

                if ($stmt->execute()) {
                    $message = "¡Gracias! Tu solicitud de registro ha sido enviada con éxito. Recibirás una notificación por correo electrónico una vez que sea revisada.";
                } else {
                    $error = "Error al registrar la solicitud. Por favor, inténtelo de nuevo.";
                    // Opcional: eliminar el archivo si la inserción falla
                    if (file_exists($target_file)) {
                        unlink($target_file);
                    }
                }
                $stmt->close();
            } else {
                $error = "Hubo un error al subir el documento.";
            }
        }
        $stmt_check->close();
        $db->closeConnection();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de Registro</title>
    <link rel="stylesheet" href="../../Public/css/styles_registroinsdepor.css">
    <style>
        .status-container {
            text-align: center;
            padding: 40px;
            margin-top: 50px;
        }
        .status-message {
            font-size: 1.2em;
            margin-bottom: 20px;
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
    </style>
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="status-container">
            <?php if (!empty($message)): ?>
                <h2 class="success">¡Solicitud Enviada!</h2>
                <p class="status-message success"><?php echo htmlspecialchars($message); ?></p>
                <a href="../../index.php" class="btn btn-primary">Volver al Inicio</a>
            <?php elseif (!empty($error)): ?>
                <h2 class="error">Error en el Registro</h2>
                <p class="status-message error"><?php echo htmlspecialchars($error); ?></p>
                <a href="registroinsdepor.php" class="btn btn-secondary">Volver a Intentar</a>
            <?php else: ?>
                <p>No se ha enviado ningún formulario.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
