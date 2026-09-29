<?php

class Admin extends Controller
{
    // Jumlah buku per halaman untuk pagination di dashboard Admin
    const PER_PAGE = 10;

    public function index()
    {
        AuthMiddleware::requireAdmin();

        $data['judul'] = 'Admin';

        // =========================
        // PAGINATION daftar buku untuk dikelola
        // (status baca, progress, catatan pribadi)
        // =========================
        $page = (int) ($_GET['page'] ?? 1);
        if ($page < 1) {
            $page = 1;
        }

        $buku = $this->model('Buku');

        $total = $buku->countAllBuku();
        $totalPages = (int) max(1, ceil($total / self::PER_PAGE));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * self::PER_PAGE;

        $data['buku'] = $buku->getAllBuku(self::PER_PAGE, $offset);
        $data['currentPage'] = $page;
        $data['totalPages'] = $totalPages;
        $data['statusBacaOptions'] = Buku::STATUS_BACA;

        // =========================
        // STATISTIK DASHBOARD
        // (Total buku, Sedang dibaca, Selesai, Belum dibaca, Buku terbaru)
        // =========================
        $data['stats'] = $buku->getDashboardStats(5);

        $this->view('templates/header', $data);
        $this->view('admin/index', $data);
        $this->view('templates/footer');
    }
}
