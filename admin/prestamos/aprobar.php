<?php
//admin/prestamos/aprobar.php
session_start();
require_once '../../config/config.php';

// Verificar permisos (solo administradores y bibliotecarios)
if (!isLoggedIn() || (!isAdmin() && !isBibliotecario())) {
    header('Location: ../../login.php');
    exit;
}

// Obtener parámetros de filtrado
$filtro = isset($_GET['filtro']) ? cleanInput($_GET['filtro']) : 'pendientes';

// Procesar la aprobación o rechazo de una solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['aprobar_id'])) {
        $id_prestamo = (int)$_POST['aprobar_id'];
        $comentario = cleanInput($_POST['comentario'] ?? '');
        
        try {
            $pdo->beginTransaction();
            
            // Actualizar el estado del préstamo
            $stmt = $pdo->prepare("
                UPDATE prestamos 
                SET estado = 'Prestado', 
                    comentario_revision = ?,
                    id_usuario_aprobacion = ?
                WHERE id_prestamo = ? AND estado = 'Pendiente'
            ");
            $stmt->execute([$comentario, $_SESSION['user_id'], $id_prestamo]);
            
            // Verificar si se actualizó correctamente
            if ($stmt->rowCount() == 0) {
                throw new Exception("No se pudo aprobar la solicitud. Puede que ya haya sido procesada.");
            }
            
            // Actualizar la disponibilidad del libro (reducir en 1)
            $stmt = $pdo->prepare("
                UPDATE libros l 
                JOIN prestamos p ON l.id_libro = p.id_libro
                SET l.cantidad_disponible = l.cantidad_disponible - 1,
                    l.estado = CASE 
                        WHEN l.cantidad_disponible - 1 = 0 THEN 'Prestado'
                        ELSE l.estado 
                    END
                WHERE p.id_prestamo = ?
            ");
            $stmt->execute([$id_prestamo]);
            
            $pdo->commit();
            $mensaje = "Préstamo aprobado exitosamente.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    } elseif (isset($_POST['rechazar_id'])) {
        $id_prestamo = (int)$_POST['rechazar_id'];
        $comentario = cleanInput($_POST['comentario'] ?? '');
        
        try {
            // Actualizar el estado del préstamo a Rechazado
            $stmt = $pdo->prepare("
                UPDATE prestamos 
                SET estado = 'Rechazado', 
                    comentario_revision = ?,
                    id_usuario_aprobacion = ?
                WHERE id_prestamo = ? AND estado = 'Pendiente'
            ");
            $stmt->execute([$comentario, $_SESSION['user_id'], $id_prestamo]);
            
            // Verificar si se actualizó correctamente
            if ($stmt->rowCount() == 0) {
                throw new Exception("No se pudo rechazar la solicitud. Puede que ya haya sido procesada.");
            }
            
            $mensaje = "Solicitud rechazada correctamente.";
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

try {
    // Construir la consulta según el filtro
    $sql = "
        SELECT p.*, 
               l.titulo as libro_titulo, l.imagen_portada,
               c.nombre as cliente_nombre, c.apellido as cliente_apellido,
               c.email as cliente_email, c.telefono as cliente_telefono,
               u1.nombre as usuario_solicitud_nombre, u1.apellido as usuario_solicitud_apellido
        FROM prestamos p
        JOIN libros l ON p.id_libro = l.id_libro
        JOIN clientes c ON p.id_cliente = c.id_cliente
        LEFT JOIN usuarios u1 ON p.id_usuario = u1.id_usuario
    ";

    $params = [];

    switch ($filtro) {
        case 'pendientes':
            $sql .= " WHERE p.estado = 'Pendiente'";
            break;
        case 'aprobados':
            $sql .= " WHERE p.estado = 'Prestado'";
            break;
        case 'rechazados':
            $sql .= " WHERE p.estado = 'Rechazado'";
            break;
        case 'todos':
            // No se aplica filtro
            break;
        default:
            $sql .= " WHERE p.estado = 'Pendiente'";
    }

    $sql .= " ORDER BY p.fecha_prestamo DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $solicitudes = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Error en aprobar.php: " . $e->getMessage());
    $error = "Error al cargar las solicitudes de préstamo: " . $e->getMessage();
}

$pageTitle = "Aprobación de Préstamos - BiblioSis";
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
                    <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                </a>
                <a href="../Autores/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-feather mr-3"></i>Autores
                </a>
                <a href="../libros/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-book mr-3"></i>Libros
                </a>
                <a href="index.php" class="flex items-center px-6 py-3 bg-gray-700 text-white">
                    <i class="fas fa-handshake mr-3"></i>Préstamos
                </a>
                <a href="../usuarios/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-users mr-3"></i>Usuarios
                </a>
                <?php if (isAdmin()): ?>
                <a href="../reportes/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-chart-bar mr-3"></i>Reportes
                </a>
                <a href="../bibliotecarios/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-users-cog mr-3"></i>Bibliotecarios
                </a>
                <a href="../configuracion/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-cog mr-3"></i>Configuración
                </a>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Contenido principal -->
        <div class="flex-1 p-8">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-bold">Aprobación de Préstamos</h1>
                    <a href="index.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        <i class="fas fa-arrow-left mr-2"></i>Volver
                    </a>
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

                <!-- Filtros -->
                <div class="bg-white rounded-lg shadow p-4 mb-6">
                    <form method="GET" class="flex flex-wrap gap-4 items-center">
                        <div>
                            <select name="filtro" class="border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                <option value="pendientes" <?php echo $filtro === 'pendientes' ? 'selected' : ''; ?>>Pendientes</option>
                                <option value="aprobados" <?php echo $filtro === 'aprobados' ? 'selected' : ''; ?>>Aprobados</option>
                                <option value="rechazados" <?php echo $filtro === 'rechazados' ? 'selected' : ''; ?>>Rechazados</option>
                                <option value="todos" <?php echo $filtro === 'todos' ? 'selected' : ''; ?>>Todos</option>
                            </select>
                        </div>
                        <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700">
                            <i class="fas fa-filter mr-2"></i>Filtrar
                        </button>
                    </form>
                </div>

                <!-- Lista de solicitudes -->
                <?php if (empty($solicitudes)): ?>
                    <div class="bg-white p-8 rounded-lg shadow text-center">
                        <div class="text-6xl text-gray-300 mb-4">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <h2 class="text-2xl font-semibold text-gray-700 mb-2">No hay solicitudes</h2>
                        <p class="text-gray-600">
                            No se encontraron solicitudes de préstamo que coincidan con el filtro actual.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Usuario
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Libro
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fecha
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($solicitudes as $solicitud): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($solicitud['cliente_nombre'] . ' ' . $solicitud['cliente_apellido']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($solicitud['cliente_email']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    Tel: <?php echo htmlspecialchars($solicitud['cliente_telefono']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <?php if ($solicitud['imagen_portada']): ?>
                                                <img src="../../<?php echo htmlspecialchars($solicitud['imagen_portada']); ?>" 
                                                     alt="Portada" 
                                                     class="h-12 w-10 object-cover mr-3">
                                            <?php else: ?>
                                                <div class="h-12 w-10 bg-gray-200 flex items-center justify-center mr-3">
                                                    <i class="fas fa-book text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($solicitud['libro_titulo']); ?>
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    ID: <?php echo $solicitud['id_libro']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php echo date('d/m/Y', strtotime($solicitud['fecha_prestamo'])); ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Devolución: <?php echo date('d/m/Y', strtotime($solicitud['fecha_devolucion_esperada'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php 
                                            switch ($solicitud['estado']) {
                                                case 'Pendiente':
                                                    echo 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 'Prestado':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'Rechazado':
                                                    echo 'bg-red-100 text-red-800';
                                                    break;
                                                default:
                                                    echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <?php echo htmlspecialchars($solicitud['estado']); ?>
                                        </span>
                                        <?php if (isset($solicitud['id_usuario_aprobacion'])): ?>
                                        <div class="text-xs text-gray-500 mt-1">
                                            Por: <?php echo isset($solicitud['usuario_aprobacion_nombre']) ? 
                                                htmlspecialchars($solicitud['usuario_aprobacion_nombre'] . ' ' . 
                                                $solicitud['usuario_aprobacion_apellido']) : 'N/A'; ?>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <?php if ($solicitud['estado'] === 'Pendiente'): ?>
                                            <div class="flex space-x-2">
                                                <button onclick="mostrarModal('aprobar', <?php echo $solicitud['id_prestamo']; ?>)" 
                                                        class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
                                                    <i class="fas fa-check"></i> Aprobar
                                                </button>
                                                <button onclick="mostrarModal('rechazar', <?php echo $solicitud['id_prestamo']; ?>)" 
                                                        class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                                    <i class="fas fa-times"></i> Rechazar
                                                </button>
                                            </div>
                                        <?php elseif ($solicitud['estado'] === 'Prestado' || $solicitud['estado'] === 'Rechazado'): ?>
                                            <div class="text-sm">
                                                <?php if (!empty($solicitud['comentario_revision'])): ?>
                                                    <span class="text-gray-700">Comentario:</span>
                                                    <p class="text-gray-500 italic">"<?php echo htmlspecialchars($solicitud['comentario_revision']); ?>"</p>
                                                <?php else: ?>
                                                    <span class="text-gray-500">Sin comentarios</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal de Aprobación/Rechazo -->
    <div id="modalAccion" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900" id="modalTitulo">Aprobar Solicitud</h3>
            </div>
            <form method="POST">
                <input type="hidden" id="idAccion" name="">
                <div class="px-6 py-4">
                    <div class="mb-4">
                        <label for="comentario" class="block text-sm font-medium text-gray-700 mb-2">
                            Comentario (opcional)
                        </label>
                        <textarea id="comentario" name="comentario" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500"
                                  placeholder="Ingrese un comentario para el usuario..."></textarea>
                    </div>
                    <p class="text-gray-600 text-sm">
                        <i class="fas fa-info-circle mr-1"></i>
                        <span id="modalMensaje">Al aprobar esta solicitud, el libro pasará a estado "Prestado" y se descontará del inventario disponible.</span>
                    </p>
                </div>
                <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3 rounded-b-lg">
                    <button type="button" onclick="cerrarModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" id="btnAccion" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700">
                        Aprobar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function mostrarModal(accion, id) {
            const modal = document.getElementById('modalAccion');
            const titulo = document.getElementById('modalTitulo');
            const mensaje = document.getElementById('modalMensaje');
            const btnAccion = document.getElementById('btnAccion');
            const idAccion = document.getElementById('idAccion');
            
            if (accion === 'aprobar') {
                titulo.textContent = 'Aprobar Solicitud';
                mensaje.textContent = 'Al aprobar esta solicitud, el libro pasará a estado "Prestado" y se descontará del inventario disponible.';
                btnAccion.textContent = 'Aprobar';
                btnAccion.className = 'px-4 py-2 border border-transparent rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700';
                idAccion.name = 'aprobar_id';
            } else {
                titulo.textContent = 'Rechazar Solicitud';
                mensaje.textContent = 'Al rechazar esta solicitud, el usuario será notificado y el libro permanecerá en inventario.';
                btnAccion.textContent = 'Rechazar';
                btnAccion.className = 'px-4 py-2 border border-transparent rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700';
                idAccion.name = 'rechazar_id';
            }
            
            idAccion.value = id;
            modal.classList.remove('hidden');
        }
        
        function cerrarModal() {
            document.getElementById('modalAccion').classList.add('hidden');
        }
    </script>
</body>
</html>