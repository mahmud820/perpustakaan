<?php

/** @var array $data */

$cover = !empty($data['buku']['cover'])
    ? BASEURL . '/img/dataGambar/' . rawurlencode($data['buku']['cover'])
    : BASEURL . '/img/no-image.png';

// Prioritas: file PDF yang diupload, lalu link eksternal
$urlBaca = '';
if (!empty($data['buku']['file_baca'])) {
    $urlBaca = BASEURL . '/uploads/pdf/' . rawurlencode($data['buku']['file_baca']);
} elseif (!empty($data['buku']['link_baca'])) {
    $urlBaca = $data['buku']['link_baca'];
}

// Status Baca & Progress Membaca (ditandai oleh Admin, ditampilkan ke semua pengunjung)
$statusBaca = $data['buku']['status_baca'] ?? 'belum_dibaca';
$statusBadgeClass = [
    'belum_dibaca'   => 'bg-secondary',
    'sedang_dibaca'  => 'bg-warning text-dark',
    'selesai_dibaca' => 'bg-success',
][$statusBaca] ?? 'bg-secondary';
$statusLabel = Buku::STATUS_BACA[$statusBaca] ?? 'Belum Dibaca';

$halamanDibaca = (int) ($data['buku']['halaman_dibaca'] ?? 0);
$totalHalaman = $data['buku']['total_halaman'] ?? null;
$persenProgress = ($totalHalaman && $totalHalaman > 0)
    ? min(100, (int) round($halamanDibaca / $totalHalaman * 100))
    : 0;
?>

<div class="detail-page">

    <div class="overlay">

        <div class="container py-5 mt-5">

            <div class="card border-0 shadow-lg bg-white bg-opacity-75">

                <div class="card-body p-4">

                    <div class="row g-4">

                        <!-- Cover -->
                        <div class="col-lg-4 text-center">

                            <img
                                src="<?= $cover; ?>"
                                class="img-fluid rounded shadow-lg book-cover"
                                alt="<?= htmlspecialchars((string) $data['buku']['judul']); ?>"
                                onerror="this.onerror=null;this.src='<?= BASEURL; ?>/img/no-image.png';">

                        </div>

                        <!-- Detail -->
                        <div class="col-lg-8">

                            <h1 class="fw-bold mb-3">
                                <?= htmlspecialchars((string) $data['buku']['judul']); ?>
                            </h1>

                            <?php if (!empty($data['buku']['klasifikasi'])) : ?>
                                <span class="badge bg-primary fs-6 mb-2 me-2">
                                    <?= htmlspecialchars((string) $data['buku']['klasifikasi']); ?>
                                </span>
                            <?php endif; ?>

                            <span class="badge <?= $statusBadgeClass; ?> fs-6 mb-4">
                                <?= htmlspecialchars($statusLabel); ?>
                            </span>

                            <?php if (!empty($totalHalaman)) : ?>
                                <div class="mb-4">
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-info"
                                            role="progressbar"
                                            style="width: <?= $persenProgress; ?>%;"
                                            aria-valuenow="<?= $persenProgress; ?>"
                                            aria-valuemin="0"
                                            aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted">
                                        Progress membaca: <?= $halamanDibaca; ?> / <?= $totalHalaman; ?> halaman (<?= $persenProgress; ?>%)
                                    </small>
                                </div>
                            <?php endif; ?>

                            <div class="row mb-3">

                                <div class="col-md-3 fw-bold">
                                    Penulis :
                                </div>

                                <div class="col-md-9">
                                    <?= htmlspecialchars((string) $data['buku']['penulis']); ?>
                                </div>

                            </div>

                            <h4 class="mt-4 mb-3">
                                Sinopsis
                            </h4>

                            <div class="bg-light p-3 rounded shadow-sm">
                                <?= !empty($data['buku']['sinopsis'])
                                    ? nl2br(htmlspecialchars((string) $data['buku']['sinopsis']))
                                    : '<span class="text-muted">Belum ada sinopsis.</span>'; ?>
                            </div>

                            <hr>

                            <div class="row text-muted small">

                                <div class="col-md-6">
                                    <strong>Dibuat:</strong><br>
                                    <?= htmlspecialchars((string) $data['buku']['created_at']); ?>
                                </div>

                                <div class="col-md-6">
                                    <strong>Diupdate:</strong><br>
                                    <?= htmlspecialchars((string) $data['buku']['updated_at']); ?>
                                </div>

                            </div>

                            <div class="mt-4 d-flex gap-2">

                                <?php if ($urlBaca !== '') : ?>
                                    <a
                                        href="<?= htmlspecialchars($urlBaca); ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="btn btn-success">
                                        📖 Baca Buku
                                    </a>
                                <?php else : ?>
                                    <button class="btn btn-secondary" disabled>
                                        📖 Belum Tersedia
                                    </button>
                                <?php endif; ?>

                                <a
                                    href="<?= BASEURL; ?>/daftarBuku"
                                    class="btn btn-secondary">
                                    ← Kembali
                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>