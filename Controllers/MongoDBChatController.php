<?php
// Controllers/MongoDBChatController.php

// ✅ INICIAR BUFFER DE SALIDA ANTES DE TODO
ob_start();

// ✅ CONFIGURACIÓN DE ERRORES
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ✅ HEADERS JSON OBLIGATORIOS AL INICIO
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar si la sesión ya está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
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
                    $this->error('Acción no válida: ' . $action);
            }
        }
        
        private function testConnection() {
            try {
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
                if (!isset($_SESSION['user_id'])) {
                    $this->error('Usuario no autenticado');
                    return;
                }
                
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input || !isset($input['target_user_id'])) {
                    $this->error('Datos de entrada inválidos');
                    return;
                }
                
                $currentUserId = $_SESSION['user_id'];
                $targetUserId = $input['target_user_id'];
                
                if (!$targetUserId) {
                    $this->error('ID de usuario objetivo requerido');
                    return;
                }
                
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
                
                // ✅ MANEJAR conversationId como ObjectId si es necesario
                $finalConversationId = $conversationId;
                if (strlen($conversationId) === 24) {
                    try {
                        $finalConversationId = new MongoDB\BSON\ObjectId($conversationId);
                    } catch (Exception $e) {
                        $finalConversationId = $conversationId;
                    }
                }
                
                $messageDoc = [
                    'conversation_id' => $finalConversationId,
                    'sender_id' => (int)$senderId,
                    'message' => $message,
                    'timestamp' => new MongoDB\BSON\UTCDateTime(),
                    'read_by' => [(int)$senderId]
                ];
                
                $messageId = $this->mongo->insertDocument('messages', $messageDoc);
                
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
                if (!isset($_SESSION['user_id'])) {
                    $this->error('Usuario no autenticado');
                    return;
                }
                
                $conversationId = $_GET['conversation_id'] ?? '';
                
                if (empty($conversationId)) {
                    $this->error('ID de conversación requerido');
                    return;
                }
                
                // ✅ BUSCAR MENSAJES DE AMBAS FORMAS
                $messages = $this->findMessagesFlexible($conversationId);
                
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
        
        private function startTeamConversation() {
            try {
                if (!isset($_SESSION['user_id'])) {
                    $this->error('Usuario no autenticado');
                    return;
                }
                
                $input = json_decode(file_get_contents('php://input'), true);
                $teamId = $input['team_id'] ?? null;
                
                if (!$teamId) {
                    $this->error('ID del equipo requerido');
                    return;
                }
                
                // Verificar si ya existe conversación del equipo
                $existingConversations = $this->mongo->findDocuments('conversations', [
                    'team_id' => (int)$teamId,
                    'type' => 'team'
                ]);
                
                if (count($existingConversations) == 0) {
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
                if (!isset($_SESSION['user_id'])) {
                    $this->error('Usuario no autenticado');
                    return;
                }
                
                $conversationId = $_GET['conversation_id'] ?? '';
                
                if (empty($conversationId)) {
                    $this->error('ID de conversación requerido');
                    return;
                }
                
                // ✅ BUSCAR MENSAJES DE EQUIPO
                $messages = $this->findMessagesFlexible($conversationId);
                
                // ✅ OBTENER NOMBRES DE USUARIOS CON PDO
                require_once __DIR__ . '/../Config/database.php';
                $database = new Database();
                $pdo = $database->getConnection();
                
                $formattedMessages = [];
                foreach ($messages as $msg) {
                    $stmt = $pdo->prepare("SELECT nombre, apellidos FROM usuarios_deportistas WHERE id = ?");
                    $stmt->execute([$msg->sender_id]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $formattedMessages[] = [
                        'id' => (string)$msg->_id,
                        'sender_id' => $msg->sender_id,
                        'sender_name' => $user ? $user['nombre'] . ' ' . $user['apellidos'] : 'Usuario Desconocido',
                        'message' => $msg->message,
                        'timestamp' => $msg->timestamp->toDateTime()->format('Y-m-d H:i:s'),
                        'read_by' => $msg->read_by ?? []
                    ];
                }
                
                $this->success($formattedMessages);
                
            } catch (Exception $e) {
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
                
                // ✅ MANEJAR conversationId como ObjectId si es necesario
                $finalConversationId = $conversationId;
                if (strlen($conversationId) === 24) {
                    try {
                        $finalConversationId = new MongoDB\BSON\ObjectId($conversationId);
                    } catch (Exception $e) {
                        $finalConversationId = $conversationId;
                    }
                }
                
                $messageDoc = [
                    'conversation_id' => $finalConversationId,
                    'sender_id' => (int)$senderId,
                    'message' => $message,
                    'timestamp' => new MongoDB\BSON\UTCDateTime(),
                    'read_by' => [(int)$senderId],
                    'is_team_message' => true
                ];
                
                $messageId = $this->mongo->insertDocument('messages', $messageDoc);
                
                $this->success([
                    'message_id' => (string)$messageId,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'message' => 'Mensaje de equipo enviado'
                ]);
                
            } catch (Exception $e) {
                $this->error('Error enviando mensaje de equipo: ' . $e->getMessage());
            }
        }
        
        // ✅ MÉTODO AUXILIAR PARA BUSCAR MENSAJES DE FORMA FLEXIBLE
        private function findMessagesFlexible($conversationId) {
            // Buscar como string
            $messagesAsString = $this->mongo->findDocuments('messages', 
                ['conversation_id' => $conversationId], 
                ['sort' => ['timestamp' => 1], 'limit' => 50]
            );
            
            // Buscar como ObjectId si es posible
            if (strlen($conversationId) === 24) {
                try {
                    $conversationObjectId = new MongoDB\BSON\ObjectId($conversationId);
                    $messagesAsObjectId = $this->mongo->findDocuments('messages', 
                        ['conversation_id' => $conversationObjectId], 
                        ['sort' => ['timestamp' => 1], 'limit' => 50]
                    );
                    return count($messagesAsObjectId) > 0 ? $messagesAsObjectId : $messagesAsString;
                } catch (Exception $e) {
                    return $messagesAsString;
                }
            }
            
            return $messagesAsString;
        }
        
        private function getOrCreateConversation($user1, $user2) {
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
    }

    // Ejecutar controlador
    $controller = new MongoDBChatController();
    $controller->handleRequest();
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Error del servidor: ' . $e->getMessage()]);
}
?>