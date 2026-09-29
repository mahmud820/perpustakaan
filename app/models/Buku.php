<?php

class Buku
{
    private $db;

    // Path absolut (tidak tergantung current working directory server)
    private $folderCover;
    private $folderFile;

    // Daftar status baca yang valid (dipakai untuk validasi & dropdown)
    const STATUS_BACA = [
        'belum_dibaca'  => 'Belum Dibaca',
        'sedang_dibaca' => 'Sedang Dibaca',
        'selesai_dibaca' => 'Selesai Dibaca',
    ];

    public function __construct()
    {
        $this->db = new Database;

        // __DIR__ = .../app/models -> naik 2 folder ke root project, lalu ke public/...
        $this->folderCover = dirname(__DIR__, 2) . '/public/img/dataGambar/';
        $this->folderFile  = dirname(__DIR__, 2) . '/public/uploads/pdf/';
    }

    // Ambil semua buku (dengan dukungan pagination opsional)
    public function getAllBuku($limit = null, $offset = null)
    {
        $query = "SELECT * FROM buku ORDER BY created_at DESC";

        if ($limit !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }

        $this->db->query($query);

        if ($limit !== null) {
            $this->db->bind('limit', (int) $limit);
            $this->db->bind('offset', (int) $offset);
        }

        return $this->db->resultSet();
    }

    // Hitung total semua buku (untuk pagination)
    public function countAllBuku()
    {
        $query = "SELECT COUNT(*) AS total FROM buku";

        $this->db->query($query);
        $hasil = $this->db->single();

        return (int) ($hasil['total'] ?? 0);
    }

    // Ambil buku berdasarkan id
    public function getBukuById($id)
    {
        $query = "SELECT * FROM buku WHERE id = :id";

        $this->db->query($query);
        $this->db->bind('id', $id);

        return $this->db->single();
    }

    // =========================
    // STATISTIK DASHBOARD ADMIN
    // =========================

    // Hitung jumlah buku berdasarkan status_baca tertentu
    public function countByStatus(string $status): int
    {
        if (!array_key_exists($status, self::STATUS_BACA)) {
            return 0;
        }

        $query = "SELECT COUNT(*) AS total FROM buku WHERE status_baca = :status";

        $this->db->query($query);
        $this->db->bind('status', $status);
        $hasil = $this->db->single();

        return (int) ($hasil['total'] ?? 0);
    }

    // Ambil N buku terbaru (berdasarkan created_at) untuk widget "Buku Terbaru"
    public function getBukuTerbaru(int $limit = 5)
    {
        $limit = max(1, min(50, $limit)); // batasi wajar, hindari limit sembarangan

        $query = "SELECT id, judul, penulis, cover, status_baca, created_at
                  FROM buku
                  ORDER BY created_at DESC
                  LIMIT :limit";

        $this->db->query($query);
        $this->db->bind('limit', $limit);

        return $this->db->resultSet();
    }

    // Kumpulan statistik siap pakai untuk Dashboard Admin
    public function getDashboardStats(int $jumlahTerbaru = 5): array
    {
        return [
            'total'          => $this->countAllBuku(),
            'sedang_dibaca'  => $this->countByStatus('sedang_dibaca'),
            'selesai_dibaca' => $this->countByStatus('selesai_dibaca'),
            'belum_dibaca'   => $this->countByStatus('belum_dibaca'),
            'terbaru'        => $this->getBukuTerbaru($jumlahTerbaru),
        ];
    }

    // Ambil daftar klasifikasi unik (untuk dropdown filter)
    public function getAllKlasifikasi()
    {
        $query = "SELECT DISTINCT klasifikasi FROM buku
                  WHERE klasifikasi IS NOT NULL AND klasifikasi <> ''
                  ORDER BY klasifikasi ASC";

        $this->db->query($query);

        return $this->db->resultSet();
    }

