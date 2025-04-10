<?php
//admin/autores/index.php
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
        // Verificar si el autor tiene libros asociados
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM libros WHERE id_autor = ?");
        $stmt->execute([$_POST['delete_id']]);
        $librosAsociados = $stmt->fetchColumn();

        if ($librosAsociados > 0) {
            $error = "No se puede eliminar el autor porque tiene libros asociados.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM autores WHERE id_autor = ?");
            $stmt->execute([$_POST['delete_id']]);
            $mensaje = "Autor eliminado exitosamente.";
        }
    } catch(PDOException $e) {
        error_log($e->getMessage());
        $error = "Error al eliminar el autor.";
    }
}

// Obtener lista de autores
try {
    $stmt = $pdo->query("
        SELECT a.*, 
               (SELECT COUNT(*) FROM libros l WHERE l.id_autor = a.id_autor) as total_libros
        FROM autores a
        ORDER BY a.apellido ASC
    ");
    $autores = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Error al cargar la lista de autores.";
}

$pageTitle = "Gestión de Autores - BiblioTech";
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
                <a href="index.php" class="flex items-center px-6 py-3 bg-gray-700 text-white">
                    <i class="fas fa-feather mr-3"></i>
                    Autores
                </a>
                <a href="../libros/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
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
                <a href="../bibliotecarios/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-users-cog mr-3"></i>
                    Bibliotecarios
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
                    <h1 class="text-2xl font-bold">Gestión de Autores</h1>
                    <div class="flex space-x-4">
                        <!-- Campo de búsqueda -->
                        <div class="relative">
                            <input type="text" 
                                id="busqueda-autores" 
                                placeholder="Buscar autores..." 
                                class="border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-600 w-64">
                            <button id="btn-buscar" class="absolute right-3 top-2 text-gray-400">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        
                        <a href="agregar.php" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            <i class="fas fa-plus mr-2"></i>Agregar Autor
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

                <!-- Tabla de autores -->
                <div id="resultados-container">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nombre
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nacionalidad
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fecha Nacimiento
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Libros Publicados
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tabla-autores" class="bg-white divide-y divide-gray-200">
                                <?php foreach ($autores as $autor): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($autor['nombre'] . ' ' . $autor['apellido']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($autor['nacionalidad']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo $autor['fecha_nacimiento'] ? date('d/m/Y', strtotime($autor['fecha_nacimiento'])) : 'No disponible'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2 py-1 text-sm rounded-full <?php echo $autor['total_libros'] > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'; ?>">
                                            <?php echo $autor['total_libros']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex space-x-3">
                                            <a href="ver.php?id=<?php echo $autor['id_autor']; ?>" 
                                               class="text-blue-500 hover:text-blue-700" 
                                               title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="editar.php?id=<?php echo $autor['id_autor']; ?>" 
                                               class="text-yellow-500 hover:text-yellow-700"
                                               title="Editar autor">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($autor['total_libros'] == 0): ?>
                                            <form method="POST" class="inline" 
                                                  onsubmit="return confirm('¿Estás seguro de que deseas eliminar este autor?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $autor['id_autor']; ?>">
                                                <button type="submit" class="text-red-500 hover:text-red-700" title="Eliminar autor">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($autores)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        No hay autores registrados
                                    </td>
                                </tr>
                                <?php endif; ?>
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
        const busquedaInput = document.getElementById('busqueda-autores');
        const tablaAutores = document.getElementById('tabla-autores');
        const btnBuscar = document.getElementById('btn-buscar');
        
        // Variable para manejar el tiempo de espera para las búsquedas
        let timeoutId;
        
        // Función para cargar los autores según el término de búsqueda
        function buscarAutores() {
            const terminoBusqueda = busquedaInput.value.trim();
            
            // Limpiar el timeout anterior si existe
            clearTimeout(timeoutId);
            
            // Establecer un nuevo timeout para evitar múltiples solicitudes
            timeoutId = setTimeout(() => {
                // Mostrar indicador de carga
                tablaAutores.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center">
                            <i class="fas fa-spinner fa-spin text-purple-600"></i> Buscando...
                        </td>
                    </tr>
                `;
                
                // Realizar la solicitud AJAX
                fetch(`buscar_autores.php?q=${encodeURIComponent(terminoBusqueda)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la solicitud');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            tablaAutores.innerHTML = `
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-red-500">
                                        ${data.error}
                                    </td>
                                </tr>
                            `;
                            return;
                        }
                        
                        // Si no hay resultados
                        if (data.length === 0) {
                            tablaAutores.innerHTML = `
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center">
                                        No se encontraron autores que coincidan con la búsqueda.
                                    </td>
                                </tr>
                            `;
                            return;
                        }
                        
                        // Construir las filas de la tabla con los resultados
                        let html = '';
                        data.forEach(autor => {
                            let fechaNacimiento = autor.fecha_nacimiento 
                                ? new Date(autor.fecha_nacimiento).toLocaleDateString('es-ES')
                                : 'No disponible';
                                
                            html += `
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        ${autor.nombre} ${autor.apellido}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        ${autor.nacionalidad || ''}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        ${fechaNacimiento}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2 py-1 text-sm rounded-full ${autor.total_libros > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'}">
                                            ${autor.total_libros}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex space-x-3">
                                            <a href="ver.php?id=${autor.id_autor}" 
                                               class="text-blue-500 hover:text-blue-700" 
                                               title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="editar.php?id=${autor.id_autor}" 
                                               class="text-yellow-500 hover:text-yellow-700"
                                               title="Editar autor">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            ${autor.total_libros == 0 ? 
                                                `<form method="POST" class="inline" 
                                                      onsubmit="return confirm('¿Estás seguro de que deseas eliminar este autor?');">
                                                    <input type="hidden" name="delete_id" value="${autor.id_autor}">
                                                    <button type="submit" class="text-red-500 hover:text-red-700" title="Eliminar autor">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>` : ''}
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                        
                        tablaAutores.innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        tablaAutores.innerHTML = `
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-red-500">
                                    Error al cargar los resultados. Por favor, intenta de nuevo.
                                </td>
                            </tr>
                        `;
                    });
            }, 300); // Esperar 300ms después de que el usuario deje de escribir
        }
        
        // Eventos para activar la búsqueda
        busquedaInput.addEventListener('input', buscarAutores);
        btnBuscar.addEventListener('click', buscarAutores);
        
        // También buscar al presionar Enter
        busquedaInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarAutores();
            }
        });
    });
    </script>
</body>
</html>