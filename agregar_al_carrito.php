<?php
// agregar_al_carrito.php
session_start();
require_once 'config/config.php';

// Verificar que el usuario esté logueado
if (!isLoggedIn()) {
    $_SESSION['error'] = "Debes iniciar sesión para agregar libros al carrito.";
    
    // Si es una solicitud AJAX
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para agregar libros al carrito.']);
        exit;
    }
    
    header('Location: ../login.php');
    exit;
}

// Verificar que se proporcionó un ID de libro
if (!isset($_POST['id_libro'])) {
    
    // Si es una solicitud AJAX
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        echo json_encode(['success' => false, 'message' => 'No se especificó un libro.']);
        exit;
    }
    
    header('Location: ../vistas/catalogo/index.php');
    exit;
}

$id_libro = (int)$_POST['id_libro'];
$id_cliente = $_SESSION['user_id'];

try {
    // Verificar si el libro existe y está disponible
    $stmt = $pdo->prepare("
        SELECT titulo, cantidad_disponible 
        FROM libros 
        WHERE id_libro = ?
    ");
    $stmt->execute([$id_libro]);
    $libro = $stmt->fetch();

    if (!$libro) {
        throw new Exception("El libro no existe.");
    }

    if ($libro['cantidad_disponible'] <= 0) {
        throw new Exception("El libro no está disponible para préstamo.");
    }

    // Verificar si el usuario ya tiene este libro en el carrito
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM carrito_prestamos 
        WHERE id_cliente = ? AND id_libro = ?
    ");
    $stmt->execute([$id_cliente, $id_libro]);
    
    // Almacenar el resultado de la consulta en una variable
    $count = $stmt->fetchColumn();
    
    // Debug - Registrar la consulta SQL y sus parámetros
    error_log("Verificando duplicados - id_cliente: $id_cliente, id_libro: $id_libro");
    error_log("Resultado verificación: " . $count);
    
    if ($count > 0) {
        throw new Exception("Este libro ya está en tu carrito.");
    }

    // Verificar si el usuario ya tiene este libro prestado
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM prestamos 
        WHERE id_cliente = ? AND id_libro = ? AND estado = 'Prestado'
    ");
    $stmt->execute([$id_cliente, $id_libro]);
    
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Ya tienes este libro en préstamo.");
    }

    // Verificar la cantidad de elementos en el carrito + préstamos activos
    $stmt = $pdo->prepare("
        SELECT 
            (SELECT COUNT(*) FROM carrito_prestamos WHERE id_cliente = ?) +
            (SELECT COUNT(*) FROM prestamos WHERE id_cliente = ? AND estado = 'Prestado') as total
    ");
    $stmt->execute([$id_cliente, $id_cliente]);
    $total = $stmt->fetchColumn();

    // Configuración del máximo de préstamos permitidos (3 por defecto)
    $stmt = $pdo->prepare("SELECT max_prestamos_usuario FROM configuracion WHERE id = 1");
    $stmt->execute();
    $max_prestamos = $stmt->fetchColumn() ?: 3;

    if ($total >= $max_prestamos) {
        throw new Exception("No puedes agregar más libros. Has alcanzado el límite de préstamos permitidos.");
    }

    // Agregar al carrito
    try {
        $stmt = $pdo->prepare("
            INSERT INTO carrito_prestamos (id_cliente, id_libro)
            VALUES (?, ?)
        ");
        $result = $stmt->execute([$id_cliente, $id_libro]);
        error_log("Inserción en carrito - Resultado: " . ($result ? "Éxito" : "Fallo"));
        
        if (!$result) {
            throw new Exception("Error al agregar el libro al carrito.");
        }
    } catch (PDOException $e) {
        error_log("Error al insertar en carrito: " . $e->getMessage());
        throw new Exception("Error al agregar el libro al carrito: " . $e->getMessage());
    }

    // Verificar después de la inserción que el libro está en el carrito
    $stmt = $pdo->prepare("
        SELECT * 
        FROM carrito_prestamos 
        WHERE id_cliente = ? AND id_libro = ?
    ");
    $stmt->execute([$id_cliente, $id_libro]);
    $item = $stmt->fetch();
    error_log("Verificación después de inserción: " . ($item ? "Libro encontrado en carrito" : "Libro NO encontrado en carrito"));

    // Obtener la cantidad de elementos en el carrito para retornar
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM carrito_prestamos WHERE id_cliente = ?");
    $stmt->execute([$id_cliente]);
    $cantidad_carrito = $stmt->fetchColumn();

    // Si es una solicitud AJAX
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        echo json_encode([
            'success' => true, 
            'message' => 'Libro agregado al carrito exitosamente',
            'titulo_libro' => $libro['titulo'],
            'cantidad_carrito' => $cantidad_carrito
        ]);
        exit;
    }

    // Redireccionar a la página anterior o al catálogo
    $redirect = $_POST['redirect'] ?? '../vistas/catalogo/index.php';
    header("Location: $redirect?mensaje=Libro agregado al carrito exitosamente");
    exit;

} catch (Exception $e) {
    // Si es una solicitud AJAX
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }

    // Redireccionar con un mensaje de error
    $redirect = $_POST['redirect'] ?? '../vistas/catalogo/index.php';
    header("Location: $redirect?error=" . urlencode($e->getMessage()));
    exit;
}