    // Pencarian + Filter (menggantikan cariBuku lama, tetap kompatibel)
    // Mendukung pagination opsional lewat $limit / $offset.
    public function cariBuku($keyword = '', $klasifikasi = '', $limit = null, $offset = null)
    {
        $query = "SELECT * FROM buku" . $this->buildWhereKeywordKlasifikasi($keyword, $klasifikasi) . " ORDER BY created_at DESC";

        if ($limit !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }

        $this->db->query($query);
        $this->bindKeywordKlasifikasi($keyword, $klasifikasi);

        if ($limit !== null) {
            $this->db->bind('limit', (int) $limit);
            $this->db->bind('offset', (int) $offset);
        }

        return $this->db->resultSet();
    }

    // Hitung total hasil pencarian + filter (untuk pagination)
    public function countCariBuku($keyword = '', $klasifikasi = '')
    {
        $query = "SELECT COUNT(*) AS total FROM buku" . $this->buildWhereKeywordKlasifikasi($keyword, $klasifikasi);

        $this->db->query($query);
        $this->bindKeywordKlasifikasi($keyword, $klasifikasi);
        $hasil = $this->db->single();

        return (int) ($hasil['total'] ?? 0);
    }

    // Helper: bangun klausa WHERE untuk keyword + klasifikasi (dipakai cariBuku & countCariBuku)
    private function buildWhereKeywordKlasifikasi($keyword, $klasifikasi)
    {
        $where = [];

        if ($keyword !== '') {
            $where[] = "(judul LIKE :keyword OR penulis LIKE :keyword OR klasifikasi LIKE :keyword)";
        }

        if ($klasifikasi !== '') {
            $where[] = "klasifikasi = :klasifikasi";
        }

        return !empty($where) ? " WHERE " . implode(' AND ', $where) : '';
    }

    // Helper: bind parameter keyword + klasifikasi (harus sesuai urutan query di atas)
    private function bindKeywordKlasifikasi($keyword, $klasifikasi)
    {
        if ($keyword !== '') {
            $this->db->bind('keyword', '%' . $keyword . '%');
        }

        if ($klasifikasi !== '') {
            $this->db->bind('klasifikasi', $klasifikasi);
        }
    }

    // Tambah buku
    public function tambahBuku($data, $files)
    {
        // =========================
        // VALIDASI DAN UPLOAD COVER
        // =========================
        $cover = $this->uploadCover($files);
        if (is_array($cover)) {
            return [
                'code' => 422,
                'error' => $cover['error']
            ];
        }

        // =========================
        // VALIDASI DAN UPLOAD FILE BACA (PDF)
        // =========================
        $fileBaca = $this->uploadFileBaca($files);
        if (is_array($fileBaca)) {
            // Rollback cover yang sudah terlanjur diupload
            if ($cover !== '' && file_exists($this->folderCover . $cover)) {
                unlink($this->folderCover . $cover);
            }
            return [
                'code' => 422,
                'error' => $fileBaca['error']
            ];
        }

        // =========================
        // INSERT DATABASE
        // =========================

        $query = "INSERT INTO buku
                (
                    judul,
                    penulis,
                    klasifikasi,
                    sinopsis,
                    cover,
                    link_baca,
                    file_baca
                )
                VALUES
                (
                    :judul,
                    :penulis,
                    :klasifikasi,
                    :sinopsis,
                    :cover,
                    :link_baca,
                    :file_baca
                )";

        $this->db->query($query);

        $this->db->bind('judul', $data['judul']);
        $this->db->bind('penulis', $data['penulis']);
        $this->db->bind('klasifikasi', $data['klasifikasi']);
        $this->db->bind('sinopsis', $data['sinopsis']);
        $this->db->bind('cover', $cover);
        $this->db->bind('link_baca', $data['link_baca']);
        $this->db->bind('file_baca', $fileBaca);

        if (!$this->db->execute()) {

            // Jika database gagal,
            // hapus file yang sudah terlanjur diupload.
            if ($cover !== '' && file_exists($this->folderCover . $cover)) {
                unlink($this->folderCover . $cover);
            }
            if ($fileBaca !== '' && file_exists($this->folderFile . $fileBaca)) {
                unlink($this->folderFile . $fileBaca);
            }

            return 'Data buku gagal disimpan';
        }

        return true;
    }

