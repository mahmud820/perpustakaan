<footer class="footer">
    <div class="container">
        <div class="row g-4">

            <!-- Logo -->
            <div class="col-md-4">
                <h4 class="footer-title">📚 Perpustakaan Digital</h4>
                <p>
                    Menyediakan berbagai koleksi buku untuk menambah wawasan
                    dan pengetahuan.
                </p>
            </div>

            <!-- Menu -->
            <div class="col-md-4">
                <h5 class="footer-subtitle">Menu</h5>
                <ul class="footer-links">
                    <li><a href="<?= BASEURL; ?>">Beranda</a></li>
                    <li><a href="<?= BASEURL; ?>/daftarBuku">Daftar Buku</a></li>
                    <li><a href="<?= BASEURL; ?>/kontak">Kontak</a></li>
                    <li><a href="<?= BASEURL; ?>/about">About</a></li>
                </ul>
            </div>

            <!-- Kontak -->
            <div class="col-md-4">
                <h5 class="footer-subtitle">Kontak</h5>

                <p>
                    <i class="bi bi-envelope-fill"></i>
                    admin@perpustakaan.com
                </p>

                <p>
                    <i class="bi bi-telephone-fill"></i>
                    +62 812 3456 7890
                </p>

                <div class="social-icons">
                    <a href="#"><i class="bi bi-facebook"></i></a>
                    <a href="#"><i class="bi bi-instagram"></i></a>
                    <a href="#"><i class="bi bi-github"></i></a>
                    <a href="#"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

        </div>

        <hr>

        <div class="footer-bottom">
            © 2026 Perpustakaan Digital. All Rights Reserved.
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
  const BASEURL = '<?= BASEURL; ?>';
</script>

<!-- SweetAlert harus sebelum buku.js -->
<!-- <script src="<?= BASEURL; ?>/sweetalert/sweetalert2.all.min.js"></script> -->

<script src="<?= BASEURL; ?>/js/buatanSendiri/buku.js"></script>

</body>

</html>