// Semua elemen di bawah ini HANYA ada di halaman Dashboard Admin.
// File ini dimuat di semua halaman (lewat footer), jadi setiap blok
// dibungkus pengecekan null/length agar tidak error di halaman lain.

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

// =========================
// STATUS BACA (auto-save saat dropdown diganti)
// =========================
document.querySelectorAll(".status-baca-select").forEach((select) => {
  select.addEventListener("change", async function () {
    const id = this.dataset.id;
    const status = this.value;

    try {
      const formData = new FormData();
      formData.append("id", id);
      formData.append("status_baca", status);

      const response = await fetch(BASEURL + "/daftarBuku/updateStatus", {
        method: "POST",
        headers: csrfHeaders(),
        body: formData,
      });

      const hasil = await response.text();

      if (response.ok && hasil.trim() === "success") {
        Swal.fire({
          icon: "success",
          title: "Status baca diperbarui",
          timer: 1200,
          showConfirmButton: false,
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Gagal",
          text: hasil.trim() || "Terjadi kesalahan",
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

// =========================
// PROGRESS MEMBACA
// =========================
document.querySelectorAll(".progress-form").forEach((form) => {
  form.addEventListener("submit", async function (e) {
    e.preventDefault();

    const id = this.dataset.id;
    const halamanDibaca =
      this.querySelector(".halaman-dibaca-input").value || "0";
    const totalHalaman = this.querySelector(".total-halaman-input").value || "";

    try {
      const formData = new FormData();
      formData.append("id", id);
      formData.append("halaman_dibaca", halamanDibaca);
      formData.append("total_halaman", totalHalaman);

      const response = await fetch(BASEURL + "/daftarBuku/updateProgress", {
        method: "POST",
        headers: csrfHeaders(),
        body: formData,
      });

      const hasil = await response.text();

      if (response.ok && hasil.trim() === "success") {
        Swal.fire({
          icon: "success",
          title: "Progress disimpan",
          timer: 1200,
          showConfirmButton: false,
        }).then(() => location.reload());
      } else {
        Swal.fire({
          icon: "error",
          title: "Gagal",
          text: hasil.trim() || "Terjadi kesalahan",
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

// =========================
// CATATAN PRIBADI
// =========================
const catatanModalEl = document.getElementById("catatanModal");
const catatanForm = document.getElementById("catatanForm");

if (catatanModalEl && catatanForm) {
  const catatanModal = new bootstrap.Modal(catatanModalEl);

  document.querySelectorAll(".btn-catatan").forEach((button) => {
    button.addEventListener("click", function () {
      document.getElementById("catatan_id").value = this.dataset.id;
      document.getElementById("catatan_judul").textContent = this.dataset.judul;
      document.getElementById("catatan_pribadi").value =
        this.dataset.catatan || "";

      catatanModal.show();
    });
  });

  catatanForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    try {
      const formData = new FormData(this);

      const response = await fetch(BASEURL + "/daftarBuku/updateCatatan", {
        method: "POST",
        headers: csrfHeaders(),
        body: formData,
      });

      const hasil = await response.text();

      if (response.ok && hasil.trim() === "success") {
        catatanModal.hide();

        Swal.fire({
          icon: "success",
          title: "Catatan disimpan",
          timer: 1200,
          showConfirmButton: false,
        }).then(() => location.reload());
      } else {
        Swal.fire({
          icon: "error",
          title: "Gagal",
          text: hasil.trim() || "Terjadi kesalahan",
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
}
