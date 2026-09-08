<?php

class DaftarBuku extends Controller
{
    // Jumlah buku per halaman untuk pagination
    const PER_PAGE = 12;

    public function index()
    {
        $data['judul'] = 'Daftar-Buku';

        $keyword = trim($_GET['keyword'] ?? '');
        $klasifikasi = trim($_GET['klasifikasi'] ?? '');

        // Batasi panjang input pencarian (mencegah query/keyword yang tidak wajar)
        $keyword = mb_substr($keyword, 0, 100);
        $klasifikasi = mb_substr($klasifikasi, 0, 100);

        // Kirim ulang nilai filter agar tetap terisi di form setelah submit
        $data['keyword'] = $keyword;
        $data['klasifikasi_terpilih'] = $klasifikasi;

        // =========================
        // PAGINATION
        // =========================
        $page = (int) ($_GET['page'] ?? 1);
        if ($page < 1) {
            $page = 1;
        }
        $offset = ($page - 1) * self::PER_PAGE;

        $buku = $this->model('Buku');

        if ($keyword !== '' || $klasifikasi !== '') {
            $total = $buku->countCariBuku($keyword, $klasifikasi);
            $data['buku'] = $buku->cariBuku($keyword, $klasifikasi, self::PER_PAGE, $offset);
        } else {
            $total = $buku->countAllBuku();
            $data['buku'] = $buku->getAllBuku(self::PER_PAGE, $offset);
        }

        $totalPages = (int) max(1, ceil($total / self::PER_PAGE));

        // Jika halaman diminta melebihi total halaman, batasi ke halaman terakhir yang valid
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $data['currentPage'] = $page;
        $data['totalPages'] = $totalPages;

        $data['daftarKlasifikasi'] = $buku->getAllKlasifikasi();

        $this->view('templates/header', $data);
        $this->view('daftarBuku/index', $data);
        $this->view('templates/footer');
    }

    public function detail($id)
    {
        // Validasi id harus numerik
        if (!ctype_digit((string) $id)) {
            http_response_code(404);
            $data['judul'] = 'Buku Tidak Ditemukan';
            $this->view('templates/header', $data);
            $this->view('daftarBuku/notfound', $data);
            $this->view('templates/footer');
            return;
        }

        $buku = $this->model('Buku')->getBukuById($id);

        if (!$buku) {
            http_response_code(404);
            $data['judul'] = 'Buku Tidak Ditemukan';
            $this->view('templates/header', $data);
            $this->view('daftarBuku/notfound', $data);
            $this->view('templates/footer');
            return;
        }

        $data['judul'] = 'Detail Buku';
        $data['buku'] = $buku;

        $this->view('templates/header', $data);
        $this->view('daftarBuku/detail', $data);
        $this->view('templates/footer');
    }

    public function tambah()
    {
        AuthMiddleware::requireAdmin();

        // Pastikan hanya POST yang diperbolehkan
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo '405 Method Not Allowed';
            return;
        }

        // =========================
        // CSRF PROTECTION
        // =========================
        Csrf::guard();

        // Ambil dan bersihkan data
        $judul = trim($_POST['judul'] ?? '');
        $penulis = trim($_POST['penulis'] ?? '');
        $klasifikasi = trim($_POST['klasifikasi'] ?? '');
        $sinopsis = trim($_POST['sinopsis'] ?? '');
        $linkBaca = trim($_POST['link_baca'] ?? '');

        // =========================
        // INPUT VALIDATION
        // =========================
        if ($judul === '') {
            http_response_code(422);
            echo 'Judul buku wajib diisi';
            return;
        }
        if ($penulis === '') {
            http_response_code(422);
            echo 'Penulis wajib diisi';
            return;
        }

        if (mb_strlen($judul) > 255) {
            http_response_code(422);
            echo 'Judul buku terlalu panjang';
            return;
        }
        if (mb_strlen($penulis) > 255) {
            http_response_code(422);
            echo 'Nama penulis terlalu panjang';
            return;
        }
        if (mb_strlen($klasifikasi) > 100) {
            http_response_code(422);
            echo 'Kategori terlalu panjang (maksimal 100 karakter)';
            return;
        }
        if (mb_strlen($sinopsis) > 5000) {
            http_response_code(422);
            echo 'Sinopsis terlalu panjang (maksimal 5000 karakter)';
            return;
        }

