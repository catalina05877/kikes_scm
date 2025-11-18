<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}
require '../../config/db.php';

$pdo = conectarDB();

// Obtener clientes
$clientes = $pdo->query("SELECT id, nombre FROM clientes ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

// Obtener tipos de huevos con stock actual
$tipos_huevos = $pdo->query("SELECT th.*,
    (SELECT SUM(CASE WHEN tipo_movimiento = 'entrada' THEN cantidad_cubetas ELSE 0 END) -
            SUM(CASE WHEN tipo_movimiento = 'salida' THEN cantidad_cubetas ELSE 0 END)
     FROM inventarios WHERE tipo_huevo_id = th.id) as stock_actual
    FROM tipos_huevos th ORDER BY tipo")->fetchAll(PDO::FETCH_ASSOC);

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Venta - Huevos Kikes SCM</title>
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

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        select, input[type="number"] {
            padding: 10px;
            margin-bottom: 15px;
            border: 2px solid #FFD700;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        select:focus, input[type="number"]:focus {
            border-color: #D2B48C;
            outline: none;
        }

        .producto-row {
            display: flex;
            gap: 10px;
            align-items: end;
            margin-bottom: 15px;
            padding: 15px;
            background-color: #FFF8DC;
            border-radius: 8px;
        }

        .producto-row select, .producto-row input {
            flex: 1;
        }

        .remove-product {
            background-color: #DC3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .remove-product:hover {
            background-color: #C82333;
        }

        .add-product {
            background-color: #17A2B8;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .add-product:hover {
            background-color: #138496;
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

            .producto-row {
                flex-direction: column;
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
        <h1>Nueva Venta</h1>

        <?php if ($msg): ?>
            <div class="msg <?php echo strpos($msg, 'ERROR') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <a href="index.php" class="btn">Ver Lista de Ventas</a>

        <form action="procesar_venta.php" method="POST" id="ventaForm">
            <label for="cliente_id">Cliente:</label>
            <select name="cliente_id" id="cliente_id" required>
                <option value="">Seleccionar cliente...</option>
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?php echo $cliente['id']; ?>"><?php echo htmlspecialchars($cliente['nombre']); ?></option>
                <?php endforeach; ?>
            </select>

            <div id="productos">
                <div class="producto-row">
                    <select name="productos[0][tipo_huevo_id]" required>
                        <option value="">Seleccionar tipo de huevo...</option>
                        <?php foreach ($tipos_huevos as $tipo): ?>
                            <option value="<?php echo $tipo['id']; ?>" data-precio="<?php echo $tipo['precio_por_cubeta']; ?>" data-stock="<?php echo $tipo['stock_actual'] ?? 0; ?>">
                                Tipo <?php echo htmlspecialchars($tipo['tipo']); ?> - $<?php echo number_format($tipo['precio_por_cubeta'], 0, ',', '.'); ?> (Stock: <?php echo $tipo['stock_actual'] ?? 0; ?> cubetas)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="productos[0][cantidad]" placeholder="Cantidad de cubetas" min="1" required>
                    <button type="button" class="remove-product" onclick="removeProduct(this)">Remover</button>
                </div>
            </div>

            <button type="button" class="add-product" onclick="addProduct()">Agregar Producto</button>

            <div class="total" id="total">Total: $0</div>

            <button type="submit" class="btn btn-success">Realizar Venta</button>
            <a href="index.php" class="btn">Cancelar</a>
        </form>
    </div>

    <script>
        let productCount = 1;

        function addProduct() {
            const productosDiv = document.getElementById('productos');
            const newRow = document.createElement('div');
            newRow.className = 'producto-row';
            newRow.innerHTML = `
                <select name="productos[${productCount}][tipo_huevo_id]" required>
                    <option value="">Seleccionar tipo de huevo...</option>
                    <?php foreach ($tipos_huevos as $tipo): ?>
                        <option value="<?php echo $tipo['id']; ?>" data-precio="<?php echo $tipo['precio_por_cubeta']; ?>" data-stock="<?php echo $tipo['stock_actual'] ?? 0; ?>">
                            Tipo <?php echo htmlspecialchars($tipo['tipo']); ?> - $<?php echo number_format($tipo['precio_por_cubeta'], 0, ',', '.'); ?> (Stock: <?php echo $tipo['stock_actual'] ?? 0; ?> cubetas)
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="productos[${productCount}][cantidad]" placeholder="Cantidad de cubetas" min="1" required>
                <button type="button" class="remove-product" onclick="removeProduct(this)">Remover</button>
            `;
            productosDiv.appendChild(newRow);
            productCount++;
            updateTotal();
        }

        function removeProduct(button) {
            if (document.querySelectorAll('.producto-row').length > 1) {
                button.parentElement.remove();
                updateTotal();
            }
        }

        function updateTotal() {
            let total = 0;
            document.querySelectorAll('.producto-row').forEach(row => {
                const select = row.querySelector('select');
                const input = row.querySelector('input[type="number"]');
                if (select.value && input.value) {
                    const precio = parseFloat(select.selectedOptions[0].getAttribute('data-precio')) || 0;
                    const cantidad = parseInt(input.value) || 0;
                    total += precio * cantidad;
                }
            });
            document.getElementById('total').textContent = 'Total: $' + total.toLocaleString('es-CO');
        }

        document.getElementById('productos').addEventListener('change', updateTotal);
        document.getElementById('productos').addEventListener('input', updateTotal);
    </script>
</body>
</html>
