<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | MaestroInsight</title>
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= base_url() ?>assets/css/login.css">
    <link rel="icon" type="image/x-icon" href="<?= base_url() ?>assets/img/favicon.ico">
</head>

<body class="login-body">
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <img src="<?= base_url() ?>assets/img/Maestro Logo (640 x 640).jpg" alt="Maestro" class="login-logo">
                <h1>MaestroInsight</h1>
                <p>CRM Segmentasi Pelanggan<br>PT. Maestro Wisata Raya</p>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
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

            <form action="<?= base_url() ?>login" method="post" class="login-form">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= old('username') ?>" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Masuk</button>
            </form>
            <div class="login-footer">
                <!-- <small>Default: <strong>admin / admin123</strong></small> -->
            </div>
        </div>
    </div>
</body>

</html>