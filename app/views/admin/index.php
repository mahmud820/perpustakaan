<?php

/** @var array $data */

?>
<section class="py-5" style="margin-top: 80px; min-height: 70vh;">
    <div class="container">

        <div class="card shadow-sm mb-4">
            <div class="card-body p-4">
                <h1 class="h3 mb-3">Halaman Admin</h1>
                <p class="mb-1">Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama']); ?></strong>.</p>
                <p class="text-muted mb-0">Halaman ini hanya dapat diakses oleh user dengan role <strong>admin</strong>.</p>
            </div>
        </div>

        <?php $stats = $data['stats'] ?? ['total' => 0, 'sedang_dibaca' => 0, 'selesai_dibaca' => 0, 'belum_dibaca' => 0, 'terbaru' => []]; ?>

        <!-- =========================
             KARTU STATISTIK DASHBOARD
             ========================= -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card shadow-sm h-100 border-0 bg-primary bg-opacity-10">
                    <div class="card-body p-3">
                        <div class="text-muted small mb-1">Total Buku</div>
                        <div class="h3 mb-0 text-primary"><?= (int) $stats['total']; ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card shadow-sm h-100 border-0 bg-warning bg-opacity-10">
                    <div class="card-body p-3">
                        <div class="text-muted small mb-1">Sedang Dibaca</div>
                        <div class="h3 mb-0 text-warning-emphasis"><?= (int) $stats['sedang_dibaca']; ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card shadow-sm h-100 border-0 bg-success bg-opacity-10">
                    <div class="card-body p-3">
                        <div class="text-muted small mb-1">Selesai Dibaca</div>
                        <div class="h3 mb-0 text-success"><?= (int) $stats['selesai_dibaca']; ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card shadow-sm h-100 border-0 bg-secondary bg-opacity-10">
                    <div class="card-body p-3">
                        <div class="text-muted small mb-1">Belum Dibaca</div>
                        <div class="h3 mb-0 text-secondary"><?= (int) $stats['belum_dibaca']; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================
             WIDGET BUKU TERBARU
             ========================= -->
        <div class="card shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Buku Terbaru</h2>

                <?php if (!empty($stats['terbaru'])) : ?>
                    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-5 g-3">
                        <?php foreach ($stats['terbaru'] as $b) : ?>
                            <?php
                            $coverTerbaru = !empty($b['cover'])
                                ? BASEURL . '/img/dataGambar/' . rawurlencode($b['cover'])
                                : BASEURL . '/img/no-image.png';
                            $statusTerbaru = $b['status_baca'] ?? 'belum_dibaca';
                            $statusLabelTerbaru = Buku::STATUS_BACA[$statusTerbaru] ?? 'Belum Dibaca';
                            $statusBadgeTerbaru = [
                                'belum_dibaca'   => 'bg-secondary',
                                'sedang_dibaca'  => 'bg-warning text-dark',
                                'selesai_dibaca' => 'bg-success',
                            ][$statusTerbaru] ?? 'bg-secondary';
                            ?>
                            <div class="col">
                                <a href="<?= BASEURL; ?>/daftarBuku/detail/<?= (int) $b['id']; ?>" class="text-decoration-none text-body">
                                    <div class="ratio ratio-2x3 mb-1">
                                        <img src="<?= $coverTerbaru; ?>"
                                            alt=""
                                            style="object-fit: cover; border-radius: 4px;"
                                            onerror="this.onerror=null;this.src='<?= BASEURL; ?>/img/no-image.png';">
                                    </div>
                                    <div class="small fw-semibold text-truncate" title="<?= htmlspecialchars((string) $b['judul']); ?>">
                                        <?= htmlspecialchars((string) $b['judul']); ?>
                                    </div>
                                    <span class="badge <?= $statusBadgeTerbaru; ?>"><?= htmlspecialchars($statusLabelTerbaru); ?></span>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p class="text-muted mb-0">Belum ada buku.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
                    <h2 class="h5 mb-0">Kelola Buku &mdash; Status Baca, Progress &amp; Catatan</h2>
                    <a href="<?= BASEURL; ?>/daftarBuku" class="btn btn-sm btn-outline-primary">
                        + Tambah / Edit / Hapus Buku
                    </a>
                </div>

                <p class="text-muted small">
                    Status baca dan progress bersifat global (ditandai oleh Admin), bukan per akun pembaca.
                    Catatan pribadi hanya terlihat di halaman ini, tidak ditampilkan ke pengunjung.
                </p>

                <div class="table-responsive">
                    <table class="table align-middle admin-buku-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">Cover</th>
                                <th>Judul / Penulis</th>
                                <th style="width: 190px;">Status Baca</th>
                                <th style="width: 220px;">Progress Membaca</th>
                                <th style="width: 140px;">Catatan Pribadi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['buku'])) : ?>
                                <?php foreach ($data['buku'] as $buku) : ?>
                                    <?php
                                    $cover = !empty($buku['cover'])
                                        ? BASEURL . '/img/dataGambar/' . rawurlencode($buku['cover'])
                                        : BASEURL . '/img/no-image.png';
                                    $totalHalaman = $buku['total_halaman'] ?? '';
                                    $halamanDibaca = (int) ($buku['halaman_dibaca'] ?? 0);
                                    $catatan = $buku['catatan_pribadi'] ?? '';
                                    ?>
                                    <tr data-id="<?= (int) $buku['id']; ?>">
                                        <td>
                                            <img src="<?= $cover; ?>"
                                                alt=""
                                                width="45"
                                                height="60"
                                                style="object-fit: cover; border-radius: 4px;"
                                                onerror="this.onerror=null;this.src='<?= BASEURL; ?>/img/no-image.png';">
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars((string) $buku['judul']); ?></div>
                                            <div class="text-muted small"><?= htmlspecialchars((string) $buku['penulis']); ?></div>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm status-baca-select" data-id="<?= (int) $buku['id']; ?>">
                                                <?php foreach ($data['statusBacaOptions'] as $value => $label) : ?>
                                                    <option value="<?= $value; ?>" <?= ($buku['status_baca'] ?? 'belum_dibaca') === $value ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($label); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <form class="progress-form d-flex align-items-center gap-1" data-id="<?= (int) $buku['id']; ?>">
                                                <input type="number" min="0" class="form-control form-control-sm halaman-dibaca-input"
                                                    style="width: 70px;" value="<?= $halamanDibaca; ?>" placeholder="Hlm" title="Halaman dibaca">
                                                <span class="text-muted">/</span>
                                                <input type="number" min="0" class="form-control form-control-sm total-halaman-input"
                                                    style="width: 70px;" value="<?= htmlspecialchars((string) $totalHalaman); ?>" placeholder="Total" title="Total halaman">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Simpan</button>
                                            </form>
                                        </td>
                                        <td>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-secondary btn-catatan"
                                                data-id="<?= (int) $buku['id']; ?>"
                                                data-judul="<?= htmlspecialchars((string) $buku['judul']); ?>"
                                                data-catatan="<?= htmlspecialchars((string) $catatan); ?>">
                                                <?= $catatan !== '' ? '📝 Lihat/Edit' : '+ Tambah'; ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada buku.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($data['totalPages'] ?? 1) > 1) : ?>
                    <nav aria-label="Navigasi halaman kelola buku" class="mt-3">
                        <ul class="pagination justify-content-center flex-wrap">

                            <li class="page-item <?= $data['currentPage'] <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?= BASEURL; ?>/admin?page=<?= max(1, $data['currentPage'] - 1); ?>">&laquo;</a>
                            </li>

                            <?php for ($i = 1; $i <= $data['totalPages']; $i++) : ?>
                                <li class="page-item <?= $i === $data['currentPage'] ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?= BASEURL; ?>/admin?page=<?= $i; ?>"><?= $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= $data['currentPage'] >= $data['totalPages'] ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?= BASEURL; ?>/admin?page=<?= min($data['totalPages'], $data['currentPage'] + 1); ?>">&raquo;</a>
                            </li>

                        </ul>
                    </nav>
                <?php endif; ?>

            </div>
        </div>

    </div>
</section>

<!-- Modal Catatan Pribadi -->
<div class="modal fade" id="catatanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="catatanForm">
                <?= Csrf::field(); ?>
                <input type="hidden" name="id" id="catatan_id">

                <div class="modal-header">
                    <h5 class="modal-title">Catatan Pribadi &mdash; <span id="catatan_judul"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <textarea name="catatan_pribadi" id="catatan_pribadi" class="form-control" rows="6"
                        maxlength="2000" placeholder="Catatan ini hanya terlihat oleh Admin..."></textarea>
                    <small class="text-muted">Maksimal 2000 karakter.</small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Catatan</button>
                </div>
            </form>
        </div>
    </div>
</div>