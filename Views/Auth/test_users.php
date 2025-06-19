<?php
echo "<h2>🔍 Diagnóstico completo del login</h2>";

require_once '../../Config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "<h3>✅ Conexión establecida</h3>";
    
    // Verificar usuarios deportistas
    $result = $conn->query("SELECT id, username, nombre, apellidos, estado FROM usuarios_deportistas ORDER BY id");
    
    echo "<h3>👥 Usuarios deportistas en Railway:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Nombre</th><th>Estado</th></tr>";
    
    $usernames = [];
    while ($row = $result->fetch_assoc()) {
        $usernames[] = $row['username'];
        $status = $row['estado'] == 1 ? '✅ Activo' : '❌ Inactivo';
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td><strong>{$row['username']}</strong></td>";
        echo "<td>{$row['nombre']} {$row['apellidos']}</td>";
        echo "<td>{$status}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>🔑 Usernames disponibles:</h3>";
    echo "<p>" . implode(', ', $usernames) . "</p>";
    
    // Probar login específico
    if (isset($_GET['test_user'])) {
        $test_username = $_GET['test_user'];
        echo "<h3>🧪 Probando login para: <strong>$test_username</strong></h3>";
        
        $stmt = $conn->prepare("SELECT id, username, password, estado FROM usuarios_deportistas WHERE username = ?");
        $stmt->bind_param("s", $test_username);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
        
        if ($usuario) {
            echo "✅ <strong>Usuario encontrado:</strong><br>";
            echo "- ID: {$usuario['id']}<br>";
            echo "- Username: {$usuario['username']}<br>";
            echo "- Estado: " . ($usuario['estado'] == 1 ? 'Activo' : 'Inactivo') . "<br>";
            echo "- Password hash: " . substr($usuario['password'], 0, 20) . "...<br>";
        } else {
            echo "❌ <strong>Usuario NO encontrado</strong><br>";
        }
        $stmt->close();
    }
    
    echo "<hr>";
    echo "<h3>🧪 Probar usuarios:</h3>";
    foreach ($usernames as $username) {
        echo "<a href='?test_user=$username' style='margin: 5px; padding: 8px 12px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;'>Probar: $username</a> ";
    }
    
} catch (Exception $e) {
    echo "❌ <strong>Error:</strong> " . $e->getMessage();
}
?>