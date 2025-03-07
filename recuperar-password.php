<?php
//recuperar-password.php
session_start();
require_once 'config/config.php';

// Añadir requisitos para PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Cargar el autoloader de Composer (ajusta la ruta si es necesario)
require 'vendor/autoload.php';

// Verificar si hay una solicitud POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = cleanInput($_POST['email']);
    
    try {
        // Primero, verificar si es personal de biblioteca
        $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            // Es personal de biblioteca
            $error = "Para recuperar contraseña de personal bibliotecario, consulta al administrador.";
        } else {
            // Verificar si el email existe como cliente
            $stmt = $pdo->prepare("SELECT id_cliente FROM clientes WHERE email = ? AND estado = 'Activo'");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();
            
            if ($usuario) {
                // Generar token aleatorio como contraseña temporal (8 caracteres)
                $token = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8);
                
                // Hashear el token para guardar en la BD
                $hashed_token = password_hash($token, PASSWORD_DEFAULT);
                
                // Actualizar la contraseña del usuario con el token hasheado
                $stmt = $pdo->prepare("UPDATE clientes SET password = ? WHERE email = ?");
                $stmt->execute([$hashed_token, $email]);
                
                // Enviar correo con la contraseña temporal usando PHPMailer
                try {
                    // Configuración de PHPMailer
                    $mail = new PHPMailer(true);
                    
                    // Configuración del servidor SMTP
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com'; // Ajusta según tu servidor
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'bibliosissoporte@gmail.com'; // Tu correo
                    $mail->Password   = 'fyledtlmlhexuurx'; // Tu contraseña de aplicación
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->CharSet    = 'UTF-8';
                    
                    // Remitente y destinatario
                    $mail->setFrom('bibliosissoporte@gmail.com', 'BiblioSis');
                    $mail->addAddress($email);
                    
                    // Contenido del correo
                    $mail->isHTML(true);
                    $mail->Subject = 'Tu contraseña temporal - BiblioSis';
                    $mail->Body = "
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                .header { background-color: #7c3aed; color: white; padding: 15px; text-align: center; }
                                .content { padding: 20px; border: 1px solid #ddd; }
                                .password { font-size: 24px; color: #7c3aed; text-align: center; padding: 15px; margin: 15px 0; background-color: #f8f5ff; border-radius: 5px; }
                                .footer { text-align: center; margin-top: 30px; color: #777; font-size: 12px; }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>
                                    <h2>Recuperación de Contraseña</h2>
                                </div>
                                <div class='content'>
                                    <p>Hola,</p>
                                    <p>Has solicitado restablecer tu contraseña en BiblioSis. Hemos generado una contraseña temporal para ti:</p>
                                    <div class='password'><strong>$token</strong></div>
                                    <p>Por favor, utiliza esta contraseña para iniciar sesión. Una vez dentro, te recomendamos cambiarla inmediatamente desde tu perfil por razones de seguridad.</p>
                                    <p>Si no has solicitado este cambio, por favor contacta al administrador de BiblioSis.</p>
                                    <p>Saludos,<br>El equipo de BiblioSis</p>
                                </div>
                                <div class='footer'>
                                    Este correo es automático, por favor no responder.
                                </div>
                            </div>
                        </body>
                        </html>
                    ";
                    
                    // Enviar el correo
                    $mail->send();
                    $mensaje = "Se ha enviado una contraseña temporal a tu correo electrónico.";
                    error_log("Contraseña temporal enviada a $email: $token");
                } catch (Exception $e) {
                    // En caso de error, mostrar la contraseña para desarrollo
                    error_log("Error al enviar correo: {$mail->ErrorInfo}");
                    $mensaje = "No se pudo enviar el correo, pero puedes usar esta contraseña temporal: <strong>$token</strong><br><br>
                                Utiliza esta contraseña para iniciar sesión y luego cámbiala desde tu perfil.";
                }
            } else {
                $error = "No existe una cuenta activa con ese correo electrónico.";
            }
        }
    } catch(PDOException $e) {
        error_log($e->getMessage());
        $error = "Error al procesar la solicitud: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - BiblioSis</title>
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <link rel="icon" type="image/png" href="img/favicon.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full px-6 py-8 bg-white shadow-lg rounded-lg">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800">Recuperar Contraseña</h1>
                <p class="text-gray-600 mt-2">Ingresa tu correo electrónico para recibir una contraseña temporal</p>
            </div>

            <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <?php if (isset($mensaje)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <?php echo $mensaje; // No usamos htmlspecialchars para permitir HTML ?>
                <p class="mt-3">
                    <a href="login.php" class="text-green-700 underline">Ir a iniciar sesión</a>
                </p>
            </div>
            <?php else: ?>
            <form action="recuperar-password.php" method="POST" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Correo Electrónico
                    </label>
                    <input type="email" 
                           id="email"
                           name="email" 
                           required 
                           class="mt-1 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600" 
                           placeholder="tucorreo@ejemplo.com">
                </div>

                <div class="flex flex-col space-y-4">
                    <button type="submit" 
                            class="w-full py-2 px-4 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        Generar Contraseña Temporal
                    </button>
                    
                    <a href="login.php" 
                       class="w-full py-2 px-4 bg-gray-200 text-gray-800 rounded-lg text-center hover:bg-gray-300">
                        ← Volver al Login
                    </a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>