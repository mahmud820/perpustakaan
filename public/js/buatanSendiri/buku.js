// Semua elemen di bawah ini HANYA ada di halaman Daftar Buku.
// File ini dimuat di semua halaman (lewat footer), jadi setiap blok
// dibungkus pengecekan null agar tidak error di halaman lain.
// Fungsi bantu (postForm, notifySuccess, dll) ada di helpers.js.

const addBookForm = document.getElementById("addBookForm");
const addBookModal = document.getElementById("addBookModal");

if (addBookForm && addBookModal) {
  const modalTitle = addBookModal.querySelector(".modal-title");
  const submitButton = addBookForm.querySelector('button[type="submit"]');

  // Fitur Tambah & Update Buku
  addBookForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const id = formData.get("id");
    const url = id
      ? BASEURL + "/daftarBuku/update"
      : BASEURL + "/daftarBuku/tambah";

    try {
      const result = await postForm(url, formData);

      if (result.ok) {
        bootstrap.Modal.getInstance(addBookModal).hide();

        notifySuccess("Berhasil", {
          text: id ? "Buku berhasil diupdate" : "Buku berhasil ditambahkan",
          timer: 1500,
          reload: true,
        });
      } else {
        notifyFailure(result);
      }
    } catch (error) {
      notifyServerError(error);
    }
  });

  // Reset Modal Saat Ditutup
  addBookModal.addEventListener("hidden.bs.modal", function () {
    addBookForm.reset();

    document.getElementById("id").value = "";

    const fileBacaInfo = document.getElementById("fileBacaInfo");
    if (fileBacaInfo) fileBacaInfo.textContent = "";

    const hapusWrapper = document.getElementById("hapusFileBacaWrapper");
    if (hapusWrapper) hapusWrapper.classList.add("d-none");

    const hapusCheckbox = document.getElementById("hapus_file_baca");
    if (hapusCheckbox) hapusCheckbox.checked = false;

    modalTitle.textContent = "Tambah Buku";
    submitButton.textContent = "Simpan";
  });

  // Fitur Edit Buku
  document.querySelectorAll(".btn-edit").forEach((button) => {
    button.addEventListener("click", function () {
      document.getElementById("id").value = this.dataset.id;

      addBookForm.elements["judul"].value = this.dataset.judul;
      addBookForm.elements["penulis"].value = this.dataset.penulis;
      addBookForm.elements["klasifikasi"].value = this.dataset.klasifikasi;
      addBookForm.elements["sinopsis"].value = this.dataset.sinopsis;
      addBookForm.elements["link_baca"].value = this.dataset.linkBaca;

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

      modalTitle.textContent = "Edit Buku";
      submitButton.textContent = "Update";

      new bootstrap.Modal(addBookModal).show();
    });
  });
}

// Fitur Hapus
document.querySelectorAll(".btn-delete").forEach((button) => {
  button.addEventListener("click", async function () {
    const id = this.dataset.id;

    const konfirmasi = await Swal.fire({
      title: "Yakin?",
      text: "Data buku akan dihapus",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Ya, Hapus",
      cancelButtonText: "Batal",
    });

    if (!konfirmasi.isConfirmed) return;

    try {
      const formData = new FormData();
      formData.append("id", id);

      const result = await postForm(BASEURL + "/daftarBuku/hapus", formData);

      if (result.ok) {
        notifySuccess("Berhasil", {
          text: "Buku berhasil dihapus",
          timer: 1500,
          reload: true,
        });
      } else {
        notifyFailure(result);
      }
    } catch (error) {
      notifyServerError(error);
    }
  });
});
