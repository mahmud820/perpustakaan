<?php

/** @var array $data */
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['judul']); ?> - Perpustakaan</title>
    <link rel="stylesheet" href="<?= BASEURL; ?>/css/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASEURL; ?>/css/auth/login.css">
</head>

<body class="login-page">
    <div class="login-card">
        <div class="text-center mb-4">
            <img src="<?= BASEURL; ?>/img/perpus.png" alt="Logo Perpustakaan" class="login-logo">
            <h1 class="h3 mt-3">Login</h1>
            <p class="text-muted mb-0">Masuk sebagai admin perpustakaan</p>
        </div>

        <?php if (!empty($data['error'])) : ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($data['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASEURL; ?>/auth/login" autocomplete="off">
            <?= Csrf::field(); ?>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    class="form-control"
                    maxlength="50"
                    required
                    autofocus>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    required>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Login
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="<?= BASEURL; ?>/beranda">Kembali ke Beranda</a>
        </div>
    </div>
</body>

</html>