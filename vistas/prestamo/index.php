<?php
// vistas/prestamos/index.php
ob_start(); // Inicia el buffer de salida
session_start();
$pageTitle = 'Mis Préstamos - BiblioSis';
require_once '../../modules/header.php';

// Verificar que el usuario esté logueado
if (!isLoggedIn()) {
    $_SESSION['error'] = "Debes iniciar sesión para ver tus préstamos.";
    echo '<script>window.location.href = "' . $basePath . 'login.php";</script>';
    exit;
}

// Obtener parámetros de filtrado
$estado = isset($_GET['estado']) ? cleanInput($_GET['estado']) : 'todos';
$orden = isset($_GET['orden']) ? cleanInput($_GET['orden']) : 'recientes';

try {
    // Construir la consulta base
    $sql = "
        SELECT p.*, l.titulo, l.imagen_portada,
               a.nombre as autor_nombre, a.apellido as autor_apellido,
               DATEDIFF(p.fecha_devolucion_esperada, CURRENT_DATE) as dias_restantes,
               CASE 
                   WHEN p.estado = 'Prestado' AND fecha_devolucion_esperada < CURRENT_DATE 
                   THEN DATEDIFF(CURRENT_DATE, p.fecha_devolucion_esperada)
                   ELSE 0 
               END as dias_atraso
        FROM prestamos p
        JOIN libros l ON p.id_libro = l.id_libro
        JOIN autores a ON l.id_autor = a.id_autor
        WHERE p.id_cliente = ?
    ";

    $params = [$_SESSION['user_id']];

    // Aplicar filtro por estado
    if ($estado !== 'todos') {
        $sql .= " AND p.estado = ?";
        $params[] = $estado;
    }

    // Aplicar ordenamiento
    switch ($orden) {
        case 'recientes':
            $sql .= " ORDER BY p.fecha_prestamo DESC";
            break;
        case 'antiguos':
            $sql .= " ORDER BY p.fecha_prestamo ASC";
            break;
        case 'proximos':
            $sql .= " ORDER BY dias_restantes ASC";
            break;
        case 'atrasados':
            $sql .= " ORDER BY dias_atraso DESC";
            break;
    }

    // Ejecutar la consulta
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $prestamos = $stmt->fetchAll();

    // Obtener estadísticas
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_prestamos,
            SUM(CASE WHEN estado = 'Prestado' THEN 1 ELSE 0 END) as prestamos_activos,
            SUM(CASE WHEN estado = 'Prestado' AND fecha_devolucion_esperada < CURRENT_DATE THEN 1 ELSE 0 END) as prestamos_atrasados
        FROM prestamos
        WHERE id_cliente = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $stats = $stmt->fetch();

} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Error al cargar los préstamos.";
}
?>

<!-- Banner -->
<div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white">
    <div class="max-w-7xl mx-auto px-4 py-12">
        <h1 class="text-4xl font-bold mb-4">Mis Préstamos</h1>
        <nav class="text-sm mb-6">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center">
                    <a href="<?php echo $basePath; ?>index.php" class="text-purple-200 hover:text-white">Inicio</a>
                    <span class="mx-2">/</span>
                </li>
                <li class="text-purple-100">Préstamos</li>
            </ol>
        </nav>
    </div>
</div>


<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6" role="alert">
    <p class="font-bold">Información importante</p>
    <p>Para devolver un libro, debe llevarlo físicamente a la biblioteca. El personal de la biblioteca procesará su devolución.</p>
</div>

<!-- Contenido principal -->
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Tarjetas de estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-gray-500 text-sm">Total Préstamos</p>
                    <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['total_prestamos']; ?></h3>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="fas fa-book text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-gray-500 text-sm">Préstamos Activos</p>
                    <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['prestamos_activos']; ?></h3>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-bookmark text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-gray-500 text-sm">Préstamos Atrasados</p>
                    <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['prestamos_atrasados']; ?></h3>
                </div>
                <div class="p-3 bg-red-100 rounded-full">
                    <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
    <form id="filtroForm" method="GET" class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
            <select name="estado" id="filtroEstado"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                <option value="todos" <?php echo $estado === 'todos' ? 'selected' : ''; ?>>Todos</option>
                <option value="Prestado" <?php echo $estado === 'Prestado' ? 'selected' : ''; ?>>Activos</option>
                <option value="Devuelto" <?php echo $estado === 'Devuelto' ? 'selected' : ''; ?>>Devueltos</option>
            </select>
        </div>

        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Ordenar por</label>
            <select name="orden" id="filtroOrden"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                <option value="recientes" <?php echo $orden === 'recientes' ? 'selected' : ''; ?>>Más recientes</option>
                <option value="antiguos" <?php echo $orden === 'antiguos' ? 'selected' : ''; ?>>Más antiguos</option>
                <option value="proximos" <?php echo $orden === 'proximos' ? 'selected' : ''; ?>>Próximos a vencer</option>
                <option value="atrasados" <?php echo $orden === 'atrasados' ? 'selected' : ''; ?>>Atrasados</option>
            </select>
        </div>

        <!-- Este botón ahora será invisible pero lo mantenemos para compatibilidad -->
        <div class="flex items-end hidden">
            <button type="submit" id="btnFiltrar"
                    class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                <i class="fas fa-filter mr-2"></i>
                Aplicar filtros
            </button>
        </div>
    </form>
