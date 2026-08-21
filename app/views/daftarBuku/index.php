<!-- DAFTAR BUKU -->
<section class="daftar-buku-section">
    <div class="container py-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center  mb-4">
            <h2 class="section-title mb-3 mb-md-0">Daftar Buku</h2>
            <div class="d-flex gap-2">
                <button
                    class="btn btn-success"
                    data-bs-toggle="modal"
                    data-bs-target="#addBookModal">
                    Tambah Buku
                </button>

                <form class="d-flex" method="GET" action="<?= BASEURL; ?>/daftarBuku">
                    <input
                        class="form-control me-2"
                        type="search"
                        name="keyword"
                        placeholder="Cari buku..."
                        value="<?= $_GET['keyword'] ?? ''; ?>">
                    <button class="btn btn-primary">Cari</button>
                </form>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4">
            <?php if (!empty($data['buku'])) : ?>
                <?php foreach ($data['buku'] as $buku) : ?>
                    <div class="col">

                        <div class="card h-100 shadow-sm">

                            <?php
                            $coverPath = '../public/img/dataGambar/' . $buku['cover'];

                            $gambar = (!empty($buku['cover']) && file_exists($coverPath))
                                ? BASEURL . '/img/dataGambar/' . $buku['cover']
                                : BASEURL . '/img/no-image.png';
                            ?>

                            <img
                                src="<?= !empty($buku['cover'])
                                            ? BASEURL . '/img/dataGambar/' . $buku['cover']
                                            : BASEURL . '/img/no-image.png'; ?>"
                                class="card-img-top cover-img"
                                alt="<?= $buku['judul']; ?>">

                            <div class="card-body d-flex flex-column">

                                <h5 class="card-title">
                                    <?= $buku['judul']; ?>
                                </h5>

                                <p class="card-text">
                                    Penulis: <?= $buku['penulis']; ?>
                                </p>

                                <div class="mt-auto">

                                    <a
                                        href="<?= $buku['link_baca']; ?>"
                                        target="_blank"
                                        class="btn btn-outline-primary w-100 mb-2">
                                        Baca Buku
                                    </a>

                                    <div class="d-flex gap-2">

                                        <a
                                            href="<?= BASEURL; ?>/daftarBuku/detail/<?= $buku['id']; ?>"
                                            class="btn btn-info btn-sm">
                                            Detail
                                        </a>

                                        <button
                                            class="btn btn-warning btn-sm btn-edit"
                                            data-id="<?= $buku['id']; ?>"
                                            data-judul="<?= $buku['judul']; ?>"
                                            data-penulis="<?= $buku['penulis']; ?>"
                                            data-klasifikasi="<?= $buku['klasifikasi']; ?>"
                                            data-sinopsis="<?= htmlspecialchars($buku['sinopsis']); ?>"
                                            data-link-baca="<?= $buku['link_baca']; ?>"
                                            data-cover="<?= $buku['cover']; ?>">
                                            Edit
                                        </button>

                                        <button
                                            class="btn btn-danger btn-sm btn-delete"
                                            data-id="<?= $buku['id']; ?>"
                                            data-cover="<?= $buku['cover']; ?>">
                                            Hapus
                                        </button>

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
    </div>
</section>

<div class="modal fade" id="addBookModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form id="addBookForm" enctype="multipart/form-data">
                <input type="hidden" name="id" id="id">
                <input type="hidden" name="cover_lama" id="cover_lama">

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
                        <label class="form-label">Klasifikasi</label>
                        <input
                            type="text"
                            name="klasifikasi"
                            class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sinopsis</label>
                        <textarea
                            name="sinopsis"
                            class="form-control"
                            rows="4"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Link Baca</label>
                        <input
                            type="url"
                            name="link_baca"
                            class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cover Buku</label>
                        <input
                            type="file"
                            name="cover"
                            class="form-control">
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