// Khusus TV mode: reload penuh halaman secara berkala.
// Kenapa reload penuh? agar data TT + jadwal dokter selalu segar & koneksi DB
// di-reset ulang (mencegah timer/memory menumpuk di layar yang nyala 24 jam).
// Catatan: reload mengembalikan auto-scroll ke atas lalu mulai lagi.
$(function () {
    const RELOAD_MS = 30 * 60 * 1000; // reload tiap 30 menit (ubah sesuai kebutuhan)

    // Hitung mundur (detik) sebelum reload cukup ditampilkan di console.
    const startAt = Date.now();
    setInterval(() => {
        const sisa = Math.max(0, RELOAD_MS - (Date.now() - startAt));
        console.log('[TV] Reload dalam ' + Math.ceil(sisa / 1000) + 's');
    }, 60000); // log tiap menit

    setTimeout(() => window.location.reload(), RELOAD_MS);
});
