<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}
require '../../config/db.php';

$pdo = conectarDB();

// Obtener tipos de huevos
$tipos = $pdo->query("SELECT * FROM tipos_huevos ORDER BY tipo")->fetchAll(PDO::FETCH_ASSOC);

// Calcular inventario actual por tipo
$inventario = [];
foreach ($tipos as $tipo) {
    $stmt = $pdo->prepare("SELECT
        SUM(CASE WHEN tipo_movimiento = 'entrada' THEN cantidad_cubetas ELSE 0 END) -
        SUM(CASE WHEN tipo_movimiento = 'salida' THEN cantidad_cubetas ELSE 0 END) as total_cubetas
        FROM inventarios WHERE tipo_huevo_id = ?");
    $stmt->execute([$tipo['id']]);
    $total = $stmt->fetchColumn() ?: 0;
    $inventario[$tipo['id']] = [
        'tipo' => $tipo,
        'total_cubetas' => $total,
        'total_unidades' => $total * 30,
        'valor_total' => $total * $tipo['precio_por_cubeta']
    ];
}

// Obtener movimientos recientes
$movimientos = $pdo->query("SELECT i.*, th.tipo, th.precio_por_cubeta
    FROM inventarios i
    JOIN tipos_huevos th ON i.tipo_huevo_id = th.id
    ORDER BY i.fecha DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario - Huevos Kikes SCM</title>
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

        .btn-info {
            background-color: #17A2B8;
            color: white;
        }

        .btn-info:hover {
            background-color: #138496;
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

        .inventario-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .inventario-card {
            background-color: #FFF8DC;
            border: 2px solid #FFD700;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .inventario-card h3 {
            color: #D2B48C;
            margin-bottom: 10px;
        }

        .inventario-card .cantidad {
            font-size: 2em;
            font-weight: bold;
            color: #333;
        }

        .inventario-card .unidades {
            font-size: 1.2em;
            color: #666;
        }

        .inventario-card .valor {
            font-size: 1.5em;
            color: #28A745;
            margin-top: 10px;
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

            .inventario-grid {
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
        <h1>Gestión de Inventario</h1>

        <?php if ($msg): ?>
            <div class="msg <?php echo strpos($msg, 'ERROR') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <a href="formulario.php" class="btn btn-success">Registrar Movimiento</a>
        <a href="exportar.php" class="btn btn-info">Exportar a Excel</a>
        <a href="../.." class="btn">Volver al inicio</a>

        <h2>Inventario Actual</h2>
        <div class="inventario-grid">
            <?php foreach ($inventario as $item): ?>
                <div class="inventario-card">
                    <h3>Tipo <?php echo htmlspecialchars($item['tipo']['tipo']); ?></h3>
                    <div class="cantidad"><?php echo htmlspecialchars($item['total_cubetas']); ?> cubetas</div>
                    <div class="unidades"><?php echo htmlspecialchars($item['total_unidades']); ?> unidades</div>
                    <div class="valor">$<?php echo number_format($item['valor_total'], 0, ',', '.'); ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <h2>Movimientos Recientes</h2>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Movimiento</th>
                    <th>Cantidad</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($movimientos)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No hay movimientos registrados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($movimientos as $mov): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($mov['fecha']))); ?></td>
                            <td><?php echo htmlspecialchars($mov['tipo']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($mov['tipo_movimiento'])); ?></td>
                            <td><?php echo htmlspecialchars($mov['cantidad_cubetas']); ?> cubetas</td>
                            <td><?php echo htmlspecialchars($mov['descripcion'] ?: 'Sin descripción'); ?></td>
                            <td class="actions">
                                <a href="eliminar_inventario.php?id=<?php echo $mov['id']; ?>" class="btn" onclick="return confirm('¿Estás seguro de eliminar este movimiento?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
