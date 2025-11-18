<?php
// LECTURA (READ) DE PROVEEDORES
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}
require '../../config/db.php';
$pdo = conectarDB();

// Lógica para obtener todos los proveedores
try {
    $stmt = $pdo->query("SELECT * FROM proveedores ORDER BY nombre ASC");
    $proveedores = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al obtener proveedores: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Módulo 1: Gestión de Proveedores - Huevos Kikes SCM</title>
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
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
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

        p {
            color: #666;
            text-align: center;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .add-link {
            display: inline-block;
            background-color: #FFD700;
            color: #333;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.3s, transform 0.2s;
            margin-bottom: 20px;
        }

        .add-link:hover {
            background-color: #FFC107;
            transform: translateY(-2px);
        }

        hr {
            border: none;
            height: 2px;
            background-color: #FFD700;
            margin-bottom: 20px;
        }

        .message {
            color: green;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        .no-providers {
            text-align: center;
            color: #666;
            font-style: italic;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        thead {
            background-color: #FFD700;
            color: #333;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        tbody tr:hover {
            background-color: #FFFACD;
            transition: background-color 0.3s;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        a {
            color: #D2B48C;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }

        a:hover {
            color: #B8860B;
        }

        .actions a {
            margin-right: 10px;
        }

        .actions a:last-child {
            margin-right: 0;
        }

        .back-link {
            display: inline-block;
            background-color: #D2B48C;
            color: #FFFFFF;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.3s, transform 0.2s;
        }

        .back-link:hover {
            background-color: #B8860B;
            transform: translateY(-2px);
        }

        /* Responsivo */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            h1 {
                font-size: 2em;
            }

            table {
                font-size: 14px;
            }

            th, td {
                padding: 8px;
            }

            .add-link, .back-link {
                padding: 10px 15px;
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .container {
                padding: 15px;
            }

            h1 {
                font-size: 1.5em;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 6px;
            }

            .actions a {
                display: block;
                margin-bottom: 5px;
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Módulo 1: Gestión de Proveedores</h1>
        <p>Aquí se administran los proveedores y se verifica la documentación (RUT y Cámara de Comercio).</p>
        <a href="formulario.php" class="add-link">➕ Registrar Nuevo Proveedor</a>
        <hr>

        <?php if (isset($_GET['msg'])): ?>
            <p class="message"><?php echo htmlspecialchars($_GET['msg']); ?></p>
        <?php endif; ?>

        <?php if (empty($proveedores)): ?>
            <p class="no-providers">No hay proveedores registrados aún.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre / Razón Social</th>
                        <th>NIT</th>
                        <th>Documentos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proveedores as $p): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['id']); ?></td>
                        <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($p['nit']); ?></td>
                        <td>
                            <a href="../../<?php echo htmlspecialchars($p['ruta_rut']); ?>" target="_blank">📄 RUT</a> |
                            <a href="../../<?php echo htmlspecialchars($p['ruta_camara_comercio']); ?>" target="_blank">🏢 Cám. Comercio</a>
                        </td>
                        <td class="actions">
                            <a href="formulario.php?id=<?php echo $p['id']; ?>">✏️ Editar</a> |
                            <a href="eliminar_proveedor.php?id=<?php echo $p['id']; ?>"
                               onclick="return confirm('¿Estás seguro de eliminar este proveedor? Esta acción es irreversible.');">🗑️ Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <br><a href="../../dashboard.php" class="back-link">Volver al Dashboard</a>
    </div>
</body>
</html>
