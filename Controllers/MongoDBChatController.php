<?php
// Controllers/MongoDBChatController.php

// ✅ MÁXIMA PROTECCIÓN: Limpiar cualquier output accidental
if (ob_get_level() > 0) {
    ob_end_clean();
}

// ✅ RESPUESTA JSON POR DEFECTO EN CASO DE ERROR FATAL
header('Content-Type: application/json; charset=utf-8');

// ✅ MANEJO DE ERRORES A PRUEBA DE BALAS
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        // Si los headers ya se enviaron, no podemos hacer nada.
        // Pero si no, forzamos una respuesta JSON de error.
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error' => 'Error fatal del servidor.',
                'details' => $error['message'] // No mostrar en producción real, solo para debug
            ]);
        }
    }
});

try {
    // ✅ VERIFICAR SESIÓN
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Usuario no autenticado. Por favor, inicie sesión.');
    }

    // ✅ INCLUIR MONGODB (Punto común de fallo)
    $mongoConfigFile = __DIR__ . '/../Config/mongodb_config.php';
    if (!file_exists($mongoConfigFile)) {
        throw new Exception('Error crítico: No se encuentra el archivo de configuración de MongoDB.');
    }
    require_once $mongoConfigFile;

    // ✅ OBTENER ACCIÓN
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    if (empty($action)) {
        throw new Exception('Acción no especificada.');
    }

    // ✅ CREAR INSTANCIA MONGODB
    $mongo = MongoDBConnection::getInstance();
    $currentUserId = (int)$_SESSION['user_id'];
    
    $response_data = null;

    // ✅ EJECUTAR ACCIONES
    switch ($action) {
        case 'start_conversation':
            $input = json_decode(file_get_contents('php://input'), true);
            $targetUserId = (int)($input['target_user_id'] ?? 0);
            if ($targetUserId === 0) throw new Exception('ID de usuario objetivo no válido.');
            
            $participants = [$currentUserId, $targetUserId];
            sort($participants);
            
            $conversationId = $mongo->getOrCreateConversation($participants, 'private');
            $response_data = ['conversation_id' => $conversationId];
            break;

        case 'start_team_conversation':
            $input = json_decode(file_get_contents('php://input'), true);
            $teamId = (int)($input['team_id'] ?? 0);
            if ($teamId === 0) throw new Exception('ID de equipo no válido.');

            $conversationId = $mongo->getOrCreateConversation([], 'team', $teamId);
            $response_data = ['conversation_id' => $conversationId, 'team_id' => $teamId];
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
            throw new Exception('Acción no válida: ' . htmlspecialchars($action));
    }

    // ✅ ENVIAR RESPUESTA EXITOSA
    echo json_encode(['success' => true, 'data' => $response_data]);

} catch (Exception $e) {
    // ✅ ENVIAR ERROR CONTROLADO
    if (!headers_sent()) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

exit;
?>