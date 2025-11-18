<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}
require '../../config/db.php';

$pdo = conectarDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $proveedor_id = filter_var($_POST['proveedor_id'], FILTER_SANITIZE_NUMBER_INT);
        $productos = $_POST['productos'];
        $medio_pago = filter_var($_POST['medio_pago'], FILTER_SANITIZE_STRING);
        $usuario_id = $_SESSION['usuario_id'];

        // Calcular total
        $total = 0;
        foreach ($productos as $producto) {
            $cantidad = filter_var($producto['cantidad'], FILTER_SANITIZE_NUMBER_INT);
            $precio_unitario = filter_var($producto['precio_unitario'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $total += $cantidad * $precio_unitario;
        }

        // Verificar saldo en caja
        $saldo_actual = $pdo->query("SELECT saldo FROM caja ORDER BY id DESC LIMIT 1")->fetchColumn() ?: 0;
        if ($total > $saldo_actual) {
            throw new Exception("Saldo insuficiente en caja. Saldo actual: $" . number_format($saldo_actual, 0, ',', '.'));
        }

        // Insertar compra
        $stmt = $pdo->prepare("INSERT INTO compras (proveedor_id, usuario_id, total, medio_pago, fecha_compra) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$proveedor_id, $usuario_id, $total, $medio_pago]);
        $compra_id = $pdo->lastInsertId();

        // Insertar detalles de compra y actualizar inventario
        foreach ($productos as $producto) {
            $tipo_huevo_id = filter_var($producto['tipo_huevo_id'], FILTER_SANITIZE_NUMBER_INT);
            $cantidad = filter_var($producto['cantidad'], FILTER_SANITIZE_NUMBER_INT);
            $precio_unitario = filter_var($producto['precio_unitario'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

            // Insertar detalle
            $stmt = $pdo->prepare("INSERT INTO compra_detalles (compra_id, tipo_huevo_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
            $stmt->execute([$compra_id, $tipo_huevo_id, $cantidad, $precio_unitario]);

            // Actualizar inventario (entrada)
            $stmt = $pdo->prepare("INSERT INTO inventarios (tipo_huevo_id, tipo_movimiento, cantidad_cubetas, descripcion, fecha) VALUES (?, 'entrada', ?, 'Compra a proveedor', NOW())");
            $stmt->execute([$tipo_huevo_id, $cantidad]);
        }

        // Actualizar saldo en caja
        $nuevo_saldo = $saldo_actual - $total;
        $stmt = $pdo->prepare("INSERT INTO caja (saldo, descripcion, fecha) VALUES (?, 'Compra de huevos', NOW())");
        $stmt->execute([$nuevo_saldo]);

        $pdo->commit();

        header("Location: index.php?msg=Compra registrada exitosamente.");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: formulario.php?msg=ERROR: " . $e->getMessage());
        exit;
    }
} else {
    header("Location: formulario.php");
    exit;
}
?>
