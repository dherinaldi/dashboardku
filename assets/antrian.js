// Dashboard capaian antrian online BPJS (read-only) - versi jQuery
$(function () {
    const REFRESH_MS = 60000; // auto refresh tiap 1 menit

    const $awal = $('#tglAwal');
    const $akhir = $('#tglAkhir');
    const $alertBox = $('#alertBox');
    const $tbodyBulanan = $('#tbodyBulanan');
    const $tbodyHarian = $('#tbodyHarian');

    // Escape teks agar aman dari XSS
    const esc = (s) => $('<div>').text(s ?? '').html().replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    const fmt = (n) => Number(n).toLocaleString('id-ID');
    const pct = (n) => Number(n).toFixed(2) + '%';
    const ymd = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;

    // Warna capaian: >=85 hijau, 70-84 kuning, <70 merah
    function capCls(v) {
        if (v >= 85) return 'success';
        if (v >= 70) return 'warning';
        return 'danger';
    }

    function rowHtml(r, i) {
        const cls = capCls(r.capaian);
        return `<tr>
            <td class="text-center">${i + 1}</td>
            <td class="fw-semibold">${esc(r.periode)}</td>
            <td class="text-center">${fmt(r.vclaim)}</td>
            <td class="text-center">${fmt(r.simgos)}</td>
            <td class="text-center fw-bold">${fmt(r.jmlsep)}</td>
            <td class="text-center">${fmt(r.mjkn)}</td>
            <td class="text-center">${fmt(r.rs)}</td>
            <td class="text-center">${fmt(r.antrian)}</td>
            <td class="text-center text-danger">${fmt(r.batal)}</td>
            <td class="text-center">${fmt(r.blmlyn)}</td>
            <td class="text-center">${fmt(r.sdglyn)}</td>
            <td class="text-center text-success fw-bold">${fmt(r.slslyn)}</td>
            <td class="text-center">${pct(r.potensi)}</td>
            <td class="text-center"><span class="badge rounded-pill bg-${cls}-subtle text-${cls}-emphasis">${pct(r.capaian)}</span></td>
            <td class="text-center">${pct(r.persen_rs)}</td>
            <td class="text-center">${pct(r.persen_mjkn)}</td>
        </tr>`;
    }

    function renderTable($tbody, list, cols) {
        if (!list.length) {
            $tbody.html(`<tr><td colspan="${cols}" class="text-center text-muted py-4"><i class="bi bi-inbox"></i> Tidak ada data</td></tr>`);
            return;
        }
        $tbody.html($.map(list, rowHtml).join(''));
    }

    // Ringkasan diambil dari baris bulanan terakhir (periode terbaru)
    function renderSummary(bulanan) {
        if (!bulanan.length) {
            $('#sumSep').text('0');
            $('#sumSelesai').text('0');
            $('#sumCapaian').text('0%');
            $('#sumPotensi').text('0%');
            return;
        }
        const b = bulanan[bulanan.length - 1];
        $('#sumSep').text(fmt(b.jmlsep));
        $('#sumSelesai').text(fmt(b.slslyn));
        $('#sumCapaian').text(pct(b.capaian));
        $('#sumPotensi').text(pct(b.potensi));
    }

    function load() {
        $.ajax({
            url: 'api_antrian.php', method: 'GET', dataType: 'json', cache: false,
            data: { awal: $awal.val(), akhir: $akhir.val() }
        })
            .done(json => {
                $alertBox.empty();
                $awal.val(json.awal);
                $akhir.val(json.akhir);
                const f = (s) => new Date(s + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                $('#periode').text(json.awal === json.akhir ? f(json.awal) : `${f(json.awal)} s/d ${f(json.akhir)}`);
                $('#waktuCetak').text(json.waktu_cetak || '-');
                $('#lastUpdate').text(String(json.updated || '').slice(11));
                renderTable($tbodyBulanan, json.bulanan || [], 16);
                renderTable($tbodyHarian, json.harian || [], 16);
                renderSummary(json.bulanan || []);
            })
            .fail(xhr => {
                const msg = (xhr.responseJSON && xhr.responseJSON.error) || 'Gagal memuat data.';
                $alertBox.html(`<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ${esc(msg)}</div>`);
            });
    }

    // Jam digital
    function tick() {
        const now = new Date();
        $('#clock').text(now.toLocaleTimeString('id-ID'));
        $('#date').text(now.toLocaleDateString('id-ID',
            { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }));
    }

    // Filter tanggal
    $('#filterForm').on('submit', function (e) {
        e.preventDefault();
        load();
    });

    // Rentang cepat
    $('[data-range]').on('click', function () {
        const r = $(this).data('range');
        const now = new Date();
        const start = new Date(now);
        if (r === 'month') start.setDate(1);
        else start.setDate(now.getDate() - Number(r));
        $awal.val(ymd(start));
        $akhir.val(ymd(now));
        load();
    });

    // Sinkronisasi: hit webservice getAntreanPerTanggal per tanggal lewat proxy PHP
    const $syncBox = $('#syncBox');
    const $btnSync = $('#btnSync');
    $btnSync.on('click', function () {
        const a = $awal.val(), b = $akhir.val();
        if (!a || !b) return;
        if (!confirm(`Sinkronisasi data antrean dari ${a} s/d ${b}?\nProses ini menarik data per tanggal dari webservice dan bisa memakan waktu.`)) return;

        const $btn = $(this);
        const oldHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyinkronkan...');
        $syncBox.html('<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Menarik data dari webservice, mohon tunggu...</div>');

        $.ajax({
            url: 'api_antrian_sync.php', method: 'GET', dataType: 'json', cache: false,
            data: { awal: a, akhir: b }, timeout: 300000
        })
            .done(res => {
                const cls = res.gagal ? 'warning' : 'success';
                let rows = $.map(res.detail || [], d =>
                    `<tr><td>${esc(d.tanggal)}</td>
                     <td class="text-center">${d.ok ? '<span class="badge bg-success">OK</span>' : '<span class="badge bg-danger">GAGAL</span>'}</td>
                     <td class="text-center">${esc(d.code)}</td>
                     <td class="small text-muted">${esc(d.pesan)}</td></tr>`).join('');
                $syncBox.html(
                    `<div class="alert alert-${cls}">
                        <i class="bi bi-check2-circle"></i> Sinkronisasi selesai:
                        <b>${res.sukses}</b> sukses, <b>${res.gagal}</b> gagal dari <b>${res.total}</b> tanggal.
                        <div class="table-responsive mt-2"><table class="table table-sm mb-0 bg-white">
                        <thead><tr><th>Tanggal</th><th class="text-center">Status</th><th class="text-center">HTTP</th><th>Pesan</th></tr></thead>
                        <tbody>${rows}</tbody></table></div>
                     </div>`);
                load(); // refresh tabel rekap
            })
            .fail(xhr => {
                const msg = (xhr.responseJSON && xhr.responseJSON.error) || (xhr.statusText === 'timeout' ? 'Timeout — rentang terlalu panjang.' : 'Sinkronisasi gagal.');
                $syncBox.html(`<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ${esc(msg)}</div>`);
            })
            .always(() => $btn.prop('disabled', false).html(oldHtml));
    });

    // Default: awal bulan ini s/d hari ini
    const now = new Date();
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    $awal.val(ymd(firstDay));
    $akhir.val(ymd(now));

    tick();
    setInterval(tick, 1000);
    load();
    setInterval(load, REFRESH_MS);
});
