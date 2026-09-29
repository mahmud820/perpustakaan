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
              <input type="hidden" name="gambarLama" value="<?= htmlspecialchars($data['user']['gambar'] ?? ''); ?>">

              <!-- Foto Profil -->
              <div class="text-center mb-4 profile-photo-wrapper">
                <img src="<?= BASEURL; ?>/img/<?= $data['user']['gambar'] ? 'profil/' . htmlspecialchars($data['user']['gambar']) : 'default.jpg'; ?>"
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

<?php if (!empty($_SESSION['flash_success'])) : ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: <?= json_encode($_SESSION['flash_success']); ?>,
        confirmButtonColor: '#e5c158'
      });
    });
  </script>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])) : ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: <?= json_encode($_SESSION['flash_error']); ?>,
        confirmButtonColor: '#e5c158'
      });
    });
  </script>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>