// Jadwal dokter HFIS hari ini (read-only) - versi jQuery
$(function () {
    const JADWAL_REFRESH_MS = 5 * 60 * 1000; // refresh 5 menit (otomatis ganti hari)
    const $wrap = $('#jadwalView');
    const $search = $('#searchDokter');
    let jadwal = [];

    // Escape teks agar aman dari XSS
    const esc = (s) => $('<div>').text(s ?? '').html().replace(/"/g, '&quot;').replace(/'/g, '&#39;');

    // Status praktik berdasarkan jam sekarang
    function statusPraktik(j) {
        if (j.libur) return { cls: 'secondary', text: 'Libur' };
        if (!j.jam_mulai || !j.jam_selesai) return { cls: 'info', text: 'Terjadwal' };
        const now = new Date().toTimeString().slice(0, 5);
        if (now < j.jam_mulai) return { cls: 'primary', text: 'Akan Praktik' };
        if (now <= j.jam_selesai) return { cls: 'success', text: 'Sedang Praktik' };
        return { cls: 'dark', text: 'Selesai' };
    }

    function render() {
        const q = $.trim($search.val()).toLowerCase();
        const list = $.grep(jadwal, j => `${j.nm_dokter} ${j.kd_poli}`.toLowerCase().includes(q));
        $('#jadwalCount').text(`${list.length} dokter`);

        if (!list.length) {
            $wrap.html('<div class="col-12 text-center text-muted py-5"><i class="bi bi-calendar-x fs-1"></i><div>Tidak ada jadwal dokter hari ini</div></div>');
            return;
        }

        const html = $.map(list, (j, i) => {
            const st = statusPraktik(j);
            const jam = j.jam || `${j.jam_mulai ?? '-'} - ${j.jam_selesai ?? '-'}`;
            const sub = j.sub_spesialis && j.sub_spesialis !== j.kd_poli ? ' / ' + esc(j.sub_spesialis) : '';
            return `<div class="col-md-6 col-xl-4 fade-up" style="animation-delay:${i * 40}ms">
                <div class="card doctor-card h-100 ${j.libur ? 'is-libur' : ''}">
                    <div class="card-body d-flex gap-3">
                        <div class="doctor-avatar"><i class="bi bi-person-badge"></i></div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between gap-2">
                                <div class="doctor-name text-truncate" title="${esc(j.nm_dokter)}">${esc(j.nm_dokter)}</div>
                                <span class="badge rounded-pill text-bg-${st.cls} align-self-start">${st.text}</span>
                            </div>
                            <div class="small text-muted mb-2"><i class="bi bi-hospital"></i> ${esc(j.nm_poli)} (${j.kd_poli}) ${sub}</div>
                            <div class="jam-badge"><i class="bi bi-clock"></i> ${esc(jam)}</div>
                            <div class="d-flex gap-2 mt-2 small">
                                <span class="quota">Kapasitas <b>${j.kapasitas}</b></span>
                                <span class="quota">JKN <b>${j.kuota_jkn}</b></span>
                                <span class="quota">Non JKN <b>${j.kuota_non_jkn}</b></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        }).join('');
        $wrap.html(html);
    }

    function load() {
        $.ajax({ url: 'api_jadwal.php', method: 'GET', dataType: 'json', cache: false })
            .done(json => {
                jadwal = json.data || [];
                const tgl = new Date(json.tanggal + 'T00:00:00').toLocaleDateString('id-ID',
                    { day: 'numeric', month: 'long', year: 'numeric' });
                $('#jadwalHari').text(`${json.hari}, ${tgl}`);
                render();
            })
            .fail(xhr => {
                const msg = (xhr.responseJSON && xhr.responseJSON.error) || 'Gagal memuat jadwal.';
                $wrap.html(`<div class="col-12"><div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ${esc(msg)}</div></div>`);
            });
    }

    $search.on('input', render);
    load();
    setInterval(load, JADWAL_REFRESH_MS);
    setInterval(render, 60 * 1000); // update status praktik tiap menit
});
