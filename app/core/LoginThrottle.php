<?php

/**
 * Rate limiting percobaan login (mitigasi brute force).
 * Disimpan di file JSON kecil di luar folder public (tidak bisa diakses langsung dari web),
 * jadi tidak butuh tabel database tambahan.
 *
 * Batas: MAX_ATTEMPTS kegagalan dalam WINDOW_SECONDS -> terkunci selama LOCKOUT_SECONDS.
 * Dikunci berdasarkan kombinasi IP + username agar satu user nakal tidak mengunci semua IP,
 * dan satu IP dengan banyak username tetap dibatasi.
 */
class LoginThrottle
{
  const MAX_ATTEMPTS     = 5;
  const WINDOW_SECONDS   = 15 * 60; // 15 menit
  const LOCKOUT_SECONDS  = 15 * 60; // 15 menit

  private static function storagePath(): string
  {
    $dir = dirname(__DIR__) . '/storage';

    if (!is_dir($dir)) {
      mkdir($dir, 0755, true);
    }

    return $dir . '/login_attempts.json';
  }

  private static function key(string $username): string
  {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    return hash('sha256', $ip . '|' . strtolower($username));
  }

  private static function readAll(): array
  {
    $path = self::storagePath();

    if (!file_exists($path)) {
      return [];
    }

    $fh = fopen($path, 'c+');
    if (!$fh) {
      return [];
    }

    flock($fh, LOCK_SH);
    $content = stream_get_contents($fh);
    flock($fh, LOCK_UN);
    fclose($fh);

    $data = json_decode((string) $content, true);
    return is_array($data) ? $data : [];
  }

  private static function writeAll(array $data): void
  {
    $path = self::storagePath();
    $fh = fopen($path, 'c+');
    if (!$fh) {
      return;
    }

    flock($fh, LOCK_EX);
    ftruncate($fh, 0);
    rewind($fh);
    fwrite($fh, json_encode($data));
    fflush($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
  }

  // Return jumlah detik sisa lockout, 0 jika tidak terkunci
  public static function secondsUntilUnlocked(string $username): int
  {
    $data = self::readAll();
    $key = self::key($username);
    $now = time();

    if (!isset($data[$key])) {
      return 0;
    }

    $entry = $data[$key];

    if (!empty($entry['locked_until']) && $entry['locked_until'] > $now) {
      return $entry['locked_until'] - $now;
    }

    return 0;
  }

  public static function recordFailure(string $username): void
  {
    $data = self::readAll();
    $key = self::key($username);
    $now = time();

    $entry = $data[$key] ?? ['count' => 0, 'first_attempt' => $now, 'locked_until' => 0];

    // Reset window kalau sudah lewat WINDOW_SECONDS sejak percobaan pertama
    if (($now - $entry['first_attempt']) > self::WINDOW_SECONDS) {
      $entry = ['count' => 0, 'first_attempt' => $now, 'locked_until' => 0];
    }

    $entry['count']++;

    if ($entry['count'] >= self::MAX_ATTEMPTS) {
      $entry['locked_until'] = $now + self::LOCKOUT_SECONDS;
    }

    $data[$key] = $entry;

    // Buang entri lama supaya file tidak membengkak
    foreach ($data as $k => $v) {
      if (($now - ($v['first_attempt'] ?? 0)) > (self::WINDOW_SECONDS + self::LOCKOUT_SECONDS)) {
        unset($data[$k]);
      }
    }

    self::writeAll($data);
  }

  public static function recordSuccess(string $username): void
  {
    $data = self::readAll();
    $key = self::key($username);
    unset($data[$key]);
    self::writeAll($data);
  }
}
