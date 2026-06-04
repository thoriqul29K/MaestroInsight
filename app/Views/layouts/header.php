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
    <div class="app-shell">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <img src="<?= $baseURL ?>assets/img/Maestro Logo (640 x 640).jpg" alt="Maestro" class="brand-logo">
                <div class="brand-text">
                    <strong>MaestroInsight</strong>
                    <small>CRM Segmentasi</small>
                </div>
            </div>
            <nav class="sidebar-nav">
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
                    <i class="bi bi-graph-up"></i><span>Analisis RFM</span>
                </a>
                <a href="<?= $baseURL ?>promosi" class="nav-link <?= $segment === 'promosi' ? 'active' : '' ?>">
                    <i class="bi bi-megaphone"></i><span>Distribusi Promosi</span>
                </a>
            </nav>
        </aside>
        <div class="main-area">
            <header class="topbar">
                <button class="btn-toggle" id="btnToggle" aria-label="Toggle Sidebar">
                    <i class="bi bi-list"></i>
                </button>
                <div class="topbar-title"><?= $pageTitle ?? 'Dashboard' ?></div>
                <div class="topbar-user">
                    <i class="bi bi-person-circle"></i>
                    <span><?= esc($namaLengkap) ?></span>
                    <a href="<?= $baseURL ?>logout" class="btn-logout"><i class="bi bi-box-arrow-right"></i> Keluar</a>
                </div>
            </header>
            <main class="content">
