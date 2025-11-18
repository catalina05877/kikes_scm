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

// Obtener datos de la venta
$stmt = $pdo->prepare("SELECT v.*, c.nombre as cliente_nombre, c.direccion, c.telefono,
    u.nombre as usuario_nombre
    FROM ventas v
    JOIN clientes c ON v.cliente_id = c.id
    JOIN usuarios u ON v.usuario_id = u.id
    WHERE v.id = ?");
$stmt->execute([$id]);
$venta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venta) {
    header("Location: index.php?msg=ERROR: Venta no encontrada");
    exit;
}

// Obtener detalle de la venta
$stmt_detalle = $pdo->prepare("SELECT dv.*, th.tipo, th.presentacion
    FROM detalle_venta dv
    JOIN tipos_huevos th ON dv.producto_id = th.id
    WHERE dv.venta_id = ?");
$stmt_detalle->execute([$id]);
$detalles = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Venta - Huevos Kikes SCM</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #FFD700, #FFFFFF);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #FFFFFF;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h1 {
            color: #D2B48C;
            text-align: center;
            margin-bottom: 20px;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1::before {
            content: "🥚 ";
        }

        h1::after {
            content: " 🥚";
        }

        .btn {
            background-color: #FFD700;
            color: #333;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin: 10px 5px;
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn:hover {
            background-color: #FFC107;
            transform: translateY(-2px);
        }

        .btn-success {
            background-color: #28A745;
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .venta-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-card {
            background-color: #FFF8DC;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .info-card h3 {
            color: #D2B48C;
            margin-bottom: 10px;
            font-size: 1.2em;
        }

        .info-card p {
            margin: 5px 0;
            line-height: 1.5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #DDD;
        }

        th {
            background-color: #FFD700;
            color: #333;
            font-weight: bold;
        }

        tr:hover {
            background-color: #FFF8DC;
        }

        .total {
            font-size: 1.5em;
            font-weight: bold;
            color: #28A745;
            text-align: right;
            margin-top: 20px;
            padding: 10px;
            background-color: #FFF8DC;
            border-radius: 5px;
        }

        /* Responsivo */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .venta-info {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 14px;
            }

            th, td {
                padding: 8px;
            }

            .btn {
                padding: 8px 16px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Detalle de Venta #<?php echo str_pad($venta['id'], 6, '0', STR_PAD_LEFT); ?></h1>

        <div class="venta-info">
            <div class="info-card">
                <h3>Información de la Venta</h3>
                <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($venta['fecha_venta'])); ?></p>
                <p><strong>Vendedor:</strong> <?php echo htmlspecialchars($venta['usuario_nombre']); ?></p>
                <p><strong>Total:</strong> $<?php echo number_format($venta['total'], 0, ',', '.'); ?></p>
            </div>

            <div class="info-card">
                <h3>Datos del Cliente</h3>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($venta['cliente_nombre']); ?></p>
                <p><strong>Dirección:</strong> <?php echo htmlspecialchars($venta['direccion']); ?></p>
                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($venta['telefono']); ?></p>
                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($venta['telefono']); ?></p>
            </div>
        </div>

        <h2>Productos Vendidos</h2>
        <table>
            <thead>
                <tr>
                    <th>Tipo de Huevo</th>
                    <th>Presentación</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $detalle): ?>
                    <tr>
                        <td>Tipo <?php echo htmlspecialchars($detalle['tipo']); ?></td>
                        <td><?php echo htmlspecialchars($detalle['presentacion']); ?></td>
                        <td><?php echo $detalle['cantidad_cubetas']; ?> cubetas</td>
                        <td>$<?php echo number_format($detalle['precio_unitario'], 0, ',', '.'); ?></td>
                        <td>$<?php echo number_format($detalle['subtotal'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total">
            Total de la Venta: $<?php echo number_format($venta['total'], 0, ',', '.'); ?>
        </div>

        <a href="generar_pdf.php?id=<?php echo $venta['id']; ?>" class="btn btn-success" target="_blank">Generar Factura PDF</a>
        <a href="index.php" class="btn">Volver a Ventas</a>
    </div>
</body>
</html>
