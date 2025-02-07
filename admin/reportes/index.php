<?php
//admin/reportes/index.php
session_start();
require_once '../../config/config.php';

// Verificar permisos (solo administradores)
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../login.php');
    exit;
}

// Obtener estadísticas generales
try {
    // Total de préstamos activos
    $stmt = $pdo->query("SELECT COUNT(*) FROM prestamos WHERE estado = 'Prestado'");
    $prestamos_activos = $stmt->fetchColumn();

    // Total de libros
    $stmt = $pdo->query("SELECT COUNT(*) FROM libros");
    $total_libros = $stmt->fetchColumn();

    // Total de usuarios activos
    $stmt = $pdo->query("SELECT COUNT(*) FROM clientes WHERE estado = 'Activo'");
    $usuarios_activos = $stmt->fetchColumn();

    // Préstamos atrasados
    $stmt = $pdo->query("
        SELECT COUNT(*) FROM prestamos 
        WHERE estado = 'Prestado' 
        AND fecha_devolucion_esperada < CURRENT_DATE
    ");
    $prestamos_atrasados = $stmt->fetchColumn();

    // Libros más prestados
    $stmt = $pdo->query("
        SELECT l.titulo, l.imagen_portada, COUNT(p.id_prestamo) as total_prestamos,
               a.nombre as autor_nombre, a.apellido as autor_apellido
        FROM libros l
        LEFT JOIN prestamos p ON l.id_libro = p.id_libro
        LEFT JOIN autores a ON l.id_autor = a.id_autor
        GROUP BY l.id_libro
        ORDER BY total_prestamos DESC
        LIMIT 5
    ");
    $libros_populares = $stmt->fetchAll();

    // Usuarios más activos
    $stmt = $pdo->query("
        SELECT c.nombre, c.apellido, c.imagen_cliente, COUNT(p.id_prestamo) as total_prestamos
        FROM clientes c
        LEFT JOIN prestamos p ON c.id_cliente = p.id_cliente
        GROUP BY c.id_cliente
        ORDER BY total_prestamos DESC
        LIMIT 5
    ");
    $usuarios_frecuentes = $stmt->fetchAll();

} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Error al generar las estadísticas.";
}

$pageTitle = "Reportes y Estadísticas - BiblioTech";
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
                    <h1 class="text-2xl font-bold">Reportes y Estadísticas</h1>
                    <div class="space-x-2">
                        <a href="generar_excel.php" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            <i class="fas fa-file-excel mr-2"></i>Exportar a Excel
                        </a>
                        <a href="generar_pdf.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                            <i class="fas fa-file-pdf mr-2"></i>Exportar a PDF
                        </a>
                    </div>
                </div>

                <!-- Tarjetas de estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <!-- Préstamos Activos -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm">Préstamos Activos</p>
                                <h3 class="text-3xl font-bold"><?php echo $prestamos_activos; ?></h3>
                            </div>
                            <div class="p-3 bg-blue-500 bg-opacity-10 rounded-full">
                                <i class="fas fa-book-reader text-blue-500 text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Total de Libros -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm">Total de Libros</p>
                                <h3 class="text-3xl font-bold"><?php echo $total_libros; ?></h3>
                            </div>
                            <div class="p-3 bg-purple-500 bg-opacity-10 rounded-full">
                                <i class="fas fa-book text-purple-500 text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Usuarios Activos -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm">Usuarios Activos</p>
                                <h3 class="text-3xl font-bold"><?php echo $usuarios_activos; ?></h3>
                            </div>
                            <div class="p-3 bg-green-500 bg-opacity-10 rounded-full">
                                <i class="fas fa-users text-green-500 text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Préstamos Atrasados -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm">Préstamos Atrasados</p>
                                <h3 class="text-3xl font-bold"><?php echo $prestamos_atrasados; ?></h3>
                            </div>
                            <div class="p-3 bg-red-500 bg-opacity-10 rounded-full">
                                <i class="fas fa-clock text-red-500 text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Libros más prestados -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold mb-4">Libros Más Prestados</h2>
                        <div class="space-y-4">
                            <?php foreach ($libros_populares as $libro): ?>
                            <div class="flex items-center">
                                <?php if ($libro['imagen_portada']): ?>
                                    <img src="../../<?php echo htmlspecialchars($libro['imagen_portada']); ?>" 
                                         alt="Portada" 
                                         class="w-12 h-16 object-cover rounded">
                                <?php else: ?>
                                    <div class="w-12 h-16 bg-gray-200 flex items-center justify-center rounded">
                                        <i class="fas fa-book text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="ml-4 flex-1">
                                    <h3 class="font-medium"><?php echo htmlspecialchars($libro['titulo']); ?></h3>
                                    <p class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($libro['autor_nombre'] . ' ' . $libro['autor_apellido']); ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo $libro['total_prestamos']; ?> préstamos
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-4">
                            <a href="libros_populares.php" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                                Ver reporte completo →
                            </a>
                        </div>
                    </div>

                    <!-- Usuarios más activos -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold mb-4">Usuarios Más Activos</h2>
                        <div class="space-y-4">
                            <?php foreach ($usuarios_frecuentes as $usuario): ?>
                            <div class="flex items-center">
                                <?php if ($usuario['imagen_cliente']): ?>
                                    <img src="../../<?php echo htmlspecialchars($usuario['imagen_cliente']); ?>" 
                                         alt="Usuario" 
                                         class="w-10 h-10 object-cover rounded-full">
                                <?php else: ?>
                                    <div class="w-10 h-10 bg-gray-200 flex items-center justify-center rounded-full">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="ml-4 flex-1">
                                    <h3 class="font-medium">
                                        <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?>
                                    </h3>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <?php echo $usuario['total_prestamos']; ?> préstamos
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-4">
                            <a href="usuarios_activos.php" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                                Ver reporte completo →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Enlaces a reportes específicos -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                    <a href="prestamos_atrasados.php" class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-red-500 bg-opacity-10 rounded-full">
                                <i class="fas fa-exclamation-circle text-red-500 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="font-semibold">Préstamos Atrasados</h3>
                                <p class="text-sm text-gray-500">Ver listado detallado de préstamos vencidos</p>
                            </div>
                        </div>
                    </a>

                    <a href="estadisticas_mensuales.php" class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-500 bg-opacity-10 rounded-full">
                                <i class="fas fa-chart-line text-blue-500 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="font-semibold">Estadísticas Mensuales</h3>
                                <p class="text-sm text-gray-500">Ver tendencias y análisis por mes</p>
                            </div>
                        </div>
                    </a>

                    <a href="inventario.php" class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-500 bg-opacity-10 rounded-full">
                                <i class="fas fa-boxes text-green-500 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="font-semibold">Estado del Inventario</h3>
                                <p class="text-sm text-gray-500">Ver disponibilidad y estado de libros</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>