    // Update buku
    public function updateBuku($data, $files)
    {
        // Ambil data buku lama dari database
        $bukuLama = $this->getBukuById($data['id']);
        if (!$bukuLama) {
            return 'Buku tidak ditemukan';
        }

        $coverLama = $bukuLama['cover'];
        $coverBaru = $coverLama;
        $uploadCoverBaru = false;

        $fileBacaLama = $bukuLama['file_baca'] ?? '';
        $fileBacaBaru = $fileBacaLama;
        $uploadFileBaru = false;

        // =========================
        // UPLOAD COVER BARU (jika ada)
        // =========================
        $hasilCover = $this->uploadCover($files);
        if (is_array($hasilCover)) {
            return [
                'code' => 422,
                'error' => $hasilCover['error']
            ];
        }
        if ($hasilCover !== '') {
            $coverBaru = $hasilCover;
            $uploadCoverBaru = true;
        }

        // =========================
        // UPLOAD FILE BACA BARU (jika ada)
        // =========================
        $hasilFile = $this->uploadFileBaca($files);
        if (is_array($hasilFile)) {
            if ($uploadCoverBaru && file_exists($this->folderCover . $coverBaru)) {
                unlink($this->folderCover . $coverBaru);
            }
            return [
                'code' => 422,
                'error' => $hasilFile['error']
            ];
        }
        if ($hasilFile !== '') {
            $fileBacaBaru = $hasilFile;
            $uploadFileBaru = true;
        }

        // Permintaan eksplisit untuk menghapus file baca lama (tanpa upload file baru)
        if (!$uploadFileBaru && !empty($data['hapus_file_baca'])) {
            $fileBacaBaru = '';
        }

        // =========================
        // UPDATE DATABASE
        // =========================
        $query = "UPDATE buku SET
                    judul = :judul,
                    penulis = :penulis,
                    klasifikasi = :klasifikasi,
                    sinopsis = :sinopsis,
                    cover = :cover,
                    link_baca = :link_baca,
                    file_baca = :file_baca
                  WHERE id = :id";

        $this->db->query($query);
        $this->db->bind('id', $data['id']);
        $this->db->bind('judul', $data['judul']);
        $this->db->bind('penulis', $data['penulis']);
        $this->db->bind('klasifikasi', $data['klasifikasi']);
        $this->db->bind('sinopsis', $data['sinopsis']);
        $this->db->bind('cover', $coverBaru);
        $this->db->bind('link_baca', $data['link_baca']);
        $this->db->bind('file_baca', $fileBacaBaru);

        if (!$this->db->execute()) {
            // Database gagal -> hapus file baru yang sudah terlanjur disimpan
            if ($uploadCoverBaru && file_exists($this->folderCover . $coverBaru)) {
                unlink($this->folderCover . $coverBaru);
            }
            if ($uploadFileBaru && file_exists($this->folderFile . $fileBacaBaru)) {
                unlink($this->folderFile . $fileBacaBaru);
            }
            return 'Data buku gagal diupdate';
        }

        // =========================
        // HAPUS FILE LAMA (setelah update sukses)
        // =========================
        if ($uploadCoverBaru && !empty($coverLama) && file_exists($this->folderCover . $coverLama)) {
            unlink($this->folderCover . $coverLama);
        }

        if (($uploadFileBaru || !empty($data['hapus_file_baca'])) && !empty($fileBacaLama) && file_exists($this->folderFile . $fileBacaLama)) {
            unlink($this->folderFile . $fileBacaLama);
        }

        return true;
    }

