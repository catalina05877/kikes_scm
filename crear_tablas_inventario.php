<?php
require 'config/db.php';

$pdo = conectarDB();

// Crear tabla tipos_huevos
$pdo->exec('CREATE TABLE IF NOT EXISTS tipos_huevos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(10) NOT NULL UNIQUE,
    presentacion VARCHAR(50) NOT NULL,
    precio_por_cubeta DECIMAL(10,2) NOT NULL
)');

// Insertar tipos de huevos
$pdo->exec("INSERT IGNORE INTO tipos_huevos (tipo, presentacion, precio_por_cubeta) VALUES
    ('A', '30 unidades (\"cubeta\")', 10500.00),
    ('AA', '30 unidades (\"cubeta\")', 20700.00),
    ('B', '30 unidades (\"cubeta\")', 11350.00)");

// Crear tabla inventarios
$pdo->exec('CREATE TABLE IF NOT EXISTS inventarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_huevo_id INT NOT NULL,
    cantidad_cubetas INT NOT NULL,
    tipo_movimiento ENUM("entrada", "salida") NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    descripcion TEXT,
    FOREIGN KEY (tipo_huevo_id) REFERENCES tipos_huevos(id)
)');

echo 'Tablas creadas exitosamente.';
?>
