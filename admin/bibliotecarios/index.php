<?php
//admin/bibliotecarios/index.php
session_start();
require_once '../../config/config.php';

// Verificar que solo el administrador pueda acceder
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../login.php');
    exit;
}

// Procesar eliminación si se solicita
if (isset($_POST['delete_id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ? AND id_rol = 2"); // 2 = rol bibliotecario
        if ($stmt->execute([$_POST['delete_id']])) {
            $mensaje = "Bibliotecario eliminado exitosamente.";
        }
    } catch(PDOException $e) {
        error_log($e->getMessage());
        $error = "Error al eliminar el bibliotecario.";
    }
}

// Cambiar estado si se solicita
if (isset($_POST['toggle_status'])) {
    try {
        $stmt = $pdo->prepare("
            UPDATE usuarios 
            SET estado = CASE WHEN estado = 'Activo' THEN 'Inactivo' ELSE 'Activo' END 
            WHERE id_usuario = ? AND id_rol = 2
        ");
        if ($stmt->execute([$_POST['toggle_status']])) {
            $mensaje = "Estado actualizado exitosamente.";
        }
    } catch(PDOException $e) {
        error_log($e->getMessage());
        $error = "Error al actualizar el estado.";
    }
}

// Obtener lista de bibliotecarios
try {
    $stmt = $pdo->query("
        SELECT u.*, r.nombre_rol,
               (SELECT COUNT(*) FROM prestamos p WHERE p.id_usuario = u.id_usuario) as total_prestamos
        FROM usuarios u
        JOIN roles r ON u.id_rol = r.id_rol
        WHERE u.id_rol = 2
        ORDER BY u.fecha_registro DESC
    ");
    $bibliotecarios = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Error al cargar la lista de bibliotecarios.";
}

$pageTitle = "Gestión de Bibliotecarios - BiblioSis";
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
                <a href="../libros/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
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
                <a href="index.php" class="flex items-center px-6 py-3 bg-gray-700 text-white">
                    <i class="fas fa-users-cog mr-3"></i>Bibliotecarios
                </a>
                <a href="../configuracion/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-cog mr-3"></i>
                    Configuración
                </a>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Contenido principal -->
        <div class="flex-1 p-8">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-bold">Gestión de Bibliotecarios</h1>
                    <a href="agregar.php" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        <i class="fas fa-plus mr-2"></i>Agregar Bibliotecario
                    </a>
                </div>

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

                <!-- Tabla de bibliotecarios -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nombre Completo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Préstamos Realizados
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha Registro
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($bibliotecarios as $bibliotecario): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($bibliotecario['nombre'] . ' ' . $bibliotecario['apellido']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($bibliotecario['email']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $bibliotecario['estado'] === 'Activo' 
                                            ? 'bg-green-100 text-green-800' 
                                            : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo htmlspecialchars($bibliotecario['estado']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $bibliotecario['total_prestamos']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d/m/Y', strtotime($bibliotecario['fecha_registro'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex space-x-3">
                                        <a href="editar.php?id=<?php echo $bibliotecario['id_usuario']; ?>" 
                                           class="text-yellow-500 hover:text-yellow-700">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" class="inline" 
                                              onsubmit="return confirm('¿Cambiar el estado del bibliotecario?');">
                                            <input type="hidden" name="toggle_status" 
                                                   value="<?php echo $bibliotecario['id_usuario']; ?>">
                                            <button type="submit" 
                                                    class="<?php echo $bibliotecario['estado'] === 'Activo' 
                                                        ? 'text-red-500 hover:text-red-700' 
                                                        : 'text-green-500 hover:text-green-700'; ?>">
                                                <i class="fas <?php echo $bibliotecario['estado'] === 'Activo' 
                                                    ? 'fa-user-slash' 
                                                    : 'fa-user-check'; ?>"></i>
                                            </button>
                                        </form>
                                        <?php if ($bibliotecario['total_prestamos'] == 0): ?>
                                        <form method="POST" class="inline" 
                                              onsubmit="return confirm('¿Estás seguro de eliminar este bibliotecario?');">
                                            <input type="hidden" name="delete_id" 
                                                   value="<?php echo $bibliotecario['id_usuario']; ?>">
                                            <button type="submit" class="text-red-500 hover:text-red-700">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>