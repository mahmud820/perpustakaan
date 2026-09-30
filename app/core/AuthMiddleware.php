<?php

class AuthMiddleware
{
    // Sesi otomatis logout setelah tidak ada aktivitas selama ini (detik)
    const IDLE_TIMEOUT = 30 * 60; // 30 menit

    // Regenerasi session id secara berkala walau tidak ada perubahan hak akses,
    // supaya session id lama yang mungkin bocor (misal ter-log di proxy) cepat basi.
    const REGENERATE_INTERVAL = 15 * 60; // 15 menit

    public static function requireLogin(): void
    {
        if (empty($_SESSION['login'])) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        self::enforceIdleTimeout();
        self::regenerateIdPeriodically();
    }

    /**
     * Versi requireLogin()/requireAdmin() untuk endpoint AJAX (fetch).
     * Endpoint AJAX TIDAK boleh me-redirect ke halaman login: fetch akan mengikuti redirect
     * dan JavaScript menerima HTML halaman login (status 200) sebagai "jawaban".
     * Karena itu di sini hasilnya berupa [status HTTP, pesan] atau null kalau lolos.
     */
    public static function ajaxAuthError(bool $butuhAdmin = true): ?array
    {
        if (empty($_SESSION['login'])) {
            return [401, 'Sesi berakhir. Silakan login kembali.'];
        }

        if (self::isIdleExpired()) {
            self::destroySession();
            return [401, 'Sesi berakhir. Silakan login kembali.'];
        }

        $_SESSION['last_activity'] = time();
        self::regenerateIdPeriodically();

        if ($butuhAdmin && ($_SESSION['role'] ?? '') !== 'admin') {
            return [403, 'Akses ditolak'];
        }

        return null;
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();

        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            die('403 Forbidden - Anda tidak memiliki izin untuk mengakses halaman ini.');
        }
    }

    public static function isLogin(): bool
    {
        if (empty($_SESSION['login'])) {
            return false;
        }

        // Untuk pengecekan pasif (mis. render menu navbar) jangan sampai memicu redirect,
        // cukup anggap tidak login lagi kalau sudah idle terlalu lama.
        if (self::isIdleExpired()) {
            self::destroySession();
            return false;
        }

        return true;
    }

    public static function isAdmin(): bool
    {
        return self::isLogin() && ($_SESSION['role'] ?? '') === 'admin';
    }

    // Tandai sesi sebagai baru login (dipanggil dari Auth::login setelah kredensial valid)
    public static function markLoggedIn(array $user): void
    {
        // Regenerasi id sesi mencegah session fixation
        session_regenerate_id(true);

        $_SESSION['login'] = true;
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['gambar'] = $user['gambar'] ?? null;
        $_SESSION['last_activity'] = time();
        $_SESSION['last_regenerate'] = time();
    }

    private static function enforceIdleTimeout(): void
    {
        if (self::isIdleExpired()) {
            self::destroySession();
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        $_SESSION['last_activity'] = time();
    }

    private static function isIdleExpired(): bool
    {
        $lastActivity = $_SESSION['last_activity'] ?? null;

        if ($lastActivity === null) {
            // Sesi lama sebelum fitur ini ada / tidak konsisten -> anggap belum expired,
            // tapi langsung catat waktu sekarang.
            $_SESSION['last_activity'] = time();
            return false;
        }

        return (time() - $lastActivity) > self::IDLE_TIMEOUT;
    }

    private static function regenerateIdPeriodically(): void
    {
        $lastRegenerate = $_SESSION['last_regenerate'] ?? time();

        if ((time() - $lastRegenerate) > self::REGENERATE_INTERVAL) {
            session_regenerate_id(false);
            $_SESSION['last_regenerate'] = time();
        }
    }

    public static function destroySession(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }
}
