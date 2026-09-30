<?php

class User
{
    private $db;
    private $fileUploader;

    public function __construct()
    {
        $this->db = new Database;
        $this->fileUploader = new FileUploader();
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

    public function updateProfileData($data, $files)
    {
        $id = $_SESSION['user_id'];

        $nama     = trim($data['nama'] ?? '');
        $email    = trim($data['email'] ?? '');
        $no_telp  = trim($data['no_telp'] ?? '');
        $tagline  = trim($data['tagline'] ?? '');
        $tentang  = trim($data['tentang'] ?? '');

        // Nama foto lama diambil dari database, BUKAN dari form: nilai dari form bisa dipalsukan
        // sehingga file milik orang lain bisa terhapus / kolom gambar terisi nama sembarang.
        $userLama = $this->getUserById($id);
        $gambarLama = $userLama['gambar'] ?? '';
        $hasilUpload = $this->fileUploader->uploadProfil($files);

        if (is_array($hasilUpload)) {
            return ['success' => false, 'error' => $hasilUpload['error']];
        }

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
            if ($gambarBaruDiupload) {
                $this->fileUploader->deleteFile($gambar, 'profile');
            }
            return ['success' => false, 'error' => 'Gagal menyimpan perubahan ke database'];
        }

        if ($gambarBaruDiupload && !empty($gambarLama) && $gambarLama !== 'default.jpg') {
            $this->fileUploader->deleteFile($gambarLama, 'profile');
        }

        return ['success' => true, 'gambar' => $gambar];
    }
}
