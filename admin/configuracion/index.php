<?php
//admin/configuracion/index.php
session_start();
require_once '../../config/config.php';

// Verificar permisos (solo administradores)
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../login.php');
    exit;
}

// Procesar formulario de configuración general
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_general'])) {
    try {
        $stmt = $pdo->prepare("
            UPDATE configuracion 
            SET dias_prestamo = ?,
                max_prestamos_usuario = ?,
                multa_dia_retraso = ?,
                nombre_biblioteca = ?,
                email_contacto = ?,
                telefono_contacto = ?
            WHERE id = 1
        ");
        
        if ($stmt->execute([
            $_POST['dias_prestamo'],
            $_POST['max_prestamos_usuario'],
            $_POST['multa_dia_retraso'],
            $_POST['nombre_biblioteca'],
            $_POST['email_contacto'],
            $_POST['telefono_contacto']
        ])) {
            $mensaje = "Configuración general actualizada exitosamente.";
        }
    } catch(PDOException $e) {
        error_log($e->getMessage());
        $error = "Error al actualizar la configuración general.";
    }
}

// Procesar actualización de logo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_logo'])) {
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['logo']['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($filetype, $allowed)) {
            $newname = 'logo.' . $filetype;
            $uploadDir = '../../img/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $newname)) {
                $mensaje = "Logo actualizado exitosamente.";
            } else {
                $error = "Error al subir el logo.";
            }
        } else {
            $error = "Tipo de archivo no permitido. Solo se permiten imágenes JPG, JPEG, PNG y GIF.";
        }
    }
}

// Obtener configuración actual
try {
    $stmt = $pdo->query("SELECT * FROM configuracion WHERE id = 1");
    $config = $stmt->fetch();
} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Error al cargar la configuración.";
}

$pageTitle = "Configuración del Sistema - BiblioTech";
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
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    Dashboard
                </a>
                <a href="../Autores/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-feather mr-3"></i>
                    Autores
                </a>
                <a href="../libros/index.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-book mr-3"></i>
                    Libros
                </a>
                <a href="../prestamos/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-handshake mr-3"></i>
                    Préstamos
                </a>
                <a href="../usuarios/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-users mr-3"></i>
                    Usuarios
                </a>
                <?php if (isAdmin()): ?>
                <a href="../reportes/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-chart-bar mr-3"></i>
                    Reportes
                </a>
                <a href="../bibliotecarios/index.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-users-cog mr-3"></i>Bibliotecarios
                </a>
                <a href="../configuracion/" class="flex items-center px-6 py-3 bg-gray-700 text-white">
                    <i class="fas fa-cog mr-3"></i>
                    Configuración
                </a>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Contenido principal -->
        <div class="flex-1 p-8">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-2xl font-bold mb-8">Configuración del Sistema</h1>

                <?php if (isset($mensaje)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    <?php echo $mensaje; ?>
                </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 gap-6">
                    <!-- Configuración General -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold mb-4">Configuración General</h2>
                        <form method="POST" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        Nombre de la Biblioteca
                                    </label>
                                    <input type="text" name="nombre_biblioteca" 
                                           value="<?php echo htmlspecialchars($config['nombre_biblioteca'] ?? ''); ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        Email de Contacto
                                    </label>
                                    <input type="email" name="email_contacto" 
                                           value="<?php echo htmlspecialchars($config['email_contacto'] ?? ''); ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        Teléfono de Contacto
                                    </label>
                                    <input type="text" name="telefono_contacto" 
                                           value="<?php echo htmlspecialchars($config['telefono_contacto'] ?? ''); ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        Días de Préstamo por Defecto
                                    </label>
                                    <input type="number" name="dias_prestamo" 
                                           value="<?php echo htmlspecialchars($config['dias_prestamo'] ?? '7'); ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        Máximo de Préstamos por Usuario
                                    </label>
                                    <input type="number" name="max_prestamos_usuario" 
                                           value="<?php echo htmlspecialchars($config['max_prestamos_usuario'] ?? '3'); ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        Multa por Día de Retraso ($)
                                    </label>
                                    <input type="number" step="0.01" name="multa_dia_retraso" 
                                           value="<?php echo htmlspecialchars($config['multa_dia_retraso'] ?? '1.00'); ?>"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" name="update_general" 
                                        class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                                    Guardar Configuración
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Logo de la Biblioteca -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold mb-4">Logo de la Biblioteca</h2>
                        <form method="POST" enctype="multipart/form-data" class="space-y-4">
                            <div class="flex items-center space-x-6">
                                <div class="flex-shrink-0">
                                    <?php
                                    $logo_path = '../../img/logo.png';
                                    if (file_exists($logo_path)): ?>
                                        <img src="<?php echo $logo_path; ?>" alt="Logo actual" class="h-32 w-auto">
                                    <?php else: ?>
                                        <div class="h-32 w-32 bg-gray-200 flex items-center justify-center rounded">
                                            <i class="fas fa-image text-gray-400 text-4xl"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700">
                                        Nuevo Logo
                                    </label>
                                    <input type="file" name="logo" accept="image/*"
                                           class="mt-1 block w-full text-sm text-gray-500
                                                  file:mr-4 file:py-2 file:px-4
                                                  file:rounded-md file:border-0
                                                  file:text-sm file:font-semibold
                                                  file:bg-purple-50 file:text-purple-600
                                                  hover:file:bg-purple-100">
                                    <p class="mt-2 text-sm text-gray-500">
                                        Formatos permitidos: JPG, JPEG, PNG, GIF. Tamaño máximo recomendado: 1MB
                                    </p>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" name="update_logo" 
                                        class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                                    Actualizar Logo
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Respaldo de Base de Datos -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold mb-4">Respaldo de Base de Datos</h2>
                        <div class="space-y-4">
                            <p class="text-gray-600">
                                Realiza un respaldo completo de la base de datos del sistema.
                            </p>
                            <div class="flex justify-end">
                                <a href="backup.php" 
                                   class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                    <i class="fas fa-database mr-2"></i>Generar Respaldo
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>