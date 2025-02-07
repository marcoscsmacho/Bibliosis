<?php
//procesar_ayuda.php
session_start();
require_once 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $asunto = cleanInput($_POST['asunto']);
    $mensaje = cleanInput($_POST['mensaje']);
    
    try {
       
        
        // Establecer el mensaje en la sesión
        $_SESSION['mensaje'] = "Tu mensaje ha sido enviado. Te contactaremos pronto.";
        
        // Redireccionar de vuelta a la página de ayuda
        header('Location: ayuda.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Hubo un error al enviar el mensaje. Por favor, intenta de nuevo.";
        header('Location: ayuda.php');
        exit;
    }
}

//  archivo sin POST
header('Location: ayuda.php');
exit;
?>