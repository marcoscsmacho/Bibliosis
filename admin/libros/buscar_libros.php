<?php
// admin/libros/buscar_libros.php
session_start();
require_once '../../config/config.php';

// Verificar permisos
if (!isLoggedIn() || (!isAdmin() && !isBibliotecario())) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Obtener el término de búsqueda
$busqueda = isset($_GET['q']) ? cleanInput($_GET['q']) : '';

try {
    // Construir la consulta de búsqueda
    $sql = "
        SELECT l.*, a.nombre as autor_nombre, a.apellido as autor_apellido, 
               g.nombre as genero_nombre, c.nombre as categoria_nombre
        FROM libros l
        LEFT JOIN autores a ON l.id_autor = a.id_autor
        LEFT JOIN generos g ON l.id_genero = g.id_genero
        LEFT JOIN categorias c ON l.id_categoria = c.id_categoria
        WHERE 1=1
    ";
    
    $params = [];
    
    // Aplicar filtro de búsqueda si se proporcionó
    if (!empty($busqueda)) {
        $sql .= " AND (l.titulo LIKE ? 
                     OR a.nombre LIKE ? 
                     OR a.apellido LIKE ?
                     OR l.isbn LIKE ?
                     OR g.nombre LIKE ?)";
        $busquedaParam = "%$busqueda%";
        $params = array_merge($params, [$busquedaParam, $busquedaParam, $busquedaParam, $busquedaParam, $busquedaParam]);
    }
    
    $sql .= " ORDER BY l.fecha_registro DESC";
    
    // Ejecutar la consulta
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $libros = $stmt->fetchAll();
    
    // Devolver los resultados como JSON
    echo json_encode($libros);
    
} catch(PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Error al buscar libros']);
}