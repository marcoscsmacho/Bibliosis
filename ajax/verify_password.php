<?php
// ajax/verify_password.php
session_start();
require_once '../config/config.php';
header('Content-Type: application/json');

// Verificar que el usuario esté logueado
if (!isLoggedIn()) {
    echo json_encode(['valid' => false, 'error' => 'No hay sesión activa']);
    exit;
}

// Verificar que se recibió la acción y contraseña correctas
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'verify_password' || !isset($_POST['current_password'])) {
    echo json_encode(['valid' => false, 'error' => 'Solicitud inválida']);
    exit;
}

$currentPassword = $_POST['current_password'];
$userId = $_SESSION['user_id'];
$isValid = false;

try {
    // Determinar si es cliente o personal
    if (isCliente()) {
        // Consultar la contraseña actual del cliente
        $stmt = $pdo->prepare("SELECT password FROM clientes WHERE id_cliente = ?");
        $stmt->execute([$userId]);
    } else {
        // Consultar la contraseña actual del usuario (bibliotecario/admin)
        $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$userId]);
    }
    
    $user = $stmt->fetch();
    
    if ($user && password_verify($currentPassword, $user['password'])) {
        $isValid = true;
    }
    
    echo json_encode(['valid' => $isValid]);
    
} catch (PDOException $e) {
    error_log("Error al verificar contraseña: " . $e->getMessage());
    echo json_encode(['valid' => false, 'error' => 'Error al verificar la contraseña']);
}