<?php
// filepath: c:\xampp\htdocs\ga\GameOn\Config\mongodb_config.php

require_once __DIR__ . '/../vendor/autoload.php';

class MongoDBConnection {
    private static $instance = null;
    private $client;
    private $database;
    
    private function __construct() {
        try {
            // ⭐⭐⭐ SOLUCIÓN DEFINITIVA: CONEXIÓN ROBUSTA PARA AZURE ⭐⭐⭐
            
            // 1. TU CONNECTION STRING DE ATLAS (MODO "STANDARD")
            // Ve a Atlas -> Database -> Tu Cluster -> Connect -> Drivers
            // Elige PHP y la versión 2.2.0 or later.
            // Copia el string de conexión que te da. NO USES el "+srv".
            // Ejemplo: mongodb://user:pass@ac-XXXX.mongodb.net/?retryWrites=true&w=majority
            // ✅ REEMPLAZA ESTA LÍNEA CON LA TUYA DE ATLAS (VERSIÓN +SRV)
            $connectionString = "mongodb+srv://gamebon_usuario:uenyQ7knyG8tonjC@gameoncluster.4jrdsxk.mongodb.net/?retryWrites=true&w=majority"; 

            // 2. NOMBRE DE TU BASE DE DATOS
            $dbName = "GameOn"; // O el nombre que uses

            // 3. OPCIONES DE CONEXIÓN EXPLÍCITAS PARA AZURE
            // Estas opciones fuerzan el uso de SSL, que es lo que Azure necesita.
            $options = [
                'ssl' => true,
                'authSource' => 'admin',
            ];

            $this->client = new MongoDB\Client($connectionString, $options);
            $this->database = $this->client->selectDatabase($dbName);

        } catch (Exception $e) {
            // En caso de fallo, termina de forma controlada y muestra un error JSON claro.
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
            }
            die(json_encode([
                'success' => false,
                'error' => 'DB_CONNECTION_FAILED',
                'message' => 'No se pudo conectar a la base de datos.',
                'details' => $e->getMessage()
            ]));
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getDatabase() {
        return $this->database;
    }
    
    public function getClient() {
        return $this->client;
    }
    
    // ✅ MÉTODOS PARA TUS COLECCIONES: conversations (8 docs) y messages (54 docs)
    
    public function insertDocument($collectionName, $document) {
        try {
            $collection = $this->database->selectCollection($collectionName);
            $result = $collection->insertOne($document);
            return $result->getInsertedId();
        } catch (Exception $e) {
            error_log("❌ Error insertando en $collectionName: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function findDocuments($collectionName, $filter = [], $options = []) {
        try {
            $collection = $this->database->selectCollection($collectionName);
            return $collection->find($filter, $options)->toArray();
        } catch (Exception $e) {
            error_log("❌ Error buscando en $collectionName: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function updateDocument($collectionName, $filter, $update) {
        try {
            $collection = $this->database->selectCollection($collectionName);
            return $collection->updateOne($filter, $update);
        } catch (Exception $e) {
            error_log("❌ Error actualizando en $collectionName: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function getOrCreateConversation($participants, $type = 'private', $teamId = null) {
        try {
            $collection = $this->database->selectCollection('conversations');
            
            if ($type === 'team') {
                $filter = ['team_id' => (int)$teamId, 'type' => 'team'];
            } else {
                // Buscar conversación privada entre participantes
                sort($participants); // Ordenar para consistencia
                $filter = [
                    'participants' => ['$all' => $participants, '$size' => count($participants)],
                    'type' => 'private'
                ];
            }
            
            $existing = $collection->findOne($filter);
            
            if ($existing) {
                return (string)$existing->_id;
            }
            
            // Crear nueva conversación
            $conversationData = [
                'type' => $type,
                'created_at' => new MongoDB\BSON\UTCDateTime(),
                'updated_at' => new MongoDB\BSON\UTCDateTime(),
                'last_message' => null
            ];
            
            if ($type === 'team') {
                $conversationData['team_id'] = (int)$teamId;
            } else {
                $conversationData['participants'] = $participants;
            }
            
            $result = $collection->insertOne($conversationData);
            return (string)$result->getInsertedId();
            
        } catch (Exception $e) {
            error_log("❌ Error en conversación: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function testConnection() {
        try {
            $result = $this->client->selectDatabase('admin')->command(['ping' => 1], [
                'maxTimeMS' => 30000
            ]);
            
            // ✅ VERIFICAR TUS COLECCIONES ESPECÍFICAS
            $conversations = $this->database->selectCollection('conversations')->countDocuments();
            $messages = $this->database->selectCollection('messages')->countDocuments();
            
            return [
                'success' => true, 
                'message' => '✅ Conexión exitosa a gameoncluster.4jrdsxk.mongodb.net',
                'database' => $this->databaseName,
                'cluster' => 'GameOnCluster',
                'collections' => [
                    'conversations' => $conversations,
                    'messages' => $messages
                ],
                'environment' => isset($_SERVER['WEBSITE_SITE_NAME']) ? 'Azure' : 'Local'
            ];
        } catch (Exception $e) {
            return [
                'success' => false, 
                'message' => '❌ Error: ' . $e->getMessage(),
                'environment' => isset($_SERVER['WEBSITE_SITE_NAME']) ? 'Azure' : 'Local'
            ];
        }
    }
}
?>