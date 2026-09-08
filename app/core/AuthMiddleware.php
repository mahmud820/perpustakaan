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
        $_SESSION['last_activity'] = time();
        $_SESSION['last_regenerate'] = time();

        // Ikat sesi ke User-Agent kasar (mitigasi sebagian session hijacking,
        // tanpa mengikat ke IP karena IP pengguna mobile/proxy bisa wajar berubah-ubah)
        $_SESSION['ua_hash'] = self::userAgentHash();
    }

    private static function enforceIdleTimeout(): void
    {
        if (self::isIdleExpired() || !self::isSameUserAgent()) {
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

    private static function isSameUserAgent(): bool
    {
        if (empty($_SESSION['ua_hash'])) {
            // Sesi lama sebelum fitur ini ada, catat sekarang dan lanjutkan
            $_SESSION['ua_hash'] = self::userAgentHash();
            return true;
        }

        return hash_equals($_SESSION['ua_hash'], self::userAgentHash());
    }

    private static function userAgentHash(): string
    {
        return hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
    }

    private static function regenerateIdPeriodically(): void
    {
        $lastRegenerate = $_SESSION['last_regenerate'] ?? time();

        if ((time() - $lastRegenerate) > self::REGENERATE_INTERVAL) {
            session_regenerate_id(true);
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
