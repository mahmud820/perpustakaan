<?php

class Buku
{
    private $db;
    private $fileUploader;

    // path absolut (tidak tergantung current working directory server)
    private $folderCover;
    private $folderFile;

    // daftar status baca yang valid (dipakai validasi & dropdown)
    const STATUS_BACA = [
        'belum_dibaca'  => 'Belum Dibaca',
        'sedang_dibaca' => 'Sedang Dibaca',
        'selesai_dibaca' => 'Selesai Dibaca',
    ];

    public function __construct()
    {
        $this->db = new Database;
        $this->fileUploader = new FileUploader();

        $this->folderCover = dirname(__DIR__, 2) . '/public/img/dataGambar/';
        $this->folderFile  = dirname(__DIR__, 2) . '/public/uploads/pdf/';
    }

    public function uploadCover($files)
    {
        return $this->fileUploader->uploadCover($files);
    }

    public function uploadFileBaca($files)
    {
        return $this->fileUploader->uploadFileBaca($files);
    }

    public function tambahBuku($data, $files)
    {
        $judul = trim($data['judul'] ?? '');
        $penulis = trim($data['penulis'] ?? '');
        $klasifikasi = trim($data['klasifikasi'] ?? '');
        $sinopsis = trim($data['sinopsis'] ?? '');
        $linkBaca = trim($data['link_baca'] ?? '');

        $cover = $this->uploadCover($files);
        if (is_array($cover) && isset($cover['error'])) {
            return ['code' => 422, 'error' => $cover['error']];
        }

        $fileBaca = $this->uploadFileBaca($files);
        if (is_array($fileBaca) && isset($fileBaca['error'])) {
            return ['code' => 422, 'error' => $fileBaca['error']];
        }

        $query = "INSERT INTO buku (judul, penulis, klasifikasi, sinopsis, link_baca, cover, file_baca, created_at)
                  VALUES (:judul, :penulis, :klasifikasi, :sinopsis, :link_baca, :cover, :file_baca, NOW())";

        $this->db->query($query);
        $this->db->bind('judul', $judul);
        $this->db->bind('penulis', $penulis);
        $this->db->bind('klasifikasi', $klasifikasi);
        $this->db->bind('sinopsis', $sinopsis);
        $this->db->bind('link_baca', $linkBaca);
        $this->db->bind('cover', $cover);
        $this->db->bind('file_baca', $fileBaca);

        if ($this->db->execute()) {
            return true;
        }

        return 'Gagal menambahkan buku';
    }

