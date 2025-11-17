<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/includes/config.php';

// Try to load Composer autoload for PhpSpreadsheet
$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Library PhpSpreadsheet tidak ditemukan.\n";
    echo "Jalankan di folder project: composer require phpoffice/phpspreadsheet\n";
    echo "Setelah itu buka kembali endpoint ini.\n";
    exit;
}
require_once $autoload;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// Ambil data
$stmt = $pdo->query("SELECT i.id_barang AS id, i.nama_barang AS name, i.tanggal_masuk AS tanggal_masuk, c.nama_kategori AS category_name, i.stock, i.harga AS price 
                    FROM barang i 
                    LEFT JOIN kategori c ON i.id_kategori = c.id");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Inventaris');

// Header
$headers = ['ID','Nama Barang','Tanggal Masuk','Kategori','Stok','Harga'];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . '1', $h);
    $sheet->getStyle($col . '1')->getFont()->setBold(true);
    $col++;
}

// Data rows
$rowNum = 2;
foreach ($items as $item) {
    $sheet->setCellValue('A' . $rowNum, $item['id']);
    $sheet->setCellValue('B' . $rowNum, $item['name']);

    // tanggal: attempt to convert to Excel date
    $dateStr = $item['tanggal_masuk'];
    if (!empty($dateStr) && $dateStr !== '0000-00-00') {
        $ts = strtotime($dateStr);
        if ($ts !== false) {
            $excelDateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($ts);
            $sheet->setCellValue('C' . $rowNum, $excelDateValue);
            $sheet->getStyle('C' . $rowNum)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
        } else {
            $sheet->setCellValue('C' . $rowNum, $dateStr);
        }
    } else {
        $sheet->setCellValue('C' . $rowNum, '');
    }

    $sheet->setCellValue('D' . $rowNum, $item['category_name']);

    // stock as integer
    $sheet->setCellValue('E' . $rowNum, is_numeric($item['stock']) ? (int)$item['stock'] : $item['stock']);
    $sheet->getStyle('E' . $rowNum)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);

    // price as numeric, format as currency (IDR-like)
    if (is_numeric($item['price'])) {
        $sheet->setCellValue('F' . $rowNum, $item['price'] + 0);
        $sheet->getStyle('F' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
    } else {
        $sheet->setCellValue('F' . $rowNum, $item['price']);
    }

    $rowNum++;
}

// Autosize columns A..F
foreach (range('A','F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Prepare download
$filename = 'inventaris_' . date('Y-m-d') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
