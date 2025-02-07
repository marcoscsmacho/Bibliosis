<?php
// vistas/catalogo/index.php
session_start();
$pageTitle = 'Catálogo - BiblioSis';
require_once '../../config/config.php';
require_once '../../modules/header.php';

try {
    // Obtener géneros para el filtro
    $stmt_generos = $pdo->query("SELECT * FROM generos ORDER BY nombre");
    $generos = $stmt_generos->fetchAll();

    // Consulta inicial para mostrar todos los libros
    $stmt = $pdo->query("
        SELECT l.*, a.nombre as autor_nombre, a.apellido as autor_apellido,
               g.nombre as genero_nombre, e.nombre as editorial_nombre
        FROM libros l
        JOIN autores a ON l.id_autor = a.id_autor
        JOIN generos g ON l.id_genero = g.id_genero
        JOIN editoriales e ON l.id_editorial = e.id_editorial
        ORDER BY l.fecha_registro DESC
    ");
    $libros = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Error al cargar el catálogo.";
}
?>

<!-- Banner del catálogo -->
<div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white">
    <div class="max-w-7xl mx-auto px-4 py-12">
        <h1 class="text-4xl font-bold mb-4">Catálogo de Libros</h1>
        <p class="text-lg text-purple-100">Explora nuestra colección completa de libros</p>
        <nav class="text-sm mt-4">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center">
                    <a href="../../index.php" class="text-purple-200 hover:text-white">Inicio</a>
                    <span class="mx-2 text-purple-300">/</span>
                </li>
                <li class="text-purple-100">Catálogo</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Filtros y contenido principal -->
<div class="max-w-7xl mx-auto">
    <!-- Filtros -->
    <div class="flex flex-wrap gap-4 p-4">
        <input type="text" 
               id="busqueda" 
               placeholder="Buscar libros..." 
               class="flex-1 p-2 border rounded focus:ring-2 focus:ring-purple-600 focus:outline-none">
               
        <select id="genero" 
                class="p-2 border rounded focus:ring-2 focus:ring-purple-600 focus:outline-none">
            <option value="">Todos los géneros</option>
            <?php foreach($generos as $genero): ?>
                <option value="<?php echo $genero['id_genero']; ?>">
                    <?php echo htmlspecialchars($genero['nombre']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select id="orden" 
                class="p-2 border rounded focus:ring-2 focus:ring-purple-600 focus:outline-none">
            <option value="reciente">Más recientes</option>
            <option value="titulo_asc">Título A-Z</option>
            <option value="titulo_desc">Título Z-A</option>
            <option value="autor">Por autor</option>
        </select>
    </div>

    <!-- Contenedor de resultados -->
    <div id="resultados" class="p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($libros as $libro): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="relative h-96">
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
                    
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">
                            <?php echo htmlspecialchars($libro['titulo']); ?>
                        </h3>
                        <p class="text-gray-600 mb-4">
                            <?php echo htmlspecialchars($libro['autor_nombre'] . ' ' . $libro['autor_apellido']); ?>
                        </p>
                        
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2 py-1 text-sm rounded-full <?php 
                                echo $libro['estado'] === 'Disponible' 
                                    ? 'bg-green-100 text-green-800' 
                                    : 'bg-red-100 text-red-800'; ?>">
                                <?php echo htmlspecialchars($libro['estado']); ?>
                            </span>
                            <span class="text-sm text-gray-500">
                                <?php echo $libro['cantidad_disponible']; ?> disponibles
                            </span>
                        </div>
                        
                        <a href="<?php echo $basePath; ?>vistas/libro/detalle.php?id=<?php echo $libro['id_libro']; ?>" 
                           class="block w-full text-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            Ver detalles
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Incluir el archivo JavaScript -->
<script src="../../js/catalogo.js"></script>

<?php require_once '../../modules/footer.php'; ?>