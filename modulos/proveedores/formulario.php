<?php
// CREACIÓN (CREATE) Y EDICIÓN (UPDATE) - Vista del Formulario
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}
require '../../config/db.php';
$pdo = conectarDB();

$es_edicion = false;
$proveedor = ['nombre' => '', 'nit' => '', 'telefono' => '', 'direccion' => ''];

if (isset($_GET['id'])) {
    $es_edicion = true;
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    // Obtener datos del proveedor para llenar el formulario
    try {
        $stmt = $pdo->prepare("SELECT * FROM proveedores WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $proveedor = $stmt->fetch();

        if (!$proveedor) {
            header("Location: index.php?msg=Proveedor no encontrado.");
            exit;
        }
    } catch (PDOException $e) {
        die("Error al cargar datos de edición: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $es_edicion ? 'Editar' : 'Registrar'; ?> Proveedor - Huevos Kikes SCM</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #FFF8DC, #F5F5DC);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
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

        h2 {
            color: #D2B48C;
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 1.5em;
            border-bottom: 2px solid #FFD700;
            padding-bottom: 5px;
        }

        hr {
            border: none;
            height: 2px;
            background-color: #FFD700;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }

        input[type="text"], input[type="file"] {
            padding: 12px;
            margin-bottom: 20px;
            border: 2px solid #FFD700;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input[type="text"]:focus, input[type="file"]:focus {
            border-color: #D2B48C;
            box-shadow: 0 0 10px rgba(210, 180, 140, 0.5);
            outline: none;
        }

        input[type="file"] {
            background-color: #FFFACD;
        }

        small {
            color: #666;
            font-style: italic;
            margin-bottom: 10px;
        }

        small a {
            color: #D2B48C;
            text-decoration: none;
            font-weight: bold;
        }

        small a:hover {
            color: #B8860B;
        }

        p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        button {
            background-color: #FFD700;
            color: #333;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            margin-top: 20px;
        }

        button:hover {
            background-color: #FFC107;
            transform: translateY(-2px);
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
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

            h2 {
                font-size: 1.3em;
            }

            input[type="text"], input[type="file"], button {
                font-size: 14px;
            }

            .back-link {
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

            h2 {
                font-size: 1.2em;
            }

            input[type="text"], input[type="file"], button {
                font-size: 12px;
                padding: 10px;
            }

            .back-link {
                padding: 8px 12px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $es_edicion ? '✏️ Editar Proveedor' : '➕ Registrar Nuevo Proveedor'; ?></h1>
        <hr>

        <form action="procesar_proveedor.php" method="POST" enctype="multipart/form-data">

            <?php if ($es_edicion): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($proveedor['id']); ?>">
            <?php endif; ?>

            <h2>Datos del Proveedor</h2>

            <label for="nombre">Nombre / Razón Social:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($proveedor['nombre']); ?>" required>

            <label for="nit">NIT:</label>
            <input type="text" id="nit" name="nit" value="<?php echo htmlspecialchars($proveedor['nit']); ?>" required>

            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($proveedor['telefono']); ?>">

            <label for="direccion">Dirección:</label>
            <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($proveedor['direccion']); ?>">

            <h2>Documentos (Solo si se requiere actualizar)</h2>
            <p>*Si está editando, solo suba nuevos archivos si desea reemplazar los existentes.</p>

            <label for="rut">RUT del Proveedor (PDF/Imagen):</label>
            <input type="file" id="rut" name="rut" accept=".pdf, .jpg, .png" <?php echo !$es_edicion ? 'required' : ''; ?>>
            <?php if ($es_edicion): ?>
                <small>Actual: <a href="../../<?php echo htmlspecialchars($proveedor['ruta_rut']); ?>" target="_blank">Ver RUT</a></small>
            <?php endif; ?>

            <label for="camara_comercio">Cámara y Comercio (PDF/Imagen):</label>
            <input type="file" id="camara_comercio" name="camara_comercio" accept=".pdf, .jpg, .png" <?php echo !$es_edicion ? 'required' : ''; ?>>
            <?php if ($es_edicion): ?>
                <small>Actual: <a href="../../<?php echo htmlspecialchars($proveedor['ruta_camara_comercio']); ?>" target="_blank">Ver Cámara y Comercio</a></small>
            <?php endif; ?>

            <button type="submit"><?php echo $es_edicion ? 'Guardar Cambios' : 'Registrar Proveedor'; ?></button>
        </form>

        <a href="index.php" class="back-link">Volver a la Lista</a>
    </div>
</body>
</html>
