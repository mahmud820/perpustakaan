<?php

class DaftarBuku extends Controller
{
    const PER_PAGE = 12;

    public function index()
    {
        $data['judul'] = 'Daftar Buku';

        $keyword = mb_substr($this->query('keyword'), 0, 100);
        $klasifikasi = mb_substr($this->query('klasifikasi'), 0, 100);

        $data['keyword'] = $keyword;
        $data['klasifikasi_terpilih'] = $klasifikasi;

        $buku = $this->model('Buku');

        // cariBuku/countCariBuku otomatis menampilkan semua buku kalau keyword & kategori kosong
        $total = $buku->countCariBuku($keyword, $klasifikasi);

        $pagination = new Pagination((int) ($_GET['page'] ?? 1), self::PER_PAGE, $total);

        $data['buku'] = $buku->cariBuku($keyword, $klasifikasi, self::PER_PAGE, $pagination->getOffset());
        $data['currentPage'] = $pagination->getPage();
        $data['totalPages'] = $pagination->getTotalPages();
        $data['daftarKlasifikasi'] = $buku->getAllKlasifikasi();

        $this->view('templates/header', $data);
        $this->view('daftarBuku/index', $data);
        $this->view('templates/footer');
    }

    public function detail($id = '')
    {
        if (!ctype_digit((string) $id)) {
            $this->tampilkanTidakDitemukan();
            return;
        }

        $buku = $this->model('Buku')->getBukuById($id);

        if (!$buku) {
            $this->tampilkanTidakDitemukan();
            return;
        }

        $data['judul'] = 'Detail Buku';
        $data['buku'] = $buku;

        $this->view('templates/header', $data);
        $this->view('daftarBuku/detail', $data);
        $this->view('templates/footer');
    }

    // =========================
    // ENDPOINT AJAX (semua membalas teks: "success" jika berhasil, selain itu pesan error)
    // =========================

    public function tambah()
    {
        $this->guardAdminPost();

        $input = $this->inputBuku();

        $error = Validator::buku($input);
        if ($error !== null) {
            $this->respond(422, $error);
        }

        $this->respondResult($this->model('Buku')->tambahBuku($input, $_FILES));
    }

    public function update()
    {
        $this->guardAdminPost();

        $id = $this->post('id');

        $idError = Validator::requiredId($id);
        if ($idError !== null) {
            $this->respond(400, $idError);
        }

        $input = $this->inputBuku();

        $error = Validator::buku($input);
        if ($error !== null) {
            $this->respond(422, $error);
        }

        $input['id'] = (int) $id;
        $input['hapus_file_baca'] = !empty($_POST['hapus_file_baca']);

        $this->respondResult($this->model('Buku')->updateBuku($input, $_FILES));
    }

    public function hapus()
    {
        $this->guardAdminPost();

        $id = $this->post('id');

        $idError = Validator::requiredId($id);
        if ($idError !== null) {
            $this->respond(400, $idError);
        }

        $this->respondResult($this->model('Buku')->hapusBuku((int) $id));
    }

    public function updateStatus()
    {
        $this->guardAdminPost();

        $id = $this->post('id');
        $status = $this->post('status_baca');

        $idError = Validator::requiredId($id);
        if ($idError !== null) {
            $this->respond(400, $idError);
        }

        if (!Validator::statusBaca($status)) {
            $this->respond(422, 'Status baca tidak valid');
        }

        $this->respondResult($this->model('Buku')->updateStatusBaca((int) $id, $status));
    }

    public function updateProgress()
    {
        $this->guardAdminPost();

        $id = $this->post('id');
        $halamanDibaca = $this->post('halaman_dibaca', '0');
        $totalHalaman = $this->post('total_halaman');

        $idError = Validator::requiredId($id);
        if ($idError !== null) {
            $this->respond(400, $idError);
        }

        $progressError = Validator::progressBuku($halamanDibaca, $totalHalaman);
        if ($progressError !== null) {
            $this->respond(422, $progressError);
        }

        $this->respondResult($this->model('Buku')->updateProgress(
            (int) $id,
            (int) $halamanDibaca,
            $totalHalaman === '' ? null : (int) $totalHalaman
        ));
    }

    public function updateCatatan()
    {
        $this->guardAdminPost();

        $id = $this->post('id');
        $catatan = $this->post('catatan_pribadi');

        $idError = Validator::requiredId($id);
        if ($idError !== null) {
            $this->respond(400, $idError);
        }

        $catatanError = Validator::catatanPribadi($catatan);
        if ($catatanError !== null) {
            $this->respond(422, $catatanError);
        }

        $this->respondResult($this->model('Buku')->updateCatatanPribadi((int) $id, $catatan));
    }

    // =========================
    // HELPER PRIVATE
    // =========================

    // Field form buku yang dipakai bersama oleh tambah() dan update()
    private function inputBuku(): array
    {
        return [
            'judul'       => $this->post('judul'),
            'penulis'     => $this->post('penulis'),
            'klasifikasi' => $this->post('klasifikasi'),
            'sinopsis'    => $this->post('sinopsis'),
            'link_baca'   => $this->post('link_baca'),
        ];
    }

    private function tampilkanTidakDitemukan(): void
    {
        http_response_code(404);

        $data['judul'] = 'Buku Tidak Ditemukan';

        $this->view('templates/header', $data);
        $this->view('daftarBuku/notfound', $data);
        $this->view('templates/footer');
    }
}
