<?php
// filepath: c:\xampp\htdocs\ga\GameOn\test_definitive_mongodb.php
echo "<h2>🎯 TEST DEFINITIVO - Tu MongoDB Atlas</h2>";

// ✅ INFORMACIÓN DEL CLUSTER
echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 10px; margin: 10px 0;'>";
echo "<h3>📊 Información del Cluster:</h3>";
echo "<p><strong>Cluster:</strong> gameoncluster.4jrdsxk.mongodb.net</p>";
echo "<p><strong>Base de datos:</strong> gameon_chat</p>";
echo "<p><strong>Usuario:</strong> gameon_user</p>";
echo "<p><strong>Colecciones esperadas:</strong></p>";
echo "<ul>";
echo "<li>conversations (8 documentos)</li>";
echo "<li>messages (54 documentos)</li>";
echo "</ul>";
echo "</div>";

try {
    require_once 'Config/mongodb_config.php';
    
    echo "<p>🔗 Conectando a tu cluster específico...</p>";
    
    $mongo = MongoDBConnection::getInstance();
    $result = $mongo->testConnection();
    
    if ($result['success']) {
        echo "<div style='color: green; padding: 20px; border: 3px solid green; border-radius: 15px; margin: 20px 0;'>";
        echo "<h3>🎉 ¡CONEXIÓN EXITOSA!</h3>";
        echo "<p>✅ " . $result['message'] . "</p>";
        echo "<p>🌍 Entorno: " . $result['environment'] . "</p>";
        echo "<p>🗄️ Base de datos: " . $result['database'] . "</p>";
        
        if (isset($result['collections'])) {
            echo "<h4>📋 Colecciones verificadas:</h4>";
            echo "<p>• conversations: " . $result['collections']['conversations'] . " documentos</p>";
            echo "<p>• messages: " . $result['collections']['messages'] . " documentos</p>";
        }
        
        echo "<p><strong>🚀 Tu chat MongoDB está listo para funcionar!</strong></p>";
        echo "</div>";
        
    } else {
        echo "<div style='color: red; padding: 20px; border: 3px solid red; border-radius: 15px; margin: 20px 0;'>";
        echo "<h3>❌ Error de Conexión</h3>";
        echo "<p>" . $result['message'] . "</p>";
        echo "<p>🌍 Entorno: " . $result['environment'] . "</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 20px; border: 3px solid red; border-radius: 15px; margin: 20px 0;'>";
    echo "<h3>❌ Error Crítico</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
    
    echo "<h3>🔧 Checklist de solución:</h3>";
    echo "<ol>";
    echo "<li>✅ IP 179.7.94.144 agregada a MongoDB Atlas</li>";
    echo "<li>✅ Usuario gameon_user tiene permisos</li>";
    echo "<li>✅ Contraseña uenyQ7knyG8tonjC es correcta</li>";
    echo "<li>✅ Cluster gameoncluster.4jrdsxk.mongodb.net está activo</li>";
    echo "<li>❓ Verificar firewall/antivirus</li>";
    echo "</ol>";
}
?>