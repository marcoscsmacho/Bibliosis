<?php
// logout.php
session_start();

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la sesión
session_destroy();

// Redireccionar al inicio
header('Location: index.php');
exit;