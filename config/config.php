<?php
//config/config.php
// Configuración general
// Activar visualización de errores para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  
define('DB_PASS', '');      
define('DB_NAME', 'biblioteca_db');

// Configuración del sitio
define('SITE_NAME', 'BiblioSis');
define('SITE_URL', 'http://localhost/biblioteca');

// Definición de roles
define('ROL_ADMIN', 1);
define('ROL_BIBLIOTECARIO', 2);

// Iniciar conexión a la base de datos
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        )
    );
} catch(PDOException $e) {
    error_log("Error de conexión: " . $e->getMessage());
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// Funciones de utilidad
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Función para verificar el rol del usuario
function hasRole($roleRequired) {
    return isset($_SESSION['user_rol']) && $_SESSION['user_rol'] == $roleRequired;
}

// Función para verificar si el usuario es administrador
function isAdmin() {
    return hasRole(ROL_ADMIN);
}

// Función para verificar si el usuario es bibliotecario
function isBibliotecario() {
    return hasRole(ROL_BIBLIOTECARIO);
}

function isCliente() {
    return isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'cliente';
}

// Función para requerir inicio de sesión
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

// Función para requerir rol de administrador
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/unauthorized.php');
        exit;
    }
}

// Función para requerir rol de bibliotecario o superior
function requireBibliotecario() {
    requireLogin();
    if (!isAdmin() && !isBibliotecario()) {
        header('Location: ' . SITE_URL . '/unauthorized.php');
        exit;
    }
}

// Función para obtener el nombre del rol
function getRoleName($rolId) {
    switch ($rolId) {
        case ROL_ADMIN:
            return 'Administrador';
        case ROL_BIBLIOTECARIO:
            return 'Bibliotecario';
        default:
            return 'Usuario';
    }
}