</div>

    <!-- Lista de préstamos -->
    <?php if (empty($prestamos)): ?>
        <div class="text-center py-12 bg-white rounded-lg shadow">
            <i class="fas fa-book-reader text-gray-400 text-5xl mb-4"></i>
            <p class="text-gray-600 text-lg mb-4">No tienes préstamos que mostrar.</p>
            <a href="<?php echo $basePath; ?>vistas/catalogo/index.php" 
               class="inline-flex items-center text-purple-600 hover:text-purple-800">
                <i class="fas fa-search mr-2"></i>
                Explorar catálogo
            </a>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="grid gap-6 p-6">
                <?php foreach ($prestamos as $prestamo): ?>
                    <div class="flex flex-col md:flex-row items-start gap-6 p-4 border rounded-lg hover:shadow-md transition-shadow">
                        <!-- Imagen del libro -->
                        <div class="w-full md:w-32 flex-shrink-0">
                            <?php if ($prestamo['imagen_portada']): ?>
                                <img src="<?php echo $basePath . $prestamo['imagen_portada']; ?>" 
                                     alt="<?php echo htmlspecialchars($prestamo['titulo']); ?>"
                                     class="w-full h-40 md:h-48 object-cover rounded">
                            <?php else: ?>
                                <div class="w-full h-40 md:h-48 bg-gray-200 flex items-center justify-center rounded">
                                    <i class="fas fa-book text-gray-400 text-4xl"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Detalles del préstamo -->
                        <div class="flex-1">
                            <div class="flex flex-wrap justify-between gap-4 mb-4">
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-800">
                                        <?php echo htmlspecialchars($prestamo['titulo']); ?>
                                    </h3>
                                    <p class="text-gray-600">
                                        <?php echo htmlspecialchars($prestamo['autor_nombre'] . ' ' . $prestamo['autor_apellido']); ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="px-3 py-1 rounded-full text-sm font-medium <?php 
                                        echo $prestamo['estado'] === 'Prestado' 
                                            ? ($prestamo['dias_restantes'] < 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800')
                                            : 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo htmlspecialchars($prestamo['estado']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-4">
                                <div>
                                    <span class="text-gray-500">Fecha de préstamo:</span><br>
                                    <?php echo date('d/m/Y', strtotime($prestamo['fecha_prestamo'])); ?>
                                </div>
                                <div>
                                    <span class="text-gray-500">Fecha de devolución:</span><br>
                                    <?php echo date('d/m/Y', strtotime($prestamo['fecha_devolucion_esperada'])); ?>
                                </div>
                                <div>
                                    <?php if ($prestamo['estado'] === 'Prestado'): ?>
                                        <?php if ($prestamo['dias_restantes'] > 0): ?>
                                            <span class="text-gray-500">Tiempo restante:</span><br>
                                            <span class="text-green-600"><?php echo $prestamo['dias_restantes']; ?> días</span>
                                        <?php else: ?>
                                            <span class="text-gray-500">Días de atraso:</span><br>
                                            <span class="text-red-600"><?php echo abs($prestamo['dias_restantes']); ?> días</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-500">Devuelto el:</span><br>
                                        <?php echo $prestamo['fecha_devolucion_real'] 
                                            ? date('d/m/Y', strtotime($prestamo['fecha_devolucion_real']))
                                            : 'N/A'; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <a href="<?php echo $basePath; ?>vistas/libro/detalle.php?id=<?php echo $prestamo['id_libro']; ?>" 
                                class="inline-flex items-center text-purple-600 hover:text-purple-800">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Ver libro
                                </a>
                                <?php if ($prestamo['estado'] === 'Prestado'): ?>
                                    <span class="inline-flex items-center text-gray-600">
                                        <i class="fas fa-info-circle mr-2"></i>
                                            Para devolver, lleve el libro a la biblioteca
                                        </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Obtener referencias a los elementos de filtro
    const filtroForm = document.getElementById('filtroForm');
    const filtroEstado = document.getElementById('filtroEstado');
    const filtroOrden = document.getElementById('filtroOrden');
    
    // Función para enviar el formulario
    function aplicarFiltros() {
        // Mostrar algún indicador de carga si es necesario
        document.body.classList.add('cursor-wait');
        
        // Enviar el formulario
        filtroForm.submit();
    }
    
    // Escuchar cambios en los selectores
    filtroEstado.addEventListener('change', aplicarFiltros);
    filtroOrden.addEventListener('change', aplicarFiltros);
    
    // Opcional: Mostrar mensaje mientras se carga
    filtroForm.addEventListener('submit', function() {
        const resultadosContainer = document.querySelector('.bg-white.rounded-lg.shadow.overflow-hidden');
        if (resultadosContainer) {
            resultadosContainer.innerHTML = `
                <div class="p-8 text-center">
                    <i class="fas fa-spinner fa-spin text-purple-600 text-3xl mb-4"></i>
                    <p class="text-gray-600">Actualizando resultados...</p>
                </div>
            `;
        }
    });
});
</script>
   
<?php require_once '../../modules/footer.php';  ob_end_flush();?>
