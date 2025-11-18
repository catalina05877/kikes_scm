<?php
// recuperar_password.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Huevos Kikes SCM</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #FFD700, #FFFFFF);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .recover-container {
            background-color: #FFFFFF;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .recover-container h1 {
            color: #D2B48C;
            margin-bottom: 10px;
            font-size: 2.2em;
        }

        .recover-container h1::before {
            content: "🥚 ";
        }

        .recover-container p {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        form {
            display: flex;
            flex-direction: column;
            text-align: left;
        }

        label {
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }

        input[type="email"] {
            padding: 12px;
            margin-bottom: 20px;
            border: 2px solid #FFD700;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }

        input[type="email"]:focus {
            border-color: #D2B48C;
            box-shadow: 0 0 10px rgba(210, 180, 140, 0.3);
            outline: none;
        }

        button {
            background-color: #FFD700;
            color: #333;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        button:hover {
            background-color: #FFC107;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .back-link {
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

        @media (max-width: 480px) {
            .recover-container {
                padding: 25px;
                margin: 15px;
            }

            .recover-container h1 {
                font-size: 1.8em;
            }
        }
    </style>
</head>
<body>
    <div class="recover-container">
        <h1>Recuperar Contraseña</h1>
        <p>Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="message error">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="message success">
                ✅ Correo enviado. Revisa tu bandeja de entrada (y spam).
            </div>
        <?php endif; ?>

        <form action="enviar_token.php" method="POST">
            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" required placeholder="tu@email.com">
            <button type="submit">Enviar Enlace de Recuperación</button>
        </form>

        <div class="back-link">
            <a href="index.php">← Volver al Inicio de Sesión</a>
        </div>
    </div>
</body>
</html>