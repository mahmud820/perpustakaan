<?php

/**
 * Model Buku.
 *
 * KONVENSI RETURN untuk method yang MENULIS data (tambah, update, hapus, status, progress, catatan):
 *   - true                                 -> berhasil
 *   - ['code' => int, 'error' => string]   -> gagal ('code' = status HTTP yang cocok)
 * Controller cukup meneruskan hasilnya ke Controller::respondResult().
 */
class Buku
{
    // daftar status baca yang valid (dipakai validasi & dropdown)
    const STATUS_BACA = [
        'belum_dibaca'  => 'Belum Dibaca',
        'sedang_dibaca' => 'Sedang Dibaca',
        'selesai_dibaca' => 'Selesai Dibaca',
    ];

    private $db;
    private $fileUploader;

    public function __construct()
    {
        $this->db = new Database;
        $this->fileUploader = new FileUploader();
    }

    // =========================
    // HELPER INTERNAL
    // =========================

    private static function gagal(int $code, string $pesan): array
    {
        return ['code' => $code, 'error' => $pesan];
    }

    private static function tidakDitemukan(): array
    {
        return self::gagal(404, 'Buku tidak ditemukan');
    }

    private function ada(int $id): bool
    {
        $this->db->query('SELECT id FROM buku WHERE id = :id LIMIT 1');
        $this->db->bind('id', $id, PDO::PARAM_INT);

        return (bool) $this->db->single();
    }

    /**
     * Hapus file cover/pdf dari disk (best effort, tidak melempar error).
     * File TIDAK dihapus kalau masih dipakai baris buku lain, supaya data lama
     * yang kebetulan berbagi satu file tidak rusak.
     */
    private function hapusFile($nama, string $tipe, int $kecualiId = 0): void
    {
        if (!is_string($nama) || $nama === '') {
            return;
        }

        // nama kolom berasal dari whitelist ini, bukan dari input user
        $kolom = $tipe === 'pdf' ? 'file_baca' : 'cover';

        $this->db->query("SELECT COUNT(*) AS total FROM buku WHERE {$kolom} = :nama AND id <> :id");
        $this->db->bind('nama', $nama);
        $this->db->bind('id', $kecualiId, PDO::PARAM_INT);
        $row = $this->db->single();

        if ((int) ($row['total'] ?? 0) > 0) {
            return;
        }

        $this->fileUploader->deleteFile($nama, $tipe === 'pdf' ? 'pdf' : 'cover');
    }

    /**
     * Bangun klausa WHERE untuk pencarian.
     * - keyword     : cocok sebagian di judul ATAU penulis ATAU kategori
     * - klasifikasi : cocok persis dengan kategori yang dipilih di dropdown
     * Kalau keduanya diisi, keduanya harus terpenuhi (AND).
     */
    private function bangunFilter(string $keyword, string $klasifikasi): array
    {
        $kondisi = [];
        $param = [];

        if ($keyword !== '') {
            // escape % dan _ supaya diperlakukan sebagai huruf biasa, bukan wildcard
            $like = '%' . addcslashes($keyword, '%_\\') . '%';

            $kondisi[] = '(judul LIKE :kw_judul OR penulis LIKE :kw_penulis OR klasifikasi LIKE :kw_klasifikasi)';
            $param['kw_judul'] = $like;
            $param['kw_penulis'] = $like;
            $param['kw_klasifikasi'] = $like;
        }

        if ($klasifikasi !== '') {
            $kondisi[] = 'klasifikasi = :klasifikasi';
            $param['klasifikasi'] = $klasifikasi;
        }

        $where = $kondisi ? ' WHERE ' . implode(' AND ', $kondisi) : '';

        return [$where, $param];
    }

    private function bindSemua(array $param): void
    {
        foreach ($param as $nama => $nilai) {
            $this->db->bind($nama, $nilai);
        }
    }

    // =========================
    // BACA DATA
    // =========================

    public function getAllBuku($limit = 12, $offset = 0)
    {
        return $this->cariBuku('', '', $limit, $offset);
    }

    public function countAllBuku()
    {
        return $this->countCariBuku('', '');
    }

