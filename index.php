<?php
// index.php
session_start();
$pageTitle = 'Inicio - BiblioSis';
require_once 'modules/header.php';

?>

<!-- Carrusel de promociones -->
<div class="max-w-7xl mx-auto mt-6 px-4">
    <div class="bg-yellow-400 rounded-lg overflow-hidden">
        <div class="p-8 flex justify-between items-center">
        <div class="max-w-lg">
    <h2 class="text-3xl font-bold text-gray-800 mb-4">Bienvenido a BiblioSIs</h2>
    <p class="text-gray-800 mb-6">Descubre nuestra colección de libros y recursos disponibles.</p>
    <a href="vistas/catalogo/index.php" class="inline-block bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-gray-700">
        Explorar catálogo
    </a>
</div>
            <div class="hidden md:block">
                <img src="img/libros/wtfsat.jpg" alt="Libros destacados" class="w-51 h-64 object-cover rounded-lg shadow-lg" >
            </div>
        </div>
    </div>
</div>

<!-- Sección de Generos -->
<div class="max-w-7xl mx-auto mt-12 px-4">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Generos Populares</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="generos/ficcion.php" class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition-shadow flex items-center space-x-2 group">
            <i class="fas fa-user-astronaut text-gray-800 text-xl group-hover:text-purple-600 transition-colors"></i>
            <h3 class="text-lg font-semibold text-gray-800 group-hover:text-purple-600 transition-colors">Ficcion</h3>
        </a>
        <a href="generos/fantasia.php" class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition-shadow flex items-center space-x-2 group">
            <i class="fas fa-rainbow text-gray-800 text-xl group-hover:text-purple-600 transition-colors"></i>
            <h3 class="text-lg font-semibold text-gray-800 group-hover:text-purple-600 transition-colors">fantasia</h3>
        </a>
        <a href="generos/misterio.php" class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition-shadow flex items-center space-x-2 group">
            <i class="fas fa-crow text-gray-800 text-xl group-hover:text-purple-600 transition-colors"></i>
            <h3 class="text-lg font-semibold text-gray-800 group-hover:text-purple-600 transition-colors">Misterio y Suspenso</h3>
        </a>
        <a href="generos/romance.php" class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition-shadow flex items-center space-x-2 group">
            <i class="fas fa-heart text-gray-800 text-xl group-hover:text-purple-600 transition-colors"></i>
            <h3 class="text-lg font-semibold text-gray-800 group-hover:text-purple-600 transition-colors">  Romance</h3>
        </a>
    </div>
</div>

<!-- Libros Recientes -->
<div class="max-w-7xl mx-auto mt-12 px-4 mb-12">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Libros Recientes</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <?php
        // Obtener libros recientes de la base de datos
        $stmt = $pdo->query("SELECT l.*, a.nombre as autor_nombre, a.apellido as autor_apellido 
                            FROM libros l 
                            JOIN autores a ON l.id_autor = a.id_autor 
                            ORDER BY l.fecha_registro DESC LIMIT 4");
        while ($libro = $stmt->fetch(PDO::FETCH_ASSOC)) {
        ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden flex flex-col h-full">
                <!-- Contenedor de imagen con altura fija -->
                <div class="relative h-100 w-full bg-gray-100">
                    <?php if ($libro['imagen_portada']): ?>
                        <img src="<?php echo $libro['imagen_portada'] ?? '/api/placeholder/300/400'; ?>" 
                             alt="<?php echo htmlspecialchars($libro['titulo']); ?>" 
                             class="w-full h-full object-cover object-center hover:opacity-90 transition-opacity">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center">
                            <i class="fas fa-book text-gray-400 text-4xl"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Información del libro con altura controlada -->
                <div class="p-4 flex-1 flex flex-col">
                    <h3 class="font-semibold text-gray-800 h-12 overflow-hidden line-clamp-2">
                        <?php echo htmlspecialchars($libro['titulo']); ?>
                    </h3>
                    <p class="text-gray-600 text-sm truncate">
                        <?php echo htmlspecialchars($libro['autor_nombre'] . ' ' . $libro['autor_apellido']); ?>
                    </p>
                    <div class="mt-auto pt-3">
                        <a href="vistas/libro/detalle.php?id=<?php echo $libro['id_libro']; ?>" 
                           class="mt-2 block w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 text-center">
                            Ver detalles
                        </a>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<?php require_once 'modules/footer.php'; ?>