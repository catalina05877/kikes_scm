<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}
require '../../config/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php?msg=ERROR: ID de venta no especificado");
    exit;
}

$pdo = conectarDB();

try {
    $pdo->beginTransaction();

    // Verificar que la venta existe
    $stmt = $pdo->prepare("SELECT * FROM ventas WHERE id = ?");
    $stmt->execute([$id]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$venta) {
        throw new Exception("Venta no encontrada");
    }

    // Obtener detalles de la venta para reversar inventario
    $stmt_detalle = $pdo->prepare("SELECT * FROM detalle_venta WHERE venta_id = ?");
    $stmt_detalle->execute([$id]);
    $detalles = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);

    // Reversar movimientos de inventario
    foreach ($detalles as $detalle) {
        $stmt_inventario = $pdo->prepare("INSERT INTO inventarios (tipo_huevo_id, cantidad_cubetas, tipo_movimiento, descripcion) VALUES (?, ?, 'entrada', ?)");
        $stmt_inventario->execute([
            $detalle['producto_id'],
            $detalle['cantidad_cubetas'],
            "Eliminación venta ID: $id"
        ]);
    }

    // Reversar movimiento de caja
    $stmt_caja = $pdo->prepare("INSERT INTO caja (tipo_movimiento, monto, descripcion, venta_id) VALUES ('salida', ?, ?, ?)");
    $stmt_caja->execute([$venta['total'], "Eliminación venta ID: $id", $id]);

    // Eliminar detalle de venta
    $stmt_delete_detalle = $pdo->prepare("DELETE FROM detalle_venta WHERE venta_id = ?");
    $stmt_delete_detalle->execute([$id]);

    // Eliminar venta
    $stmt_delete_venta = $pdo->prepare("DELETE FROM ventas WHERE id = ?");
    $stmt_delete_venta->execute([$id]);

    $pdo->commit();

    header("Location: index.php?msg=Venta eliminada exitosamente");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    header("Location: index.php?msg=ERROR: " . $e->getMessage());
    exit;
}
?>
