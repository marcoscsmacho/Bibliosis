<?php
// vistas/carrito/index.php
session_start();
$pageTitle = 'Carrito de Préstamos - BiblioSis';
require_once '../../modules/header.php';

// Verificar que el usuario esté logueado
if (!isLoggedIn()) {
    $_SESSION['error'] = "Debes iniciar sesión para ver tu carrito de préstamos.";
    header('Location: ' . $basePath . 'login.php');
    exit;
}

$id_cliente = $_SESSION['user_id'];
$error = null;
$mensaje = null;

// Procesar la eliminación de un item del carrito
if (isset($_POST['remove_item'])) {
    $id_carrito = (int)$_POST['remove_item'];
    
    try {
        $stmt = $pdo->prepare("
            DELETE FROM carrito_prestamos 
            WHERE id_carrito = ? AND id_cliente = ?
        ");
        $stmt->execute([$id_carrito, $id_cliente]);
        $mensaje = "Libro eliminado del carrito.";
    } catch (Exception $e) {
        $error = "Error al eliminar el libro del carrito.";
        error_log("Error al eliminar libro del carrito: " . $e->getMessage());
    }
}

// Procesar el vaciado del carrito
if (isset($_POST['vaciar_carrito'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM carrito_prestamos WHERE id_cliente = ?");
        $stmt->execute([$id_cliente]);
        $mensaje = "Carrito vaciado correctamente.";
    } catch (Exception $e) {
        $error = "Error al vaciar el carrito.";
        error_log("Error al vaciar el carrito: " . $e->getMessage());
    }
}

// Obtener los libros en el carrito
try {
    // Añadir logging para depuración
    error_log("Consultando carrito para usuario: " . $id_cliente);
    
    $stmt = $pdo->prepare("
        SELECT 
            cp.id_carrito, 
            cp.id_libro, 
            cp.fecha_agregado, 
            l.titulo AS libro_titulo, 
            l.imagen_portada AS libro_imagen, 
            l.estado AS libro_estado, 
            l.cantidad_disponible AS libro_disponible,
            a.nombre AS autor_nombre, 
            a.apellido AS autor_apellido,
            g.nombre AS genero_nombre
        FROM carrito_prestamos cp
        JOIN libros l ON cp.id_libro = l.id_libro
        JOIN autores a ON l.id_autor = a.id_autor
        JOIN generos g ON l.id_genero = g.id_genero
        WHERE cp.id_cliente = ?
        ORDER BY cp.fecha_agregado DESC
    ");
    $stmt->execute([$id_cliente]);
    $items_carrito = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Log para verificar qué se está recuperando
    error_log("Items en carrito recuperados: " . count($items_carrito));
    foreach ($items_carrito as $index => $item) {
        error_log("Item #$index - ID: " . $item['id_libro'] . ", Título: " . $item['libro_titulo']);
    }

    // Verificar disponibilidad actual de los libros
    foreach ($items_carrito as &$item) {
        // Si la disponibilidad cambió desde que se agregó al carrito
        if ($item['libro_disponible'] <= 0 || $item['libro_estado'] !== 'Disponible') {
            $item['disponible'] = false;
        } else {
            $item['disponible'] = true;
        }
    }
    
    // Para asegurarnos de que se liberan los recursos
    unset($item);

    // Obtener información de préstamos activos
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as prestamos_activos
        FROM prestamos 
        WHERE id_cliente = ? AND estado = 'Prestado'
    ");
    $stmt->execute([$id_cliente]);
    $prestamos_activos = $stmt->fetch()['prestamos_activos'];

    // Obtener configuración de préstamos máximos
    $stmt = $pdo->prepare("SELECT max_prestamos_usuario FROM configuracion WHERE id = 1");
    $stmt->execute();
    $max_prestamos = $stmt->fetchColumn() ?: 3;

    // Verificar si puede finalizar el préstamo
    $total_disponibles = 0;
    foreach ($items_carrito as $item) {
        if ($item['disponible']) {
            $total_disponibles++;
        }
    }

    $puede_finalizar = ($total_disponibles > 0) && 
                       ($prestamos_activos + $total_disponibles <= $max_prestamos);

} catch (Exception $e) {
    error_log("Error al cargar el carrito: " . $e->getMessage());
    $error = "Error al cargar el carrito: " . $e->getMessage();
}
?>

<!-- Banner -->
<div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white">
    <div class="max-w-7xl mx-auto px-4 py-12">
        <h1 class="text-4xl font-bold mb-4">Carrito de Préstamos</h1>
        <nav class="text-sm mb-6">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center">
                    <a href="<?php echo $basePath; ?>index.php" class="text-purple-200 hover:text-white">Inicio</a>
                    <span class="mx-2">/</span>
                </li>
                <li class="text-purple-100">Carrito</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Contenido principal -->
<div class="max-w-7xl mx-auto px-4 py-8">
    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($mensaje)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($items_carrito)): ?>
        <!-- Carrito vacío -->
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <div class="text-gray-400 text-6xl mb-4">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Tu carrito está vacío</h2>
            <p class="text-gray-600 mb-6">
                No tienes libros en tu carrito de préstamos.
            </p>
            <a href="<?php echo $basePath; ?>vistas/catalogo/index.php" 
               class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 inline-block">
                <i class="fas fa-book mr-2"></i>Explorar catálogo
            </a>
        </div>
    <?php else: ?>
        <!-- Carrito con items -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Lista de libros en el carrito -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-800">
                            Libros en tu carrito (<?php echo count($items_carrito); ?>)
                        </h2>
                        <form method="POST" onsubmit="return confirm('¿Estás seguro de vaciar tu carrito?')">
                            <button type="submit" name="vaciar_carrito" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-trash-alt mr-1"></i>Vaciar carrito
                            </button>
                        </form>
                    </div>

                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($items_carrito as $item): ?>
                            <li class="p-4">
                                <div class="flex items-start space-x-4">
                                    <!-- Imagen del libro -->
                                    <div class="flex-shrink-0 w-24">
    <?php if ($item['libro_imagen']): ?>
        <img src="<?php echo $basePath . $item['libro_imagen']; ?>" 
             alt="<?php echo htmlspecialchars($item['libro_titulo']); ?>"
             class="w-full h-32 object-cover object-center rounded">
    <?php else: ?>
        <div class="w-full h-32 bg-gray-200 flex items-center justify-center rounded">
            <i class="fas fa-book text-gray-400 text-4xl"></i>
        </div>
    <?php endif; ?>
</div>

                                    <!-- Información del libro -->
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-medium text-gray-900">
                                            <?php echo htmlspecialchars($item['libro_titulo']); ?>
                                        </h3>
                                        <p class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($item['autor_nombre'] . ' ' . $item['autor_apellido']); ?>
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <?php echo htmlspecialchars($item['genero_nombre']); ?>
                                        </p>
                                        
                                        <!-- Estado de disponibilidad -->
                                        <?php if (!$item['disponible']): ?>
                                            <p class="mt-2 text-red-600 text-sm">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                Este libro ya no está disponible para préstamo
                                            </p>
                                        <?php else: ?>
                                            <p class="mt-2 text-green-600 text-sm">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Disponible para préstamo
                                            </p>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Acciones -->
                                    <div class="flex-shrink-0 flex flex-col items-end space-y-2">
                                        <form method="POST">
                                            <input type="hidden" name="remove_item" value="<?php echo $item['id_carrito']; ?>">
                                            <button type="submit" class="text-red-500 hover:text-red-700">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                        
                                        <a href="<?php echo $basePath; ?>vistas/libro/detalle.php?id=<?php echo $item['id_libro']; ?>" 
                                           class="text-blue-500 hover:text-blue-700 text-sm">
                                            <i class="fas fa-info-circle mr-1"></i>Ver detalles
                                        </a>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Resumen y finalización -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Resumen</h2>
                    
                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total de libros:</span>
                            <span class="font-medium"><?php echo count($items_carrito); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Libros disponibles:</span>
                            <span class="font-medium"><?php echo $total_disponibles; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Préstamos activos:</span>
                            <span class="font-medium"><?php echo $prestamos_activos; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Máximo permitido:</span>
                            <span class="font-medium"><?php echo $max_prestamos; ?></span>
                        </div>
                        
                        <div class="border-t pt-4">
                            <div class="flex justify-between font-semibold">
                                <span>Disponibilidad:</span>
                                <?php if ($puede_finalizar): ?>
                                    <span class="text-green-600">
                                        <i class="fas fa-check-circle mr-1"></i>Disponible
                                    </span>
                                <?php else: ?>
                                    <span class="text-red-600">
                                        <i class="fas fa-times-circle mr-1"></i>No disponible
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($puede_finalizar): ?>
                        <a href="<?php echo $basePath; ?>vistas/carrito/finalizar.php" 
                           class="w-full bg-purple-600 text-white text-center px-4 py-2 rounded-lg hover:bg-purple-700 inline-block">
                            <i class="fas fa-check-circle mr-2"></i>Realizar préstamo
                        </a>
                    <?php else: ?>
                        <button disabled 
                                class="w-full bg-gray-400 text-white text-center px-4 py-2 rounded-lg cursor-not-allowed">
                            <i class="fas fa-ban mr-2"></i>Realizar préstamo
                        </button>
                        <?php if ($prestamos_activos + $total_disponibles > $max_prestamos): ?>
                            <p class="text-sm text-red-600 mt-2">
                                Has alcanzado el límite de préstamos permitidos (<?php echo $max_prestamos; ?>).
                            </p>
                        <?php elseif ($total_disponibles == 0): ?>
                            <p class="text-sm text-red-600 mt-2">
                                No hay libros disponibles en tu carrito.
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="mt-4">
                        <a href="<?php echo $basePath; ?>vistas/catalogo/index.php" 
                           class="text-purple-600 hover:text-purple-800 inline-flex items-center">
                            <i class="fas fa-arrow-left mr-2"></i>Seguir explorando
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../modules/footer.php'; ?>