<?php
//admin/usuarios/editar.php
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

$id_cliente = $_GET['id'];

// Obtener datos del usuario
try {
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
    $stmt->execute([$id_cliente]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        header('Location: index.php');
        exit;
    }
} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Error al cargar los datos del usuario.";
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = cleanInput($_POST['nombre']);
    $apellido = cleanInput($_POST['apellido']);
    $email = cleanInput($_POST['email']);
    $telefono = cleanInput($_POST['telefono']);
    $direccion = cleanInput($_POST['direccion']);
    $estado = cleanInput($_POST['estado']);
    
    // Procesar imagen si se subió una nueva
    $imagen_cliente = $usuario['imagen_cliente']; // Mantener la imagen actual por defecto
    if (isset($_FILES['imagen_cliente']) && $_FILES['imagen_cliente']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['imagen_cliente']['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($filetype, $allowed)) {
            $newname = uniqid() . '.' . $filetype;
            $uploadDir = '../../img/usuarios/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['imagen_cliente']['tmp_name'], $uploadDir . $newname)) {
                // Eliminar imagen anterior si existe
                if ($usuario['imagen_cliente'] && file_exists('../../' . $usuario['imagen_cliente'])) {
                    unlink('../../' . $usuario['imagen_cliente']);
                }
                $imagen_cliente = 'img/usuarios/' . $newname;
            } else {
                $error = "Error al subir la nueva imagen";
            }
        } else {
            $error = "Tipo de archivo no permitido. Solo se permiten imágenes JPG, JPEG, PNG y GIF.";
        }
    }

    // Eliminar imagen actual si se solicitó
    if (isset($_POST['eliminar_imagen']) && $_POST['eliminar_imagen'] == '1') {
        if ($usuario['imagen_cliente'] && file_exists('../../' . $usuario['imagen_cliente'])) {
            unlink('../../' . $usuario['imagen_cliente']);
        }
        $imagen_cliente = null;
    }

    if (!isset($error)) {
        try {
            // Verificar si el email ya existe y no es del usuario actual
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE email = ? AND id_cliente != ?");
            $stmt->execute([$email, $id_cliente]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Ya existe otro usuario con este email.";
            } else {
                // Preparar la consulta base sin contraseña
                $sql = "UPDATE clientes SET nombre = ?, apellido = ?, email = ?, telefono = ?, 
                        direccion = ?, imagen_cliente = ?, estado = ?";
                $params = [
                    $nombre,
                    $apellido,
                    $email,
                    $telefono,
                    $direccion,
                    $imagen_cliente,
                    $estado
                ];
    
                // Si se proporcionó una nueva contraseña, añadirla a la actualización
                if (!empty($_POST['password'])) {
                    $sql .= ", password = ?";
                    $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                }
    
                $sql .= " WHERE id_cliente = ?";
                $params[] = $id_cliente;
    
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute($params)) {
                    header('Location: index.php?mensaje=Usuario actualizado exitosamente');
                    exit;
                }
            }
        } catch(PDOException $e) {
            error_log($e->getMessage());
            $error = "Error al actualizar el usuario.";
        }
    }
}

$pageTitle = "Editar Usuario - BiblioTech";
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
                <a href="../../index.php" class="text-white text-xl font-bold">BiblioTech</a>
            </div>
            <nav class="mt-4">
                <a href="../dashboard.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                </a>
                <a href="../libros/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-book mr-3"></i>Libros
                </a>
                <a href="../prestamos/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-handshake mr-3"></i>Préstamos
                </a>
                <a href="index.php" class="flex items-center px-6 py-3 bg-gray-700 text-white">
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
                    <h1 class="text-2xl font-bold">Editar Usuario</h1>
                    <a href="index.php" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-arrow-left mr-2"></i>Volver
                    </a>
                </div>

                <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Imagen actual del usuario -->
                        <?php if ($usuario['imagen_cliente']): ?>
                        <div class="mb-4 col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Imagen Actual
                            </label>
                            <div class="flex items-center space-x-4">
                                <img src="../../<?php echo htmlspecialchars($usuario['imagen_cliente']); ?>" 
                                     alt="Imagen actual del usuario"
                                     class="w-32 h-32 object-cover rounded-full">
                                <div class="flex items-center">
                                    <input type="checkbox" id="eliminar_imagen" name="eliminar_imagen" value="1"
                                           class="mr-2">
                                    <label for="eliminar_imagen" class="text-sm text-gray-600">
                                        Eliminar imagen actual
                                    </label>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="nombre">
                                Nombre
                            </label>
                            <input type="text" id="nombre" name="nombre" required
                                   value="<?php echo htmlspecialchars($usuario['nombre']); ?>"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-purple-600">
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="apellido">
                                Apellido
                            </label>
                            <input type="text" id="apellido" name="apellido" required
                                   value="<?php echo htmlspecialchars($usuario['apellido']); ?>"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-purple-600">
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                                Email
                            </label>
                            <input type="email" id="email" name="email" required
                                   value="<?php echo htmlspecialchars($usuario['email']); ?>"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-purple-600">
                        </div>

                        <div class="mb-4">
    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
        Nueva Contraseña
    </label>
    <input type="password" 
           id="password" 
           name="password"
           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-purple-600"
           placeholder="Dejar vacío para mantener la contraseña actual">
    <p class="text-sm text-gray-500 mt-1">
        Dejar vacío para mantener la contraseña actual
    </p>
</div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="telefono">
                                Teléfono
                            </label>
                            <input type="tel" id="telefono" name="telefono" required
                                   value="<?php echo htmlspecialchars($usuario['telefono']); ?>"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-purple-600">
                        </div>

                        <div class="mb-4 col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="direccion">
                                Dirección
                            </label>
                            <textarea id="direccion" name="direccion" rows="3"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-purple-600"><?php echo htmlspecialchars($usuario['direccion']); ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="estado">
                                Estado
                            </label>
                            <select id="estado" name="estado" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-purple-600">
                                <option value="Activo" <?php echo $usuario['estado'] == 'Activo' ? 'selected' : ''; ?>>
                                    Activo
                                </option>
                                <option value="Inactivo" <?php echo $usuario['estado'] == 'Inactivo' ? 'selected' : ''; ?>>
                                    Inactivo
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="imagen_cliente">
                                Nueva Imagen
                            </label>
                            <input type="file" id="imagen_cliente" name="imagen_cliente" accept="image/*"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-purple-600">
                            <p class="text-sm text-gray-500 mt-1">Formatos permitidos: JPG, JPEG, PNG, GIF</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="index.php" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mr-2">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Script para manejar la interacción entre el checkbox de eliminar imagen y el input de nueva imagen
    document.getElementById('eliminar_imagen')?.addEventListener('change', function() {
        const nuevaImagenInput = document.getElementById('imagen_cliente');
        if (this.checked) {
            nuevaImagenInput.value = '';
            nuevaImagenInput.disabled = true;
        } else {
            nuevaImagenInput.disabled = false;
        }
    });
    </script>
</body>
</html>