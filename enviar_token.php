<?php
// enviar_token.php - Recuperación de contraseña con SendGrid
require 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: recuperar_password.php?error=Correo electrónico inválido");
        exit;
    }

    $pdo = conectarDB();

    try {
        // 1. Verificar si el usuario existe
        $stmt = $pdo->prepare("SELECT id, nombre FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            // 2. Generar token seguro (64 caracteres)
            $token = bin2hex(random_bytes(32));
            $expiracion = date("Y-m-d H:i:s", time() + 3600); // 1 hora

            // 3. Guardar token en la base de datos
            $stmt = $pdo->prepare("UPDATE usuarios SET token_recuperacion = :token, token_expira = :expiracion WHERE id = :id");
            $stmt->execute([
                'token' => $token, 
                'expiracion' => $expiracion, 
                'id' => $usuario['id']
            ]);

            // 4. Preparar el correo con SendGrid
            $email_obj = new \SendGrid\Mail\Mail();
            $email_obj->setFrom(SENDGRID_FROM_EMAIL, SENDGRID_FROM_NAME);
            $email_obj->setSubject("🥚 Recuperación de Contraseña - Huevos Kikes SCM");
            $email_obj->addTo($email, $usuario['nombre']);
            
            // URL de recuperación
            $url_recuperacion = APP_URL . "restablecer_password.php?token=" . $token;
            
            // Contenido HTML del correo
            $htmlContent = "
            <!DOCTYPE html>
            <html lang='es'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <style>
                    body { 
                        font-family: Arial, sans-serif; 
                        background-color: #f4f4f4; 
                        margin: 0;
                        padding: 0;
                    }
                    .email-wrapper {
                        background-color: #f4f4f4;
                        padding: 20px;
                    }
                    .container { 
                        max-width: 600px; 
                        margin: 0 auto; 
                        background-color: #ffffff; 
                        padding: 40px; 
                        border-radius: 10px; 
                        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                    }
                    .header {
                        text-align: center;
                        margin-bottom: 30px;
                    }
                    h1 { 
                        color: #FFD700; 
                        margin: 0;
                        font-size: 28px;
                    }
                    .egg-icon {
                        font-size: 48px;
                        margin-bottom: 10px;
                    }
                    p { 
                        color: #333; 
                        line-height: 1.8; 
                        margin: 15px 0;
                        font-size: 16px;
                    }
                    .button-container {
                        text-align: center;
                        margin: 30px 0;
                    }
                    .button { 
                        display: inline-block; 
                        background-color: #FFD700; 
                        color: #333 !important; 
                        padding: 15px 40px; 
                        text-decoration: none; 
                        border-radius: 8px; 
                        font-weight: bold;
                        font-size: 18px;
                        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                    }
                    .alert { 
                        background: linear-gradient(135deg, #fff3cd, #ffeaa7);
                        border-left: 4px solid #ffc107;
                        padding: 15px; 
                        border-radius: 5px; 
                        margin: 20px 0;
                    }
                    .alert strong {
                        color: #856404;
                    }
                    .url-box {
                        background-color: #f8f9fa;
                        padding: 15px;
                        border-radius: 5px;
                        word-break: break-all;
                        font-size: 14px;
                        margin: 15px 0;
                    }
                    .footer { 
                        margin-top: 40px; 
                        padding-top: 20px; 
                        border-top: 2px solid #e0e0e0; 
                        font-size: 13px; 
                        color: #666; 
                        text-align: center;
                        line-height: 1.6;
                    }
                    .footer p {
                        margin: 5px 0;
                        color: #666;
                    }
                </style>
            </head>
            <body>
                <div class='email-wrapper'>
                    <div class='container'>
                        <div class='header'>
                            <div class='egg-icon'>🥚</div>
                            <h1>Recuperación de Contraseña</h1>
                        </div>
                        
                        <p>Hola <strong>" . htmlspecialchars($usuario['nombre']) . "</strong>,</p>
                        
                        <p>Hemos recibido una solicitud para restablecer tu contraseña en el <strong>Sistema de Gestión Huevos Kikes</strong>.</p>
                        
                        <p>Para crear una nueva contraseña, haz clic en el siguiente botón:</p>
                        
                        <div class='button-container'>
                            <a href='" . $url_recuperacion . "' class='button'>Restablecer mi Contraseña</a>
                        </div>
                        
                        <div class='alert'>
                            <strong>⚠️ Importante:</strong> Este enlace es válido por <strong>1 hora</strong>. Si no solicitaste este cambio, puedes ignorar este mensaje de forma segura.
                        </div>
                        
                        <p><strong>Si el botón no funciona</strong>, copia y pega esta URL en tu navegador:</p>
                        
                        <div class='url-box'>
                            " . $url_recuperacion . "
                        </div>
                        
                        <div class='footer'>
                            <p><strong>🥚 Huevos Kikes SCM</strong></p>
                            <p>Este es un mensaje automático. Por favor, no respondas a este correo.</p>
                            <p>&copy; " . date('Y') . " Huevos Kikes SCM. Todos los derechos reservados.</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            ";

            $email_obj->addContent("text/html", $htmlContent);

            // Versión de texto plano (para clientes que no soportan HTML)
            $textoPlano = "Hola {$usuario['nombre']},\n\n";
            $textoPlano .= "Recuperación de contraseña para Huevos Kikes SCM.\n\n";
            $textoPlano .= "Haz clic en este enlace para restablecer tu contraseña:\n";
            $textoPlano .= $url_recuperacion . "\n\n";
            $textoPlano .= "Este enlace es válido por 1 hora.\n\n";
            $textoPlano .= "Si no solicitaste este cambio, ignora este mensaje.\n\n";
            $textoPlano .= "---\n";
            $textoPlano .= "Huevos Kikes SCM\n";
            $textoPlano .= "Este es un mensaje automático.";
            
            $email_obj->addContent("text/plain", $textoPlano);

            // 5. Enviar el correo
            $sendgrid = new \SendGrid(SENDGRID_API_KEY);
            
            try {
                $response = $sendgrid->send($email_obj);
                
                // Verificar que el envío fue exitoso (códigos 2xx)
                if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                    // Log opcional para debug
                    error_log("✅ Correo enviado exitosamente a: " . $email . " - Status: " . $response->statusCode());
                    
                    header("Location: recuperar_password.php?success=1");
                    exit;
                } else {
                    // Log del error
                    error_log("❌ SendGrid Error - Status: " . $response->statusCode());
                    error_log("❌ SendGrid Error - Body: " . $response->body());
                    
                    header("Location: recuperar_password.php?error=Error al enviar el correo. Intenta nuevamente.");
                    exit;
                }

            } catch (Exception $e) {
                error_log("❌ SendGrid Exception: " . $e->getMessage());
                header("Location: recuperar_password.php?error=Error del servidor de correo. Verifica tu configuración.");
                exit;
            }

        } else {
            // Por seguridad, siempre muestra mensaje de éxito 
            // aunque el email no exista (evita enumerar usuarios)
            error_log("⚠️ Intento de recuperación para email no registrado: " . $email);
            header("Location: recuperar_password.php?success=1");
            exit;
        }

    } catch (PDOException $e) {
        error_log("❌ Database Error: " . $e->getMessage());
        header("Location: recuperar_password.php?error=Error del sistema. Intenta más tarde.");
        exit;
    }
    
} else {
    // Si accede directamente sin POST, redirigir
    header("Location: recuperar_password.php");
    exit;
}
?>