<?php
session_start();
require_once '../../config/config.php';

// Verificar permisos
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../login.php');
    exit;
}

// Obtener las categorías para el select
try {
    $stmt = $pdo->query("SELECT * FROM categorias ORDER BY nombre");
    $categorias = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Error al cargar las categorías.";
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_categoria = cleanInput($_POST['id_categoria']);
    $nombre = cleanInput($_POST['nombre']);
    $descripcion = cleanInput($_POST['descripcion']);

    try {
        // Verificar si ya existe una subcategoría con ese nombre en esa categoría
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM subcategorias WHERE nombre = ? AND id_categoria = ?");
        $stmt->execute([$nombre, $id_categoria]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Ya existe una subcategoría con ese nombre en esta categoría.";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO subcategorias (id_categoria, nombre, descripcion)
                VALUES (?, ?, ?)
            ");
            
            if ($stmt->execute([$id_categoria, $nombre, $descripcion])) {
                $_SESSION['mensaje'] = "Subcategoría agregada exitosamente.";
                header('Location: index.php');
                exit;
            }
        }
    } catch(PDOException $e) {
        error_log($e->getMessage());
        $error = "Error al agregar la subcategoría.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Subcategoría - BiblioTech</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Agregar Subcategoría</h1>
                    <a href="index.php" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-arrow-left mr-2"></i>Volver
                    </a>
                </div>

                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Categoría Principal
                        </label>
                        <select name="id_categoria" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Seleccionar categoría</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['id_categoria']; ?>">
                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre de la Subcategoría
                        </label>
                        <input type="text" name="nombre" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Descripción
                        </label>
                        <textarea name="descripcion" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500"></textarea>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="index.php" 
                           class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700">
                            Guardar Subcategoría
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>