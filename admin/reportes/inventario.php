<?php
// admin/reportes/inventario.php
session_start();
require_once '../../config/config.php';

// Verificar permisos (solo administradores)
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../login.php');
    exit;
}

// Obtener parámetros de filtrado
$genero = isset($_GET['genero']) ? cleanInput($_GET['genero']) : 'todos';
$estado = isset($_GET['estado']) ? cleanInput($_GET['estado']) : 'todos';
$disponibilidad = isset($_GET['disponibilidad']) ? cleanInput($_GET['disponibilidad']) : 'todos';

try {
    // Obtener géneros para el filtro
    $stmt = $pdo->query("SELECT * FROM generos ORDER BY nombre");
    $generos = $stmt->fetchAll();

    // Obtener estadísticas generales
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_libros,
            SUM(cantidad_total) as ejemplares_totales,
            SUM(cantidad_disponible) as ejemplares_disponibles,
            SUM(cantidad_total - cantidad_disponible) as ejemplares_prestados
        FROM libros
    ");
    $estadisticas = $stmt->fetch();

    // Construir la consulta para el inventario
    $sql = "
        SELECT l.*, 
               a.nombre as autor_nombre, a.apellido as autor_apellido,
               g.nombre as genero_nombre, e.nombre as editorial_nombre,
               (SELECT COUNT(*) FROM prestamos WHERE id_libro = l.id_libro AND estado = 'Prestado') as prestamos_activos,
               (SELECT COUNT(*) FROM prestamos WHERE id_libro = l.id_libro) as total_prestamos
        FROM libros l
        LEFT JOIN autores a ON l.id_autor = a.id_autor
        LEFT JOIN generos g ON l.id_genero = g.id_genero
        LEFT JOIN editoriales e ON l.id_editorial = e.id_editorial
        WHERE 1=1
    ";

    $params = [];

    // Aplicar filtros
    if ($genero !== 'todos') {
        $sql .= " AND l.id_genero = ?";
        $params[] = $genero;
    }

    if ($estado !== 'todos') {
        $sql .= " AND l.estado = ?";
        $params[] = $estado;
    }

    if ($disponibilidad === 'disponibles') {
        $sql .= " AND l.cantidad_disponible > 0";
    } elseif ($disponibilidad === 'agotados') {
        $sql .= " AND l.cantidad_disponible = 0";
    } elseif ($disponibilidad === 'prestados') {
        $sql .= " AND l.cantidad_disponible < l.cantidad_total";
    }

    $sql .= " ORDER BY l.titulo ASC";

    // Ejecutar consulta
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $libros = $stmt->fetchAll();

} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Error al generar el reporte de inventario.";
}

