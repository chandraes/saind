window.createIncrementalDataTable = function (element, options = {}) {
    const node = $(element)[0];
    node.style.width = '100%';
    if (node.tBodies[0]?.querySelector('td[colspan]')) node.tBodies[0].replaceChildren();
    const grid = $(node).DataTable({
        paging: false, searching: false, ordering: false, info: false, autoWidth: true,
        scrollY: node.closest('.modal') ? '40vh' : '55vh', scrollCollapse: true, scrollX: true,
        language: {emptyTable: 'Belum ada data sesuai filter.'}, ...options
    });
    const body = grid.table().container().querySelector('.dataTables_scrollBody, .dt-scroll-body');
    body.tabIndex = 0;
    body.setAttribute('role', 'region');
    body.setAttribute('aria-label', 'Data tabel, gulir untuk melihat baris berikutnya');
    body.style.overscrollBehavior = 'contain';
    // Header & isi adalah dua tabel terpisah (scrollY): hitung ulang lebar kolom setelah font web
    // termuat dan saat lebar wadah berubah, agar garis kolom header selalu sejajar dengan isi.
    let frame = 0;
    const align = () => { cancelAnimationFrame(frame); frame = requestAnimationFrame(() => { if (node.isConnected) grid.columns.adjust(); }); };
    document.fonts?.ready.then(align);
    let lastWidth = 0;
    new ResizeObserver(entries => {
        const width = Math.round(entries[0].contentRect.width);
        if (width !== lastWidth) { lastWidth = width; align(); }
    }).observe(grid.table().container());
    return {
        grid, body,
        nearEnd: () => body.isConnected && body.clientHeight > 0 && body.scrollHeight - body.scrollTop - body.clientHeight <= 200,
        add(rows) {
            const top = body.scrollTop, left = body.scrollLeft;
            grid.rows.add(rows).draw(false);
            grid.columns.adjust();
            body.scrollTop = top;
            body.scrollLeft = left;
        },
        watch(callback) {
            const observer = new ResizeObserver(() => {
                if (!body.isConnected) { observer.disconnect(); return; }
                callback();
            });
            body.addEventListener('scroll', callback, {passive: true});
            observer.observe(body);
            node.closest('.modal')?.addEventListener('shown.bs.modal', () => { grid.columns.adjust(); callback(); });
        }
    };
};
