<?php
if (!session_id()) {
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

require_once '../app/init.php';

$app = new App;