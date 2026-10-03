<?php
// Mode ketat: PHP menolak ID sesi yang tidak pernah dibuat server (mencegah session fixation).
ini_set('session.use_strict_mode', '1');

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps, // otomatis true kalau diakses via HTTPS
        'httponly' => true,     // cookie sesi tidak bisa dibaca lewat JavaScript (mitigasi XSS)
        'samesite' => 'Lax',    // mitigasi CSRF dasar
    ]);

    session_start();
}

require_once __DIR__ . '/../app/init.php';

$app = new App();
