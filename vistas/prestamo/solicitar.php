<?php
// vistas/prestamos/solicitar.php
session_start();
$pageTitle = 'Solicitar Préstamo - BiblioSis';
require_once '../../modules/header.php';

// Verificar que el usuario esté logueado
if (!isLoggedIn()) {
    $_SESSION['error'] = "Debes iniciar sesión para solicitar un préstamo.";
    header('Location: ' . $basePath . 'login.php');
    exit;
}

// Verificar que se proporcionó un ID de libro
if (!isset($_GET['id'])) {
    header('Location: ' . $basePath . 'vistas/catalogo/index.php');
    exit;
}

$id_libro = (int)$_GET['id'];
$error = null;
$mensaje = null;

try {
    // Obtener información del libro
    $stmt = $pdo->prepare("
        SELECT l.*, a.nombre as autor_nombre, a.apellido as autor_apellido,
               g.nombre as genero_nombre, e.nombre as editorial_nombre
        FROM libros l
        JOIN autores a ON l.id_autor = a.id_autor
        JOIN generos g ON l.id_genero = g.id_genero
        JOIN editoriales e ON l.id_editorial = e.id_editorial
        WHERE l.id_libro = ?
    ");
    $stmt->execute([$id_libro]);
    $libro = $stmt->fetch();

    if (!$libro) {
        throw new Exception("El libro solicitado no existe.");
    }

    // Verificar disponibilidad
    if ($libro['cantidad_disponible'] <= 0) {
        throw new Exception("Este libro no está disponible actualmente.");
    }

    // Verificar si el usuario ya tiene este libro prestado
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM prestamos 
        WHERE id_libro = ? AND id_cliente = ? AND estado = 'Prestado'
    ");
    $stmt->execute([$id_libro, $_SESSION['user_id']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Ya tienes un ejemplar de este libro prestado.");
    }

    // Verificar cantidad de préstamos activos del usuario
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM prestamos 
        WHERE id_cliente = ? AND estado = 'Prestado'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    if ($stmt->fetchColumn() >= 3) {
        throw new Exception("Has alcanzado el límite máximo de préstamos (3).");
    }

    // Procesar la solicitud de préstamo
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $dias_prestamo = isset($_POST['dias_prestamo']) ? (int)$_POST['dias_prestamo'] : 7;
        
        try {
            $pdo->beginTransaction();

            // Registrar el préstamo
            $stmt = $pdo->prepare("
                INSERT INTO prestamos (id_libro, id_cliente, fecha_devolucion_esperada, estado)
                VALUES (?, ?, DATE_ADD(CURRENT_DATE, INTERVAL ? DAY), 'Prestado')
            ");
            $stmt->execute([$id_libro, $_SESSION['user_id'], $dias_prestamo]);

            // Actualizar disponibilidad del libro
            $stmt = $pdo->prepare("
                UPDATE libros 
                SET cantidad_disponible = cantidad_disponible - 1,
                    estado = CASE 
                        WHEN cantidad_disponible - 1 = 0 THEN 'No Disponible'
                        ELSE estado 
                    END
                WHERE id_libro = ?
            ");
            $stmt->execute([$id_libro]);

            $pdo->commit();
            $mensaje = "¡Préstamo realizado con éxito! Puedes recoger el libro en la biblioteca.";

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error al procesar el préstamo: " . $e->getMessage();
        }
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white">
    <div class="max-w-7xl mx-auto px-4 py-12">
        <h1 class="text-4xl font-bold mb-4">Solicitar Préstamo</h1>
        <nav class="text-sm">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center">
                    <a href="<?php echo $basePath; ?>index.php" class="text-purple-200 hover:text-white">Inicio</a>
                    <span class="mx-2">/</span>
                </li>
                <li class="flex items-center">
                    <a href="<?php echo $basePath; ?>vistas/catalogo/index.php" class="text-purple-200 hover:text-white">Catálogo</a>
                    <span class="mx-2">/</span>
                </li>
                <li class="text-purple-100">Solicitar Préstamo</li>
            </ol>
        </nav>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-8">
    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline"><?php echo $error; ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($mensaje)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline"><?php echo $mensaje; ?></span>
            <div class="mt-2">
                <a href="<?php echo $basePath; ?>vistas/catalogo/index.php" 
                   class="text-green-700 underline hover:text-green-800">
                    Volver al catálogo
                </a>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($libro) && !isset($mensaje)): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="md:flex">
                <!-- Imagen del libro -->
                <div class="md:flex-shrink-0 md:w-64 h-96">
                    <?php if ($libro['imagen_portada']): ?>
                        <img src="<?php echo $basePath . $libro['imagen_portada']; ?>" 
                             alt="<?php echo htmlspecialchars($libro['titulo']); ?>"
                             class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-book text-gray-400 text-4xl"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Información del libro y formulario -->
                <div class="p-8 flex-1">
                    <div class="uppercase tracking-wide text-sm text-indigo-500 font-semibold">
                        <?php echo htmlspecialchars($libro['genero_nombre']); ?>
                    </div>
                    <h2 class="mt-1 text-2xl font-semibold text-gray-900">
                        <?php echo htmlspecialchars($libro['titulo']); ?>
                    </h2>
                    <p class="mt-2 text-gray-600">
                        por <?php echo htmlspecialchars($libro['autor_nombre'] . ' ' . $libro['autor_apellido']); ?>
                    </p>
                    
                    <div class="mt-4 text-gray-500">
                        <p>Editorial: <?php echo htmlspecialchars($libro['editorial_nombre']); ?></p>
                        <p>Año: <?php echo $libro['año_publicacion']; ?></p>
                        <p>ISBN: <?php echo htmlspecialchars($libro['isbn']); ?></p>
                        <p class="mt-2">Disponibles: <?php echo $libro['cantidad_disponible']; ?> ejemplares</p>
                    </div>

                    <?php if (!isset($error)): ?>
                        <form method="POST" class="mt-6 space-y-6">
                            <div>
                                <label for="dias_prestamo" class="block text-sm font-medium text-gray-700">
                                    Duración del préstamo
                                </label>
                                <select id="dias_prestamo" 
                                        name="dias_prestamo"
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="7">7 días</option>
                                    <option value="14">14 días</option>
                                </select>
                            </div>

                            <div class="flex items-center space-x-4">
                                <button type="submit"
                                        class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                                    Confirmar Préstamo
                                </button>
                                
                                <a href="<?php echo $basePath; ?>vistas/catalogo/index.php"
                                   class="text-gray-600 hover:text-gray-800">
                                    Cancelar
                                </a>
                            </div>
                        </form>

                        <div class="mt-6 text-sm text-gray-500">
                            <p><i class="fas fa-info-circle mr-2"></i>Al confirmar el préstamo, tendrás que recoger el libro en la biblioteca dentro de las próximas 24 horas.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once $basePath . 'modules/footer.php'; ?>



