// Semua elemen di bawah ini HANYA ada di halaman Daftar Buku.
// File ini dimuat di semua halaman (lewat footer), jadi setiap blok
// dibungkus pengecekan null agar tidak error di halaman lain.
// Fungsi bantu (submitAction, notifySuccess, dll) ada di helpers.js.

const addBookForm = document.getElementById("addBookForm");
const addBookModal = document.getElementById("addBookModal");

if (addBookForm && addBookModal) {
  const modalTitle = addBookModal.querySelector(".modal-title");
  const submitButton = addBookForm.querySelector('button[type="submit"]');
  const idInput = document.getElementById("id");
  const fileBacaInfo = document.getElementById("fileBacaInfo");
  const hapusWrapper = document.getElementById("hapusFileBacaWrapper");
  const hapusCheckbox = document.getElementById("hapus_file_baca");

  // Info "File saat ini" + checkbox hapus. Dipakai saat edit DAN saat reset modal.
  // File input tidak bisa diisi otomatis oleh browser (alasan keamanan),
  // jadi cukup tampilkan nama file yang sudah ada.
  function tampilkanFileBaca(namaFile) {
    if (fileBacaInfo) {
      fileBacaInfo.textContent = namaFile ? "File saat ini: " + namaFile : "";
    }
    if (hapusWrapper) hapusWrapper.classList.toggle("d-none", !namaFile);
    if (hapusCheckbox) hapusCheckbox.checked = false;
  }

  // Fitur Tambah & Update Buku
  addBookForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const id = formData.get("id");
    const url = BASEURL + (id ? "/daftarBuku/update" : "/daftarBuku/tambah");

    submitAction(
      url,
      formData,
      () => {
        bootstrap.Modal.getOrCreateInstance(addBookModal).hide();

        notifySuccess("Berhasil", {
          text: id ? "Buku berhasil diupdate" : "Buku berhasil ditambahkan",
          timer: 1500,
          reload: true,
        });
      },
      submitButton,
    );
  });

  // Reset Modal Saat Ditutup
  addBookModal.addEventListener("hidden.bs.modal", function () {
    addBookForm.reset();
    idInput.value = "";
    tampilkanFileBaca("");

    modalTitle.textContent = "Tambah Buku";
    submitButton.textContent = "Simpan";
  });

  // Fitur Edit Buku
  document.querySelectorAll(".btn-edit").forEach((button) => {
    button.addEventListener("click", function () {
      idInput.value = this.dataset.id;

      addBookForm.elements["judul"].value = this.dataset.judul;
      addBookForm.elements["penulis"].value = this.dataset.penulis;
      addBookForm.elements["klasifikasi"].value = this.dataset.klasifikasi;
      addBookForm.elements["sinopsis"].value = this.dataset.sinopsis;
      addBookForm.elements["link_baca"].value = this.dataset.linkBaca;

      tampilkanFileBaca(this.dataset.fileBaca);

      modalTitle.textContent = "Edit Buku";
      submitButton.textContent = "Update";

      bootstrap.Modal.getOrCreateInstance(addBookModal).show();
    });
  });
}

// Fitur Hapus
document.querySelectorAll(".btn-delete").forEach((button) => {
  button.addEventListener("click", async function () {
    const konfirmasi = await Swal.fire({
      title: "Yakin?",
      text: "Data buku akan dihapus",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Ya, Hapus",
      cancelButtonText: "Batal",
    });

    if (!konfirmasi.isConfirmed) return;

    const formData = new FormData();
    formData.append("id", this.dataset.id);

    submitAction(BASEURL + "/daftarBuku/hapus", formData, () =>
      notifySuccess("Berhasil", {
        text: "Buku berhasil dihapus",
        timer: 1500,
        reload: true,
      }),
    );
  });
});
