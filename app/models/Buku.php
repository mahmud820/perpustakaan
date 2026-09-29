<?php

class Buku
{
    private $db;

    // Path absolut (tidak tergantung current working directory server)
    private $folderCover;
    private $folderFile;

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
    }

    // ... seluruh method lain tetap sama ...

    // =========================
    // HELPER: UPLOAD COVER
    // Return: string nama file baru, '' jika tidak ada file dikirim,
    //         atau array ['error' => pesan] jika gagal validasi/upload.
    // =========================
    public function uploadCover($files)
    {
        return $this->uploadFile(
            $files['cover'] ?? null,
            [
                'directory' => $this->folderCover,
                'fieldName' => 'cover',
                'maxSize' => 2 * 1024 * 1024,
                'allowedMimeTypes' => [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                ],
                'errorMessage' => 'Format cover harus JPG, PNG, atau WEBP',
                'sizeMessage' => 'Ukuran cover maksimal 2 MB',
                'contentMessage' => 'File cover rusak atau bukan gambar yang valid',
                'customValidator' => function ($tmpName) {
                    return @getimagesize($tmpName) !== false;
                },
            ]
        );
    }

    // =========================
    // HELPER: UPLOAD FILE BACA (PDF)
    // Return: string nama file baru, '' jika tidak ada file dikirim,
    //         atau array ['error' => pesan] jika gagal validasi/upload.
    // =========================
    public function uploadFileBaca($files)
    {
        return $this->uploadFile(
            $files['file_baca'] ?? null,
            [
                'directory' => $this->folderFile,
                'fieldName' => 'file_baca',
                'maxSize' => 20 * 1024 * 1024,
                'allowedMimeTypes' => [
                    'application/pdf' => 'pdf',
                ],
                'errorMessage' => 'File baca harus berformat PDF',
                'sizeMessage' => 'Ukuran file baca maksimal 20 MB',
                'contentMessage' => 'File baca rusak atau bukan PDF yang valid',
                'customValidator' => function ($tmpName) {
                    $handle = @fopen($tmpName, 'rb');
                    if (!$handle) {
                        return false;
                    }

                    $header = fread($handle, 5);
                    fclose($handle);

                    return $header === '%PDF-';
                },
            ]
        );
    }

    // =========================
    // HELPER: UPLOAD FILE (INTERNAL)
    // =========================
    private function uploadFile($file, array $config)
    {
        if ($file === null || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return '';
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Upload ' . $config['fieldName'] . ' gagal'];
        }

        if ($file['size'] > $config['maxSize']) {
            return ['error' => $config['sizeMessage']];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $finfo ? finfo_file($finfo, $file['tmp_name']) : null;
        if ($finfo) {
            finfo_close($finfo);
        }

        $allowedMimeTypes = $config['allowedMimeTypes'];
        $extension = $allowedMimeTypes[$mimeType] ?? null;

        if ($extension === null) {
            return ['error' => $config['errorMessage']];
        }

        if (isset($config['customValidator']) && !call_user_func($config['customValidator'], $file['tmp_name'])) {
            return ['error' => $config['contentMessage']];
        }

        $directory = $config['directory'];
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            return ['error' => 'Folder upload tidak ditemukan'];
        }

        $namaBaru = bin2hex(random_bytes(16)) . '.' . $extension;

        if (!move_uploaded_file($file['tmp_name'], $directory . $namaBaru)) {
            return ['error' => ucfirst($config['fieldName']) . ' gagal disimpan'];
        }

        return $namaBaru;
    }
}
