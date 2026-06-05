<?php
$session = session();
$namaLengkap = $session->get('nama_lengkap') ?? 'Pengguna';
$segment = service('uri')->getSegment(1) ?: 'dashboard';
$baseURL = base_url();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'MaestroInsight' ?> | PT. Maestro Wisata Raya</title>
    <link rel="stylesheet" href="<?= $baseURL ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="<?= $baseURL ?>assets/img/favicon.ico">
</head>

<body>
    <header class="topbar">
        <a href="<?= $baseURL ?>" class="topbar-brand">
            <img src="<?= $baseURL ?>assets/img/Maestro Logo (640 x 640).jpg" alt="Maestro" class="brand-logo">
            <div class="brand-text">
                <strong>MaestroInsight</strong>
                <small>CRM Segmentasi</small>
            </div>
        </a>
        <nav class="topbar-nav">
            <a href="<?= $baseURL ?>" class="nav-link <?= $segment === '' || $segment === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i><span>Dashboard</span>
            </a>
            <a href="<?= $baseURL ?>pelanggan" class="nav-link <?= $segment === 'pelanggan' ? 'active' : '' ?>">
                <i class="bi bi-people"></i><span>Pelanggan</span>
            </a>
            <a href="<?= $baseURL ?>transaksi" class="nav-link <?= $segment === 'transaksi' ? 'active' : '' ?>">
                <i class="bi bi-receipt"></i><span>Transaksi</span>
            </a>
            <a href="<?= $baseURL ?>analisis" class="nav-link <?= $segment === 'analisis' ? 'active' : '' ?>">
                <i class="bi bi-graph-up"></i><span>Analisis Pelanggan</span>
            </a>
            <a href="<?= $baseURL ?>promosi" class="nav-link <?= $segment === 'promosi' ? 'active' : '' ?>">
                <i class="bi bi-megaphone"></i><span>Lakukan Promosi!</span>
            </a>
        </nav>
        <div class="topbar-user">
            <i class="bi bi-person-circle"></i>
            <span class="topbar-user-name"><?= esc($namaLengkap) ?></span>
            <a href="<?= $baseURL ?>logout" class="btn-logout"><i class="bi bi-box-arrow-right"></i> Keluar</a>
        </div>
    </header>
    <main class="content">