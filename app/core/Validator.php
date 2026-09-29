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
        if (!ctype_digit((string) $halamanDibaca)) {
            return 'Halaman dibaca tidak valid';
        }

        $totalHalaman = ($totalHalaman === '' || $totalHalaman === null) ? null : (int) $totalHalaman;

        if ($totalHalaman !== null && !ctype_digit((string) $totalHalaman)) {
            return 'Total halaman tidak valid';
        }

        if ($totalHalaman !== null && (int) $halamanDibaca > $totalHalaman) {
            return 'Halaman dibaca tidak boleh melebihi total halaman';
        }

        return null;
    }
}