    // Hapus buku
    // Catatan keamanan: nama file cover/file_baca TIDAK diambil dari input client,
    // melainkan diambil ulang dari database berdasarkan id agar tidak bisa
    // dimanipulasi untuk menghapus file sembarangan di server.
    public function hapusBuku($data)
    {
        $id = $data['id'] ?? null;

        if (empty($id) || !ctype_digit((string) $id)) {
            return 0;
        }

        $buku = $this->getBukuById($id);

        if (!$buku) {
            return 0;
        }

        $query = "DELETE FROM buku WHERE id = :id";

        $this->db->query($query);
        $this->db->bind('id', $id);
        $this->db->execute();

        $terhapus = $this->db->rowCount();

        if ($terhapus > 0) {
            if (!empty($buku['cover']) && file_exists($this->folderCover . $buku['cover'])) {
                unlink($this->folderCover . $buku['cover']);
            }
            if (!empty($buku['file_baca']) && file_exists($this->folderFile . $buku['file_baca'])) {
                unlink($this->folderFile . $buku['file_baca']);
            }
        }

        return $terhapus;
    }

    // =========================
    // STATUS BACA
    // Update status baca sebuah buku (belum_dibaca / sedang_dibaca / selesai_dibaca)
    // =========================
    public function updateStatusBaca($id, $status)
    {
        if (!ctype_digit((string) $id) || !array_key_exists($status, self::STATUS_BACA)) {
            return false;
        }

        $query = "UPDATE buku SET status_baca = :status WHERE id = :id";

        $this->db->query($query);
        $this->db->bind('id', (int) $id);
        $this->db->bind('status', $status);

        return $this->db->execute();
    }

    // =========================
    // PROGRESS MEMBACA
    // $halamanDibaca dan $totalHalaman divalidasi sebagai bilangan bulat >= 0.
    // Jika total_halaman diisi, halaman_dibaca tidak boleh melebihi total_halaman.
    // Status baca otomatis disesuaikan mengikuti progress:
    //   0 halaman             -> belum_dibaca
    //   halaman == total      -> selesai_dibaca (jika total diisi)
    //   selain itu             -> sedang_dibaca
    // =========================
    public function updateProgress($id, $halamanDibaca, $totalHalaman)
    {
        if (!ctype_digit((string) $id)) {
            return 'ID buku tidak valid';
        }

        if (!ctype_digit((string) $halamanDibaca)) {
            return 'Halaman dibaca tidak valid';
        }

        $halamanDibaca = (int) $halamanDibaca;
        $totalHalaman = ($totalHalaman === '' || $totalHalaman === null) ? null : (int) $totalHalaman;

        if ($totalHalaman !== null && !ctype_digit((string) $totalHalaman)) {
            return 'Total halaman tidak valid';
        }

        if ($totalHalaman !== null && $halamanDibaca > $totalHalaman) {
            return 'Halaman dibaca tidak boleh melebihi total halaman';
        }

        // Tentukan status baca otomatis berdasarkan progress
        if ($halamanDibaca === 0) {
            $status = 'belum_dibaca';
        } elseif ($totalHalaman !== null && $halamanDibaca >= $totalHalaman) {
            $status = 'selesai_dibaca';
        } else {
            $status = 'sedang_dibaca';
        }

        $query = "UPDATE buku SET
                    halaman_dibaca = :halaman_dibaca,
                    total_halaman = :total_halaman,
                    status_baca = :status
                  WHERE id = :id";

        $this->db->query($query);
        $this->db->bind('id', $id);
        $this->db->bind('halaman_dibaca', $halamanDibaca);
        $this->db->bind('total_halaman', $totalHalaman);
        $this->db->bind('status', $status);

        return $this->db->execute() ? true : 'Progress gagal disimpan';
    }

