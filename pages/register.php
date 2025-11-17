<?php
$pageTitle = 'Register';
require_once __DIR__ . '/../includes/header.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    if ($username === '' || $password === '' || $password2 === '') {
        $error = 'Semua kolom wajib diisi.';
    } elseif ($password !== $password2) {
        $error = 'Password tidak cocok.';
    } else {
        // Cek apakah username sudah ada
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Username sudah terdaftar.';
        } else {
                // Simpan user baru (level default: user)
                // Menyimpan password tanpa hashing sesuai permintaan (TIDAK DIREKOMENDASIKAN untuk produksi)
                $stmt = $pdo->prepare('INSERT INTO users (username, level, password) VALUES (?, ?, ?)');
                $stmt->execute([$username, 'user', $password]);
            header('Location: ../index.php?registered=1');
            exit;
        }
    }
}
?>

<div class="login-container">
    <div class="card login-card">
        <div class="card-header">
            <h3>Register</h3>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="register.php">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password2" class="form-label">Ulangi Password</label>
                    <input type="password" name="password2" id="password2" class="form-control" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Daftar</button>
                </div>
                <div style="margin-top:0.75rem; text-align:center;">
                    <a href="../index.php">Kembali ke Login</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
