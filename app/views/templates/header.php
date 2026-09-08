<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= htmlspecialchars(Csrf::token()); ?>">
  <title>Perpustakaan</title>

  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous"> -->

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= BASEURL; ?>/css/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="<?= BASEURL; ?>/css/style.css">
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg fixed-top p-2 custom-navbar">
    <div class="container-fluid">

      <a class="navbar-brand d-flex align-items-center" href="<?= BASEURL; ?>">
        <img src="<?= BASEURL; ?>/img/perpus.png"
          alt="Logo"
          width="50"
          height="50"
          class="me-2 rounded-circle flame-logo">

        <span class="brand-text">Perpustakaan</span>
      </a>

      <button class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav">

        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">

        <ul class="navbar-nav ms-auto">

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
            <li class="nav-item">
              <span class="nav-link">
                Halo, <?= htmlspecialchars($_SESSION['nama'] ?? ''); ?>
              </span>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= BASEURL; ?>/auth/logout">Logout</a>
            </li>
          <?php else : ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= BASEURL; ?>/login">Login</a>
            </li>
          <?php endif; ?>
        </ul>

      </div>
    </div>
  </nav>