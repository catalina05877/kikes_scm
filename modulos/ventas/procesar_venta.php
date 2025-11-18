<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}
require '../../config/db.php';

$pdo = conectarDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = $_POST['cliente_id'] ?? null;
    $productos = $_POST['productos'] ?? [];

    if (!$cliente_id || empty($productos)) {
        header("Location: formulario.php?msg=ERROR: Datos incompletos");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Verificar stock y calcular total
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

        // Insertar venta
        $stmt = $pdo->prepare("INSERT INTO ventas (cliente_id, usuario_id, total) VALUES (?, ?, ?)");
        $stmt->execute([$cliente_id, $_SESSION['usuario_id'], $total]);
        $venta_id = $pdo->lastInsertId();

        // Insertar detalle de venta
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

            // Registrar salida de inventario
            $stmt_inventario = $pdo->prepare("INSERT INTO inventarios (tipo_huevo_id, cantidad_cubetas, tipo_movimiento, descripcion) VALUES (?, ?, 'salida', ?)");
            $stmt_inventario->execute([
                $producto['tipo_huevo_id'],
                $producto['cantidad'],
                "Venta ID: $venta_id"
            ]);
        }

        // Registrar entrada en caja
        $stmt_caja = $pdo->prepare("INSERT INTO caja (tipo_movimiento, monto, descripcion, venta_id) VALUES ('entrada', ?, ?, ?)");
        $stmt_caja->execute([$total, "Venta realizada", $venta_id]);

        $pdo->commit();

        header("Location: index.php?msg=Venta realizada exitosamente");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: formulario.php?msg=ERROR: " . $e->getMessage());
        exit;
    }
}

header("Location: formulario.php");
exit;
?>
