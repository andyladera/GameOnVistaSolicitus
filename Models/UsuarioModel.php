<?php
require_once __DIR__ . '/../Config/database.php';

class UsuarioModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function obtenerUsuarioPorUsername($username, $user_type) {
        $table = '';
        $extra_condition = '';
        
        if ($user_type === 'deportista') {
            $table = 'usuarios_deportistas';
        } else if ($user_type === 'instalacion') {
            $table = 'usuarios_instalaciones';
            $extra_condition = " AND tipo_usuario = 'privado'";
        } else {
            return false;
        }

        $sql = "SELECT id, username, password, estado FROM $table WHERE username = ?$extra_condition";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$username]);
        
        return $stmt->fetch(); // PDO fetch() en lugar de fetch_assoc()
    }

    public function registrarDeportista($data) {
        try {
            $sql = "INSERT INTO usuarios_deportistas (nombre, apellidos, email, telefono, fecha_nacimiento, genero, nivel_habilidad, username, password) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                $data['nombre'],
                $data['apellidos'],
                $data['email'],
                $data['telefono'],
                $data['fecha_nacimiento'],
                $data['genero'],
                $data['nivel_habilidad'],
                $data['username'],
                password_hash($data['password'], PASSWORD_DEFAULT)
            ]);
            
            if ($result) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            throw new RuntimeException('Error al registrar deportista: ' . $e->getMessage());
        }
    }

    public function registrarInstalacion($data) {
        try {
            $sql = "INSERT INTO usuarios_instalaciones (username, password, tipo_usuario) VALUES (?, ?, 'privado')";
            
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                $data['username'],
                password_hash($data['password'], PASSWORD_DEFAULT)
            ]);
            
            if ($result) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            throw new RuntimeException('Error al registrar instalación: ' . $e->getMessage());
        }
    }

    public function usernameExiste($username, $user_type) {
        $table = ($user_type === 'deportista') ? 'usuarios_deportistas' : 'usuarios_instalaciones';
        
        $sql = "SELECT COUNT(*) FROM $table WHERE username = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$username]);
        
        return $stmt->fetchColumn() > 0;
    }

    public function emailExiste($email) {
        $sql = "SELECT COUNT(*) FROM usuarios_deportistas WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email]);
        
        return $stmt->fetchColumn() > 0;
    }
}
?>