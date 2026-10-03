<?php

/** @var array $data */
?>
<section class="profile-fullscreen">
  <div class="container">
    <div class="row">
      <div class="col-md-8 offset-md-2">
        <div class="profile-card">
          <div class="profile-card-header">
            <h4>Edit Profil</h4>
          </div>
          <div class="profile-card-body">
            <form action="<?= BASEURL; ?>/profile/update" method="POST" enctype="multipart/form-data">
              <?= Csrf::field(); ?>

              <!-- Foto Profil -->
              <div class="text-center mb-4 profile-photo-wrapper">
                <img src="<?= BASEURL; ?>/img/<?= !empty($data['user']['gambar']) ? 'profil/' . htmlspecialchars($data['user']['gambar']) : 'default.png'; ?>"
                  class="rounded-circle img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                <div class="mt-2">
                  <input type="file" class="form-control" name="gambar" id="gambar" accept=".jpg,.jpeg,.png,.webp">
                </div>
              </div>

              <!-- Nama & Email -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="nama" class="form-label">Nama Lengkap</label>
                  <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($data['user']['nama'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($data['user']['email'] ?? ''); ?>" required>
                </div>
              </div>

              <!-- No Telp & Tagline -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="no_telp" class="form-label">No. Telepon</label>
                  <input type="text" class="form-control" id="no_telp" name="no_telp" value="<?= htmlspecialchars($data['user']['no_telp'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                  <label for="tagline" class="form-label">Tagline Singkat</label>
                  <input type="text" class="form-control" id="tagline" name="tagline" placeholder="contoh: Web Developer Enthusiast" value="<?= htmlspecialchars($data['user']['tagline'] ?? ''); ?>">
                </div>
              </div>

              <!-- Tentang Saya -->
              <div class="mb-3">
                <label for="tentang" class="form-label">Tentang Saya</label>
                <textarea class="form-control" id="tentang" name="tentang" rows="4"><?= htmlspecialchars($data['user']['tentang'] ?? ''); ?></textarea>
              </div>

              <button type="submit" class="btn btn-profile-save w-100">Simpan Perubahan</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php
// Pesan flash dititipkan lewat atribut data-*, lalu ditampilkan oleh helpers.js (tampilkanFlash).
// Tanpa <script> inline, jadi aman dari Content-Security-Policy "script-src 'self'".
foreach (['success' => 'flash_success', 'error' => 'flash_error'] as $tipe => $kunci) :
  if (!empty($_SESSION[$kunci])) : ?>
    <div class="d-none" data-flash="<?= $tipe; ?>" data-flash-message="<?= htmlspecialchars($_SESSION[$kunci], ENT_QUOTES, 'UTF-8'); ?>"></div>
    <?php unset($_SESSION[$kunci]); ?>
  <?php endif;
endforeach; ?>