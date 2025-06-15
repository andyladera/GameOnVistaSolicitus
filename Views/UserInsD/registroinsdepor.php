<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro - Institución Deportiva</title>
  <link rel="stylesheet" href="../../Public/css/styles_registroinsdepor.css">
</head>
<body class="auth-page">
  <div class="auth-container dual-column">
    
    <div class="auth-info">
      <h2>Información Importante</h2>
      <p>
        Bienvenido al proceso de registro para Propietarios de Instalaciones Deportivas en <strong>GameOn Network</strong>.
      </p>
      <p>
        Esta sección está diseñada exclusivamente para instituciones deportivas que desean formar parte de nuestra plataforma. 
        Para completar el registro, deberás adjuntar un documento legal en formato PDF que respalde tu actividad.
      </p>
      <p>
        Una vez enviado el formulario, tu solicitud será evaluada por un miembro del equipo en un plazo de hasta <strong>3 días hábiles</strong>. 
        Recibirás un correo electrónico desde nuestra cuenta oficial de Gmail indicando si tu solicitud fue aprobada o si requiere modificaciones.
      </p>
      <p>
        En caso de ser aprobada, recibirás los datos de acceso y podrás comenzar a gestionar tus instalaciones, horarios, tarifas y más.
      </p>
      <p><em>¡Gracias por formar parte de la comunidad GameOn Network!</em></p>
    </div>

    <!-- COLUMNA DERECHA: Formulario -->
    <div class="auth-form">
      <div class="auth-header">
        <h2>Registro de Institución Deportiva</h2>
      </div>
      <div class="auth-body">
        <form action="procesar_registroinsdepor.php" method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <label for="nombre">Nombre de la Institución</label>
            <input type="text" name="nombre" id="nombre" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="ruc">RUC</label>
            <input type="text" name="ruc" id="ruc" class="form-control" required pattern="\d{11}" title="Ingrese 11 dígitos">
          </div>

          <div class="form-group">
            <label for="email">Correo electrónico</label>
            <input type="email" name="email" id="email" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" name="password" id="password" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="documento">Subir Documento Legal (PDF)</label>
            <input type="file" name="documento" id="documento" class="form-control" accept=".pdf" required>
          </div>

          <button type="submit" class="btn btn-primary btn-large">Registrarse</button>
        </form>
      </div>
      <div class="auth-footer">
        ¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a>
      </div>
    </div>

  </div>
</body>
</html>