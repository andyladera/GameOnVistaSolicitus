<?php
// filepath: c:\xampp\htdocs\ga\GameOn\Config\mongodb_config.php

// ✅ INCLUIR AUTOLOADER DE COMPOSER
require_once __DIR__ . '/../vendor/autoload.php';

class MongoDBConnection {
    private static $instance = null;
    private $client;
    private $database;
    
    // ⭐ CONNECTION STRING ACTUALIZADO
    private $connectionString = 'mongodb+srv://gameon_user:uenyQ7knyG8tonjC@gameoncluster.4jrdsxk.mongodb.net/gameon_chat?retryWrites=true&w=majority&appName=GameOnCluster';
    private $databaseName = 'gameon_chat';
    
    private function __construct() {
        try {
            if (!class_exists('MongoDB\Client')) {
                throw new Exception('❌ MongoDB Client no está disponible. Verifica que composer autoload esté incluido.');
            }
            
            // ⭐ CONFIGURACIÓN SSL MEJORADA
            $options = [
                'serverSelectionTimeoutMS' => 30000,    // 30 segundos
                'connectTimeoutMS' => 30000,             // 30 segundos
                'socketTimeoutMS' => 30000,              // 30 segundos
                'maxPoolSize' => 5,                      // Límite de conexiones
                'retryWrites' => true,                   // Reintentar escrituras
                'retryReads' => true,                    // Reintentar lecturas
                'ssl' => true,                           // ⭐ FORZAR SSL
                'tlsAllowInvalidCertificates' => true,   // ⭐ PERMITIR CERTIFICADOS INVÁLIDOS
                'tlsAllowInvalidHostnames' => true,      // ⭐ PERMITIR HOSTNAMES INVÁLIDOS
            ];
            
            // ⭐ CONFIGURACIÓN ESPECÍFICA PARA WINDOWS XAMPP
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $options['tlsCAFile'] = null;           // No usar archivo CA en Windows
                $options['tlsInsecure'] = true;         // Permitir conexiones inseguras
            }
            
            $this->client = new MongoDB\Client($this->connectionString, $options);
            $this->database = $this->client->selectDatabase($this->databaseName);
            
            // ✅ TEST DE CONEXIÓN CON TIMEOUT MAYOR
            $this->client->selectDatabase('admin')->command(['ping' => 1], [
                'maxTimeMS' => 30000
            ]);
            
            error_log("✅ MongoDB Atlas conectado exitosamente");
            
        } catch (Exception $e) {
            error_log("❌ Error MongoDB Atlas: " . $e->getMessage());
            throw new Exception("❌ Error de conexión MongoDB: " . $e->getMessage());
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
    
    // ✅ MÉTODOS PARA TU ESTRUCTURA DE 2 COLECCIONES
    
    // Insertar mensaje (privado o grupo) en colección "messages"
    public function insertMessage($messageData) {
        try {
            $collection = $this->database->selectCollection('messages');
            $messageData['timestamp'] = new MongoDB\BSON\UTCDateTime();
            $result = $collection->insertOne($messageData);
            return $result->getInsertedId();
        } catch (Exception $e) {
            error_log("❌ Error insertando mensaje: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Obtener mensajes por conversation_id
    public function getMessages($conversationId, $limit = 50) {
        try {
            $collection = $this->database->selectCollection('messages');
            
            // ⭐ BUSCAR por conversation_id (string o ObjectId)
            $filter = ['conversation_id' => $conversationId];
            
            // Si es ObjectId, también buscar como ObjectId
            try {
                if (is_string($conversationId) && strlen($conversationId) === 24) {
                    $filter = [
                        '$or' => [
                            ['conversation_id' => $conversationId],
                            ['conversation_id' => new MongoDB\BSON\ObjectId($conversationId)]
                        ]
                    ];
                }
            } catch (Exception $e) {
                // Mantener búsqueda original si falla conversión
            }
            
            return $collection->find(
                $filter,
                ['sort' => ['timestamp' => 1], 'limit' => $limit]
            )->toArray();
        } catch (Exception $e) {
            error_log("❌ Error obteniendo mensajes: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Crear o encontrar conversación
    public function getOrCreateConversation($participants, $type = 'private', $teamId = null) {
        try {
            $collection = $this->database->selectCollection('conversations');
            
            if ($type === 'team') {
                // Buscar conversación de equipo
                $filter = ['team_id' => (int)$teamId, 'type' => 'team'];
            } else {
                // Buscar conversación privada
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
    
    // ⭐ ELIMINAR métodos obsoletos de 3 colecciones
    // Comentar o eliminar: insertPrivateMessage, insertGroupMessage, getPrivateMessages, getGroupMessages, insertTeam, getTeams
    
    // ✅ MÉTODO GENÉRICO PARA INSERTAR EN CUALQUIER COLECCIÓN
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
    
    // ✅ MÉTODO DE PRUEBA
    public function testConnection() {
        try {
            $result = $this->client->selectDatabase('admin')->command(['ping' => 1], [
                'maxTimeMS' => 30000
            ]);
            return [
                'success' => true, 
                'message' => '✅ Conexión exitosa a MongoDB Atlas',
                'database' => $this->databaseName,
                'cluster' => 'GameOnCluster'
            ];
        } catch (Exception $e) {
            return [
                'success' => false, 
                'message' => '❌ Error: ' . $e->getMessage()
            ];
        }
    }
}
?>