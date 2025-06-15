<?php
// Controllers/MongoDBChatController.php

// Verificar si la sesión ya está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Config/mongodb_config.php';

class MongoDBChatController {
    private $mongo;
    
    public function __construct() {
        $this->mongo = MongoDBConnection::getInstance();
    }
    
    public function handleRequest() {
        $action = $_GET['action'] ?? '';
        
        switch($action) {
            case 'test':
                $this->testConnection();
                break;
            case 'start_conversation':
                $this->startConversation();
                break;
            case 'send_message':
                $this->sendMessage();
                break;
            case 'get_messages':
                $this->getMessages();
                break;
            case 'get_conversations':
                $this->getConversations();
                break;
            case 'start_team_conversation':
                $this->startTeamConversation();
                break;
            case 'get_team_messages':
                $this->getTeamMessages();
                break;
            case 'send_team_message':
                $this->sendTeamMessage();
                break;
            default:
                $this->error('Acción no válida');
        }
    }
    
    private function testConnection() {
        try {
            // Verificar autenticación
            if (!isset($_SESSION['user_id'])) {
                $this->error('Usuario no autenticado');
                return;
            }
            
            $this->success([
                'message' => 'MongoDB Controller funcionando correctamente',
                'user_id' => $_SESSION['user_id'],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            $this->error('Error en test: ' . $e->getMessage());
        }
    }
    
    private function startConversation() {
        try {
            // Verificar autenticación
            if (!isset($_SESSION['user_id'])) {
                $this->error('Usuario no autenticado');
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validar input
            if (!$input || !isset($input['target_user_id'])) {
                $this->error('Datos de entrada inválidos');
                return;
            }
            
            $currentUserId = $_SESSION['user_id'];
            $targetUserId = $input['target_user_id'];
            
            // Validar que target_user_id no sea null o vacío
            if (!$targetUserId) {
                $this->error('ID de usuario objetivo requerido');
                return;
            }
            
            // Evitar conversación consigo mismo
            if ($currentUserId == $targetUserId) {
                $this->error('No puedes iniciar una conversación contigo mismo');
                return;
            }
            
            $conversationId = $this->getOrCreateConversation($currentUserId, $targetUserId);
            
            $this->success([
                'conversation_id' => $conversationId,
                'current_user' => $currentUserId,
                'target_user' => $targetUserId,
                'message' => 'Conversación iniciada exitosamente'
            ]);
            
        } catch (Exception $e) {
            $this->error('Error creando conversación: ' . $e->getMessage());
        }
    }
    
    private function sendMessage() {
        try {
            // Verificar autenticación
            if (!isset($_SESSION['user_id'])) {
                $this->error('Usuario no autenticado');
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            $conversationId = $input['conversation_id'] ?? '';
            $message = trim($input['message'] ?? '');
            $senderId = $_SESSION['user_id'];
            
            if (empty($conversationId)) {
                $this->error('ID de conversación requerido');
                return;
            }
            
            if (empty($message)) {
                $this->error('El mensaje no puede estar vacío');
                return;
            }
            
            $messageDoc = [
                'conversation_id' => $conversationId,
                'sender_id' => (int)$senderId,
                'message' => $message,
                'timestamp' => new MongoDB\BSON\UTCDateTime(),
                'read_by' => [(int)$senderId]
            ];
            
            $messageId = $this->mongo->insertDocument('messages', $messageDoc);
            
            // Actualizar conversación
            $this->updateConversationLastMessage($conversationId, $message);
            
            $this->success([
                'message_id' => (string)$messageId,
                'timestamp' => date('Y-m-d H:i:s'),
                'message' => 'Mensaje enviado correctamente'
            ]);
            
        } catch (Exception $e) {
            $this->error('Error enviando mensaje: ' . $e->getMessage());
        }
    }
    
    private function getMessages() {
        try {
            // Verificar autenticación
            if (!isset($_SESSION['user_id'])) {
                $this->error('Usuario no autenticado');
                return;
            }
            
            $conversationId = $_GET['conversation_id'] ?? '';
            
            if (empty($conversationId)) {
                $this->error('ID de conversación requerido');
                return;
            }
            
            $messages = $this->mongo->findDocuments('messages', 
                ['conversation_id' => $conversationId], 
                ['sort' => ['timestamp' => 1], 'limit' => 50]
            );
            
            $formattedMessages = [];
            foreach ($messages as $msg) {
                $formattedMessages[] = [
                    'id' => (string)$msg->_id,
                    'sender_id' => $msg->sender_id,
                    'message' => $msg->message,
                    'timestamp' => $msg->timestamp->toDateTime()->format('Y-m-d H:i:s'),
                    'read_by' => $msg->read_by ?? []
                ];
            }
            
            $this->success($formattedMessages);
            
        } catch (Exception $e) {
            $this->error('Error obteniendo mensajes: ' . $e->getMessage());
        }
    }
    
    private function getConversations() {
        try {
            // Verificar autenticación
            if (!isset($_SESSION['user_id'])) {
                $this->error('Usuario no autenticado');
                return;
            }
            
            $userId = $_SESSION['user_id'];
            
            $conversations = $this->mongo->findDocuments('conversations', 
                ['participants' => (int)$userId], 
                ['sort' => ['updated_at' => -1]]
            );
            
            $formattedConversations = [];
            foreach ($conversations as $conv) {
                $formattedConversations[] = [
                    'id' => (string)$conv->_id,
                    'participants' => $conv->participants,
                    'type' => $conv->type ?? 'private',
                    'last_message' => $conv->last_message ?? null,
                    'updated_at' => $conv->updated_at->toDateTime()->format('Y-m-d H:i:s')
                ];
            }
            
            $this->success($formattedConversations);
            
        } catch (Exception $e) {
            $this->error('Error obteniendo conversaciones: ' . $e->getMessage());
        }
    }
    
    private function getOrCreateConversation($user1, $user2) {
        // Buscar conversación existente
        $conversations = $this->mongo->findDocuments('conversations', [
            'participants' => [
                '$all' => [(int)$user1, (int)$user2],
                '$size' => 2
            ],
            'type' => 'private'
        ]);
        
        if (count($conversations) > 0) {
            return (string)$conversations[0]->_id;
        }
        
        // Crear nueva conversación
        $conversation = [
            'participants' => [(int)$user1, (int)$user2],
            'type' => 'private',
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime(),
            'last_message' => null
        ];
        
        return (string)$this->mongo->insertDocument('conversations', $conversation);
    }
    
    private function updateConversationLastMessage($conversationId, $message) {
        $this->mongo->updateDocument('conversations',
            ['_id' => new MongoDB\BSON\ObjectId($conversationId)],
            [
                '$set' => [
                    'last_message' => $message,
                    'updated_at' => new MongoDB\BSON\UTCDateTime()
                ]
            ]
        );
    }
    
    private function success($data) {
        // Limpiar cualquier salida previa
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
    
    private function error($message) {
        // Limpiar cualquier salida previa
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
        exit;
    }

    private function startTeamConversation() {
        try {
            if (!isset($_SESSION['user_id'])) {
                $this->error('Usuario no autenticado');
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $teamId = $input['team_id'] ?? null;
            $userId = $_SESSION['user_id'];
            
            if (!$teamId) {
                $this->error('ID del equipo requerido');
                return;
            }
            
            // ⭐ USAR tu sistema MongoDB existente
            $conversationId = "team_" . $teamId;
            
            // Verificar si ya existe conversación del equipo
            $existingConversations = $this->mongo->findDocuments('conversations', [
                'team_id' => (int)$teamId,
                'type' => 'team'
            ]);
            
            if (count($existingConversations) == 0) {
                // Crear nueva conversación de equipo
                $conversation = [
                    'team_id' => (int)$teamId,
                    'type' => 'team',
                    'created_at' => new MongoDB\BSON\UTCDateTime(),
                    'updated_at' => new MongoDB\BSON\UTCDateTime(),
                    'last_message' => 'Conversación de equipo iniciada'
                ];
                
                $conversationId = (string)$this->mongo->insertDocument('conversations', $conversation);
            } else {
                $conversationId = (string)$existingConversations[0]->_id;
            }
            
            $this->success([
                'conversation_id' => $conversationId,
                'team_id' => $teamId,
                'message' => 'Conversación de equipo iniciada'
            ]);
            
        } catch (Exception $e) {
            $this->error('Error iniciando conversación de equipo: ' . $e->getMessage());
        }
    }

    private function getTeamMessages() {
        try {
            if (ob_get_level()) {
                ob_clean();
            }
            
            if (!isset($_SESSION['user_id'])) {
                $this->error('Usuario no autenticado');
                return;
            }
            
            $conversationId = $_GET['conversation_id'] ?? '';
            
            if (empty($conversationId)) {
                $this->error('ID de conversación requerido');
                return;
            }
            
            // ⭐ DEBUG: Ver qué buscamos
            error_log("🔍 GET - conversationId recibido: " . $conversationId . " (tipo: " . gettype($conversationId) . ")");
            
            // ⭐ BUSCAR DE AMBAS FORMAS para debug
            // 1. Como ObjectId
            try {
                $conversationObjectId = new MongoDB\BSON\ObjectId($conversationId);
                $messagesAsObjectId = $this->mongo->findDocuments('messages', 
                    ['conversation_id' => $conversationObjectId], 
                    ['sort' => ['timestamp' => 1], 'limit' => 50]
                );
                error_log("🔍 GET - Mensajes encontrados como ObjectId: " . count($messagesAsObjectId));
            } catch (Exception $mongoError) {
                $messagesAsObjectId = [];
                error_log("🔍 GET - Error buscando como ObjectId: " . $mongoError->getMessage());
            }
            
            // 2. Como string
            $messagesAsString = $this->mongo->findDocuments('messages', 
                ['conversation_id' => $conversationId], 
                ['sort' => ['timestamp' => 1], 'limit' => 50]
            );
            error_log("🔍 GET - Mensajes encontrados como string: " . count($messagesAsString));
            
            // ⭐ USAR el que tenga resultados
            $messages = count($messagesAsObjectId) > 0 ? $messagesAsObjectId : $messagesAsString;
            
            error_log("🔍 GET - Total mensajes finales: " . count($messages));
            
            // ... resto del código igual
            require_once '../Config/database.php';
            $database = new Database();
            $mysqli = $database->getConnection();
            
            $formattedMessages = [];
            foreach ($messages as $msg) {
                $stmt = $mysqli->prepare("SELECT nombre, apellidos FROM usuarios_deportistas WHERE id = ?");
                $stmt->bind_param("i", $msg->sender_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                $formattedMessages[] = [
                    'id' => (string)$msg->_id,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $user ? $user['nombre'] . ' ' . $user['apellidos'] : 'Usuario Desconocido',
                    'message' => $msg->message,
                    'timestamp' => $msg->timestamp->toDateTime()->format('Y-m-d H:i:s'),
                    'read_by' => $msg->read_by ?? []
                ];
            }
            
            $database->closeConnection();
            $this->success($formattedMessages);
            
        } catch (Exception $e) {
            error_log("💥 GET - Error: " . $e->getMessage());
            $this->error('Error obteniendo mensajes de equipo: ' . $e->getMessage());
        }
    }

    private function sendTeamMessage() {
        try {
            if (!isset($_SESSION['user_id'])) {
                $this->error('Usuario no autenticado');
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            $conversationId = $input['conversation_id'] ?? '';
            $message = trim($input['message'] ?? '');
            $senderId = $_SESSION['user_id'];
            
            if (empty($conversationId) || empty($message)) {
                $this->error('Datos incompletos');
                return;
            }
            
            // ⭐ DEBUG: Ver qué tipo de conversationId recibimos
            error_log("🔍 SEND - conversationId recibido: " . $conversationId . " (tipo: " . gettype($conversationId) . ")");
            
            // ⭐ DECIDIR: ¿Guardar como ObjectId o como string?
            // OPCIÓN A: Guardar como ObjectId
            try {
                $conversationObjectId = new MongoDB\BSON\ObjectId($conversationId);
                $finalConversationId = $conversationObjectId;
                error_log("🔍 SEND - Guardando como ObjectId");
            } catch (Exception $e) {
                $finalConversationId = $conversationId; // Mantener como string
                error_log("🔍 SEND - Guardando como string");
            }
            
            $messageDoc = [
                'conversation_id' => $finalConversationId, // ← USAR la variable decidida
                'sender_id' => (int)$senderId,
                'message' => $message,
                'timestamp' => new MongoDB\BSON\UTCDateTime(),
                'read_by' => [(int)$senderId],
                'is_team_message' => true
            ];
            
            $messageId = $this->mongo->insertDocument('messages', $messageDoc);
            
            error_log("✅ SEND - Mensaje guardado con ID: " . $messageId);
            
            $this->success([
                'message_id' => (string)$messageId,
                'timestamp' => date('Y-m-d H:i:s'),
                'message' => 'Mensaje de equipo enviado'
            ]);
            
        } catch (Exception $e) {
            error_log("💥 SEND - Error: " . $e->getMessage());
            $this->error('Error enviando mensaje de equipo: ' . $e->getMessage());
        }
    }
}

// Ejecutar controlador
try {
    $controller = new MongoDBChatController();
    $controller->handleRequest();
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Error del servidor: ' . $e->getMessage()]);
}
?>