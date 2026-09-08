# Authentication & Authorization

## 1. Authentication

Authentication merupakan proses untuk memastikan identitas pengguna sebelum pengguna dapat mengakses fitur yang membutuhkan login.

Pada aplikasi **Perpustakaan Digital**, Authentication digunakan untuk proses:

- Login Admin
- Validasi username dan password
- Pembuatan session setelah login
- Logout
- Penghancuran session setelah logout
- Perlindungan halaman yang membutuhkan login

### Alur Authentication

```text
Admin memasukkan username & password
                ↓
        Validasi input
                ↓
      Cari username di database
                ↓
       Username ditemukan?
          ↓            ↓
        Tidak          Ya
          ↓             ↓
     Login gagal   password_verify()
                         ↓
                  Password benar?
                   ↓           ↓
                 Tidak         Ya
                   ↓            ↓
              Login gagal    Session dibuat
                                  ↓
                           Masuk ke Admin
```

---

## 2. Login

Admin melakukan login menggunakan:

```text
Username
Password
```

Data username dicari dari tabel `users`.

Password tidak dibandingkan secara langsung dengan password yang tersimpan di database. Aplikasi menggunakan:

```php
password_verify($password, $user['password'])
```

untuk memverifikasi password.

### Contoh

Password asli:

```text
admin123
```

Password yang tersimpan di database:

```text
$2y$10$................................................
```

Ketika Admin login, sistem melakukan verifikasi menggunakan `password_verify()`.

---

## 3. Password Hashing

Password Admin disimpan dalam bentuk hash menggunakan:

```php
password_hash($password, PASSWORD_DEFAULT)
```

Tujuannya adalah agar password asli tidak disimpan secara langsung di database.

Contoh:

```php
$hash = password_hash($password, PASSWORD_DEFAULT);
```

Kemudian hash tersebut disimpan pada kolom `password` tabel `users`.

Untuk melakukan login:

```php
password_verify($password, $user['password']);
```

### Keuntungan

- Password asli tidak tersimpan di database.
- Lebih aman jika database terekspos.
- Menggunakan mekanisme hashing password bawaan PHP.

---

# 4. Session

Setelah login berhasil, aplikasi membuat session untuk menyimpan informasi Admin.

Contoh data session:

```php
$_SESSION['login'] = true;
$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['nama'] = $user['nama'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];
```

Session digunakan agar sistem dapat mengetahui bahwa pengguna sudah login.

### Alur Session

```text
Login berhasil
     ↓
Session dibuat
     ↓
Admin mengakses halaman
     ↓
Sistem membaca session
     ↓
Admin tetap dianggap login
```

---

# 5. Session Regeneration

Setelah login berhasil, aplikasi menggunakan:

```php
session_regenerate_id(true);
```

Tujuannya untuk mengganti ID session setelah proses login.

Contoh:

```php
if ($user && password_verify($password, $user['password'])) {

    session_regenerate_id(true);

    $_SESSION['login'] = true;
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
}
```

Hal ini membantu meningkatkan keamanan session setelah proses authentication.

---

# 6. Logout

Logout digunakan untuk mengakhiri session Admin.

Proses logout dilakukan dengan:

```php
$_SESSION = [];
session_destroy();
```

Setelah session dihancurkan, pengguna tidak lagi dianggap login.

Alurnya:

```text
Admin
  ↓
Logout
  ↓
Session dikosongkan
  ↓
session_destroy()
  ↓
Kembali ke halaman Login
```

Setelah logout, Admin tidak boleh lagi mengakses halaman yang membutuhkan login.

---

# 7. Authorization

Authorization merupakan proses untuk menentukan apakah pengguna memiliki hak untuk mengakses suatu fitur atau halaman.

Pada aplikasi ini terdapat dua kondisi pengguna:

```text
Guest
Admin
```

### Guest

Guest adalah pengguna yang belum login.

Guest dapat mengakses halaman publik seperti:

```text
Beranda
Daftar Buku
Detail Buku
Tentang
Kontak
```

Namun Guest tidak dapat mengakses halaman dan operasi khusus Admin.

