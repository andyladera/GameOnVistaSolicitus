<?php
// Controllers/MongoDBChatController.php

// ✅ LIMPIAR CUALQUIER OUTPUT ACCIDENTAL
if (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');

try {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Usuario no autenticado.');
    }

    require_once __DIR__ . '/../Config/mongodb_config.php';

    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    if (empty($action)) {
        throw new Exception('Acción no especificada.');
    }

    $mongo = MongoDBConnection::getInstance();
    $currentUserId = (int)$_SESSION['user_id'];
    
    switch ($action) {
        case 'start_conversation':
            $input = json_decode(file_get_contents('php://input'), true);
            $targetUserId = (int)($input['target_user_id'] ?? 0);
            if ($targetUserId === 0) throw new Exception('ID de usuario objetivo no válido.');
            
            $participants = [$currentUserId, $targetUserId];
            sort($participants);
            
            $conversationId = $mongo->getOrCreateConversation($participants, 'private');
            
            echo json_encode([
                'success' => true, 
                'data' => ['conversation_id' => $conversationId]
            ]);
            exit; // ✅ IMPORTANTE: Salir inmediatamente
            
        case 'start_team_conversation':
            $input = json_decode(file_get_contents('php://input'), true);
            $teamId = (int)($input['team_id'] ?? 0);
            if ($teamId === 0) throw new Exception('ID de equipo no válido.');

            $conversationId = $mongo->getOrCreateConversation([], 'team', $teamId);
            
            echo json_encode([
                'success' => true, 
                'data' => [
                    'conversation_id' => $conversationId, 
                    'team_id' => $teamId
                ]
            ]);
            exit; // ✅ IMPORTANTE: Salir inmediatamente
        
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
            exit; // ✅ IMPORTANTE: Salir inmediatamente
            
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
            exit; // ✅ IMPORTANTE: Salir inmediatamente
            
        case 'send_message':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['conversation_id']) || !isset($input['message'])) {
                throw new Exception('conversation_id y message requeridos');
            }
            
            $conversationId = $input['conversation_id'];
            $message = trim($input['message']);
            
            $messageDoc = [
                'conversation_id' => $conversationId,
                'sender_id' => $currentUserId,
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
            exit; // ✅ IMPORTANTE: Salir inmediatamente
            
        case 'send_team_message':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['conversation_id']) || !isset($input['message'])) {
                throw new Exception('conversation_id y message requeridos');
            }
            
            $conversationId = $input['conversation_id'];
            $message = trim($input['message']);
            
            $messageDoc = [
                'conversation_id' => $conversationId,
                'sender_id' => $currentUserId,
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
            exit; // ✅ IMPORTANTE: Salir inmediatamente
            
        default:
            throw new Exception('Acción no válida: ' . htmlspecialchars($action));
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
}

exit;
?>