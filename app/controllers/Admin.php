<?php

class Admin extends Controller
{
    // Jumlah buku per halaman untuk pagination di dashboard Admin
    const PER_PAGE = 10;

    public function index()
    {
        AuthMiddleware::requireAdmin();

        $data['judul'] = 'Admin';

        $buku = $this->model('Buku');
        $total = $buku->countAllBuku();

        $pagination = new Pagination((int) ($_GET['page'] ?? 1), self::PER_PAGE, $total);
        $page = $pagination->getPage();
        $offset = $pagination->getOffset();

        $data['buku'] = $buku->getAllBuku(self::PER_PAGE, $offset);
        $data['currentPage'] = $page;
        $data['totalPages'] = $pagination->getTotalPages();
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
