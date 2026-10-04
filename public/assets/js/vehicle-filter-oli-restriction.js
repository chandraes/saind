document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('change', async (event) => {
        const checkbox = event.target.closest('.vehicle-filter-oli-restriction');
        if (!checkbox) return;
        const previous = checkbox.dataset.saved === '1';
        const selected = checkbox.checked;
        checkbox.checked = previous;
        checkbox.disabled = true;
        try {
            const confirmation = await Swal.fire({
                icon: 'question', title: 'Konfirmasi pembatasan',
                text: `${selected ? 'Aktifkan' : 'Nonaktifkan'} penanda pembatasan filter & oli mesin untuk kendaraan ${checkbox.dataset.vehicle}?`,
                showCancelButton: true, confirmButtonText: 'Ya, simpan', cancelButtonText: 'Batal', focusCancel: true,
            });
            if (!confirmation.isConfirmed) return;
            Swal.fire({title: 'Menyimpan…', allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading()});
            const response = await fetch(checkbox.dataset.url, {
                method: 'PATCH',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': checkbox.dataset.token},
                body: JSON.stringify({pembatasan_filter_oli: selected}),
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.message || 'Perubahan gagal disimpan. Silakan muat ulang halaman dan coba kembali.');
            checkbox.checked = result.pembatasan_filter_oli;
            checkbox.dataset.saved = checkbox.checked ? '1' : '0';
            await Swal.fire({icon: 'success', title: 'Berhasil', text: result.message, confirmButtonText: 'OK'});
        } catch (error) {
            checkbox.checked = previous;
            await Swal.fire({icon: 'error', title: 'Gagal menyimpan', text: error.message, confirmButtonText: 'OK'});
        } finally {
            checkbox.disabled = false;
        }
    });
});
