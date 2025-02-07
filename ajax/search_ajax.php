<?php
// ajax/search_ajax.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/config.php';
header('Content-Type: application/json');

try {
    if (!isset($_POST['query'])) {
        throw new Exception('No query provided');
    }

    $query = '%' . $_POST['query'] . '%';
    
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
    
    echo json_encode($results);

} catch(Exception $e) {
    http_response_code(200);
    echo json_encode(['error' => $e->getMessage()]);
}
?>