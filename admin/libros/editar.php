<?php
//admin/libros/editar.php
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

$id_libro = $_GET['id'];

// Obtener datos del libro
try {
    $stmt = $pdo->prepare("SELECT * FROM libros WHERE id_libro = ?");
    $stmt->execute([$id_libro]);
    $libro = $stmt->fetch();

    if (!$libro) {
        header('Location: index.php');
        exit;
    }

    // Obtener listas para los selects
    $autores = $pdo->query("SELECT * FROM autores ORDER BY nombre")->fetchAll();
    $editoriales = $pdo->query("SELECT * FROM editoriales ORDER BY nombre")->fetchAll();
    $generos = $pdo->query("SELECT * FROM generos ORDER BY nombre")->fetchAll();
    $categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();
} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Error al cargar los datos del libro.";
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
    
    // Procesar imagen si se subió una nueva
    $imagen_portada = $libro['imagen_portada']; // Mantener la imagen actual por defecto
    if (isset($_FILES['imagen_portada']) && $_FILES['imagen_portada']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['imagen_portada']['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($filetype, $allowed)) {
            // Crear nombre único para la imagen
            $newname = uniqid() . '.' . $filetype;
            $uploadDir = '../../img/libros/';
            
            // Asegurarse de que la carpeta existe
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Subir la nueva imagen
            if (move_uploaded_file($_FILES['imagen_portada']['tmp_name'], $uploadDir . $newname)) {
                // Eliminar imagen anterior si existe
                if ($libro['imagen_portada'] && file_exists('../../' . $libro['imagen_portada'])) {
                    unlink('../../' . $libro['imagen_portada']);
                }
                $imagen_portada = 'img/libros/' . $newname;
            } else {
                $error = "Error al subir la nueva imagen";
            }
        } else {
            $error = "Tipo de archivo no permitido. Solo se permiten imágenes JPG, JPEG, PNG y GIF.";
        }
    }

    if (!isset($error)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE libros 
                SET titulo = ?, imagen_portada = ?, id_autor = ?, id_editorial = ?, 
                    id_genero = ?, id_categoria = ?, isbn = ?, año_publicacion = ?, cantidad_total = ?, 
                    sinopsis = ? 
                WHERE id_libro = ?
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
                $sinopsis, 
                $id_libro
            ])) {
                header('Location: index.php?mensaje=Libro actualizado exitosamente');
                exit;
            }
        } catch(PDOException $e) {
            error_log($e->getMessage());
            $error = "Error al actualizar el libro.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Libro - BiblioTech</title>
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
                <a href="../prestamos/" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
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
                    <h1 class="text-2xl font-bold">Editar Libro</h1>
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
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="titulo">
                                Título
                            </label>
                            <input type="text" id="titulo" name="titulo" required
                                   value="<?php echo htmlspecialchars($libro['titulo']); ?>"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="imagen_portada">
                                Imagen de Portada
                            </label>
                            <?php if ($libro['imagen_portada']): ?>
                                <img src="../../<?php echo htmlspecialchars($libro['imagen_portada']); ?>" 
                                     alt="Portada actual" 
                                     class="w-32 h-auto mb-2">
                            <?php endif; ?>
                            <input type="file" id="imagen_portada" name="imagen_portada" accept="image/*"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <p class="text-sm text-gray-500 mt-1">Deja vacío para mantener la imagen actual</p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="id_autor">
                                Autor
                            </label>
                            <select id="id_autor" name="id_autor" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">Seleccionar autor</option>
                                <?php foreach ($autores as $autor): ?>
                                    <option value="<?php echo $autor['id_autor']; ?>" 
                                            <?php echo $autor['id_autor'] == $libro['id_autor'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($autor['nombre'] . ' ' . $autor['apellido']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="id_editorial">
                                Editorial
                            </label>
                            <select id="id_editorial" name="id_editorial" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">Seleccionar editorial</option>
                                <?php foreach ($editoriales as $editorial): ?>
                                    <option value="<?php echo $editorial['id_editorial']; ?>"
                                            <?php echo $editorial['id_editorial'] == $libro['id_editorial'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($editorial['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="id_genero">
                                Género
                            </label>
                            <select id="id_genero" name="id_genero" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">Seleccionar género</option>
                                <?php foreach ($generos as $genero): ?>
                                    <option value="<?php echo $genero['id_genero']; ?>"
                                            <?php echo $genero['id_genero'] == $libro['id_genero'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($genero['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="id_categoria">
                                Categoría
                            </label>
                            <select id="id_categoria" name="id_categoria" required
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                                <option value="">Seleccionar categoría</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo $categoria['id_categoria']; ?>"
                                            data-genero="<?php echo $categoria['id_genero']; ?>"
                                            <?php echo $categoria['id_categoria'] == $libro['id_categoria'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="isbn">
                                ISBN
                            </label>
                            <input type="text" id="isbn" name="isbn" required
                                   value="<?php echo htmlspecialchars($libro['isbn']); ?>"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="año_publicacion">
                                Año de Publicación
                            </label>
                            <input type="number" id="año_publicacion" name="año_publicacion" required
                                   value="<?php echo htmlspecialchars($libro['año_publicacion']); ?>"
                                   min="1800" max="<?php echo date('Y'); ?>"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="cantidad_total">
                                Cantidad Total
                            </label>
                            <input type="number" id="cantidad_total" name="cantidad_total" required
                                   value="<?php echo htmlspecialchars($libro['cantidad_total']); ?>"
                                   min="1"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <div class="mb-4 col-span-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="sinopsis">
                                Sinopsis
                            </label>
                            <textarea id="sinopsis" name="sinopsis" rows="4"
                                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?php echo htmlspecialchars($libro['sinopsis']); ?></textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Guardar Cambios
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
    
    // Mostrar todas las opciones primero
    Array.from(categoriaSelect.options).forEach(option => {
        option.style.display = 'block';
    });

    // Si no hay género seleccionado, mostrar todas las categorías
    if (!generoSeleccionado) {
        return;
    }

    // Filtrar las categorías según el género seleccionado
    Array.from(categoriaSelect.options).forEach(option => {
        const generoCategoria = option.getAttribute('data-genero');
        if (option.value === '') {
            // Siempre mostrar la opción "Seleccionar categoría"
            option.style.display = 'block';
        } else {
            // Mostrar solo las categorías del género seleccionado
            if (generoCategoria !== generoSeleccionado) {
                option.style.display = 'none';
            }
        }
    });

    // Si la categoría seleccionada no pertenece al nuevo género, resetear la selección
    const categoriaSeleccionada = categoriaSelect.options[categoriaSelect.selectedIndex];
    if (categoriaSeleccionada && 
        categoriaSeleccionada.value !== '' && 
        categoriaSeleccionada.getAttribute('data-genero') !== generoSeleccionado) {
        categoriaSelect.value = '';
    }
}

// Asignar el evento change al select de género
document.addEventListener('DOMContentLoaded', function() {
    const generoSelect = document.getElementById('id_genero');
    generoSelect.addEventListener('change', actualizarCategorias);
    
    // Ejecutar una vez al cargar la página para establecer el estado inicial
    actualizarCategorias();
});
</script>

</body>
</html>