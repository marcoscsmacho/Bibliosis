<?php
//admin/prestamos/ver.php
session_start();
require_once '../../config/config.php';

// Verificar permisos
if (!isLoggedIn() || (!isAdmin() && !isBibliotecario())) {
    header('Location: ../../login.php');
    exit;
}

// Verificar que se proporcionó un ID
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_prestamo = $_GET['id'];

// Obtener datos del préstamo
try {
    $stmt = $pdo->prepare("
        SELECT p.*,
               l.titulo as libro_titulo,
               l.isbn,
               l.imagen_portada,
               a.nombre as autor_nombre,
               a.apellido as autor_apellido,
               c.nombre as cliente_nombre,
               c.apellido as cliente_apellido,
               c.email as cliente_email,
               c.telefono as cliente_telefono,
               c.imagen_cliente,
               u.nombre as bibliotecario_nombre,
               u.apellido as bibliotecario_apellido
        FROM prestamos p
        LEFT JOIN libros l ON p.id_libro = l.id_libro
        LEFT JOIN autores a ON l.id_autor = a.id_autor
        LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
        LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
        WHERE p.id_prestamo = ?
    ");
    $stmt->execute([$id_prestamo]);
    $prestamo = $stmt->fetch();

    if (!$prestamo) {
        header('Location: index.php');
        exit;
    }

    // Calcular días de atraso si aplica
    $dias_atraso = 0;
    if ($prestamo['estado'] == 'Prestado') {
        $fecha_devolucion = new DateTime($prestamo['fecha_devolucion_esperada']);
        $hoy = new DateTime();
        if ($hoy > $fecha_devolucion) {
            $dias_atraso = $hoy->diff($fecha_devolucion)->days;
        }
    }

} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Error al cargar los datos del préstamo.";
}

// Procesar devolución si se solicita
if (isset($_POST['devolver']) && $prestamo['estado'] == 'Prestado') {
    try {
        $pdo->beginTransaction();

        // Actualizar préstamo
        $stmt = $pdo->prepare("
            UPDATE prestamos 
            SET estado = 'Devuelto',
                fecha_devolucion_real = CURRENT_DATE,
                observaciones = CONCAT(IFNULL(observaciones, ''), '\nDevuelto el ', CURRENT_DATE)
            WHERE id_prestamo = ?
        ");
        $stmt->execute([$id_prestamo]);

        // No actualizamos el libro manualmente - el trigger after_prestamo_update lo hará automáticamente

        $pdo->commit();
        header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $id_prestamo . '&mensaje=Préstamo devuelto exitosamente');
        exit;
    } catch(PDOException $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        $error = "Error al procesar la devolución.";
    }
}

