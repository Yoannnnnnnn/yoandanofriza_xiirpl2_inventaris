<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once __DIR__ . '/includes/config.php';

// Ambil semua data barang (sesuai schema `barang` / `kategori`)
 $stmt = $pdo->query("SELECT i.id_barang AS id, i.nama_barang AS name, i.tanggal_masuk AS tanggal_masuk, c.nama_kategori AS category_name, i.stock, i.harga AS price 
                    FROM barang i 
                    LEFT JOIN kategori c ON i.id_kategori = c.id");
 $items = $stmt->fetchAll();

// Set header untuk download CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="inventaris_' . date('Y-m-d') . '.csv"');

// Buka output stream
 $output = fopen('php://output', 'w');

// Header CSV
fputcsv($output, ['ID', 'Nama Barang', 'Tanggal Masuk', 'Kategori', 'Stok', 'Harga']);

// Data CSV
foreach ($items as $item) {
    fputcsv($output, [
        $item['id'],
        $item['name'],
        $item['tanggal_masuk'],
        $item['category_name'],
        $item['stock'],
        $item['price']
    ]);
}

fclose($output);
exit;
?>