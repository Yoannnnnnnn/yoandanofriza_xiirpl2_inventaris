<?php
$pageTitle = 'Manajemen Kategori';
require_once __DIR__ . '/../includes/header.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

// CREATE
if ($action == 'create' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    if (empty($name)) {
        $error = "Nama kategori wajib diisi.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
            $stmt->execute([$name]);
            header("Location: categories.php?success=create");
            exit;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $error = "Kategori dengan nama tersebut sudah ada.";
            } else {
                $error = "Gagal menyimpan kategori.";
            }
        }
    }
}

// UPDATE
if ($action == 'update' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    try {
        $stmt = $pdo->prepare("UPDATE kategori SET nama_kategori=? WHERE id=?");
        $stmt->execute([$name, $id]);
        header("Location: categories.php?success=update");
        exit;
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $error = "Kategori dengan nama tersebut sudah ada.";
        } else {
            $error = "Gagal memperbarui kategori.";
        }
    }
}

// DELETE
if ($action == 'delete') {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM barang WHERE id_kategori=?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        $error = "Kategori tidak bisa dihapus karena sedang digunakan oleh barang.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM kategori WHERE id=?");
        $stmt->execute([$id]);
        header("Location: categories.php?success=delete");
        exit;
    }
}

// Ambil data untuk form edit
$category_to_edit = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT id, nama_kategori AS name FROM kategori WHERE id=?");
    $stmt->execute([$id]);
    $category_to_edit = $stmt->fetch();
}

// Ambil semua kategori
$categories = $pdo->query("SELECT id, nama_kategori AS name FROM kategori ORDER BY nama_kategori")->fetchAll();
?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Operasi berhasil!</div>
<?php endif; ?>

<div class="row">
    <!-- Form Tambah/Edit Kategori -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi <?= $action == 'edit' ? 'bi-pencil-fill' : 'bi-plus-circle-fill' ?>"></i>
                    <?= $action == 'edit' ? 'Edit' : 'Tambah' ?> Kategori
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="categories.php?action=<?= $action == 'edit' ? 'update' : 'create' ?>">
                    <?php if ($action == 'edit' && $category_to_edit): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($category_to_edit['id']) ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Kategori</label>
                        <input type="text" name="name" id="name" class="form-control" 
                               value="<?= htmlspecialchars($category_to_edit['name'] ?? '') ?>" required autofocus>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> <?= $action == 'edit' ? 'Update' : 'Simpan' ?>
                        </button>
                        <?php if ($action == 'edit'): ?>
                            <a href="categories.php" class="btn btn-secondary">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Daftar Kategori -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-tags-fill"></i> Daftar Kategori</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Kategori</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categories)): ?>
                                <tr>
                                    <td colspan="3" class="text-center">Belum ada kategori.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><?= htmlspecialchars($cat['id']) ?></td>
                                    <td><?= htmlspecialchars($cat['name']) ?></td>
                                    <td class="text-center table-actions">
                                        <a href="categories.php?action=edit&id=<?= $cat['id'] ?>" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                                        <a href="categories.php?action=delete&id=<?= $cat['id'] ?>" class="btn btn-sm btn-danger" title="Hapus" onclick="return confirm('Yakin ingin menghapus kategori ini?')"><i class="bi bi-trash-fill"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>