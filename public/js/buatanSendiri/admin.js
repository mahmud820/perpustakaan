// Semua elemen di bawah ini HANYA ada di halaman Dashboard Admin.
// File ini dimuat di semua halaman (lewat footer), jadi setiap blok
// dibungkus pengecekan null/length agar tidak error di halaman lain.
// Fungsi bantu (postForm, notifySuccess, dll) ada di helpers.js.

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

      const result = await postForm(
        BASEURL + "/daftarBuku/updateStatus",
        formData,
      );

      if (result.ok) {
        // simpan status terakhir yang BERHASIL disimpan
        this.dataset.status = status;

        notifySuccess("Status baca diperbarui");
      } else {
        // gagal disimpan -> kembalikan dropdown ke status sebelumnya
        // supaya tampilan tidak berbohong soal data di database
        this.value = this.dataset.status;

        notifyFailure(result);
      }
    } catch (error) {
      this.value = this.dataset.status;

      notifyServerError(error);
    }
  });
});

// =========================
// PROGRESS MEMBACA
// =========================
document.querySelectorAll(".progress-form").forEach((form) => {
  form.addEventListener("submit", async function (e) {
    e.preventDefault();

    const formData = new FormData();
    formData.append("id", this.dataset.id);
    formData.append(
      "halaman_dibaca",
      this.querySelector(".halaman-dibaca-input").value || "0",
    );
    formData.append(
      "total_halaman",
      this.querySelector(".total-halaman-input").value || "",
    );

    try {
      const result = await postForm(
        BASEURL + "/daftarBuku/updateProgress",
        formData,
      );

      if (result.ok) {
        notifySuccess("Progress disimpan", { reload: true });
      } else {
        notifyFailure(result);
      }
    } catch (error) {
      notifyServerError(error);
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
      const result = await postForm(
        BASEURL + "/daftarBuku/updateCatatan",
        new FormData(this),
      );

      if (result.ok) {
        catatanModal.hide();

        notifySuccess("Catatan disimpan", { reload: true });
      } else {
        notifyFailure(result);
      }
    } catch (error) {
      notifyServerError(error);
    }
  });
}
