<?php
// validar_login.php - CON DEBUG PARA SOLUCIONAR FALLA DE LOGIN
session_start();
require 'config/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password_ingresada = $_POST['password'] ?? ''; 

    try {
        $pdo = conectarDB();
        
        // --- 1. INTENTO DE ENCONTRAR AL USUARIO ---
        $stmt = $pdo->prepare("SELECT id, nombre, email, password, rol FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            echo "DEBUG: Usuario encontrado. Hash en BD: " . $usuario['password'] . "<br>";
            
            // --- 2. VERIFICACIÓN DE CONTRASEÑA ---
            if (password_verify($password_ingresada, $usuario['password'])) {
                
                // INICIO DE SESIÓN EXITOSO
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                
                echo "DEBUG: ¡Contraseña verificada correctamente! Redirigiendo...";
                header("Location: dashboard.php");
                exit;

            } else {
                echo "DEBUG: ERROR: El HASH de la BD NO coincide con la contraseña ingresada. Falla password_verify().";
                // FALLO EN CREDENCIALES
                // header("Location: index.php?error=credenciales");
                // exit;
            }

        } else {
            echo "DEBUG: ERROR: Usuario NO encontrado en la base de datos.";
            // FALLO EN CREDENCIALES
            // header("Location: index.php?error=credenciales");
            // exit;
        }

    } catch (Exception $e) {
        die("DEBUG: ERROR CRÍTICO DE CONEXIÓN O PDO: " . $e->getMessage()); 
    }
} else {
    header("Location: index.php");
    exit;
}
?>