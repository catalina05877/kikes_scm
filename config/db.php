<?php
// config/db.php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Cargar .env solo si existe (en Render NO existe)
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// Cargar variables desde Render o desde .env
$DB_HOST = $_ENV['DB_HOST'] ?? 'localhost';
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

    $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

    try {
        return new PDO($dsn, $DB_USER, $DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        die("Error de Conexión a la Base de Datos: " . $e->getMessage());
    }
}
