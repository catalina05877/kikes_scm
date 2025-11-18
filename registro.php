<?php
// Módulo de Registro (Sign-up)
session_start();
require 'config/db.php'; 

$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    // Asignamos 'Vendedor' por defecto, ya que 'Administrador' debe ser reservado.
    $rol = 'Vendedor'; 

    if (strlen($password) < 6) {
        $mensaje = "ERROR: La contraseña debe tener al menos 6 caracteres.";
    } else {
        // Encriptar la contraseña usando password_hash()
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo = conectarDB();

        try {
            // Insertar el nuevo usuario en la tabla
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (:nombre, :email, :password, :rol)");
            $stmt->execute([
                'nombre' => $nombre,
                'email' => $email,
                'password' => $password_hash,
                'rol' => $rol
            ]);

            // Redirigir al login con mensaje de éxito
            header("Location: index.php?success=registro_ok");
            exit;

        } catch (PDOException $e) {
            // Manejar error de email duplicado (código 23000)
            if ($e->getCode() == '23000') {
                $mensaje = "ERROR: El correo electrónico ya está registrado.";
            } else {
                $mensaje = "ERROR en la base de datos: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario - Huevos Kikes SCM</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #FFD700, #FFFFFF);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        .register-container {
            background-color: #FFFFFF;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            position: relative;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .register-container h1 {
            color: #D2B48C;
            margin-bottom: 20px;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .register-container h1::before {
            content: " ";
        }

        .register-container h1::after {
            content: " ";
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            text-align: left;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            padding: 12px;
            margin-bottom: 20px;
            border: 2px solid #FFD700;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus {
            border-color: #D2B48C;
            box-shadow: 0 0 10px rgba(210, 180, 140, 0.5);
            outline: none;
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
            margin-top: 10px;
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

        p {
            margin: 10px 0;
            font-weight: bold;
        }

        /* Responsivo */
        @media (max-width: 480px) {
            .register-container {
                padding: 20px;
                margin: 20px;
            }

            .register-container h1 {
                font-size: 2em;
            }

            input[type="text"], input[type="email"], input[type="password"], button {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h1>Crear Nueva Cuenta</h1>

        <?php if ($mensaje): ?>
            <p style="color:red;"><?php echo $mensaje; ?></p>
        <?php endif; ?>

        <form method="POST" action="registro.php">
            <label for="nombre">Nombre Completo:</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Contraseña (Mínimo 6 caracteres):</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Registrarse</button>
        </form>

        <div class="back-link">
            <a href="index.php">Volver al inicio</a>
        </div>
    </div>
</body>
</html>
