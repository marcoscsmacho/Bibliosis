<?php
// login.php
session_start();
require_once 'config/config.php';

// Si ya está logueado, redirigir
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Obtener mensaje de error si existe
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']); // Limpiar el mensaje de error
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BiblioSis</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo $basedir; ?>img/favicon.svg">
    <link rel="icon" type="image/png" href="<?php echo $basePath; ?>img/favicon.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full px-6 py-8 bg-white shadow-lg rounded-lg">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800">Identifícate</h1>
            </div>

            <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form action="process_login.php" method="POST" class="space-y-6">
                <div>
                    <input type="email" 
                           name="email" 
                           required 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600" 
                           placeholder="Email"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="relative">
                    <input type="password" 
                           name="password" 
                           required 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600" 
                           placeholder="Contraseña">
                    <button type="button" 
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"
                            onclick="togglePassword(this)">
                        <i class="far fa-eye"></i>
                    </button>
                </div>

                <div class="flex items-center justify-end">
                    <a href="recuperar-password.php" class="text-sm text-purple-600 hover:text-purple-800">
                        Olvidé mi contraseña
                    </a>
                </div>

                <div class="flex flex-col space-y-4">
                    <button type="submit" 
                            class="w-full py-2 px-4 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                        Entrar
                    </button>
                    
                    <a href="index.php" 
                       class="w-full py-2 px-4 bg-gray-200 text-gray-800 rounded-lg text-center hover:bg-gray-300">
                        ← Volver
                    </a>
                </div>
            </form>

            <div class="mt-6 text-center">
                <p class="text-gray-600">
                    ¿No tienes una cuenta? 
                    <a href="registro.php" class="text-purple-600 hover:text-purple-800">Regístrate</a>
                </p>
            </div>
        </div>
    </div>

    <script>
    function togglePassword(button) {
        const input = button.parentElement.querySelector('input');
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    </script>
</body>
</html>