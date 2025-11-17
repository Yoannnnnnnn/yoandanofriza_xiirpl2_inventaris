<?php
$action = isset($_GET['action']) ? $_GET['action'] : 'create';
$pageTitle = ($action == 'edit' ? 'Edit' : 'Tambah') . ' Barang';
require_once __DIR__ . '/../includes/header.php';

// Proses CRUD (menggunakan tabel `barang` dan `kategori` sesuai DB dump)
// CREATE
if ($action == 'create' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $stock = $_POST['stock'];
    $price = $_POST['price'];
    $tanggal_masuk = !empty($_POST['tanggal_masuk']) ? $_POST['tanggal_masuk'] : date('Y-m-d');
    
    $errors = [];
    if (empty($name)) $errors[] = "Nama barang wajib diisi";
    if (empty($category_id)) $errors[] = "Kategori wajib dipilih";
    if (!is_numeric($stock) || $stock < 0) $errors[] = "Stok harus angka non-negatif";
    if (!is_numeric($price) || $price < 0) $errors[] = "Harga harus angka non-negatif";
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO barang (nama_barang, id_kategori, stock, harga, tanggal_masuk) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category_id, $stock, $price, $tanggal_masuk]);
        header("Location: dashboard.php?success=create");
        exit;
    }
}

// UPDATE
if ($action == 'update' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $stock = $_POST['stock'];
    $price = $_POST['price'];
    $tanggal_masuk = !empty($_POST['tanggal_masuk']) ? $_POST['tanggal_masuk'] : date('Y-m-d');
    
    $stmt = $pdo->prepare("UPDATE barang SET nama_barang=?, id_kategori=?, stock=?, harga=?, tanggal_masuk=? WHERE id_barang=?");
    $stmt->execute([$name, $category_id, $stock, $price, $tanggal_masuk, $id]);
    header("Location: dashboard.php?success=update");
    exit;
}

// DELETE
if ($action == 'delete') {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM barang WHERE id_barang=?");
    $stmt->execute([$id]);
    header("Location: dashboard.php?success=delete");
    exit;
}

// Ambil data untuk form edit
$item = null;
if ($action == 'edit') {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT *, id_barang AS id, nama_barang AS name, harga AS price, tanggal_masuk FROM barang WHERE id_barang=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) {
        $item = $row;
    }
}

// Ambil data kategori
$categories = $pdo->query("SELECT id, nama_kategori AS name FROM kategori ORDER BY nama_kategori")->fetchAll();
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pencil-square"></i> <?= ($action == 'edit' ? 'Edit' : 'Tambah') ?> Barang</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <strong>Terjadi Kesalahan:</strong>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="items.php?action=<?= ($action == 'edit' ? 'update' : 'create') ?>">
                    <?php if ($action == 'edit'): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($item['id']) ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Barang</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($item['name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Kategori</label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= (isset($item) && $item['id_kategori'] == $category['id']) || (isset($item) && ($item['category_id'] ?? '') == $category['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                        <input type="date" name="tanggal_masuk" id="tanggal_masuk" class="form-control" value="<?= htmlspecialchars($item['tanggal_masuk'] ?? date('Y-m-d')) ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="stock" class="form-label">Stok</label>
                            <input type="number" name="stock" id="stock" class="form-control" value="<?= htmlspecialchars($item['stock'] ?? '0') ?>" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Harga</label>
                            <input type="number" name="price" id="price" class="form-control" value="<?= htmlspecialchars($item['price'] ?? '0') ?>" min="0" step="100" required>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <a href="dashboard.php" class="btn btn-secondary me-2">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> <?= ($action == 'edit' ? 'Update' : 'Simpan') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>