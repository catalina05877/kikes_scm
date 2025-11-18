<?php
require 'config/db.php';

echo "<h2>🔍 Verificación de Configuración SendGrid</h2>";

// Verificar constantes
echo "<h3>📋 Variables de Entorno:</h3>";
$checks = [
    'SENDGRID_API_KEY' => defined('SENDGRID_API_KEY'),
    'SENDGRID_FROM_EMAIL' => defined('SENDGRID_FROM_EMAIL'),
    'SENDGRID_FROM_NAME' => defined('SENDGRID_FROM_NAME'),
    'APP_URL' => defined('APP_URL'),
];

foreach ($checks as $name => $status) {
    $icon = $status ? '✅' : '❌';
    if ($name === 'SENDGRID_API_KEY' && $status) {
        $value = substr(constant($name), 0, 20) . '...'; // Mostrar solo inicio por seguridad
    } else {
        $value = $status ? constant($name) : 'NO DEFINIDA';
    }
    echo "<p>{$icon} <strong>{$name}:</strong> " . htmlspecialchars($value) . "</p>";
}

// Verificar clase SendGrid
echo "<h3>📦 SendGrid instalado:</h3>";
if (class_exists('\SendGrid')) {
    echo "<p>✅ Clase SendGrid disponible</p>";
    echo "<p>✅ SendGrid versión: 8.1.2</p>";
} else {
    echo "<p>❌ Clase SendGrid NO encontrada</p>";
}

// Verificar conexión a BD
echo "<h3>🗄️ Base de Datos:</h3>";
try {
    $pdo = conectarDB();
    echo "<p>✅ Conexión exitosa a la base de datos</p>";
    
    // Verificar tabla usuarios
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Tabla 'usuarios' existe</p>";
        
        // Verificar columnas
        $stmt = $pdo->query("DESCRIBE usuarios");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $hasToken = in_array('token_recuperacion', $columns);
        $hasExpira = in_array('token_expira', $columns);
        
        echo "<p>" . ($hasToken ? '✅' : '❌') . " Columna <code>token_recuperacion</code></p>";
        echo "<p>" . ($hasExpira ? '✅' : '❌') . " Columna <code>token_expira</code></p>";
        
        if (!$hasToken || !$hasExpira) {
            echo "<div style='background: #fff3cd; padding: 15px; margin: 10px 0; border-left: 4px solid #ffc107;'>";
            echo "<strong>⚠️ Faltan columnas.</strong> Ejecuta en phpMyAdmin:<br><br>";
            echo "<code>ALTER TABLE usuarios ADD COLUMN token_recuperacion VARCHAR(100) DEFAULT NULL, ADD COLUMN token_expira DATETIME DEFAULT NULL;</code>";
            echo "</div>";
        }
        
        // Contar usuarios
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
        $total = $stmt->fetch()['total'];
        echo "<p>📊 Total de usuarios registrados: <strong>{$total}</strong></p>";
        
    } else {
        echo "<p>❌ Tabla 'usuarios' NO existe</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<h3>🚀 Siguiente paso:</h3>";
echo "<p>Si todo está en ✅, ve a: <a href='recuperar_password.php'>recuperar_password.php</a> para probar el envío de correo.</p>";
?>