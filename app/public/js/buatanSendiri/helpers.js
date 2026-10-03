// Fungsi bantu yang dipakai bersama oleh buku.js dan admin.js.
// File ini HARUS dimuat sebelum buku.js dan admin.js (lihat templates/footer.php).
// Bergantung pada Swal dan bootstrap (keduanya dimuat footer.php sebelum file ini).

// =========================
// BASEURL
// Dibaca dari <meta name="base-url"> yang dirender server (lihat header.php),
// bukan dari <script> inline, supaya Content-Security-Policy "script-src 'self'" tidak memblokirnya.
// =========================
const BASEURL = (
  document.querySelector('meta[name="base-url"]')?.getAttribute("content") ?? ""
).replace(/\/+$/, "");

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

// =========================
// Satu pintu untuk semua aksi tulis:
// kirim -> sukses? panggil onSuccess : tampilkan error -> jaringan putus? tampilkan error server.
// Return: true kalau berhasil, false kalau gagal (pemanggil bisa memakainya, mis. mengembalikan dropdown).
// Tombol submit (opsional) dikunci selama request berjalan supaya tidak terkirim dua kali.
// =========================
async function submitAction(url, formData, onSuccess, button = null) {
  if (button) button.disabled = true;

  try {
    const result = await postForm(url, formData);

    if (result.ok) {
      onSuccess();
      return true;
    }

    notifyFailure(result);
    return false;
  } catch (error) {
    notifyServerError(error);
    return false;
  } finally {
    if (button) button.disabled = false;
  }
}

// =========================
// Gambar cadangan (pengganti atribut onerror="..." di tag <img>)
// Pemakaian di view: <img data-fallback="no-image.png"> -> kalau gagal dimuat, diganti BASEURL/img/no-image.png.
// Hanya dicoba SEKALI per gambar supaya tidak looping kalau file cadangan juga tidak ada.
// =========================
function pakaiGambarCadangan(img) {
  if (!img.dataset.fallback || img.dataset.fallbackDone) return;

  img.dataset.fallbackDone = "1";
  img.src = BASEURL + "/img/" + img.dataset.fallback;
}

// Event "error" pada <img> tidak menggelembung, jadi didengar di fase capture.
document.addEventListener("error", (e) => {
  if (e.target instanceof HTMLImageElement) pakaiGambarCadangan(e.target);
}, true);

// Gambar yang SUDAH gagal sebelum file ini dimuat tidak akan memicu event lagi, jadi disapu manual.
document.querySelectorAll("img[data-fallback]").forEach((img) => {
  if (img.complete && img.naturalWidth === 0) pakaiGambarCadangan(img);
});

// =========================
// Pesan flash dari server (pengganti <script> inline di halaman profil)
// Markup: <div data-flash="success|error" data-flash-message="..."></div>
// =========================
function tampilkanFlash() {
  document.querySelectorAll("[data-flash]").forEach((el) => {
    const sukses = el.dataset.flash === "success";

    Swal.fire({
      icon: sukses ? "success" : "error",
      title: sukses ? "Berhasil" : "Gagal",
      text: el.dataset.flashMessage || "",
      confirmButtonColor: "#e5c158",
    });

    el.remove();
  });
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", tampilkanFlash);
} else {
  tampilkanFlash();
}
