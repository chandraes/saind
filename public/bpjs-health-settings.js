document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('bpjs-health-form');
    const wageInput = document.getElementById('minimum-wage');
    const rawWage = document.getElementById('minimum-wage-raw');
    const wage = new Cleave(wageInput, {
        numeral: true, numeralThousandsGroupStyle: 'thousand', delimiter: '.',
        numeralDecimalMark: ',', numeralDecimalScale: 0, numeralPositiveOnly: true,
        onValueChanged: function (event) {
            rawWage.value = event.target.rawValue;
            const amount = Number(event.target.rawValue);
            wageInput.setCustomValidity(amount > 12000000 || (event.target.rawValue !== '' && amount < 1) ? 'Isi nominal antara Rp1 dan Rp12.000.000.' : '');
        }
    });
    rawWage.value = wage.getRawValue();
    let confirmed = false;
    let pending = false;
    form?.addEventListener('submit', async function (event) {
        if (pending) { event.preventDefault(); return; }
        if (!confirmed) {
            event.preventDefault();
            const result = await Swal.fire({icon: 'question', title: 'Simpan pengaturan BPJS?', text: 'Perhitungan gaji baru pada tahun ini akan memakai nilai tersebut. Rekap tersimpan tetap utuh.', showCancelButton: true, confirmButtonText: 'Ya, simpan', cancelButtonText: 'Batal'});
            if (result.isConfirmed) { confirmed = true; form.requestSubmit(); }
            return;
        }
        pending = true;
        form.querySelectorAll('button').forEach(button => button.disabled = true);
        Swal.fire({title: 'Menyimpan pengaturan…', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, didOpen: () => Swal.showLoading()});
    });
    const feedback = document.getElementById('bpjs-feedback');
    if (feedback) { Swal.fire({icon: feedback.dataset.error === 'true' ? 'error' : 'success', title: feedback.dataset.error === 'true' ? 'Belum berhasil' : 'Tersimpan', text: feedback.dataset.message}); }
    if (window.jQuery && jQuery.fn.DataTable) {
        jQuery('#bpjs-settings-table').DataTable({paging: false, scrollY: '360px', scrollX: true, scrollCollapse: true, autoWidth: false, order: [[0, 'desc']], columnDefs: [{targets: 4, orderable: false, searchable: false}], language: {search: 'Cari:', emptyTable: 'Belum ada pengaturan. Isi nilai tahun yang diperlukan.', info: 'Menampilkan _TOTAL_ pengaturan', infoEmpty: 'Belum ada pengaturan'}});
    }
});
