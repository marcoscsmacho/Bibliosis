<?php
// vistas/libro/detalle.php
session_start();
$pageTitle = 'Detalles del Libro - BiblioSis';
require_once '../../modules/header.php';

// Verificar que se proporcionó un ID
if (!isset($_GET['id'])) {
    header('Location: ' . $basePath . 'vistas/catalogo/index.php');
    exit;
}

$id_libro = (int)$_GET['id'];

try {
    // Obtener información detallada del libro
    $stmt = $pdo->prepare("
        SELECT l.*, 
               a.nombre as autor_nombre, a.apellido as autor_apellido,
               a.biografia as autor_biografia,
               g.nombre as genero_nombre, g.descripcion as genero_descripcion,
               e.nombre as editorial_nombre,
               (SELECT COUNT(*) FROM prestamos WHERE id_libro = l.id_libro) as total_prestamos,
               (SELECT COUNT(*) FROM prestamos 
                WHERE id_libro = l.id_libro 
                AND estado = 'Prestado') as prestamos_activos
        FROM libros l
        JOIN autores a ON l.id_autor = a.id_autor
        JOIN generos g ON l.id_genero = g.id_genero
        JOIN editoriales e ON l.id_editorial = e.id_editorial
        WHERE l.id_libro = ?
    ");
    $stmt->execute([$id_libro]);
    $libro = $stmt->fetch();

    if (!$libro) {
        throw new Exception("El libro solicitado no existe.");
    }

    // Obtener libros relacionados 
    $stmt = $pdo->prepare("
        SELECT l.*, a.nombre as autor_nombre, a.apellido as autor_apellido
        FROM libros l
        JOIN autores a ON l.id_autor = a.id_autor
        WHERE l.id_genero = ? AND l.id_libro != ?
        ORDER BY l.fecha_registro DESC
        LIMIT 4
    ");
    $stmt->execute([$libro['id_genero'], $id_libro]);
    $libros_relacionados = $stmt->fetchAll();

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!-- Banner del libro -->
<div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white">
    <div class="max-w-7xl mx-auto px-4 py-12">
        <nav class="text-sm mb-4">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center">
                    <a href="<?php echo $basePath; ?>index.php" class="text-purple-200 hover:text-white">Inicio</a>
                    <span class="mx-2">/</span>
                </li>
                <li class="flex items-center">
                    <a href="<?php echo $basePath; ?>vistas/catalogo/index.php" class="text-purple-200 hover:text-white">Catálogo</a>
                    <span class="mx-2">/</span>
                </li>
                <li class="text-purple-100"><?php echo htmlspecialchars($libro['titulo']); ?></li>
            </ol>
        </nav>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($libro) && !isset($error)): ?>
<!-- Detalles del libro -->
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="md:flex">
            <!-- Imagen y acciones -->
            <div class="md:w-1/3 p-8">
                <div class="aspect-w-3 aspect-h-4 rounded-lg overflow-hidden mb-6">
                    <?php if ($libro['imagen_portada']): ?>
                        <img src="<?php echo $basePath . $libro['imagen_portada']; ?>" 
                             alt="<?php echo htmlspecialchars($libro['titulo']); ?>"
                             class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-book text-gray-400 text-4xl"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Estado y disponibilidad -->
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-gray-600">Estado:</span>
                        <span class="px-3 py-1 rounded-full <?php 
                            echo $libro['estado'] === 'Disponible' 
                                ? 'bg-green-100 text-green-800' 
                                : 'bg-red-100 text-red-800'; ?>">
                            <?php echo htmlspecialchars($libro['estado']); ?>
                        </span>
                    </div>
                    
                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-gray-600">Disponibles:</span>
                        <span class="font-semibold"><?php echo $libro['cantidad_disponible']; ?> de <?php echo $libro['cantidad_total']; ?></span>
                    </div>

                    <?php if ($libro['cantidad_disponible'] > 0 && isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo $basePath; ?>/vistas/prestamo/solicitar.php?id=<?php echo $libro['id_libro']; ?>" 
                           class="block w-full bg-purple-600 text-white text-center px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                            <i class="fas fa-book-reader mr-2"></i>
                            Solicitar Préstamo
                        </a>
                    <?php elseif (!isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo $basePath; ?>login.php" 
                           class="block w-full bg-gray-600 text-white text-center px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Iniciar sesión para prestar
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Información del libro -->
            <div class="md:w-2/3 p-8 border-l">
                <h1 class="text-3xl font-bold text-gray-900 mb-4">
                    <?php echo htmlspecialchars($libro['titulo']); ?>
                </h1>

                <div class="flex items-center space-x-4 mb-6">
                    <a href="#autor" class="text-purple-600 hover:text-purple-800">
                        <?php echo htmlspecialchars($libro['autor_nombre'] . ' ' . $libro['autor_apellido']); ?>
                    </a>
                    <span class="text-gray-400">|</span>
                    <span class="text-gray-600"><?php echo htmlspecialchars($libro['genero_nombre']); ?></span>
                </div>

                <!-- Detalles técnicos -->
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="space-y-2">
                        <p class="text-gray-600">
                            <span class="font-medium">Editorial:</span><br>
                            <?php echo htmlspecialchars($libro['editorial_nombre']); ?>
                        </p>
                        <p class="text-gray-600">
                            <span class="font-medium">ISBN:</span><br>
                            <?php echo htmlspecialchars($libro['isbn']); ?>
                        </p>
                    </div>
                    <div class="space-y-2">
                        <p class="text-gray-600">
                            <span class="font-medium">Año de publicación:</span><br>
                            <?php echo $libro['año_publicacion']; ?>
                        </p>
                        <p class="text-gray-600">
                            <span class="font-medium">Total de préstamos:</span><br>
                            <?php echo $libro['total_prestamos']; ?>
                        </p>
                    </div>
                </div>

                <!-- Sinopsis -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Sinopsis</h2>
                    <p class="text-gray-600 leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($libro['sinopsis'])); ?>
                    </p>
                </div>

                <!-- Información del autor -->
                <div id="autor" class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Sobre el autor</h2>
                    <p class="text-gray-600 leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($libro['autor_biografia'])); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Libros relacionados -->
    <?php if (!empty($libros_relacionados)): ?>
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Libros relacionados</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            <?php foreach ($libros_relacionados as $libro_rel): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <a href="<?php echo $basePath; ?>vistas/libro/detalle.php?id=<?php echo $libro_rel['id_libro']; ?>" 
                   class="block">
                    <div class="relative h-64">
                        <?php if ($libro_rel['imagen_portada']): ?>
                            <img src="<?php echo $basePath . $libro_rel['imagen_portada']; ?>" 
                                 alt="<?php echo htmlspecialchars($libro_rel['titulo']); ?>"
                                 class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-book text-gray-400 text-4xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">
                            <?php echo htmlspecialchars($libro_rel['titulo']); ?>
                        </h3>
                        <p class="text-gray-600 text-sm">
                            <?php echo htmlspecialchars($libro_rel['autor_nombre'] . ' ' . $libro_rel['autor_apellido']); ?>
                        </p>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once '../../modules/footer.php'; ?>