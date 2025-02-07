<?php
//admin/libros/agregar.php
session_start();
require_once '../../config/config.php';

// Verificar permisos
if (!isLoggedIn() || (!isAdmin() && !isBibliotecario())) {
    header('Location: ../../login.php');
    exit;
}

// Obtener listas para los selects
try {
    $autores = $pdo->query("SELECT * FROM autores ORDER BY nombre")->fetchAll();
    $editoriales = $pdo->query("SELECT * FROM editoriales ORDER BY nombre")->fetchAll();
    $generos = $pdo->query("SELECT * FROM generos ORDER BY nombre")->fetchAll();
    $categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();
} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Error al cargar los datos necesarios.";
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = cleanInput($_POST['titulo']);
    $id_autor = cleanInput($_POST['id_autor']);
    $id_editorial = cleanInput($_POST['id_editorial']);
    $id_genero = cleanInput($_POST['id_genero']);
    $id_categoria = cleanInput($_POST['id_categoria']);
    $isbn = cleanInput($_POST['isbn']);
    $año_publicacion = cleanInput($_POST['año_publicacion']);
    $cantidad_total = cleanInput($_POST['cantidad_total']);
    $sinopsis = cleanInput($_POST['sinopsis']);
    
    // Procesar imagen
    $imagen_portada = null;
    if (isset($_FILES['imagen_portada']) && $_FILES['imagen_portada']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['imagen_portada']['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($filetype, $allowed)) {
            $newname = uniqid() . '.' . $filetype;
            $uploadDir = '../../img/libros/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['imagen_portada']['tmp_name'], $uploadDir . $newname)) {
                $imagen_portada = 'img/libros/' . $newname;
            } else {
                $error = "Error al subir la imagen";
            }
        } else {
            $error = "Tipo de archivo no permitido. Solo se permiten imágenes JPG, JPEG, PNG y GIF.";
        }
    }

    if (!isset($error)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO libros (titulo, imagen_portada, id_autor, id_editorial, 
                                  id_genero, id_categoria, isbn, año_publicacion, 
                                  cantidad_total, cantidad_disponible, sinopsis, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Disponible')
            ");
            
            if ($stmt->execute([
                $titulo, 
                $imagen_portada, 
                $id_autor, 
                $id_editorial, 
                $id_genero,
                $id_categoria,
                $isbn,
                $año_publicacion, 
                $cantidad_total, 
                $cantidad_total, 
                $sinopsis
            ])) {
                header('Location: index.php?mensaje=Libro agregado exitosamente');
                exit;
            }
        } catch(PDOException $e) {
            error_log($e->getMessage());
            $error = "Error al agregar el libro.";
        }
    }
}

$pageTitle = "Agregar Libro - BiblioSis";
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
                <a href="index.php" class="flex items-center px-6 py-3 bg-gray-700 text-white">
                    <i class="fas fa-book mr-3"></i>Libros
                </a>
                <!-- ... resto del menú ... -->
            </nav>
        </div>

        <!-- Contenido principal -->
        <div class="flex-1 p-8">
            <div class="max-w-3xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-2xl font-bold">Agregar Nuevo Libro</h1>
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
                        <!-- Título -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="titulo">
                                Título
                            </label>
                            <input type="text" id="titulo" name="titulo" required
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <!-- ISBN -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="isbn">
                                ISBN
                            </label>
                            <input type="text" id="isbn" name="isbn" required
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <!-- Autor -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="id_autor">
                                Autor
                            </label>
                            <select id="id_autor" name="id_autor" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">Seleccionar autor</option>
                                <?php foreach ($autores as $autor): ?>
                                    <option value="<?php echo $autor['id_autor']; ?>">
                                        <?php echo htmlspecialchars($autor['nombre'] . ' ' . $autor['apellido']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Editorial -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="id_editorial">
                                Editorial
                            </label>
                            <select id="id_editorial" name="id_editorial" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">Seleccionar editorial</option>
                                <?php foreach ($editoriales as $editorial): ?>
                                    <option value="<?php echo $editorial['id_editorial']; ?>">
                                        <?php echo htmlspecialchars($editorial['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Género -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="id_genero">
                                Género
                            </label>
                            <select id="id_genero" name="id_genero" required
                                    onchange="actualizarCategorias()"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">Seleccionar género</option>
                                <?php foreach ($generos as $genero): ?>
                                    <option value="<?php echo $genero['id_genero']; ?>">
                                        <?php echo htmlspecialchars($genero['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Categoría -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="id_categoria">
                                Categoría
                            </label>
                            <select id="id_categoria" name="id_categoria" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">Seleccionar categoría</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo $categoria['id_categoria']; ?>"
                                            data-genero="<?php echo $categoria['id_genero']; ?>">
                                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Año de Publicación -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="año_publicacion">
                                Año de Publicación
                            </label>
                            <input type="number" id="año_publicacion" name="año_publicacion" required
                                   min="1800" max="<?php echo date('Y'); ?>"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <!-- Cantidad -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="cantidad_total">
                                Cantidad Total
                            </label>
                            <input type="number" id="cantidad_total" name="cantidad_total" required
                                   min="1"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <!-- Imagen de Portada -->
                        <div class="mb-4 col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Imagen de Portada
                            </label>
                            <input type="file" name="imagen_portada" accept="image/*"
                                   class="w-full text-gray-700 px-3 py-2 border rounded">
                        </div>

                        <!-- Sinopsis -->
                        <div class="mb-4 col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="sinopsis">
                                Sinopsis
                            </label>
                            <textarea id="sinopsis" name="sinopsis" rows="4"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="index.php" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mr-2">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Guardar Libro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function actualizarCategorias() {
        const generoSelect = document.getElementById('id_genero');
        const categoriaSelect = document.getElementById('id_categoria');
        const generoSeleccionado = generoSelect.value;
        
        // Ocultar todas las categorías
        Array.from(categoriaSelect.options).forEach(option => {
            const generoCategoria = option.getAttribute('data-genero');
            if (option.value === '') {
                option.style.display = 'block'; // Mostrar siempre la opción por defecto
            } else {
                option.style.display = generoCategoria === generoSeleccionado ? 'block' : 'none';
            }
        });

        // Resetear la selección de categoría
        categoriaSelect.value = '';
    }

    // Ejecutar al cargar la página
    document.addEventListener('DOMContentLoaded', actualizarCategorias);
    </script>
</body>
</html>