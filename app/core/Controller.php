<?php

class Controller
{
  // view() dan model() sengaja `protected`: hanya boleh dipanggil dari dalam controller.
  // Kalau public, URL seperti /daftarBuku/view/xxx akan ikut bisa dijalankan oleh router.
  protected function view($view, $data = [])
  {
    require __DIR__ . '/../views/' . $view . '.php';
  }

  protected function model($model)
  {
    // class model dimuat otomatis oleh autoloader di init.php
    return new $model;
  }

  // Ambil field POST sebagai string yang sudah di-trim.
  // Kalau field dikirim sebagai array (mis. judul[]=x) hasilnya $default, bukan error fatal.
  protected function post(string $key, string $default = ''): string
  {
    $value = $_POST[$key] ?? $default;

    return is_string($value) ? trim($value) : $default;
  }

  // Sama seperti post(), tetapi untuk query string (?keyword=...)
  protected function query(string $key, string $default = ''): string
  {
    $value = $_GET[$key] ?? $default;

    return is_string($value) ? trim($value) : $default;
  }

  // Kirim respon teks biasa lalu hentikan eksekusi.
  // Kontrak endpoint AJAX: status 200 + body "success" = berhasil, selain itu = gagal + body pesan error.
  protected function respond(int $code, string $message = 'success'): void
  {
    http_response_code($code);
    echo $message;
    exit;
  }

  // Ubah hasil method model menjadi respon HTTP.
  // Model mengembalikan: true (berhasil) atau ['code' => int, 'error' => string] (gagal).
  protected function respondResult($hasil): void
  {
    if ($hasil === true) {
      $this->respond(200);
    }

    if (is_array($hasil) && isset($hasil['code'], $hasil['error'])) {
      $this->respond((int) $hasil['code'], (string) $hasil['error']);
    }

    $this->respond(500, 'Terjadi kesalahan pada server');
  }

  // Pengaman standar untuk endpoint AJAX yang mengubah data:
  // harus login sebagai admin -> method POST -> token CSRF valid.
  protected function guardAdminPost(): void
  {
    $gagal = AuthMiddleware::ajaxAuthError(true);

    if ($gagal !== null) {
      $this->respond($gagal[0], $gagal[1]);
    }

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
      $this->respond(405, '405 Method Not Allowed');
    }

    Csrf::guard();
  }
}
