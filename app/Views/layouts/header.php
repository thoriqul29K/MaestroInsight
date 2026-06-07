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
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="<?= base_url('assets/img/favicon.ico') ?>">
</head>

<body>
    <header class="topbar">
        <a href="<?= $baseURL ?>" class="topbar-brand">
            <img src="<?= base_url('assets/img/maestro-logo-640x640.jpg') ?>" alt="Maestro" class="brand-logo">
            <div class="brand-text">
                <strong>MaestroInsight</strong>
                <small>Aplikasi CRM Segmentasi Pelanggan Berbasis Web</small>
            </div>
        </a>
        <nav class="topbar-nav">
            <a href="<?= base_url('dashboard') ?>" class="nav-link <?= $segment === 'dashboard' ? 'active' : '' ?>">
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
            <a href="<?= $baseURL ?>logout" class="btn-logout"><i class="bi bi-box-arrow-right"></i> Keluar</a>
        </div>
    </header>
    <main class="content">