    // =========================
    // CATATAN PRIBADI (hanya untuk Admin, tidak ditampilkan ke Guest)
    // =========================
    public function updateCatatanPribadi($id, $catatan)
    {
        if (!ctype_digit((string) $id)) {
            return false;
        }

        $query = "UPDATE buku SET catatan_pribadi = :catatan WHERE id = :id";

        $this->db->query($query);
        $this->db->bind('id', (int) $id);
        $this->db->bind('catatan', $catatan);

        return $this->db->execute();
    }

    // =========================
    // HELPER: UPLOAD COVER
    // Return: string nama file baru, '' jika tidak ada file dikirim,
    //         atau array ['error' => pesan] jika gagal validasi/upload.
    // =========================
    private function uploadCover($files)
    {
        if (!isset($files['cover']) || $files['cover']['error'] === UPLOAD_ERR_NO_FILE) {
            return '';
        }

        if ($files['cover']['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Upload cover gagal'];
        }

        $maxSize = 2 * 1024 * 1024; // 2 MB
        if ($files['cover']['size'] > $maxSize) {
            return ['error' => 'Ukuran cover maksimal 2 MB'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $files['cover']['tmp_name']);
        finfo_close($finfo);

        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($allowedMimeTypes[$mimeType])) {
            return ['error' => 'Format cover harus JPG, PNG, atau WEBP'];
        }

        // Validasi tambahan: pastikan file benar-benar bisa didekode sebagai gambar,
        // bukan cuma file dengan MIME type yang "kelihatan" cocok (mis. polyglot file).
        $imageInfo = @getimagesize($files['cover']['tmp_name']);
        if ($imageInfo === false) {
            return ['error' => 'File cover rusak atau bukan gambar yang valid'];
        }

        $extension = $allowedMimeTypes[$mimeType];
        $namaBaru = bin2hex(random_bytes(16)) . '.' . $extension;

        if (!is_dir($this->folderCover) && !mkdir($this->folderCover, 0755, true)) {
            return ['error' => 'Folder cover tidak ditemukan'];
        }

        if (!move_uploaded_file($files['cover']['tmp_name'], $this->folderCover . $namaBaru)) {
            return ['error' => 'Cover gagal disimpan'];
        }

        return $namaBaru;
    }

    // =========================
    // HELPER: UPLOAD FILE BACA (PDF)
    // Return: string nama file baru, '' jika tidak ada file dikirim,
    //         atau array ['error' => pesan] jika gagal validasi/upload.
    // =========================
    private function uploadFileBaca($files)
    {
        if (!isset($files['file_baca']) || $files['file_baca']['error'] === UPLOAD_ERR_NO_FILE) {
            return '';
        }

        if ($files['file_baca']['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Upload file baca gagal'];
        }

        $maxSize = 20 * 1024 * 1024; // 20 MB
        if ($files['file_baca']['size'] > $maxSize) {
            return ['error' => 'Ukuran file baca maksimal 20 MB'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $files['file_baca']['tmp_name']);
        finfo_close($finfo);

        if ($mimeType !== 'application/pdf') {
            return ['error' => 'File baca harus berformat PDF'];
        }

        // Validasi tambahan: cek magic bytes "%PDF-" di awal file agar tidak mudah
        // ditipu dengan file yang MIME-nya terdeteksi PDF tapi isinya bukan.
        $handle = @fopen($files['file_baca']['tmp_name'], 'rb');
        $header = $handle ? fread($handle, 5) : '';
        if ($handle) {
            fclose($handle);
        }
        if ($header !== '%PDF-') {
            return ['error' => 'File baca rusak atau bukan PDF yang valid'];
        }

        $namaBaru = bin2hex(random_bytes(16)) . '.pdf';

        if (!is_dir($this->folderFile) && !mkdir($this->folderFile, 0755, true)) {
            return ['error' => 'Folder file baca tidak ditemukan'];
        }

        if (!move_uploaded_file($files['file_baca']['tmp_name'], $this->folderFile . $namaBaru)) {
            return ['error' => 'File baca gagal disimpan'];
        }

        return $namaBaru;
    }
}
