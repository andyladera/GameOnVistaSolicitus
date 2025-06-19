<?php
session_start();
require_once __DIR__ . '/../Models/UsuarioModel.php';

class AuthController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new UsuarioModel();
    }

    public function login($username, $password, $user_type) {
        try {
            $usuario = $this->usuarioModel->obtenerUsuarioPorUsername($username, $user_type);

            if (!$usuario) {
                return "Credenciales incorrectas.";
            }

            if (!password_verify($password, $usuario['password'])) {
                return "Contraseña incorrecta.";
            }

            if ($usuario['estado'] != 1) {
                return "Tu cuenta no está activa.";
            }

            // Autenticación exitosa: iniciar sesión
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['username'] = $usuario['username'];
            $_SESSION['user_type'] = $user_type;

            // ✅ USAR LAS MISMAS RUTAS QUE FUNCIONAN PARA INSTALACIONES
            switch ($user_type) {
                case 'deportista':
                    // Forzar creación de carpeta en Azure (temporal)
                    $userDepPath = __DIR__ . '/../Views/UserDep';
                    if (!is_dir($userDepPath)) {
                        mkdir($userDepPath, 0755, true);
                        file_put_contents($userDepPath . '/dashboard.php', file_get_contents(__DIR__ . '/../Views/UserInsD/dashboard.php'));
                        file_put_contents($userDepPath . '/header.php', file_get_contents(__DIR__ . '/../Views/UserInsD/header.php'));
                        file_put_contents($userDepPath . '/footer.php', file_get_contents(__DIR__ . '/../Views/UserInsD/footer.php'));
                    }
                    header("Location: ../UserDep/dashboard.php");
                    break;
                    
                case 'instalacion':
                    // ✅ ESTA RUTA YA FUNCIONA
                    header("Location: ../UserInsD/dashboard.php");
                    break;
                    
                default:
                    return "Tipo de usuario no válido.";
            }
            
            exit();
            
        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            return "Error interno del sistema. Por favor, inténtalo de nuevo.";
        }
    }
}
?>