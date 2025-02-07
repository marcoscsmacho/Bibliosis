<?php
//admin/reportes/libros_populares.php
session_start();
require_once '../../config/config.php';

// Verificar permisos (solo administradores)
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../login.php');
    exit;
}

// Obtener parámetros de filtrado
$periodo = isset($_GET['periodo']) ? cleanInput($_GET['periodo']) : 'todos';
$genero = isset($_GET['genero']) ? cleanInput($_GET['genero']) : 'todos';
$limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 20;

try {
    // Obtener géneros para el filtro
    $stmt = $pdo->query("SELECT * FROM generos ORDER BY nombre");
    $generos = $stmt->fetchAll();

    // Construir la consulta base
    $sql = "
        SELECT l.*, a.nombre as autor_nombre, a.apellido as autor_apellido,
               g.nombre as genero_nombre, e.nombre as editorial_nombre,
               COUNT(p.id_prestamo) as total_prestamos,
               (SELECT COUNT(*) FROM prestamos p2 
                WHERE p2.id_libro = l.id_libro AND p2.estado = 'Prestado') as prestamos_activos,
               (l.cantidad_total - l.cantidad_disponible) as copias_prestadas
        FROM libros l
        LEFT JOIN prestamos p ON l.id_libro = p.id_libro
        JOIN autores a ON l.id_autor = a.id_autor
        JOIN generos g ON l.id_genero = g.id_genero
        JOIN editoriales e ON l.id_editorial = e.id_editorial
        WHERE 1=1
    ";
    
    $params = [];

    // Aplicar filtros
    if ($genero !== 'todos') {
        $sql .= " AND g.id_genero = ?";
        $params[] = $genero;
    }

    if ($periodo !== 'todos') {
        switch ($periodo) {
            case 'mes':
                $sql .= " AND p.fecha_prestamo >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)";
                break;
            case 'trimestre':
                $sql .= " AND p.fecha_prestamo >= DATE_SUB(CURRENT_DATE, INTERVAL 3 MONTH)";
                break;
            case 'semestre':
                $sql .= " AND p.fecha_prestamo >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)";
                break;
            case 'anio':
                $sql .= " AND p.fecha_prestamo >= DATE_SUB(CURRENT_DATE, INTERVAL 1 YEAR)";
                break;
        }
    }

    $sql .= " GROUP BY l.id_libro ORDER BY total_prestamos DESC LIMIT ?";
    $params[] = $limite;

    // Ejecutar la consulta
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $libros = $stmt->fetchAll();

    // Obtener estadísticas generales
    $stmt = $pdo->query("
        SELECT 
            COUNT(DISTINCT id_libro) as libros_prestados,
            COUNT(*) as total_prestamos,
            AVG(DATEDIFF(fecha_devolucion_real, fecha_prestamo)) as promedio_dias_prestamo
        FROM prestamos 
        WHERE estado = 'Devuelto'
    ");
    $stats = $stmt->fetch();

} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Error al cargar los datos del reporte.";
}

$pageTitle = "Libros Más Populares - BiblioTech";
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
        <a href="../reportes/" class="flex items-center px-6 py-3 bg-gray-700 text-white">
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
                    <h1 class="text-2xl font-bold">Libros Más Populares</h1>
                    <div class="space-x-2">
                    <a href="index.php"  class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    <i class="fas fa-arrow-left mr-2"></i>
                    </a>
                        <a href="generar_excel.php?reporte=libros_populares" 
                           class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            <i class="fas fa-file-excel mr-2"></i>Exportar a Excel
                        </a>
                        <a href="generar_pdf.php?reporte=libros_populares" 
                           class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                            <i class="fas fa-file-pdf mr-2"></i>Exportar a PDF
                        </a>
                    </div>
                </div>

                <!-- Estadísticas generales -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Préstamos</h3>
                        <p class="text-3xl font-bold text-purple-600">
                            <?php echo number_format($stats['total_prestamos']); ?>
                        </p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Libros Prestados</h3>
                        <p class="text-3xl font-bold text-purple-600">
                            <?php echo number_format($stats['libros_prestados']); ?>
                        </p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Promedio Días Préstamo</h3>
                        <p class="text-3xl font-bold text-purple-600">
                            <?php echo number_format($stats['promedio_dias_prestamo'], 1); ?>
                        </p>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="bg-white rounded-lg shadow p-6 mb-8">
                    <form method="GET" class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Período</label>
                            <select name="periodo" 
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                <option value="todos" <?php echo $periodo === 'todos' ? 'selected' : ''; ?>>Todo el tiempo</option>
                                <option value="mes" <?php echo $periodo === 'mes' ? 'selected' : ''; ?>>Último mes</option>
                                <option value="trimestre" <?php echo $periodo === 'trimestre' ? 'selected' : ''; ?>>Último trimestre</option>
                                <option value="semestre" <?php echo $periodo === 'semestre' ? 'selected' : ''; ?>>Último semestre</option>
                                <option value="anio" <?php echo $periodo === 'anio' ? 'selected' : ''; ?>>Último año</option>
                            </select>
                        </div>

                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Género</label>
                            <select name="genero" 
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                <option value="todos">Todos los géneros</option>
                                <?php foreach ($generos as $gen): ?>
                                    <option value="<?php echo $gen['id_genero']; ?>" 
                                            <?php echo $genero == $gen['id_genero'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($gen['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mostrar</label>
                            <select name="limite" 
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                <option value="10" <?php echo $limite === 10 ? 'selected' : ''; ?>>10 libros</option>
                                <option value="20" <?php echo $limite === 20 ? 'selected' : ''; ?>>20 libros</option>
                                <option value="50" <?php echo $limite === 50 ? 'selected' : ''; ?>>50 libros</option>
                                <option value="100" <?php echo $limite === 100 ? 'selected' : ''; ?>>100 libros</option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button type="submit" 
                                    class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700">
                                <i class="fas fa-filter mr-2"></i>Aplicar filtros
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tabla de libros -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Libro
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Autor
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Género
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total Préstamos
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado Actual
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($libros as $libro): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <?php if ($libro['imagen_portada']): ?>
                                            <img src="../../<?php echo htmlspecialchars($libro['imagen_portada']); ?>" 
                                                 alt="Portada" 
                                                 class="h-12 w-9 object-cover mr-3">
                                        <?php endif; ?>
                                        <div>
                                            <div class="font-medium text-gray-900">
                                                <?php echo htmlspecialchars($libro['titulo']); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($libro['editorial_nombre']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo htmlspecialchars($libro['autor_nombre'] . ' ' . $libro['autor_apellido']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo htmlspecialchars($libro['genero_nombre']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                        <?php echo $libro['total_prestamos']; ?> préstamos
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo $libro['copias_prestadas']; ?> de <?php echo $libro['cantidad_total']; ?> prestados
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo $libro['prestamos_activos']; ?> préstamos activos
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