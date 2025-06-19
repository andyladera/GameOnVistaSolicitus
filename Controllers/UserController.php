<?php
require_once __DIR__ . '/../Models/UsuarioModel.php';

class UserController {
    private $model;

    public function __construct() {
        $this->model = new UsuarioModel();
    }

    public function registroDeportista() {
        $error_message = '';
        $success_message = '';

        $deportes = [1 => "Fútbol", 2 => "Vóley", 3 => "Básquet"];
        $niveles = ['principiante' => "Principiante", 'intermedio' => "Intermedio", 'avanzado' => "Avanzado"];
        $dias_semana = ['lunes' => "Lunes", 'martes' => "Martes", 'miércoles' => "Miércoles", 'jueves' => "Jueves", 'viernes' => "Viernes", 'sábado' => "Sábado", 'domingo' => "Domingo"];
        $franjas_horarias = ['mañana' => "Mañana (6:00 - 12:00)", 'tarde' => "Tarde (12:00 - 18:00)", 'noche' => "Noche (18:00 - 00:00)"];

        $form_data = ['nombre' => '', 'apellidos' => '', 'email' => '', 'username' => '', 'telefono' => '', 'fecha_nacimiento' => '', 'genero' => '', 'nivel_habilidad' => '', 'deportes_favoritos' => [], 'disponibilidad' => []];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            foreach ($form_data as $key => $_) {
                if (isset($_POST[$key])) {
                    $form_data[$key] = is_array($_POST[$key]) ? $_POST[$key] : trim($_POST[$key]);
                }
            }

            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($form_data['nombre']) || empty($form_data['apellidos']) || empty($form_data['email']) || empty($form_data['username'])) {
                $error_message = "Todos los campos obligatorios deben estar completos.";
            } elseif ($password !== $confirm_password) {
                $error_message = "Las contraseñas no coinciden.";
            } elseif (strlen($password) < 6) {
                $error_message = "La contraseña debe tener al menos 6 caracteres.";
            } elseif ($this->model->usernameExiste($form_data['username'])) {
                $error_message = "El nombre de usuario ya está en uso.";
            } elseif ($this->model->emailExiste($form_data['email'])) {
                $error_message = "El email ya está registrado.";
            } else {
                $resultado = $this->model->registrarDeportista(array_merge($form_data, ['password' => $password]));

                if (isset($resultado['error'])) {
                    $error_message = $resultado['error'];
                } else {
                    $success_message = "¡Registro exitoso! Ya puedes iniciar sesión.";
                    $form_data = array_map(fn($v) => is_array($v) ? [] : '', $form_data);
                }
            }
        }

        return ['form_data' => $form_data, 'error_message' => $error_message, 'success_message' => $success_message, 'deportes' => $deportes, 'niveles' => $niveles, 'dias_semana' => $dias_semana, 'franjas_horarias' => $franjas_horarias];
    }
}
?>