<?php
// admin/prestamos/index.php
session_start();
require_once '../../config/config.php';

// Verificar si el usuario está logueado y tiene permisos
if (!isLoggedIn() || (!isAdmin() && !isBibliotecario())) {
    header('Location: ../../login.php');
    exit;
}

// Procesar devolución si se solicita
if (isset($_POST['devolver_id'])) {
    try {
        $stmt = $pdo->prepare("
            UPDATE prestamos 
            SET estado = 'Devuelto', 
                fecha_devolucion_real = CURRENT_DATE
            WHERE id_prestamo = ? AND estado = 'Prestado'
        ");
        if ($stmt->execute([$_POST['devolver_id']])) {
            $mensaje = "Libro devuelto exitosamente.";
        }
    } catch(PDOException $e) {
        error_log($e->getMessage());
        $error = "Error al procesar la devolución.";
    }
}

// Filtros
$estado = $_GET['estado'] ?? 'todos';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

// Construir la consulta base
$sql = "
    SELECT p.*, 
           l.titulo as libro_titulo,
           CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre,
           c.email as cliente_email,
           l.imagen_portada
    FROM prestamos p
    LEFT JOIN libros l ON p.id_libro = l.id_libro
    LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
    WHERE 1=1
";

// Aplicar filtros
$params = [];
if ($estado !== 'todos') {
    $sql .= " AND p.estado = ?";
    $params[] = $estado;
}
if ($fecha_inicio) {
    $sql .= " AND p.fecha_prestamo >= ?";
    $params[] = $fecha_inicio;
}
if ($fecha_fin) {
    $sql .= " AND p.fecha_prestamo <= ?";
    $params[] = $fecha_fin;
}

$sql .= " ORDER BY p.fecha_prestamo DESC";

// Obtener lista de préstamos
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $prestamos = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Error al cargar la lista de préstamos.";
}

$pageTitle = "Gestión de Préstamos - BiblioSis";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="icon" type="image/svg+xml" href="<?php echo $baseUrl; ?>favicon.svg">
    <link rel="icon" type="png" href="<?php echo $baseUrl; ?>favicon.png">
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
                <a href="../prestamos/index.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
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
                    <h1 class="text-2xl font-bold">Gestión de Préstamos</h1>
                    <div class="flex space-x-3">
                        <?php
                        // Obtener conteo de solicitudes pendientes
                        $stmt = $pdo->query("SELECT COUNT(*) FROM prestamos WHERE estado = 'Pendiente'");
                        $pendientes = $stmt->fetchColumn();
                        ?>
                        <a href="aprobar.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 flex items-center">
                            <i class="fas fa-tasks mr-2"></i>Aprobaciones
                            <?php if ($pendientes > 0): ?>
                                <span class="ml-2 bg-red-600 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center">
                                    <?php echo $pendientes; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        <a href="agregar.php" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            <i class="fas fa-plus mr-2"></i>Nuevo Préstamo
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

                <!-- Filtros -->
                <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                    <form method="GET" class="flex flex-wrap gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select name="estado" class="rounded border-gray-300 shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                <option value="todos" <?php echo $estado === 'todos' ? 'selected' : ''; ?>>Todos</option>
                                <option value="Pendiente" <?php echo $estado === 'Pendiente' ? 'selected' : ''; ?>>Pendientes</option>
                                <option value="Prestado" <?php echo $estado === 'Prestado' ? 'selected' : ''; ?>>Prestado</option>
                                <option value="Devuelto" <?php echo $estado === 'Devuelto' ? 'selected' : ''; ?>>Devuelto</option>
                                <option value="Rechazado" <?php echo $estado === 'Rechazado' ? 'selected' : ''; ?>>Rechazado</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>"
                                   class="rounded border-gray-300 shadow-sm focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                            <input type="date" name="fecha_fin" value="<?php echo $fecha_fin; ?>"
                                   class="rounded border-gray-300 shadow-sm focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <div>
                            <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                                <i class="fas fa-filter mr-2"></i>Filtrar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tabla de préstamos -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Libro
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Usuario
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha Préstamo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha Devolución
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($prestamos as $prestamo): ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <?php if ($prestamo['imagen_portada']): ?>
                                            <img src="../../<?php echo htmlspecialchars($prestamo['imagen_portada']); ?>" 
                                                 alt="Portada" 
                                                 class="h-12 w-9 object-cover mr-3">
                                        <?php else: ?>
                                            <div class="h-12 w-9 bg-gray-200 flex items-center justify-center mr-3">
                                                <i class="fas fa-book text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                        <span class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($prestamo['libro_titulo']); ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <div class="font-medium text-gray-900">
                                            <?php echo htmlspecialchars($prestamo['cliente_nombre']); ?>
                                        </div>
                                        <div class="text-gray-500">
                                            <?php echo htmlspecialchars($prestamo['cliente_email']); ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d/m/Y', strtotime($prestamo['fecha_prestamo'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php 
                                    if ($prestamo['fecha_devolucion_real']) {
                                        echo date('d/m/Y', strtotime($prestamo['fecha_devolucion_real']));
                                    } else {
                                        echo date('d/m/Y', strtotime($prestamo['fecha_devolucion_esperada']));
                                        $dias_restantes = (strtotime($prestamo['fecha_devolucion_esperada']) - time()) / (60 * 60 * 24);
                                        if ($dias_restantes < 0) {
                                            echo ' <span class="text-red-500">(Atrasado)</span>';
                                        } elseif ($dias_restantes <= 2) {
                                            echo ' <span class="text-yellow-500">(Próximo)</span>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full <?php 
                                        switch ($prestamo['estado']) {
                                            case 'Pendiente':
                                                echo 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'Prestado':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            case 'Devuelto':
                                                echo 'bg-blue-100 text-blue-800';
                                                break;
                                            case 'Rechazado':
                                                echo 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                echo 'bg-gray-100 text-gray-800';
                                        }
                                    ?>">
                                        <?php echo htmlspecialchars($prestamo['estado']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex space-x-3">
                                        <a href="ver.php?id=<?php echo $prestamo['id_prestamo']; ?>" 
                                           class="text-blue-500 hover:text-blue-700">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($prestamo['estado'] === 'Prestado'): ?>
                                        <form method="POST" class="inline" 
                                              onsubmit="return confirm('¿Confirmar la devolución del libro?');">
                                            <input type="hidden" name="devolver_id" value="<?php echo $prestamo['id_prestamo']; ?>">
                                            <button type="submit" class="text-green-500 hover:text-green-700">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                        <?php if ($prestamo['estado'] === 'Pendiente'): ?>
                                        <a href="aprobar.php?filtro=pendientes" 
                                           class="text-yellow-500 hover:text-yellow-700">
                                            <i class="fas fa-check-circle"></i>
                                        </a>
                                        <?php endif; ?>
                                        <!--<?php if (isAdmin()): ?>
                                        <a href="editar.php?id=<?php echo $prestamo['id_prestamo']; ?>" 
                                           class="text-yellow-500 hover:text-yellow-700">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?> -->
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
</body>
</html>