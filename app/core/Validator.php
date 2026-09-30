<?php

class Validator
{
    public static function requiredId($id): ?string
    {
        if ($id === '' || !ctype_digit((string) $id)) {
            return 'ID buku tidak valid';
        }

        return null;
    }

    // Validasi semua field form buku sekaligus (dipakai tambah & update).
    // Mengembalikan pesan error pertama, atau null kalau semua valid.
    public static function buku(array $input): ?string
    {
        $errors = [
            self::judul($input['judul'] ?? ''),
            self::penulis($input['penulis'] ?? ''),
            self::klasifikasi($input['klasifikasi'] ?? ''),
            self::sinopsis($input['sinopsis'] ?? ''),
            self::linkBaca($input['link_baca'] ?? ''),
        ];

        foreach ($errors as $error) {
            if ($error !== null) {
                return $error;
            }
        }

        return null;
    }

    public static function judul($judul): ?string
    {
        $judul = trim((string) $judul);

        if ($judul === '') {
            return 'Judul buku wajib diisi';
        }

        if (mb_strlen($judul) > 255) {
            return 'Judul buku terlalu panjang';
        }

        return null;
    }

    public static function penulis($penulis): ?string
    {
        $penulis = trim((string) $penulis);

        if ($penulis === '') {
            return 'Penulis wajib diisi';
        }

        if (mb_strlen($penulis) > 255) {
            return 'Nama penulis terlalu panjang';
        }

        return null;
    }

    public static function klasifikasi($klasifikasi): ?string
    {
        $klasifikasi = trim((string) $klasifikasi);

        if (mb_strlen($klasifikasi) > 100) {
            return 'Kategori terlalu panjang (maksimal 100 karakter)';
        }

        return null;
    }

    public static function sinopsis($sinopsis): ?string
    {
        $sinopsis = trim((string) $sinopsis);

        if (mb_strlen($sinopsis) > 5000) {
            return 'Sinopsis terlalu panjang (maksimal 5000 karakter)';
        }

        return null;
    }

    public static function linkBaca($linkBaca): ?string
    {
        $linkBaca = trim((string) $linkBaca);

        if ($linkBaca === '') {
            return null;
        }

        if (mb_strlen($linkBaca) > 2048) {
            return 'Link baca terlalu panjang';
        }

        if (!filter_var($linkBaca, FILTER_VALIDATE_URL) || !preg_match('/^https?:\/\//i', $linkBaca)) {
            return 'Link baca tidak valid (harus diawali http:// atau https://)';
        }

        return null;
    }

    public static function catatanPribadi($catatan): ?string
    {
        $catatan = trim((string) $catatan);

        if (mb_strlen($catatan) > 2000) {
            return 'Catatan maksimal 2000 karakter';
        }

        return null;
    }

    public static function statusBaca($status): bool
    {
        return array_key_exists($status, Buku::STATUS_BACA);
    }

    public static function progressBuku($halamanDibaca, $totalHalaman): ?string
    {
        $halamanDibaca = trim((string) $halamanDibaca);
        $totalHalaman = trim((string) $totalHalaman);   // null / '' = total tidak diisi

        if (!ctype_digit($halamanDibaca)) {
            return 'Halaman dibaca tidak valid';
        }

        if ($totalHalaman !== '' && !ctype_digit($totalHalaman)) {
            return 'Total halaman tidak valid';
        }

        if ($totalHalaman !== '' && (int) $halamanDibaca > (int) $totalHalaman) {
            return 'Halaman dibaca tidak boleh melebihi total halaman';
        }

        return null;
    }

    public static function nama($nama): ?string
    {
        $nama = trim((string) $nama);

        if ($nama === '') {
            return 'Nama wajib diisi';
        }

        if (mb_strlen($nama) > 100) {
            return 'Nama terlalu panjang (maksimal 100 karakter)';
        }

        return null;
    }

    public static function email($email): ?string
    {
        $email = trim((string) $email);

        if ($email === '') {
            return 'Email wajib diisi';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Format email tidak valid';
        }

        if (mb_strlen($email) > 255) {
            return 'Email terlalu panjang';
        }

        return null;
    }

    public static function noTelp($noTelp): ?string
    {
        $noTelp = trim((string) $noTelp);

        if ($noTelp === '') {
            return null;
        }

        if (mb_strlen($noTelp) > 30) {
            return 'Nomor telepon terlalu panjang';
        }

        if (!preg_match('/^[0-9+\-\s()]+$/', $noTelp)) {
            return 'Nomor telepon hanya boleh berisi angka, +, -, spasi, atau tanda kurung';
        }

        return null;
    }

    public static function tagline($tagline): ?string
    {
        $tagline = trim((string) $tagline);

        if (mb_strlen($tagline) > 150) {
            return 'Tagline terlalu panjang (maksimal 150 karakter)';
        }

        return null;
    }

    public static function tentang($tentang): ?string
    {
        $tentang = trim((string) $tentang);

        if (mb_strlen($tentang) > 2000) {
            return 'Tentang saya terlalu panjang (maksimal 2000 karakter)';
        }

        return null;
    }

    public static function username($username): ?string
    {
        $username = trim((string) $username);

        if ($username === '') {
            return 'Username wajib diisi';
        }

        if (mb_strlen($username) > 50) {
            return 'Username maksimal 50 karakter';
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            return 'Username hanya boleh berisi huruf, angka, underscore, atau dash';
        }

        return null;
    }

    public static function password($password): ?string
    {
        $password = (string) $password;

        if ($password === '') {
            return 'Password wajib diisi';
        }

        if (mb_strlen($password) < 6) {
            return 'Password minimal 6 karakter';
        }

        return null;
    }
}
