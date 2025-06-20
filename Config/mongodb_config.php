<?php
// filepath: c:\xampp\htdocs\ga\GameOn\Config\mongodb_config.php

require_once __DIR__ . '/../vendor/autoload.php';

class MongoDBConnection {
    private static $instance = null;
    private $client;
    private $database;
    
    // ⭐ TUS DATOS EXACTOS DE MONGODB ATLAS
    private $connectionString = 'mongodb+srv://gameon_user:uenyQ7knyG8tonjC@gameoncluster.4jrdsxk.mongodb.net/gameon_chat?retryWrites=true&w=majority&ssl=true&tlsAllowInvalidCertificates=true';
    private $databaseName = 'gameon_chat';
    
    private function __construct() {
        try {
            if (!class_exists('MongoDB\Client')) {
                throw new Exception('❌ MongoDB Client no disponible');
            }
            
            // ⭐ CONFIGURACIÓN ESPECÍFICA PARA TU CLUSTER
            $options = [
                // Timeouts generosos para conexión estable
                'serverSelectionTimeoutMS' => 30000,
                'connectTimeoutMS' => 30000,
                'socketTimeoutMS' => 30000,
                
                // Configuración SSL para Azure/Cloud
                'ssl' => true,
                'tls' => true,
                'authSource' => 'admin',
                
                // Optimización para tu cluster específico
                'retryWrites' => true,
                'retryReads' => true,
                'maxPoolSize' => 5,
                'minPoolSize' => 1,
                
                // Configuración de red
                'serverSelectionTryOnce' => false,
                'heartbeatFrequencyMS' => 30000,
                
                // ⭐ CONFIGURACIÓN SSL PERMISIVA para resolver TLS handshake
                'tlsAllowInvalidCertificates' => true,
                'tlsAllowInvalidHostnames' => true,
                'tlsInsecure' => true,
            ];
            
            // ⭐ DETECCIÓN DE ENTORNO (Azure vs Local)
            if (isset($_SERVER['WEBSITE_SITE_NAME'])) {
                // Estamos en Azure Web App
                error_log("🌍 Conectando desde Azure Web App: " . $_SERVER['WEBSITE_SITE_NAME']);
                $options['tlsAllowInvalidCertificates'] = false; // Azure tiene certificados válidos
            } else {
                // Estamos en desarrollo local
                error_log("💻 Conectando desde entorno local");
                $options['tlsInsecure'] = true; // Para desarrollo local
            }
            
            $this->client = new MongoDB\Client($this->connectionString, $options);
            $this->database = $this->client->selectDatabase($this->databaseName);
            
            // ✅ TEST DE PING a tu cluster específico
            $result = $this->client->selectDatabase('admin')->command(['ping' => 1], [
                'maxTimeMS' => 30000
            ]);
            
            error_log("✅ Conectado exitosamente a gameoncluster.4jrdsxk.mongodb.net");
            
        } catch (Exception $e) {
            error_log("❌ Error conectando a MongoDB Atlas: " . $e->getMessage());
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