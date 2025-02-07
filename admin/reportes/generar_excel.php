<?php
//admin/reportes/generar_excel.php
session_start();
require_once '../../config/config.php';
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Verificar permisos
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../../login.php');
    exit;
}

try {
    // Crear nuevo documento Excel
    $spreadsheet = new Spreadsheet();
    
    // Configurar metadatos del documento
    $spreadsheet->getProperties()
        ->setCreator('BiblioTech')
        ->setLastModifiedBy('BiblioTech')
        ->setTitle('Reporte de Biblioteca')
        ->setSubject('Estadísticas y reportes de la biblioteca')
        ->setDescription('Documento generado automáticamente con estadísticas de la biblioteca');

    // Estilo para encabezados
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4A5568'],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
    ];

    // Estilo para las celdas de datos
    $dataStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ];

    // ====== HOJA 1: Préstamos Activos ======
    $activeSheet = $spreadsheet->getActiveSheet();
    $activeSheet->setTitle('Préstamos Activos');

    // Encabezados
    $activeSheet->setCellValue('A1', 'ID');
    $activeSheet->setCellValue('B1', 'Libro');
    $activeSheet->setCellValue('C1', 'Usuario');
    $activeSheet->setCellValue('D1', 'Fecha Préstamo');
    $activeSheet->setCellValue('E1', 'Fecha Devolución');
    $activeSheet->setCellValue('F1', 'Estado');
    
    // Aplicar estilo a encabezados
    $activeSheet->getStyle('A1:F1')->applyFromArray($headerStyle);

    // Obtener datos de préstamos activos
    $stmt = $pdo->query("
        SELECT p.*, l.titulo as libro_titulo, 
               CONCAT(c.nombre, ' ', c.apellido) as usuario_nombre
        FROM prestamos p
        JOIN libros l ON p.id_libro = l.id_libro
        JOIN clientes c ON p.id_cliente = c.id_cliente
        WHERE p.estado = 'Prestado'
        ORDER BY p.fecha_prestamo DESC
    ");
    $prestamos = $stmt->fetchAll();

    // Llenar datos
    $row = 2;
    foreach ($prestamos as $prestamo) {
        $activeSheet->setCellValue('A' . $row, $prestamo['id_prestamo']);
        $activeSheet->setCellValue('B' . $row, $prestamo['libro_titulo']);
        $activeSheet->setCellValue('C' . $row, $prestamo['usuario_nombre']);
        $activeSheet->setCellValue('D' . $row, date('d/m/Y', strtotime($prestamo['fecha_prestamo'])));
        $activeSheet->setCellValue('E' . $row, date('d/m/Y', strtotime($prestamo['fecha_devolucion_esperada'])));
        $activeSheet->setCellValue('F' . $row, $prestamo['estado']);
        $row++;
    }

    // Aplicar estilo a los datos
    $activeSheet->getStyle('A2:F' . ($row-1))->applyFromArray($dataStyle);
    
    // Ajustar ancho de columnas
    foreach(range('A','F') as $col) {
        $activeSheet->getColumnDimension($col)->setAutoSize(true);
    }

    // ====== HOJA 2: Libros Más Prestados ======
    $spreadsheet->createSheet();
    $popularSheet = $spreadsheet->getSheet(1);
    $popularSheet->setTitle('Libros Populares');

    // Encabezados
    $popularSheet->setCellValue('A1', 'Libro');
    $popularSheet->setCellValue('B1', 'Autor');
    $popularSheet->setCellValue('C1', 'Total Préstamos');
    $popularSheet->setCellValue('D1', 'Disponibilidad');
    
    // Aplicar estilo a encabezados
    $popularSheet->getStyle('A1:D1')->applyFromArray($headerStyle);

    // Obtener datos de libros populares
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
    ");
    $libros = $stmt->fetchAll();

    // Llenar datos
    $row = 2;
    foreach ($libros as $libro) {
        $popularSheet->setCellValue('A' . $row, $libro['titulo']);
        $popularSheet->setCellValue('B' . $row, $libro['autor']);
        $popularSheet->setCellValue('C' . $row, $libro['total_prestamos']);
        $popularSheet->setCellValue('D' . $row, $libro['cantidad_disponible']);
        $row++;
    }

    // Aplicar estilo a los datos
    $popularSheet->getStyle('A2:D' . ($row-1))->applyFromArray($dataStyle);
    
    // Ajustar ancho de columnas
    foreach(range('A','D') as $col) {
        $popularSheet->getColumnDimension($col)->setAutoSize(true);
    }

    // ====== HOJA 3: Estadísticas Mensuales ======
    $spreadsheet->createSheet();
    $statsSheet = $spreadsheet->getSheet(2);
    $statsSheet->setTitle('Estadísticas Mensuales');

    // Encabezados
    $statsSheet->setCellValue('A1', 'Mes');
    $statsSheet->setCellValue('B1', 'Total Préstamos');
    $statsSheet->setCellValue('C1', 'Préstamos Atrasados');
    $statsSheet->setCellValue('D1', 'Devoluciones a Tiempo');
    
    // Aplicar estilo a encabezados
    $statsSheet->getStyle('A1:D1')->applyFromArray($headerStyle);

    // Obtener estadísticas mensuales
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(fecha_prestamo, '%Y-%m') as mes,
            COUNT(*) as total_prestamos,
            SUM(CASE WHEN estado = 'Atrasado' THEN 1 ELSE 0 END) as atrasados,
            SUM(CASE 
                WHEN estado = 'Devuelto' 
                AND fecha_devolucion_real <= fecha_devolucion_esperada 
                THEN 1 ELSE 0 END) as a_tiempo
        FROM prestamos
        GROUP BY DATE_FORMAT(fecha_prestamo, '%Y-%m')
        ORDER BY mes DESC
        LIMIT 12
    ");
    $estadisticas = $stmt->fetchAll();

    // Llenar datos
    $row = 2;
    foreach ($estadisticas as $stat) {
        $statsSheet->setCellValue('A' . $row, date('M Y', strtotime($stat['mes'] . '-01')));
        $statsSheet->setCellValue('B' . $row, $stat['total_prestamos']);
        $statsSheet->setCellValue('C' . $row, $stat['atrasados']);
        $statsSheet->setCellValue('D' . $row, $stat['a_tiempo']);
        $row++;
    }

    // Aplicar estilo a los datos
    $statsSheet->getStyle('A2:D' . ($row-1))->applyFromArray($dataStyle);
    
    // Ajustar ancho de columnas
    foreach(range('A','D') as $col) {
        $statsSheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Establecer la primera hoja como activa
    $spreadsheet->setActiveSheetIndex(0);

    // Generar el archivo Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Reporte_Biblioteca_' . date('Y-m-d') . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch(Exception $e) {
    error_log($e->getMessage());
    header('Location: index.php?error=Error al generar el reporte');
    exit;
}