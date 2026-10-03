<?php

/** @var array $data */
?>
<!-- DAFTAR BUKU -->
<section class="daftar-buku-section">
    <div class="container py-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
            <h2 class="section-title mb-3 mb-md-0">Daftar Buku</h2>

            <?php if (AuthMiddleware::isAdmin()) : ?>
                <button
                    class="btn btn-success"
                    data-bs-toggle="modal"
                    data-bs-target="#addBookModal">
                    Tambah Buku
                </button>
            <?php endif; ?>
        </div>

        <!-- Form Pencarian & Filter -->
        <form class="row g-2 mb-4" method="GET" action="<?= BASEURL; ?>/daftarBuku">
            <div class="col-12 col-md-5">
                <input
                    class="form-control"
                    type="search"
                    name="keyword"
                    placeholder="Cari judul, penulis, atau kategori..."
                    value="<?= htmlspecialchars($data['keyword'] ?? ''); ?>">
            </div>

            <div class="col-8 col-md-4">
                <select name="klasifikasi" class="form-select">
                    <option value="">Semua Kategori</option>
                    <?php if (!empty($data['daftarKlasifikasi'])) : ?>
                        <?php foreach ($data['daftarKlasifikasi'] as $k) : ?>
                            <option
                                value="<?= htmlspecialchars($k['klasifikasi']); ?>"
                                <?= (($data['klasifikasi_terpilih'] ?? '') === $k['klasifikasi']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($k['klasifikasi']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-4 col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-fill" type="submit">Cari</button>
                <?php if (!empty($data['keyword']) || !empty($data['klasifikasi_terpilih'])) : ?>
                    <a href="<?= BASEURL; ?>/daftarBuku" class="btn btn-outline-secondary" title="Reset filter">✕</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4">
            <?php if (!empty($data['buku'])) : ?>
                <?php foreach ($data['buku'] as $buku) : ?>
                    <div class="col">

                        <div class="card h-100 shadow-sm">

                            <img
                                src="<?= ViewHelper::coverUrl($buku['cover'] ?? null); ?>"
                                class="card-img-top cover-img"
                                alt="<?= htmlspecialchars((string) $buku['judul']); ?>"
                                data-fallback="no-image.png">

                            <div class="card-body d-flex flex-column">

                                <h5 class="card-title">
                                    <?= htmlspecialchars((string) $buku['judul']); ?>
                                </h5>

                                <p class="card-text mb-1">
                                    Penulis: <?= htmlspecialchars((string) $buku['penulis']); ?>
                                </p>

                                <?php if (!empty($buku['klasifikasi'])) : ?>
                                    <p class="card-text mb-1">
                                        <span class="badge bg-secondary"><?= htmlspecialchars((string) $buku['klasifikasi']); ?></span>
                                    </p>
                                <?php endif; ?>

                                <?php
                                $statusBaca = $buku['status_baca'] ?? null;
                                $halamanDibaca = (int) ($buku['halaman_dibaca'] ?? 0);
                                $totalHalaman = $buku['total_halaman'] ?? null;
                                $persenProgress = ViewHelper::progressPercent($halamanDibaca, $totalHalaman);
                                ?>

                                <p class="card-text mb-2">
                                    <span class="badge <?= ViewHelper::statusBadgeClass($statusBaca); ?>"><?= htmlspecialchars(ViewHelper::statusLabel($statusBaca)); ?></span>
                                </p>

                                <?php if (!empty($totalHalaman)) : ?>
                                    <div class="progress mb-2" style="height: 8px;" title="<?= $halamanDibaca; ?> / <?= $totalHalaman; ?> halaman">
                                        <div class="progress-bar bg-info"
                                            role="progressbar"
                                            style="width: <?= $persenProgress; ?>%;"
                                            aria-valuenow="<?= $persenProgress; ?>"
                                            aria-valuemin="0"
                                            aria-valuemax="100"></div>
                                    </div>
                                    <p class="card-text small text-muted mb-2">
                                        <?= $halamanDibaca; ?> / <?= $totalHalaman; ?> halaman (<?= $persenProgress; ?>%)
                                    </p>
                                <?php endif; ?>

                                <div class="mt-auto">

                                    <?php $urlBaca = ViewHelper::bacaUrl($buku); ?>

                                    <?php if ($urlBaca !== '') : ?>
                                        <a
                                            href="<?= htmlspecialchars($urlBaca); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="btn btn-outline-primary w-100 mb-2">
                                            Baca Buku
                                        </a>
                                    <?php else : ?>
                                        <button class="btn btn-outline-secondary w-100 mb-2" disabled>
                                            Belum Tersedia
                                        </button>
                                    <?php endif; ?>

                                    <div class="d-flex gap-2">

                                        <a
                                            href="<?= BASEURL; ?>/daftarBuku/detail/<?= (int) $buku['id']; ?>"
                                            class="btn btn-info btn-sm">
                                            Detail
                                        </a>

                                        <?php if (AuthMiddleware::isAdmin()) : ?>
                                            <button
                                                class="btn btn-warning btn-sm btn-edit"
                                                data-id="<?= (int) $buku['id']; ?>"
                                                data-judul="<?= htmlspecialchars((string) $buku['judul']); ?>"
                                                data-penulis="<?= htmlspecialchars((string) $buku['penulis']); ?>"
                                                data-klasifikasi="<?= htmlspecialchars((string) $buku['klasifikasi']); ?>"
                                                data-sinopsis="<?= htmlspecialchars((string) $buku['sinopsis']); ?>"
                                                data-link-baca="<?= htmlspecialchars((string) $buku['link_baca']); ?>"
                                                data-cover="<?= htmlspecialchars((string) $buku['cover']); ?>"
                                                data-file-baca="<?= htmlspecialchars($buku['file_baca'] ?? ''); ?>">
                                                Edit
                                            </button>

                                            <button
                                                class="btn btn-danger btn-sm btn-delete"
                                                data-id="<?= (int) $buku['id']; ?>">
                                                Hapus
                                            </button>
                                        <?php endif; ?>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="alert alert-warning">
                    Buku tidak ditemukan.
                </div>
            <?php endif; ?>


        </div>

        <?php
        // Query string dasar (keyword/klasifikasi) dipertahankan saat pindah halaman
        $paramsPaging = array_filter([
            'keyword' => $data['keyword'] ?? '',
            'klasifikasi' => $data['klasifikasi_terpilih'] ?? '',
        ], 'strlen');

        $pager = [
            'ariaLabel' => 'Navigasi halaman daftar buku',
            'ulClass' => 'pagination library-pagination justify-content-center flex-wrap',
            'linkClass' => '',
            'prevLabel' => '&laquo; Sebelumnya',
            'nextLabel' => 'Selanjutnya &raquo;',
            'current' => $data['currentPage'],
            'total' => $data['totalPages'] ?? 1,
            'urlFor' => function ($halaman) use ($paramsPaging) {
                return BASEURL . '/daftarBuku?' . http_build_query($paramsPaging + ['page' => $halaman]);
            },
        ];
        require __DIR__ . '/../templates/pagination.php';
        ?>

    </div>
</section>

<!-- Modal Untuk Tambah dan Ubah -->
<?php if (AuthMiddleware::isAdmin()) : ?>
    <div class="modal fade" id="addBookModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <form id="addBookForm" enctype="multipart/form-data">
                    <?= Csrf::field(); ?>
                    <input type="hidden" name="id" id="id">

                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Buku</h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                        </button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label">Judul Buku</label>
                            <input
                                type="text"
                                name="judul"
                                class="form-control"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Penulis</label>
                            <input
                                type="text"
                                name="penulis"
                                class="form-control"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <input
                                type="text"
                                name="klasifikasi"
                                class="form-control"
                                placeholder="Misal: Fiksi, Sejarah, Teknologi">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sinopsis</label>
                            <textarea
                                name="sinopsis"
                                class="form-control"
                                rows="4"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Link Baca (opsional)</label>
                            <input
                                type="url"
                                name="link_baca"
                                class="form-control"
                                placeholder="https://...">
                            <small class="text-muted">Dipakai jika tidak ada file PDF yang diupload.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File Baca (PDF, opsional)</label>
                            <input type="file" name="file_baca" id="file_baca" class="form-control" accept="application/pdf">
                            <small class="text-muted">Maksimal 20 MB. Jika diisi, tombol "Baca Buku" akan membuka file ini, bukan link.</small>
                            <div id="fileBacaInfo" class="form-text"></div>
                            <div class="form-check mt-1 d-none" id="hapusFileBacaWrapper">
                                <input class="form-check-input" type="checkbox" name="hapus_file_baca" id="hapus_file_baca" value="1">
                                <label class="form-check-label" for="hapus_file_baca">
                                    Hapus file baca yang sudah ada
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Cover Buku</label>
                            <input type="file" name="cover" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">JPG, PNG, atau WEBP. Maksimal 2 MB.</small>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                            Batal
                        </button>

                        <button
                            type="submit"
                            class="btn btn-success">
                            Simpan
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
<?php endif; ?>