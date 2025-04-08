
<?php
session_start();
require_once 'config/config.php';
$_SESSION['user_rol'] = 1; // Establecer temporalmente como admin
header('Location: admin/dashboard.php');
exit;
?>