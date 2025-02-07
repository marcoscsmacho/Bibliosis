<?php
session_start();
require_once '../../config/config.php';

// Verificar permisos (solo administradores)
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../login.php');
    exit;
}

// Obtener parámetros de filtrado
$orden = isset($_GET['orden']) ? cleanInput($_GET['orden']) : 'dias_atraso';
$atraso_minimo = isset($_GET['atraso_minimo']) ? (int)$_GET['atraso_minimo'] : 1;

try {
    // Obtener estadísticas generales
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_atrasados,
            SUM(DATEDIFF(CURRENT_DATE, fecha_devolucion_esperada)) as dias_atraso_total,
            MAX(DATEDIFF(CURRENT_DATE, fecha_devolucion_esperada)) as max_dias_atraso
        FROM prestamos
        WHERE estado = 'Prestado'
        AND fecha_devolucion_esperada < CURRENT_DATE
    ");
    $estadisticas = $stmt->fetch();

    // Construir la consulta para préstamos atrasados
    $sql = "
        SELECT p.*,
               l.titulo as libro_titulo,
               l.imagen_portada,
               CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre,
               c.email as cliente_email,
               c.telefono as cliente_telefono,
               CONCAT(u.nombre, ' ', u.apellido) as bibliotecario_nombre,
               DATEDIFF(CURRENT_DATE, p.fecha_devolucion_esperada) as dias_atraso
        FROM prestamos p
        JOIN libros l ON p.id_libro = l.id_libro
        JOIN clientes c ON p.id_cliente = c.id_cliente
        LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
        WHERE p.estado = 'Prestado'
        AND p.fecha_devolucion_esperada < CURRENT_DATE
        AND DATEDIFF(CURRENT_DATE, p.fecha_devolucion_esperada) >= ?
    ";

    // Aplicar ordenamiento
    switch ($orden) {
        case 'dias_atraso':
            $sql .= " ORDER BY dias_atraso DESC";
            break;
        case 'fecha_prestamo':
            $sql .= " ORDER BY p.fecha_prestamo ASC";
            break;
        case 'usuario':
            $sql .= " ORDER BY cliente_nombre ASC";
            break;
        default:
            $sql .= " ORDER BY dias_atraso DESC";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$atraso_minimo]);
    $prestamos = $stmt->fetchAll();

} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Error al generar el reporte de préstamos atrasados.";
}

$pageTitle = "Préstamos Atrasados - BiblioTech";
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
                        <h1 class="text-2xl font-bold text-gray-900">Préstamos Atrasados</h1>
                        <p class="text-gray-600">Reporte de préstamos con demora en la devolución</p>
                    </div>
                    <div class="space-x-2">
                        <a href="index.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Volver
                        </a>
                        <a href="#" onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700">
                            <i class="fas fa-print mr-2"></i>
                            Imprimir
                        </a>
                    </div>
                </div>

                <!-- Tarjetas de resumen -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-500">Total Préstamos Atrasados</p>
                                <h3 class="text-2xl font-bold text-gray-900">
                                    <?php echo number_format($estadisticas['total_atrasados']); ?>
                                </h3>
                            </div>
                            <div class="p-3 bg-red-100 rounded-full">
                                <i class="fas fa-clock text-red-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-500">Promedio Días de Atraso</p>
                                <h3 class="text-2xl font-bold text-gray-900">
                                    <?php 
                                    echo $estadisticas['total_atrasados'] > 0 
                                        ? number_format($estadisticas['dias_atraso_total'] / $estadisticas['total_atrasados'], 1) 
                                        : '0';
                                    ?> días
                                </h3>
                            </div>
                            <div class="p-3 bg-yellow-100 rounded-full">
                                <i class="fas fa-calendar-times text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-500">Mayor Atraso</p>
                                <h3 class="text-2xl font-bold text-gray-900">
                                    <?php echo $estadisticas['max_dias_atraso'] ?? '0'; ?> días
                                </h3>
                            </div>
                            <div class="p-3 bg-orange-100 rounded-full">
                                <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                    <form method="GET" class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Atraso Mínimo (días)
                            </label>
                            <input type="number" 
                                   name="atraso_minimo" 
                                   value="<?php echo $atraso_minimo; ?>"
                                   min="1"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                        </div>

                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Ordenar por
                            </label>
                            <select name="orden" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="dias_atraso" <?php echo $orden == 'dias_atraso' ? 'selected' : ''; ?>>Mayor atraso</option>
                                <option value="fecha_prestamo" <?php echo $orden == 'fecha_prestamo' ? 'selected' : ''; ?>>Fecha de préstamo</option>
                                <option value="usuario" <?php echo $orden == 'usuario' ? 'selected' : ''; ?>>Usuario</option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button type="submit"
                                    class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700">
                                <i class="fas fa-filter mr-2"></i>
                                Aplicar Filtros
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tabla de préstamos -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Libro
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Usuario
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha Préstamo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha Devolución
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Días de Atraso
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($prestamos)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        No hay préstamos atrasados que coincidan con los criterios de búsqueda.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($prestamos as $prestamo): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <?php if ($prestamo['imagen_portada']): ?>
                                                <img class="h-10 w-8 object-cover" 
                                                     src="../../<?php echo htmlspecialchars($prestamo['imagen_portada']); ?>" 
                                                     alt="<?php echo htmlspecialchars($prestamo['libro_titulo']); ?>">
                                            <?php else: ?>
                                                <div class="h-10 w-8 bg-gray-200 flex items-center justify-center">
                                                    <i class="fas fa-book text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($prestamo['libro_titulo']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php echo htmlspecialchars($prestamo['cliente_nombre']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($prestamo['cliente_email']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('d/m/Y', strtotime($prestamo['fecha_prestamo'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('d/m/Y', strtotime($prestamo['fecha_devolucion_esperada'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            <?php echo $prestamo['dias_atraso']; ?> días
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-3">
                                            <a href="../prestamos/ver.php?id=<?php echo $prestamo['id_prestamo']; ?>" 
                                               class="text-purple-600 hover:text-purple-900"
                                               title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="mailto:<?php echo htmlspecialchars($prestamo['cliente_email']); ?>"
                                               class="text-blue-600 hover:text-blue-900"
                                               title="Enviar recordatorio">
                                                <i class="fas fa-envelope"></i>
                                            </a>
                                            <a href="tel:<?php echo htmlspecialchars($prestamo['cliente_telefono']); ?>"
                                               class="text-green-600 hover:text-green-900"
                                               title="Llamar usuario">
                                                <i class="fas fa-phone"></i>
                                            </a>
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
</body>
</html>