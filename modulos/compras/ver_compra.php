<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}
require '../../config/db.php';

$pdo = conectarDB();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: index.php?msg=Compra no encontrada.");
    exit;
}

// Obtener datos de la compra
$stmt = $pdo->prepare("SELECT c.*, p.nombre as proveedor_nombre, p.nit, p.telefono, p.direccion, u.nombre as usuario_nombre
    FROM compras c
    JOIN proveedores p ON c.proveedor_id = p.id
    JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.id = ?");
$stmt->execute([$id]);
$compra = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$compra) {
    header("Location: index.php?msg=Compra no encontrada.");
    exit;
}

// Obtener detalles de la compra
$stmt = $pdo->prepare("SELECT cd.*, th.tipo, th.presentacion
    FROM compra_detalles cd
    JOIN tipos_huevos th ON cd.tipo_huevo_id = th.id
    WHERE cd.compra_id = ?");
$stmt->execute([$id]);
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura de Compra - Huevos Kikes SCM</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #FFF8DC, #F5F5DC);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background-color: #FFFFFF;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 800px;
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

        .factura-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #FFD700;
        }

        .factura-info h2 {
            color: #D2B48C;
            margin-bottom: 10px;
        }

        .proveedor-info, .compra-info {
            margin-bottom: 20px;
        }

        .proveedor-info h3, .compra-info h3 {
            color: #333;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #FFD700;
            color: #333;
            font-weight: bold;
        }

        .total-row {
            background-color: #FFF8DC;
            font-weight: bold;
            font-size: 1.2em;
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

        .btn-primary {
            background-color: #17A2B8;
            color: white;
        }

        .btn-primary:hover {
            background-color: #138496;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #D2B48C;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }

        .back-link a:hover {
            color: #B8860B;
        }

        /* Responsivo */
        @media (max-width: 480px) {
            .container {
                padding: 20px;
                margin: 20px;
            }

            h1 {
                font-size: 2em;
            }

            .factura-header {
                flex-direction: column;
            }

            table {
                font-size: 14px;
            }

            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Factura de Compra</h1>

        <div class="factura-header">
            <div class="factura-info">
                <h2>Huevos Kikes SCM</h2>
                <p><strong>Factura N°:</strong> <?php echo htmlspecialchars($compra['id']); ?></p>
                <p><strong>Fecha:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($compra['fecha_compra']))); ?></p>
            </div>
            <div class="compra-info">
                <h3>Información de la Compra</h3>
                <p><strong>Usuario:</strong> <?php echo htmlspecialchars($compra['usuario_nombre']); ?></p>
                <p><strong>Medio de Pago:</strong> <?php echo htmlspecialchars($compra['medio_pago']); ?></p>
            </div>
        </div>

        <div class="proveedor-info">
            <h3>Datos del Proveedor</h3>
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($compra['proveedor_nombre']); ?></p>
            <p><strong>NIT:</strong> <?php echo htmlspecialchars($compra['nit']); ?></p>
            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($compra['telefono']); ?></p>
            <p><strong>Dirección:</strong> <?php echo htmlspecialchars($compra['direccion']); ?></p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Tipo de Huevo</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $detalle): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($detalle['tipo'] . ' - ' . $detalle['presentacion']); ?></td>
                        <td><?php echo htmlspecialchars($detalle['cantidad']); ?> cubetas</td>
                        <td>$<?php echo number_format($detalle['precio_unitario'], 0, ',', '.'); ?></td>
                        <td>$<?php echo number_format($detalle['cantidad'] * $detalle['precio_unitario'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                    <td>$<?php echo number_format($compra['total'], 0, ',', '.'); ?></td>
                </tr>
            </tbody>
        </table>

        <div style="text-align: center; margin-top: 30px;">
            <a href="generar_pdf.php?id=<?php echo $compra['id']; ?>" class="btn btn-primary" target="_blank">Generar PDF</a>
        </div>

        <div class="back-link">
            <a href="index.php">Volver a Compras</a>
        </div>
    </div>
</body>
</html>
