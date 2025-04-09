<?php
//admin/usuarios/index.php
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
        // Verificar si el usuario tiene préstamos activos
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM prestamos WHERE id_cliente = ? AND estado = 'Prestado'");
        $stmt->execute([$_POST['delete_id']]);
        $prestamosActivos = $stmt->fetchColumn();

        if ($prestamosActivos > 0) {
            $error = "No se puede eliminar el usuario porque tiene préstamos activos.";
        } else {
            // Obtener la imagen antes de eliminar
            $stmt = $pdo->prepare("SELECT imagen_cliente FROM clientes WHERE id_cliente = ?");
            $stmt->execute([$_POST['delete_id']]);
            $usuario = $stmt->fetch();
            
            // Eliminar la imagen si existe
            if ($usuario && $usuario['imagen_cliente'] && file_exists('../../' . $usuario['imagen_cliente'])) {
                unlink('../../' . $usuario['imagen_cliente']);
            }
            
            $stmt = $pdo->prepare("DELETE FROM clientes WHERE id_cliente = ?");
            $stmt->execute([$_POST['delete_id']]);
            $mensaje = "Usuario eliminado exitosamente.";
        }
    } catch(PDOException $e) {
        error_log($e->getMessage());
        $error = "Error al eliminar el usuario.";
    }
}

// Obtener lista de usuarios
try {
    $stmt = $pdo->query("
        SELECT c.*, 
               (SELECT COUNT(*) FROM prestamos p WHERE p.id_cliente = c.id_cliente AND p.estado = 'Prestado') 
               as prestamos_activos
        FROM clientes c
        ORDER BY c.fecha_registro DESC
    ");
    $usuarios = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Error al cargar la lista de usuarios.";
}

$pageTitle = "Gestión de Usuarios - BiblioTech";
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
                <a href="../libros/index.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-book mr-3"></i>
                    Libros
                </a>
                <a href="../prestamos/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-handshake mr-3"></i>
                    Préstamos
                </a>
                <a href="../usuarios/" class="flex items-center px-6 py-3 bg-gray-700 text-white">
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
                    <h1 class="text-2xl font-bold">Gestión de Usuarios</h1>
                    <div class="flex space-x-4">
                        <!-- Campo de búsqueda -->
                        <div class="relative">
                            <input type="text" 
                                id="busqueda-usuarios" 
                                placeholder="Buscar usuarios..." 
                                class="border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-600 w-64">
                            <button id="btn-buscar" class="absolute right-3 top-2 text-gray-400">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        
                        <a href="agregar.php" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            <i class="fas fa-plus mr-2"></i>Agregar Usuario
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

                <!-- Tabla de usuarios -->
                <div id="resultados-container">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Foto
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nombre
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Teléfono
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Préstamos Activos
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tabla-usuarios" class="bg-white divide-y divide-gray-200">
                                <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($usuario['imagen_cliente']): ?>
                                            <img src="../../<?php echo htmlspecialchars($usuario['imagen_cliente']); ?>" 
                                                 alt="Foto de <?php echo htmlspecialchars($usuario['nombre']); ?>"
                                                 class="h-12 w-12 rounded-full object-cover">
                                        <?php else: ?>
                                            <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center">
                                                <i class="fas fa-user text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($usuario['email']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($usuario['telefono']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full <?php 
                                            echo $usuario['estado'] === 'Activo' ? 'bg-green-100 text-green-800' : 
                                                'bg-red-100 text-red-800'; ?>">
                                            <?php echo htmlspecialchars($usuario['estado']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <?php echo $usuario['prestamos_activos']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex space-x-3">
                                            <a href="editar.php?id=<?php echo $usuario['id_cliente']; ?>" 
                                               class="text-yellow-500 hover:text-yellow-700">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($usuario['prestamos_activos'] == 0): ?>
                                            <form method="POST" class="inline" 
                                                  onsubmit="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $usuario['id_cliente']; ?>">
                                                <button type="submit" class="text-red-500 hover:text-red-700">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
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
        const busquedaInput = document.getElementById('busqueda-usuarios');
        const tablaUsuarios = document.getElementById('tabla-usuarios');
        const btnBuscar = document.getElementById('btn-buscar');
        
        // Variable para manejar el tiempo de espera para las búsquedas
        let timeoutId;
        
        // Función para cargar los usuarios según el término de búsqueda
        function buscarUsuarios() {
            const terminoBusqueda = busquedaInput.value.trim();
            
            // Limpiar el timeout anterior si existe
            clearTimeout(timeoutId);
            
            // Establecer un nuevo timeout para evitar múltiples solicitudes
            timeoutId = setTimeout(() => {
                // Mostrar indicador de carga
                tablaUsuarios.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center">
                            <i class="fas fa-spinner fa-spin text-purple-600"></i> Buscando...
                        </td>
                    </tr>
                `;
                
                // Realizar la solicitud AJAX
                fetch(`buscar_usuarios.php?q=${encodeURIComponent(terminoBusqueda)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la solicitud');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            tablaUsuarios.innerHTML = `
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
                            tablaUsuarios.innerHTML = `
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center">
                                        No se encontraron usuarios que coincidan con la búsqueda.
                                    </td>
                                </tr>
                            `;
                            return;
                        }
                        
                        // Construir las filas de la tabla con los resultados
                        let html = '';
                        data.forEach(usuario => {
                            html += `
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        ${usuario.imagen_cliente 
                                            ? `<img src="../../${usuario.imagen_cliente}" 
                                                  alt="Foto de ${usuario.nombre}" 
                                                  class="h-12 w-12 rounded-full object-cover">`
                                            : `<div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center">
                                                <i class="fas fa-user text-gray-400"></i>
                                              </div>`
                                        }
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        ${usuario.nombre} ${usuario.apellido}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        ${usuario.email}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        ${usuario.telefono || ''}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            ${usuario.estado === 'Activo' 
                                                ? 'bg-green-100 text-green-800' 
                                                : 'bg-red-100 text-red-800'}">
                                            ${usuario.estado}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        ${usuario.prestamos_activos}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex space-x-3">
                                            <a href="editar.php?id=${usuario.id_cliente}" 
                                               class="text-yellow-500 hover:text-yellow-700">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            ${parseInt(usuario.prestamos_activos) === 0 
                                                ? `<form method="POST" class="inline" 
                                                      onsubmit="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">
                                                    <input type="hidden" name="delete_id" value="${usuario.id_cliente}">
                                                    <button type="submit" class="text-red-500 hover:text-red-700">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>`
                                                : ''
                                            }
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                        
                        tablaUsuarios.innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        tablaUsuarios.innerHTML = `
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
        busquedaInput.addEventListener('input', buscarUsuarios);
        btnBuscar.addEventListener('click', buscarUsuarios);
        
        // También buscar al presionar Enter
        busquedaInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarUsuarios();
            }
        });
    });
    </script>
</body>
</html>