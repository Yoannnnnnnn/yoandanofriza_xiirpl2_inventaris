<?php
$pageTitle = 'Login';
require_once __DIR__ . '/includes/header.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: pages/dashboard.php");
    exit;
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Ambil user dari DB
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Verifikasi password: bandingkan plaintext sesuai permintaan (TIDAK DIREKOMENDASIKAN)
    $ok = false;
    if ($user) {
        if (isset($user['password']) && $password === $user['password']) {
            $ok = true;
        }
    }

    if ($ok) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: pages/dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}

// Tampilkan pesan setelah registrasi sukses
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $success = 'Registrasi berhasil. Silakan login.';
}
?>

<div class="login-container">
    <div class="card login-card">
        <div class="card-header">
            <h3><i class="bi bi-box-seam"></i> Login Inventaris</h3>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?= $error ?></div>
            <?php endif; ?>
            <form method="POST" action="index.php">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                        <input type="text" name="username" id="username" class="form-control" placeholder="Masukkan username" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
?>
</body>
</html>