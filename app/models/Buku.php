<?php

class Buku
{
    private $db;

    // Path absolut (tidak tergantung current working directory server)
    private $folderCover;
    private $folderFile;
    private FileUploader $fileUploader;

    // Daftar status baca yang valid (dipakai untuk validasi & dropdown)
    const STATUS_BACA = [
        'belum_dibaca'  => 'Belum Dibaca',
        'sedang_dibaca' => 'Sedang Dibaca',
        'selesai_dibaca' => 'Selesai Dibaca',
    ];

    public function __construct()
    {
        $this->db = new Database;

        // __DIR__ = .../app/models -> naik 2 folder ke root project, lalu ke public/...
        $this->folderCover = dirname(__DIR__, 2) . '/public/img/dataGambar/';
        $this->folderFile  = dirname(__DIR__, 2) . '/public/uploads/pdf/';
        $this->fileUploader = new FileUploader();
    }

    // ... seluruh method lain tetap sama ...

    // =========================
    // HELPER: UPLOAD COVER
    // Return: string nama file baru, '' jika tidak ada file dikirim,
    //         atau array ['error' => pesan] jika gagal validasi/upload.
    // =========================
    private function uploadCover($files)
    {
        return $this->fileUploader->uploadCover($files);
    }

    // =========================
    // HELPER: UPLOAD FILE BACA (PDF)
    // Return: string nama file baru, '' jika tidak ada file dikirim,
    //         atau array ['error' => pesan] jika gagal validasi/upload.
    // =========================
    private function uploadFileBaca($files)
    {
        return $this->fileUploader->uploadFileBaca($files);
    }
}
