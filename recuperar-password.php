<?php
//recuperar-password.php
session_start();
require_once 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = cleanInput($_POST['email']);
    
    try {
        $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = "Para recuperar contraseña de personal bibliotecario, consulta al administrador.";
        } else {
            $mensaje = "Se han enviado las instrucciones de recuperación a tu correo.";
        }
    } catch(PDOException $e) {
        $error = "Error al procesar la solicitud.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - BiblioSis</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full px-6 py-8 bg-white shadow-lg rounded-lg">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800">Recuperar Contraseña</h1>
                <p class="text-gray-600 mt-2">Ingresa tu correo electrónico</p>
            </div>

            <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <?php if (isset($mensaje)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

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
                        Enviar Instrucciones
                    </button>
                    
                    <a href="login.php" 
                       class="w-full py-2 px-4 bg-gray-200 text-gray-800 rounded-lg text-center hover:bg-gray-300">
                        ← Volver al Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>