    public function getBukuById($id)
    {
        $this->db->query('SELECT * FROM buku WHERE id = :id LIMIT 1');
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function cariBuku($keyword, $klasifikasi, $limit, $offset)
    {
        [$where, $param] = $this->bangunFilter((string) $keyword, (string) $klasifikasi);

        $this->db->query("SELECT * FROM buku{$where} ORDER BY id DESC LIMIT :limit OFFSET :offset");
        $this->bindSemua($param);
        $this->db->bind('limit', (int) $limit, PDO::PARAM_INT);
        $this->db->bind('offset', (int) $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    public function countCariBuku($keyword, $klasifikasi)
    {
        [$where, $param] = $this->bangunFilter((string) $keyword, (string) $klasifikasi);

        $this->db->query("SELECT COUNT(*) AS total FROM buku{$where}");
        $this->bindSemua($param);
        $row = $this->db->single();

        return (int) ($row['total'] ?? 0);
    }

    public function getAllKlasifikasi()
    {
        // kategori kosong tidak perlu muncul sebagai pilihan dropdown
        $this->db->query("SELECT DISTINCT klasifikasi FROM buku WHERE klasifikasi IS NOT NULL AND klasifikasi <> '' ORDER BY klasifikasi ASC");
        return $this->db->resultSet();
    }

    public function getDashboardStats($limit = 5)
    {
        // Satu query untuk semua hitungan (sebelumnya 4 query COUNT terpisah)
        $this->db->query("SELECT
                COUNT(*) AS total,
                COALESCE(SUM(status_baca = 'belum_dibaca'), 0) AS belum_dibaca,
                COALESCE(SUM(status_baca = 'sedang_dibaca'), 0) AS sedang_dibaca,
                COALESCE(SUM(status_baca = 'selesai_dibaca'), 0) AS selesai_dibaca
            FROM buku");
        $row = $this->db->single() ?: [];

        $stats = [
            'total'          => (int) ($row['total'] ?? 0),
            'belum_dibaca'   => (int) ($row['belum_dibaca'] ?? 0),
            'sedang_dibaca'  => (int) ($row['sedang_dibaca'] ?? 0),
            'selesai_dibaca' => (int) ($row['selesai_dibaca'] ?? 0),
        ];

        $this->db->query('SELECT * FROM buku ORDER BY id DESC LIMIT :limit');
        $this->db->bind('limit', (int) $limit, PDO::PARAM_INT);
        $stats['terbaru'] = $this->db->resultSet();

        return $stats;
    }

    // =========================
    // TULIS DATA
    // =========================

    public function tambahBuku(array $data, array $files)
    {
        $cover = $this->fileUploader->uploadCover($files);
        if (is_array($cover)) {
            return self::gagal(422, $cover['error']);
        }

        $fileBaca = $this->fileUploader->uploadFileBaca($files);
        if (is_array($fileBaca)) {
            // cover sudah terlanjur tersimpan, buang supaya tidak jadi file yatim
            $this->hapusFile($cover, 'cover');
            return self::gagal(422, $fileBaca['error']);
        }

        $query = "INSERT INTO buku (judul, penulis, klasifikasi, sinopsis, link_baca, cover, file_baca, created_at)
                  VALUES (:judul, :penulis, :klasifikasi, :sinopsis, :link_baca, :cover, :file_baca, NOW())";

        $this->db->query($query);
        $this->db->bind('judul', $data['judul'] ?? '');
        $this->db->bind('penulis', $data['penulis'] ?? '');
        $this->db->bind('klasifikasi', $data['klasifikasi'] ?? '');
        $this->db->bind('sinopsis', $data['sinopsis'] ?? '');
        $this->db->bind('link_baca', $data['link_baca'] ?? '');
        $this->db->bind('cover', $cover);
        $this->db->bind('file_baca', $fileBaca);

        if (!$this->db->execute()) {
            // insert gagal: file yang barusan diupload tidak punya baris di database
            $this->hapusFile($cover, 'cover');
            $this->hapusFile($fileBaca, 'pdf');
            return self::gagal(500, 'Gagal menambahkan buku');
        }

        return true;
    }

    public function updateBuku(array $data, array $files)
    {
        $id = (int) ($data['id'] ?? 0);

        $bukuLama = $this->getBukuById($id);
        if (!$bukuLama) {
            return self::tidakDitemukan();
        }

        // '' = tidak ada file baru dikirim -> pakai file lama
        $coverBaru = $this->fileUploader->uploadCover($files);
        if (is_array($coverBaru)) {
            return self::gagal(422, $coverBaru['error']);
        }

        $fileBacaBaru = $this->fileUploader->uploadFileBaca($files);
        if (is_array($fileBacaBaru)) {
            $this->hapusFile($coverBaru, 'cover');
            return self::gagal(422, $fileBacaBaru['error']);
        }

        $coverLama = $bukuLama['cover'] ?? '';
        $fileBacaLama = $bukuLama['file_baca'] ?? '';

        $cover = $coverBaru !== '' ? $coverBaru : $coverLama;

        if ($fileBacaBaru !== '') {
            $fileBaca = $fileBacaBaru;
        } elseif (!empty($data['hapus_file_baca'])) {
            $fileBaca = '';   // checkbox "Hapus file baca yang sudah ada"
        } else {
            $fileBaca = $fileBacaLama;
        }

        $query = "UPDATE buku SET judul = :judul, penulis = :penulis, klasifikasi = :klasifikasi, sinopsis = :sinopsis, link_baca = :link_baca, cover = :cover, file_baca = :file_baca WHERE id = :id";

        $this->db->query($query);
        $this->db->bind('judul', $data['judul'] ?? '');
        $this->db->bind('penulis', $data['penulis'] ?? '');
        $this->db->bind('klasifikasi', $data['klasifikasi'] ?? '');
        $this->db->bind('sinopsis', $data['sinopsis'] ?? '');
        $this->db->bind('link_baca', $data['link_baca'] ?? '');
        $this->db->bind('cover', $cover);
        $this->db->bind('file_baca', $fileBaca);
        $this->db->bind('id', $id, PDO::PARAM_INT);

        if (!$this->db->execute()) {
            // update gagal: buang file baru, data lama tetap utuh
            $this->hapusFile($coverBaru, 'cover');
            $this->hapusFile($fileBacaBaru, 'pdf');
            return self::gagal(500, 'Terjadi kesalahan pada server');
        }

        // update berhasil: file lama yang sudah tidak dipakai boleh dibuang
        if ($cover !== $coverLama) {
            $this->hapusFile($coverLama, 'cover', $id);
        }
        if ($fileBaca !== $fileBacaLama) {
            $this->hapusFile($fileBacaLama, 'pdf', $id);
        }

        return true;
    }

    public function hapusBuku(int $id)
    {
        $buku = $this->getBukuById($id);
        if (!$buku) {
            return self::tidakDitemukan();
        }

        $this->db->query('DELETE FROM buku WHERE id = :id');
        $this->db->bind('id', $id, PDO::PARAM_INT);

        if (!$this->db->execute()) {
            return self::gagal(500, 'Terjadi kesalahan pada server');
        }

        // baris sudah terhapus, sekarang file cover & pdf-nya ikut dibersihkan dari disk
        $this->hapusFile($buku['cover'] ?? '', 'cover', $id);
        $this->hapusFile($buku['file_baca'] ?? '', 'pdf', $id);

        return true;
    }

    public function updateStatusBaca(int $id, string $status)
    {
        if (!$this->ada($id)) {
            return self::tidakDitemukan();
        }

        $this->db->query('UPDATE buku SET status_baca = :status WHERE id = :id');
        $this->db->bind('status', $status);
        $this->db->bind('id', $id, PDO::PARAM_INT);

        return $this->db->execute() ? true : self::gagal(500, 'Status baca gagal disimpan');
    }

    public function updateProgress(int $id, int $halamanDibaca, ?int $totalHalaman)
    {
        if (!$this->ada($id)) {
            return self::tidakDitemukan();
        }

        $this->db->query('UPDATE buku SET halaman_dibaca = :halaman_dibaca, total_halaman = :total_halaman WHERE id = :id');
        $this->db->bind('halaman_dibaca', $halamanDibaca, PDO::PARAM_INT);
        $this->db->bind('total_halaman', $totalHalaman);
        $this->db->bind('id', $id, PDO::PARAM_INT);

        return $this->db->execute() ? true : self::gagal(500, 'Progress gagal disimpan');
    }

    public function updateCatatanPribadi(int $id, string $catatan)
    {
        if (!$this->ada($id)) {
            return self::tidakDitemukan();
        }

        $this->db->query('UPDATE buku SET catatan_pribadi = :catatan WHERE id = :id');
        $this->db->bind('catatan', $catatan);
        $this->db->bind('id', $id, PDO::PARAM_INT);

        return $this->db->execute() ? true : self::gagal(500, 'Catatan gagal disimpan');
    }
}
