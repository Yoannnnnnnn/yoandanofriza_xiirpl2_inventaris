<?php
session_start();
// Allow public pages (login/register) to be accessed without authentication
$publicFiles = ['index.php', 'register.php'];
if (!isset($_SESSION['user_id']) && !in_array(basename($_SERVER['PHP_SELF']), $publicFiles)) {
    header("Location: index.php");
    exit;
}
require_once __DIR__ . '/config.php';

// Hitung base path proyek (hilangkan trailing `/pages` jika file dijalankan dari folder pages)
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$projectBase = preg_replace('#/pages$#', '', $scriptDir);
if ($projectBase === false) $projectBase = $scriptDir;
if ($projectBase === '/' || $projectBase === '') {
    $projectBase = '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Sistem Inventaris' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $projectBase ?>/assets/css/style.css">
    <style>
        /* Small inline tweak so auth pages look consistent in case of caching */
        .alert { margin-bottom: .75rem; }
    </style>
</head>
<body>

<?php if (isset($_SESSION['user_id'])): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= $projectBase ?>/pages/dashboard.php">
            <i class="bi bi-box-seam-fill"></i>
            Inventaris Barang
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : '' ?>" href="<?= $projectBase ?>/pages/dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'items.php') ? 'active' : '' ?>" href="<?= $projectBase ?>/pages/items.php?action=create">
                        <i class="bi bi-plus-circle"></i> Tambah Barang
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'categories.php') ? 'active' : '' ?>" href="<?= $projectBase ?>/pages/categories.php">
                        <i class="bi bi-tags"></i> Kategori
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $projectBase ?>/export.php">
                        <i class="bi bi-download"></i> Export Data
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> Akun
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="<?= $projectBase ?>/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>

<main class="main-content">
    <div class="container mt-4">
