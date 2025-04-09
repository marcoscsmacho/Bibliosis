<?php
//admin/reportes/estadisticas_mensuales.php
session_start();
require_once '../../config/config.php';

// Verificar permisos (solo administradores)
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../login.php');
    exit;
}

// Obtener parámetros de filtrado
$periodo = isset($_GET['periodo']) ? (int)$_GET['periodo'] : 12; // Meses a mostrar, por defecto 12
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : date('Y'); // Año a mostrar, por defecto el actual

try {
    // Obtenemos estadísticas mensuales de préstamos
    $sql_prestamos = "
        SELECT 
            MONTH(fecha_prestamo) as mes,
            YEAR(fecha_prestamo) as anio,
            COUNT(*) as total_prestamos,
            SUM(CASE WHEN estado = 'Prestado' THEN 1 ELSE 0 END) as prestamos_activos,
            SUM(CASE WHEN estado = 'Devuelto' THEN 1 ELSE 0 END) as prestamos_devueltos,
            SUM(CASE 
                WHEN estado = 'Devuelto' AND fecha_devolucion_real > fecha_devolucion_esperada 
                THEN 1 ELSE 0 END) as devoluciones_atrasadas
        FROM prestamos
        WHERE YEAR(fecha_prestamo) = ?
        GROUP BY YEAR(fecha_prestamo), MONTH(fecha_prestamo)
        ORDER BY YEAR(fecha_prestamo), MONTH(fecha_prestamo)
    ";
    
    $stmt = $pdo->prepare($sql_prestamos);
    $stmt->execute([$anio]);
    $datos_mensuales = $stmt->fetchAll();
    
    // Obtener lista de años disponibles para el filtro
    $stmt = $pdo->query("
        SELECT DISTINCT YEAR(fecha_prestamo) as anio 
        FROM prestamos 
        ORDER BY anio DESC
    ");
    $anios_disponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Si no hay años disponibles, usamos el año actual
    if (empty($anios_disponibles)) {
        $anios_disponibles = [date('Y')];
    }
    
    // Obtener estadísticas de libros más prestados
    $stmt = $pdo->prepare("
        SELECT l.titulo, COUNT(p.id_prestamo) as total_prestamos,
               a.nombre as autor_nombre, a.apellido as autor_apellido
        FROM prestamos p
        JOIN libros l ON p.id_libro = l.id_libro
        JOIN autores a ON l.id_autor = a.id_autor
        WHERE YEAR(p.fecha_prestamo) = ?
        GROUP BY l.id_libro
        ORDER BY total_prestamos DESC
        LIMIT 10
    ");
    $stmt->execute([$anio]);
    $libros_populares = $stmt->fetchAll();
    
    // Obtener estadísticas de géneros más populares
    $stmt = $pdo->prepare("
        SELECT g.nombre as genero, COUNT(p.id_prestamo) as total_prestamos
        FROM prestamos p
        JOIN libros l ON p.id_libro = l.id_libro
        JOIN generos g ON l.id_genero = g.id_genero
        WHERE YEAR(p.fecha_prestamo) = ?
        GROUP BY g.id_genero
        ORDER BY total_prestamos DESC
    ");
    $stmt->execute([$anio]);
    $generos_populares = $stmt->fetchAll();
    
    // Estadísticas de devoluciones a tiempo vs. atrasadas
    $stmt = $pdo->prepare("
        SELECT 
            MONTH(fecha_devolucion_real) as mes,
            YEAR(fecha_devolucion_real) as anio,
            SUM(CASE WHEN fecha_devolucion_real <= fecha_devolucion_esperada THEN 1 ELSE 0 END) as devoluciones_a_tiempo,
            SUM(CASE WHEN fecha_devolucion_real > fecha_devolucion_esperada THEN 1 ELSE 0 END) as devoluciones_atrasadas
        FROM prestamos
        WHERE estado = 'Devuelto' AND YEAR(fecha_devolucion_real) = ?
        GROUP BY YEAR(fecha_devolucion_real), MONTH(fecha_devolucion_real)
        ORDER BY YEAR(fecha_devolucion_real), MONTH(fecha_devolucion_real)
    ");
    $stmt->execute([$anio]);
    $devoluciones_stats = $stmt->fetchAll();
    
    // Preparar datos para los gráficos
    $meses = [];
    $prestamos_por_mes = [];
    $devoluciones_por_mes = [];
    $atrasados_por_mes = [];
    
    // Inicializar arrays con ceros para todos los meses
    for ($i = 1; $i <= 12; $i++) {
        $nombre_mes = date('M', mktime(0, 0, 0, $i, 1, $anio));
        $meses[$i] = $nombre_mes;
        $prestamos_por_mes[$i] = 0;
        $devoluciones_por_mes[$i] = 0;
        $atrasados_por_mes[$i] = 0;
    }
    
    // Llenar con datos reales
    foreach ($datos_mensuales as $dato) {
        $mes = (int)$dato['mes'];
        $prestamos_por_mes[$mes] = (int)$dato['total_prestamos'];
    }
    
    foreach ($devoluciones_stats as $dato) {
        $mes = (int)$dato['mes'];
        $devoluciones_por_mes[$mes] = (int)$dato['devoluciones_a_tiempo'];
        $atrasados_por_mes[$mes] = (int)$dato['devoluciones_atrasadas'];
    }
    
} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Error al generar las estadísticas: " . $e->getMessage();
}

$pageTitle = "Estadísticas Mensuales - BiblioSis";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
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
                        <h1 class="text-2xl font-bold text-gray-900">Estadísticas Mensuales</h1>
                        <p class="text-gray-600">Análisis detallado del rendimiento mensual de la biblioteca</p>
                    </div>
                    <div class="space-x-2">
                        <a href="index.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Volver
                        </a>
                        <a href="#" onclick="window.print()" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                            <i class="fas fa-print mr-2"></i>
                            Imprimir
                        </a>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                        <span class="block sm:inline"><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <!-- Filtros -->
                <div class="bg-white rounded-lg shadow p-6 mb-8">
                    <form method="GET" id="filtrosForm" class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Año
                            </label>
                            <select name="anio" id="selectAnio"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <?php foreach ($anios_disponibles as $anio_option): ?>
                                    <option value="<?php echo $anio_option; ?>" <?php echo $anio == $anio_option ? 'selected' : ''; ?>>
                                        <?php echo $anio_option; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Período
                            </label>
                            <select name="periodo" id="selectPeriodo"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="3" <?php echo $periodo == 3 ? 'selected' : ''; ?>>Últimos 3 meses</option>
                                <option value="6" <?php echo $periodo == 6 ? 'selected' : ''; ?>>Últimos 6 meses</option>
                                <option value="12" <?php echo $periodo == 12 ? 'selected' : ''; ?>>Último año</option>
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Gráficos principales -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Gráfico de préstamos por mes -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Préstamos por Mes</h2>
                        <div class="h-80">
                            <canvas id="prestamosChart"></canvas>
                        </div>
                    </div>

                    <!-- Gráfico de devoluciones a tiempo vs. atrasadas -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Devoluciones: A Tiempo vs. Atrasadas</h2>
                        <div class="h-80">
                            <canvas id="devolucionesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas adicionales -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Libros más prestados -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Libros Más Prestados</h2>
                        <?php if (empty($libros_populares)): ?>
                            <p class="text-gray-500 text-center py-4">No hay datos disponibles para este período</p>
                        <?php else: ?>
                            <div class="overflow-auto max-h-96">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Autor</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Préstamos</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($libros_populares as $libro): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo htmlspecialchars($libro['titulo']); ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500">
                                                        <?php echo htmlspecialchars($libro['autor_nombre'] . ' ' . $libro['autor_apellido']); ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                        <?php echo $libro['total_prestamos']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Géneros más populares -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Géneros Más Populares</h2>
                        <?php if (empty($generos_populares)): ?>
                            <p class="text-gray-500 text-center py-4">No hay datos disponibles para este período</p>
                        <?php else: ?>
                            <div class="h-80">
                                <canvas id="generosChart"></canvas>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configurar gráficos con Chart.js y manejar los filtros automáticos
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar envío automático del formulario al cambiar los selectores
            document.getElementById('selectAnio').addEventListener('change', function() {
                document.getElementById('filtrosForm').submit();
            });
            
            document.getElementById('selectPeriodo').addEventListener('change', function() {
                document.getElementById('filtrosForm').submit();
            });
            
            // Datos para los gráficos
            const meses = <?php echo json_encode(array_values($meses)); ?>;
            const prestamosPorMes = <?php echo json_encode(array_values($prestamos_por_mes)); ?>;
            const devolucionesPorMes = <?php echo json_encode(array_values($devoluciones_por_mes)); ?>;
            const atrasadosPorMes = <?php echo json_encode(array_values($atrasados_por_mes)); ?>;
            
            // Colores para los gráficos
            const colorPurple = 'rgba(126, 34, 206, 0.7)';
            const colorGreen = 'rgba(16, 185, 129, 0.7)';
            const colorRed = 'rgba(239, 68, 68, 0.7)';
            
            // Gráfico de préstamos por mes
            const prestamosCtx = document.getElementById('prestamosChart').getContext('2d');
            new Chart(prestamosCtx, {
                type: 'bar',
                data: {
                    labels: meses,
                    datasets: [{
                        label: 'Total Préstamos',
                        data: prestamosPorMes,
                        backgroundColor: colorPurple,
                        borderColor: 'rgba(126, 34, 206, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

            // Gráfico de devoluciones: a tiempo vs. atrasadas
            const devolucionesCtx = document.getElementById('devolucionesChart').getContext('2d');
            new Chart(devolucionesCtx, {
                type: 'bar',
                data: {
                    labels: meses,
                    datasets: [
                        {
                            label: 'A Tiempo',
                            data: devolucionesPorMes,
                            backgroundColor: colorGreen,
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Atrasadas',
                            data: atrasadosPorMes,
                            backgroundColor: colorRed,
                            borderColor: 'rgba(239, 68, 68, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        },
                        x: {
                            stacked: false
                        }
                    }
                }
            });

            // Gráfico de géneros más populares
            <?php if (!empty($generos_populares)): ?>
            const generosCtx = document.getElementById('generosChart').getContext('2d');
            new Chart(generosCtx, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode(array_column($generos_populares, 'genero')); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_column($generos_populares, 'total_prestamos')); ?>,
                        backgroundColor: [
                            'rgba(126, 34, 206, 0.7)',
                            'rgba(79, 70, 229, 0.7)',
                            'rgba(59, 130, 246, 0.7)',
                            'rgba(16, 185, 129, 0.7)',
                            'rgba(245, 158, 11, 0.7)',
                            'rgba(239, 68, 68, 0.7)',
                            'rgba(236, 72, 153, 0.7)',
                            'rgba(139, 92, 246, 0.7)',
                            'rgba(6, 182, 212, 0.7)',
                            'rgba(202, 138, 4, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
            <?php endif; ?>
        });
    </script>
</body>
</html>