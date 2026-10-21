(function () {
    'use strict';
    const rupiah = value => 'Rp ' + new Intl.NumberFormat('id-ID', {maximumFractionDigits: 0}).format(Number(value) || 0);
    const element = (tag, className, text) => {
        const node = document.createElement(tag);
        node.className = className || '';
        if (text !== undefined) node.textContent = text;
        return node;
    };
    const stamp = (value, timezone) => {
        if (!value) return '—';
        try { return new Intl.DateTimeFormat('id-ID', {timeZone: timezone, day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', hourCycle: 'h23'}).format(new Date(value)); }
        catch { return '—'; }
    };
    window.showPayrollAttendanceDetail = function (name, period, detail, downloadUrl) {
        if (!detail || !Array.isArray(detail.days)) return;
        const root = element('div', 'payroll-attendance-popup');
        root.append(element('p', 'payroll-attendance-person', name + ' · ' + period));
        if (detail.from && detail.to) root.append(element('p', 'small text-muted', 'Periode absensi: ' + detail.from + ' sampai ' + detail.to));
        if (detail.provisional) {
            root.append(element('p','small text-warning','Potongan sementara. Shift mendatang/berjalan dan cuti/sakit menunggu HR belum dikenai potongan.'));
        }
        if (downloadUrl) {
            const download = element('a', 'btn btn-outline-success btn-sm mb-3', 'Unduh rincian PDF');
            download.href = downloadUrl;
            download.addEventListener('click', async event => {
                event.preventDefault();
                if (download.dataset.busy) return;
                download.dataset.busy = '1';
                Swal.fire({title: 'Menyiapkan PDF…', allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading()});
                try {
                    const response = await fetch(downloadUrl, {headers: {'Accept': 'application/pdf'}, credentials: 'same-origin'});
                    if (!response.ok || !(response.headers.get('content-type') || '').includes('application/pdf')) throw new Error('Rincian belum tersedia atau sesi berakhir. Muat ulang halaman lalu coba lagi.');
                    const blob = await response.blob();
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = (response.headers.get('content-disposition') || '').match(/filename="?([^";]+)/)?.[1] || 'Rincian_Absensi.pdf';
                    document.body.append(link); link.click(); link.remove();
                    setTimeout(() => URL.revokeObjectURL(url), 60000);
                    Swal.fire({icon: 'success', title: 'PDF siap', text: 'Rincian potongan berhasil diunduh.'});
                } catch (error) {
                    Swal.fire({icon: 'error', title: 'Unduhan gagal', text: error.message});
                } finally { delete download.dataset.busy; }
            });
            root.append(download);
        }
        const summary = element('div', 'payroll-attendance-summary');
        [['Nilai satu hari · pembagi 25', detail.daily], ['Total sebelum batas', detail.raw], ['Batas periode · 25%', detail.cap], ['Potongan akhir', detail.amount]].forEach(([label, value], index) => {
            const card = element('div', 'payroll-attendance-stat' + (index === 3 ? ' is-final' : ''));
            card.append(element('span', '', label), element('strong', '', rupiah(value)));
            summary.append(card);
        });
        root.append(summary);
        root.append(element('p', 'small text-muted', 'Nominal setiap hari di bawah adalah sebelum batas 25%. Batas tersebut diterapkan pada jumlah seluruh potongan periode, bukan pada masing-masing tanggal.'));
        const shell = element('div', 'payroll-attendance-table-shell');
        const table = element('table', 'table table-hover align-middle payroll-attendance-table');
        const head = element('thead', 'table-success');
        const header = element('tr');
        ['Hari / tanggal', 'Jadwal & kehadiran', 'Keterangan', 'Dasar perhitungan', 'Potongan hari itu'].forEach(label => header.append(element('th', '', label)));
        head.append(header); table.append(head);
        const body = element('tbody');
        const timezone = detail.timezone || 'Asia/Jakarta';
        root.append(element('p', 'small text-muted', 'Tanggal kerja mengikuti tanggal mulai jadwal, termasuk shift malam yang pulang keesokan hari. Semua jam dalam zona ' + timezone + '.'));
        detail.days.filter(day => day.automatic > 0 || day.deduction > 0 || day.override || ['pending_leave','pending','upcoming','in_progress'].includes(day.status)).forEach(day => {
            const row = element('tr');
            const dateCell = element('td');
            dateCell.append(element('strong', '', new Intl.DateTimeFormat('id-ID', {timeZone: 'UTC', weekday: 'long'}).format(new Date(day.date + 'T12:00:00Z'))), element('div', 'small text-muted', day.date));
            const timeCell = element('td');
            const scheduleLabel = day.schedule_mode === 'shift' ? 'Shift' : (day.schedule_mode === 'weekly' ? 'Jadwal mingguan' : 'Jenis jadwal belum tersedia');
            timeCell.append(element('div', 'fw-semibold mb-1', scheduleLabel + (day.schedule_mode === 'shift' && day.schedule_name ? ' · ' + day.schedule_name : '')));
            timeCell.append(element('div', 'small', 'Jadwal: ' + stamp(day.scheduled_start_at, timezone) + ' – ' + stamp(day.scheduled_end_at, timezone)), element('div', 'small text-muted', 'Masuk: ' + stamp(day.check_in_at, timezone)), element('div', 'small text-muted', 'Pulang: ' + stamp(day.check_out_at, timezone)));
            const reasonCell = element('td');
            const reasons = [];
            if (day.status === 'pending_leave') reasons.push('Menunggu keputusan HR · belum dihitung');
            else if (['pending','upcoming','in_progress'].includes(day.status)) reasons.push(day.status === 'upcoming' ? 'Jadwal mendatang · belum dihitung' : 'Shift/batas pulang belum selesai · belum dihitung');
            else if (day.status === 'absent') reasons.push('Tidak masuk');
            else if (day.status === 'half_day') reasons.push('Tidak absen pulang · setengah hari');
            else {
                if (day.late_minutes > 0) reasons.push('Terlambat ' + day.late_minutes + ' menit setelah toleransi');
                if (day.early_minutes > 0) reasons.push('Pulang cepat ' + day.early_minutes + ' menit');
                if (!reasons.length) reasons.push('Hadir sesuai jadwal');
            }
            reasons.forEach(reason => reasonCell.append(element('div', 'small', reason)));
            const formulaCell = element('td');
            const effective = Number(day.effective_minutes) || 0;
            let formula;
            if (['pending_leave','pending','upcoming','in_progress'].includes(day.status)) formula = 'Belum dihitung';
            else if (day.status === 'absent') formula = '1 × ' + rupiah(detail.daily);
            else if (day.status === 'half_day') formula = '½ × ' + rupiah(detail.daily) + ' · tanpa tambahan terlambat';
            else {
                const lost = Math.min(effective, Math.max(0, Number(day.late_minutes)) + Math.max(0, Number(day.early_minutes)));
                formula = lost + ' / ' + effective + ' menit × ' + rupiah(detail.daily);
            }
            formulaCell.append(element('div', 'small', formula), element('div', 'small text-muted', 'Kerja efektif: ' + day.scheduled_minutes + ' − ' + day.break_minutes + ' istirahat = ' + effective + ' menit'));
            const amountCell = element('td', 'text-end');
            amountCell.append(element('strong', 'text-danger', rupiah(day.deduction)));
            if (day.override) {
                amountCell.append(element('div', 'small text-muted', 'Otomatis: ' + rupiah(day.automatic)), element('div', 'small', 'Penyesuaian: ' + day.override.reason));
            }
            row.append(dateCell, timeCell, reasonCell, formulaCell, amountCell); body.append(row);
        });
        table.append(body); shell.append(table); root.append(shell);
        root.append(element('p', 'small text-muted mt-3 mb-0', 'Metode proporsional tanpa jam mulai istirahat. Nilai harian dan potongan per hari dibulatkan ke rupiah. Jam ditampilkan dalam zona ' + timezone + '.'));
        let grid;
        Swal.fire({title: 'Rincian Potongan Absensi', html: root, width: Math.min(1120, window.innerWidth - 24), confirmButtonText: 'Tutup', confirmButtonColor: '#18754e',
            didOpen: () => {
                if (window.jQuery && jQuery.fn.DataTable) grid = jQuery(table).DataTable({paging: false, ordering: false, info: false, autoWidth: false, scrollX: true, scrollY: '38vh', scrollCollapse: true, dom: 'ft', language: {search: 'Cari tanggal / keterangan:', zeroRecords: 'Tidak ada rincian yang sesuai.', emptyTable: 'Tidak ada potongan pada periode ini.'}});
            }, willClose: () => { if (grid) grid.destroy(); }
        });
    };
    document.addEventListener('click', event => {
        const button = event.target.closest('[data-saved-attendance-detail]');
        if (!button) return;
        try { window.showPayrollAttendanceDetail(button.dataset.employeeName, button.dataset.payrollPeriod, JSON.parse(button.dataset.savedAttendanceDetail), button.dataset.downloadUrl); }
        catch { Swal.fire({icon: 'error', title: 'Rincian belum dapat dibuka', text: 'Data rincian tidak tersedia atau tidak valid.'}); }
    });
})();
