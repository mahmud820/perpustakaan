<?php

/**
 * CSRF Protection
 * Token disimpan di session, dikirim balik lewat hidden input (form biasa)
 * atau header X-CSRF-Token (request AJAX/fetch).
 */
class Csrf
{
  const SESSION_KEY = 'csrf_token';
  const FIELD_NAME  = 'csrf_token';
  const HEADER_NAME = 'X-CSRF-Token';

  // Ambil token yang sedang aktif, buat baru jika belum ada
  public static function token(): string
  {
    if (empty($_SESSION[self::SESSION_KEY])) {
      $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
    }

    return $_SESSION[self::SESSION_KEY];
  }

  // Render hidden input siap pakai di dalam <form>
  public static function field(): string
  {
    return '<input type="hidden" name="' . self::FIELD_NAME . '" value="' . htmlspecialchars(self::token()) . '">';
  }

  // Verifikasi token dari POST body ATAU header X-CSRF-Token (untuk request fetch berbasis FormData/JSON)
  public static function verify(): bool
  {
    $token = $_POST[self::FIELD_NAME]
      ?? $_SERVER['HTTP_X_CSRF_TOKEN']
      ?? '';

    if (empty($_SESSION[self::SESSION_KEY]) || $token === '') {
      return false;
    }

    return hash_equals($_SESSION[self::SESSION_KEY], $token);
  }

  // Dipanggil di awal setiap handler POST yang perlu proteksi CSRF.
  // Menghentikan eksekusi (403) jika token tidak valid.
  public static function guard(): void
  {
    if (!self::verify()) {
      http_response_code(403);
      echo 'Sesi tidak valid atau kedaluwarsa (CSRF). Muat ulang halaman dan coba lagi.';
      exit;
    }
  }
}
