<?php

class Profile extends Controller
{

  public function index()
  {
    // Ambil ID user dari session yang sedang login
    $userId = $_SESSION['user_id'];

    $data['title'] = 'Profil Saya';
    $data['user'] = $this->model('User')->getUserById($userId);

    $this->view('templates/header', $data);
    $this->view('profile/index', $data);
    $this->view('templates/footer');
  }

  public function update()
  {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      // CSRF PROTECTION
      Csrf::guard();

      $hasil = $this->model('User')->updateProfileData($_POST, $_FILES);

      if ($hasil['success']) {
        $_SESSION['flash_success'] = 'Profil berhasil diperbarui';
        // Sinkronkan data di session supaya navbar (nama & foto) langsung ter-update
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
}
