<?php

class User
{
    private $db;
    private $folderGambar;

    public function __construct()
    {
        $this->db = new Database;
        $this->folderGambar = dirname(__DIR__, 2) . '/public/img/profil/';
    }

    public function getByUsername(string $username)
    {
        $query = "SELECT id, nama, username, password, role, gambar FROM users WHERE username = :username LIMIT 1";

        $this->db->query($query);
        $this->db->bind('username', $username);

        return $this->db->single();
    }

    public function createUser(string $nama, string $username, string $passwordPlain, string $role = 'admin'): bool
    {
        $hash = password_hash($passwordPlain, PASSWORD_DEFAULT);

        $query = "INSERT INTO users (nama, username, password, role, created_at)
                  VALUES (:nama, :username, :password, :role, NOW())";

        $this->db->query($query);
        $this->db->bind('nama', $nama);
        $this->db->bind('username', $username);
        $this->db->bind('password', $hash);
        $this->db->bind('role', $role);

        return $this->db->execute();
    }

    public function getUserById($id)
    {
        $this->db->query('SELECT * FROM users WHERE id = :id');
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    // Return: ['success' => true] jika berhasil,
    //         atau ['success' => false, 'error' => pesan] jika gagal validasi.
    public function updateProfileData($data, $files)
    {
        $id = $_SESSION['user_id'];

        // Sanitasi ringan saja di sini (trim). Escaping untuk tampilan (htmlspecialchars)
        // dilakukan di VIEW saat data ditampilkan, bukan saat disimpan ke DB,
        // supaya data tidak ter-double-encode saat form dibuka ulang untuk diedit.
        $nama     = trim($data['nama'] ?? '');
        $email    = trim($data['email'] ?? '');
        $no_telp  = trim($data['no_telp'] ?? '');
        $tagline  = trim($data['tagline'] ?? '');
        $tentang  = trim($data['tentang'] ?? '');

        if ($nama === '' || $email === '') {
            return ['success' => false, 'error' => 'Nama dan email wajib diisi'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Format email tidak valid'];
        }

        // Cegah email bentrok dengan akun lain
        $this->db->query('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1');
        $this->db->bind('email', $email);
        $this->db->bind('id', $id);
        if ($this->db->single()) {
            return ['success' => false, 'error' => 'Email sudah digunakan akun lain'];
        }

        // Handling Upload Gambar
        $gambarLama = $data['gambarLama'] ?? '';
        $hasilUpload = $this->uploadGambar($files['gambar'] ?? null);

        if (is_array($hasilUpload)) {
            // ['error' => pesan] -> gagal validasi/upload, jangan sentuh foto lama
            return ['success' => false, 'error' => $hasilUpload['error']];
        }

        // string kosong '' = tidak ada file baru dikirim -> pertahankan foto lama
        $gambar = $hasilUpload === '' ? $gambarLama : $hasilUpload;
        $gambarBaruDiupload = $hasilUpload !== '';

        $query = "UPDATE users SET 
                    nama = :nama, 
                    email = :email, 
                    no_telp = :no_telp, 
                    tagline = :tagline, 
                    tentang = :tentang, 
                    gambar = :gambar 
                  WHERE id = :id";

        $this->db->query($query);
        $this->db->bind('nama', $nama);
        $this->db->bind('email', $email);
        $this->db->bind('no_telp', $no_telp);
        $this->db->bind('tagline', $tagline);
        $this->db->bind('tentang', $tentang);
        $this->db->bind('gambar', $gambar);
        $this->db->bind('id', $id);

        if (!$this->db->execute()) {
            // Rollback file yang sudah terlanjur diupload kalau query gagal
            if ($gambarBaruDiupload && file_exists($this->folderGambar . $gambar)) {
                unlink($this->folderGambar . $gambar);
            }
            return ['success' => false, 'error' => 'Gagal menyimpan perubahan ke database'];
        }

        // Hapus foto lama dari disk kalau berhasil ganti foto baru
        if ($gambarBaruDiupload && !empty($gambarLama) && $gambarLama !== 'default.jpg' && file_exists($this->folderGambar . $gambarLama)) {
            unlink($this->folderGambar . $gambarLama);
        }

        return ['success' => true, 'gambar' => $gambar];
    }

    // Return: string nama file baru, '' jika tidak ada file dikirim,
    //         atau array ['error' => pesan] jika gagal validasi/upload.
    private function uploadGambar($file)
    {
        if (empty($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return '';
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Upload foto gagal'];
        }

        $maxSize = 2 * 1024 * 1024; // 2 MB
        if ($file['size'] > $maxSize) {
            return ['error' => 'Ukuran foto maksimal 2 MB'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($allowedMimeTypes[$mimeType])) {
            return ['error' => 'Format foto harus JPG, PNG, atau WEBP'];
        }

        // Pastikan file benar-benar bisa didekode sebagai gambar,
        // bukan cuma file dengan MIME type yang "kelihatan" cocok (mis. polyglot file).
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['error' => 'File rusak atau bukan gambar yang valid'];
        }

        $extension = $allowedMimeTypes[$mimeType];
        $namaFileBaru = bin2hex(random_bytes(16)) . '.' . $extension;

        if (!is_dir($this->folderGambar) && !mkdir($this->folderGambar, 0755, true)) {
            return ['error' => 'Folder foto tidak ditemukan'];
        }

        if (!move_uploaded_file($file['tmp_name'], $this->folderGambar . $namaFileBaru)) {
            return ['error' => 'Foto gagal disimpan'];
        }

        return $namaFileBaru;
    }
}
