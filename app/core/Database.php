<?php

class Database
{
    private string $host = DB_HOST;
    private string $user = DB_USER;
    private string $pass = DB_PASS;
    private string $db_name = DB_NAME;

    private PDO $dbh;
    private PDOStatement $stmt;

    public function __construct()
    {
        // data source name
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->db_name;

        $option = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $option);
        } catch (PDOException $e) {
            // Pesan asli PDO memuat host, nama database, dan username -> jangan tampilkan ke pengunjung.
            // Selalu dicatat ke error log (XAMPP: apache/logs/error.log).
            error_log('[Database connect] ' . $e->getMessage());

            http_response_code(500);

            // Saat development, tambahkan define('APP_DEBUG', true); di config.php
            // untuk melihat pesan aslinya langsung di browser.
            if (defined('APP_DEBUG') && APP_DEBUG) {
                die('Koneksi database gagal: ' . htmlspecialchars($e->getMessage()));
            }

            die('Layanan sedang tidak tersedia. Silakan coba beberapa saat lagi.');
        }
    }

    public function query(string $query): void
    {
        $this->stmt = $this->dbh->prepare($query);
    }

    public function bind($param, $value, $type = null)
    {
        // normalisasi nama parameter: tambahkan ':' jika tidak ada
        if (is_string($param) && strpos($param, ':') !== 0) {
            $param = ':' . $param;
        }

        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;

                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;

                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;

                default:
                    $type = PDO::PARAM_STR;
            }
        }

        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute(): bool
    {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {

            // log agar mudah didiagnosa tanpa menampilkan ke user
            error_log('[Database execute] ' . $e->getMessage());

            return false;
        }
    }

    // Kalau query gagal, execute() sudah mencatat errornya ke error log dan mengembalikan false.
    // Jangan lanjut fetch: statement yang gagal dijalankan akan melempar PDOException lain
    // (tidak tertangkap -> halaman error 500 yang bisa menampilkan detail SQL/path).
    public function resultSet()
    {
        if (!$this->execute()) {
            return [];
        }

        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Return: array satu baris, atau false kalau tidak ada baris ATAU query gagal.
    public function single()
    {
        if (!$this->execute()) {
            return false;
        }

        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function rowCount(): int
    {
        return $this->stmt->rowCount();
    }
}