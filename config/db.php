<?php
// config/db.php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Cargar .env solo si existe
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// Cargar variables de entorno o valores por defecto
$DB_HOST = $_ENV['DB_HOST'] ?? '127.0.0.1';
$DB_NAME = $_ENV['DB_NAME'] ?? 'kikes_scm';
$DB_USER = $_ENV['DB_USER'] ?? 'root';
$DB_PASS = $_ENV['DB_PASS'] ?? '';

$APP_NAME = $_ENV['APP_NAME'] ?? 'Mi App';
$APP_URL  = $_ENV['APP_URL'] ?? '/';

$SENDGRID_API_KEY = $_ENV['SENDGRID_API_KEY'] ?? '';
$SENDGRID_FROM_EMAIL = $_ENV['SENDGRID_FROM_EMAIL'] ?? '';
$SENDGRID_FROM_NAME  = $_ENV['SENDGRID_FROM_NAME'] ?? '';

function conectarDB() {
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;

    // Forzar 127.0.0.1 si se usa localhost (evita error HY000 [2002])
    if ($DB_HOST === 'localhost') {
        $DB_HOST = '127.0.0.1';
    }

    $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die("Error de Conexión a la Base de Datos: " . $e->getMessage());
    }
}
