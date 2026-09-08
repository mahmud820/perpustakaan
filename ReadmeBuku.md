# Perpustakaan Digital

Aplikasi **Perpustakaan Digital** berbasis PHP Native dengan konsep
**MVC (Model-View-Controller)** dan database MySQL.

## 1. Teknologi

-   PHP Native
-   MySQL
-   PDO
-   HTML, CSS, JavaScript
-   Bootstrap
-   SweetAlert2
-   XAMPP
-   Postman
-   MVC

## 2. Fitur

-   CRUD buku
-   Search buku
-   Filter klasifikasi
-   Detail buku
-   Upload cover
-   Link/file baca
-   Authentication
-   Authorization Admin
-   Session
-   Password hashing
-   Validasi input dan upload

## 3. Perbaikan CREATE / Tambah Buku

Validasi yang ditambahkan:

-   Judul wajib diisi
-   Penulis wajib diisi
-   Panjang judul dan penulis dibatasi
-   Link baca divalidasi jika diisi
-   Cover maksimal 2 MB
-   Cover hanya JPG/JPEG, PNG, dan WEBP
-   Nama file cover dibuat secara acak
-   Jika database gagal setelah upload, cover baru dihapus kembali

## 4. Perbaikan UPDATE / Edit Buku

UPDATE diperbaiki dengan:

-   Hanya Admin yang dapat melakukan update
-   Endpoint update hanya menerima POST
-   ID buku divalidasi
-   Judul dan penulis wajib diisi
-   Link baca divalidasi
-   Cover baru divalidasi
-   Cover lama tidak dihapus jika tidak ada cover baru
-   Cover lama baru dihapus setelah cover baru dan database berhasil
    diperbarui
-   Jika database gagal, cover baru yang sudah terupload akan dihapus
-   Keberhasilan update ditentukan dari `execute()`, bukan hanya
    `rowCount()`

Alur penggantian cover:

``` text
Upload cover baru
        ↓
Validasi cover
        ↓
Simpan cover baru
        ↓
Update database
        ↓
Database berhasil?
   ├── Tidak → hapus cover baru
   └── Ya → hapus cover lama
```

## 5. Pengujian dengan Postman

Postman digunakan untuk menguji endpoint CRUD dan response HTTP.

Kesalahan yang ditemukan:

``` text
POST /daftarBuku?update=9999
```

URL tersebut tidak memanggil method `update()` dengan benar.

Endpoint yang digunakan:

``` text
POST /daftarBuku/update
```

ID dikirim melalui Body, misalnya:

``` text
id = 9999
```

### Pengujian ID Tidak Valid

Contoh:

``` text
id = abc
```

Hasil yang diharapkan:

``` text
HTTP 400
ID buku tidak valid
```

### Pengujian Buku Tidak Ditemukan

Contoh:

``` text
id = 999999
```

Jika ID tidak terdapat di database:

``` text
HTTP 404
Buku tidak ditemukan
```

Perbedaan:

``` text
ID tidak valid
    ↓
400 Bad Request

ID valid tetapi buku tidak ada
    ↓
404 Not Found
```

## 6. Status Pengerjaan

  Fitur               Status
  ------------------- -----------------------
  CREATE              🔄 Diperbaiki & diuji
  READ                ✅ Tersedia
  UPDATE              🔄 Diperbaiki & diuji
  DELETE              ⏳ Selanjutnya
  Search              ⏳ Selanjutnya
  Filter              ⏳ Selanjutnya
  Detail              ⏳ Selanjutnya
  Cover               🔄 Sedang diperbaiki
  Link/File Baca      🔄 Sedang diperbaiki
  Authentication      ✅ Tersedia
  Authorization       ✅ Admin
  Pengujian Postman   🔄 Berjalan

## 7. Urutan Pengerjaan

``` text
CRUD
 ↓
CREATE
 ↓
READ
 ↓
UPDATE
 ↓
DELETE
 ↓
Pengujian CRUD
 ↓
Search
 ↓
Filter
 ↓
Detail
 ↓
Cover
 ↓
Link/File Baca
 ↓
Pengujian akhir
```

## 8. Target Pengujian

Setiap fitur diuji dalam kondisi normal dan kondisi error.

``` text
Data benar
    → berhasil

Data kosong
    → ditolak

ID tidak valid
    → 400

ID valid tetapi tidak ditemukan
    → 404

Bukan Admin
    → 403

Method HTTP salah
    → 405

File cover tidak sesuai
    → ditolak

File terlalu besar
    → ditolak
```

Tujuannya adalah memastikan aplikasi tidak hanya berjalan pada kondisi
normal, tetapi juga dapat menangani request yang salah dengan response
yang sesuai.
