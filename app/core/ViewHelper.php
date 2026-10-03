<?php

/**
 * Helper kecil untuk view. Tujuannya satu: logika yang tadinya ditulis ulang
 * di daftarBuku/index.php, daftarBuku/detail.php dan admin/index.php
 * (badge status, URL cover, URL baca, persen progress) cukup ditulis di satu tempat.
 */
class ViewHelper
{
    const STATUS_BADGE = [
        'belum_dibaca'   => 'bg-secondary',
        'sedang_dibaca'  => 'bg-warning text-dark',
        'selesai_dibaca' => 'bg-success',
    ];

    public static function statusBadgeClass($status): string
    {
        return self::STATUS_BADGE[$status ?? 'belum_dibaca'] ?? 'bg-secondary';
    }

    public static function statusLabel($status): string
    {
        return Buku::STATUS_BACA[$status ?? 'belum_dibaca'] ?? 'Belum Dibaca';
    }

    public static function coverUrl($cover): string
    {
        return !empty($cover)
            ? BASEURL . '/img/dataGambar/' . rawurlencode($cover)
            : BASEURL . '/img/no-image.png';
    }

    // Prioritas: file PDF yang diupload, lalu link eksternal. '' = belum tersedia.
    public static function bacaUrl(array $buku): string
    {
        if (!empty($buku['file_baca'])) {
            return BASEURL . '/uploads/pdf/' . rawurlencode($buku['file_baca']);
        }

        return !empty($buku['link_baca']) ? (string) $buku['link_baca'] : '';
    }

    public static function progressPercent($halamanDibaca, $totalHalaman): int
    {
        $total = (int) $totalHalaman;

        if ($total <= 0) {
            return 0;
        }

        return min(100, (int) round(((int) $halamanDibaca) / $total * 100));
    }
}
