<?php
// eliminar_del_carrito.php
session_start();
require_once '../config/config.php';

// Verificar que el usuario esté logueado
if (!isLoggedIn()) {
    // Si es una solicitud AJAX
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para gestionar tu carrito.']);
        exit;
    }
    
    $_SESSION['error'] = "Debes iniciar sesión para gestionar tu carrito.";
    header('Location: ../login.php');
    exit;
}

// Verificar que se proporcionó un ID de carrito
if (!isset($_POST['id_carrito'])) {
    // Si es una solicitud AJAX
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        echo json_encode(['success' => false, 'message' => 'No se especificó un ítem del carrito.']);
        exit;
    }
    
    header('Location: ../vistas/carrito/index.php');
    exit;
}

$id_carrito = (int)$_POST['id_carrito'];
$id_cliente = $_SESSION['user_id'];

try {
    // Eliminar el ítem del carrito
    $stmt = $pdo->prepare("
        DELETE FROM carrito_prestamos 
        WHERE id_carrito = ? AND id_cliente = ?
    ");
    $stmt->execute([$id_carrito, $id_cliente]);
    
    // Verificar si se eliminó correctamente
    if ($stmt->rowCount() == 0) {
        throw new Exception("No se pudo eliminar el ítem del carrito.");
    }
    
    // Obtener la cantidad de elementos en el carrito para retornar
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM carrito_prestamos WHERE id_cliente = ?");
    $stmt->execute([$id_cliente]);
    $cantidad_carrito = $stmt->fetchColumn();

    // Si es una solicitud AJAX
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        echo json_encode([
            'success' => true, 
            'message' => 'Libro eliminado del carrito exitosamente',
            'cantidad_carrito' => $cantidad_carrito
        ]);
        exit;
    }

    // Redireccionar al carrito
    header('Location: ../vistas/carrito/index.php?mensaje=Libro eliminado del carrito exitosamente');
    exit;

} catch (Exception $e) {
    // Si es una solicitud AJAX
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }

    // Redireccionar con mensaje de error
    header('Location: ../vistas/carrito/index.php?error=' . urlencode($e->getMessage()));
    exit;
}