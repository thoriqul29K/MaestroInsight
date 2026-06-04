<?php
// Main layout — composes header + content + footer partials
$baseURL = base_url();
?>
<?= $this->include('layouts/header') ?>

<div class="page-header">
    <h1><i class="bi <?= $pageIcon ?? 'bi-house' ?>"></i> <?= $pageTitle ?? 'Halaman' ?></h1>
    <?php if (! empty($pageActions)): ?>
        <div class="page-actions"><?= $pageActions ?></div>
    <?php endif; ?>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-error"><i class="bi bi-exclamation-triangle"></i> <?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-error">
        <ul>
            <?php foreach (session()->getFlashdata('errors') as $err): ?>
                <li><?= esc($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?= $this->renderSection('main') ?>

<?= $this->include('layouts/footer') ?>
