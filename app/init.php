<?php

// Hanya config yang dimuat manual (berisi konstanta yang dipakai class lain).
require_once __DIR__ . '/config/index.php';

// Semua class lain (core, controllers, models) dimuat otomatis saat pertama kali dipakai.
// Nama class harus sama persis dengan nama file (contoh: class Buku -> models/Buku.php).
spl_autoload_register(function ($class) {
    foreach (['controllers', 'models', 'core'] as $folder) {
        $file = __DIR__ . '/' . $folder . '/' . $class . '.php';

        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});
