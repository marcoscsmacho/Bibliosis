<?php
//admin/autores/agregar.php
session_start();
require_once '../../config/config.php';

// Verificar permisos
if (!isLoggedIn() || (!isAdmin() && !isBibliotecario())) {
    header('Location: ../../login.php');
    exit;
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = cleanInput($_POST['nombre']);
    $apellido = cleanInput($_POST['apellido']);
    $nacionalidad = cleanInput($_POST['nacionalidad']);
    $fecha_nacimiento = cleanInput($_POST['fecha_nacimiento']);
    $biografia = cleanInput($_POST['biografia']);

    if (!isset($error)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO autores (nombre, apellido, nacionalidad, fecha_nacimiento, biografia)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([
                $nombre,
                $apellido,
                $nacionalidad,
                $fecha_nacimiento ?: null,
                $biografia
            ])) {
                $_SESSION['mensaje'] = "Autor agregado exitosamente.";
                header('Location: index.php');
                exit;
            }
        } catch(PDOException $e) {
            error_log($e->getMessage());
            $error = "Error al agregar el autor.";
        }
    }
}

$pageTitle = "Agregar Autor - BiblioSis ";
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
                    <i class="fas fa-feather mr-3"></i>
                    Autores
                </a>
                <a href="../libros/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-book mr-3"></i>Libros
                </a>
                <a href="../prestamos/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-handshake mr-3"></i>Préstamos
                </a>
                <a href="../usuarios/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-users mr-3"></i>Usuarios
                </a>
                <?php if (isAdmin()): ?>
                <a href="../reportes/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-chart-bar mr-3"></i>Reportes
                </a>
                <a href="../bibliotecarios/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-users-cog mr-3"></i>Bibliotecarios
                </a>
                <a href="../configuracion/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-cog mr-3"></i>Configuración
                </a>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Contenido principal -->
        <div class="flex-1 p-8">
            <div class="max-w-3xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-bold">Agregar Nuevo Autor</h1>
                    <a href="index.php"  class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        <i class="fas fa-arrow-left mr-2"></i>
                    </a>
                </div>

                <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Nombre -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="nombre">
                                Nombre
                            </label>
                            <input type="text" id="nombre" name="nombre" required
                                   value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Apellido -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="apellido">
                                Apellido
                            </label>
                            <input type="text" id="apellido" name="apellido" required
                                   value="<?php echo isset($_POST['apellido']) ? htmlspecialchars($_POST['apellido']) : ''; ?>"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Nacionalidad -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="nacionalidad">
                                Nacionalidad
                            </label>
                            <input type="text" id="nacionalidad" name="nacionalidad" required
                                   value="<?php echo isset($_POST['nacionalidad']) ? htmlspecialchars($_POST['nacionalidad']) : ''; ?>"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Fecha de Nacimiento -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="fecha_nacimiento">
                                Fecha de Nacimiento
                            </label>
                            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                                   max="<?php echo date('Y-m-d'); ?>"
                                   value="<?php echo isset($_POST['fecha_nacimiento']) ? htmlspecialchars($_POST['fecha_nacimiento']) : ''; ?>"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Biografía -->
                        <div class="mb-4 col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="biografia">
                                Biografía
                            </label>
                            <textarea id="biografia" name="biografia" rows="4"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo isset($_POST['biografia']) ? htmlspecialchars($_POST['biografia']) : ''; ?></textarea>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end mt-6">
                        <a href="index.php" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mr-2">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Guardar Autor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>