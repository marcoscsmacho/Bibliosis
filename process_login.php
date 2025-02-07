<?php
// process_login.php
require_once 'config/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    $error = null;

    try {
        // Debug: Imprimir los datos recibidos
        error_log("Intento de login - Email: " . $email);
        
        // buscar en la tabla de clientes
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE email = ?");
        $stmt->execute([$email]);
        $cliente = $stmt->fetch();

        // Debug: Verificar si encontramos el cliente
        error_log("Cliente encontrado: " . ($cliente ? "SI" : "NO"));
        if ($cliente) {
            error_log("Estado del cliente: " . $cliente['estado']);
            error_log("Password guardado: " . $cliente['password']);
        }

        if ($cliente && $cliente['estado'] === 'Activo' && password_verify($password, $cliente['password'])) {
            // Login exitoso como cliente
            $_SESSION['user_id'] = $cliente['id_cliente'];
            $_SESSION['user_nombre'] = $cliente['nombre'];
            $_SESSION['user_rol'] = 'cliente';
            header('Location: index.php');
            exit;
        } else {
            // Si no es cliente, buscamos en la tabla usuarios
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($password, $usuario['password'])) {
                // Login exitoso como admin/bibliotecario
                $_SESSION['user_id'] = $usuario['id_usuario'];
                $_SESSION['user_nombre'] = $usuario['nombre'];
                $_SESSION['user_rol'] = $usuario['id_rol'];
                
                if ($usuario['id_rol'] == ROL_ADMIN || $usuario['id_rol'] == ROL_BIBLIOTECARIO) {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                $error = "Credenciales incorrectas";
            }
        }
    } catch(PDOException $e) {
        error_log("Error de BD: " . $e->getMessage());
        $error = "Error al procesar el login";
    }

    if ($error) {
        $_SESSION['error'] = $error;
        header('Location: login.php');
        exit;
    }
}