// Dashboard pengiriman SATUSEHAT (read-only) - versi jQuery
$(function () {
    const REFRESH_MS = 60000; // auto refresh tiap 1 menit

    const $cardView = $('#cardView');
    const $tableView = $('#tableView');
    const $tbody = $('#tbody');
    const $search = $('#search');
    const $awal = $('#tglAwal');
    const $akhir = $('#tglAkhir');
    const $alertBox = $('#alertBox');
    let rows = [];

    // Ikon per resource
    const ICONS = {
        'Organization': 'bi-building', 'Location': 'bi-geo-alt', 'Patient': 'bi-person',
        'Practitioner': 'bi-person-badge', 'Encounter': 'bi-door-open', 'Condition': 'bi-clipboard2-pulse',
        'Observation': 'bi-activity', 'Procedure': 'bi-bandaid', 'Composition': 'bi-file-earmark-text',
        'Medication': 'bi-capsule', 'Medication Request': 'bi-prescription2', 'Medication Dispance': 'bi-box-seam',
        'Service Request': 'bi-send', 'Specimen': 'bi-droplet', 'Diagnostic Report': 'bi-file-medical',
        'Allergy Intolerance': 'bi-exclamation-octagon'
    };

    // Escape teks agar aman dari XSS
    const esc = (s) => $('<div>').text(s ?? '').html().replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    const fmt = (n) => Number(n).toLocaleString('id-ID');
    const ymd = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;

    // Status warna berdasarkan persentase terkirim
    function status(r) {
        if (!r.total) return { cls: 'secondary', hex: '#94a3b8', text: 'Tidak Ada Data' };
        if (r.pct >= 95) return { cls: 'success', hex: '#10b981', text: 'Baik' };
        if (r.pct >= 80) return { cls: 'warning', hex: '#f59e0b', text: 'Perlu Perhatian' };
        return { cls: 'danger', hex: '#ef4444', text: 'Kritis' };
    }

    function calc(r) {
        const pct = r.total ? Math.round((r.terkirim / r.total) * 1000) / 10 : 0;
        return { ...r, pct };
    }

    function renderSummary() {
        let t = 0, k = 0, b = 0;
        $.each(rows, (_, r) => { t += r.total; k += r.terkirim; b += r.belum; });
        $('#sumTotal').text(fmt(t));
        $('#sumTerkirim').text(fmt(k));
        $('#sumBelum').text(fmt(b));
        $('#sumPct').text((t ? Math.round((k / t) * 1000) / 10 : 0) + '%');
    }

    function filtered() {
        const q = $.trim($search.val()).toLowerCase();
        return $.grep(rows, r => r.nama.toLowerCase().includes(q));
    }

    function renderCards(list) {
        if (!list.length) {
            $cardView.html('<div class="col-12 text-center text-muted py-5"><i class="bi bi-inbox fs-1"></i><div>Data tidak ditemukan</div></div>');
            return;
        }
        const html = $.map(list, (r, i) => {
            const st = status(r);
            const icon = ICONS[r.nama] || 'bi-cloud';
            return `<div class="col-sm-6 col-lg-4 col-xl-3 fade-up" style="animation-delay:${i * 40}ms">
                <div class="card room-card h-100">
                    <div class="top-bar bg-${st.cls}"></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="room-name"><i class="bi ${icon} text-${st.cls}"></i> ${esc(r.nama)}</div>
                            <span class="badge rounded-pill bg-${st.cls}-subtle text-${st.cls}-emphasis">${st.text}</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="donut" style="--p:${r.pct};--c:${st.hex}" data-label="${r.pct}%"
                                 role="img" aria-label="Terkirim ${r.pct} persen"></div>
                            <div class="flex-grow-1 d-grid gap-2">
                                <div class="mini-stat"><div class="num text-success">${fmt(r.terkirim)}</div><div class="lbl">Memiliki ID</div></div>
                                <div class="mini-stat"><div class="num text-danger">${fmt(r.belum)}</div><div class="lbl">Tidak Memiliki ID</div></div>
                            </div>
                        </div>
                        <div class="text-center small text-muted mt-3"><i class="bi bi-collection"></i> Total ${fmt(r.total)} data</div>
                    </div>
                </div>
            </div>`;
        }).join('');
        $cardView.html(html);
    }

    function renderTable(list) {
        if (!list.length) {
            $tbody.html('<tr><td colspan="6" class="text-center text-muted py-4">Data tidak ditemukan</td></tr>');
            return;
        }
        const html = $.map(list, (r, i) => {
            const st = status(r);
            return `<tr>
                <td class="text-center">${i + 1}</td>
                <td class="fw-semibold"><i class="bi ${ICONS[r.nama] || 'bi-cloud'} text-${st.cls}"></i> ${esc(r.nama)}</td>
                <td class="text-center fw-bold text-success">${fmt(r.terkirim)}</td>
                <td class="text-center fw-bold text-danger">${fmt(r.belum)}</td>
                <td class="text-center">${fmt(r.total)}</td>
                <td>
                    <div class="progress" role="progressbar" aria-label="Terkirim ${esc(r.nama)}"
                         aria-valuenow="${r.pct}" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar bg-${st.cls}" style="width:${r.pct}%">${r.pct}%</div>
                    </div>
                </td>
            </tr>`;
        }).join('');
        $tbody.html(html);
    }

    function render() {
        const list = filtered();
        renderCards(list);
        renderTable(list);
    }

    function load() {
        $.ajax({
            url: 'api_satusehat.php', method: 'GET', dataType: 'json', cache: false,
            data: { awal: $awal.val(), akhir: $akhir.val() }
        })
            .done(json => {
                rows = $.map(json.data || [], calc);
                $alertBox.empty();
                $awal.val(json.awal);
                $akhir.val(json.akhir);
                const f = (s) => new Date(s + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                $('#periode').text(json.awal === json.akhir ? f(json.awal) : `${f(json.awal)} s/d ${f(json.akhir)}`);
                $('#lastUpdate').text(String(json.updated || '').slice(11));
                renderSummary();
                render();
            })
            .fail(xhr => {
                const msg = (xhr.responseJSON && xhr.responseJSON.error) || 'Gagal memuat data.';
                $alertBox.html(`<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ${esc(msg)}</div>`);
                if (!rows.length) $cardView.empty();
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

    // Ganti mode tampilan kartu / tabel
    $('[data-view]').on('click', function () {
        const view = $(this).data('view');
        $('[data-view]').removeClass('active');
        $(this).addClass('active');
        $cardView.toggleClass('d-none', view !== 'card');
        $tableView.toggleClass('d-none', view !== 'table');
    });

    $search.on('input', render);

    const today = ymd(new Date());
    $awal.val(today);
    $akhir.val(today);

    tick();
    setInterval(tick, 1000);
    load();
    setInterval(load, REFRESH_MS);
});
