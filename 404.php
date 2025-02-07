<?php
session_start();
http_response_code(404); // Establece explícitamente el código de estado HTTP
$pageTitle = 'Página no encontrada - BiblioSis';
require_once 'modules/header.php';
?>

<div class="min-h-[calc(100vh-200px)] flex items-center justify-center px-4">
    <div class="max-w-lg w-full text-center">
        <!-- Ilustración de error -->
        <div class="mb-8">
            <div class="relative mx-auto w-64 h-64">
                <i class="fas fa-book-dead text-purple-200 text-9xl absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"></i>
                <div class="absolute inset-0 bg-purple-100 rounded-full opacity-20 animate-ping"></div>
            </div>
        </div>

        <!-- Mensaje de error -->
        <h1 class="text-6xl font-bold text-purple-600 mb-4">404</h1>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">¡Página no encontrada!</h2>
        <p class="text-gray-600 mb-8">
            Lo sentimos, la página que estás buscando parece haberse perdido en nuestra biblioteca.
        </p>

        <!-- Botones de acción -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button onclick="history.back()" 
                    class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver atrás
            </button>
            <a href="<?php echo $basePath; ?>index.php" 
               class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                <i class="fas fa-home mr-2"></i>
                Ir al inicio
            </a>
        </div>

        <!-- Sugerencias -->
        <div class="mt-12 text-gray-600">
            <p class="mb-4">Mientras tanto, podrías:</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center text-sm">
                <a href="<?php echo $basePath; ?>vistas/catalogo/index.php" 
                   class="flex items-center justify-center text-purple-600 hover:text-purple-800">
                    <i class="fas fa-book mr-2"></i>
                    Explorar el catálogo
                </a>
                <a href="<?php echo $basePath; ?>vistas/prestamo/index.php" 
                   class="flex items-center justify-center text-purple-600 hover:text-purple-800">
                    <i class="fas fa-bookmark mr-2"></i>
                    Ver tus préstamos
                </a>
                <a href="<?php echo $basePath; ?>ayuda.php" 
                   class="flex items-center justify-center text-purple-600 hover:text-purple-800">
                    <i class="fas fa-question-circle mr-2"></i>
                    Buscar ayuda
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'modules/footer.php'; ?>