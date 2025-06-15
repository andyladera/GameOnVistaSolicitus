<?php
class Database {
    private $host = "localhost";
    private $db_name = "gameon";
    private $username = "root";
    private $password = "";
    private $conn;
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            
            // Verificar la conexión
            if ($this->conn->connect_error) {
                throw new Exception("Error de conexión: " . $this->conn->connect_error);
            }
            
            // Establecer el conjunto de caracteres a utf8
            $this->conn->set_charset("utf8");
            
        } catch(Exception $e) {
            echo "Error de conexión: " . $e->getMessage();
        }

        return $this->conn;
    }
    
    // Método para cerrar la conexión
    public function closeConnection() {
        if ($this->conn != null) {
            $this->conn->close();
        }
    }
}
?>