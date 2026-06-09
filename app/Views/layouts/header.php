<?php
$session = session();
$namaLengkap = $session->get('nama_lengkap') ?? 'Pengguna';
$segment = service('uri')->getSegment(1) ?: 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'MaestroInsight' ?> | PT. Maestro Wisata Raya</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="icon" type="image/x-icon" href="<?= base_url('assets/img/favicon.ico') ?>">
</head>

<body>
    <header class="topbar">
        <button class="hamburger-btn" id="hamburgerBtn" aria-label="Buka menu">
            <span></span><span></span><span></span>
        </button>
        <a href="<?= base_url('/') ?>" class="topbar-brand">
            <img src="<?= base_url('assets/img/maestro-logo-640x640.jpg') ?>" alt="Maestro" class="brand-logo">
            <div class="brand-text">
                <strong>MaestroInsight</strong>
                <small>Aplikasi CRM Segmentasi Pelanggan Berbasis Web</small>
            </div>
        </a>
        <nav class="topbar-nav">
            <a href="<?= base_url('/') ?>" class="nav-link <?= $segment === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i><span>Dashboard</span>
            </a>
            <a href="<?= base_url('pelanggan') ?>" class="nav-link <?= $segment === 'pelanggan' ? 'active' : '' ?>">
                <i class="bi bi-people"></i><span>Pelanggan</span>
            </a>
            <a href="<?= base_url('transaksi') ?>" class="nav-link <?= $segment === 'transaksi' ? 'active' : '' ?>">
                <i class="bi bi-receipt"></i><span>Transaksi</span>
            </a>
            <a href="<?= base_url('analisis') ?>" class="nav-link <?= $segment === 'analisis' ? 'active' : '' ?>">
                <i class="bi bi-graph-up"></i><span>Analisis Pelanggan</span>
            </a>
            <a href="<?= base_url('promosi') ?>" class="nav-link <?= $segment === 'promosi' ? 'active' : '' ?>">
                <i class="bi bi-megaphone"></i><span>Lakukan Promosi!</span>
            </a>
        </nav>
        <div class="topbar-user">
            <i class="bi bi-person-circle"></i>
            <span class="topbar-user-name"><?= esc($namaLengkap) ?></span>
            <a href="<?= base_url('logout') ?>" class="btn-logout"><i class="bi bi-box-arrow-right"></i> <span class="logout-label">Keluar</span></a>
        </div>
    </header>

    <aside class="sidebar-drawer" id="sidebarDrawer">
        <div class="drawer-header">
            <div class="drawer-brand">
                <img src="<?= base_url('assets/img/maestro-logo-640x640.jpg') ?>" alt="Maestro" class="brand-logo">
                <strong>MaestroInsight</strong>
            </div>
            <button class="drawer-close" id="drawerClose" aria-label="Tutup menu">&times;</button>
        </div>
        <nav class="drawer-nav">
            <a href="<?= base_url('dashboard') ?>" class="<?= $segment === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="<?= base_url('pelanggan') ?>" class="<?= $segment === 'pelanggan' ? 'active' : '' ?>">
                <i class="bi bi-people"></i> Pelanggan
            </a>
            <a href="<?= base_url('transaksi') ?>" class="<?= $segment === 'transaksi' ? 'active' : '' ?>">
                <i class="bi bi-receipt"></i> Transaksi
            </a>
            <a href="<?= base_url('analisis') ?>" class="<?= $segment === 'analisis' ? 'active' : '' ?>">
                <i class="bi bi-graph-up"></i> Analisis Pelanggan
            </a>
            <a href="<?= base_url('promosi') ?>" class="<?= $segment === 'promosi' ? 'active' : '' ?>">
                <i class="bi bi-megaphone"></i> Lakukan Promosi!
            </a>
        </nav>
        <div class="drawer-footer">
            <span class="drawer-user"><i class="bi bi-person-circle"></i> <?= esc($namaLengkap) ?></span>
            <a href="<?= base_url('logout') ?>" class="drawer-logout"><i class="bi bi-box-arrow-right"></i> Keluar</a>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="content">