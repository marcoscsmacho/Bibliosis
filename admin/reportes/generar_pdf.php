<?php
//admin/reportes/generar_pdf.php
session_start();
require_once '../../config/config.php';
require_once '../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Verificar permisos
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../login.php');
    exit;
}

try {
    // Obtener datos para el reporte
    // Préstamos activos
    $stmt = $pdo->query("
        SELECT p.*, l.titulo as libro_titulo, 
               CONCAT(c.nombre, ' ', c.apellido) as usuario_nombre,
               DATE_FORMAT(p.fecha_prestamo, '%d/%m/%Y') as fecha_prestamo_formateada,
               DATE_FORMAT(p.fecha_devolucion_esperada, '%d/%m/%Y') as fecha_devolucion_formateada,
               DATEDIFF(fecha_devolucion_esperada, CURRENT_DATE) as dias_restantes
        FROM prestamos p
        JOIN libros l ON p.id_libro = l.id_libro
        JOIN clientes c ON p.id_cliente = c.id_cliente
        WHERE p.estado = 'Prestado'
        ORDER BY p.fecha_prestamo DESC
    ");
    $prestamos = $stmt->fetchAll();

    // Libros más prestados
    $stmt = $pdo->query("
        SELECT l.titulo, 
               CONCAT(a.nombre, ' ', a.apellido) as autor,
               COUNT(p.id_prestamo) as total_prestamos,
               l.cantidad_disponible
        FROM libros l
        LEFT JOIN prestamos p ON l.id_libro = p.id_libro
        LEFT JOIN autores a ON l.id_autor = a.id_autor
        GROUP BY l.id_libro
        ORDER BY total_prestamos DESC
        LIMIT 10
    ");
    $libros_populares = $stmt->fetchAll();

    // Estadísticas generales
    $stmt = $pdo->query("SELECT COUNT(*) FROM prestamos WHERE estado = 'Prestado'");
    $prestamos_activos = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM libros");
    $total_libros = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM clientes WHERE estado = 'Activo'");
    $usuarios_activos = $stmt->fetchColumn();

    $stmt = $pdo->query("
        SELECT COUNT(*) FROM prestamos 
        WHERE estado = 'Prestado' 
        AND fecha_devolucion_esperada < CURRENT_DATE
    ");
    $prestamos_atrasados = $stmt->fetchColumn();

    // Crear el contenido HTML para el PDF
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Reporte BiblioTech</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                line-height: 1.4;
                color: #333;
            }
            .header {
                text-align: center;
                padding: 20px 0;
                border-bottom: 2px solid #4a5568;
                margin-bottom: 30px;
            }
            .header h1 {
                color: #2d3748;
                font-size: 24px;
                margin: 0 0 10px 0;
            }
            .fecha-reporte {
                color: #718096;
                font-size: 14px;
            }
            .estadisticas {
                display: flex;
                justify-content: space-between;
                margin: 20px 0;
                padding: 15px;
                background-color: #f8fafc;
                border-radius: 8px;
            }
            .estadistica {
                text-align: center;
                padding: 10px;
            }
            .numero {
                font-size: 20px;
                font-weight: bold;
                color: #2d3748;
                margin-bottom: 5px;
            }
            .etiqueta {
                font-size: 12px;
                color: #718096;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            th {
                background-color: #4a5568;
                color: white;
                text-align: left;
                padding: 10px;
                font-size: 12px;
            }
            td {
                padding: 8px;
                border-bottom: 1px solid #e2e8f0;
                font-size: 11px;
            }
            tr:nth-child(even) {
                background-color: #f8fafc;
            }
            .seccion {
                margin: 30px 0;
                page-break-inside: avoid;
            }
            .seccion-titulo {
                color: #2d3748;
                font-size: 18px;
                margin-bottom: 15px;
                padding-bottom: 5px;
                border-bottom: 1px solid #e2e8f0;
            }
            .page-break {
                page-break-after: always;
            }
            .estado {
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 11px;
                font-weight: bold;
            }
            .estado-prestado { background-color: #fef3c7; color: #92400e; }
            .estado-atrasado { background-color: #fee2e2; color: #991b1b; }
            .footer {
                position: fixed;
                bottom: 0;
                width: 100%;
                text-align: center;
                font-size: 10px;
                color: #718096;
                padding: 10px 0;
                border-top: 1px solid #e2e8f0;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Reporte BiblioTech</h1>
            <div class="fecha-reporte">Generado el ' . date('d/m/Y H:i') . '</div>
        </div>

        <div class="seccion">
            <div class="seccion-titulo">Resumen General</div>
            <div class="estadisticas">
                <div class="estadistica">
                    <div class="numero">' . $prestamos_activos . '</div>
                    <div class="etiqueta">Préstamos Activos</div>
                </div>
                <div class="estadistica">
                    <div class="numero">' . $total_libros . '</div>
                    <div class="etiqueta">Total Libros</div>
                </div>
                <div class="estadistica">
                    <div class="numero">' . $usuarios_activos . '</div>
                    <div class="etiqueta">Usuarios Activos</div>
                </div>
                <div class="estadistica">
                    <div class="numero">' . $prestamos_atrasados . '</div>
                    <div class="etiqueta">Préstamos Atrasados</div>
                </div>
            </div>
        </div>

        <div class="seccion">
            <div class="seccion-titulo">Préstamos Activos</div>
            <table>
                <thead>
                    <tr>
                        <th>Libro</th>
                        <th>Usuario</th>
                        <th>Fecha Préstamo</th>
                        <th>Fecha Devolución</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>';
                foreach ($prestamos as $prestamo) {
                    $estado_class = $prestamo['dias_restantes'] < 0 ? 'estado-atrasado' : 'estado-prestado';
                    $estado_texto = $prestamo['dias_restantes'] < 0 ? 'Atrasado' : 'Prestado';
                    
                    $html .= '
                    <tr>
                        <td>' . htmlspecialchars($prestamo['libro_titulo']) . '</td>
                        <td>' . htmlspecialchars($prestamo['usuario_nombre']) . '</td>
                        <td>' . $prestamo['fecha_prestamo_formateada'] . '</td>
                        <td>' . $prestamo['fecha_devolucion_formateada'] . '</td>
                        <td><span class="estado ' . $estado_class . '">' . $estado_texto . '</span></td>
                    </tr>';
                }
                $html .= '
                </tbody>
            </table>
        </div>

        <div class="page-break"></div>

        <div class="seccion">
            <div class="seccion-titulo">Libros Más Prestados</div>
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Total Préstamos</th>
                        <th>Disponibles</th>
                    </tr>
                </thead>
                <tbody>';
                foreach ($libros_populares as $libro) {
                    $html .= '
                    <tr>
                        <td>' . htmlspecialchars($libro['titulo']) . '</td>
                        <td>' . htmlspecialchars($libro['autor']) . '</td>
                        <td>' . $libro['total_prestamos'] . '</td>
                        <td>' . $libro['cantidad_disponible'] . '</td>
                    </tr>';
                }
                $html .= '
                </tbody>
            </table>
        </div>

        <div class="footer">
            Página {PAGENO} de {nb}
        </div>
    </body>
    </html>';

    // Configurar DOMPDF
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $options->set('defaultFont', 'Arial');

    // Crear instancia de DOMPDF
    $dompdf = new Dompdf($options);
    $dompdf->setPaper('A4', 'portrait');

    // Cargar HTML
    $dompdf->loadHtml($html);

    // Renderizar PDF
    $dompdf->render();

    // Enviar el PDF al navegador
    $dompdf->stream('Reporte_BiblioTech_' . date('Y-m-d') . '.pdf', [
        'Attachment' => true
    ]);

} catch(Exception $e) {
    error_log($e->getMessage());
    header('Location: index.php?error=Error al generar el PDF');
    exit;
}