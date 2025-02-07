<?php
//admin/bibliotecarios/editar.php
session_start();
require_once '../../config/config.php';

// Verificar que solo el administrador pueda acceder
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../login.php');
    exit;
}

// Verificar que se proporcionó un ID
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_usuario = $_GET['id'];

// Obtener datos del bibliotecario
try {
    $stmt = $pdo->prepare("
        SELECT u.*, r.nombre_rol 
        FROM usuarios u 
        JOIN roles r ON u.id_rol = r.id_rol 
        WHERE u.id_usuario = ? AND u.id_rol = 2
    ");
    $stmt->execute([$id_usuario]);
    $bibliotecario = $stmt->fetch();

    if (!$bibliotecario) {
        header('Location: index.php');
        exit;
    }
} catch(PDOException $e) {
    error_log($e->getMessage());
    header('Location: index.php');
    exit;
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = cleanInput($_POST['nombre']);
    $apellido = cleanInput($_POST['apellido']);
    $email = cleanInput($_POST['email']);
    $estado = cleanInput($_POST['estado']);
    $password = $_POST['password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';

    $error = false;

    // Validaciones
    if (empty($nombre) || empty($apellido) || empty($email)) {
        $error = "Los campos nombre, apellido y email son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El formato del email no es válido.";
    }

    // Validar contraseña solo si se está cambiando
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $error = "La contraseña debe tener al menos 6 caracteres.";
        } elseif ($password !== $confirmar_password) {
            $error = "Las contraseñas no coinciden.";
        }
    }

    if (!$error) {
        try {
            // Verificar si el email ya existe (excluyendo el usuario actual)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ? AND id_usuario != ?");
            $stmt->execute([$email, $id_usuario]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Ya existe otro usuario con este email.";
            } else {
                // Preparar la consulta base
                $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, estado = ?";
                $params = [$nombre, $apellido, $email, $estado];

                // Agregar actualización de contraseña si se proporcionó una nueva
                if (!empty($password)) {
                    $sql .= ", password = ?";
                    $params[] = password_hash($password, PASSWORD_DEFAULT);
                }

                $sql .= " WHERE id_usuario = ? AND id_rol = 2";
                $params[] = $id_usuario;

                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute($params)) {
                    $_SESSION['mensaje'] = "Bibliotecario actualizado exitosamente.";
                    header('Location: index.php');
                    exit;
                }
            }
        } catch(PDOException $e) {
            error_log($e->getMessage());
            $error = "Error al actualizar el bibliotecario.";
        }
    }
}

$pageTitle = "Editar Bibliotecario - BiblioSis";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 min-h-screen">
            <div class="flex items-center justify-center h-16 bg-gray-900">
                <a href="../../index.php" class="text-white text-xl font-bold">BiblioSis</a>
            </div>
            <nav class="mt-4">
                <a href="../dashboard.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                </a>
                <a href="index.php" class="flex items-center px-6 py-3 bg-gray-700 text-white">
                    <i class="fas fa-users-cog mr-3"></i>Bibliotecarios
                </a>
            </nav>
        </div>

        <!-- Contenido principal -->
        <div class="flex-1 p-8">
            <div class="max-w-2xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-bold">Editar Bibliotecario</h1>
                    <a href="index.php" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-arrow-left mr-2"></i>Volver
                    </a>
                </div>

                <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="bg-white rounded-lg shadow-sm p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <!-- Nombre -->
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre
                            </label>
                            <input type="text" id="nombre" name="nombre" required
                                   value="<?php echo htmlspecialchars($bibliotecario['nombre']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500">
                        </div>

                        <!-- Apellido -->
                        <div>
                            <label for="apellido" class="block text-sm font-medium text-gray-700 mb-2">
                                Apellido
                            </label>
                            <input type="text" id="apellido" name="apellido" required
                                   value="<?php echo htmlspecialchars($bibliotecario['apellido']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500">
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo htmlspecialchars($bibliotecario['email']); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500">
                    </div>

                    <!-- Estado -->
                    <div class="mb-6">
                        <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                            Estado
                        </label>
                        <select id="estado" name="estado" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500">
                            <option value="Activo" <?php echo $bibliotecario['estado'] == 'Activo' ? 'selected' : ''; ?>>
                                Activo
                            </option>
                            <option value="Inactivo" <?php echo $bibliotecario['estado'] == 'Inactivo' ? 'selected' : ''; ?>>
                                Inactivo
                            </option>
                        </select>
                    </div>

                    <!-- Nueva Contraseña -->
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Nueva Contraseña
                        </label>
                        <input type="password" id="password" name="password"
                               minlength="6"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500">
                        <p class="mt-1 text-sm text-gray-500">
                            Dejar en blanco para mantener la contraseña actual.
                        </p>
                    </div>

                    <!-- Confirmar Nueva Contraseña -->
                    <div class="mb-6">
                        <label for="confirmar_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirmar Nueva Contraseña
                        </label>
                        <input type="password" id="confirmar_password" name="confirmar_password"
                               minlength="6"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500">
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end space-x-4">
                        <a href="index.php" 
                           class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>