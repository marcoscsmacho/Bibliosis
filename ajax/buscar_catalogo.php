<?php
//ajax/buscar_catalogo.php
require_once '../config/config.php';
header('Content-Type: application/json');

$busqueda = $_GET['busqueda'] ?? '';
$genero = $_GET['genero'] ?? '';
$orden = $_GET['orden'] ?? 'reciente';

try {
    $sql = "
        SELECT l.*, a.nombre as autor_nombre, a.apellido as autor_apellido,
               g.nombre as genero_nombre
        FROM libros l
        JOIN autores a ON l.id_autor = a.id_autor
        JOIN generos g ON l.id_genero = g.id_genero
        WHERE 1=1
    ";
    
    $params = [];

    if (!empty($busqueda)) {
        $sql .= " AND (l.titulo LIKE ? OR a.nombre LIKE ? OR a.apellido LIKE ?)";
        $busquedaParam = "%$busqueda%";
        $params = array_merge($params, [$busquedaParam, $busquedaParam, $busquedaParam]);
    }

    if (!empty($genero)) {
        $sql .= " AND g.id_genero = ?";
        $params[] = $genero;
    }

    switch ($orden) {
        case 'titulo_asc':
            $sql .= " ORDER BY l.titulo ASC";
            break;
        case 'titulo_desc':
            $sql .= " ORDER BY l.titulo DESC";
            break;
        case 'autor':
            $sql .= " ORDER BY a.apellido ASC, a.nombre ASC";
            break;
        default:
            $sql .= " ORDER BY l.fecha_registro DESC";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $libros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($libros);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al buscar libros']);
}