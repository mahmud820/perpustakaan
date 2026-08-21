// Fitur Tambah & Update Buku
const addBookForm = document.getElementById('addBookForm');
const addBookModal = document.getElementById('addBookModal');

addBookForm.addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {

        const id = formData.get('id');

        const url = id
            ? BASEURL + '/daftarBuku/update'
            : BASEURL + '/daftarBuku/tambah';

        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        const result = await response.text();

        if (result.trim() === 'success') {

            const modal = bootstrap.Modal.getInstance(addBookModal);

            modal.hide();

            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: id
                    ? 'Buku berhasil diupdate'
                    : 'Buku berhasil ditambahkan',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });

        } else {

            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: id
                    ? 'Buku gagal diupdate'
                    : 'Buku gagal ditambahkan'
            });

        }

    } catch (error) {

        console.error(error);

        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Terjadi kesalahan pada server'
        });

    }
});


// Reset Modal Saat Ditutup
addBookModal.addEventListener('hidden.bs.modal', function () {

    addBookForm.reset();

    document.getElementById('id').value = '';
    document.getElementById('cover_lama').value = '';

    document.querySelector('.modal-title').textContent =
        'Tambah Buku';

    document.querySelector(
        '#addBookForm button[type="submit"]'
    ).textContent = 'Simpan';

});


// Fitur Edit Buku
document.querySelectorAll('.btn-edit').forEach(button => {

    button.addEventListener('click', function () {

        document.getElementById('id').value =
            this.dataset.id;

        document.getElementById('cover_lama').value =
            this.dataset.cover;

        document.querySelector('[name="judul"]').value =
            this.dataset.judul;

        document.querySelector('[name="penulis"]').value =
            this.dataset.penulis;

        document.querySelector('[name="klasifikasi"]').value =
            this.dataset.klasifikasi;

        document.querySelector('[name="sinopsis"]').value =
            this.dataset.sinopsis;

        document.querySelector('[name="link_baca"]').value =
            this.dataset.linkBaca;

        document.querySelector('.modal-title').textContent =
            'Edit Buku';

        document.querySelector(
            '#addBookForm button[type="submit"]'
        ).textContent = 'Update';

        const modal = new bootstrap.Modal(addBookModal);

        modal.show();

    });

});

// Fitur Hapus
document.querySelectorAll('.btn-delete').forEach(button => {

    button.addEventListener('click', async function () {

        const id = this.dataset.id;
        const cover = this.dataset.cover;

        const result = await Swal.fire({
            title: 'Yakin?',
            text: 'Data buku akan dihapus',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        });

        if (!result.isConfirmed) return;

        try {

            const formData = new FormData();

            formData.append('id', id);
            formData.append('cover', cover);

            const response = await fetch(
                BASEURL + '/daftarBuku/hapus',
                {
                    method: 'POST',
                    body: formData
                }
            );

            const hasil = await response.text();

            if (hasil.trim() === 'success') {

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Buku berhasil dihapus',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });

            } else {

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Buku gagal dihapus'
                });

            }

        } catch (error) {

            console.error(error);

            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Terjadi kesalahan pada server'
            });

        }

    });

});