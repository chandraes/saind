document.addEventListener('DOMContentLoaded', () => {
    const table = document.getElementById('note-inbox-table');
    const indicator = document.getElementById('note-inbox-loading');
    if (!table || !indicator) return;
    if (window.jQuery?.fn.select2) jQuery('#vendor_id').select2({width: '100%'});
    const grid = table.tBodies[0].querySelector('[colspan]') ? null : jQuery(table).DataTable({paging: false, searching: false, ordering: false, info: false, autoWidth: false});
    const stateUrl = new URL(window.location.href);
    stateUrl.searchParams.delete('page');
    for (const [key, value] of [...stateUrl.searchParams]) {
        if (!value) stateUrl.searchParams.delete(key);
    }
    stateUrl.searchParams.sort();
    const stateKey = 'note-inbox:' + stateUrl.pathname + stateUrl.search;
    let saved;
    try { saved = JSON.parse(sessionStorage.getItem(stateKey) || 'null'); } catch { saved = null; }
    let loadedPages = Number(new URL(window.location.href).searchParams.get('page') || 1);
    let restoring = Boolean(saved && loadedPages === 1);
    table.addEventListener('click', event => {
        if (!event.target.closest('a')) return;
        try { sessionStorage.setItem(stateKey, JSON.stringify({pages: loadedPages, scroll: window.scrollY})); } catch {}
    });
    let next = indicator.dataset.next;
    let loading = false;
    let failed = false;
    async function load() {
        if (!next || loading || failed) return;
        loading = true;
        indicator.setAttribute('aria-busy', 'true');
        indicator.textContent = 'Memuat nota…';
        try {
            const response = await fetch(next, {headers: {Accept: 'application/json'}});
            if (!response.ok) throw new Error('load failed');
            const data = await response.json();
            const body = document.createElement('tbody');
            body.innerHTML = data.rows;
            const rows = Array.from(body.rows);
            if (grid) grid.rows.add(rows).draw(false);
            else rows.forEach(row => table.tBodies[0].append(row));
            next = data.next;
            loadedPages++;
            indicator.textContent = next ? 'Memuat nota berikutnya saat Anda menggulir…' : 'Seluruh nota telah ditampilkan.';
        } catch {
            failed = true;
            indicator.textContent = 'Nota gagal dimuat. ';
            const retry = document.createElement('button');
            retry.type = 'button';
            retry.className = 'btn btn-outline-primary btn-sm';
            retry.textContent = 'Coba ulang';
            retry.addEventListener('click', () => {failed = false; load();});
            indicator.append(retry);
        } finally {
            loading = false;
            indicator.setAttribute('aria-busy', 'false');
            if (!restoring && next && !failed && indicator.getBoundingClientRect().top < window.innerHeight + 250) load();
        }
    }
    async function restore() {
        if (restoring) {
            while (next && !failed && loadedPages < saved.pages) await load();
            window.scrollTo(0, saved.scroll);
            restoring = false;
        }
        new IntersectionObserver(entries => {if (entries.some(entry => entry.isIntersecting)) load();}, {rootMargin: '250px'}).observe(indicator);
    }
    restore();
});
