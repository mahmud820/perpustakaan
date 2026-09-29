<?php

class Profile extends Controller
{
    public function index()
    {
        $userId = $_SESSION['user_id'] ?? null;

        if ($userId === null) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        $data['title'] = 'Profil Saya';
        $data['user'] = $this->model('User')->getUserById($userId);

        $this->view('templates/header', $data);
        $this->view('profile/index', $data);
        $this->view('templates/footer');
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo '405 Method Not Allowed';
            return;
        }

        Csrf::guard();

        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $noTelp = trim($_POST['no_telp'] ?? '');
        $tagline = trim($_POST['tagline'] ?? '');
        $tentang = trim($_POST['tentang'] ?? '');

        $errors = [
            'nama' => Validator::nama($nama),
            'email' => Validator::email($email),
            'no_telp' => Validator::noTelp($noTelp),
            'tagline' => Validator::tagline($tagline),
            'tentang' => Validator::tentang($tentang),
        ];

        foreach ($errors as $error) {
            if ($error !== null) {
                $_SESSION['flash_error'] = $error;
                header('Location: ' . BASEURL . '/profile');
                exit;
            }
        }

        $hasil = $this->model('User')->updateProfileData($_POST, $_FILES);

        if ($hasil['success']) {
            $_SESSION['flash_success'] = 'Profil berhasil diperbarui';
            $_SESSION['nama'] = trim($_POST['nama']);
            if (!empty($hasil['gambar'])) {
                $_SESSION['gambar'] = $hasil['gambar'];
            }
        } else {
            $_SESSION['flash_error'] = $hasil['error'];
        }

        header('Location: ' . BASEURL . '/profile');
        exit;
    }
}
