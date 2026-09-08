# Ringkasan Perubahan — Status Baca, Progress, Catatan, Kategori, Pagination

Konsep yang sudah ada (MVC custom, PDO, pola AJAX + SweetAlert2, Bootstrap)
dipertahankan sepenuhnya. Tidak ada library/dependency baru yang ditambahkan.

## 1. Database (WAJIB dijalankan dulu)

Jalankan `database/migration_fitur_baca.sql` di database `perpustakaan` kamu
(lewat phpMyAdmin, atau `mysql -u root perpustakaan < database/migration_fitur_baca.sql`).

Ini menambah 4 kolom baru ke tabel `buku`:
- `status_baca` ENUM('belum_dibaca','sedang_dibaca','selesai_dibaca') default `belum_dibaca`
- `halaman_dibaca` INT default 0
- `total_halaman` INT NULL
- `catatan_pribadi` TEXT NULL

Kolom `klasifikasi` **tidak diubah/rename** — hanya label di tampilan yang
diganti jadi "Kategori" (sesuai konfirmasi kamu), supaya tidak perlu migrasi
data yang berisiko.

## 2. Status Baca & Progress Membaca

- Bersifat **global per buku**, ditandai oleh Admin (bukan per akun pembaca —
  sesuai konfirmasi kamu).
- Guest & Admin sama-sama bisa **melihat** badge status baca + progress bar
  di Daftar Buku dan Detail Buku.
- Hanya Admin yang bisa **mengubah** status/progress, lewat halaman
  `/admin` (dashboard baru).
- Progress disimpan sebagai `halaman_dibaca` / `total_halaman` (bukan
  persentase langsung), lalu persentase dihitung otomatis di tampilan.
- Status baca ikut menyesuaikan otomatis saat progress diupdate:
  0 halaman → Belum Dibaca, halaman < total → Sedang Dibaca,
  halaman = total → Selesai Dibaca. Admin tetap bisa override manual lewat
  dropdown status.

## 3. Catatan Pribadi

- Hanya untuk Admin. Disimpan per buku, **tidak pernah** ditampilkan ke
  Guest/publik — hanya muncul di dashboard `/admin`.

## 4. Kategori

- Memakai kolom `klasifikasi` yang sudah ada (tidak ada tabel baru).
- Semua label di UI yang sebelumnya "Klasifikasi" diganti jadi "Kategori"
  (form pencarian, dropdown filter, modal tambah buku).

## 5. Pagination

- Diterapkan di **Daftar Buku** (`/daftarBuku`, 12 buku/halaman) dan
  **Admin** (`/admin`, 10 baris/halaman).
- Filter pencarian & kategori tetap terjaga saat pindah halaman.

## File yang diubah/ditambah

**Model**
- `app/models/Buku.php` — tambah `STATUS_BACA` const, pagination
  (`getAllBuku`, `cariBuku` + method count-nya), dan
  `updateStatusBaca()`, `updateProgress()`, `updateCatatanPribadi()`.

**Controller**
- `app/controllers/DaftarBuku.php` — pagination di `index()`, endpoint baru
  `updateStatus()`, `updateProgress()`, `updateCatatan()` (semua Admin-only,
  POST-only, mengikuti pola validasi & response yang sudah ada).
- `app/controllers/Admin.php` — dashboard sekarang menampilkan tabel buku
  dengan pagination untuk mengelola status/progress/catatan.

**View**
- `app/views/daftarBuku/index.php` — badge status baca + progress bar per
  kartu, label "Kategori", navigasi pagination.
- `app/views/daftarBuku/detail.php` — badge status baca + progress bar.
- `app/views/admin/index.php` — tabel kelola buku (status/progress/catatan)
  + modal catatan pribadi.

**Aset baru**
- `public/js/buatanSendiri/admin.js` — AJAX untuk status/progress/catatan
  (pola sama seperti `buku.js`: fetch + FormData + SweetAlert2).
- `public/css/partials/admin.css` — styling ringan tabel admin.
- `database/migration_fitur_baca.sql` — migrasi kolom baru.

**Lainnya**
- `app/views/templates/footer.php` — tambah `<script>` untuk `admin.js`.
- `public/css/style.css` — tambah `@import` untuk `admin.css`.

## Yang perlu kamu cek setelah menjalankan migrasi

1. Buka `/daftarBuku` sebagai Guest — pastikan badge status & progress bar
   muncul, catatan pribadi TIDAK muncul di mana pun untuk Guest.
2. Login sebagai Admin → buka `/admin` — ubah status dropdown (harus
   langsung tersimpan tanpa reload), update progress (Simpan → reload),
   isi catatan pribadi lewat modal.
3. Coba pagination di `/daftarBuku` dan `/admin` dengan >12 / >10 buku.
4. Coba search + filter kategori lalu pindah halaman — filter harus tetap
   terjaga di URL.
