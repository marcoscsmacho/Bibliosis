<?php
// perfil.php
session_start();
require_once 'config/config.php';

// Verificar si el usuario está logueado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Obtener datos del usuario según su rol
try {
    if (isCliente()) {
        // Buscar en la tabla clientes
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $usuario = $stmt->fetch();
        $es_cliente = true;
    } else {
        // Buscar en la tabla usuarios
        $stmt = $pdo->prepare("SELECT u.*, r.nombre_rol 
                          FROM usuarios u 
                          JOIN roles r ON u.id_rol = r.id_rol 
                          WHERE u.id_usuario = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $usuario = $stmt->fetch();
        $es_cliente = false;
    }

    if (!$usuario) {
        throw new Exception("Error al obtener datos del usuario");
    }
} catch(PDOException $e) {
    error_log($e->getMessage());
    $error = "Error al obtener datos del usuario";
}

// Procesar el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = cleanInput($_POST['nombre']);
    $apellido = cleanInput($_POST['apellido']);
    $email = cleanInput($_POST['email']);
    $telefono = isset($_POST['telefono']) ? cleanInput($_POST['telefono']) : null;
    $direccion = isset($_POST['direccion']) ? cleanInput($_POST['direccion']) : null;
    $password_actual = $_POST['password_actual'] ?? '';
    $password_nuevo = $_POST['password_nuevo'] ?? '';
    
    try {
        if ($es_cliente) {
            // Actualizar cliente
            if (!empty($password_actual) && !empty($password_nuevo)) {
                // Verificar contraseña actual
                if (password_verify($password_actual, $usuario['password'])) {
                    $stmt = $pdo->prepare("UPDATE clientes 
                                         SET nombre = ?, apellido = ?, email = ?, 
                                             telefono = ?, direccion = ?, password = ? 
                                         WHERE id_cliente = ?");
                    $stmt->execute([
                        $nombre, $apellido, $email, $telefono, $direccion,
                        password_hash($password_nuevo, PASSWORD_DEFAULT),
                        $_SESSION['user_id']
                    ]);
                } else {
                    $error = "La contraseña actual es incorrecta";
                }
            } else {
                $stmt = $pdo->prepare("UPDATE clientes 
                                     SET nombre = ?, apellido = ?, email = ?, 
                                         telefono = ?, direccion = ?
                                     WHERE id_cliente = ?");
                $stmt->execute([$nombre, $apellido, $email, $telefono, $direccion, $_SESSION['user_id']]);
            }
        } else {
            // Actualizar usuario (admin/bibliotecario)
            if (!empty($password_actual) && !empty($password_nuevo)) {
                if (password_verify($password_actual, $usuario['password'])) {
                    $stmt = $pdo->prepare("UPDATE usuarios 
                                         SET nombre = ?, apellido = ?, email = ?, password = ? 
                                         WHERE id_usuario = ?");
                    $stmt->execute([
                        $nombre,
                        $apellido,
                        $email,
                        password_hash($password_nuevo, PASSWORD_DEFAULT),
                        $_SESSION['user_id']
                    ]);
                } else {
                    $error = "La contraseña actual es incorrecta";
                }
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios 
                                     SET nombre = ?, apellido = ?, email = ? 
                                     WHERE id_usuario = ?");
                $stmt->execute([$nombre, $apellido, $email, $_SESSION['user_id']]);
            }
        }
        
        if (!isset($error)) {
            $_SESSION['user_nombre'] = $nombre; // Actualizar el nombre en la sesión
            $mensaje = "Perfil actualizado correctamente";
            
            // Recargar datos del usuario
            if ($es_cliente) {
                $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
                $stmt->execute([$_SESSION['user_id']]);
            } else {
                $stmt = $pdo->prepare("SELECT u.*, r.nombre_rol 
                                     FROM usuarios u 
                                     JOIN roles r ON u.id_rol = r.id_rol 
                                     WHERE u.id_usuario = ?");
                $stmt->execute([$_SESSION['user_id']]);
            }
            $usuario = $stmt->fetch();
        }
    } catch(PDOException $e) {
        error_log($e->getMessage());
        $error = "Error al actualizar el perfil";
    }
}

$pageTitle = 'Mi Perfil - BiblioTech';
require_once 'modules/header.php';
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Mi Perfil</h1>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($mensaje)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6" id="profileForm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Nombre
                    </label>
                    <input type="text" 
                           name="nombre" 
                           value="<?php echo htmlspecialchars($usuario['nombre']); ?>" 
                           required
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Apellido
                    </label>
                    <input type="text" 
                           name="apellido" 
                           value="<?php echo htmlspecialchars($usuario['apellido']); ?>" 
                           required
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Email
                    </label>
                    <input type="email" 
                           name="email" 
                           value="<?php echo htmlspecialchars($usuario['email']); ?>" 
                           required
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                </div>

                <?php if ($es_cliente): ?>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Teléfono
                        </label>
                        <input type="tel" 
                               name="telefono" 
                               value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Dirección
                        </label>
                        <textarea name="direccion" 
                                  class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600"
                                  rows="3"><?php echo htmlspecialchars($usuario['direccion'] ?? ''); ?></textarea>
                    </div>
                <?php else: ?>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Rol
                        </label>
                        <input type="text" 
                               value="<?php echo htmlspecialchars($usuario['nombre_rol']); ?>" 
                               readonly
                               class="w-full px-3 py-2 bg-gray-100 border rounded-lg">
                    </div>
                <?php endif; ?>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Cambiar Contraseña</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Contraseña Actual
                        </label>
                        <input type="password" 
                               name="password_actual"
                               id="password_actual"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                        <div id="current-password-feedback" class=""></div>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Nueva Contraseña
                        </label>
                        <input type="password" 
                               name="password_nuevo"
                               id="password_nuevo"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                        <div id="new-password-feedback" class=""></div>
                        <p class="text-sm text-gray-500 mt-1">
                            La contraseña debe tener al menos 8 caracteres.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="index.php" 
                   class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                    Cancelar
                </a>
                <button type="submit" 
                        id="submitButton"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script src="<?php echo $basePath; ?>js/profile-validation.js"></script>

<?php require_once 'modules/footer.php'; ?>