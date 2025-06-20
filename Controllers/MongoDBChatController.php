<?php
// Controllers/MongoDBChatController.php

// ✅ PARA AZURE - SIN CLASES, SOLO FUNCIONES SIMPLES
ob_start();
ob_clean();

// ✅ HEADERS OBLIGATORIOS
header('Content-Type: application/json; charset=utf-8');

// ✅ CONTROL DE ERRORES - SIEMPRE JSON
set_error_handler(function($errno, $errstr) {
    echo json_encode(['success' => false, 'error' => "Error: $errstr"]);
    exit;
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR])) {
        echo json_encode(['success' => false, 'error' => "Error fatal: " . $error['message']]);
    }
});

// ✅ SESIÓN
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado']);
    exit;
}

try {
    // ✅ MONGODB
    require_once __DIR__ . '/../Config/mongodb_config.php';
    $mongo = MongoDBConnection::getInstance();
    
    // ✅ ACCIÓN
    $action = $_GET['action'] ?? '';
    
    // ✅ PROCESAMIENTO SEGÚN ACCIÓN
    switch ($action) {
        case 'test':
            echo json_encode([
                'success' => true, 
                'data' => [
                    'message' => 'MongoDB Controller funcionando',
                    'user_id' => $_SESSION['user_id'],
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);
            break;
            
        case 'start_conversation':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['target_user_id'])) {
                throw new Exception('target_user_id requerido');
            }
            
            $targetUserId = (int)$input['target_user_id'];
            $currentUserId = (int)$_SESSION['user_id'];
            
            $participants = [$currentUserId, $targetUserId];
            sort($participants);
            
            $conversationId = $mongo->getOrCreateConversation($participants);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'conversation_id' => $conversationId,
                    'current_user' => $currentUserId,
                    'target_user' => $targetUserId
                ]
            ]);
            break;
            
        case 'start_team_conversation':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['team_id'])) {
                throw new Exception('team_id requerido');
            }
            
            $teamId = (int)$input['team_id'];
            $conversationId = $mongo->getOrCreateConversation([], 'team', $teamId);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'conversation_id' => $conversationId,
                    'team_id' => $teamId
                ]
            ]);
            break;
            
        case 'get_messages':
            $conversationId = $_GET['conversation_id'] ?? '';
            if (empty($conversationId)) {
                throw new Exception('conversation_id requerido');
            }
            
            $messages = $mongo->findDocuments('messages', 
                ['conversation_id' => $conversationId], 
                ['sort' => ['timestamp' => 1], 'limit' => 50]
            );
            
            $formattedMessages = [];
            foreach ($messages as $msg) {
                $formattedMessages[] = [
                    'id' => (string)$msg->_id,
                    'sender_id' => $msg->sender_id,
                    'message' => $msg->message,
                    'timestamp' => $msg->timestamp->toDateTime()->format('Y-m-d H:i:s')
                ];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $formattedMessages
            ]);
            break;
            
        case 'get_team_messages':
            $conversationId = $_GET['conversation_id'] ?? '';
            if (empty($conversationId)) {
                throw new Exception('conversation_id requerido');
            }
            
            $messages = $mongo->findDocuments('messages', 
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
                    'sender_name' => 'Usuario ' . $msg->sender_id
                ];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $formattedMessages
            ]);
            break;
            
        case 'send_message':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['conversation_id']) || !isset($input['message'])) {
                throw new Exception('conversation_id y message requeridos');
            }
            
            $conversationId = $input['conversation_id'];
            $message = trim($input['message']);
            
            $messageDoc = [
                'conversation_id' => $conversationId,
                'sender_id' => (int)$_SESSION['user_id'],
                'message' => $message,
                'timestamp' => new MongoDB\BSON\UTCDateTime()
            ];
            
            $messageId = $mongo->insertDocument('messages', $messageDoc);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'message_id' => (string)$messageId,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);
            break;
            
        case 'send_team_message':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['conversation_id']) || !isset($input['message'])) {
                throw new Exception('conversation_id y message requeridos');
            }
            
            $conversationId = $input['conversation_id'];
            $message = trim($input['message']);
            
            $messageDoc = [
                'conversation_id' => $conversationId,
                'sender_id' => (int)$_SESSION['user_id'],
                'message' => $message,
                'timestamp' => new MongoDB\BSON\UTCDateTime(),
                'is_team_message' => true
            ];
            
            $messageId = $mongo->insertDocument('messages', $messageDoc);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'message_id' => (string)$messageId,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Acción no válida: ' . $action]);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

exit;
?>