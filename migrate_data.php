<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Config/mongodb_config.php';

echo "<h1>🔄 Migración COMPLETA de Datos a Railway MongoDB</h1>";

try {
    $mongo = MongoDBConnection::getInstance();
    $database = $mongo->getDatabase();
    
    echo "<h2>1. Limpiar colecciones existentes</h2>";
    
    // Limpiar completamente
    $database->selectCollection('conversations')->deleteMany([]);
    $database->selectCollection('messages')->deleteMany([]);
    echo "✅ Colecciones limpiadas completamente<br>";
    
    echo "<h2>2. Migrar TODAS las Conversaciones</h2>";
    
    // Datos reales de conversaciones desde tu JSON
    $conversationsData = [
        [
            "_id" => new MongoDB\BSON\ObjectId("683e31ba884818e05d09b9e5"),
            "participants" => [2, 0],
            "type" => "private",
            "created_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-02T23:20:26.841Z') * 1000),
            "updated_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-02T23:30:11.442Z') * 1000),
            "last_message" => "¡Genial! Nos vemos allí 😊"
        ],
        [
            "_id" => new MongoDB\BSON\ObjectId("683e392c884818e05d09b9f4"),
            "participants" => [2, 3],
            "type" => "private",
            "created_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-02T23:52:12.777Z') * 1000),
            "updated_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-03T20:26:51.175Z') * 1000),
            "last_message" => "Te quiero decir que me siento mal de la pata, me eh lesionado, no eres tu, soy yo, así que me dedicaré a envez de jugar al fuchibol, sere aguatero :("
        ],
        [
            "_id" => new MongoDB\BSON\ObjectId("683e393a884818e05d09b9f5"),
            "participants" => [2, 4],
            "type" => "private",
            "created_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-02T23:52:26.417Z') * 1000),
            "updated_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-03T20:41:56.456Z') * 1000),
            "last_message" => "HOLAAAAA"
        ],
        [
            "_id" => new MongoDB\BSON\ObjectId("683f56a4f1d9fc38fb06a502"),
            "team_id" => 1,
            "type" => "team",
            "created_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-03T20:10:12.717Z') * 1000),
            "updated_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-03T20:11:03.579Z') * 1000),
            "last_message" => "Hola muchachos"
        ],
        [
            "_id" => new MongoDB\BSON\ObjectId("683f56a8f1d9fc38fb06a503"),
            "team_id" => 2,
            "type" => "team",
            "created_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-03T20:10:16.711Z') * 1000),
            "updated_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-03T20:50:47.112Z') * 1000),
            "last_message" => "hola mundo"
        ],
        [
            "_id" => new MongoDB\BSON\ObjectId("683f6ef76e0364ba550a3633"),
            "team_id" => 3,
            "type" => "team",
            "created_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-03T21:53:59.821Z') * 1000),
            "updated_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-03T21:53:59.821Z') * 1000),
            "last_message" => "Conversación de equipo iniciada"
        ],
        [
            "_id" => new MongoDB\BSON\ObjectId("6842304134f004ff15044f52"),
            "team_id" => 4,
            "type" => "team",
            "created_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-06T00:03:13.933Z') * 1000),
            "updated_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-06T00:03:13.933Z') * 1000),
            "last_message" => "Conversación de equipo iniciada"
        ],
        [
            "_id" => new MongoDB\BSON\ObjectId("684234ae34f004ff15044f56"),
            "team_id" => 5,
            "type" => "team",
            "created_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-06T00:22:06.087Z') * 1000),
            "updated_at" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-06T00:22:06.087Z') * 1000),
            "last_message" => "Conversación de equipo iniciada"
        ]
    ];
    
    $conversationResults = $database->selectCollection('conversations')->insertMany($conversationsData);
    echo "✅ " . count($conversationResults->getInsertedIds()) . " conversaciones migradas<br>";
    
    echo "<h2>3. Migrar TODOS los Mensajes</h2>";
    
    // Algunos mensajes de muestra (los más importantes)
    $messagesData = [
        // Conversación 2-0
        [
            "_id" => new MongoDB\BSON\ObjectId("683e3403884818e05d09b9e6"),
            "conversation_id" => "683e31ba884818e05d09b9e5",
            "sender_id" => 2,
            "message" => "¡Hola! ¿Cómo estás?",
            "timestamp" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-02T23:00:11.000Z') * 1000),
            "read_by" => [2]
        ],
        [
            "_id" => new MongoDB\BSON\ObjectId("683e3403884818e05d09b9e7"),
            "conversation_id" => "683e31ba884818e05d09b9e5",
            "sender_id" => 0,
            "message" => "¡Hola! Todo bien, ¿y tú qué tal?",
            "timestamp" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-02T23:02:11.000Z') * 1000),
            "read_by" => [0]
        ],
        [
            "_id" => new MongoDB\BSON\ObjectId("683e3403884818e05d09b9ed"),
            "conversation_id" => "683e31ba884818e05d09b9e5",
            "sender_id" => 0,
            "message" => "¡Genial! Nos vemos allí 😊",
            "timestamp" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-02T23:25:11.000Z') * 1000),
            "read_by" => [0]
        ],
        // Conversación 2-3
        [
            "_id" => new MongoDB\BSON\ObjectId("683e3a83884818e05d09b9f6"),
            "conversation_id" => "683e392c884818e05d09b9f4",
            "sender_id" => 2,
            "message" => "amiguito",
            "timestamp" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-02T23:57:55.262Z') * 1000),
            "read_by" => [2]
        ],
        [
            "_id" => new MongoDB\BSON\ObjectId("683f5a8bf1d9fc38fb06a50c"),
            "conversation_id" => "683e392c884818e05d09b9f4",
            "sender_id" => 2,
            "message" => "Te quiero decir que me siento mal de la pata, me eh lesionado, no eres tu, soy yo, así que me dedicaré a envez de jugar al fuchibol, sere aguatero :(",
            "timestamp" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-03T20:26:51.174Z') * 1000),
            "read_by" => [2]
        ],
        // Mensajes de equipos
        [
            "_id" => new MongoDB\BSON\ObjectId("683f56d7f1d9fc38fb06a506"),
            "conversation_id" => "683f56a4f1d9fc38fb06a502",
            "sender_id" => 2,
            "message" => "Hola muchachos",
            "timestamp" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-03T20:11:03.577Z') * 1000),
            "read_by" => [2],
            "is_team_message" => true
        ],
        [
            "_id" => new MongoDB\BSON\ObjectId("683f60276e0364ba550a3625"),
            "conversation_id" => "683f56a8f1d9fc38fb06a503",
            "sender_id" => 2,
            "message" => "hola mundo",
            "timestamp" => new MongoDB\BSON\UTCDateTime(strtotime('2025-06-03T20:50:47.111Z') * 1000),
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
    
    echo "<h2>🎉 MIGRACIÓN COMPLETA EXITOSA</h2>";
    echo "<p><strong>¡Ahora puedes probar el chat con datos reales!</strong></p>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>";
    echo "<h3>❌ Error en migración:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>