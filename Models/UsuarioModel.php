<?php
require_once __DIR__ . '/../Config/database.php';

class UsuarioModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function obtenerUsuarioPorUsername($username, $user_type) {
        try {
            if ($user_type === 'deportista') {
                $sql = "SELECT id, username, password, estado FROM usuarios_deportistas WHERE username = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("s", $username);
            } else if ($user_type === 'instalacion') {
                $sql = "SELECT id, username, password, estado FROM usuarios_instalaciones WHERE username = ? AND tipo_usuario = 'privado'";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("s", $username);
            } else {
                return false;
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $usuario = $result->fetch_assoc();
            $stmt->close();
            
            // ✅ DEBUG: Agregar log para ver qué pasa
            error_log("🔍 USUARIO ENCONTRADO: " . ($usuario ? "SÍ - ID: " . $usuario['id'] : "NO") . " para username: $username");
            
            return $usuario;
            
        } catch (Exception $e) {
            error_log("❌ ERROR en obtenerUsuarioPorUsername: " . $e->getMessage());
            return false;
        }
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