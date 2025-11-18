<?php
// validar_login.php
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
            // --- 2. VERIFICACIÓN DE CONTRASEÑA ---
            if (password_verify($password_ingresada, $usuario['password'])) {

                // INICIO DE SESIÓN EXITOSO
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_rol'] = $usuario['rol'];

                header("Location: dashboard.php");
                exit;

            } else {
                // FALLO EN CREDENCIALES
                header("Location: index.php?error=credenciales");
                exit;
            }

        } else {
            // FALLO EN CREDENCIALES
            header("Location: index.php?error=credenciales");
            exit;
        }

    } catch (Exception $e) {
        die("DEBUG: ERROR CRÍTICO DE CONEXIÓN O PDO: " . $e->getMessage()); 
    }
} else {
    header("Location: index.php");
    exit;
}
?>