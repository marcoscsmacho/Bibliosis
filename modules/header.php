<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determinar la ruta base de forma robusta
function getBasePath() {
    // Definir la raíz de la aplicación explícitamente
    return '/biblioteca/';
}

$basePath = getBasePath();
require_once $_SERVER['DOCUMENT_ROOT'] . '/biblioteca/config/config.php';

// Determinar el título de la página si no está definido
if (!isset($pageTitle)) {
    $pageTitle = 'BiblioSis';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="icon" type="image/svg+xml" href="<?php echo $basePath; ?>img/favicon.svg">
    <link rel="icon" type="image/png" href="<?php echo $basePath; ?>img/favicon.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dropdown-menu {
            display: none;
        }
        .dropdown-menu.active {
            display: block;
        }
        .category-item:hover .categories-submenu {
            display: block;
        }
        .categories-submenu {
            display: none;
        }
        .category-item:hover {
            background-color: #F3F4F6;
        }
    </style>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- Logo y Menú -->
                <div class="flex items-center">
                    <!-- Logo -->
                    <div class="flex-shrink-0">
                    <a href="<?php echo $basePath; ?>index.php" class="text-2xl font-bold text-purple-700">BiblioSis</a>
                    </div>
                    <!-- Menú de categorías -->
                    <div class="relative ml-4 group">
                        <button class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:text-purple-600 focus:outline-none" id="categoryButton">
                            <i class="fas fa-bars"></i>
                            <span>Categorías</span>
                        </button>

                        <div id="categoryMenu" class="hidden absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                <!-- Ficción -->
                                <div class="category-item relative">
                                    <a href="<?php echo $basePath; ?>generos/ficcion.php" 
                                       class="flex justify-between items-center px-4 py-2 text-sm text-gray-700 hover:bg-purple-50">
                                        <span>Ficción</span>
                                        <i class="fas fa-chevron-right text-xs"></i>
                                    </a>
                                    <div class="categories-submenu absolute left-full top-0 w-56 rounded-md shadow-lg bg-white">
                                        <a href="<?php echo $basePath; ?>generos/ficcion.php?cat=novela-historica" 
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50">Novela Histórica</a>
                                        <a href="<?php echo $basePath; ?>generos/ficcion.php?cat=realismo-contemporaneo" 
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50">Realismo Contemporáneo</a>
                                       <a href="<?php echo $basePath; ?>generos/ficcion.php?cat=aventura" 
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50">Aventura</a>
                                    </div>
                                </div>

                                <!-- Fantasía y Ciencia Ficción -->
                                <div class="category-item relative">
                                   <a href="<?php echo $basePath; ?>generos/fantasia.php" 
                                       class="flex justify-between items-center px-4 py-2 text-sm text-gray-700 hover:bg-purple-50">
                                        <span>Fantasía y Ciencia Ficción</span>
                                        <i class="fas fa-chevron-right text-xs"></i>
                                    </a>
                                    <div class="categories-submenu absolute left-full top-0 w-56 rounded-md shadow-lg bg-white">
                                        <a href="<?php echo $basePath; ?>generos/fantasia.php?cat=fantasia-epica" 
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50">Fantasía Épica</a>
                                        <a href="<?php echo $basePath; ?>generos/fantasia.php?cat=ciencia-ficcion-distopica" 
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50">Ciencia Ficción Distópica</a>
                                       <a href="<?php echo $basePath; ?>eneros/fantasia.php?cat=fantasia-urbana" 
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50">Fantasía Urbana</a>
                                    </div>
                                </div>

                                <!-- Misterio y Suspenso -->
                                <div class="category-item relative">
                                    <a href="<?php echo $basePath; ?>generos/misterio.php" 
                                       class="flex justify-between items-center px-4 py-2 text-sm text-gray-700 hover:bg-purple-50">
                                        <span>Misterio y Suspenso</span>
                                        <i class="fas fa-chevron-right text-xs"></i>
                                    </a>
                                    <div class="categories-submenu absolute left-full top-0 w-56 rounded-md shadow-lg bg-white">
                                        <a href="<?php echo $basePath; ?>generos/misterio.php?cat=novela-policial" 
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50">Novela Policial</a>
                                        <a href="<?php echo $basePath; ?>generos/misterio.php?cat=thriller-psicologico" 
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50">Thriller Psicológico</a>
                                        <a href="<?php echo $basePath; ?>generos/misterio.php?cat=misterio-sobrenatural" 
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50">Misterio Sobrenatural</a>
                                    </div>
                                </div>

                                <!-- Romance -->
                                <div class="category-item relative">
                                    <a href="<?php echo $basePath; ?>generos/romance.php" 
                                       class="flex justify-between items-center px-4 py-2 text-sm text-gray-700 hover:bg-purple-50">
                                        <span>Romance</span>
                                        <i class="fas fa-chevron-right text-xs"></i>
                                    </a>
                                    <div class="categories-submenu absolute left-full top-0 w-56 rounded-md shadow-lg bg-white">
                                        <a href="<?php echo $basePath; ?>generos/romance.php?cat=romance-contemporaneo" 
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50">Romance Contemporáneo</a>
                                        <a href="<?php echo $basePath; ?>generos/romance.php?cat=romance-historico" 
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50">Romance Histórico</a>
                                        <a href="<?php echo $basePath; ?>generos/romance.php?cat=comedia-romantica" 
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50">Comedia Romántica</a>
                                    </div>
                                </div>

                                <div class="border-t border-gray-100">
                                    <a href="<?php echo $basePath; ?>ayuda.php" 
                                       class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-purple-50">
                                        <i class="fas fa-question-circle mr-2 text-purple-600"></i>
                                        <span>Ayuda</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Buscador -->
        <div class="flex-1 max-w-lg mx-6">
        <div class="relative">
            <input type="search" 
                    id="search-input"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-600"
                    placeholder="¿Qué libro estás buscando?">
            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <div id="search-results" class="hidden absolute left-0 right-0 mt-1 bg-white border rounded-lg shadow-lg z-50 max-h-96 overflow-y-auto"></div>
        </div>
        </div>

                <!-- Menú derecho -->
                <div class="flex items-center gap-4">
                    <a href="<?php echo $basePath; ?>vistas/catalogo/index.php" class="text-gray-600 hover:text-purple-700">
                        Catálogo
                    </a>
                    <?php if (isLoggedIn()): ?>
                    <a href="<?php echo $basePath; ?>vistas/prestamo/index.php" class="text-gray-600 hover:text-purple-700">
                           Préstamos
                    </a>
                <?php endif; ?>
                    
                    <!-- Usuario -->
                    <div class="relative">
                        <?php if (isLoggedIn()): ?>
                            <button id="userMenuButton" 
                                    class="flex items-center gap-2 text-gray-600 hover:text-purple-700">
                                <span><?php echo htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario'); ?></span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div id="userDropdown" 
                                 class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl z-50">
                                <?php if (isAdmin() || isBibliotecario()): ?>
                                    <a href="<?php echo $basePath; ?>admin/dashboard.php" 
                                       class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-purple-50">
                                        <i class="fas fa-tachometer-alt w-5"></i>
                                        <span>Dashboard</span>
                                    </a>
                                    <div class="border-t border-gray-100"></div>
                                <?php endif; ?>
                                
                                <a href="<?php echo $basePath; ?>perfil.php" 
                                   class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-purple-50">
                                    <i class="fas fa-user-edit w-5"></i>
                                    <span>Editar Perfil</span>
                                </a>
                                
                                <a href="<?php echo $basePath; ?>logout.php" 
                                   class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-purple-50">
                                    <i class="fas fa-sign-out-alt w-5"></i>
                                    <span>Cerrar Sesión</span>
                                </a>
                            </div>
                        <?php else: ?>
                            <a href="<?php echo $basePath; ?>login.php" 
                               class="flex items-center gap-2 text-gray-600 hover:text-purple-700">
                                <i class="fas fa-user"></i>
                                <span>Mi Cuenta</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Script para el menú de categorías
        const categoryButton = document.getElementById('categoryButton');
        const categoryMenu = document.getElementById('categoryMenu');
        let isMenuOpen = false;

        categoryButton.addEventListener('click', function(e) {
            e.stopPropagation();
            isMenuOpen = !isMenuOpen;
            categoryMenu.classList.toggle('hidden');
        });

        // Cerrar el menú de categorías al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (isMenuOpen && !categoryMenu.contains(e.target)) {
                categoryMenu.classList.add('hidden');
                isMenuOpen = false;
            }
        });

        // Prevenir que el menú se cierre al hacer clic dentro
        categoryMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Script para el menú de usuario
        const userMenuButton = document.getElementById('userMenuButton');
        const userDropdown = document.getElementById('userDropdown');
        let isDropdownOpen = false;

        if (userMenuButton && userDropdown) {
            userMenuButton.addEventListener('click', function(e) {
                e.stopPropagation();
                isDropdownOpen = !isDropdownOpen;
                userDropdown.classList.toggle('active');
            });

            document.addEventListener('click', function(e) {
                if (isDropdownOpen && !userDropdown.contains(e.target)) {
                    isDropdownOpen = false;
                    userDropdown.classList.remove('active');
                }
            });

            userDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    });
  

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');
    let searchTimeout;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }

        // Esperar 300ms antes de hacer la búsqueda
            searchTimeout = setTimeout(() => {
            fetch('ajax/search_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'query=' + encodeURIComponent(query)
            })
            .then(response => response.json())
            .then(data => {
                searchResults.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'p-2 hover:bg-gray-100 cursor-pointer';
                        div.innerHTML = `
                            <div class="flex items-center space-x-3">
                                ${item.imagen_portada ? 
                                    `<img src="${item.imagen_portada}" class="w-10 h-14 object-cover">` :
                                    `<div class="w-10 h-14 bg-gray-200 flex items-center justify-center">
                                        <i class="fas fa-book text-gray-400"></i>
                                    </div>`
                                }
                                <div>
                                    <div class="font-medium">${item.titulo}</div>
                                    <div class="text-sm text-gray-600">${item.autor_nombre} ${item.autor_apellido}</div>
                                </div>
                            </div>
                        `;
                        
                        div.addEventListener('click', () => {
                            window.location.href = `vistas/libro/detalle.php?id=${item.id_libro}`;
                        });
                        
                        searchResults.appendChild(div);
                    });
                    searchResults.style.display = 'block';
                } else {
                    searchResults.innerHTML = '<div class="p-2 text-gray-500">No se encontraron resultados</div>';
                    searchResults.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }, 300);
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
});
</script>

</body>
</html>