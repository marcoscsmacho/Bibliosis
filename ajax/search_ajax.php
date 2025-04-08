<?php
// Apagar notificaciones de error para evitar contaminación del JSON
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/config.php';

// Establecer el tipo de contenido como JSON
header('Content-Type: application/json');

try {
    if (!isset($_POST['query'])) {
        throw new Exception('No query provided');
    }

    $query = '%' . $_POST['query'] . '%';

    // Obtener la ruta base usando la función del config.php
    $basePath = getBasePath();
    
    $stmt = $pdo->prepare("
        SELECT l.id_libro, l.titulo, l.imagen_portada,
               a.nombre as autor_nombre, a.apellido as autor_apellido
        FROM libros l
        JOIN autores a ON l.id_autor = a.id_autor
        WHERE l.titulo LIKE ? 
           OR CONCAT(a.nombre, ' ', a.apellido) LIKE ? 
           OR a.nombre LIKE ? 
           OR a.apellido LIKE ?
        LIMIT 5
    ");
    
    $stmt->execute([$query, $query, $query, $query]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Asegurar que las rutas de las imágenes sean correctas
    foreach ($results as &$result) {
        if (!empty($result['imagen_portada'])) {
            $result['imagen_portada'] = $basePath . $result['imagen_portada'];
        }
    }

    echo json_encode($results);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
