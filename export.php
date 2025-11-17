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

// Set header untuk download CSV (UTF-8) dan kirim BOM supaya Excel mengenali encoding
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="inventaris_' . date('Y-m-d') . '.csv"');

// Keluarkan BOM UTF-8 agar Excel menampilkan karakter non-ASCII dengan benar
echo "\xEF\xBB\xBF";

// Buka output stream
 $output = fopen('php://output', 'w');

// Header CSV
fputcsv($output, ['ID', 'Nama Barang', 'Tanggal Masuk', 'Kategori', 'Stok', 'Harga']);

// Data CSV — format tanggal dd-mm-YYYY; pastikan stok & harga dikirim sebagai angka
foreach ($items as $item) {
    $tanggal = '';
    if (!empty($item['tanggal_masuk']) && $item['tanggal_masuk'] !== '0000-00-00') {
        $ts = strtotime($item['tanggal_masuk']);
        if ($ts !== false) $tanggal = date('d-m-Y', $ts);
    }

    $stock = is_numeric($item['stock']) ? (int)$item['stock'] : $item['stock'];
    $price = is_numeric($item['price']) ? ($item['price'] + 0) : $item['price'];

    fputcsv($output, [
        $item['id'],
        $item['name'],
        $tanggal,
        $item['category_name'],
        $stock,
        $price
    ]);
}

fclose($output);
exit;
?>