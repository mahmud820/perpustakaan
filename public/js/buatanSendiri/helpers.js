// Fungsi bantu yang dipakai bersama oleh buku.js dan admin.js.
// File ini HARUS dimuat sebelum buku.js dan admin.js (lihat templates/footer.php).

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
// Kirim POST ke backend.
// Kontrak backend: status 200 + body "success" = berhasil, selain itu body = pesan error.
// Return: { ok, status, text }
// =========================
async function postForm(url, formData) {
  const response = await fetch(url, {
    method: "POST",
    headers: csrfHeaders(),
    body: formData,
  });

  const text = (await response.text()).trim();

  return {
    ok: response.ok && text === "success",
    status: response.status,
    text,
  };
}

// =========================
// Notifikasi (SweetAlert2)
// =========================
function notifySuccess(title, { text = "", timer = 1200, reload = false } = {}) {
  return Swal.fire({
    icon: "success",
    title,
    text,
    timer,
    showConfirmButton: false,
  }).then(() => {
    if (reload) location.reload();
  });
}

// Dipakai saat backend membalas gagal (hasil dari postForm)
function notifyFailure(result) {
  // 401 = sesi login habis -> arahkan ke halaman login, bukan sekadar menampilkan error
  if (result.status === 401) {
    return Swal.fire({
      icon: "warning",
      title: "Sesi berakhir",
      text: result.text || "Silakan login kembali.",
    }).then(() => {
      location.href = BASEURL + "/auth/login";
    });
  }

  return Swal.fire({
    icon: "error",
    title: "Gagal",
    text: result.text || "Terjadi kesalahan",
  });
}

// Dipakai di blok catch (jaringan putus, server tidak merespon, dll)
function notifyServerError(error) {
  console.error(error);

  return Swal.fire({
    icon: "error",
    title: "Oops...",
    text: "Terjadi kesalahan pada server",
  });
}