$pageTitle = "Estado del Inventario - BiblioSis";
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
                <!-- Encabezado -->
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Estado del Inventario</h1>
                        <p class="text-gray-600">Reporte detallado del inventario de libros</p>
                    </div>
                    <div class="space-x-2">
                        <a href="index.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Volver
                        </a>
                        <a href="generar_excel.php?reporte=inventario<?php 
                                echo $genero !== 'todos' ? '&genero='.$genero : ''; 
                                echo $estado !== 'todos' ? '&estado='.$estado : '';
                                echo $disponibilidad !== 'todos' ? '&disponibilidad='.$disponibilidad : '';
                            ?>" 
                           class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            <i class="fas fa-file-excel mr-2"></i>
                            Exportar a Excel
                        </a>
                        <a href="generar_pdf.php?reporte=inventario<?php 
                                echo $genero !== 'todos' ? '&genero='.$genero : ''; 
                                echo $estado !== 'todos' ? '&estado='.$estado : '';
                                echo $disponibilidad !== 'todos' ? '&disponibilidad='.$disponibilidad : '';
                            ?>" 
                           class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                            <i class="fas fa-file-pdf mr-2"></i>
                            Exportar a PDF
                        </a>
                    </div>
                </div>

                <!-- Tarjetas de estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-500">Total de Libros</p>
                                <h3 class="text-3xl font-bold text-gray-800"><?php echo number_format($estadisticas['total_libros']); ?></h3>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-full">
                                <i class="fas fa-book text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-500">Ejemplares Totales</p>
                                <h3 class="text-3xl font-bold text-gray-800"><?php echo number_format($estadisticas['ejemplares_totales']); ?></h3>
                            </div>
                            <div class="p-3 bg-purple-100 rounded-full">
                                <i class="fas fa-layer-group text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-500">Ejemplares Disponibles</p>
                                <h3 class="text-3xl font-bold text-gray-800"><?php echo number_format($estadisticas['ejemplares_disponibles']); ?></h3>
                            </div>
                            <div class="p-3 bg-green-100 rounded-full">
                                <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-500">Ejemplares Prestados</p>
                                <h3 class="text-3xl font-bold text-gray-800"><?php echo number_format($estadisticas['ejemplares_prestados']); ?></h3>
                            </div>
                            <div class="p-3 bg-yellow-100 rounded-full">
                                <i class="fas fa-hand-holding text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="bg-white rounded-lg shadow p-6 mb-8">
                    <form method="GET" class="flex flex-wrap gap-4 items-end">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Género</label>
                            <select name="genero" id="filtro-genero" class="w-full rounded border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="todos" <?php echo $genero === 'todos' ? 'selected' : ''; ?>>Todos los géneros</option>
                                <?php foreach ($generos as $gen): ?>
                                    <option value="<?php echo $gen['id_genero']; ?>" <?php echo $genero == $gen['id_genero'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($gen['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select name="estado" id="filtro-estado" class="w-full rounded border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="todos" <?php echo $estado === 'todos' ? 'selected' : ''; ?>>Todos los estados</option>
                                <option value="Disponible" <?php echo $estado === 'Disponible' ? 'selected' : ''; ?>>Disponible</option>
                                <option value="Prestado" <?php echo $estado === 'Prestado' ? 'selected' : ''; ?>>Prestado</option>
                                <option value="No disponible" <?php echo $estado === 'No disponible' ? 'selected' : ''; ?>>No disponible</option>
                            </select>
                        </div>

                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Disponibilidad</label>
                            <select name="disponibilidad" id="filtro-disponibilidad" class="w-full rounded border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="todos" <?php echo $disponibilidad === 'todos' ? 'selected' : ''; ?>>Todos</option>
                                <option value="disponibles" <?php echo $disponibilidad === 'disponibles' ? 'selected' : ''; ?>>Con ejemplares disponibles</option>
                                <option value="agotados" <?php echo $disponibilidad === 'agotados' ? 'selected' : ''; ?>>Sin ejemplares disponibles</option>
                                <option value="prestados" <?php echo $disponibilidad === 'prestados' ? 'selected' : ''; ?>>Con ejemplares prestados</option>
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Tabla de inventario -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Libro
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Autor
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Género
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ejemplares
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Préstamos
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($libros)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        No se encontraron libros que coincidan con los criterios seleccionados.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($libros as $libro): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <?php if ($libro['imagen_portada']): ?>
                                                <img src="../../<?php echo htmlspecialchars($libro['imagen_portada']); ?>" 
                                                     class="h-10 w-8 object-cover" alt="<?php echo htmlspecialchars($libro['titulo']); ?>">
                                            <?php else: ?>
                                                <div class="h-10 w-8 bg-gray-200 flex items-center justify-center">
                                                    <i class="fas fa-book text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($libro['titulo']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    ISBN: <?php echo htmlspecialchars($libro['isbn']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php echo htmlspecialchars($libro['autor_nombre'] . ' ' . $libro['autor_apellido']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php echo htmlspecialchars($libro['genero_nombre']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php echo $libro['cantidad_disponible']; ?> de <?php echo $libro['cantidad_total']; ?> disponibles
                                        </div>
                                        <?php if ($libro['cantidad_disponible'] == 0): ?>
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Agotado
                                            </span>
                                        <?php elseif ($libro['cantidad_disponible'] < ($libro['cantidad_total'] / 2)): ?>
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Limitado
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $libro['estado'] === 'Disponible' 
                                                ? 'bg-green-100 text-green-800' 
                                                : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo htmlspecialchars($libro['estado']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php echo $libro['prestamos_activos']; ?> activos
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo $libro['total_prestamos']; ?> totales
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Obtener referencias a los elementos de filtro
        const filtroGenero = document.getElementById('filtro-genero');
        const filtroEstado = document.getElementById('filtro-estado');
        const filtroDisponibilidad = document.getElementById('filtro-disponibilidad');
        
        // Función para aplicar los filtros automáticamente
        function aplicarFiltros() {
            // Enviar el formulario
            filtroGenero.form.submit();
        }
        
        // Agregar eventos para detectar cambios en los filtros
        filtroGenero.addEventListener('change', aplicarFiltros);
        filtroEstado.addEventListener('change', aplicarFiltros);
        filtroDisponibilidad.addEventListener('change', aplicarFiltros);
    });
    </script>
</body>
</html>