// Tampilan data tempat tidur (read-only) - versi jQuery
$(function () {
    const REFRESH_MS = 30000; // auto refresh tiap 30 detik

    const $cardView = $('#cardView');
    const $tableView = $('#tableView');
    const $tbody = $('#tbody');
    const $search = $('#search');
    const $filterKelas = $('#filterKelas');
    const $alertBox = $('#alertBox');
    let rows = [];

    // Escape teks agar aman dari XSS
    const esc = (s) => $('<div>').text(s ?? '').html().replace(/"/g, '&quot;').replace(/'/g, '&#39;');

    // Status warna berdasarkan persentase keterisian
    function status(pct) {
        if (pct >= 80) return { cls: 'danger', hex: '#ef4444', text: 'Penuh / Kritis' };
        if (pct >= 50) return { cls: 'warning', hex: '#f59e0b', text: 'Hampir Penuh' };
        return { cls: 'success', hex: '#10b981', text: 'Tersedia' };
    }

    function calc(r) {
        const total = r.terisi + r.kosong;
        const pct = total ? Math.round((r.terisi / total) * 100) : 0;
        return { total, pct, st: status(pct) };
    }

    function renderSummary() {
        let t = 0, k = 0;
        $.each(rows, (_, r) => { t += r.terisi; k += r.kosong; });
        $('#sumTerisi').text(t);
        $('#sumKosong').text(k);
        $('#sumTotal').text(t + k);
        $('#sumBor').text((t + k ? Math.round((t / (t + k)) * 100) : 0) + '%');
    }

    function renderFilter() {
        const current = $filterKelas.val();
        const kelas = [...new Set($.map(rows, r => r.kelas))].sort();
        let html = '<option value="">Semua Kelas</option>';
        $.each(kelas, (_, k) => { html += `<option value="${esc(k)}">Kelas ${esc(k)}</option>`; });
        $filterKelas.html(html).val(kelas.includes(current) ? current : '');
    }

    function filtered() {
        const q = $.trim($search.val()).toLowerCase();
        const k = $filterKelas.val();
        return $.grep(rows, r => String(r.nama_ruang).toLowerCase().includes(q) && (!k || r.kelas === k));
    }

    function renderCards(list) {
        if (!list.length) {
            $cardView.html('<div class="col-12 text-center text-muted py-5"><i class="bi bi-inbox fs-1"></i><div>Data tidak ditemukan</div></div>');
            return;
        }
        const html = $.map(list, (r, i) => {
            const { total, pct, st } = calc(r);
            return `<div class="col-sm-6 col-lg-4 col-xl-3 fade-up" style="animation-delay:${i * 60}ms">
                <div class="card room-card h-100">
                    <div class="top-bar bg-${st.cls}"></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <div class="room-name">${esc(r.nama_ruang)}</div>
                                <span class="badge rounded-pill text-bg-light border">Kelas ${esc(r.kelas)}</span>
                            </div>
                            <span class="badge rounded-pill bg-${st.cls}-subtle text-${st.cls}-emphasis">${st.text}</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="donut" style="--p:${pct};--c:${st.hex}" data-label="${pct}%"
                                 role="img" aria-label="Keterisian ${pct} persen"></div>
                            <div class="flex-grow-1 d-grid gap-2">
                                <div class="mini-stat"><div class="num text-danger">${r.terisi}</div><div class="lbl">Terisi</div></div>
                                <div class="mini-stat"><div class="num text-success">${r.kosong}</div><div class="lbl">Kosong</div></div>
                            </div>
                        </div>
                        <div class="text-center small text-muted mt-3"><i class="bi bi-grid-3x3-gap"></i> Total ${total} tempat tidur</div>
                    </div>
                </div>
            </div>`;
        }).join('');
        $cardView.html(html);
    }

    function renderTable(list) {
        if (!list.length) {
            $tbody.html('<tr><td colspan="7" class="text-center text-muted py-4">Data tidak ditemukan</td></tr>');
            return;
        }
        const html = $.map(list, (r, i) => {
            const { total, pct, st } = calc(r);
            return `<tr>
                <td class="text-center">${i + 1}</td>
                <td class="fw-semibold text-capitalize">${esc(r.nama_ruang)}</td>
                <td class="text-center"><span class="badge rounded-pill text-bg-light border">${esc(r.kelas)}</span></td>
                <td class="text-center fw-bold text-danger">${r.terisi}</td>
                <td class="text-center fw-bold text-success">${r.kosong}</td>
                <td class="text-center">${total}</td>
                <td>
                    <div class="progress" role="progressbar" aria-label="Keterisian ${esc(r.nama_ruang)}"
                         aria-valuenow="${pct}" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar bg-${st.cls}" style="width:${pct}%">${pct}%</div>
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
        $.ajax({ url: 'api.php', method: 'GET', dataType: 'json', cache: false })
            .done(json => {
                rows = json.data || [];
                $alertBox.empty();
                $('#lastUpdate').text(String(json.updated || '').slice(11));
                renderSummary();
                renderFilter();
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

    // Ganti mode tampilan kartu / tabel
    $('[data-view]').on('click', function () {
        const view = $(this).data('view');
        $('[data-view]').removeClass('active');
        $(this).addClass('active');
        $cardView.toggleClass('d-none', view !== 'card');
        $tableView.toggleClass('d-none', view !== 'table');
    });

    $search.on('input', render);
    $filterKelas.on('change', render);

    tick();
    setInterval(tick, 1000);
    load();
    setInterval(load, REFRESH_MS);
});
