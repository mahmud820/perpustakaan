// Semua elemen di bawah ini HANYA ada di halaman Dashboard Admin.
// File ini dimuat di semua halaman (lewat footer), jadi setiap blok
// dibungkus pengecekan null/length agar tidak error di halaman lain.
// Fungsi bantu (submitAction, notifySuccess, dll) ada di helpers.js.

// =========================
// STATUS BACA (auto-save saat dropdown diganti)
// =========================
document.querySelectorAll(".status-baca-select").forEach((select) => {
  select.addEventListener("change", async function () {
    const status = this.value;

    const formData = new FormData();
    formData.append("id", this.dataset.id);
    formData.append("status_baca", status);

    const berhasil = await submitAction(
      BASEURL + "/daftarBuku/updateStatus",
      formData,
      () => notifySuccess("Status baca diperbarui"),
    );

    if (berhasil) {
      // simpan status terakhir yang BERHASIL disimpan
      this.dataset.status = status;
    } else {
      // gagal disimpan -> kembalikan dropdown ke status sebelumnya
      // supaya tampilan tidak berbohong soal data di database
      this.value = this.dataset.status;
    }
  });
});

// =========================
// PROGRESS MEMBACA
// =========================
document.querySelectorAll(".progress-form").forEach((form) => {
  form.addEventListener("submit", function (e) {
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

    submitAction(
      BASEURL + "/daftarBuku/updateProgress",
      formData,
      () => notifySuccess("Progress disimpan", { reload: true }),
      this.querySelector('button[type="submit"]'),
    );
  });
});

// =========================
// CATATAN PRIBADI
// =========================
const catatanModalEl = document.getElementById("catatanModal");
const catatanForm = document.getElementById("catatanForm");

if (catatanModalEl && catatanForm) {
  const catatanModal = bootstrap.Modal.getOrCreateInstance(catatanModalEl);

  document.querySelectorAll(".btn-catatan").forEach((button) => {
    button.addEventListener("click", function () {
      document.getElementById("catatan_id").value = this.dataset.id;
      document.getElementById("catatan_judul").textContent = this.dataset.judul;
      document.getElementById("catatan_pribadi").value =
        this.dataset.catatan || "";

      catatanModal.show();
    });
  });

  catatanForm.addEventListener("submit", function (e) {
    e.preventDefault();

    submitAction(
      BASEURL + "/daftarBuku/updateCatatan",
      new FormData(this),
      () => {
        catatanModal.hide();
        notifySuccess("Catatan disimpan", { reload: true });
      },
      this.querySelector('button[type="submit"]'),
    );
  });
}
