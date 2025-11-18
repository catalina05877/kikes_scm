<?php
// DASHBOARD / PÁGINA DE BIENVENIDA
session_start();

// CRÍTICO: Validar que la sesión exista
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// Obtener datos de sesión
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$rol_usuario = $_SESSION['usuario_rol'] ?? 'N/A';

// Conectar a la base de datos
require 'config/db.php';
$pdo = conectarDB();

// Obtener estadísticas en tiempo real
try {
    // Inventario total (suma de todas las cubetas disponibles)
    $inventario_total = $pdo->query("
        SELECT SUM(
            CASE WHEN tipo_movimiento = 'entrada' THEN cantidad_cubetas ELSE -cantidad_cubetas END
        ) as total_cubetas
        FROM inventarios
    ")->fetchColumn() ?: 0;

    // Ventas del día (suma de totales de ventas de hoy)
    $ventas_dia = $pdo->query("
        SELECT COALESCE(SUM(total), 0) as total_ventas
        FROM ventas
        WHERE DATE(fecha_venta) = CURDATE()
    ")->fetchColumn() ?: 0;

    // Compras pendientes (compras que no han sido pagadas completamente)
    $compras_pendientes = $pdo->query("
        SELECT COUNT(*) as pendientes
        FROM compras
        WHERE estado = 'pendiente'
    ")->fetchColumn() ?: 0;

    // Clientes activos (total de clientes registrados)
    $clientes_activos = $pdo->query("
        SELECT COUNT(*) as total_clientes
        FROM clientes
    ")->fetchColumn() ?: 0;

    // Actividad reciente (últimas 3 operaciones)
    $actividad_reciente = $pdo->query("
        (SELECT 'venta' as tipo, fecha_venta as fecha, CONCAT('Venta registrada - $', total) as descripcion
         FROM ventas ORDER BY fecha_venta DESC LIMIT 3)
        UNION ALL
        (SELECT 'compra' as tipo, fecha_compra as fecha, CONCAT('Compra procesada - $', total) as descripcion
         FROM compras ORDER BY fecha_compra DESC LIMIT 3)
        UNION ALL
        (SELECT 'cliente' as tipo, fecha_registro as fecha, CONCAT('Cliente registrado: ', nombre) as descripcion
         FROM clientes ORDER BY fecha_registro DESC LIMIT 3)
        ORDER BY fecha DESC LIMIT 3
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // En caso de error, usar valores por defecto
    $inventario_total = 0;
    $ventas_dia = 0;
    $compras_pendientes = 0;
    $clientes_activos = 0;
    $actividad_reciente = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Huevos Kikes SCM</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #98c3eeff, #adbdf3ff);
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.6;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #2C3E50;
            color: #ECF0F1;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 {
            color: #FFD700;
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.5em;
        }

        .sidebar .user-info {
            text-align: center;
            margin-bottom: 30px;
            padding: 15px;
            background-color: #34495E;
            border-radius: 8px;
        }

        .sidebar .user-info p {
            margin: 5px 0;
            font-size: 0.9em;
        }

        .sidebar nav ul {
            list-style: none;
            padding: 0;
        }

        .sidebar nav ul li {
            margin-bottom: 10px;
        }

        .sidebar nav ul li a {
            color: #ECF0F1;
            text-decoration: none;
            display: block;
            padding: 12px 15px;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
        }

        .sidebar nav ul li a:hover {
            background-color: #FFD700;
            color: #2C3E50;
        }

        .sidebar .logout {
            margin-top: auto;
            margin-bottom: 20px;
        }

        .sidebar .logout a {
            color: #E74C3C;
            text-decoration: none;
            display: block;
            padding: 12px 15px;
            border-radius: 5px;
            text-align: center;
            background-color: #C0392B;
            transition: background-color 0.3s;
        }

        .sidebar .logout a:hover {
            background-color: #A93226;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 40px;
            background-color: #FFFFFF;
            min-height: 100vh;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px;
            background-color: #34495E;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .header h1 {
            color: #ffffffff;
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .header h1::before {
            content: "🥚 ";
        }

        .header h2 {
            color: #FFD700;
            font-size: 1.8em;
            margin-bottom: 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background-color: #FFFFFF;
            border: 1px solid #9491edff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .stat-card h3 {
            color: #D2B48C;
            margin-top: 0;
            font-size: 1.2em;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #FFD700;
            margin: 10px 0;
        }

        .stat-card p {
            color: #666;
            margin: 0;
            font-size: 0.9em;
        }

        .quick-actions {
            margin-bottom: 40px;
        }

        .quick-actions h2 {
            color: #D2B48C;
            margin-bottom: 20px;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            background-color: #FFD700;
            color: #333;
            padding: 15px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.3s, transform 0.2s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .action-btn:hover {
            background-color: #FFC107;
            transform: translateY(-2px);
        }

        .action-icon {
            font-size: 1.5em;
            margin-right: 10px;
        }

        .recent-activity h2 {
            color: #D2B48C;
            margin-bottom: 20px;
        }

        .activity-list {
            background-color: #FFFFFF;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .activity-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #EEE;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-time {
            color: #666;
            font-size: 0.9em;
            font-weight: bold;
        }

        .activity-desc {
            color: #333;
            flex: 1;
            margin-left: 15px;
        }

        /* Responsivo */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
            }

            .header h1 {
                font-size: 2em;
            }

            .header h2 {
                font-size: 1.5em;
            }

            .modules {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>🥚 KIKES SCM</h2>
            <div class="user-info">
                <p><strong><?php echo htmlspecialchars($nombre_usuario); ?></strong></p>
                <p>Rol: <?php echo htmlspecialchars($rol_usuario); ?></p>
            </div>
            <nav>
                <ul>
                    
                    <li><a href="modulos/proveedores/index.php">Proveedores</a></li>
                    <li><a href="modulos/clientes/index.php">Clientes</a></li>
                    <li><a href="modulos/inventarios/index.php">Inventarios</a></li>
                    <li><a href="modulos/ventas/index.php">Ventas</a></li>
                    <li><a href="modulos/compras/index.php">Compras</a></li>
                    <li><a href="modulos/caja/index.php">Saldo en Caja</a></li>
                </ul>
            </nav>
            <div class="logout">
                <a href="logout.php">Cerrar Sesión</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>¡Bienvenido!</h1>
                <h2>Sistema de Gestión Huevos Kikes</h2>
            </div>

            <!-- Dashboard Content -->
            <div class="dashboard-overview">
                <h2>📊 Resumen del Sistema</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>📦 Inventario Total</h3>
                        <p class="stat-number"><?php echo number_format($inventario_total); ?></p>
                        <p>Cubetas disponibles</p>
                    </div>
                    <div class="stat-card">
                        <h3>💰 Ventas del Día</h3>
                        <p class="stat-number">$<?php echo number_format($ventas_dia, 0, ',', '.'); ?></p>
                        <p>Ingresos hoy</p>
                    </div>
                    <div class="stat-card">
                        <h3>🛒 Compras Pendientes</h3>
                        <p class="stat-number"><?php echo number_format($compras_pendientes); ?></p>
                        <p>Órdenes activas</p>
                    </div>
                    <div class="stat-card">
                        <h3>👥 Clientes Activos</h3>
                        <p class="stat-number"><?php echo number_format($clientes_activos); ?></p>
                        <p>Registrados</p>
                    </div>
                </div>

                <div class="quick-actions">
                    <h2>⚡ Acciones Rápidas</h2>
                    <div class="actions-grid">
                        <a href="modulos/ventas/formulario.php" class="action-btn">
                            <span class="action-icon">💰</span>
                            <span>Nueva Venta</span>
                        </a>
                        <a href="modulos/compras/formulario.php" class="action-btn">
                            <span class="action-icon">🛒</span>
                            <span>Nueva Compra</span>
                        </a>
                        <a href="modulos/inventarios/formulario.php" class="action-btn">
                            <span class="action-icon">📦</span>
                            <span>Actualizar Inventario</span>
                        </a>
                        <a href="modulos/caja/index.php" class="action-btn">
                            <span class="action-icon">💵</span>
                            <span>Ver Saldo</span>
                        </a>
                    </div>
                </div>

                <div class="recent-activity">
                    <h2>📋 Actividad Reciente</h2>
                    <div class="activity-list">
                        <?php if (empty($actividad_reciente)): ?>
                            <div class="activity-item">
                                <span class="activity-time">Sin actividad</span>
                                <span class="activity-desc">No hay actividad reciente registrada</span>
                            </div>
                        <?php else: ?>
                            <?php foreach ($actividad_reciente as $actividad): ?>
                                <div class="activity-item">
                                    <span class="activity-time"><?php echo htmlspecialchars(date('d/m H:i', strtotime($actividad['fecha']))); ?></span>
                                    <span class="activity-desc"><?php echo htmlspecialchars($actividad['descripcion']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
