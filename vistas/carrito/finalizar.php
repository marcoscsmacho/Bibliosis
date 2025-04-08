<?php
// vistas/carrito/finalizar.php
session_start();
$pageTitle = 'Finalizar Solicitud - BiblioSis';
require_once '../../modules/header.php';

// Verificar que el usuario esté logueado
if (!isLoggedIn()) {
    $_SESSION['error'] = "Debes iniciar sesión para finalizar la solicitud.";
    header('Location: ' . $basePath . 'login.php');
    exit;
}

$id_cliente = $_SESSION['user_id'];
$error = null;
$mensaje = null;

try {
    // Obtener configuración de préstamos
    $stmt = $pdo->prepare("
        SELECT dias_prestamo, max_prestamos_usuario 
        FROM configuracion 
        WHERE id = 1
    ");
    $stmt->execute();
    $config = $stmt->fetch();
    $dias_prestamo_default = $config['dias_prestamo'] ?: 7;
    $max_prestamos = $config['max_prestamos_usuario'] ?: 3;

    // Verificar préstamos activos y pendientes
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as prestamos_activos
        FROM prestamos 
        WHERE id_cliente = ? AND estado IN ('Pendiente', 'Aprobado', 'Prestado')
    ");
    $stmt->execute([$id_cliente]);
    $prestamos_activos = $stmt->fetch()['prestamos_activos'];

    // Obtener libros en el carrito
    $stmt = $pdo->prepare("
        SELECT cp.id_carrito, cp.id_libro, l.titulo, l.imagen_portada, 
               l.cantidad_disponible, l.estado,
               a.nombre as autor_nombre, a.apellido as autor_apellido
        FROM carrito_prestamos cp
        JOIN libros l ON cp.id_libro = l.id_libro
        JOIN autores a ON l.id_autor = a.id_autor
        WHERE cp.id_cliente = ?
    ");
    // Debug - Registrar la consulta
    error_log("Consultando carrito para finalizar - id_cliente: $id_cliente");
    $stmt->execute([$id_cliente]);
    $items_carrito = $stmt->fetchAll();

    // Verificar si hay libros en el carrito
    if (empty($items_carrito)) {
        throw new Exception("No hay libros en tu carrito.");
    }

    // Verificar disponibilidad actual de cada libro
    $libros_disponibles = [];
    foreach ($items_carrito as $item) {
        if ($item['cantidad_disponible'] > 0 && $item['estado'] === 'Disponible') {
            $libros_disponibles[] = $item;
        }
    }

    // Verificar si hay libros disponibles
    if (empty($libros_disponibles)) {
        throw new Exception("No hay libros disponibles en tu carrito.");
    }

    // Verificar límite de préstamos
    if (count($libros_disponibles) + $prestamos_activos > $max_prestamos) {
        throw new Exception("Excedes el límite de solicitudes/préstamos permitidos. Máximo: $max_prestamos.");
    }

    // Procesar el formulario de finalización
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $dias_prestamo = isset($_POST['dias_prestamo']) ? (int)$_POST['dias_prestamo'] : $dias_prestamo_default;
        
        try {
            $pdo->beginTransaction();

            // Crear las solicitudes para cada libro disponible
            foreach ($libros_disponibles as $libro) {
                // Verificar que el libro siga disponible (podría haber cambiado mientras el usuario estaba en esta página)
                $stmt = $pdo->prepare("
                    SELECT cantidad_disponible, estado 
                    FROM libros 
                    WHERE id_libro = ? FOR UPDATE
                ");
                $stmt->execute([$libro['id_libro']]);
                $libro_actual = $stmt->fetch();

                if ($libro_actual['cantidad_disponible'] <= 0 || $libro_actual['estado'] !== 'Disponible') {
                    throw new Exception("El libro '{$libro['titulo']}' ya no está disponible.");
                }

                // Registrar la solicitud de préstamo como PENDIENTE
                $stmt = $pdo->prepare("
                    INSERT INTO prestamos (
                        id_libro, id_cliente, fecha_devolucion_esperada, 
                        estado, observaciones
                    ) VALUES (
                        ?, ?, DATE_ADD(CURRENT_DATE, INTERVAL ? DAY), 
                        'Pendiente', 'Solicitud realizada desde carrito, pendiente de aprobación'
                    )
                ");
                $stmt->execute([
                    $libro['id_libro'],
                    $id_cliente,
                    $dias_prestamo
                ]);

                // Eliminar el libro del carrito
                $stmt = $pdo->prepare("
                    DELETE FROM carrito_prestamos 
                    WHERE id_carrito = ? AND id_cliente = ?
                ");
                $stmt->execute([$libro['id_carrito'], $id_cliente]);
            }

            $pdo->commit();
            $mensaje = "¡Solicitudes enviadas con éxito! Tus préstamos están pendientes de aprobación por el personal de la biblioteca. Recibirás una notificación cuando sean aprobados.";
            
            // Eliminar los elementos restantes del carrito que no estaban disponibles
            $stmt = $pdo->prepare("DELETE FROM carrito_prestamos WHERE id_cliente = ?");
            $stmt->execute([$id_cliente]);

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error al procesar las solicitudes: " . $e->getMessage();
        }
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!-- Banner -->
<div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white">
    <div class="max-w-7xl mx-auto px-4 py-12">
        <h1 class="text-4xl font-bold mb-4">Finalizar Solicitud</h1>
        <nav class="text-sm mb-6">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center">
                    <a href="<?php echo $basePath; ?>index.php" class="text-purple-200 hover:text-white">Inicio</a>
                    <span class="mx-2">/</span>
                </li>
                <li class="flex items-center">
                    <a href="<?php echo $basePath; ?>vistas/carrito/index.php" class="text-purple-200 hover:text-white">Carrito</a>
                    <span class="mx-2">/</span>
                </li>
                <li class="text-purple-100">Finalizar</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Contenido principal -->
<div class="max-w-7xl mx-auto px-4 py-8">
    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
            <?php echo $error; ?>
            <div class="mt-2">
                <a href="<?php echo $basePath; ?>vistas/carrito/index.php" class="text-red-700 underline hover:text-red-800">
                    Volver al carrito
                </a>
            </div>
        </div>
    <?php elseif (isset($mensaje)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
            <?php echo $mensaje; ?>
            <div class="mt-2">
                <a href="<?php echo $basePath; ?>vistas/prestamo/index.php" class="text-green-700 underline hover:text-green-800">
                    Ver mis solicitudes
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Resumen de la solicitud -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Confirmación de Solicitud</h2>
                <p class="text-gray-600 mt-1">
                    Estás a punto de solicitar el préstamo de <?php echo count($libros_disponibles); ?> libros.
                </p>
                <div class="mt-2 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Tus solicitudes serán revisadas por el personal de la biblioteca. Recibirás una notificación cuando sean aprobadas.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de libros a solicitar -->
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-800 mb-4">Libros seleccionados</h3>
                
                <ul class="space-y-4">
                    <?php foreach ($libros_disponibles as $libro): ?>
                        <li class="flex items-start space-x-4">
                            <!-- Imagen del libro -->
                            <div class="flex-shrink-0 w-16">
                                <?php if ($libro['imagen_portada']): ?>
                                    <img src="<?php echo $basePath . $libro['imagen_portada']; ?>" 
                                         alt="<?php echo htmlspecialchars($libro['titulo']); ?>"
                                         class="w-full h-20 object-cover rounded">
                                <?php else: ?>
                                    <div class="w-full h-20 bg-gray-200 flex items-center justify-center rounded">
                                        <i class="fas fa-book text-gray-400 text-xl"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Información del libro -->
                            <div>
                                <h4 class="text-base font-medium text-gray-800">
                                    <?php echo htmlspecialchars($libro['titulo']); ?>
                                </h4>
                                <p class="text-sm text-gray-600">
                                    <?php echo htmlspecialchars($libro['autor_nombre'] . ' ' . $libro['autor_apellido']); ?>
                                </p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Formulario para confirmar la solicitud -->
            <form method="POST" class="p-6">
                <div class="mb-6">
                    <label for="dias_prestamo" class="block text-sm font-medium text-gray-700 mb-2">
                        Duración del préstamo
                    </label>
                    <select id="dias_prestamo" 
                            name="dias_prestamo"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                        <option value="7" <?php echo $dias_prestamo_default == 7 ? 'selected' : ''; ?>>7 días</option>
                        <option value="14" <?php echo $dias_prestamo_default == 14 ? 'selected' : ''; ?>>14 días</option>
                        <option value="30" <?php echo $dias_prestamo_default == 30 ? 'selected' : ''; ?>>30 días</option>
                    </select>
                    <p class="mt-1 text-sm text-gray-500">
                        Período solicitado para el préstamo una vez sea aprobado.
                    </p>
                </div>

                <div class="flex items-center justify-between">
                    <a href="<?php echo $basePath; ?>vistas/carrito/index.php" 
                       class="text-gray-600 hover:text-gray-800">
                        Volver al carrito
                    </a>
                    <button type="submit"
                            class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                        Enviar Solicitud
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../modules/footer.php'; ?>