<?php
// admin/Autores/buscar_autores.php
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
        SELECT a.*, 
               (SELECT COUNT(*) FROM libros l WHERE l.id_autor = a.id_autor) as total_libros
        FROM autores a
        WHERE 1=1
    ";
    
    $params = [];
    
    // Aplicar filtro de búsqueda si se proporcionó
    if (!empty($busqueda)) {
        $sql .= " AND (a.nombre LIKE ? 
                     OR a.apellido LIKE ?
                     OR a.nacionalidad LIKE ?
                     OR CONCAT(a.nombre, ' ', a.apellido) LIKE ?)";
        $busquedaParam = "%$busqueda%";
        $params = array_merge($params, [$busquedaParam, $busquedaParam, $busquedaParam, $busquedaParam]);
    }
    
    $sql .= " ORDER BY a.apellido ASC";
    
    // Ejecutar la consulta
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $autores = $stmt->fetchAll();
    
    // Devolver los resultados como JSON
    echo json_encode($autores);
    
} catch(PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Error al buscar autores']);
}