### Admin

Admin adalah pengguna yang sudah berhasil melakukan login dan memiliki:

```text
role = admin
```

Admin dapat mengakses fitur yang membutuhkan hak Admin.

---

# 8. Perbedaan Authentication dan Authorization

### Authentication

Menjawab pertanyaan:

> Siapa pengguna ini?

Contoh:

```text
Username + Password
        ↓
     Login
        ↓
Identitas pengguna diketahui
```

### Authorization

Menjawab pertanyaan:

> Apa yang boleh dilakukan pengguna ini?

Contoh:

```text
Sudah login?
     ↓
   Ya
     ↓
role = admin?
     ↓
   Ya
     ↓
Boleh mengakses Admin
```

Secara sederhana:

```text
Authentication = Identitas
Authorization  = Hak Akses
```

---

# 9. AuthMiddleware

Aplikasi menggunakan `AuthMiddleware` untuk mengatur akses berdasarkan status login dan role.

Middleware menyediakan pengecekan seperti:

```php
AuthMiddleware::isLogin()
```

dan:

```php
AuthMiddleware::isAdmin()
```

Serta perlindungan halaman menggunakan:

```php
AuthMiddleware::requireLogin();
```

dan:

```php
AuthMiddleware::requireAdmin();
```

---

# 10. requireLogin()

`requireLogin()` digunakan untuk melindungi halaman yang hanya boleh diakses oleh pengguna yang sudah login.

Contoh alur:

```text
Guest
  ↓
Membuka halaman yang membutuhkan login
  ↓
requireLogin()
  ↓
Belum login
  ↓
Redirect ke halaman Login
```

Jika pengguna sudah login:

```text
Admin
  ↓
requireLogin()
  ↓
Session login tersedia
  ↓
Akses diperbolehkan
```

---

# 11. requireAdmin()

`requireAdmin()` digunakan untuk melindungi halaman atau fitur yang hanya boleh digunakan oleh Admin.

Konsepnya:

```text
Pengguna
    ↓
Sudah login?
   /   \
 Tidak   Ya
  ↓       ↓
Login   role = admin?
          /    \
        Tidak   Ya
          ↓      ↓
        403    Akses
```

Contoh penggunaan:

```php
AuthMiddleware::requireAdmin();
```

Dengan middleware tersebut, halaman Admin tidak dapat digunakan oleh Guest.

---

# 12. Authorization pada Dashboard Admin

Halaman Admin dilindungi menggunakan:

```php
AuthMiddleware::requireAdmin();
```

Sehingga:

```text
Guest
  ↓
/admin
  ↓
Tidak memiliki session login
  ↓
Redirect ke Login
```

Sedangkan:

```text
Admin
  ↓
/admin
  ↓
Session login tersedia
  ↓
role = admin
  ↓
Dashboard dapat diakses
```

---

# 13. Authorization pada CRUD Buku

Fitur CRUD Buku memiliki hak akses sebagai berikut:

| Fitur               | Guest | Admin |
| ------------------- | :---: | :---: |
| Melihat daftar buku |  ✅   |  ✅   |
| Melihat detail buku |  ✅   |  ✅   |
| Membaca buku        |  ✅   |  ✅   |
| Menambah buku       |  ❌   |  ✅   |
| Mengubah buku       |  ❌   |  ✅   |
| Menghapus buku      |  ❌   |  ✅   |

Guest tetap dapat menggunakan fitur yang bersifat publik, tetapi tidak dapat melakukan perubahan terhadap data buku.

---

# 14. Perlindungan Endpoint CRUD

Operasi CRUD yang membutuhkan hak Admin harus melakukan pengecekan Authorization pada Controller.

Contoh:

```php
public function tambah()
{
    AuthMiddleware::requireAdmin();

    // proses tambah buku
}
```

Update:

```php
public function update()
{
    AuthMiddleware::requireAdmin();

    // proses update buku
}
```

Delete:

```php
public function hapus()
{
    AuthMiddleware::requireAdmin();

    // proses hapus buku
}
```

