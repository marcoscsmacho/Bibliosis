<?php
//admin/configuracion/backup.php
session_start();
require_once '../../config/config.php';

// Verificar permisos
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../login.php');
    exit;
}

try {
    // Crear el directorio de backups si no existe
    $backup_dir = __DIR__ . '/backups';
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0777, true);
    }

    // Nombre del archivo
    $fecha = date('Y-m-d_H-i-s');
    $filename = "backup_biblioteca_{$fecha}.sql";
    $filepath = $backup_dir . '/' . $filename;

    // Iniciar el buffer de salida
    ob_start();

    // Obtener la estructura de las tablas
    $tablas = [];
    $result = $pdo->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tablas[] = $row[0];
    }

    // Generar el contenido del backup
    $output = "-- Backup de la base de datos BiblioTech\n";
    $output .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n\n";
    $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    // Procesar cada tabla
    foreach ($tablas as $tabla) {
        // Obtener la estructura de la tabla
        $result = $pdo->query("SHOW CREATE TABLE $tabla");
        $row = $result->fetch(PDO::FETCH_NUM);
        
        $output .= "\n\n-- Estructura de la tabla `$tabla`\n";
        $output .= "DROP TABLE IF EXISTS `$tabla`;\n";
        $output .= $row[1] . ";\n\n";
        
        // Obtener los datos
        $result = $pdo->query("SELECT * FROM $tabla");
        $num_fields = $result->columnCount();
        
        $output .= "-- Datos de la tabla `$tabla`\n";
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $output .= "INSERT INTO `$tabla` VALUES(";
            for ($i = 0; $i < $num_fields; $i++) {
                if (isset($row[$i])) {
                    $row[$i] = addslashes($row[$i]);
                    $row[$i] = str_replace("\n", "\\n", $row[$i]);
                    $output .= '"' . $row[$i] . '"';
                } else {
                    $output .= 'NULL';
                }
                if ($i < ($num_fields - 1)) {
                    $output .= ',';
                }
            }
            $output .= ");\n";
        }
        $output .= "\n";
    }

    $output .= "\nSET FOREIGN_KEY_CHECKS=1;\n";

    // Guardar el archivo
    file_put_contents($filepath, $output);

    // Verificar si el archivo se creó correctamente
    if (file_exists($filepath)) {
        // Configurar headers para la descarga
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($filepath));
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Expires: 0');

        // Leer y enviar el archivo
        readfile($filepath);

        // Registrar el backup en la base de datos
        $stmt = $pdo->prepare("
            INSERT INTO backups (nombre_archivo, fecha_creacion, creado_por)
            VALUES (?, NOW(), ?)
        ");
        $stmt->execute([$filename, $_SESSION['user_id']]);

        // Eliminar archivos antiguos (mantener solo los últimos 5)
        $files = glob($backup_dir . '/*.sql');
        if (count($files) > 5) {
            array_multisort(
                array_map('filemtime', $files),
                SORT_DESC,
                $files
            );
            $old_files = array_slice($files, 5);
            foreach ($old_files as $file) {
                unlink($file);
            }
        }

        exit;
    } else {
        throw new Exception("Error al crear el archivo de respaldo");
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Error al generar el respaldo: " . $e->getMessage();
    header('Location: index.php');
    exit;
}