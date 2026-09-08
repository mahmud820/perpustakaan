# Catatan Update: Dashboard Admin + Peningkatan Keamanan

## 1. Fitur Dashboard Admin (baru)

File: `app/models/Buku.php`, `app/controllers/Admin.php`, `app/views/admin/index.php`

Dashboard Admin (`/admin`) sekarang menampilkan kartu statistik di bagian atas:

- **Total Buku**
- **Buku Sedang Dibaca**
- **Buku Selesai Dibaca**
- **Buku Belum Dibaca**
- **Buku Terbaru** (5 buku terbaru, ditampilkan sebagai grid cover + judul + status)

Ditambahkan method baru di model `Buku`:

- `countByStatus($status)`
- `getBukuTerbaru($limit)`
- `getDashboardStats($jumlahTerbaru)` — mengembalikan semua angka di atas sekaligus.

## 2. CSRF Protection (baru, sebelumnya TIDAK ADA)

File baru: `app/core/Csrf.php`

- Token CSRF dibuat sekali per sesi, disimpan di `$_SESSION['csrf_token']`.
- Dirender lewat `<meta name="csrf-token">` di `<head>` (lihat `templates/header.php`) supaya bisa dibaca JavaScript.
- Semua form POST juga punya hidden input `Csrf::field()` sebagai cadangan (login, tambah/edit buku, catatan pribadi).
- Semua request `fetch()` di `admin.js` dan `buku.js` sekarang mengirim header `X-CSRF-Token`.
- Semua controller action yang mengubah data (`Auth::login`, `DaftarBuku::tambah/update/hapus/updateStatus/updateProgress/updateCatatan`) memvalidasi token lewat `Csrf::guard()` / `Csrf::verify()` sebelum memproses apa pun. Token tidak valid -> HTTP 403.

## 3. Session Security (diperkuat)

File: `app/core/AuthMiddleware.php`, `public/index.php` (sudah ada sebagian sebelumnya)

Yang sudah ada sebelumnya (dipertahankan): cookie `HttpOnly`, `Secure` (otomatis saat HTTPS), `SameSite=Lax`, `session_regenerate_id()` saat login.

Ditambahkan:

- **Idle timeout** 30 menit — sesi otomatis dianggap habis jika tidak ada aktivitas.
- **Regenerasi session ID berkala** setiap 15 menit selama sesi aktif (mengurangi risiko session id lama yang bocor tetap valid lama).
- **Binding ke User-Agent** (hash) — sesi otomatis dianggap tidak valid jika User-Agent berubah drastis (indikasi sesi dicuri/dipakai ulang di device lain).
- `AuthMiddleware::markLoggedIn()` — titik tunggal untuk set session saat login sukses (dipakai `Auth::login`).
- `AuthMiddleware::destroySession()` — titik tunggal untuk hapus session (dipakai saat logout maupun saat idle timeout / UA mismatch terdeteksi).

## 4. Rate Limiting Login / Anti Brute Force (baru)

File baru: `app/core/LoginThrottle.php`

- Maksimal 5 percobaan gagal dalam 15 menit per kombinasi IP + username.
- Setelah limit tercapai, akun/IP tersebut dikunci 15 menit (pesan error menampilkan sisa waktu).
- Disimpan di `app/storage/login_attempts.json` (folder di luar `public/`, dilindungi tambahan `.htaccess` + `Require all denied`), jadi tidak perlu tabel database baru.
- Pesan error login disamakan ("Username atau password salah") baik saat username tidak ditemukan maupun password salah, supaya tidak bisa dipakai untuk menebak username yang valid (anti user enumeration).

## 5. Upload Validation (diperkuat)(yg ini selanjutnya)

File: `app/models/Buku.php`, `public/uploads/.htaccess` (baru), `public/img/dataGambar/.htaccess` (baru)

Yang sudah ada sebelumnya (dipertahankan): validasi ukuran file, validasi MIME type via `finfo`, nama file diacak (`random_bytes`), rollback file jika insert/update gagal.

Ditambahkan:

- Cover: validasi tambahan `getimagesize()` untuk memastikan file benar-benar bisa didekode sebagai gambar (bukan cuma file "polyglot" yang MIME-nya kebetulan cocok).
- File baca (PDF): validasi tambahan magic bytes `%PDF-` di 5 byte pertama file.
- `.htaccess` di folder `public/uploads/` dan `public/img/dataGambar/` untuk **mencegah eksekusi PHP** sama sekali di folder tersebut — lapisan pertahanan tambahan seandainya ada file berbahaya yang lolos validasi MIME/magic bytes.

## 6. Input Validation (diperkuat)

File: `app/controllers/DaftarBuku.php`

- Panjang `klasifikasi` dibatasi maksimal 100 karakter, `sinopsis` maksimal 5000 karakter (sebelumnya tidak ada batas).
- Validasi `link_baca` diperketat: wajib diawali `http://` atau `https://`, panjang maksimal 2048 karakter.
- Keyword & filter kategori pencarian (`DaftarBuku::index`) dibatasi maksimal 100 karakter.

## 7. XSS Protection

Sudah diaudit di seluruh view (`admin/index.php`, `daftarBuku/index.php`, `daftarBuku/detail.php`, `auth/login.php`, `templates/header.php`, `templates/footer.php`) — semua output data milik user sudah konsisten memakai `htmlspecialchars()` / `rawurlencode()` untuk URL. Tidak ditemukan celah XSS reflected/stored yang belum di-escape.

Tambahan: security header di `public/.htaccess`:

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `X-XSS-Protection: 1; mode=block`
- `Permissions-Policy` (nonaktifkan geolocation/microphone/camera secara default)

## 8. SQL Injection Protection

Sudah diaudit — seluruh query di `app/models/Buku.php`, `app/models/User.php`, dan `app/core/Database.php` sudah 100% memakai PDO prepared statements dengan named parameter (`:param`) dan `bindValue()`. Tidak ada satu pun query yang menggabungkan input user langsung ke string SQL. Tidak ada perubahan yang diperlukan di sisi ini, hanya dikonfirmasi aman.

## 9. Password Hashing & Verification

Sudah diaudit — `User::createUser()` memakai `password_hash($pass, PASSWORD_DEFAULT)`, `Auth::login()` memakai `password_verify()`. Tidak ada perubahan yang diperlukan, hanya dikonfirmasi aman. (Rate limiting di poin 4 melengkapi ini sebagai lapisan anti brute-force.)

---

## File yang perlu diperhatikan saat deploy

- Pastikan folder `app/storage/` writable oleh web server (dipakai `LoginThrottle` untuk menyimpan percobaan login).
- Jika server bukan Apache/tidak mendukung `.htaccess` (mis. Nginx), aturan di `public/.htaccess`, `public/uploads/.htaccess`, dan `public/img/dataGambar/.htaccess` perlu diterjemahkan manual ke konfigurasi server yang sesuai (security header + larangan eksekusi PHP di folder upload).
- Technical debt yang SUDAH DICATAT sebelumnya (HTTP 500 vs 422 pada validasi upload di Model, dan bug offset pagination) **belum diperbaiki di update ini** — sesuai kesepakatan sebelumnya, itu akan dikerjakan terpisah saat proses hosting.
