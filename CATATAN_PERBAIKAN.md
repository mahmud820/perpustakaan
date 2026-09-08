# Catatan Perbaikan & Peningkatan — Modul Perpustakaan

## 0. Langkah wajib sebelum dijalankan
Jalankan `database_migration.sql` di database `perpustakaan` kamu.
Ini menambahkan kolom `file_baca` (untuk fitur upload PDF) ke tabel `buku`.

```sql
ALTER TABLE `buku` ADD COLUMN `file_baca` VARCHAR(255) NULL DEFAULT NULL AFTER `link_baca`;
ALTER TABLE `buku` ADD INDEX `idx_buku_klasifikasi` (`klasifikasi`);
```

---

## 1. Bug & celah keamanan yang diperbaiki

| # | Masalah | Lokasi lama | Perbaikan |
|---|---------|-------------|-----------|
| 1 | **Keamanan**: nama file cover yang dihapus diambil dari input client (`$_POST['cover']`), bukan dari database — bisa dimanfaatkan untuk menghapus file lain di server dengan request yang dimanipulasi. | `DaftarBuku::hapus()` | Controller sekarang hanya mengirim `id`. Model `hapusBuku()` mengambil ulang nama file cover **dan** file baca langsung dari database sebelum menghapus. |
| 2 | **XSS**: judul, penulis, klasifikasi, sinopsis, dll ditampilkan tanpa `htmlspecialchars()` di halaman daftar & detail. | `daftarBuku/index.php`, `daftarBuku/detail.php` | Semua output dari database sekarang di-escape. Sinopsis pakai `nl2br(htmlspecialchars(...))` supaya baris baru tetap tampil tapi tag HTML tidak dieksekusi. |
| 3 | Halaman **Detail** buku yang tidak ditemukan menampilkan PHP warning/halaman rusak. | `DaftarBuku::detail()` | Sekarang mengecek `id` valid & data ditemukan, kalau tidak → halaman 404 khusus (`daftarBuku/notfound.php`) dengan status HTTP 404. |
| 4 | Tombol **"Baca Buku"** rusak (href kosong) kalau `link_baca` kosong. | `index.php`, `detail.php` | Kalau tidak ada link maupun file, tombol berubah jadi "Belum Tersedia" (disabled), tidak lagi link kosong. |
| 5 | `buku.js` dimuat di **semua halaman** (lewat footer) tapi langsung memanggil `.addEventListener` pada elemen modal yang hanya ada di halaman Daftar Buku → error di console pada halaman lain (Beranda, Detail, Login, dll). | `buku.js` | Semua blok kode terkait modal tambah/edit dibungkus pengecekan `if (addBookForm && addBookModal) { ... }`. |
| 6 | Path folder upload cover pakai path relatif (`../public/img/dataGambar/`) — rawan gagal tergantung *working directory* server. | `Buku.php` | Diganti path absolut memakai `dirname(__DIR__, 2)`. |
| 7 | Cover yang filenya sudah terhapus manual dari server masih mencoba ditampilkan (gambar rusak). | `index.php`, `detail.php` | Ditambahkan `onerror` di tag `<img>` untuk otomatis fallback ke `no-image.png`. |
| 8 | Nama file gambar/PDF dengan karakter spesial di URL bisa rusak. | — | Ditambahkan `rawurlencode()` saat membangun URL cover & file baca. |

## 2. Fitur baru

### Filter (klasifikasi/kategori)
- Dropdown "Semua Klasifikasi" di halaman Daftar Buku, datanya diambil otomatis dari nilai `klasifikasi` unik yang ada di database (`Buku::getAllKlasifikasi()`).
- Bisa dikombinasikan dengan pencarian kata kunci sekaligus (`Buku::cariBuku($keyword, $klasifikasi)`).
- Ada tombol reset (✕) saat filter/pencarian aktif.

### Link/File Baca (upload PDF)
- Admin sekarang bisa mengisi **Link Baca** (URL eksternal) **dan/atau** meng-upload **File Baca** berupa PDF.
- Prioritas tampilan: kalau ada file PDF yang diupload → tombol "Baca Buku" membuka file itu. Kalau tidak ada, baru pakai Link Baca.
- Validasi upload: hanya PDF asli (dicek via MIME type, bukan cuma ekstensi), maksimal 20 MB.
- File tersimpan di `public/uploads/pdf/` dengan nama acak (`bin2hex(random_bytes(16))`) supaya tidak bisa ditebak/dieksekusi sebagai path lain.
- Saat edit, ada info nama file yang sedang aktif + checkbox "Hapus file baca yang sudah ada" (jika ingin menghapus tanpa mengganti dengan file baru).
- Saat buku dihapus atau file diganti, file lama otomatis dihapus dari server (tidak jadi sampah).

## 3. File yang diubah
- `app/models/Buku.php`
- `app/controllers/DaftarBuku.php`
- `app/views/daftarBuku/index.php`
- `app/views/daftarBuku/detail.php`
- `app/views/daftarBuku/notfound.php` (baru)
- `public/js/buatanSendiri/buku.js`
- `database_migration.sql` (baru)

## 4. Yang belum tersentuh (di luar scope permintaan)
- Struktur Auth, Admin, Kontak, About tidak diubah.
- Belum ada pagination untuk daftar buku — kalau jumlah buku sudah banyak, ini bagus untuk ditambahkan berikutnya.
- Belum ada CSRF token pada form tambah/update/hapus (saat ini hanya diproteksi session admin) — bisa ditambahkan sebagai peningkatan keamanan lanjutan kalau dibutuhkan.
