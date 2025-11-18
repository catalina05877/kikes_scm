<?php
// config/db.php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Cargar variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Configuración de la base de datos desde .env
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);
define('DB_NAME', $_ENV['DB_NAME']);

// Configuración de la aplicación
define('APP_NAME', $_ENV['APP_NAME']);
define('APP_URL', $_ENV['APP_URL']);

// Configuración de Gmail (por si la necesitas)
define('GMAIL_USERNAME', $_ENV['GMAIL_USERNAME'] ?? '');
define('GMAIL_APP_PASSWORD', $_ENV['GMAIL_APP_PASSWORD'] ?? '');

// ========================================
// CONFIGURACIÓN DE SENDGRID
// ========================================
define('SENDGRID_API_KEY', $_ENV['SENDGRID_API_KEY']);
define('SENDGRID_FROM_EMAIL', $_ENV['SENDGRID_FROM_EMAIL']);
define('SENDGRID_FROM_NAME', $_ENV['SENDGRID_FROM_NAME']);

function conectarDB() {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
        return $pdo;
    } catch (PDOException $e) {
        die("Error de Conexión a la Base de Datos: " . $e->getMessage());
    }
}
?>