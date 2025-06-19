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
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");

        $password_hashed = password_hash($data['password'], PASSWORD_DEFAULT);
        $telefono = !empty($data['telefono']) ? $data['telefono'] : null;
        $fecha_nacimiento = !empty($data['fecha_nacimiento']) ? $data['fecha_nacimiento'] : null;
        $genero = !empty($data['genero']) ? ucfirst($data['genero']) : 'Masculino';
        $nivel_habilidad = !empty($data['nivel_habilidad']) ? ucfirst($data['nivel_habilidad']) : 'Principiante';
        
        $stmt->bind_param(
            "sssssssss",
            $data['nombre'],
            $data['apellidos'],
            $data['email'],
            $data['username'],
            $password_hashed,
            $telefono,
            $fecha_nacimiento,
            $genero,
            $nivel_habilidad
        );

        if (!$stmt->execute()) {
            return ['error' => 'Error al registrar: ' . $stmt->error];
        }

        $usuario_id = $stmt->insert_id;
        $stmt->close();
        return ['success' => true, 'usuario_id' => $usuario_id];
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

    public function usernameExiste($username) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM usuarios_deportistas WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['total'] > 0;
    }

    public function emailExiste($email) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM usuarios_deportistas WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['total'] > 0;
    }
}
?>