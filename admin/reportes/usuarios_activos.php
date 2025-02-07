<?php
session_start();
require_once '../../config/config.php';

// Verificar permisos (solo administradores)
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../login.php');
    exit;
}

// Obtener parámetros de filtrado
$orden = isset($_GET['orden']) ? cleanInput($_GET['orden']) : 'prestamos';
$periodo = isset($_GET['periodo']) ? cleanInput($_GET['periodo']) : '30'; // días por defecto

try {
    // Consulta base para obtener estadísticas generales
    $stmt = $pdo->query("
        SELECT COUNT(*) as total_usuarios,
               SUM(CASE WHEN estado = 'Activo' THEN 1 ELSE 0 END) as usuarios_activos
        FROM clientes
    ");
    $estadisticas = $stmt->fetch();

    // Construir la consulta para usuarios activos
    $sql = "
        SELECT c.*,
               COUNT(p.id_prestamo) as total_prestamos,
               MAX(p.fecha_prestamo) as ultimo_prestamo,
               SUM(CASE WHEN p.estado = 'Prestado' THEN 1 ELSE 0 END) as prestamos_activos
        FROM clientes c
        LEFT JOIN prestamos p ON c.id_cliente = p.id_cliente
        WHERE c.estado = 'Activo'
    ";

    if ($periodo !== 'todos') {
        $sql .= " AND p.fecha_prestamo >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)";
    }

    $sql .= " GROUP BY c.id_cliente ";

    // Aplicar ordenamiento
    switch ($orden) {
        case 'prestamos':
            $sql .= "ORDER BY total_prestamos DESC";
            break;
        case 'recientes':
            $sql .= "ORDER BY ultimo_prestamo DESC";
            break;
        case 'nombre':
            $sql .= "ORDER BY c.nombre ASC, c.apellido ASC";
            break;
        default:
            $sql .= "ORDER BY total_prestamos DESC";
    }

    $stmt = $pdo->prepare($sql);
    
    if ($periodo !== 'todos') {
        $stmt->execute([$periodo]);
    } else {
        $stmt->execute();
    }
    
    $usuarios = $stmt->fetchAll();

} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Error al generar el reporte de usuarios activos.";
}

$pageTitle = "Reporte de Usuarios Activos - BiblioTech";
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
                        <h1 class="text-2xl font-bold text-gray-900">Reporte de Usuarios Activos</h1>
                        <p class="text-gray-600">Análisis detallado de la actividad de usuarios</p>
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
                                <p class="text-sm text-gray-500">Total Usuarios</p>
                                <h3 class="text-2xl font-bold text-gray-900">
                                    <?php echo number_format($estadisticas['total_usuarios']); ?>
                                </h3>
                            </div>
                            <div class="p-3 bg-purple-100 rounded-full">
                                <i class="fas fa-users text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-500">Usuarios Activos</p>
                                <h3 class="text-2xl font-bold text-gray-900">
                                    <?php echo number_format($estadisticas['usuarios_activos']); ?>
                                </h3>
                            </div>
                            <div class="p-3 bg-green-100 rounded-full">
                                <i class="fas fa-user-check text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-500">Tasa de Actividad</p>
                                <h3 class="text-2xl font-bold text-gray-900">
                                    <?php 
                                    $tasa = $estadisticas['total_usuarios'] > 0 
                                        ? round(($estadisticas['usuarios_activos'] / $estadisticas['total_usuarios']) * 100, 1)
                                        : 0;
                                    echo $tasa . '%';
                                    ?>
                                </h3>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-full">
                                <i class="fas fa-chart-line text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                    <form method="GET" class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Periodo de Análisis
                            </label>
                            <select name="periodo" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="7" <?php echo $periodo == '7' ? 'selected' : ''; ?>>Última semana</option>
                                <option value="30" <?php echo $periodo == '30' ? 'selected' : ''; ?>>Último mes</option>
                                <option value="90" <?php echo $periodo == '90' ? 'selected' : ''; ?>>Últimos 3 meses</option>
                                <option value="365" <?php echo $periodo == '365' ? 'selected' : ''; ?>>Último año</option>
                                <option value="todos" <?php echo $periodo == 'todos' ? 'selected' : ''; ?>>Todo el tiempo</option>
                            </select>
                        </div>

                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Ordenar por
                            </label>
                            <select name="orden" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="prestamos" <?php echo $orden == 'prestamos' ? 'selected' : ''; ?>>Más préstamos</option>
                                <option value="recientes" <?php echo $orden == 'recientes' ? 'selected' : ''; ?>>Actividad reciente</option>
                                <option value="nombre" <?php echo $orden == 'nombre' ? 'selected' : ''; ?>>Nombre</option>
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

                <!-- Tabla de usuarios -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Usuario
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total Préstamos
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Préstamos Activos
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Último Préstamo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if ($usuario['imagen_cliente']): ?>
                                            <img class="h-10 w-10 rounded-full" 
                                                 src="../../<?php echo htmlspecialchars($usuario['imagen_cliente']); ?>" 
                                                 alt="<?php echo htmlspecialchars($usuario['nombre']); ?>">
                                        <?php else: ?>
                                            <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                                                <i class="fas fa-user text-purple-600"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo htmlspecialchars($usuario['email']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $usuario['total_prestamos']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php 
                                        echo $usuario['prestamos_activos'] > 0 
                                            ? 'bg-green-100 text-green-800' 
                                            : 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo $usuario['prestamos_activos']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php 
                                    echo $usuario['ultimo_prestamo'] 
                                        ? date('d/m/Y', strtotime($usuario['ultimo_prestamo']))
                                        : 'N/A';
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="../usuarios/editar.php?id=<?php echo $usuario['id_cliente']; ?>" 
                                       class="text-purple-600 hover:text-purple-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
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