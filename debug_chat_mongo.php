<?php
// filepath: c:\xampp\htdocs\ga\GameOn\debug_chat_mongo.php
require_once 'Config/mongodb_config.php';

echo "<h2>🐛 Debug Chat MongoDB</h2>";

try {
    $mongo = MongoDBConnection::getInstance();
    
    echo "<h3>1. ✅ Conexión a MongoDB Atlas</h3>";
    $testResult = $mongo->testConnection();
    echo "<p>Resultado: " . json_encode($testResult) . "</p>";
    
    echo "<h3>2. 📊 Verificar colecciones existentes</h3>";
    $conversations = $mongo->findDocuments('conversations', [], ['limit' => 5]);
    $messages = $mongo->findDocuments('messages', [], ['limit' => 5]);
    
    echo "<p><strong>Conversaciones:</strong> " . count($conversations) . "</p>";
    echo "<p><strong>Mensajes:</strong> " . count($messages) . "</p>";
    
    if (count($conversations) > 0) {
        echo "<h4>📋 Muestra de conversaciones:</h4>";
        foreach ($conversations as $conv) {
            echo "<p>ID: " . $conv->_id . " | Tipo: " . ($conv->type ?? 'N/A') . "</p>";
        }
    }
    
    if (count($messages) > 0) {
        echo "<h4>💬 Muestra de mensajes:</h4>";
        foreach (array_slice($messages, 0, 3) as $msg) {
            echo "<p>ID: " . $msg->_id . " | conversation_id: " . ($msg->conversation_id ?? 'N/A') . " | Tipo conversation_id: " . gettype($msg->conversation_id) . "</p>";
        }
    }
    
    echo "<h3>3. 🔧 Estado de archivos:</h3>";
    echo "<p>MongoDBChatController.php: " . (file_exists('Controllers/MongoDBChatController.php') ? '✅ Existe' : '❌ No existe') . "</p>";
    echo "<p>chatmongo.js: " . (file_exists('Public/js/chatmongo.js') ? '✅ Existe' : '❌ No existe') . "</p>";
    echo "<p>chat.js: " . (file_exists('Public/js/chat.js') ? '✅ Existe' : '❌ No existe') . "</p>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Error: " . $e->getMessage() . "</div>";
}
?>