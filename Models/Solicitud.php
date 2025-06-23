<?php
require_once __DIR__ . '/../Config/database.php';

class Solicitud {
    private $conn;
    private $table_name = 'solicitudes_registro';
    public $error; // Para almacenar mensajes de error específicos

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtiene todas las solicitudes, opcionalmente filtradas por estado.
     */
    public function obtenerTodas($estado = null) {
        $query = "SELECT id, nombre_institucion, ruc, email, documento_path, estado, fecha_solicitud FROM " . $this->table_name;
        if ($estado) {
            $query .= " WHERE estado = ?";
        }
        $query .= " ORDER BY fecha_solicitud DESC";

        $stmt = $this->conn->prepare($query);

        if ($estado) {
            $stmt->bind_param("s", $estado);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene los datos de una solicitud específica por su ID.
     */
    public function obtenerPorId($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Aprueba una solicitud y crea el usuario correspondiente.
     * Devuelve el ID del nuevo usuario creado o false si falla.
     */
    public function aprobar($solicitud_id, $admin_id) {
        $solicitud = $this->obtenerPorId($solicitud_id);
        if (!$solicitud || $solicitud['estado'] !== 'pendiente') {
            return false; // No se puede aprobar si no está pendiente
        }

        // Iniciar transacción para asegurar la integridad de los datos
        $this->conn->begin_transaction();

        try {
            // 1. Crear el usuario en la tabla `usuarios_instalaciones`
            $stmt_user = $this->conn->prepare(
                "INSERT INTO usuarios_instalaciones (username, password, tipo_usuario) VALUES (?, ?, 'privado')"
            );
            $stmt_user->bind_param("ss", $solicitud['email'], $solicitud['password']);
            
            if (!$stmt_user->execute()) {
                $this->conn->rollback();
                throw new Exception("Error al crear el usuario. El email ya podría estar registrado como nombre de usuario.");
            }
            
            $new_user_id = $this->conn->insert_id;
            $stmt_user->close();

            if ($new_user_id == 0) {
                $this->conn->rollback();
                throw new Exception("La creación del usuario falló silenciosamente. No se obtuvo un ID de usuario.");
            }

            // 2. Crear la institución en la tabla `instituciones_deportivas`
            $stmt_inst = $this->conn->prepare(
                "INSERT INTO instituciones_deportivas (usuario_instalacion_id, nombre, ruc, email, estado) VALUES (?, ?, ?, ?, 'activa')"
            );
            $stmt_inst->bind_param("isss", $new_user_id, $solicitud['nombre_institucion'], $solicitud['ruc'], $solicitud['email']);
            $stmt_inst->execute();
            $stmt_inst->close();

            // 3. Actualizar el estado de la solicitud
            $stmt_sol = $this->conn->prepare(
                "UPDATE " . $this->table_name . " SET estado = 'aprobada', revisado_por = ? WHERE id = ?"
            );
            $stmt_sol->bind_param("ii", $admin_id, $solicitud_id);
            $stmt_sol->execute();
            $stmt_sol->close();

            // Si todo fue bien, confirmar la transacción
            $this->conn->commit();
            return $new_user_id;

        } catch (mysqli_sql_exception $e) {
            $this->conn->rollback();
            // Error 1062 es para 'Duplicate entry'
            if ($e->getCode() == 1062) {
                if (strpos($e->getMessage(), 'username') !== false) {
                    $this->error = "Error: Ya existe un usuario con el email '{$solicitud['email']}'.";
                } elseif (strpos($e->getMessage(), 'ruc') !== false) {
                    $this->error = "Error: Ya existe una institución con el RUC '{$solicitud['ruc']}'.";
                } else {
                    $this->error = "Error: Conflicto de datos duplicados.";
                }
            } else {
                $this->error = "Error de base de datos: " . $e->getMessage();
            }
            return false;
        } catch (Exception $e) {
            $this->conn->rollback();
            $this->error = "Error inesperado: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Rechaza una solicitud y guarda el motivo.
     */
    public function rechazar($solicitud_id, $admin_id, $motivo) {
        $query = "UPDATE " . $this->table_name . " SET estado = 'rechazada', motivo_rechazo = ?, revisado_por = ? WHERE id = ? AND estado = 'pendiente'";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sii", $motivo, $admin_id, $solicitud_id);
        
        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        }
        return false;
    }
}
?>
