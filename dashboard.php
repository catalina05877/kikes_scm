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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Huevos Kikes SCM</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #FFF8DC, #F5F5DC);
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #FFFFFF;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 40px;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h1 {
            color: #D2B48C;
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        h1::before {
            content: "🥚 ";
        }

        h2 {
            color: #FFD700;
            text-align: center;
            font-size: 1.8em;
            margin-bottom: 30px;
        }

        .welcome-info {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px;
            background-color: #FFF8DC;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .welcome-info p {
            font-size: 1.1em;
            margin: 0;
        }

        .modules {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .module-card {
            background-color: #FFFFFF;
            border: 1px solid #FFD700;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .module-card h3 {
            color: #D2B48C;
            margin-top: 0;
            font-size: 1.4em;
        }

        .module-card p {
            color: #666;
            margin-bottom: 15px;
        }

        .module-card a {
            display: inline-block;
            background-color: #FFD700;
            color: #333;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .module-card a:hover {
            background-color: #FFC107;
        }

        .logout {
            text-align: right;
        }

        .logout a {
            color: #D2B48C;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }

        .logout a:hover {
            color: #B8860B;
        }

        /* Responsivo */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            h1 {
                font-size: 2em;
            }

            h2 {
                font-size: 1.5em;
            }

            .modules {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>¡Bienvenido, <?php echo htmlspecialchars($nombre_usuario); ?>!</h1>
        <h2>Sistema de Gestión Huevos kikes</h2>

        <div class="welcome-info">
            <p>Has iniciado sesión con el rol de: <b><?php echo htmlspecialchars($rol_usuario); ?></b></p>
        </div>

        <h2>Módulos del Sistema:</h2>
        <div class="modules">
            <div class="module-card">
                <h3>Módulo Gestión de proveedores</h3>
                <p>Registro, edición, y manejo de la documentación (RUT, Cámara y Comercio) de los proveedores.</p>
                <a href="modulos/proveedores/index.php">Ir a Gestión de Proveedores</a>
            </div>

            <div class="module-card">
                <h3>Módulo Gestión de Clientes</h3>
                <p>Registro, edición y eliminación de clientes con selección de ubicación mediante Google Maps.</p>
                <a href="modulos/clientes/index.php">Ir a Gestión de Clientes</a>
            </div>

            <div class="module-card">
    <h3>Módulo Gestión de Inventarios</h3>
    <p>Control de entradas y salidas de huevos, gestión de stock por tipo y generación de reportes.</p>
    <a href="modulos/inventarios/index.php">Ir a Gestión de Inventarios</a>
</div>

            <div class="module-card">
                <h3>Módulo de Ventas</h3>
                <p>Realizar ventas de huevos a clientes registrados, generar facturas PDF y controlar el saldo en caja.</p>
                <a href="modulos/ventas/index.php">Ir a Ventas</a>
            </div>

            <div class="module-card">
                <h3>Módulo de Compras</h3>
                <p>Realizar compras de huevos a proveedores registrados, generar facturas de compra PDF y controlar el saldo en caja.</p>
                <a href="modulos/compras/index.php">Ir a Compras</a>
            </div>

            <div class="module-card">
                <h3>Módulo Saldo en Caja</h3>
                <p>Visualizar el saldo actual en caja, ingresos por ventas y egresos por compras con historial de movimientos.</p>
                <a href="modulos/caja/index.php">Ir a Saldo en Caja</a>
            </div>

        </div>

        <div class="logout">
            <a href="logout.php">Cerrar Sesión</a>
        </div>
    </div>
</body>
</html>
