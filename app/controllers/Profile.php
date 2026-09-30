<?php

class Profile extends Controller
{
    public function index()
    {
        AuthMiddleware::requireLogin();

        $data['judul'] = 'Profil Saya';
        $data['user'] = $this->model('User')->getUserById($_SESSION['user_id']);

        $this->view('templates/header', $data);
        $this->view('profile/index', $data);
        $this->view('templates/footer');
    }

    public function update()
    {
        // WAJIB login: tanpa ini, pengunjung anonim bisa mengirim POST dan memicu upload file
        AuthMiddleware::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo '405 Method Not Allowed';
            return;
        }

        Csrf::guard();

        $nama = $this->post('nama');
        $email = $this->post('email');
        $noTelp = $this->post('no_telp');
        $tagline = $this->post('tagline');
        $tentang = $this->post('tentang');

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

        $hasil = $this->model('User')->updateProfileData([
            'nama' => $nama,
            'email' => $email,
            'no_telp' => $noTelp,
            'tagline' => $tagline,
            'tentang' => $tentang,
        ], $_FILES);

        if ($hasil['success']) {
            $_SESSION['flash_success'] = 'Profil berhasil diperbarui';
            $_SESSION['nama'] = $nama;
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
