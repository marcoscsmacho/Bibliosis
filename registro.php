<?php
//registro.php
session_start();
require_once 'config/config.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = cleanInput($_POST['nombre']);
    $apellido = cleanInput($_POST['apellido']);
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    $confirmar_password = $_POST['confirmar_password'];
    $telefono = cleanInput($_POST['telefono']);
    $direccion = cleanInput($_POST['direccion']);

    $error = false;

    if (empty($nombre) || empty($apellido) || empty($email) || empty($password)) {
        $error = "Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El formato del email no es válido.";
    } elseif ($password !== $confirmar_password) {
        $error = "Las contraseñas no coinciden.";
    } elseif (strlen($password) < 8) {
        $error = "La contraseña debe tener al menos 8 caracteres.";
    }

    if (!$error) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Ya existe una cuenta con este email.";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("
                    INSERT INTO clientes (nombre, apellido, email, password, telefono, direccion, estado)
                    VALUES (?, ?, ?, ?, ?, ?, 'Activo')
                ");
                
                if ($stmt->execute([
                    $nombre,
                    $apellido,
                    $email,
                    $password_hash,
                    $telefono,
                    $direccion
                ])) {
                    $_SESSION['mensaje'] = "Cuenta creada exitosamente. Por favor inicia sesión.";
                    header('Location: login.php');
                    exit;
                }
            }
        } catch(PDOException $e) {
            $error = "Error al crear la cuenta.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <link rel="icon" type="image/png" href="img/favicon.png">
    <title>Registro - BiblioSis</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .error-message {
            display: none;
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .input-error {
            border-color: #ef4444 !important;
        }
        .input-success {
            border-color: #10b981 !important;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-800">Crear Cuenta</h2>
                <p class="mt-2 text-gray-600">Regístrate para acceder a la biblioteca</p>
            </div>

            <?php if (isset($error)): ?>
            <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" method="POST" id="registroForm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700">
                            Nombre
                        </label>
                        <input type="text" 
                               id="nombre" 
                               name="nombre" 
                               required
                               value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>"
                               class="mt-1 block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-purple-500 focus:outline-none focus:ring-purple-500">
                    </div>

                    <div>
                        <label for="apellido" class="block text-sm font-medium text-gray-700">
                            Apellido
                        </label>
                        <input type="text" 
                               id="apellido" 
                               name="apellido" 
                               required
                               value="<?php echo isset($_POST['apellido']) ? htmlspecialchars($_POST['apellido']) : ''; ?>"
                               class="mt-1 block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-purple-500 focus:outline-none focus:ring-purple-500">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Email
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           class="mt-1 block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-purple-500 focus:outline-none focus:ring-purple-500">
                    <p class="error-message" id="emailError"></p>
                </div>

                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700">
                        Teléfono
                    </label>
                    <input type="tel" 
                           id="telefono" 
                           name="telefono" 
                           value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>"
                           class="mt-1 block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-purple-500 focus:outline-none focus:ring-purple-500">
                </div>

                <div>
                    <label for="direccion" class="block text-sm font-medium text-gray-700">
                        Dirección
                    </label>
                    <textarea id="direccion" 
                              name="direccion" 
                              rows="2"
                              class="mt-1 block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-purple-500 focus:outline-none focus:ring-purple-500"><?php echo isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion']) : ''; ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Contraseña
                        </label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required
                               minlength="8"
                               class="mt-1 block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-purple-500 focus:outline-none focus:ring-purple-500">
                        <p class="text-sm text-gray-500 mt-1">
                            La contraseña debe tener al menos 8 caracteres.
                        </p>
                        <p class="error-message" id="passwordError"></p>
                    </div>

                    <div>
                        <label for="confirmar_password" class="block text-sm font-medium text-gray-700">
                            Confirmar Contraseña
                        </label>
                        <input type="password" 
                               id="confirmar_password" 
                               name="confirmar_password" 
                               required
                               minlength="8"
                               class="mt-1 block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-purple-500 focus:outline-none focus:ring-purple-500">
                        <p class="error-message" id="confirmarPasswordError"></p>
                    </div>
                </div>

                <div class="flex flex-col space-y-4">
                    <button type="submit" 
                            id="submitButton"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Crear Cuenta
                    </button>
                    
                    <a href="login.php" 
                       class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        ← Volver al Login
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elementos del formulario
            const form = document.getElementById('registroForm');
            const passwordInput = document.getElementById('password');
            const confirmarPasswordInput = document.getElementById('confirmar_password');
            const emailInput = document.getElementById('email');
            const submitButton = document.getElementById('submitButton');
            
            // Elementos de error
            const passwordError = document.getElementById('passwordError');
            const confirmarPasswordError = document.getElementById('confirmarPasswordError');
            const emailError = document.getElementById('emailError');
            
            // Validación de la contraseña
            passwordInput.addEventListener('input', function() {
                validatePassword();
            });
            
            // Validación de confirmación de contraseña
            confirmarPasswordInput.addEventListener('input', function() {
                validatePasswordMatch();
            });
            
            // Validación de email
            emailInput.addEventListener('input', function() {
                validateEmail();
            });
            
            // Función para validar la contraseña
            function validatePassword() {
                const password = passwordInput.value;
                
                // Remover clases previas
                passwordInput.classList.remove('input-error', 'input-success');
                passwordError.style.display = 'none';
                
                if (password.length === 0) {
                    return;
                }
                
                if (password.length < 8) {
                    passwordInput.classList.add('input-error');
                    passwordError.textContent = 'La contraseña debe tener al menos 8 caracteres';
                    passwordError.style.display = 'block';
                    return false;
                } else {
                    passwordInput.classList.add('input-success');
                    return true;
                }
            }
            
            // Función para validar que las contraseñas coinciden
            function validatePasswordMatch() {
                const password = passwordInput.value;
                const confirmarPassword = confirmarPasswordInput.value;
                
                // Remover clases previas
                confirmarPasswordInput.classList.remove('input-error', 'input-success');
                confirmarPasswordError.style.display = 'none';
                
                if (confirmarPassword.length === 0) {
                    return;
                }
                
                if (password !== confirmarPassword) {
                    confirmarPasswordInput.classList.add('input-error');
                    confirmarPasswordError.textContent = 'Las contraseñas no coinciden';
                    confirmarPasswordError.style.display = 'block';
                    return false;
                } else {
                    confirmarPasswordInput.classList.add('input-success');
                    return true;
                }
            }
            
            // Función para validar el formato del email
            function validateEmail() {
                const email = emailInput.value;
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                // Remover clases previas
                emailInput.classList.remove('input-error', 'input-success');
                emailError.style.display = 'none';
                
                if (email.length === 0) {
                    return;
                }
                
                if (!emailRegex.test(email)) {
                    emailInput.classList.add('input-error');
                    emailError.textContent = 'Por favor, introduce un email válido';
                    emailError.style.display = 'block';
                    return false;
                } else {
                    emailInput.classList.add('input-success');
                    return true;
                }
            }
            
            // Validación del formulario completo antes de enviar
            form.addEventListener('submit', function(e) {
                const isPasswordValid = validatePassword();
                const isPasswordMatch = validatePasswordMatch();
                const isEmailValid = validateEmail();
                
                if (!isPasswordValid || !isPasswordMatch || !isEmailValid) {
                    e.preventDefault(); // Prevenir el envío del formulario
                }
            });
        });
    </script>
</body>
</html>