    public function getAllBuku($limit = 12, $offset = 0)
    {
        $query = "SELECT * FROM buku ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $this->db->query($query);
        $this->db->bind('limit', (int) $limit, PDO::PARAM_INT);
        $this->db->bind('offset', (int) $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function countAllBuku()
    {
        $this->db->query('SELECT COUNT(*) AS total FROM buku');
        $row = $this->db->single();
        return (int) ($row['total'] ?? 0);
    }

    public function getBukuById($id)
    {
        $this->db->query('SELECT * FROM buku WHERE id = :id LIMIT 1');
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function cariBuku($keyword, $klasifikasi, $limit, $offset)
    {
        $query = "SELECT * FROM buku WHERE judul LIKE :keyword OR penulis LIKE :keyword OR klasifikasi LIKE :klasifikasi ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $this->db->query($query);
        $this->db->bind('keyword', '%' . $keyword . '%');
        $this->db->bind('klasifikasi', '%' . $klasifikasi . '%');
        $this->db->bind('limit', (int) $limit, PDO::PARAM_INT);
        $this->db->bind('offset', (int) $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function countCariBuku($keyword, $klasifikasi)
    {
        $query = "SELECT COUNT(*) AS total FROM buku WHERE judul LIKE :keyword OR penulis LIKE :keyword OR klasifikasi LIKE :klasifikasi";
        $this->db->query($query);
        $this->db->bind('keyword', '%' . $keyword . '%');
        $this->db->bind('klasifikasi', '%' . $klasifikasi . '%');
        $row = $this->db->single();
        return (int) ($row['total'] ?? 0);
    }

    public function getAllKlasifikasi()
    {
        $this->db->query('SELECT DISTINCT klasifikasi FROM buku ORDER BY klasifikasi ASC');
        return $this->db->resultSet();
    }

    public function updateBuku($data, $files)
    {
        $id = (int) ($data['id'] ?? 0);
        $judul = trim($data['judul'] ?? '');
        $penulis = trim($data['penulis'] ?? '');
        $klasifikasi = trim($data['klasifikasi'] ?? '');
        $sinopsis = trim($data['sinopsis'] ?? '');
        $linkBaca = trim($data['link_baca'] ?? '');

        $bukuLama = $this->getBukuById($id);
        if (!$bukuLama) {
            return 'Buku tidak ditemukan';
        }

        $cover = $bukuLama['cover'] ?? '';
        if (!empty($files['cover']['name'] ?? '')) {
            $uploadCover = $this->uploadCover($files);
            if (is_array($uploadCover) && isset($uploadCover['error'])) {
                return ['code' => 422, 'error' => $uploadCover['error']];
            }
            $cover = $uploadCover;
        }

        $fileBaca = $bukuLama['file_baca'] ?? '';
        if (!empty($files['file_baca']['name'] ?? '')) {
            $uploadFileBaca = $this->uploadFileBaca($files);
            if (is_array($uploadFileBaca) && isset($uploadFileBaca['error'])) {
                return ['code' => 422, 'error' => $uploadFileBaca['error']];
            }
            $fileBaca = $uploadFileBaca;
        }

        $query = "UPDATE buku SET judul = :judul, penulis = :penulis, klasifikasi = :klasifikasi, sinopsis = :sinopsis, link_baca = :link_baca, cover = :cover, file_baca = :file_baca WHERE id = :id";

        $this->db->query($query);
        $this->db->bind('judul', $judul);
        $this->db->bind('penulis', $penulis);
        $this->db->bind('klasifikasi', $klasifikasi);
        $this->db->bind('sinopsis', $sinopsis);
        $this->db->bind('link_baca', $linkBaca);
        $this->db->bind('cover', $cover);
        $this->db->bind('file_baca', $fileBaca);
        $this->db->bind('id', $id);

        return $this->db->execute();
    }

    public function hapusBuku($data)
    {
        $id = (int) ($data['id'] ?? 0);
        $buku = $this->getBukuById($id);

        if (!$buku) {
            return 0;
        }

        $this->db->query('DELETE FROM buku WHERE id = :id');
        $this->db->bind('id', $id);
        return $this->db->execute() ? 1 : 0;
    }

    public function updateStatusBaca($id, $status)
    {
        $this->db->query('UPDATE buku SET status_baca = :status WHERE id = :id');
        $this->db->bind('status', $status);
        $this->db->bind('id', $id);
        return $this->db->execute();
    }

    public function updateProgress($id, $halamanDibaca, $totalHalaman)
    {
        $this->db->query('UPDATE buku SET halaman_dibaca = :halaman_dibaca, total_halaman = :total_halaman WHERE id = :id');
        $this->db->bind('halaman_dibaca', (int) $halamanDibaca);
        $this->db->bind('total_halaman', $totalHalaman === '' ? null : (int) $totalHalaman);
        $this->db->bind('id', $id);
        return $this->db->execute();
    }

    public function updateCatatanPribadi($id, $catatan)
    {
        $this->db->query('UPDATE buku SET catatan_pribadi = :catatan WHERE id = :id');
        $this->db->bind('catatan', $catatan);
        $this->db->bind('id', $id);
        return $this->db->execute();
    }

    public function getDashboardStats($limit = 5)
    {
        $stats = [];

        $this->db->query('SELECT COUNT(*) AS total FROM buku');
        $stats['total'] = (int) $this->db->single()['total'];

        $this->db->query("SELECT COUNT(*) AS total FROM buku WHERE status_baca = 'belum_dibaca'");
        $stats['belum_dibaca'] = (int) $this->db->single()['total'];

        $this->db->query("SELECT COUNT(*) AS total FROM buku WHERE status_baca = 'sedang_dibaca'");
        $stats['sedang_dibaca'] = (int) $this->db->single()['total'];

        $this->db->query("SELECT COUNT(*) AS total FROM buku WHERE status_baca = 'selesai_dibaca'");
        $stats['selesai_dibaca'] = (int) $this->db->single()['total'];

        $this->db->query('SELECT * FROM buku ORDER BY id DESC LIMIT :limit');
        $this->db->bind('limit', (int) $limit, PDO::PARAM_INT);
        $stats['terbaru'] = $this->db->resultSet();

        return $stats;
    }
}
