<?php
//generos/ficcion.php
$pageTitle = 'Literatura - BiblioSis';
require_once '../modules/header.php';

try {
   $sql = "
       SELECT l.*, a.nombre as autor_nombre, a.apellido as autor_apellido,
              g.nombre as genero_nombre
       FROM libros l
       JOIN autores a ON l.id_autor = a.id_autor
       JOIN generos g ON l.id_genero = g.id_genero
       WHERE g.nombre = 'Ficcion'
       ORDER BY l.titulo ASC
   ";
   $stmt = $pdo->prepare($sql);
   $stmt->execute();
   $libros = $stmt->fetchAll();
} catch(PDOException $e) {
   error_log($e->getMessage());
   $error = "Error al cargar los libros.";
}
?>

<!-- Banner -->
<div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white">
   <div class="max-w-7xl mx-auto px-4 py-12">
       <div class="flex items-center space-x-2 text-sm mb-4">
           <a href="../index.php" class="text-purple-100 hover:text-white">Inicio</a>
           <span class="text-purple-300">/</span>
           <span class="text-white">Ficcion</span>
       </div>
       <h1 class="text-4xl font-bold mb-4">Ficcion</h1>
       <p class="text-lg text-purple-100">Explora nuestra colección de obras literarias clásicas y contemporáneas</p>
   </div>
</div>

<!-- Filtros -->
<div class="max-w-7xl mx-auto px-4 py-6">
   <div class="flex flex-wrap items-center justify-between gap-4">
       <div class="flex items-center space-x-4">
           <select name="orden_libros" id="orden_libros" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
               <option value="titulo_asc">Título A-Z</option>
               <option value="titulo_desc">Título Z-A</option>
               <option value="autor">Por autor</option>
               <option value="recientes">Más recientes</option>
           </select>
       </div>
       
       <div class="relative">
           <input type="search" 
                  name="busqueda_libros" 
                  id="busqueda_libros"
                  placeholder="Buscar libros..." 
                  class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
           <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
       </div>
   </div>
</div>

<!-- Resultados -->
<div class="max-w-7xl mx-auto px-4 py-8">
   <div id="results-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
       <?php foreach ($libros as $libro): ?>
       <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
           <div class="relative h-96">
               <?php if ($libro['imagen_portada']): ?>
                   <img src="../<?php echo htmlspecialchars($libro['imagen_portada']); ?>" 
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
               
               <a href="../vistas/libro/detalle.php?id=<?php echo $libro['id_libro']; ?>" 
                  class="block w-full text-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-300">
                   Ver detalles
               </a>
           </div>
       </div>
       <?php endforeach; ?>
   </div>
</div>

<script src="../js/search.js"></script>
<script>
   document.addEventListener('DOMContentLoaded', function() {
       initializeSearch('buscar_libros.php?genero=Ficcion');
   });
</script>

<?php require_once '../modules/footer.php'; ?>