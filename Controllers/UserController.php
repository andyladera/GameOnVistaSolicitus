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

        $deportes = [
            1 => "Fútbol", 2 => "Básquet", 3 => "Tenis", 4 => "Padel",
            5 => "Natación", 6 => "Crossfit", 7 => "Running", 8 => "Ciclismo"
        ];

        $niveles = [
            'principiante' => "Principiante",
            'intermedio' => "Intermedio",
            'avanzado' => "Avanzado"
        ];

        $dias_semana = [
            'lunes' => "Lunes", 'martes' => "Martes", 'miércoles' => "Miércoles",
            'jueves' => "Jueves", 'viernes' => "Viernes", 'sábado' => "Sábado", 'domingo' => "Domingo"
        ];

        $franjas_horarias = [
            'mañana' => "Mañana (6:00 - 12:00)",
            'tarde' => "Tarde (12:00 - 18:00)",
            'noche' => "Noche (18:00 - 00:00)"
        ];

        $form_data = [
            'nombre' => '', 'apellidos' => '', 'email' => '', 'username' => '', 'telefono' => '',
            'fecha_nacimiento' => '', 'genero' => '', 'nivel_habilidad' => '', 'deportes_favoritos' => [],
            'disponibilidad' => []
        ];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            foreach ($form_data as $key => $_) {
                if (isset($_POST[$key])) {
                    $form_data[$key] = is_array($_POST[$key]) ? $_POST[$key] : trim($_POST[$key]);
                }
            }

            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if ($password !== $confirm_password) {
                $error_message = "Las contraseñas no coinciden.";
            } else {
                $resultado = $this->model->registrarDeportista(
                    array_merge($form_data, ['password' => $password])
                );

                if (isset($resultado['error'])) {
                    $error_message = $resultado['error'];
                } else {
                    $success_message = "Registro exitoso. Ya puedes iniciar sesión.";
                    $form_data = array_map(fn($v) => is_array($v) ? [] : '', $form_data);
                }
            }
        }

        // Retornar los datos necesarios a la vista
        return [
            'form_data' => $form_data,
            'error_message' => $error_message,
            'success_message' => $success_message,
            'deportes' => $deportes,
            'niveles' => $niveles,
            'dias_semana' => $dias_semana,
            'franjas_horarias' => $franjas_horarias
        ];
    }
}