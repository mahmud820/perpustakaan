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
        $adaFilter = ($keyword !== '' || $klasifikasi !== '');

        // Hitung total dulu, SEBELUM offset dihitung
        $total = $adaFilter
            ? $buku->countCariBuku($keyword, $klasifikasi)
            : $buku->countAllBuku();

        $totalPages = (int) max(1, ceil($total / self::PER_PAGE));

        // Clamp $page ke totalPages SEBELUM offset dihitung & query dijalankan
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * self::PER_PAGE;

        $data['buku'] = $adaFilter
            ? $buku->cariBuku($keyword, $klasifikasi, self::PER_PAGE, $offset)
            : $buku->getAllBuku(self::PER_PAGE, $offset);

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

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo '405 Method Not Allowed';
            return;
        }

        Csrf::guard();

        $judul = trim($_POST['judul'] ?? '');
        $penulis = trim($_POST['penulis'] ?? '');
        $klasifikasi = trim($_POST['klasifikasi'] ?? '');
        $sinopsis = trim($_POST['sinopsis'] ?? '');
        $linkBaca = trim($_POST['link_baca'] ?? '');

        $errors = [
            'judul' => Validator::judul($judul),
            'penulis' => Validator::penulis($penulis),
            'klasifikasi' => Validator::klasifikasi($klasifikasi),
            'sinopsis' => Validator::sinopsis($sinopsis),
            'link_baca' => Validator::linkBaca($linkBaca),
        ];

        foreach ($errors as $error) {
            if ($error !== null) {
                http_response_code(422);
                echo $error;
                return;
            }
        }

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
            return;
        }

        if (is_array($hasil) && isset($hasil['code'], $hasil['error'])) {
            http_response_code($hasil['code']);
            echo $hasil['error'];
            return;
        }

        http_response_code(500);
        echo is_string($hasil) ? $hasil : 'Terjadi kesalahan pada server';
    }

    public function update()
    {
        AuthMiddleware::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo '405 Method Not Allowed';
            return;
        }

        Csrf::guard();

        $id = trim($_POST['id'] ?? '');
        $judul = trim($_POST['judul'] ?? '');
        $penulis = trim($_POST['penulis'] ?? '');
        $klasifikasi = trim($_POST['klasifikasi'] ?? '');
        $sinopsis = trim($_POST['sinopsis'] ?? '');
        $linkBaca = trim($_POST['link_baca'] ?? '');
        $hapusFileBaca = !empty($_POST['hapus_file_baca']);

        $idError = Validator::requiredId($id);
        if ($idError !== null) {
            http_response_code(400);
            echo $idError;
            return;
        }

        $errors = [
            'judul' => Validator::judul($judul),
            'penulis' => Validator::penulis($penulis),
            'klasifikasi' => Validator::klasifikasi($klasifikasi),
            'sinopsis' => Validator::sinopsis($sinopsis),
            'link_baca' => Validator::linkBaca($linkBaca),
        ];

        foreach ($errors as $error) {
            if ($error !== null) {
                http_response_code(422);
                echo $error;
                return;
            }
        }

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

        if (is_array($hasil) && isset($hasil['code'], $hasil['error'])) {
            http_response_code($hasil['code']);
            echo $hasil['error'];
            return;
        }

        if (is_string($hasil)) {
            $statusMap = [
                'Buku tidak ditemukan' => 404,
            ];
            http_response_code($statusMap[$hasil] ?? 500);
            echo $hasil;
            return;
        }

        http_response_code(500);
        echo 'Terjadi kesalahan pada server';
    }

    public function hapus()
    {
        if (!AuthMiddleware::isAdmin()) {
            http_response_code(403);
            echo 'Akses ditolak';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method tidak diizinkan';
            return;
        }

        if (!Csrf::verify()) {
            http_response_code(403);
            echo 'Sesi tidak valid atau kedaluwarsa (CSRF)';
            return;
        }

        $id = trim($_POST['id'] ?? '');
        $idError = Validator::requiredId($id);

        if ($idError !== null) {
            http_response_code(400);
            echo $idError;
            return;
        }

        $hasil = $this->model('Buku')->hapusBuku(['id' => (int) $id]);

        if ($hasil > 0) {
            echo 'Buku berhasil dihapus';
        } else {
            http_response_code(404);
            echo 'Buku tidak ditemukan';
        }
    }

    public function updateStatus()
    {
        AuthMiddleware::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo '405 Method Not Allowed';
            return;
        }

        Csrf::guard();

        $id = trim($_POST['id'] ?? '');
        $status = trim($_POST['status_baca'] ?? '');

        $idError = Validator::requiredId($id);
        if ($idError !== null) {
            http_response_code(400);
            echo $idError;
            return;
        }

        if (!Validator::statusBaca($status)) {
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

    public function updateProgress()
    {
        AuthMiddleware::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo '405 Method Not Allowed';
            return;
        }

        Csrf::guard();

        $id = trim($_POST['id'] ?? '');
        $halamanDibaca = trim($_POST['halaman_dibaca'] ?? '0');
        $totalHalaman = trim($_POST['total_halaman'] ?? '');

        $idError = Validator::requiredId($id);
        if ($idError !== null) {
            http_response_code(400);
            echo $idError;
            return;
        }

        $progressError = Validator::progressBuku($halamanDibaca, $totalHalaman);
        if ($progressError !== null) {
            http_response_code(422);
            echo $progressError;
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

    public function updateCatatan()
    {
        AuthMiddleware::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo '405 Method Not Allowed';
            return;
        }

        Csrf::guard();

        $id = trim($_POST['id'] ?? '');
        $catatan = trim($_POST['catatan_pribadi'] ?? '');

        $idError = Validator::requiredId($id);
        if ($idError !== null) {
            http_response_code(400);
            echo $idError;
            return;
        }

        $catatanError = Validator::catatanPribadi($catatan);
        if ($catatanError !== null) {
            http_response_code(422);
            echo $catatanError;
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