Dengan demikian, keamanan tidak hanya bergantung pada tombol yang ditampilkan di halaman.

---

# 15. Pembatasan HTTP Method

Endpoint CRUD digunakan untuk menerima request tertentu.

Contohnya proses tambah buku menggunakan:

```text
POST /daftarBuku/tambah
```

Sehingga request selain POST dapat ditolak.

Contoh:

```php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('405 Method Not Allowed');
}
```

Dengan demikian:

```text
GET /daftarBuku/tambah
```

tidak digunakan untuk menjalankan proses tambah data.

Sedangkan:

```text
POST /daftarBuku/tambah
```

digunakan untuk mengirim data buku.

---

# 16. Status HTTP pada Authorization

Aplikasi membedakan kondisi pengguna.

### Belum Login

Jika Guest mencoba mengakses halaman yang membutuhkan login:

```text
Redirect → /auth/login
```

### Tidak Memiliki Hak Akses

Jika pengguna sudah terautentikasi tetapi tidak memiliki hak yang diperlukan:

```text
403 Forbidden
```

Contoh pesan:

```text
403 Forbidden - Anda tidak memiliki izin untuk mengakses halaman ini.
```

### Method Tidak Sesuai

Jika endpoint membutuhkan POST tetapi menerima GET:

```text
405 Method Not Allowed
```

Contoh:

```text
405 Method Not Allowed
```

---

# 17. Pengujian Authentication & Authorization

### Authentication

|  No | Pengujian                       | Expected Result |
| --: | ------------------------------- | --------------- |
|   1 | Username benar + password benar | Login berhasil  |
|   2 | Username benar + password salah | Login ditolak   |
|   3 | Username salah                  | Login ditolak   |
|   4 | Username kosong                 | Login ditolak   |
|   5 | Password kosong                 | Login ditolak   |
|   6 | Username dan password kosong    | Login ditolak   |

### Authorization

|  No | Pengujian                    | Expected Result   |
| --: | ---------------------------- | ----------------- |
|   1 | Guest membuka halaman publik | Berhasil          |
|   2 | Guest membuka `/admin`       | Redirect ke Login |
|   3 | Admin membuka `/admin`       | Berhasil          |
|   4 | Guest mencoba tambah buku    | Ditolak           |
|   5 | Guest mencoba update buku    | Ditolak           |
|   6 | Guest mencoba hapus buku     | Ditolak           |
|   7 | Admin menambah buku          | Berhasil          |
|   8 | Admin mengubah buku          | Berhasil          |
|   9 | Admin menghapus buku         | Berhasil          |

### Session

|  No | Pengujian                       | Expected Result     |
| --: | ------------------------------- | ------------------- |
|   1 | Login berhasil                  | Session dibuat      |
|   2 | Pindah halaman                  | Session tetap aktif |
|   3 | Refresh halaman                 | Tetap login         |
|   4 | Logout                          | Session dihancurkan |
|   5 | Setelah logout membuka `/admin` | Redirect ke Login   |
|   6 | Setelah logout mencoba CRUD     | Ditolak             |

---

# 18. Kesimpulan

Authentication dan Authorization pada aplikasi **Perpustakaan Digital** digunakan untuk membatasi akses berdasarkan status login dan hak akses Admin.

Authentication memastikan pengguna melakukan login menggunakan username dan password yang valid. Password disimpan menggunakan hashing dan diverifikasi menggunakan `password_verify()`.

Session digunakan untuk mempertahankan status login selama pengguna menggunakan aplikasi. Setelah login berhasil, session ID diregenerasi untuk meningkatkan keamanan session.

Authorization digunakan untuk membedakan akses antara Guest dan Admin. Guest dapat mengakses fitur publik, sedangkan fitur administrasi seperti Dashboard dan CRUD Buku hanya dapat digunakan oleh Admin.

Dengan kombinasi:

```text
Authentication
      +
Session
      +
Authorization
      +
AuthMiddleware
      +
Password Hashing
      +
HTTP Method Validation
```

aplikasi memiliki dasar keamanan yang lebih baik dalam mengontrol akses pengguna dan melindungi fitur administrasi.
