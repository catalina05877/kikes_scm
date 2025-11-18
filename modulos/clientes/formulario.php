<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}
require '../../config/db.php';

$pdo = conectarDB();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$cliente = null;
$es_edicion = false;

if ($id) {
    $es_edicion = true;
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$cliente) {
        header("Location: index.php?msg=Cliente no encontrado.");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $es_edicion ? 'Editar' : 'Agregar'; ?> Cliente - Huevos Kikes SCM</title>
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
            content: " ";
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

        input[type="text"], input[type="tel"], textarea {
            padding: 12px;
            margin-bottom: 20px;
            border: 2px solid #FFD700;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input[type="text"]:focus, input[type="tel"]:focus, textarea:focus {
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

        /* Mapa */
        #map {
            height: 300px;
            width: 100%;
            border: 2px solid #FFD700;
            border-radius: 8px;
            margin-bottom: 20px;
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

            input[type="text"], input[type="tel"], textarea, button {
                font-size: 14px;
            }

            #map {
                height: 250px;
            }
        }
    </style>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAOVYRIgupAurZup5y1PRh8Ismb1A3lLao&callback=initMap" async defer></script>
    <script>
        let map;
        let marker;

        function initMap() {
            const defaultLocation = { lat: 4.6097, lng: -74.0817 }; // Bogotá, Colombia como ejemplo

            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 12,
                center: defaultLocation,
            });

            marker = new google.maps.Marker({
                position: defaultLocation,
                map: map,
                draggable: true,
            });

            // Inicializar ubicación si está vacía
            if (!document.getElementById('ubicacion').value) {
                document.getElementById('ubicacion').value = defaultLocation.lat + ',' + defaultLocation.lng;
            }

            // Actualizar coordenadas al mover el marcador
            marker.addListener('dragend', function() {
                const position = marker.getPosition();
                document.getElementById('ubicacion').value = position.lat() + ',' + position.lng();
            });

            // Centrar mapa en ubicación actual si es edición
            const ubicacionInput = document.getElementById('ubicacion').value;
            if (ubicacionInput) {
                const coords = ubicacionInput.split(',');
                const latLng = { lat: parseFloat(coords[0]), lng: parseFloat(coords[1]) };
                map.setCenter(latLng);
                marker.setPosition(latLng);
            }
        }

        function getCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };
                    map.setCenter(pos);
                    marker.setPosition(pos);
                    document.getElementById('ubicacion').value = pos.lat + ',' + pos.lng();
                });
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1><?php echo $es_edicion ? 'Editar' : 'Agregar'; ?> Cliente</h1>

        <form action="procesar_cliente.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $cliente['id'] ?? ''; ?>">
            <?php
            $lat = $cliente['latitud'] ?? 4.6097;
            $lng = $cliente['longitud'] ?? -74.0817;
            if ($lat == 0 && $lng == 0) {
                $lat = 4.6097;
                $lng = -74.0817;
            }
            ?>
            <input type="hidden" id="ubicacion" name="ubicacion" value="<?php echo htmlspecialchars($lat . ',' . $lng); ?>">

            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($cliente['nombre'] ?? ''); ?>" required>

            <label for="identificacion">Identificación</label>
            <input type="text" id="identificacion" name="identificacion" value="<?php echo htmlspecialchars($cliente['identificacion'] ?? ''); ?>" required>

            <label for="telefono">Teléfono</label>
            <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>" required>

            <label for="direccion">Dirección</label>
            <textarea id="direccion" name="direccion" required><?php echo htmlspecialchars($cliente['direccion'] ?? ''); ?></textarea>

            <label for="map">Ubicación (arrastra el marcador en el mapa)</label>
            <div id="map"></div>
            <button type="button" onclick="getCurrentLocation()">Usar mi ubicación actual</button>

            <button type="submit"><?php echo $es_edicion ? 'Actualizar' : 'Crear'; ?> Cliente</button>
        </form>

        <div class="back-link">
            <a href="index.php">Volver a la lista de clientes</a>
        </div>
    </div>
</body>
</html>
