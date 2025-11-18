<?php
require 'config/db.php';

$pdo = conectarDB();

// Eliminar tablas en orden correcto para evitar restricciones de clave foránea
try {
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    $pdo->exec('DROP TABLE IF EXISTS caja');
    $pdo->exec('DROP TABLE IF EXISTS compra_detalles');
    $pdo->exec('DROP TABLE IF EXISTS compras');
    $pdo->exec('DROP TABLE IF EXISTS detalle_venta');
    $pdo->exec('DROP TABLE IF EXISTS ventas');
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
} catch (Exception $e) {
    echo 'Error al eliminar tablas: ' . $e->getMessage() . '<br>';
}

// Crear tabla ventas (ajustada a esquema existente)
try {
    $pdo->exec('CREATE TABLE ventas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cliente_id INT NOT NULL,
        usuario_id INT NOT NULL,
        fecha_venta DATETIME DEFAULT CURRENT_TIMESTAMP,
        total DECIMAL(10,2) NOT NULL,
        ruta_factura_pdf VARCHAR(255) NULL,
        FOREIGN KEY (cliente_id) REFERENCES clientes(id),
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    )');
    echo 'Tabla ventas creada.<br>';
} catch (Exception $e) {
    echo 'Error creando tabla ventas: ' . $e->getMessage() . '<br>';
}

// Crear tabla detalle_venta (ajustada a esquema existente)
try {
    $pdo->exec('CREATE TABLE detalle_venta (
        id INT AUTO_INCREMENT PRIMARY KEY,
        venta_id INT NOT NULL,
        producto_id INT NOT NULL,
        cantidad_cubetas INT NOT NULL,
        precio_unitario DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
        FOREIGN KEY (producto_id) REFERENCES tipos_huevos(id)
    )');
    echo 'Tabla detalle_venta creada.<br>';
} catch (Exception $e) {
    echo 'Error creando tabla detalle_venta: ' . $e->getMessage() . '<br>';
}

// Crear tabla caja
try {
    $pdo->exec('CREATE TABLE caja (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        tipo_movimiento ENUM("entrada", "salida") NOT NULL,
        monto DECIMAL(10,2) NOT NULL,
        descripcion TEXT,
        venta_id INT NULL,
        FOREIGN KEY (venta_id) REFERENCES ventas(id)
    )');
    echo 'Tabla caja creada.<br>';
} catch (Exception $e) {
    echo 'Error creando tabla caja: ' . $e->getMessage() . '<br>';
}

// Crear tabla compras
try {
    $pdo->exec('CREATE TABLE compras (
        id INT AUTO_INCREMENT PRIMARY KEY,
        proveedor_id INT NOT NULL,
        usuario_id INT NOT NULL,
        fecha_compra DATETIME DEFAULT CURRENT_TIMESTAMP,
        total DECIMAL(10,2) NOT NULL,
        medio_pago ENUM("efectivo", "transferencia") NOT NULL,
        ruta_factura_pdf VARCHAR(255) NULL,
        FOREIGN KEY (proveedor_id) REFERENCES proveedores(id),
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    )');
    echo 'Tabla compras creada.<br>';
} catch (Exception $e) {
    echo 'Error creando tabla compras: ' . $e->getMessage() . '<br>';
}

// Crear tabla compra_detalles
try {
    $pdo->exec('CREATE TABLE compra_detalles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        compra_id INT NOT NULL,
        tipo_huevo_id INT NOT NULL,
        cantidad INT NOT NULL,
        precio_unitario DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (compra_id) REFERENCES compras(id) ON DELETE CASCADE,
        FOREIGN KEY (tipo_huevo_id) REFERENCES tipos_huevos(id)
    )');
    echo 'Tabla compra_detalles creada.<br>';
} catch (Exception $e) {
    echo 'Error creando tabla compra_detalles: ' . $e->getMessage() . '<br>';
}

// Modificar tabla caja para incluir saldo
try {
    $pdo->exec('ALTER TABLE caja ADD COLUMN saldo DECIMAL(10,2) NULL AFTER monto');
    echo 'Columna saldo agregada a tabla caja.<br>';
} catch (Exception $e) {
    echo 'Error modificando tabla caja: ' . $e->getMessage() . '<br>';
}

// Insertar saldo inicial en caja
try {
    $pdo->exec('INSERT INTO caja (saldo, descripcion, fecha) VALUES (1000000, "Saldo inicial del sistema", NOW())');
    echo 'Saldo inicial agregado a caja.<br>';
} catch (Exception $e) {
    echo 'Error agregando saldo inicial: ' . $e->getMessage() . '<br>';
}

echo 'Tablas de ventas y compras recreadas exitosamente.';
?>
