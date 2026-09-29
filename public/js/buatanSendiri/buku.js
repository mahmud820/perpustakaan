// Semua elemen di bawah ini HANYA ada di halaman Daftar Buku.
// File ini dimuat di semua halaman (lewat footer), jadi setiap blok
// dibungkus pengecekan null agar tidak error di halaman lain.

// =========================
// CSRF helper
// Token diambil dari <meta name="csrf-token"> yang dirender server (lihat header.php)
// dan dikirim lewat header X-CSRF-Token di setiap request state-changing (POST).
// =========================
function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute("content") : "";
}

function csrfHeaders(extra = {}) {
  return Object.assign({ "X-CSRF-Token": getCsrfToken() }, extra);
}

const addBookForm = document.getElementById("addBookForm");
const addBookModal = document.getElementById("addBookModal");

if (addBookForm && addBookModal) {
  // Fitur Tambah & Update Buku
  addBookForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
      const id = formData.get("id");

      const url = id
        ? BASEURL + "/daftarBuku/update"
        : BASEURL + "/daftarBuku/tambah";

      const response = await fetch(url, {
        method: "POST",
        headers: csrfHeaders(),
        body: formData,
      });

      const result = await response.text();

      if (response.ok && result.trim() === "success") {
        const modal = bootstrap.Modal.getInstance(addBookModal);

        modal.hide();

        Swal.fire({
          icon: "success",
          title: "Berhasil",
          text: id ? "Buku berhasil diupdate" : "Buku berhasil ditambahkan",
          timer: 1500,
          showConfirmButton: false,
        }).then(() => {
          location.reload();
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Gagal",
          text: result.trim() || "Terjadi kesalahan",
        });
      }
    } catch (error) {
      console.error(error);

      Swal.fire({
        icon: "error",
        title: "Oops...",
        text: "Terjadi kesalahan pada server",
      });
    }
  });

  // Reset Modal Saat Ditutup
  addBookModal.addEventListener("hidden.bs.modal", function () {
    addBookForm.reset();

    document.getElementById("id").value = "";
    document.getElementById("cover_lama").value = "";
    document.getElementById("file_baca_lama").value = "";

    const fileBacaInfo = document.getElementById("fileBacaInfo");
    if (fileBacaInfo) fileBacaInfo.textContent = "";

    const hapusWrapper = document.getElementById("hapusFileBacaWrapper");
    if (hapusWrapper) hapusWrapper.classList.add("d-none");

    const hapusCheckbox = document.getElementById("hapus_file_baca");
    if (hapusCheckbox) hapusCheckbox.checked = false;

    document.querySelector(".modal-title").textContent = "Tambah Buku";

    document.querySelector('#addBookForm button[type="submit"]').textContent =
      "Simpan";
  });

  // Fitur Edit Buku
  document.querySelectorAll(".btn-edit").forEach((button) => {
    button.addEventListener("click", function () {
      document.getElementById("id").value = this.dataset.id;

      document.getElementById("cover_lama").value = this.dataset.cover;
      document.getElementById("file_baca_lama").value =
        this.dataset.fileBaca || "";

      document.querySelector('[name="judul"]').value = this.dataset.judul;

      document.querySelector('[name="penulis"]').value = this.dataset.penulis;

      document.querySelector('[name="klasifikasi"]').value =
        this.dataset.klasifikasi;

      document.querySelector('[name="sinopsis"]').value = this.dataset.sinopsis;

      document.querySelector('[name="link_baca"]').value =
        this.dataset.linkBaca;

      // File input tidak bisa diisi otomatis oleh browser (alasan keamanan),
      // jadi cukup tampilkan info file yang sudah ada saat ini.
      const fileBacaInfo = document.getElementById("fileBacaInfo");
      const hapusWrapper = document.getElementById("hapusFileBacaWrapper");

      if (this.dataset.fileBaca) {
        if (fileBacaInfo) {
          fileBacaInfo.textContent = "File saat ini: " + this.dataset.fileBaca;
        }
        if (hapusWrapper) hapusWrapper.classList.remove("d-none");
      } else {
        if (fileBacaInfo) fileBacaInfo.textContent = "";
        if (hapusWrapper) hapusWrapper.classList.add("d-none");
      }

      document.querySelector(".modal-title").textContent = "Edit Buku";

      document.querySelector('#addBookForm button[type="submit"]').textContent =
        "Update";

      const modal = new bootstrap.Modal(addBookModal);

      modal.show();
    });
  });
}

// Fitur Hapus
document.querySelectorAll(".btn-delete").forEach((button) => {
  button.addEventListener("click", async function () {
    const id = this.dataset.id;

    const result = await Swal.fire({
      title: "Yakin?",
      text: "Data buku akan dihapus",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Ya, Hapus",
      cancelButtonText: "Batal",
    });

    if (!result.isConfirmed) return;

    try {
      const formData = new FormData();

      formData.append("id", id);

      const response = await fetch(BASEURL + "/daftarBuku/hapus", {
        method: "POST",
        headers: csrfHeaders(),
        body: formData,
      });

      const hasil = await response.text();

      if (response.ok) {
        Swal.fire({
          icon: "success",
          title: "Berhasil",
          text: hasil.trim() || "Buku berhasil dihapus",
          timer: 1500,
          showConfirmButton: false,
        }).then(() => {
          location.reload();
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Gagal",
          text: hasil.trim() || "Buku gagal dihapus",
        });
      }
    } catch (error) {
      console.error(error);

      Swal.fire({
        icon: "error",
        title: "Oops...",
        text: "Terjadi kesalahan pada server",
      });
    }
  });
});
