<?php
//generos/buscar_libros.php
require_once '../config/config.php';
header('Content-Type: application/json');

$genero = cleanInput($_GET['genero'] ?? '');
$search = cleanInput($_GET['search'] ?? '');
$order = cleanInput($_GET['order'] ?? '');

try {
    $sql = "
        SELECT l.*, a.nombre as autor_nombre, a.apellido as autor_apellido
        FROM libros l
        JOIN autores a ON l.id_autor = a.id_autor
        JOIN generos g ON l.id_genero = g.id_genero
        WHERE g.nombre = ?
    ";
    $params = [$genero];

    if (!empty($search)) {
        $sql .= " AND (l.titulo LIKE ? OR a.nombre LIKE ? OR a.apellido LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    }

    switch ($order) {
        case 'titulo_asc':
            $sql .= " ORDER BY l.titulo ASC";
            break;
        case 'titulo_desc':
            $sql .= " ORDER BY l.titulo DESC";
            break;
        case 'autor':
            $sql .= " ORDER BY a.apellido ASC, a.nombre ASC";
            break;
        case 'recientes':
            $sql .= " ORDER BY l.fecha_registro DESC";
            break;
        default:
            $sql .= " ORDER BY l.titulo ASC";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al buscar libros']);
}