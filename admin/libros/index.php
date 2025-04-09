<?php
//admin/libros/index.php
session_start();
require_once '../../config/config.php';

// Verificar si el usuario está logueado y tiene permisos
if (!isLoggedIn() || (!isAdmin() && !isBibliotecario())) {
    header('Location: ../../login.php');
    exit;
}

// Procesar eliminación si se solicita
if (isset($_POST['delete_id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM libros WHERE id_libro = ?");
        $stmt->execute([$_POST['delete_id']]);
        $mensaje = "Libro eliminado exitosamente.";
    } catch(PDOException $e) {
        error_log($e->getMessage());
        $error = "Error al eliminar el libro.";
    }
}

// Obtener lista de libros
try {
    $stmt = $pdo->query("
        SELECT l.*, a.nombre as autor_nombre, a.apellido as autor_apellido, 
               e.nombre as editorial_nombre, g.nombre as genero_nombre, 
               c.nombre as categoria_nombre
        FROM libros l
        LEFT JOIN autores a ON l.id_autor = a.id_autor
        LEFT JOIN editoriales e ON l.id_editorial = e.id_editorial
        LEFT JOIN generos g ON l.id_genero = g.id_genero
        LEFT JOIN categorias c ON l.id_categoria = c.id_categoria
        ORDER BY l.fecha_registro DESC
    ");
    $libros = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Error al cargar la lista de libros.";
}

$pageTitle = "Gestión de Libros - BiblioSis";
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
                <a href="../libros/index.php" class="flex items-center px-6 py-3 bg-gray-700 text-white">
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
                <a href="../reportes/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
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
        <div class="flex-1">
            <div class="p-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-bold">Gestión de Libros</h1>
                    <div class="flex space-x-4">
                        <!-- Campo de búsqueda -->
                        <div class="relative">
                            <input type="text" 
                                id="busqueda-libros" 
                                placeholder="Buscar libros..." 
                                class="border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-600 w-64">
                            <button id="btn-buscar" class="absolute right-3 top-2 text-gray-400">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        
                        <a href="agregar.php" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            <i class="fas fa-plus mr-2"></i>Agregar Libro
                        </a>
                    </div>
                </div>

                <?php if (isset($mensaje)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    <?php echo $mensaje; ?>
                </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <!-- Tabla de libros -->
                <div id="resultados-container">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Título
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Autor
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Editorial
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Género
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Categoría
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tabla-libros" class="bg-white divide-y divide-gray-200">
                                <?php foreach ($libros as $libro): ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <?php echo htmlspecialchars($libro['titulo']); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php echo htmlspecialchars($libro['autor_nombre'] . ' ' . $libro['autor_apellido']); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php echo htmlspecialchars($libro['editorial_nombre']); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php echo htmlspecialchars($libro['genero_nombre']); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php  if ($libro['categoria_nombre']) {
                                    echo htmlspecialchars($libro['categoria_nombre']);
                                        } else {
                                        echo '<span class="text-gray-400">Sin categoría</span>';
                                        }      
                                            ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs rounded-full <?php 
                                            echo $libro['estado'] === 'Disponible' ? 'bg-green-100 text-green-800' : 
                                                ($libro['estado'] === 'Prestado' ? 'bg-yellow-100 text-yellow-800' : 
                                                'bg-red-100 text-red-800'); ?>">
                                            <?php echo htmlspecialchars($libro['estado']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        
                                    <div class="flex space-x-3">
                                            <a href="editar.php?id=<?php echo $libro['id_libro']; ?>" 
                                               class="text-yellow-500 hover:text-yellow-700">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" class="inline" 
                                                  onsubmit="return confirm('¿Estás seguro de que deseas eliminar este libro?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $libro['id_libro']; ?>">
                                                <button type="submit" class="text-red-500 hover:text-red-700">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script para buscador asíncrono -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const busquedaInput = document.getElementById('busqueda-libros');
        const tablaLibros = document.getElementById('tabla-libros');
        const btnBuscar = document.getElementById('btn-buscar');
        
        // Variable para manejar el tiempo de espera para las búsquedas
        let timeoutId;
        
        // Función para cargar los libros según el término de búsqueda
        function buscarLibros() {
            const terminoBusqueda = busquedaInput.value.trim();
            
            // Limpiar el timeout anterior si existe
            clearTimeout(timeoutId);
            
            // Establecer un nuevo timeout para evitar múltiples solicitudes
            timeoutId = setTimeout(() => {
                // Mostrar indicador de carga
                tablaLibros.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center">
                            <i class="fas fa-spinner fa-spin text-purple-600"></i> Buscando...
                        </td>
                    </tr>
                `;
                
                // Realizar la solicitud AJAX
                fetch(`buscar_libros.php?q=${encodeURIComponent(terminoBusqueda)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la solicitud');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            tablaLibros.innerHTML = `
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-red-500">
                                        ${data.error}
                                    </td>
                                </tr>
                            `;
                            return;
                        }
                        
                        // Si no hay resultados
                        if (data.length === 0) {
                            tablaLibros.innerHTML = `
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center">
                                        No se encontraron libros que coincidan con la búsqueda.
                                    </td>
                                </tr>
                            `;
                            return;
                        }
                        
                        // Construir las filas de la tabla con los resultados
                        let html = '';
                        data.forEach(libro => {
                            html += `
                                <tr>
                                    <td class="px-6 py-4">
                                        ${libro.titulo}
                                    </td>
                                    <td class="px-6 py-4">
                                        ${libro.autor_nombre} ${libro.autor_apellido}
                                    </td>
                                    <td class="px-6 py-4">
                                        ${libro.editorial_nombre || ''}
                                    </td>
                                    <td class="px-6 py-4">
                                        ${libro.genero_nombre || ''}
                                    </td>
                                    <td class="px-6 py-4">
                                        ${libro.categoria_nombre ? libro.categoria_nombre : '<span class="text-gray-400">Sin categoría</span>'}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            ${libro.estado === 'Disponible' ? 'bg-green-100 text-green-800' : 
                                              libro.estado === 'Prestado' ? 'bg-yellow-100 text-yellow-800' : 
                                              'bg-red-100 text-red-800'}">
                                            ${libro.estado}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-3">
                                            <a href="editar.php?id=${libro.id_libro}" 
                                               class="text-yellow-500 hover:text-yellow-700">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" class="inline" 
                                                  onsubmit="return confirm('¿Estás seguro de que deseas eliminar este libro?');">
                                                <input type="hidden" name="delete_id" value="${libro.id_libro}">
                                                <button type="submit" class="text-red-500 hover:text-red-700">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                        
                        tablaLibros.innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        tablaLibros.innerHTML = `
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-red-500">
                                    Error al cargar los resultados. Por favor, intenta de nuevo.
                                </td>
                            </tr>
                        `;
                    });
            }, 300); // Esperar 300ms después de que el usuario deje de escribir
        }
        
        // Eventos para activar la búsqueda
        busquedaInput.addEventListener('input', buscarLibros);
        btnBuscar.addEventListener('click', buscarLibros);
        
        // También buscar al presionar Enter
        busquedaInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarLibros();
            }
        });
    });
    </script>
</body>
</html>