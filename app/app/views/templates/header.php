<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= htmlspecialchars(Csrf::token()); ?>">
  <meta name="base-url" content="<?= htmlspecialchars(BASEURL, ENT_QUOTES); ?>">
  <title><?= !empty($data['judul']) ? htmlspecialchars($data['judul']) . ' - Perpustakaan' : 'Perpustakaan'; ?></title>

  <link rel="stylesheet" href="<?= BASEURL; ?>/css/vendor/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= BASEURL; ?>/css/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="<?= BASEURL; ?>/css/style.css">
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg fixed-top custom-navbar">
    <div class="container">

      <a class="navbar-brand d-flex align-items-center" href="<?= BASEURL; ?>">
        <img src="<?= BASEURL; ?>/img/perpus.png"
          alt="Logo"
          width="42"
          height="42"
          class="me-2 rounded-circle">

        <span class="brand-text">Perpustakaan</span>
      </a>

      <button class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav"
        aria-controls="navbarNav"
        aria-expanded="false"
        aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-lg-center">

          <li class="nav-item">
            <a class="nav-link" href="<?= BASEURL; ?>/beranda">Beranda</a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="<?= BASEURL; ?>/daftarBuku">Daftar Buku</a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="<?= BASEURL; ?>/kontak">Kontak</a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="<?= BASEURL; ?>/about">About</a>
          </li>

          <?php if (AuthMiddleware::isAdmin()) : ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= BASEURL; ?>/admin">Admin</a>
            </li>
          <?php endif; ?>

          <?php if (AuthMiddleware::isLogin()) : ?>
            <?php
            $fotoProfil = !empty($_SESSION['gambar'])
              ? BASEURL . '/img/profil/' . htmlspecialchars($_SESSION['gambar'])
              : BASEURL . '/img/default.png';
            ?>
            <li class="nav-item dropdown profile-dropdown ms-lg-2">
              <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?= $fotoProfil; ?>" alt="Foto Profil" class="profile-avatar">
                <span class="user-greeting mb-0">Halo, <?= htmlspecialchars($_SESSION['nama'] ?? ''); ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end profile-dropdown-menu" aria-labelledby="profileDropdown">
                <?php if (AuthMiddleware::isAdmin()) : ?>
                  <li><a class="dropdown-item" href="<?= BASEURL; ?>/profile"><i class="bi bi-person-circle me-2"></i>Profil Saya</a></li>
                  <li>
                    <hr class="dropdown-divider">
                  </li>
                <?php endif; ?>
                <li>
                  <!-- Logout memakai form POST + token CSRF (lihat Auth::logout) -->
                  <form method="post" action="<?= BASEURL; ?>/auth/logout" class="m-0">
                    <?= Csrf::field(); ?>
                    <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                  </form>
                </li>
              </ul>
            </li>
          <?php else : ?>
            <li class="nav-item ms-lg-2">
              <a class="nav-link btn-login-nav" href="<?= BASEURL; ?>/login">Sign In</a>
            </li>
          <?php endif; ?>

        </ul>
      </div>
    </div>
  </nav>