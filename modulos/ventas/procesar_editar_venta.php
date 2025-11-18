<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}
require '../../config/db.php';

$pdo = conectarDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $venta_id = $_POST['venta_id'] ?? null;
    $cliente_id = $_POST['cliente_id'] ?? null;
    $productos = $_POST['productos'] ?? [];

    if (!$venta_id || !$cliente_id || empty($productos)) {
        header("Location: editar_venta.php?id=$venta_id&msg=ERROR: Datos incompletos");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Verificar que la venta existe
        $stmt = $pdo->prepare("SELECT * FROM ventas WHERE id = ?");
        $stmt->execute([$venta_id]);
        $venta_existente = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$venta_existente) {
            throw new Exception("Venta no encontrada");
        }

        // Obtener detalles actuales para reversar inventario
        $stmt_detalle_actual = $pdo->prepare("SELECT * FROM detalle_venta WHERE venta_id = ?");
        $stmt_detalle_actual->execute([$venta_id]);
        $detalles_actuales = $stmt_detalle_actual->fetchAll(PDO::FETCH_ASSOC);

        // Reversar movimientos de inventario
        foreach ($detalles_actuales as $detalle) {
            $stmt_inventario = $pdo->prepare("INSERT INTO inventarios (tipo_huevo_id, cantidad_cubetas, tipo_movimiento, descripcion) VALUES (?, ?, 'entrada', ?)");
            $stmt_inventario->execute([
                $detalle['producto_id'],
                $detalle['cantidad_cubetas'],
                "Reversión venta ID: $venta_id"
            ]);
        }

        // Reversar movimiento de caja
        $stmt_caja = $pdo->prepare("INSERT INTO caja (tipo_movimiento, monto, descripcion, venta_id) VALUES ('salida', ?, ?, ?)");
        $stmt_caja->execute([$venta_existente['total'], "Reversión venta ID: $venta_id", $venta_id]);

        // Verificar stock y calcular nuevo total
        $total = 0;
        $productos_validos = [];

        foreach ($productos as $producto) {
            $tipo_huevo_id = $producto['tipo_huevo_id'];
            $cantidad = (int)$producto['cantidad'];

            if ($tipo_huevo_id && $cantidad > 0) {
                // Obtener precio y stock actual
                $stmt = $pdo->prepare("SELECT precio_por_cubeta,
                    (SELECT SUM(CASE WHEN tipo_movimiento = 'entrada' THEN cantidad_cubetas ELSE 0 END) -
                            SUM(CASE WHEN tipo_movimiento = 'salida' THEN cantidad_cubetas ELSE 0 END)
                     FROM inventarios WHERE tipo_huevo_id = ?) as stock_actual
                    FROM tipos_huevos WHERE id = ?");
                $stmt->execute([$tipo_huevo_id, $tipo_huevo_id]);
                $tipo = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$tipo || $tipo['stock_actual'] < $cantidad) {
                    throw new Exception("Stock insuficiente para el tipo de huevo seleccionado");
                }

                $productos_validos[] = [
                    'tipo_huevo_id' => $tipo_huevo_id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $tipo['precio_por_cubeta']
                ];

                $total += $tipo['precio_por_cubeta'] * $cantidad;
            }
        }

        if (empty($productos_validos)) {
            throw new Exception("No se seleccionaron productos válidos");
        }

        // Actualizar venta
        $stmt = $pdo->prepare("UPDATE ventas SET cliente_id = ?, total = ? WHERE id = ?");
        $stmt->execute([$cliente_id, $total, $venta_id]);

        // Eliminar detalles anteriores
        $stmt_delete = $pdo->prepare("DELETE FROM detalle_venta WHERE venta_id = ?");
        $stmt_delete->execute([$venta_id]);

        // Insertar nuevos detalles de venta
        $stmt_detalle = $pdo->prepare("INSERT INTO detalle_venta (venta_id, producto_id, cantidad_cubetas, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
        foreach ($productos_validos as $producto) {
            $subtotal = $producto['precio_unitario'] * $producto['cantidad'];
            $stmt_detalle->execute([
                $venta_id,
                $producto['tipo_huevo_id'],
                $producto['cantidad'],
                $producto['precio_unitario'],
                $subtotal
            ]);

            // Registrar nueva salida de inventario
            $stmt_inventario = $pdo->prepare("INSERT INTO inventarios (tipo_huevo_id, cantidad_cubetas, tipo_movimiento, descripcion) VALUES (?, ?, 'salida', ?)");
            $stmt_inventario->execute([
                $producto['tipo_huevo_id'],
                $producto['cantidad'],
                "Venta actualizada ID: $venta_id"
            ]);
        }

        // Registrar nueva entrada en caja
        $stmt_caja = $pdo->prepare("INSERT INTO caja (tipo_movimiento, monto, descripcion, venta_id) VALUES ('entrada', ?, ?, ?)");
        $stmt_caja->execute([$total, "Venta actualizada", $venta_id]);

        $pdo->commit();

        header("Location: index.php?msg=Venta actualizada exitosamente");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: editar_venta.php?id=$venta_id&msg=ERROR: " . $e->getMessage());
        exit;
    }
}

header("Location: index.php");
exit;
?>
