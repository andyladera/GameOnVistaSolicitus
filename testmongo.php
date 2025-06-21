<?php
// Mostrar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Test de Conexión MongoDB</h1>";

// 1. Verificar extensión MongoDB
echo "<h2>1. Información de PHP y MongoDB</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";

if (extension_loaded('mongodb')) {
    echo "✅ Extensión MongoDB: CARGADA<br>";
    echo "Versión extensión: " . phpversion('mongodb') . "<br>";
} else {
    echo "❌ Extensión MongoDB: NO ENCONTRADA<br>";
}

// 2. Verificar autoload
echo "<h2>2. Verificar Composer</h2>";
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    echo "✅ Autoload encontrado<br>";
    require_once $autoloadPath;
    echo "✅ Autoload cargado<br>";
} else {
    echo "❌ Autoload NO encontrado en: $autoloadPath<br>";
    die("No se puede continuar sin composer autoload");
}

// 3. Test de conexión Railway
echo "<h2>3. Test Conexión Railway MongoDB</h2>";
try {
    $connectionString = "mongodb://mongo:eyQlSOAqnvZXIlwliWbUlZDROyDEhpEw@hopper.proxy.rlwy.net:25700";
    echo "Connection String: " . substr($connectionString, 0, 30) . "...<br>";
    
    $client = new MongoDB\Client($connectionString);
    echo "✅ Cliente MongoDB creado<br>";
    
    // Test ping
    $admin = $client->selectDatabase('admin');
    $result = $admin->command(['ping' => 1]);
    echo "✅ PING exitoso<br>";
    
    echo "<h3>🎉 CONEXIÓN EXITOSA A RAILWAY MONGODB</h3>";
    
} catch (Exception $e) {
    echo "❌ ERROR DE CONEXIÓN:<br>";
    echo "Mensaje: " . $e->getMessage() . "<br>";
}

echo "<h2>4. Información del Servidor</h2>";
echo "Versión MongoDB Extension: " . (extension_loaded('mongodb') ? phpversion('mongodb') : 'No instalada') . "<br>";
?>