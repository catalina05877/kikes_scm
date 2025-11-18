<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}
require '../../config/db.php';

$pdo = conectarDB();
$tipos = $pdo->query("SELECT * FROM tipos_huevos ORDER BY tipo")->fetchAll(PDO::FETCH_ASSOC);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$movimiento = null;
$es_edicion = false;

if ($id) {
    $es_edicion = true;
    $stmt = $pdo->prepare("SELECT * FROM inventarios WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $movimiento = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$movimiento) {
        header("Location: index.php?msg=Movimiento no encontrado.");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $es_edicion ? 'Editar' : 'Registrar'; ?> Movimiento - Huevos Kikes SCM</title>
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
            max-width: 600px;
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

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }

        select, input[type="number"], textarea {
            padding: 12px;
            margin-bottom: 20px;
            border: 2px solid #FFD700;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        select:focus, input[type="number"]:focus, textarea:focus {
            border-color: #D2B48C;
            box-shadow: 0 0 10px rgba(210, 180, 140, 0.5);
            outline: none;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
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
            margin-bottom: 20px;
        }

        button:hover {
            background-color: #FFC107;
            transform: translateY(-2px);
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

            select, input[type="number"], textarea, button {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $es_edicion ? 'Editar' : 'Registrar'; ?> Movimiento de Inventario</h1>

        <form action="procesar_inventario.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $movimiento['id'] ?? ''; ?>">

            <label for="tipo_huevo_id">Tipo de Huevo</label>
            <select id="tipo_huevo_id" name="tipo_huevo_id" required>
                <option value="">Seleccione un tipo</option>
                <?php foreach ($tipos as $tipo): ?>
                    <option value="<?php echo $tipo['id']; ?>" <?php echo ($movimiento['tipo_huevo_id'] ?? '') == $tipo['id'] ? 'selected' : ''; ?>>
                        Tipo <?php echo htmlspecialchars($tipo['tipo']); ?> - <?php echo htmlspecialchars($tipo['presentacion']); ?> - $<?php echo number_format($tipo['precio_por_cubeta'], 0, ',', '.'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="tipo_movimiento">Tipo de Movimiento</label>
            <select id="tipo_movimiento" name="tipo_movimiento" required>
                <option value="entrada" <?php echo ($movimiento['tipo_movimiento'] ?? '') == 'entrada' ? 'selected' : ''; ?>>Entrada</option>
                <option value="salida" <?php echo ($movimiento['tipo_movimiento'] ?? '') == 'salida' ? 'selected' : ''; ?>>Salida</option>
            </select>

            <label for="cantidad_cubetas">Cantidad (Cubetas)</label>
            <input type="number" id="cantidad_cubetas" name="cantidad_cubetas" min="1" value="<?php echo htmlspecialchars($movimiento['cantidad_cubetas'] ?? ''); ?>" required>

            <label for="descripcion">Descripción (Opcional)</label>
            <textarea id="descripcion" name="descripcion"><?php echo htmlspecialchars($movimiento['descripcion'] ?? ''); ?></textarea>

            <button type="submit"><?php echo $es_edicion ? 'Actualizar' : 'Registrar'; ?> Movimiento</button>
        </form>

        <div class="back-link">
            <a href="index.php">Volver al Inventario</a>
        </div>
    </div>
</body>
</html>
