<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';

// Ambil data untuk kartu statistik
$totalbarang = $pdo->query("SELECT COUNT(*) FROM barang")->fetchColumn();
$totalStock = $pdo->query("SELECT SUM(stock) FROM barang")->fetchColumn();
$totalkategori = $pdo->query("SELECT COUNT(*) FROM kategori")->fetchColumn();

// Pagination
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Ambil data barang dengan pagination dan urutkan dari yang terbaru
$stmt = $pdo->prepare("SELECT i.*, i.id_barang AS id, i.nama_barang AS name, i.harga AS price, i.tanggal_masuk, c.nama_kategori AS category_name 
                      FROM barang i 
                      LEFT JOIN kategori c ON i.id_kategori = c.id 
                      ORDER BY i.id_barang DESC 
                      LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$barang = $stmt->fetchAll();

// Hitung total halaman
$totalbarangResult = $pdo->query("SELECT COUNT(*) FROM barang")->fetchColumn();
$totalPages = ceil($totalbarangResult / $limit);

// Ambil data untuk grafik (5 stok tertinggi)
$chartData = $pdo->query("SELECT i.nama_barang AS name, i.stock FROM barang i WHERE i.stock > 0 ORDER BY i.stock DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Kartu Statistik -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary">
            <div class="card-body d-flex justify-content-between align-barang-center">
                <div>
                    <h5 class="card-title">Total Barang</h5>
                    <p class="card-text fs-2 fw-bold"><?= $totalbarang ?></p>
                </div>
                <i class="bi bi-box-seam" style="font-size: 3rem; opacity: 0.5;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body d-flex justify-content-between align-barang-center">
                <div>
                    <h5 class="card-title">Total Stok</h5>
                    <p class="card-text fs-2 fw-bold"><?= $totalStock ?? 0 ?></p>
                </div>
                <i class="bi bi-stack" style="font-size: 3rem; opacity: 0.5;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info">
            <div class="card-body d-flex justify-content-between align-barang-center">
                <div>
                    <h5 class="card-title">Jumlah Kategori</h5>
                    <p class="card-text fs-2 fw-bold"><?= $totalkategori ?></p>
                </div>
                <i class="bi bi-tags-fill" style="font-size: 3rem; opacity: 0.5;"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Tabel Barang -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-ul"></i> Daftar Barang Terbaru</h5>
                <input type="text" id="search" class="form-control w-auto" placeholder="Cari barang...">
            </div>
            <div class="card-body">
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">Operasi berhasil dilakukan!</div>
                <?php endif; ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="barangTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Barang</th>
                                <th>Tanggal Masuk</th>
                                <th>Kategori</th>
                                <th>Stok</th>
                                <th>Harga</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($barang)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Belum ada barang. <a href="items.php?action=create">Tambah sekarang</a>.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($barang as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['id']) ?></td>
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    <td><?= htmlspecialchars($item['tanggal_masuk'] ?? '-') ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($item['category_name'] ?? 'N/A') ?></span></td>
                                    <td><?= htmlspecialchars($item['stock']) ?></td>
                                    <td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                                    <td class="text-center table-actions">
                                        <a href="items.php?action=edit&id=<?= $item['id'] ?>" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                                        <a href="items.php?action=delete&id=<?= $item['id'] ?>" class="btn btn-sm btn-danger" title="Hapus" onclick="return confirm('Yakin ingin menghapus barang ini?')"><i class="bi bi-trash-fill"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <!-- Tombol Previous -->
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                        </li>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <!-- Tombol Next -->
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Grafik -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart-line-fill"></i> Top 5 Stok Barang</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($chartData)): ?>
                    <canvas id="stockChart"></canvas>
                <?php else: ?>
                    <p class="text-center">Data stok tidak cukup untuk menampilkan grafik.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Grafik stok barang
        <?php if (!empty($chartData)): ?>
        const ctx = document.getElementById('stockChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($chartData, 'name')) ?>,
                datasets: [{
                    label: 'Stok',
                    data: <?= json_encode(array_column($chartData, 'stock')) ?>,
                    backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6c757d'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        <?php endif; ?>

        // Pencarian real-time
        document.getElementById('search').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#barangTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>