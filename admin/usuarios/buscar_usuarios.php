<?php
// admin/usuarios/buscar_usuarios.php
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
        SELECT c.*, 
               (SELECT COUNT(*) FROM prestamos p WHERE p.id_cliente = c.id_cliente AND p.estado = 'Prestado') 
               as prestamos_activos
        FROM clientes c
        WHERE 1=1
    ";
    
    $params = [];
    
    // Aplicar filtro de búsqueda si se proporcionó
    if (!empty($busqueda)) {
        $sql .= " AND (c.nombre LIKE ? 
                     OR c.apellido LIKE ?
                     OR c.email LIKE ?
                     OR c.telefono LIKE ?
                     OR CONCAT(c.nombre, ' ', c.apellido) LIKE ?)";
        $busquedaParam = "%$busqueda%";
        $params = array_merge($params, [$busquedaParam, $busquedaParam, $busquedaParam, $busquedaParam, $busquedaParam]);
    }
    
    $sql .= " ORDER BY c.fecha_registro DESC";
    
    // Ejecutar la consulta
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll();
    
    // Devolver los resultados como JSON
    echo json_encode($usuarios);
    
} catch(PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Error al buscar usuarios']);
}