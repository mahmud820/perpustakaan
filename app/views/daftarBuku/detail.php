<?php

/** @var array $data */

$cover = !empty($data['buku']['cover'])
    ? BASEURL . '/img/dataGambar/' . $data['buku']['cover']
    : BASEURL . '/img/no-image.png';
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
                                alt="<?= $data['buku']['judul']; ?>">

                        </div>

                        <!-- Detail -->
                        <div class="col-lg-8">

                            <h1 class="fw-bold mb-3">
                                <?= $data['buku']['judul']; ?>
                            </h1>

                            <span class="badge bg-primary fs-6 mb-4">
                                <?= $data['buku']['klasifikasi']; ?>
                            </span>

                            <div class="row mb-3">

                                <div class="col-md-3 fw-bold">
                                    Penulis :
                                </div>

                                <div class="col-md-9">
                                    <?= $data['buku']['penulis']; ?>
                                </div>

                            </div>

                            <h4 class="mt-4 mb-3">
                                Sinopsis
                            </h4>

                            <div class="bg-light p-3 rounded shadow-sm">
                                <?= nl2br($data['buku']['sinopsis']); ?>
                            </div>

                            <hr>

                            <div class="row text-muted small">

                                <div class="col-md-6">
                                    <strong>Dibuat:</strong><br>
                                    <?= $data['buku']['created_at']; ?>
                                </div>

                                <div class="col-md-6">
                                    <strong>Diupdate:</strong><br>
                                    <?= $data['buku']['updated_at']; ?>
                                </div>

                            </div>

                            <div class="mt-4 d-flex gap-2">

                                <a
                                    href="<?= $data['buku']['link_baca']; ?>"
                                    target="_blank"
                                    class="btn btn-success">
                                    📖 Baca Buku
                                </a>

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