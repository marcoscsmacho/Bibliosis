<?php
//admin/prestamos/agregar.php
session_start();
require_once '../../config/config.php';

// Verificar permisos
if (!isLoggedIn() || (!isAdmin() && !isBibliotecario())) {
    header('Location: ../../login.php');
    exit;
}

// Obtener listas para los selects
try {
    // Obtener libros disponibles
    $stmt = $pdo->query("
        SELECT l.*, a.nombre as autor_nombre, a.apellido as autor_apellido 
        FROM libros l
        LEFT JOIN autores a ON l.id_autor = a.id_autor
        WHERE l.cantidad_disponible > 0 AND l.estado = 'Disponible'
        ORDER BY l.titulo
    ");
    $libros = $stmt->fetchAll();

    // Obtener usuarios activos
    $stmt = $pdo->query("
        SELECT * FROM clientes 
        WHERE estado = 'Activo'
        ORDER BY nombre, apellido
    ");
    $usuarios = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Error al cargar los datos necesarios.";
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_libro = cleanInput($_POST['id_libro']);
    $id_cliente = cleanInput($_POST['id_cliente']);
    $dias_prestamo = cleanInput($_POST['dias_prestamo']);
    $observaciones = cleanInput($_POST['observaciones']);

    try {
        // Verificar disponibilidad del libro
        $stmt = $pdo->prepare("
            SELECT cantidad_disponible, titulo 
            FROM libros 
            WHERE id_libro = ? AND estado = 'Disponible'
        ");
        $stmt->execute([$id_libro]);
        $libro = $stmt->fetch();

        if (!$libro || $libro['cantidad_disponible'] <= 0) {
            $error = "El libro seleccionado no está disponible.";
        } else {
            // Verificar si el usuario tiene préstamos activos
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as prestamos_activos 
                FROM prestamos 
                WHERE id_cliente = ? AND estado = 'Prestado'
            ");
            $stmt->execute([$id_cliente]);
            $prestamos_activos = $stmt->fetch()['prestamos_activos'];

            if ($prestamos_activos >= 3) {
                $error = "El usuario ya tiene el máximo de préstamos permitidos (3).";
            } else {
                // Iniciar transacción
                $pdo->beginTransaction();

                // Crear el préstamo
                $stmt = $pdo->prepare("
                    INSERT INTO prestamos (id_libro, id_cliente, id_usuario, fecha_devolucion_esperada, observaciones)
                    VALUES (?, ?, ?, DATE_ADD(CURRENT_DATE, INTERVAL ? DAY), ?)
                ");
                
                $stmt->execute([
                    $id_libro,
                    $id_cliente,
                    $_SESSION['user_id'],
                    $dias_prestamo,
                    $observaciones
                ]);

                // Actualizar disponibilidad del libro
                $stmt = $pdo->prepare("
                    UPDATE libros 
                    SET cantidad_disponible = cantidad_disponible - 1,
                        estado = CASE 
                            WHEN cantidad_disponible - 1 = 0 THEN 'Prestado'
                            ELSE estado 
                        END
                    WHERE id_libro = ?
                ");
                $stmt->execute([$id_libro]);

                $pdo->commit();
                header('Location: index.php?mensaje=Préstamo registrado exitosamente');
                exit;
            }
        }
    } catch(PDOException $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        $error = "Error al registrar el préstamo.";
    }
}

$pageTitle = "Nuevo Préstamo - BiblioSis";
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
                    <i class="fas fa-chart-bar mr-3"></i>
                    Reportes
                </a>
                <a href="index.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
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
        <div class="flex-1 p-8">
            <div class="max-w-3xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-bold">Registrar Nuevo Préstamo</h1>
                    <a href="index.php" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-arrow-left mr-2"></i>Volver
                    </a>
                </div>

                <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                    <!-- Selección de libro -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="id_libro">
                            Libro
                        </label>
                        <select id="id_libro" name="id_libro" required
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-purple-600">
                            <option value="">Seleccionar libro</option>
                            <?php foreach ($libros as $libro): ?>
                                <option value="<?php echo $libro['id_libro']; ?>">
                                    <?php echo htmlspecialchars($libro['titulo'] . ' - ' . 
                                          $libro['autor_nombre'] . ' ' . $libro['autor_apellido'] . 
                                          ' (Disponibles: ' . $libro['cantidad_disponible'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Selección de usuario -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="id_cliente">
                            Usuario
                        </label>
                        <select id="id_cliente" name="id_cliente" required
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-purple-600">
                            <option value="">Seleccionar usuario</option>
                            <?php foreach ($usuarios as $usuario): ?>
                                <option value="<?php echo $usuario['id_cliente']; ?>">
                                    <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido'] . 
                                          ' (' . $usuario['email'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Duración del préstamo -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="dias_prestamo">
                            Días de Préstamo
                        </label>
                        <select id="dias_prestamo" name="dias_prestamo" required
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-purple-600">
                            <option value="7">7 días</option>
                            <option value="14">14 días</option>
                            <option value="30">30 días</option>
                        </select>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="observaciones">
                            Observaciones
                        </label>
                        <textarea id="observaciones" name="observaciones" rows="3"
                                 class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-purple-600"
                                 placeholder="Observaciones adicionales sobre el préstamo..."></textarea>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-end">
                        <a href="index.php" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mr-2">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Registrar Préstamo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>