        // Validasi URL jika diisi
        if ($linkBaca !== '') {
            if (mb_strlen($linkBaca) > 2048) {
                http_response_code(422);
                echo 'Link baca terlalu panjang';
                return;
            }
            if (!filter_var($linkBaca, FILTER_VALIDATE_URL) || !preg_match('/^https?:\/\//i', $linkBaca)) {
                http_response_code(422);
                echo 'Link baca tidak valid (harus diawali http:// atau https://)';
                return;
            }
        }

        // Kirim data yang sudah divalidasi ke model
        $data = [
            'judul' => $judul,
            'penulis' => $penulis,
            'klasifikasi' => $klasifikasi,
            'sinopsis' => $sinopsis,
            'link_baca' => $linkBaca,
        ];

        $hasil = $this->model('Buku')->tambahBuku($data, $_FILES);

        if ($hasil === true) {
            echo 'success';
        } else {
            http_response_code(500);
            echo $hasil;
        }
    }

    public function update()
    {
        // Hanya Admin yang boleh update buku
        AuthMiddleware::requireAdmin();

        // Hanya POST yang diperbolehkan
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo '405 Method Not Allowed';
            return;
        }

        // =========================
        // CSRF PROTECTION
        // =========================
        Csrf::guard();

        // Ambil data dengan aman
        $id = trim($_POST['id'] ?? '');
        $judul = trim($_POST['judul'] ?? '');
        $penulis = trim($_POST['penulis'] ?? '');
        $klasifikasi = trim($_POST['klasifikasi'] ?? '');
        $sinopsis = trim($_POST['sinopsis'] ?? '');
        $linkBaca = trim($_POST['link_baca'] ?? '');
        $hapusFileBaca = !empty($_POST['hapus_file_baca']);

        // =========================
        // VALIDASI ID
        // =========================
        if ($id === '' || !ctype_digit($id)) {
            http_response_code(400);
            echo 'ID buku tidak valid';
            return;
        }

        // =========================
        // INPUT VALIDATION
        // =========================
        if ($judul === '') {
            http_response_code(422);
            echo 'Judul buku wajib diisi';
            return;
        }
        if ($penulis === '') {
            http_response_code(422);
            echo 'Penulis wajib diisi';
            return;
        }
        if (mb_strlen($judul) > 255) {
            http_response_code(422);
            echo 'Judul buku terlalu panjang';
            return;
        }
        if (mb_strlen($penulis) > 255) {
            http_response_code(422);
            echo 'Nama penulis terlalu panjang';
            return;
        }
        if (mb_strlen($klasifikasi) > 100) {
            http_response_code(422);
            echo 'Kategori terlalu panjang (maksimal 100 karakter)';
            return;
        }
        if (mb_strlen($sinopsis) > 5000) {
            http_response_code(422);
            echo 'Sinopsis terlalu panjang (maksimal 5000 karakter)';
            return;
        }

        // =========================
        // VALIDASI LINK BACA
        // =========================
        if ($linkBaca !== '') {
            if (mb_strlen($linkBaca) > 2048) {
                http_response_code(422);
                echo 'Link baca terlalu panjang';
                return;
            }
            if (!filter_var($linkBaca, FILTER_VALIDATE_URL) || !preg_match('/^https?:\/\//i', $linkBaca)) {
                http_response_code(422);
                echo 'Link baca tidak valid (harus diawali http:// atau https://)';
                return;
            }
        }

        // =========================
        // DATA UNTUK MODEL
        // =========================
        $data = [
            'id' => (int) $id,
            'judul' => $judul,
            'penulis' => $penulis,
            'klasifikasi' => $klasifikasi,
            'sinopsis' => $sinopsis,
            'link_baca' => $linkBaca,
            'hapus_file_baca' => $hapusFileBaca,
        ];

        $hasil = $this->model('Buku')->updateBuku($data, $_FILES);

        if ($hasil === true) {
            echo 'success';
            return;
        }

        // Error validasi dari model
        if (is_array($hasil) && isset($hasil['code'], $hasil['error'])) {
            http_response_code($hasil['code']);
            echo $hasil['error'];
            return;
        }

        // Error tidak terduga
        http_response_code(500);
        echo 'Terjadi kesalahan pada server';
    }

    public function hapus()
    {
        if (!AuthMiddleware::isAdmin()) {
            http_response_code(403);
            echo 'failed';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'failed';
            return;
        }

        // =========================
        // CSRF PROTECTION
        // =========================
        if (!Csrf::verify()) {
            http_response_code(403);
            echo 'failed';
            return;
        }

        $id = trim($_POST['id'] ?? '');

        if ($id === '' || !ctype_digit($id)) {
            http_response_code(400);
            echo 'failed';
            return;
        }

        // Nama file yang dihapus diambil ulang dari database di dalam model,
        // BUKAN dari input client, agar tidak bisa dimanipulasi.
        $hasil = $this->model('Buku')->hapusBuku(['id' => (int) $id]);

        if ($hasil > 0) {
            echo 'success';
        } else {
            http_response_code(404);
            echo 'failed';
        }
    }

    // =========================
    // UPDATE STATUS BACA (Admin only, dipakai dari Dashboard Admin)
    // =========================
    public function updateStatus()
    {
        AuthMiddleware::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo '405 Method Not Allowed';
            return;
        }

        // =========================
        // CSRF PROTECTION
        // =========================
        Csrf::guard();

        $id = trim($_POST['id'] ?? '');
        $status = trim($_POST['status_baca'] ?? '');

        if ($id === '' || !ctype_digit($id)) {
            http_response_code(400);
            echo 'ID buku tidak valid';
            return;
        }

        if (!array_key_exists($status, Buku::STATUS_BACA)) {
            http_response_code(422);
            echo 'Status baca tidak valid';
            return;
        }

        $hasil = $this->model('Buku')->updateStatusBaca($id, $status);

        if ($hasil) {
            echo 'success';
        } else {
            http_response_code(500);
            echo 'Status baca gagal disimpan';
        }
    }

    // =========================
    // UPDATE PROGRESS MEMBACA (Admin only)
    // =========================
    public function updateProgress()
    {
        AuthMiddleware::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo '405 Method Not Allowed';
            return;
        }

        // =========================
        // CSRF PROTECTION
        // =========================
        Csrf::guard();

        $id = trim($_POST['id'] ?? '');
        $halamanDibaca = trim($_POST['halaman_dibaca'] ?? '0');
        $totalHalaman = trim($_POST['total_halaman'] ?? '');

        if ($id === '' || !ctype_digit($id)) {
            http_response_code(400);
            echo 'ID buku tidak valid';
            return;
        }

        $hasil = $this->model('Buku')->updateProgress($id, $halamanDibaca, $totalHalaman);

        if ($hasil === true) {
            echo 'success';
        } else {
            http_response_code(422);
            echo $hasil;
        }
    }

    // =========================
    // UPDATE CATATAN PRIBADI (Admin only, tidak ditampilkan ke Guest)
    // =========================
    public function updateCatatan()
    {
        AuthMiddleware::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo '405 Method Not Allowed';
            return;
        }

        // =========================
        // CSRF PROTECTION
        // =========================
        Csrf::guard();

        $id = trim($_POST['id'] ?? '');
        $catatan = trim($_POST['catatan_pribadi'] ?? '');

        if ($id === '' || !ctype_digit($id)) {
            http_response_code(400);
            echo 'ID buku tidak valid';
            return;
        }

        if (mb_strlen($catatan) > 2000) {
            http_response_code(422);
            echo 'Catatan maksimal 2000 karakter';
            return;
        }

        $hasil = $this->model('Buku')->updateCatatanPribadi($id, $catatan);

        if ($hasil) {
            echo 'success';
        } else {
            http_response_code(500);
            echo 'Catatan gagal disimpan';
        }
    }
}
