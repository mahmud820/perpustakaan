<?php

class DaftarBuku extends Controller
{
  public function index()
  {
    $data['judul'] = 'Daftar-Buku';
    $keyword = $_GET['keyword'] ?? '';

    if (!empty($keyword)) {
        $data['buku'] = $this->model('Buku')->cariBuku($keyword);
    } else {
        $data['buku'] = $this->model('Buku')->getAllBuku();
    }


    $this->view('templates/header', $data);
    $this->view('daftarBuku/index', $data);
    $this->view('templates/footer');
  }

  public function detail($id)
  {
    $data['judul'] = 'Detail Buku';
    $data['buku'] = $this->model('Buku')->getBukuById($id);

    $this->view('templates/header', $data);
    $this->view('daftarBuku/detail', $data);
    $this->view('templates/footer');
  }

  public function tambah()
  {
      $hasil = $this->model('Buku')
                    ->tambahBuku($_POST, $_FILES);

      if ($hasil > 0) {

          echo 'success';

      } else {

          echo 'failed';
      }
  }

  public function update()
  {
      $hasil = $this->model('Buku')
                    ->updateBuku($_POST, $_FILES);

      if ($hasil > 0) {

          echo 'success';

      } else {

          echo 'failed';
      }
  }

  public function hapus()
  {
      $hasil = $this->model('Buku')
                    ->hapusBuku($_POST);

      if ($hasil > 0) {

          echo 'success';

      } else {

          echo 'failed';
      }
  }

}
