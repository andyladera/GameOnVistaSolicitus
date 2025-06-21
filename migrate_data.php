<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Config/mongodb_config.php';

echo "<h1>🔄 Migración de Datos a Railway MongoDB</h1>";

try {
    $mongo = MongoDBConnection::getInstance();
    $database = $mongo->getDatabase();
    
    echo "<h2>1. Limpiar colecciones existentes</h2>";
    
    // Limpiar colecciones
    $database->selectCollection('conversations')->deleteMany([]);
    $database->selectCollection('messages')->deleteMany([]);
    echo "✅ Colecciones limpiadas<br>";
    
    echo "<h2>2. Migrar Conversaciones</h2>";
    
    // Datos de conversaciones de tu JSON
    $conversationsData = [
        [
            "participants" => [2, 0],
            "type" => "private",
            "created_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-02T23:20:26.841Z') * 1000),
            "updated_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-02T23:30:11.442Z') * 1000),
            "last_message" => "¡Genial! Nos vemos allí 😊"
        ],
        [
            "participants" => [2, 3],
            "type" => "private",
            "created_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-02T23:52:12.777Z') * 1000),
            "updated_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-03T20:26:51.175Z') * 1000),
            "last_message" => "Te quiero decir que me siento mal de la pata, me eh lesionado, no eres tu, soy yo, así que me dedicaré a envez de jugar al fuchibol, sere aguatero :("
        ],
        [
            "participants" => [2, 4],
            "type" => "private",
            "created_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-02T23:52:26.417Z') * 1000),
            "updated_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-03T20:41:56.456Z') * 1000),
            "last_message" => "HOLAAAAA"
        ],
        [
            "team_id" => 1,
            "type" => "team",
            "created_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-03T20:10:12.717Z') * 1000),
            "updated_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-03T20:11:03.579Z') * 1000),
            "last_message" => "Hola muchachos"
        ],
        [
            "team_id" => 2,
            "type" => "team",
            "created_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-03T20:10:16.711Z') * 1000),
            "updated_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-03T20:50:47.112Z') * 1000),
            "last_message" => "hola mundo"
        ],
        [
            "team_id" => 3,
            "type" => "team",
            "created_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-03T21:53:59.821Z') * 1000),
            "updated_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-03T21:53:59.821Z') * 1000),
            "last_message" => "Conversación de equipo iniciada"
        ]
    ];
    
    $conversationResults = $database->selectCollection('conversations')->insertMany($conversationsData);
    echo "✅ " . count($conversationResults->getInsertedIds()) . " conversaciones migradas<br>";
    
    // Obtener IDs de las conversaciones insertadas para mapear mensajes
    $conversationIds = $conversationResults->getInsertedIds();
    
    echo "<h2>3. Migrar Mensajes de Muestra</h2>";
    
    // Algunos mensajes de muestra (usando los nuevos IDs)
    $messagesData = [
        [
            "conversation_id" => (string)$conversationIds[0], // Conversación 2-0
            "sender_id" => 2,
            "message" => "¡Hola! ¿Cómo estás?",
            "timestamp" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-02T23:00:11.000Z') * 1000),
            "read_by" => [2]
        ],
        [
            "conversation_id" => (string)$conversationIds[0],
            "sender_id" => 0,
            "message" => "¡Hola! Todo bien, ¿y tú qué tal?",
            "timestamp" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-02T23:02:11.000Z') * 1000),
            "read_by" => [0]
        ],
        [
            "conversation_id" => (string)$conversationIds[1], // Conversación 2-3
            "sender_id" => 2,
            "message" => "amiguito",
            "timestamp" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-02T23:57:55.262Z') * 1000),
            "read_by" => [2]
        ],
        [
            "conversation_id" => (string)$conversationIds[3], // Team 1
            "sender_id" => 2,
            "message" => "Hola muchachos",
            "timestamp" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-03T20:11:03.577Z') * 1000),
            "read_by" => [2],
            "is_team_message" => true
        ]
    ];
    
    $messageResults = $database->selectCollection('messages')->insertMany($messagesData);
    echo "✅ " . count($messageResults->getInsertedIds()) . " mensajes migrados<br>";
    
    echo "<h2>4. Verificar Migración</h2>";
    
    $totalConversations = $database->selectCollection('conversations')->countDocuments();
    $totalMessages = $database->selectCollection('messages')->countDocuments();
    
    echo "📊 Total conversaciones: $totalConversations<br>";
    echo "📊 Total mensajes: $totalMessages<br>";
    
    echo "<h2>🎉 MIGRACIÓN COMPLETADA</h2>";
    echo "<p><strong>¡Ahora puedes probar el chat!</strong></p>";
    echo "<p><a href='Views/UserDep/misequipos.php' class='btn btn-primary'>Ir al Chat</a></p>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>";
    echo "<h3>❌ Error en migración:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>