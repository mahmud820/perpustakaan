<footer class="footer">
    <div class="container">
        <!-- Menggunakan row dengan 2 kolom (col-md-6 masing-masing) -->
        <div class="row g-4 align-items-center justify-content-between">

            <!-- Kolom 1: Logo & Deskripsi Singkat -->
            <div class="col-md-6 col-lg-5">
                <h4 class="footer-title text-center text-md-start">📚 Perpustakaan Digital</h4>
                <p class="footer-text text-center text-md-start">
                    Menyediakan berbagai koleksi buku pribadi saya untuk menambah wawasan
                    dan pengetahuan dalam dunia literasi.
                </p>
            </div>

            <!-- Kolom 2: Kontak & Sosial Media -->
            <div class="col-md-6 col-lg-5 text-center text-md-end">
                <h5 class="footer-subtitle">Hubungi Kami</h5>

                <p class="contact-item justify-content-center justify-content-md-end">
                    <i class="bi bi-envelope-fill me-2"></i>
                    <span>waryonomahmud812@gmail.com</span>
                </p>

                <p class="contact-item justify-content-center justify-content-md-end">
                    <i class="bi bi-telephone-fill me-2"></i>
                    <span>+62 812 3456 7890</span>
                </p>

                <div class="social-icons justify-content-center justify-content-md-end">
                    <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" aria-label="Github"><i class="bi bi-github"></i></a>
                    <a href="#" aria-label="Youtube"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

        </div>

        <!-- Garis Pemisah (Divider) Gradasi Emas -->
        <div class="footer-divider"></div>

        <!-- Copyright -->
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

<script src="<?= BASEURL; ?>/js/buatanSendiri/buku.js"></script>
<script src="<?= BASEURL; ?>/js/buatanSendiri/admin.js"></script>

</body>

</html>