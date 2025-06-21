<?php
// filepath: c:\xampp\htdocs\ga\GameOn\Config\mongodb_config.php

require_once __DIR__ . '/../vendor/autoload.php';

class MongoDBConnection {
    private static $instance = null;
    private $client;
    private $database;
    
    private function __construct() {
        try {
            // ✅ RAILWAY MONGODB - Tus credenciales reales
            $connectionString = "mongodb://mongo:eyQlSOAqnvZXIlwliWbUlZDROyDEhpEw@hopper.proxy.rlwy.net:25700";

            // Nombre de tu base de datos
            $dbName = "GameOn";

            $this->client = new MongoDB\Client($connectionString);
            $this->database = $this->client->selectDatabase($dbName);

        } catch (Exception $e) {
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
}
?>