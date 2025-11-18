<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Huevos Kikes SCM - Login</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: url('img/fondo 2.png') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        .login-container {
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

        .login-container h2 {
            color: #D2B48C;
            margin-bottom: 20px;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .login-container h2::before {
            content: " ";
        }

        .login-container h2::after {
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

        input[type="email"], input[type="password"] {
            padding: 12px;
            margin-bottom: 20px;
            border: 2px solid #FFD700;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input[type="email"]:focus, input[type="password"]:focus {
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

        .forgot-password {
            margin-top: 10px;
        }

        .forgot-password a {
            color: #D2B48C;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }

        .forgot-password a:hover {
            color: #B8860B;
        }

        p {
            margin: 10px 0;
            font-weight: bold;
        }

        /* Responsivo */
        @media (max-width: 480px) {
            .login-container {
                padding: 20px;
                margin: 20px;
            }

            .login-container h2 {
                font-size: 2em;
            }

            input[type="email"], input[type="password"], button {
                font-size: 14px;
            }
        }
    </style>

</head>
<body>
    <div class="login-container">
        <h2>🥚 SISTEMA HUEVOS KIKES</h2>
        
        <?php
        if (isset($_GET['error']) && $_GET['error'] == 'credenciales') {
            echo '<p style="color:red; text-align:center;">Email o contraseña incorrectos.</p>';
        }
        if (isset($_GET['success']) && $_GET['success'] == 'reset') {
            echo '<p style="color:green; text-align:center;">Contraseña restablecida con éxito. Inicia sesión.</p>';
        }
        
        // MENSAJE DE ÉXITO TRAS EL REGISTRO
        if (isset($_GET['success']) && $_GET['success'] == 'registro_ok') {
            echo '<p style="color:green; text-align:center;">¡Registro exitoso! Por favor, inicia sesión.</p>';
        }
        ?>

        <form action="validar_login.php" method="POST">
            <label for="email"><b>Correo Electrónico</b></label>
            <input type="email" placeholder="Ingresa tu email" name="email" required>

            <label for="password"><b>Contraseña</b></label>
            <input type="password" placeholder="Ingresa tu contraseña" name="password" required>

            <button type="submit">Iniciar Sesión</button>
        </form>

        <div class="forgot-password">
            <a href="recuperar_password.php">¿Olvidaste tu Contraseña?</a>
        </div>
        
        <div class="signup-link" style="margin-top: 15px; text-align: center;">
            ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
        </div>
        
    </div>
</body>
</html>