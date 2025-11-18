<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}
require '../../config/db.php';

$pdo = conectarDB();

// Obtener saldo actual en caja
$saldo_actual = $pdo->query("SELECT saldo FROM caja ORDER BY id DESC LIMIT 1")->fetchColumn() ?: 0;

// Obtener movimientos recientes (últimos 50)
$movimientos = $pdo->query("
    SELECT
        c.fecha,
        c.descripcion,
        CASE
            WHEN c.saldo IS NOT NULL THEN 'saldo_inicial'
            WHEN c.venta_id IS NOT NULL THEN 'venta'
            WHEN c.descripcion LIKE '%Compra%' THEN 'compra'
            ELSE 'otro'
        END as tipo_movimiento,
        CASE
            WHEN c.saldo IS NOT NULL THEN c.saldo
            WHEN c.venta_id IS NOT NULL THEN (SELECT total FROM ventas WHERE id = c.venta_id)
            WHEN c.descripcion LIKE '%Compra%' THEN -(SELECT total FROM compras WHERE id = (SELECT compra_id FROM compra_detalles WHERE compra_id IN (SELECT id FROM compras WHERE fecha_compra = c.fecha) LIMIT 1))
            ELSE 0
        END as monto,
        c.saldo as saldo_actual
    FROM caja c
    ORDER BY c.fecha DESC
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);

// Calcular totales
$total_ingresos = 0;
$total_egresos = 0;
foreach ($movimientos as $mov) {
    if ($mov['tipo_movimiento'] == 'venta' && $mov['monto'] > 0) {
        $total_ingresos += $mov['monto'];
    } elseif ($mov['tipo_movimiento'] == 'compra' && $mov['monto'] < 0) {
        $total_egresos += abs($mov['monto']);
    }
}

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Saldo en Caja - Huevos Kikes SCM</title>
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
            content: "💰 ";
        }

        h1::after {
            content: " 💰";
        }

        .saldo-principal {
            text-align: center;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(255, 215, 0, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        .saldo-principal h2 {
            margin: 0 0 10px 0;
            font-size: 1.2em;
            opacity: 0.9;
        }

        .saldo-principal .monto {
            font-size: 3em;
            font-weight: bold;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .resumen {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .resumen-card {
            background-color: #FFF8DC;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .resumen-card.ingresos {
            border-left: 5px solid #28A745;
        }

        .resumen-card.egresos {
            border-left: 5px solid #DC3545;
        }

        .resumen-card h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 1.1em;
        }

        .resumen-card .monto {
            font-size: 1.8em;
            font-weight: bold;
            margin: 0;
        }

        .resumen-card.ingresos .monto {
            color: #28A745;
        }

        .resumen-card.egresos .monto {
            color: #DC3545;
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

        .tipo-movimiento {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: bold;
            text-transform: uppercase;
        }

        .tipo-venta {
            background-color: #28A745;
            color: white;
        }

        .tipo-compra {
            background-color: #DC3545;
            color: white;
        }

        .tipo-saldo {
            background-color: #17A2B8;
            color: white;
        }

        .tipo-otro {
            background-color: #6C757D;
            color: white;
        }

        .monto-positivo {
            color: #28A745;
            font-weight: bold;
        }

        .monto-negativo {
            color: #DC3545;
            font-weight: bold;
        }

        /* Responsivo */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .saldo-principal {
                padding: 20px;
            }

            .saldo-principal .monto {
                font-size: 2em;
            }

            .resumen {
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
        <h1>Saldo en Caja</h1>

        <?php if ($msg): ?>
            <div class="msg <?php echo strpos($msg, 'ERROR') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <div class="saldo-principal">
            <h2>Saldo Actual</h2>
            <div class="monto">$<?php echo number_format($saldo_actual, 0, ',', '.'); ?></div>
        </div>

        <div class="resumen">
            <div class="resumen-card ingresos">
                <h3>Ingresos (Ventas)</h3>
                <div class="monto">$<?php echo number_format($total_ingresos, 0, ',', '.'); ?></div>
            </div>
            <div class="resumen-card egresos">
                <h3>Egresos (Compras)</h3>
                <div class="monto">$<?php echo number_format($total_egresos, 0, ',', '.'); ?></div>
            </div>
        </div>

        <a href="../../dashboard.php" class="btn">Volver al Dashboard</a>

        <h2>Movimientos Recientes</h2>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Monto</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($movimientos)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">No hay movimientos registrados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($movimientos as $mov): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($mov['fecha']))); ?></td>
                            <td>
                                <span class="tipo-movimiento tipo-<?php echo $mov['tipo_movimiento']; ?>">
                                    <?php
                                    switch ($mov['tipo_movimiento']) {
                                        case 'venta': echo 'Venta'; break;
                                        case 'compra': echo 'Compra'; break;
                                        case 'saldo_inicial': echo 'Saldo Inicial'; break;
                                        default: echo 'Otro'; break;
                                    }
                                    ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($mov['descripcion']); ?></td>
                            <td class="<?php echo $mov['monto'] >= 0 ? 'monto-positivo' : 'monto-negativo'; ?>">
                                <?php if ($mov['monto'] != 0): ?>
                                    <?php echo ($mov['monto'] > 0 ? '+' : '') . '$' . number_format($mov['monto'], 0, ',', '.'); ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>$<?php echo number_format($mov['saldo_actual'] ?: 0, 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
