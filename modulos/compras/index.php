<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}
require '../../config/db.php';

$pdo = conectarDB();

// Obtener compras recientes
$compras = $pdo->query("SELECT c.*, p.nombre as proveedor_nombre, u.nombre as usuario_nombre
    FROM compras c
    JOIN proveedores p ON c.proveedor_id = p.id
    JOIN usuarios u ON c.usuario_id = u.id
    ORDER BY c.id DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Compras - Huevos Kikes SCM</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #FFF8DC, #F5F5DC);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
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

        .msg {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }

        .msg.success {
            background-color: #D4EDDA;
            color: #155724;
            border: 1px solid #C3E6CB;
        }

        .msg.error {
            background-color: #F8D7DA;
            color: #721C24;
            border: 1px solid #F5C6CB;
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

        .actions {
            white-space: nowrap;
        }

        /* Responsivo */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
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
        <h1>Gestión de Compras</h1>

        <?php if ($msg): ?>
            <div class="msg <?php echo strpos($msg, 'ERROR') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <a href="formulario.php" class="btn btn-success">Nueva Compra</a>
        <a href="../../dashboard.php" class="btn">Volver al Dashboard</a>

        <h2>Compras Recientes</h2>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Proveedor</th>
                    <th>Usuario</th>
                    <th>Total</th>
                    <th>Medio de Pago</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($compras)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No hay compras registradas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($compras as $compra): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($compra['fecha_compra']))); ?></td>
                            <td><?php echo htmlspecialchars($compra['proveedor_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($compra['usuario_nombre']); ?></td>
                            <td>$<?php echo number_format($compra['total'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($compra['medio_pago']); ?></td>
                            <td class="actions">
                                <a href="ver_compra.php?id=<?php echo $compra['id']; ?>" class="btn">Ver Detalle</a>
                                <a href="generar_pdf.php?id=<?php echo $compra['id']; ?>" class="btn" target="_blank">Factura PDF</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
