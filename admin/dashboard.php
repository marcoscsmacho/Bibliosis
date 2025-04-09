<?php
//admin/dashboard.php
session_start();
require_once '../config/config.php';

// Verificar si el usuario está logueado y tiene permisos
if (!isLoggedIn() || (!isAdmin() && !isBibliotecario())) {
    header('Location: ../login.php');
    exit;
}

// Obtener estadísticas
try {
    // Total de libros
    $stmtLibros = $pdo->query("SELECT COUNT(*) as total FROM libros");
    $totalLibros = $stmtLibros->fetch()['total'];
    
    // Libros prestados
    $stmtPrestados = $pdo->query("SELECT COUNT(*) as total FROM prestamos WHERE estado = 'Prestado'");
    $librosPrestados = $stmtPrestados->fetch()['total'];
    
    // Total de usuarios
    $stmtUsuarios = $pdo->query("SELECT COUNT(*) as total FROM clientes");
    $totalUsuarios = $stmtUsuarios->fetch()['total'];
    
    // Préstamos recientes
    $stmtPrestamos = $pdo->query("
        SELECT p.*, l.titulo, c.nombre, c.apellido 
        FROM prestamos p 
        JOIN libros l ON p.id_libro = l.id_libro 
        JOIN clientes c ON p.id_cliente = c.id_cliente 
        ORDER BY p.fecha_prestamo DESC LIMIT 5
    ");
    $prestamosRecientes = $stmtPrestamos->fetchAll();
} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Error al cargar las estadísticas";
}

$pageTitle = 'Bibliocont - BiblioSis';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="icon" type="image/svg+xml" href="<?php echo $basePath; ?>/img/favicon.svg">
    <link rel="icon" type="image/png" href="<?php echo $basePath; ?>img/favicon.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 min-h-screen">
            <div class="flex items-center justify-center h-16 bg-gray-900">
            <a href="../index.php" class="flex items-center text-white hover:text-purple-400 transition-colors duration-200">
                    <span class="text-xl font-bold">BiblioSis</span>
                </a>
            </div>
            <nav class="mt-4">
                <a href="dashboard.php" class="flex items-center px-6 py-3 bg-gray-900 text-white">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    inicio
                </a>
                <a href="Autores/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-feather mr-3"></i>
                    Autores
                </a>
                <a href="libros/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-book mr-3"></i>
                    Libros
                </a>
                <a href="prestamos/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-handshake mr-3"></i>
                    Préstamos
                </a>
                <a href="usuarios/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-users mr-3"></i>
                    Usuarios
                </a>
                <?php if (isAdmin()): ?>
                <a href="reportes/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-chart-bar mr-3"></i>
                    Reportes
                </a>
                <a href="bibliotecarios/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                <i class="fas fa-users-cog mr-3"></i>Bibliotecarios
                </a>
                <a href="configuracion/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-cog mr-3"></i>
                    Configuración
                </a>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Contenido principal -->
        <div class="flex-1">
            <!-- Header -->
            <header class="bg-white shadow">
                <div class="flex justify-between items-center px-6 py-4">
                    <h2 class="text-2xl font-semibold text-gray-800">BiblioSis</h2>
                    <div class="flex items-center">
                        <span class="text-gray-600 mr-4">
                            Bienvenido <?php echo htmlspecialchars($_SESSION['user_nombre']); ?>
                        </span>
                        <a href="../logout.php" class="text-red-500 hover:text-red-700">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Contenido -->
            <main class="p-6">
                <!-- Tarjetas de estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-500 bg-opacity-10">
                                <i class="fas fa-book text-blue-500 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-gray-500 text-sm">Total Libros</h4>
                                <p class="text-2xl font-semibold text-gray-800"><?php echo $totalLibros; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-500 bg-opacity-10">
                                <i class="fas fa-handshake text-green-500 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-gray-500 text-sm">Libros Prestados</h4>
                                <p class="text-2xl font-semibold text-gray-800"><?php echo $librosPrestados; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-500 bg-opacity-10">
                                <i class="fas fa-users text-purple-500 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-gray-500 text-sm">Total Usuarios</h4>
                                <p class="text-2xl font-semibold text-gray-800"><?php echo $totalUsuarios; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Préstamos Recientes -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800">Préstamos Recientes</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Libro</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($prestamosRecientes as $prestamo): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($prestamo['titulo']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($prestamo['nombre'] . ' ' . $prestamo['apellido']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo date('d/m/Y', strtotime($prestamo['fecha_prestamo'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full <?php 
                                            echo $prestamo['estado'] === 'Prestado' ? 'bg-yellow-100 text-yellow-800' : 
                                                ($prestamo['estado'] === 'Devuelto' ? 'bg-green-100 text-green-800' : 
                                                'bg-red-100 text-red-800'); ?>">
                                            <?php echo htmlspecialchars($prestamo['estado']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="prestamos/ver.php?id=<?php echo $prestamo['id_prestamo']; ?>" 
                                           class="text-blue-500 hover:text-blue-700 mr-2">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>