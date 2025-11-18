<?php
// restablecer_password.php
require 'config/db.php';

$mensaje_error = '';
$mensaje_exito = '';
$token_valido = false;
$usuario_id = null;

// Verificar si hay un token en la URL
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    $pdo = conectarDB();

    try {
        // Verificar que el token existe y no ha expirado
        $stmt = $pdo->prepare("SELECT id, nombre, token_expira FROM usuarios WHERE token_recuperacion = :token");
        $stmt->execute(['token' => $token]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            // Verificar si el token no ha expirado
            $ahora = date("Y-m-d H:i:s");
            if ($usuario['token_expira'] > $ahora) {
                $token_valido = true;
                $usuario_id = $usuario['id'];
            } else {
                $mensaje_error = "⏱️ Este enlace ha expirado. Solicita uno nuevo.";
            }
        } else {
            $mensaje_error = "🔒 Enlace inválido o ya utilizado.";
        }

    } catch (PDOException $e) {
        $mensaje_error = "❌ Error del sistema. Intenta más tarde.";
        error_log("Error verificando token: " . $e->getMessage());
    }
}

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nueva_password'])) {
    $nueva_password = $_POST['nueva_password'];
    $confirmar_password = $_POST['confirmar_password'];
    $token = $_POST['token'];
    
    // Validaciones
    if (empty($nueva_password)) {
        $mensaje_error = "⚠️ La contraseña no puede estar vacía.";
    } elseif (strlen($nueva_password) < 6) {
        $mensaje_error = "⚠️ La contraseña debe tener al menos 6 caracteres.";
    } elseif ($nueva_password !== $confirmar_password) {
        $mensaje_error = "⚠️ Las contraseñas no coinciden.";
    } else {
        $pdo = conectarDB();
        
        try {
            // Verificar nuevamente el token
            $stmt = $pdo->prepare("SELECT id, token_expira FROM usuarios WHERE token_recuperacion = :token");
            $stmt->execute(['token' => $token]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && $usuario['token_expira'] > date("Y-m-d H:i:s")) {
                // Encriptar la nueva contraseña
                $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
                
                // Actualizar la contraseña y eliminar el token
                $stmt = $pdo->prepare("UPDATE usuarios SET password = :password, token_recuperacion = NULL, token_expira = NULL WHERE id = :id");
                $stmt->execute([
                    'password' => $password_hash,
                    'id' => $usuario['id']
                ]);
                
                $mensaje_exito = "✅ ¡Contraseña actualizada exitosamente! Ya puedes iniciar sesión.";
                $token_valido = false; // Ocultar el formulario
                
                error_log("✅ Contraseña actualizada para usuario ID: " . $usuario['id']);
                
            } else {
                $mensaje_error = "⏱️ El enlace ha expirado o es inválido.";
            }

        } catch (PDOException $e) {
            $mensaje_error = "❌ Error al actualizar la contraseña. Intenta nuevamente.";
            error_log("Error actualizando password: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Huevos Kikes SCM</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #FFD700, #FFFFFF);
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background-color: #FFFFFF;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h1 {
            color: #D2B48C;
            text-align: center;
            margin-bottom: 10px;
            font-size: 2.2em;
        }

        h1::before {
            content: "🥚 ";
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1em;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }

        input[type="password"] {
            padding: 12px;
            margin-bottom: 20px;
            border: 2px solid #FFD700;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }

        input[type="password"]:focus {
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

        .password-requirements {
            background-color: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #0c5460;
            border-radius: 5px;
        }

        .password-requirements ul {
            margin: 5px 0 0 0;
            padding-left: 20px;
        }

        @media (max-width: 480px) {
            .container {
                padding: 25px;
                margin: 10px;
            }

            h1 {
                font-size: 1.8em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Restablecer Contraseña</h1>
        
        <?php if ($mensaje_error): ?>
            <div class="alert alert-error">
                <?php echo $mensaje_error; ?>
            </div>
        <?php endif; ?>

        <?php if ($mensaje_exito): ?>
            <div class="alert alert-success">
                <?php echo $mensaje_exito; ?>
            </div>
            <div class="back-link">
                <a href="index.php">→ Ir al inicio de sesión</a>
            </div>
        <?php elseif ($token_valido): ?>
            <p class="subtitle">Ingresa tu nueva contraseña</p>
            
            <div class="password-requirements">
                <strong>📋 Requisitos:</strong>
                <ul>
                    <li>Mínimo 6 caracteres</li>
                    <li>Ambas contraseñas deben coincidir</li>
                </ul>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                
                <label for="nueva_password">Nueva Contraseña:</label>
                <input type="password" id="nueva_password" name="nueva_password" required minlength="6" placeholder="Mínimo 6 caracteres">

                <label for="confirmar_password">Confirmar Contraseña:</label>
                <input type="password" id="confirmar_password" name="confirmar_password" required minlength="6" placeholder="Repite la contraseña">

                <button type="submit">🔒 Actualizar Contraseña</button>
            </form>
        <?php else: ?>
            <?php if (!$mensaje_exito && !$mensaje_error): ?>
                <div class="alert alert-error">
                    🔒 Enlace inválido o no proporcionado.
                </div>
            <?php endif; ?>
            
            <div class="back-link">
                <a href="recuperar_password.php">← Solicitar nuevo enlace</a>
            </div>
        <?php endif; ?>

        <?php if (!$mensaje_exito): ?>
            <div class="back-link" style="margin-top: 15px;">
                <a href="index.php">Volver al inicio</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>