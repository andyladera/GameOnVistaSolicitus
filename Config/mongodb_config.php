<?php
// Crear: Config/mongodb_config.php

class MongoDBConnection {
    private static $instance = null;
    private $client;
    private $database;
    
    private function __construct() {
        try {
            // Verificar extensión PHP
            if (!extension_loaded('mongodb')) {
                throw new Exception("⚠️ Extension MongoDB no está instalada en PHP");
            }

            $this->client = new MongoDB\Driver\Manager("mongodb://localhost:27017");
            $this->database = 'gameon_chat';
            
            // Test de conexión
            $command = new MongoDB\Driver\Command(['ping' => 1]);
            $cursor = $this->client->executeCommand('admin', $command);
                        
        } catch (MongoDB\Driver\Exception\Exception $e) {
            echo "❌ Error MongoDB: " . $e->getMessage() . "<br>";
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "<br>";
            echo "💡 Para instalar extensión PHP: https://pecl.php.net/package/mongodb<br>";
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getClient() {
        return $this->client;
    }
    
    public function getDatabase() {
        return $this->database;
    }
    
    // Insertar documento
    public function insertDocument($collection, $document) {
        $bulk = new MongoDB\Driver\BulkWrite;
        $insertedId = $bulk->insert($document);
        
        $result = $this->client->executeBulkWrite(
            $this->database . '.' . $collection, 
            $bulk
        );
        
        return $insertedId;
    }
    
    // Buscar documentos
    public function findDocuments($collection, $filter = [], $options = []) {
        $query = new MongoDB\Driver\Query($filter, $options);
        $cursor = $this->client->executeQuery(
            $this->database . '.' . $collection, 
            $query
        );
        
        return $cursor->toArray();
    }
    
    // Actualizar documento
    public function updateDocument($collection, $filter, $update) {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->update($filter, $update);
        
        return $this->client->executeBulkWrite(
            $this->database . '.' . $collection, 
            $bulk
        );
    }
}
?>