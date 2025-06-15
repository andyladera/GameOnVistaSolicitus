<?php
require_once __DIR__ . '/../Config/database.php';

class UsuarioModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function obtenerUsuarioPorUsername($username, $user_type) {
        if ($user_type === 'deportista') {
            $table = 'usuarios_deportistas';
            $stmt = $this->conn->prepare("SELECT id, username, password, estado FROM $table WHERE username = ?");
        } else if ($user_type === 'instalacion') {
            $table = 'usuarios_instalaciones';
            // SOLO instalaciones privadas (excluir IPD)
            $stmt = $this->conn->prepare("SELECT id, username, password, estado FROM $table WHERE username = ? AND tipo_usuario = 'privado'");
        } else {
            return false;
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();

        $stmt->close();
        return $usuario;
    }

    public function registrarDeportista($data) {
        $stmt = $this->conn->prepare("INSERT INTO usuarios_deportistas (
            nombre, apellidos, email, username, password, telefono, fecha_nacimiento, genero, nivel_habilidad, estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'activo')");

        $password_hashed = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt->bind_param(
            "sssssssss",
            $data['nombre'],
            $data['apellidos'],
            $data['email'],
            $data['username'],
            $password_hashed,
            $data['telefono'],
            $data['fecha_nacimiento'],
            $data['genero'],
            $data['nivel_habilidad']
        );

        if (!$stmt->execute()) {
            return ['error' => $stmt->error];
        }

        $usuario_id = $stmt->insert_id;
        $stmt->close();

        // Deportes favoritos
        if (!empty($data['deportes_favoritos'])) {
            foreach ($data['deportes_favoritos'] as $deporte_id) {
                $insertDeporte = $this->conn->prepare("INSERT INTO usuarios_deportes (usuario_id, deporte_id) VALUES (?, ?)");
                $insertDeporte->bind_param("ii", $usuario_id, $deporte_id);
                $insertDeporte->execute();
                $insertDeporte->close();
            }
        }

        // Disponibilidad
        if (!empty($data['disponibilidad'])) {
            foreach ($data['disponibilidad'] as $dia => $franjas) {
                foreach ($franjas as $franja) {
                    $insertDisponibilidad = $this->conn->prepare("INSERT INTO usuarios_disponibilidad (usuario_id, disponibilidad_id) VALUES (?, ?)");
                    $insertDisponibilidad->bind_param("ii", $usuario_id, $franja);
                    $insertDisponibilidad->execute();
                    $insertDisponibilidad->close();
                }
            }
        }

        return ['success' => true];
    }

    public function registrarInstalacion($data) {
        $stmt = $this->conn->prepare("INSERT INTO usuarios_instalaciones (
            username, password, tipo_usuario, estado
        ) VALUES (?, ?, ?, 1)");

        $password_hashed = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt->bind_param(
            "sss",
            $data['username'],
            $password_hashed,
            $data['tipo_usuario'] ?? 'privado'
        );

        if (!$stmt->execute()) {
            return ['error' => $stmt->error];
        }

        $usuario_id = $stmt->insert_id;
        $stmt->close();

        return ['success' => true, 'usuario_id' => $usuario_id];
    }
}
?>