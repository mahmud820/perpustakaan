<?php

/** @var array $data */
?>

<section class="admin-fullscreen">
    <div class="container">

        <!-- Header Card -->
        <div class="card mb-4">
            <div class="card-body p-4">
                <h1 class="h3 fw-bold mb-2">Halaman Admin</h1>
                <p class="mb-1 text-dark">Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></strong>.</p>
                <p class="text-muted small mb-0">Halaman ini hanya dapat diakses oleh user dengan role <strong>admin</strong>.</p>
            </div>
        </div>

        <?php $stats = $data['stats'] ?? ['total' => 0, 'sedang_dibaca' => 0, 'selesai_dibaca' => 0, 'belum_dibaca' => 0, 'terbaru' => []]; ?>

        <!-- Kartu Statistik Dashboard -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card bg-stat-blue">
                    <div class="stat-icon"><i class="bi bi-book"></i></div>
                    <div class="small fw-medium">Total Buku</div>
                    <div class="h2 fw-bold mb-0"><?= (int) $stats['total']; ?></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card bg-stat-yellow">
                    <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
                    <div class="small fw-medium">Sedang Dibaca</div>
                    <div class="h2 fw-bold mb-0"><?= (int) $stats['sedang_dibaca']; ?></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card bg-stat-green">
                    <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                    <div class="small fw-medium">Selesai Dibaca</div>
                    <div class="h2 fw-bold mb-0"><?= (int) $stats['selesai_dibaca']; ?></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card bg-stat-gray">
                    <div class="stat-icon"><i class="bi bi-eye"></i></div>
                    <div class="small fw-medium">Belum Dibaca</div>
                    <div class="h2 fw-bold mb-0"><?= (int) $stats['belum_dibaca']; ?></div>
                </div>
            </div>
        </div>

        <!-- Widget Buku Terbaru -->
        <div class="card mb-4">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3">Buku Terbaru</h2>

                <?php if (!empty($stats['terbaru'])) : ?>
                    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-5 g-3">
                        <?php foreach ($stats['terbaru'] as $b) : ?>
                            <?php $coverTerbaru = ViewHelper::coverUrl($b['cover'] ?? null); ?>
                            <div class="col text-center">
                                <a href="<?= BASEURL; ?>/daftarBuku/detail/<?= (int) $b['id']; ?>" class="text-decoration-none text-body d-block">
                                    <div class="ratio ratio-2x3 mb-2 shadow-sm rounded overflow-hidden">
                                        <img src="<?= $coverTerbaru; ?>"
                                            alt=""
                                            style="object-fit: cover;"
                                            onerror="this.onerror=null;this.src='<?= BASEURL; ?>/img/no-image.png';">
                                    </div>
                                    <div class="small fw-bold text-truncate mb-1" title="<?= htmlspecialchars((string) $b['judul']); ?>">
                                        <?= htmlspecialchars((string) $b['judul']); ?>
                                    </div>
                                    <span class="badge badge-pill-custom <?= ViewHelper::statusBadgeClass($b['status_baca'] ?? null); ?>"><?= htmlspecialchars(ViewHelper::statusLabel($b['status_baca'] ?? null)); ?></span>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p class="text-muted mb-0">Belum ada buku.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tabel Kelola Buku -->
        <div class="card">
            <div class="card-body p-4">

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
                    <h2 class="h5 fw-bold mb-0">Kelola Buku &mdash; Status Baca, Progress &amp; Catatan</h2>
                    <a href="<?= BASEURL; ?>/daftarBuku" class="btn btn-sm btn-primary rounded-pill px-3">
                        <i class="bi bi-plus-lg me-1"></i> Tambah / Edit / Hapus Buku
                    </a>
                </div>

                <p class="text-muted small mb-4">
                    <i class="bi bi-info-circle me-1"></i> Status baca dan progress bersifat global (ditandai oleh Admin), bukan per akun pembaca. Catatan pribadi hanya terlihat di halaman ini.
                </p>

                <div class="table-responsive">
                    <table class="table align-middle admin-buku-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Cover</th>
                                <th>Judul / Penulis</th>
                                <th style="width: 170px;">Status Baca</th>
                                <th style="width: 220px;">Progress Membaca</th>
                                <th style="width: 140px;" class="text-end">Catatan Pribadi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['buku'])) : ?>
                                <?php foreach ($data['buku'] as $buku) : ?>
                                    <?php
                                    $cover = ViewHelper::coverUrl($buku['cover'] ?? null);
                                    $totalHalaman = (int) ($buku['total_halaman'] ?? 0);
                                    $halamanDibaca = (int) ($buku['halaman_dibaca'] ?? 0);
                                    $catatan = $buku['catatan_pribadi'] ?? '';
                                    $percent = ViewHelper::progressPercent($halamanDibaca, $totalHalaman);
                                    $statusCurrent = $buku['status_baca'] ?? 'belum_dibaca';
                                    ?>
                                    <tr data-id="<?= (int) $buku['id']; ?>">
                                        <td>
                                            <img src="<?= $cover; ?>"
                                                alt=""
                                                width="45"
                                                height="60"
                                                class="rounded shadow-sm"
                                                style="object-fit: cover;"
                                                onerror="this.onerror=null;this.src='<?= BASEURL; ?>/img/no-image.png';">
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars((string) $buku['judul']); ?></div>
                                            <div class="text-muted small"><?= htmlspecialchars((string) $buku['penulis']); ?></div>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm status-baca-select" data-id="<?= (int) $buku['id']; ?>" data-status="<?= $statusCurrent; ?>">
                                                <?php foreach ($data['statusBacaOptions'] as $value => $label) : ?>
                                                    <option value="<?= $value; ?>" <?= $statusCurrent === $value ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($label); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <form class="progress-form d-flex flex-column gap-1" data-id="<?= (int) $buku['id']; ?>">
                                                <div class="d-flex align-items-center gap-1 mb-1">
                                                    <input type="number" min="0" class="form-control form-control-sm halaman-dibaca-input border-0 bg-light fw-bold"
                                                        style="width: 55px;" value="<?= $halamanDibaca; ?>" placeholder="Hlm">
                                                    <span class="text-muted small">/</span>
                                                    <input type="number" min="0" class="form-control form-control-sm total-halaman-input border-0 bg-light text-muted"
                                                        style="width: 55px;" value="<?= $totalHalaman ?: ''; ?>" placeholder="Total">
                                                    <button type="submit" class="btn btn-sm btn-light border-0 ms-auto" title="Simpan"><i class="bi bi-check-lg"></i></button>
                                                </div>
                                                <div class="custom-progress-bar">
                                                    <div class="custom-progress-fill" style="width: <?= $percent; ?>%;"></div>
                                                </div>
                                            </form>
                                        </td>
                                        <td class="text-end">
                                            <button type="button"
                                                class="btn btn-sm <?= $catatan !== '' ? 'btn-outline-primary' : 'btn-light text-primary'; ?> rounded-pill px-3 btn-catatan"
                                                data-id="<?= (int) $buku['id']; ?>"
                                                data-judul="<?= htmlspecialchars((string) $buku['judul']); ?>"
                                                data-catatan="<?= htmlspecialchars((string) $catatan); ?>">
                                                <i class="bi <?= $catatan !== '' ? 'bi-pencil-square' : 'bi-plus'; ?> me-1"></i>
                                                <?= $catatan !== '' ? 'Lihat/Edit' : 'Tambah'; ?>
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

                <?php
                $pager = [
                    'ariaLabel' => 'Navigasi halaman kelola buku',
                    'ulClass' => 'pagination admin-pagination pagination-sm justify-content-center flex-wrap gap-1',
                    'linkClass' => 'rounded-circle border-0',
                    'prevLabel' => '&lsaquo;',
                    'nextLabel' => '&rsaquo;',
                    'current' => $data['currentPage'],
                    'total' => $data['totalPages'] ?? 1,
                    'urlFor' => function ($halaman) {
                        return BASEURL . '/admin?page=' . $halaman;
                    },
                ];
                require __DIR__ . '/../templates/pagination.php';
                ?>

            </div>
        </div>

    </div>
</section>

<!-- Modal Catatan Pribadi -->
<div class="modal fade" id="catatanModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <form id="catatanForm">
                <?= Csrf::field(); ?>
                <input type="hidden" name="id" id="catatan_id">

                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Catatan Pribadi &mdash; <span id="catatan_judul"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <textarea name="catatan_pribadi" id="catatan_pribadi" class="form-control bg-light border-0" rows="6"
                        maxlength="2000" style="border-radius: 12px;" placeholder="Catatan ini hanya terlihat oleh Admin..."></textarea>
                    <small class="text-muted mt-2 d-block">Maksimal 2000 karakter.</small>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Catatan</button>
                </div>
            </form>
        </div>
    </div>
</div>