$pageTitle = "Ver Préstamo - BiblioSis";
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
        <div class="flex-1 p-8">
            <div class="max-w-4xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-bold">Detalles del Préstamo</h1>
                    <a href="index.php" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-arrow-left mr-2"></i>Volver
                    </a>
                </div>

                <?php if (isset($_GET['mensaje'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    <?php echo htmlspecialchars($_GET['mensaje']); ?>
                </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <!-- Estado del préstamo -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-semibold mb-2">Estado del Préstamo</h2>
                            <span class="px-3 py-1 rounded-full text-sm <?php 
                                echo $prestamo['estado'] === 'Prestado' ? 'bg-yellow-100 text-yellow-800' : 
                                    ($prestamo['estado'] === 'Devuelto' ? 'bg-green-100 text-green-800' : 
                                    'bg-red-100 text-red-800'); ?>">
                                <?php echo htmlspecialchars($prestamo['estado']); ?>
                            </span>
                            <?php if ($dias_atraso > 0): ?>
                            <span class="ml-2 text-red-600">
                                (<?php echo $dias_atraso; ?> días de atraso)
                            </span>
                            <?php endif; ?>
                        </div>
                        <?php if ($prestamo['estado'] == 'Prestado'): ?>
                        <form method="POST" onsubmit="return confirm('¿Confirmar la devolución del libro?');">
                            <button type="submit" name="devolver" value="1"
                                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                                <i class="fas fa-undo mr-2"></i>Procesar Devolución
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Información del libro -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold mb-4">Información del Libro</h2>
                        <div class="flex mb-4">
                            <?php if ($prestamo['imagen_portada']): ?>
                                <img src="../../<?php echo htmlspecialchars($prestamo['imagen_portada']); ?>" 
                                     alt="Portada" 
                                     class="w-24 h-32 object-cover rounded">
                            <?php else: ?>
                                <div class="w-24 h-32 bg-gray-200 flex items-center justify-center rounded">
                                    <i class="fas fa-book text-gray-400 text-4xl"></i>
                                </div>
                            <?php endif; ?>
                            <div class="ml-4">
                                <h3 class="font-medium"><?php echo htmlspecialchars($prestamo['libro_titulo']); ?></h3>
                                <p class="text-gray-600">
                                    <?php echo htmlspecialchars($prestamo['autor_nombre'] . ' ' . $prestamo['autor_apellido']); ?>
                                </p>
                                <p class="text-gray-500 text-sm">ISBN: <?php echo htmlspecialchars($prestamo['isbn']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Información del usuario -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold mb-4">Información del Usuario</h2>
                        <div class="flex items-start">
                            <?php if ($prestamo['imagen_cliente']): ?>
                                <img src="../../<?php echo htmlspecialchars($prestamo['imagen_cliente']); ?>" 
                                     alt="Usuario" 
                                     class="w-16 h-16 object-cover rounded-full">
                            <?php else: ?>
                                <div class="w-16 h-16 bg-gray-200 flex items-center justify-center rounded-full">
                                    <i class="fas fa-user text-gray-400 text-2xl"></i>
                                </div>
                            <?php endif; ?>
                            <div class="ml-4">
                                <h3 class="font-medium">
                                    <?php echo htmlspecialchars($prestamo['cliente_nombre'] . ' ' . $prestamo['cliente_apellido']); ?>
                                </h3>
                                <p class="text-gray-600"><?php echo htmlspecialchars($prestamo['cliente_email']); ?></p>
                                <p class="text-gray-500">Tel: <?php echo htmlspecialchars($prestamo['cliente_telefono']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles del préstamo -->
                    <div class="bg-white rounded-lg shadow-sm p-6 md:col-span-2">
                        <h2 class="text-xl font-semibold mb-4">Detalles del Préstamo</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-600">
                                    <span class="font-medium">Fecha de Préstamo:</span><br>
                                    <?php echo date('d/m/Y', strtotime($prestamo['fecha_prestamo'])); ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-600">
                                    <span class="font-medium">Fecha de Devolución Esperada:</span><br>
                                    <?php echo date('d/m/Y', strtotime($prestamo['fecha_devolucion_esperada'])); ?>
                                </p>
                            </div>
                            <?php if ($prestamo['fecha_devolucion_real']): ?>
                            <div>
                                <p class="text-gray-600">
                                    <span class="font-medium">Fecha de Devolución Real:</span><br>
                                    <?php echo date('d/m/Y', strtotime($prestamo['fecha_devolucion_real'])); ?>
                                </p>
                            </div>
                            <?php endif; ?>
                            <div>
                                <p class="text-gray-600">
                                    <span class="font-medium">Registrado por:</span><br>
                                    <?php echo htmlspecialchars($prestamo['bibliotecario_nombre'] . ' ' . $prestamo['bibliotecario_apellido']); ?>
                                </p>
                            </div>
                        </div>
                        <?php if ($prestamo['observaciones']): ?>
                        <div class="mt-4">
                            <p class="font-medium">Observaciones:</p>
                            <p class="text-gray-600 whitespace-pre-line">
                                <?php echo htmlspecialchars($prestamo['observaciones']); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (isAdmin()): ?>
                <div class="mt-6 flex justify-end">
                    <a href="editar.php?id=<?php echo $prestamo['id_prestamo']; ?>" 
                       class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">
                        <i class="fas fa-edit mr-2"></i>Editar Préstamo
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>