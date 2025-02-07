<?php
// vistas/prestamos/devolver.php
session_start();
if (!isset($basePath)) {
    $basePath = '../../';
}
$pageTitle = 'Devolver Préstamo - BiblioSis';
require_once '../../modules/header.php';

// Verificar que el usuario esté logueado
if (!isLoggedIn()) {
    $_SESSION['error'] = "Debes iniciar sesión para realizar esta acción.";
    header('Location: ../../login.php');
    exit;
}

// Verificar que se proporcionó un ID
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_prestamo = (int)$_GET['id'];
$error = null;
$mensaje = null;

try {
    // Obtener información del préstamo
    $stmt = $pdo->prepare("
        SELECT p.*, l.titulo, l.imagen_portada,
               a.nombre as autor_nombre, a.apellido as autor_apellido,
               DATEDIFF(p.fecha_devolucion_esperada, CURRENT_DATE) as dias_restantes
        FROM prestamos p
        JOIN libros l ON p.id_libro = l.id_libro
        JOIN autores a ON l.id_autor = a.id_autor
        WHERE p.id_prestamo = ? AND p.id_cliente = ? AND p.estado = 'Prestado'
    ");
    $stmt->execute([$id_prestamo, $_SESSION['user_id']]);
    $prestamo = $stmt->fetch();

    if (!$prestamo) {
        throw new Exception("No se encontró el préstamo o ya ha sido devuelto.");
    }

    // Procesar la devolución
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $pdo->beginTransaction();

        try {
            // Actualizar el préstamo
            $stmt = $pdo->prepare("
                UPDATE prestamos 
                SET estado = 'Devuelto',
                    fecha_devolucion_real = CURRENT_DATE,
                    observaciones = CASE 
                        WHEN observaciones IS NULL THEN ? 
                        ELSE CONCAT(observaciones, '\n', ?)
                    END
                WHERE id_prestamo = ? AND id_cliente = ?
            ");
            $observacion = "Devuelto el " . date('Y-m-d') . ": " . ($_POST['observaciones'] ?? '');
            $stmt->execute([
                $observacion,
                $observacion,
                $id_prestamo,
                $_SESSION['user_id']
            ]);

            // Actualizar el libro
            $stmt = $pdo->prepare("
                UPDATE libros 
                SET cantidad_disponible = cantidad_disponible + 1,
                    estado = CASE 
                        WHEN cantidad_disponible + 1 > 0 THEN 'Disponible'
                        ELSE estado 
                    END
                WHERE id_libro = ?
            ");
            $stmt->execute([$prestamo['id_libro']]);

            $pdo->commit();
            $mensaje = "¡El libro ha sido devuelto exitosamente!";
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white">
    <div class="max-w-7xl mx-auto px-4 py-12">
        <h1 class="text-4xl font-bold mb-4">Devolver Libro</h1>
        <nav class="text-sm">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center">
                    <a href="../../index.php" class="text-purple-200 hover:text-white">Inicio</a>
                    <span class="mx-2">/</span>
                </li>
                <li class="flex items-center">
                    <a href="index.php" class="text-purple-200 hover:text-white">Préstamos</a>
                    <span class="mx-2">/</span>
                </li>
                <li class="text-purple-100">Devolver</li>
            </ol>
        </nav>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-8">
    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline"><?php echo $error; ?></span>
            <div class="mt-2">
                <a href="index.php" class="text-red-700 underline hover:text-red-800">
                    Volver a mis préstamos
                </a>
            </div>
        </div>
    <?php elseif (isset($mensaje)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline"><?php echo $mensaje; ?></span>
            <div class="mt-2">
                <a href="index.php" class="text-green-700 underline hover:text-green-800">
                    Volver a mis préstamos
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            
            <div class="md:flex">
                <!-- Imagen del libro -->
                <div class="md:flex-shrink-0 md:w-48">
                    <?php if ($prestamo['imagen_portada']): ?>
                        <img src="../../<?php echo $prestamo['imagen_portada']; ?>" 
                             alt="<?php echo htmlspecialchars($prestamo['titulo']); ?>"
                             class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-book text-gray-400 text-4xl"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Detalles del préstamo -->
                <div class="p-8 flex-1">
                    <div class="mb-4">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">
                            <?php echo htmlspecialchars($prestamo['titulo']); ?>
                        </h2>
                        <p class="text-gray-600">
                            <?php echo htmlspecialchars($prestamo['autor_nombre'] . ' ' . $prestamo['autor_apellido']); ?>
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-gray-600">
                                <span class="font-medium">Fecha de préstamo:</span><br>
                                <?php echo date('d/m/Y', strtotime($prestamo['fecha_prestamo'])); ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-600">
                                <span class="font-medium">Fecha de devolución esperada:</span><br>
                                <?php echo date('d/m/Y', strtotime($prestamo['fecha_devolucion_esperada'])); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Estado del préstamo -->
                    <div class="mb-6">
                        <?php if ($prestamo['dias_restantes'] < 0): ?>
                            <div class="bg-red-100 text-red-700 p-4 rounded-lg">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                El préstamo está atrasado por <?php echo abs($prestamo['dias_restantes']); ?> días.
                            </div>
                        <?php else: ?>
                            <div class="bg-green-100 text-green-700 p-4 rounded-lg">
                                <i class="fas fa-clock mr-2"></i>
                                El préstamo está dentro del plazo. Quedan <?php echo $prestamo['dias_restantes']; ?> días.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Formulario de devolución -->
                    <form method="POST" class="space-y-6">
                        <div>
                            <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-2">
                                Observaciones sobre el estado del libro (opcional)
                            </label>
                            <textarea id="observaciones" 
                                      name="observaciones" 
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500"
                                      placeholder="Anota cualquier observación sobre el estado del libro..."></textarea>
                        </div>

                        <div class="flex items-center justify-end space-x-4">
                            <a href="index.php" 
                               class="text-gray-600 hover:text-gray-800">
                                Cancelar
                            </a>
                            <button type="submit"
                                    class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                                Confirmar Devolución
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../modules/footer.php'; ?>